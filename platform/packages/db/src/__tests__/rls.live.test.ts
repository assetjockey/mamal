/**
 * Live RLS proof. Runs against a real Postgres as a NON-SUPERUSER role,
 * because superusers bypass row level security entirely — a test run as the
 * owner would pass vacuously and prove nothing.
 *
 * Skipped unless TEST_DATABASE_URL is set (CI sets it; `pnpm db:test:setup`
 * creates the role locally).
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { unsafeUnscopedDb, withWorkspace, closeDb, asPlatformAdmin } from '../client.ts';
import { users, workspaces, projects, sites } from '../schema/index.ts';

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('row level security (live)', () => {
  const db = unsafeUnscopedDb(URL);
  let wsA = '';
  let wsB = '';

  beforeAll(async () => {
    // Seed as platform admin so the policies let the fixture in.
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx
        .insert(users)
        .values({ email: `rls-${Date.now()}@test.local`, name: 'RLS Fixture' })
        .returning();
      const [a] = await tx
        .insert(workspaces)
        .values({ slug: `ws-a-${Date.now()}`, name: 'A', ownerUserId: u!.id })
        .returning();
      const [b] = await tx
        .insert(workspaces)
        .values({ slug: `ws-b-${Date.now()}`, name: 'B', ownerUserId: u!.id })
        .returning();
      wsA = a!.id;
      wsB = b!.id;
      for (const ws of [a!, b!]) {
        const [p] = await tx
          .insert(projects)
          .values({ workspaceId: ws.id, name: 'Default', slug: 'default', isDefault: true })
          .returning();
        await tx.insert(sites).values({
          workspaceId: ws.id,
          projectId: p!.id,
          host: `${ws.slug}.example.com`,
          rootUrl: `https://${ws.slug}.example.com`,
        });
      }
    }, { db });
  });

  afterAll(async () => {
    // Both workspaces, then the user — owner_user_id is ON DELETE RESTRICT, so
    // leaving one behind orphans the fixture and it accumulates across runs.
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id in (${wsA}, ${wsB})`);
      await tx.execute(sql`delete from users where email like 'rls-%@test.local'`);
    }, { db });
    await closeDb();
  });

  it('the test role is not a superuser (otherwise RLS is bypassed)', async () => {
    const rows = await db.execute<{ usesuper: boolean }>(
      sql`select usesuper from pg_user where usename = current_user`,
    );
    expect(rows[0]?.usesuper, 'run this test as a non-superuser role').toBe(false);
  });

  it('a workspace sees only its own sites', async () => {
    const seen = await withWorkspace(wsA, (tx) => tx.select().from(sites), { db });
    expect(seen).toHaveLength(1);
    expect(seen[0]!.workspaceId).toBe(wsA);
  });

  it('a workspace cannot read another workspace rows even when asked by id', async () => {
    const leaked = await withWorkspace(
      wsA,
      (tx) => tx.select().from(sites).where(sql`workspace_id = ${wsB}`),
      { db },
    );
    expect(leaked).toEqual([]);
  });

  it('WITH CHECK stops a write that would land in another workspace', async () => {
    // postgres.js wraps the driver error; the real PG message is on .cause.
    // 42501 = insufficient_privilege, which is what an RLS WITH CHECK raises.
    const err = await withWorkspace(
      wsA,
      async (tx) => {
        const [p] = await tx.select().from(projects).limit(1);
        await tx.insert(sites).values({
          workspaceId: wsB, // hostile value
          projectId: p!.id,
          host: 'evil.example.com',
          rootUrl: 'https://evil.example.com',
        });
        return null;
      },
      { db },
    ).then(
      () => null,
      (e: { cause?: { code?: string; message?: string } }) => e.cause,
    );

    expect(err, 'the cross-tenant insert was NOT rejected').toBeTruthy();
    expect(err?.code).toBe('42501');
    expect(err?.message).toMatch(/row-level security/i);
  });

  it('the workspaces table itself isolates on id', async () => {
    const seen = await withWorkspace(wsA, (tx) => tx.select().from(workspaces), { db });
    expect(seen.map((w) => w.id)).toEqual([wsA]);
  });
});
