import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb } from '@mamal/db';
import { claimDue, withLeaderLock } from '../claim.ts';
import { QUEUES, queueFor } from '../queues.ts';

describe('queue taxonomy', () => {
  it('never retries a probe — a failed check IS the signal', () => {
    expect(QUEUES['monitor.check'].attempts).toBe(0);
    expect(QUEUES['monitor.probe.heavy'].attempts).toBe(0);
  });

  it('caps the Lighthouse pool, because concurrency is the cost cap', () => {
    expect(QUEUES['audit.lighthouse'].concurrency).toBeLessThanOrEqual(4);
  });

  it('splits AI queues by latency, not by provider', () => {
    expect(QUEUES['ai.video'].concurrency).toBeLessThan(QUEUES['ai.text'].concurrency);
  });

  it('routes free tenants to a throttled mirror where one exists', () => {
    expect(queueFor('audit.crawl', true)).toBe('free.crawl');
    expect(queueFor('audit.crawl', false)).toBe('audit.crawl');
    // No mirror: falls back to the shared queue rather than inventing one.
    expect(queueFor('notify', true)).toBe('notify');
  });

  it('free mirrors run at concurrency 1-2 so a free user cannot force a scale-up', () => {
    expect(QUEUES['free.crawl'].concurrency).toBe(1);
    expect(QUEUES['free.probe'].concurrency).toBeLessThanOrEqual(2);
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

// Both suites share one pooled connection; closing it inside the first would
// pull it out from under the second.
afterAll(() => closeDb());

d('claim-and-enqueue', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `job${Date.now()}`;
  let ws = '';
  let project = '';

  /** monitors is the real schedulable entity this pattern exists for. */
  const seedMonitors = async (count: number, dueMinutesAgo: number) => {
    await asPlatformAdmin(async (tx) => {
      for (let i = 0; i < count; i++) {
        await tx.execute(sql`
          insert into monitors_stub (workspace_id, project_id, name, next_check_at, is_enabled)
          values (${ws}, ${project}, ${'m' + i},
                  now() - (${dueMinutesAgo} * interval '1 minute'), true)`);
      }
    }, { db });
  };

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      // A stand-in for the real monitors table, which arrives in Phase 5.
      await tx.execute(sql`
        create table if not exists monitors_stub (
          id uuid primary key default uuidv7(),
          workspace_id uuid not null,
          project_id uuid not null,
          name text not null,
          next_check_at timestamptz not null default now(),
          is_enabled boolean not null default true
        )`);
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Job') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Job', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`drop table if exists monitors_stub`);
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
  });

  beforeEach(async () => {
    await asPlatformAdmin((tx) => tx.execute(sql`delete from monitors_stub`), { db });
  });

  const claim = (batchSize?: number) =>
    claimDue(db, {
      table: 'monitors_stub',
      dueColumn: 'next_check_at',
      where: sql`is_enabled`,
      intervalSeconds: 300,
      ...(batchSize ? { batchSize } : {}),
    });

  it('claims everything that is due', async () => {
    await seedMonitors(5, 10);
    const claimed = await claim();
    expect(claimed).toHaveLength(5);
    expect(claimed[0]!.workspaceId).toBe(ws);
  });

  /** The property that makes N schedulers safe without leader election. */
  it('does not hand the same row to a second pass', async () => {
    await seedMonitors(3, 10);
    expect(await claim()).toHaveLength(3);
    expect(await claim()).toHaveLength(0);
  });

  it('pushes the due time forward by the interval', async () => {
    await seedMonitors(1, 10);
    await claim();
    const [row] = await asPlatformAdmin(
      (tx) => tx.execute<{ seconds: number }>(sql`
        select extract(epoch from next_check_at - now())::int as seconds from monitors_stub`),
      { db },
    );
    expect(Number(row!.seconds)).toBeGreaterThan(250);
    expect(Number(row!.seconds)).toBeLessThanOrEqual(300);
  });

  it('honours the extra predicate', async () => {
    await seedMonitors(2, 10);
    await asPlatformAdmin((tx) => tx.execute(sql`update monitors_stub set is_enabled = false`), { db });
    expect(await claim()).toHaveLength(0);
  });

  it('ignores rows that are not due yet', async () => {
    await seedMonitors(2, -30); // due in 30 minutes
    expect(await claim()).toHaveLength(0);
  });

  it('respects the batch size, so one pass cannot flood the queue', async () => {
    await seedMonitors(10, 10);
    expect(await claim(4)).toHaveLength(4);
    expect(await claim(4)).toHaveLength(4);
    expect(await claim(4)).toHaveLength(2);
  });

  it('two concurrent schedulers split the work and never duplicate it', async () => {
    await seedMonitors(20, 10);
    const [a, b] = await Promise.all([claim(), claim()]);
    const ids = [...a.map((r) => r.id), ...b.map((r) => r.id)];
    expect(ids).toHaveLength(20);
    expect(new Set(ids).size).toBe(20);
  });
});

d('leader lock', () => {
  const db = unsafeUnscopedDb(URL);

  it('runs the work for the holder', async () => {
    const result = await withLeaderLock(db, 'test-relay', async () => 'ran');
    expect(result).toBe('ran');
  });

  it('releases the lock afterwards, even when the work throws', async () => {
    await withLeaderLock(db, 'test-throwing', async () => {
      throw new Error('boom');
    }).catch(() => null);
    // A second acquisition proves the lock was released.
    expect(await withLeaderLock(db, 'test-throwing', async () => 'ok')).toBe('ok');
  });
});
