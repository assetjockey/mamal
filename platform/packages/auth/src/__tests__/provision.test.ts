import { sql } from 'drizzle-orm';
import { afterAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { can, memberOf, provisionWorkspace, workspacesFor } from '../provision.ts';

describe('can()', () => {
  const owner = { role: 'owner', toolGrants: {} };
  const viewer = { role: 'viewer', toolGrants: {} };
  const member = { role: 'member', toolGrants: {} };

  it('owners and admins can do anything', () => {
    expect(can(owner, 'link:links:delete')).toBe(true);
    expect(can({ role: 'admin', toolGrants: {} }, 'monitor:monitors:create')).toBe(true);
  });

  it('viewers read but do not write', () => {
    expect(can(viewer, 'link:links:read')).toBe(true);
    expect(can(viewer, 'link:links:create')).toBe(false);
  });

  it('members write', () => {
    expect(can(member, 'link:links:create')).toBe(true);
  });

  /** The point of per-tool grants: Link yes, Market no, on the same person. */
  it('a per-tool grant overrides the base role in both directions', () => {
    const mixed = { role: 'member', toolGrants: { market: 'viewer', monitor: null } };
    expect(can(mixed, 'link:links:create')).toBe(true);
    expect(can(mixed, 'market:posts:create')).toBe(false);
    expect(can(mixed, 'market:posts:read')).toBe(true);
    expect(can(mixed, 'monitor:monitors:read')).toBe(false);
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('provisioning', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `prov${Date.now()}`;
  const emails: string[] = [];

  const makeUser = async (email: string, name?: string) => {
    emails.push(email);
    const [u] = await asPlatformAdmin(
      (tx) => tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${email}, ${name ?? null}) returning id`),
      { db },
    );
    return u!.id;
  };

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      for (const e of emails) {
        await tx.execute(sql`delete from workspaces where owner_user_id in
          (select id from users where email = ${e})`);
        await tx.execute(sql`delete from users where email = ${e}`);
      }
    }, { db });
    await closeDb();
  });

  it('gives a new user a workspace, a Default project and an owner seat', async () => {
    const userId = await makeUser(`${tag}-a@test.local`, 'Ada Lovelace');
    const result = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: userId, email: `${tag}-a@test.local`, name: 'Ada Lovelace' }),
      { db },
    );

    expect(result.slug).toBe('ada-lovelace');

    const [project] = await withWorkspace(
      result.workspaceId,
      (tx) => tx.execute<{ name: string; is_default: boolean }>(sql`
        select name, is_default from projects where workspace_id = ${result.workspaceId}`),
      { db },
    );
    expect(project).toMatchObject({ name: 'Default', is_default: true });

    const member = await withWorkspace(
      result.workspaceId,
      (tx) => memberOf(tx, result.workspaceId, userId),
      { db },
    );
    expect(member?.role).toBe('owner');
  });

  it('derives a slug from the email when there is no name', async () => {
    const email = `${tag}-b@test.local`;
    const userId = await makeUser(email);
    const r = await asPlatformAdmin((tx) => provisionWorkspace(tx, { id: userId, email }), { db });
    expect(r.slug).toBe(`${tag}-b`);
  });

  it('never collides two workspaces on the same slug', async () => {
    const a = await makeUser(`${tag}-c@test.local`, 'Same Name');
    const b = await makeUser(`${tag}-d@test.local`, 'Same Name');
    const first = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: a, email: `${tag}-c@test.local`, name: 'Same Name' }), { db });
    const second = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: b, email: `${tag}-d@test.local`, name: 'Same Name' }), { db });
    expect(first.slug).toBe('same-name');
    expect(second.slug).toBe('same-name-2');
  });

  /**
   * The reason provisioning is not optional: without a workspace there is no
   * RLS scope, so a signed-in user would see nothing at all.
   */
  it('a freshly provisioned workspace already resolves the free tier', async () => {
    const email = `${tag}-e@test.local`;
    const userId = await makeUser(email, 'Fresh Start');
    const { workspaceId } = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: userId, email, name: 'Fresh Start' }), { db });

    const decision = await withWorkspace(
      workspaceId,
      async (tx) => {
        const ctx = await loadContext(tx, workspaceId, 'link.links');
        return ctx ? resolve(ctx) : null;
      },
      { db },
    );
    expect(decision?.allowed).toBe(true);
    expect(decision?.allowed && decision.limit).toBe(25);
  });

  it('lists every workspace a user belongs to', async () => {
    const email = `${tag}-f@test.local`;
    const userId = await makeUser(email, 'Multi Member');
    const own = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: userId, email, name: 'Multi Member' }), { db });

    // invited into someone else's workspace
    const otherEmail = `${tag}-g@test.local`;
    const otherId = await makeUser(otherEmail, 'Host');
    const host = await asPlatformAdmin(
      (tx) => provisionWorkspace(tx, { id: otherId, email: otherEmail, name: 'Host' }), { db });
    await asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into workspace_members (workspace_id, user_id, role)
        values (${host.workspaceId}, ${userId}, 'member')`),
      { db },
    );

    const list = await asPlatformAdmin((tx) => workspacesFor(tx, userId), { db });
    expect(list).toHaveLength(2);
    expect(list.find((w) => w.id === own.workspaceId)?.role).toBe('owner');
    expect(list.find((w) => w.id === host.workspaceId)?.role).toBe('member');
  });
});
