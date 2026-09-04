/**
 * Retention has to be tested by deleting real rows.
 *
 * The failure mode is silent in both directions: a sweep that never runs looks
 * identical to one that has nothing to do, and a sweep with the wrong cutoff
 * destroys data nobody notices until they go looking for it. So every case here
 * asserts both halves — what went, and what stayed.
 */
import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { runRetention, eventSweeper, type Sweeper } from '../index.ts';

const db = unsafeUnscopedDb();
const tag = `ret${Date.now()}`;

let freeWs = '';
let paidWs = '';
let zeroWs = '';

const daysAgo = (n: number) => new Date(Date.now() - n * 86_400_000);

async function makeWorkspace(slug: string, planKey: string | null): Promise<string> {
  return asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${slug}@test.local`}, 'Retention') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id)
      values (${slug}, 'Retention', ${u!.id}) returning id`);
    await tx.execute(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${w!.id}, 'Default', 'default', true)`);
    if (planKey) {
      await tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status)
        select ${w!.id}, id, 'active' from plans where key = ${planKey}`);
    }
    return w!.id;
  }, { db });
}

async function seedEvents(workspaceId: string, ages: number[]): Promise<void> {
  await withWorkspace(workspaceId, async (tx) => {
    const [p] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${workspaceId} limit 1`);
    for (const age of ages) {
      await tx.execute(sql`
        insert into events_raw
          (workspace_id, project_id, event_id, kind, tool, ts, subject_type, subject_id)
        values (${workspaceId}, ${p!.id}, ${randomUUID()}, 'pageview', 'track',
                ${daysAgo(age).toISOString()}::timestamptz, 'site', ${randomUUID()})`);
    }
  }, { db });
}

const countEvents = (workspaceId: string) =>
  withWorkspace(
    workspaceId,
    async (tx) => {
      const [r] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from events_raw where workspace_id = ${workspaceId}`);
      return r!.n;
    },
    { db },
  );

beforeAll(async () => {
  freeWs = await makeWorkspace(`${tag}-free`, null);          // free plan floor: 7 days
  paidWs = await makeWorkspace(`${tag}-paid`, 'audit_pro');   // a longer window

  // A plan that resolves retention to 0 — the shape a mis-seeded or
  // mis-edited plan would have, and the one the floor guard exists for.
  await asPlatformAdmin(async (tx) => {
    const [plan] = await tx.execute<{ id: string }>(sql`
      insert into plans (key, name, kind, status)
      values (${`${tag}-zero`}, 'Zero retention', 'subscription', 'active')
      returning id`);
    await tx.execute(sql`
      insert into plan_entitlements (plan_id, feature_key, mode, limit_value)
      values (${plan!.id}, 'core.data_retention_days', 'limit', 0)`);
  }, { db });
  zeroWs = await makeWorkspace(`${tag}-zero-ws`, `${tag}-zero`);
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where slug like ${`${tag}%`}`);
    await tx.execute(sql`delete from users where email like ${`${tag}%`}`);
    await tx.execute(sql`delete from plans where key like ${`${tag}%`}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await asPlatformAdmin(
    (tx) => tx.execute(
      sql`delete from events_raw where workspace_id in (${freeWs}, ${paidWs}, ${zeroWs})`),
    { db },
  );
});

describe('retention', () => {
  it('deletes past the free tier window and keeps what is inside it', async () => {
    await seedEvents(freeWs, [1, 3, 6, 30, 400]);
    expect(await countEvents(freeWs)).toBe(5);

    const report = await runRetention(db, [eventSweeper], { workspaceIds: [freeWs] });

    expect(report.outcomes[0]!.retentionDays, 'free resolves to the 7-day floor').toBe(7);
    expect(report.deleted).toBe(2);
    expect(await countEvents(freeWs)).toBe(3);
  });

  it('honours a longer window from a paid plan', async () => {
    await seedEvents(paidWs, [1, 30, 400]);
    const report = await runRetention(db, [eventSweeper], { workspaceIds: [paidWs] });

    expect(report.outcomes[0]!.retentionDays).toBeGreaterThan(7);
    // The 30-day-old row survives here and did not on free — that difference IS
    // the entitlement doing its job.
    expect(await countEvents(paidWs)).toBe(2);
  });

  it('never touches another workspace', async () => {
    await seedEvents(freeWs, [400]);
    await seedEvents(paidWs, [400]);

    await runRetention(db, [eventSweeper], { workspaceIds: [freeWs] });

    expect(await countEvents(freeWs)).toBe(0);
    expect(await countEvents(paidWs), 'sweeping one workspace must not reach another').toBe(1);
  });

  it('refuses to sweep when the resolved window is below the floor', async () => {
    // The worst failure this job can have: a plan resolving to 0 days would
    // otherwise set the cutoff to "now" and delete everything the workspace
    // has, including rows written seconds ago.
    await seedEvents(zeroWs, [0, 1, 400]);
    expect(await countEvents(zeroWs)).toBe(3);

    const report = await runRetention(db, [eventSweeper], { workspaceIds: [zeroWs] });

    expect(report.outcomes[0]!.error).toContain('below the');
    expect(report.deleted).toBe(0);
    expect(await countEvents(zeroWs), 'a 0-day window must delete nothing at all').toBe(3);
  });

  it('one workspace failing does not stop the others', async () => {
    await seedEvents(freeWs, [400]);
    await seedEvents(paidWs, [400]);
    const exploding: Sweeper = {
      key: 'boom',
      sweep: async (_tx, workspaceId) => {
        if (workspaceId === freeWs) throw new Error('deliberate');
        return 0;
      },
    };

    const report = await runRetention(db, [exploding, eventSweeper], {
      workspaceIds: [freeWs, paidWs],
    });

    expect(report.outcomes.find((o) => o.workspaceId === freeWs)!.error).toContain('deliberate');
    // The second workspace still swept — a single bad row must not freeze
    // retention platform-wide.
    expect(await countEvents(paidWs)).toBe(0);
  });

  it('reports what it deleted, per sweeper', async () => {
    await seedEvents(freeWs, [400, 401]);
    const report = await runRetention(db, [eventSweeper], { workspaceIds: [freeWs] });
    expect(report.outcomes[0]!.deleted).toEqual({ events_raw: 2 });
    expect(report.workspaces).toBe(1);
  });
});
