import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { coreUrn, isUrn, makeUrn, parseUrn } from '../urn.ts';
import { mint, neighbors, pick, relate, resolveUrn, unrelate } from '../registry.ts';

describe('URN grammar', () => {
  it('round-trips', () => {
    const urn = makeUrn('monitor', 'monitor', 'abc-123');
    expect(urn).toBe('urn:mamal:monitor:monitor:abc-123');
    expect(parseUrn(urn)).toEqual({ tool: 'monitor', type: 'monitor', id: 'abc-123' });
  });

  it('rejects malformed input rather than producing an unaddressable resource', () => {
    expect(() => makeUrn('mon itor', 'x', '1')).toThrow(/invalid URN tool/);
    expect(() => makeUrn('monitor', 'a:b', '1')).toThrow(/invalid URN type/);
    expect(() => makeUrn('monitor', 'x', '')).toThrow(/id is required/);
    expect(() => parseUrn('urn:other:a:b:c')).toThrow(/not a mamal URN/);
    expect(isUrn('nope')).toBe(false);
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('resource registry', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `res${Date.now()}`;
  let ws = '';
  let project = '';
  let siteId = '';

  const inWs = <T>(fn: Parameters<typeof withWorkspace<T>>[1]) => withWorkspace(ws, fn, { db });

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Res') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Res', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
      const [s] = await tx.execute<{ id: string }>(sql`
        insert into sites (workspace_id, project_id, host, root_url)
        values (${ws}, ${project}, ${tag + '.example.com'}, ${'https://' + tag + '.example.com'})
        returning id`);
      siteId = s!.id;
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  it('mints and resolves', async () => {
    const r = await inWs((tx) =>
      mint(tx, { workspaceId: ws, projectId: project, tool: 'core', type: 'site', externalId: siteId, label: 'Example' }),
    );
    expect(r.urn).toBe(coreUrn.site(siteId));
    const found = await inWs((tx) => resolveUrn(tx, ws, r.urn));
    expect(found?.label).toBe('Example');
  });

  it('mint is idempotent — re-registering updates rather than duplicating', async () => {
    await inWs((tx) =>
      mint(tx, { workspaceId: ws, projectId: project, tool: 'core', type: 'site', externalId: siteId, label: 'Renamed' }),
    );
    const rows = await inWs((tx) =>
      tx.execute<{ n: number }>(sql`
        select count(*)::int as n from resources where workspace_id = ${ws} and type = 'site'`),
    );
    expect(Number(rows[0]!.n)).toBe(1);
    const found = await inWs((tx) => resolveUrn(tx, ws, coreUrn.site(siteId)));
    expect(found?.label).toBe('Renamed');
  });

  /**
   * The payoff. Audit, Monitor and Track each register their own object
   * against the SAME core site, and one query renders the panel that shows
   * all of it — with no tool importing another.
   */
  describe('the Connected panel', () => {
    const site = () => coreUrn.site(siteId);
    const monitorUrn = makeUrn('monitor', 'monitor', 'mon-1');
    const auditUrn = makeUrn('audit', 'run', 'aud-1');
    const linkUrn = makeUrn('link', 'link', 'lnk-1');

    beforeAll(async () => {
      await inWs(async (tx) => {
        for (const [tool, type, id, label] of [
          ['monitor', 'monitor', 'mon-1', 'Homepage uptime'],
          ['audit', 'run', 'aud-1', 'Weekly audit'],
          ['link', 'link', 'lnk-1', 'Campaign link'],
        ] as const) {
          await mint(tx, { workspaceId: ws, projectId: project, tool, type, externalId: id, label });
        }
        await relate(tx, { workspaceId: ws, from: monitorUrn, to: site(), relation: 'monitors', createdBy: 'automation:broken-link' });
        await relate(tx, { workspaceId: ws, from: auditUrn, to: site(), relation: 'audits', createdBy: 'user' });
        await relate(tx, { workspaceId: ws, from: linkUrn, to: site(), relation: 'shortens', createdBy: 'user' });
      });
    });

    it('shows every tool attached to one site', async () => {
      const found = await inWs((tx) => neighbors(tx, ws, site()));
      expect(found.map((n) => n.tool).sort()).toEqual(['audit', 'link', 'monitor']);
      expect(found.every((n) => n.direction === 'in')).toBe(true);
    });

    it('filters by relation', async () => {
      const found = await inWs((tx) => neighbors(tx, ws, site(), { relation: 'monitors' }));
      expect(found).toHaveLength(1);
      expect(found[0]!.label).toBe('Homepage uptime');
    });

    it('filters by tool', async () => {
      const found = await inWs((tx) => neighbors(tx, ws, site(), { tool: 'audit' }));
      expect(found).toHaveLength(1);
    });

    it('reads from the other end too', async () => {
      const found = await inWs((tx) => neighbors(tx, ws, monitorUrn));
      expect(found).toHaveLength(1);
      expect(found[0]!.type).toBe('site');
      expect(found[0]!.direction).toBe('out');
    });

    it('records who made the edge, so an automation can clean up only its own', async () => {
      const found = await inWs((tx) => neighbors(tx, ws, site(), { relation: 'monitors' }));
      expect(found[0]!.createdBy).toBe('automation:broken-link');
    });

    it('relate is idempotent', async () => {
      await inWs((tx) =>
        relate(tx, { workspaceId: ws, from: monitorUrn, to: site(), relation: 'monitors' }),
      );
      const found = await inWs((tx) => neighbors(tx, ws, site(), { relation: 'monitors' }));
      expect(found).toHaveLength(1);
    });

    it('unrelate removes the edge but keeps both resources', async () => {
      await inWs((tx) =>
        unrelate(tx, { workspaceId: ws, from: linkUrn, to: site(), relation: 'shortens' }),
      );
      const found = await inWs((tx) => neighbors(tx, ws, site()));
      expect(found.map((n) => n.tool).sort()).toEqual(['audit', 'monitor']);
      expect(await inWs((tx) => resolveUrn(tx, ws, linkUrn))).not.toBeNull();
    });

    it('refuses to link an unknown resource rather than creating a dangling edge', async () => {
      await expect(
        inWs((tx) =>
          relate(tx, { workspaceId: ws, from: makeUrn('link', 'link', 'ghost'), to: site(), relation: 'shortens' }),
        ),
      ).rejects.toThrow(/unknown resource/);
    });
  });

  it('pick searches by label for the automations builder', async () => {
    const found = await inWs((tx) => pick(tx, ws, { type: 'monitor', query: 'Homepage' }));
    expect(found).toHaveLength(1);
  });
});
