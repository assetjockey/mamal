import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb } from '@mamal/db';
import { PostgresEventStore } from '../adapters/postgres.ts';
import type { EventInput } from '../schema.ts';

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('event store', () => {
  const db = unsafeUnscopedDb(URL);
  const store = new PostgresEventStore(db);
  const tag = `ev${Date.now()}`;
  let ws = '';
  let project = '';
  const linkId = randomUUID();
  const siteId = randomUUID();

  const at = (minutesAgo: number) => new Date(Date.now() - minutesAgo * 60_000);
  const range = { from: at(120), to: new Date(Date.now() + 60_000) };

  const event = (over: Partial<EventInput> = {}): EventInput => ({
    workspaceId: ws,
    projectId: project,
    kind: 'click',
    tool: 'link',
    subjectId: linkId,
    subjectType: 'link',
    eventId: randomUUID(),
    ts: at(10),
    ...over,
  });

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Ev') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Ev', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
    }, { db });
  });

  afterAll(async () => {
    await db.execute(sql`delete from events_raw where workspace_id = ${ws}`);
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    await db.execute(sql`delete from events_raw where workspace_id = ${ws}`);
  });

  it('inserts a batch', async () => {
    const n = await store.insert([event(), event(), event()]);
    expect(n).toBe(3);
    expect((await store.count({ workspaceId: ws, range })).total).toBe(3);
  });

  it('is idempotent on event id — a redelivered edge batch does not double count', async () => {
    const row = event();
    await store.insert([row]);
    await store.insert([row]);
    expect((await store.count({ workspaceId: ws, range })).total).toBe(1);
  });

  it('counts unique visitors distinctly from hits', async () => {
    await store.insert([
      event({ visitorId: 'v1' }), event({ visitorId: 'v1' }), event({ visitorId: 'v2' }),
    ]);
    const { total, uniques } = await store.count({ workspaceId: ws, range });
    expect(total).toBe(3);
    expect(uniques).toBe(2);
  });

  it('excludes bots from every aggregate', async () => {
    await store.insert([event(), event({ isBot: true })]);
    expect((await store.count({ workspaceId: ws, range })).total).toBe(1);
  });

  it('groups by a dimension, most frequent first', async () => {
    await store.insert([
      event({ country: 'DE' }), event({ country: 'DE' }), event({ country: 'US' }),
    ]);
    const buckets = await store.aggregate({
      workspaceId: ws, range, dimension: 'country',
    });
    expect(buckets[0]).toMatchObject({ key: 'DE', count: 2 });
    expect(buckets[1]).toMatchObject({ key: 'US', count: 1 });
  });

  it('refuses a dimension that is not on the allow-list', async () => {
    await expect(
      store.aggregate({ workspaceId: ws, range, dimension: 'utm' as never }),
    ).rejects.toThrow(/not an aggregatable dimension/);
  });

  it('never returns another workspace rows', async () => {
    await store.insert([event()]);
    const other = await store.count({ workspaceId: randomUUID(), range });
    expect(other.total).toBe(0);
  });

  /**
   * The reason the fact table is shared: one click_id ties a Link click to the
   * Track pageview it caused and the conversion that followed. Without it,
   * "Link feeds Track" would be an ETL job.
   */
  describe('cross-tool attribution', () => {
    it('joins a click, its pageview and its conversion by click_id', async () => {
      const clickId = randomUUID();
      await store.insert([
        event({ kind: 'click', tool: 'link', subjectId: linkId, subjectType: 'link', clickId, ts: at(30) }),
        event({ kind: 'pageview', tool: 'track', subjectId: siteId, subjectType: 'site', clickId, ts: at(29), path: '/pricing' }),
        event({ kind: 'conversion', tool: 'track', subjectId: siteId, subjectType: 'site', clickId, ts: at(20), name: 'signup', value: 49 }),
        // an unrelated click that must not appear
        event({ kind: 'click', clickId: randomUUID(), ts: at(25) }),
      ]);

      const journey = await store.journey(ws, clickId);
      expect(journey.map((e) => e.kind)).toEqual(['click', 'pageview', 'conversion']);
      expect(journey.map((e) => e.tool)).toEqual(['link', 'track', 'track']);
      expect(journey[2]!.value).toBe(49);
    });

    it('reads Link clicks and Track pageviews from the same table', async () => {
      const clickId = randomUUID();
      await store.insert([
        event({ kind: 'click', tool: 'link', clickId }),
        event({ kind: 'pageview', tool: 'track', subjectId: siteId, subjectType: 'site', clickId }),
      ]);
      const byTool = await store.aggregate({ workspaceId: ws, range, dimension: 'tool' });
      expect(byTool.map((b) => b.key).sort()).toEqual(['link', 'track']);
    });
  });

  it('prunes only what is older than the retention window', async () => {
    await store.insert([
      event({ ts: at(60 * 24 * 40) }), // 40 days old
      event({ ts: at(5) }),
    ]);
    const removed = await store.prune(ws, at(60 * 24 * 30));
    expect(removed).toBe(1);
    expect((await store.count({ workspaceId: ws, range })).total).toBe(1);
  });
});
