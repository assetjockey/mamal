/**
 * Market's Module 4A, against a real database.
 *
 * The finders themselves are unit-tested; what needs a real database is the
 * part that keeps them honest — that a dismissal survives a recompute, that a
 * batch of keywords is refused as a batch, and that re-running research does
 * not blank figures a paid source already paid for.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  createRankConfig, isNotableMove, markConnectionFailed, normaliseDomain,
  recomputeOpportunities, recordRankSnapshots, saveConnection, setOpportunityStatus,
  trackKeywords, upsertKeywords,
} from '../service.ts';
import { marketManifest } from '../manifest.ts';

const db = unsafeUnscopedDb();
const tag = `mkt${Date.now()}`;

let ws = '';
let project = '';
let connectionId = '';

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Market') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Market', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'market_pro'`);
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

/* ------------------------------------------------------------ connections */

describe('connections', () => {
  it('reconnecting updates rather than duplicating', async () => {
    await withWorkspace(ws, async (tx) => {
      const first = await saveConnection(tx, {
        workspaceId: ws, projectId: project,
        provider: 'google_search_console', externalId: 'sc-domain:example.com',
        displayName: 'example.com', credentialsEncrypted: 'v1.enc',
      });
      // A second row would double every metric this connection feeds, with
      // nothing on screen to explain the jump.
      const again = await saveConnection(tx, {
        workspaceId: ws, projectId: project,
        provider: 'google_search_console', externalId: 'sc-domain:example.com',
        displayName: 'example.com (renamed)',
      });
      expect(again).toBe(first);
      connectionId = first;

      const [row] = await tx.execute<{ display_name: string; credentials_encrypted: string }>(sql`
        select display_name, credentials_encrypted from market_connections where id = ${first}`);
      expect(row!.display_name).toBe('example.com (renamed)');
      // Reconnecting without a new token must not wipe the one that works.
      expect(row!.credentials_encrypted).toBe('v1.enc');
    }, { db });
  });

  it('separates “never connected” from “stopped working”', async () => {
    await withWorkspace(ws, async (tx) => {
      await markConnectionFailed(tx, {
        connectionId, status: 'expired', error: 'Token has been expired or revoked.',
      });
      const [row] = await tx.execute<{ status: string; last_error: string; credentials_encrypted: string }>(sql`
        select status, last_error, credentials_encrypted from market_connections where id = ${connectionId}`);
      expect(row!.status).toBe('expired');
      expect(row!.last_error).toMatch(/revoked/);
      // The token is kept: the difference between an onboarding prompt and a
      // "reconnect" alert is whether one was ever there.
      expect(row!.credentials_encrypted).not.toBeNull();

      // Reconnecting clears the failure.
      await saveConnection(tx, {
        workspaceId: ws, projectId: project,
        provider: 'google_search_console', externalId: 'sc-domain:example.com',
        displayName: 'example.com', credentialsEncrypted: 'v2.enc',
      });
      const [after] = await tx.execute<{ status: string; last_error: string | null }>(sql`
        select status, last_error from market_connections where id = ${connectionId}`);
      expect(after).toMatchObject({ status: 'active', last_error: null });
    }, { db });
  });

  it('counts each provider against its own allowance', async () => {
    await withWorkspace(ws, async (tx) => {
      // A social account must not consume the Search Console allowance.
      await saveConnection(tx, {
        workspaceId: ws, projectId: project, provider: 'x',
        externalId: 'x-1', displayName: '@example',
      });
      const [gsc] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from market_connections
         where workspace_id = ${ws} and provider = 'google_search_console'`);
      expect(gsc!.n).toBe(1);
    }, { db });
  });
});

/* --------------------------------------------------------------- keywords */

describe('keywords', () => {
  it('never lets a cheap source blank what a paid one supplied', async () => {
    await withWorkspace(ws, async (tx) => {
      await upsertKeywords(tx, {
        workspaceId: ws, projectId: project,
        keywords: [{
          keyword: 'Widget Reviews', volume: 8100, difficulty: 42, intent: 'commercial',
          monthly: [{ year: 2026, month: 1, volume: 7000 }],
        }],
      });

      // Autocomplete knows the phrase and nothing else. Overwriting with nulls
      // would silently throw away a call somebody paid for.
      await upsertKeywords(tx, {
        workspaceId: ws, projectId: project,
        keywords: [{ keyword: 'widget reviews', source: 'autocomplete' }],
      });

      const [row] = await tx.execute<{
        volume: number; difficulty: number; intent: string; monthly: unknown[]; keyword: string;
      }>(sql`
        select keyword, volume, difficulty, intent, monthly from seo_keywords
         where project_id = ${project} and keyword = 'widget reviews'`);
      expect(row).toMatchObject({ volume: 8100, difficulty: 42, intent: 'commercial' });
      expect(row!.monthly).toHaveLength(1);
      // Normalised on the way in, so the same phrase is one row.
      expect(row!.keyword).toBe('widget reviews');
    }, { db });
  });

  it('is one row per keyword per market', async () => {
    await withWorkspace(ws, async (tx) => {
      await upsertKeywords(tx, {
        workspaceId: ws, projectId: project,
        keywords: [
          { keyword: 'widgets', locationCode: 2840, volume: 1000 },
          { keyword: 'widgets', locationCode: 2276, volume: 300 },
        ],
      });
      const [n] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from seo_keywords
         where project_id = ${project} and keyword = 'widgets'`);
      // The same word is worth different amounts in different countries; one
      // row would make a keyword list wrong for everyone but one market.
      expect(n!.n).toBe(2);
    }, { db });
  });
});

/* ---------------------------------------------------------- rank tracking */

describe('rank tracking', () => {
  let configId = '';

  it('creates a tracker and relates it to the site', async () => {
    await withWorkspace(ws, async (tx) => {
      configId = await createRankConfig(tx, {
        workspaceId: ws, projectId: project, domain: 'https://www.Example.com/path',
      });
      const [row] = await tx.execute<{ domain: string; next_check_at: string }>(sql`
        select domain, next_check_at from rank_configs where id = ${configId}`);
      // Normalised to match `sites.host`, so one hostname is one thing across
      // every tool.
      expect(row!.domain).toBe('example.com');
      expect(row!.next_check_at, 'due immediately, so the first run is not a day away').not.toBeNull();
    }, { db });
  });

  it('returns the same tracker rather than making a second one', async () => {
    await withWorkspace(ws, async (tx) => {
      // Somebody adds example.com twice by accident. Two identical trackers
      // double the SERP calls and the bill, and show the same table twice.
      const again = await createRankConfig(tx, {
        workspaceId: ws, projectId: project, domain: 'EXAMPLE.com/',
      });
      expect(again).toBe(configId);

      // A different market is a different tracker, and legitimately so.
      const german = await createRankConfig(tx, {
        workspaceId: ws, projectId: project, domain: 'example.com', locationCode: 2276,
      });
      expect(german).not.toBe(configId);
    }, { db });
  });

  it('adds keywords as one batch against the allowance', async () => {
    await withWorkspace(ws, async (tx) => {
      const added = await trackKeywords(tx, {
        workspaceId: ws, configId,
        keywords: ['widget reviews', 'best widgets', ' best widgets ', 'widgets near me'],
      });
      // Deduplicated and trimmed before counting: a pasted list has repeats.
      expect(added).toBe(3);

      // Re-adding is a no-op rather than an error.
      expect(await trackKeywords(tx, { workspaceId: ws, configId, keywords: ['best widgets'] })).toBe(0);
    }, { db });
  });

  it('records positions and reports what moved', async () => {
    await withWorkspace(ws, async (tx) => {
      const keywords = await tx.execute<{ id: string; keyword: string }>(sql`
        select id, keyword from rank_keywords where config_id = ${configId} order by keyword`);
      expect(keywords, 'the three added above, alphabetically').toHaveLength(3);
      const [best, reviews, near] = keywords as unknown as [
        { id: string }, { id: string }, { id: string },
      ];

      await recordRankSnapshots(tx, {
        workspaceId: ws, configId, capturedOn: '2026-03-01',
        results: [
          { keywordId: best.id, device: 'desktop', position: 14 },
          { keywordId: reviews.id, device: 'desktop', position: 4 },
          { keywordId: near.id, device: 'desktop', position: null },
        ],
      });

      const moved = await recordRankSnapshots(tx, {
        workspaceId: ws, configId, capturedOn: '2026-03-08',
        results: [
          { keywordId: best.id, device: 'desktop', position: 8 },
          { keywordId: reviews.id, device: 'desktop', position: 4 },
          { keywordId: near.id, device: 'desktop', position: 22 },
        ],
      });

      // Only what actually changed.
      expect(moved.map((m) => m.to).sort((a, b) => (a ?? 0) - (b ?? 0))).toEqual([8, 22]);

      const [row] = await tx.execute<{ position: number; previous_position: number }>(sql`
        select position, previous_position from rank_snapshots
         where keyword_id = ${best.id} and captured_on = '2026-03-08'`);
      expect(row).toMatchObject({ position: 8, previous_position: 14 });
    }, { db });
  });

  it('stores “not ranking” as null, never as a sentinel', async () => {
    await withWorkspace(ws, async (tx) => {
      const [row] = await tx.execute<{ position: number | null }>(sql`
        select position from rank_snapshots
         where config_id = ${configId} and captured_on = '2026-03-01' and position is null`);
      // 101 as a stand-in is how averages quietly become nonsense.
      expect(row!.position).toBeNull();
    }, { db });
  });

  it('re-running a day corrects it rather than duplicating it', async () => {
    await withWorkspace(ws, async (tx) => {
      const [k] = await tx.execute<{ id: string }>(sql`
        select id from rank_keywords where config_id = ${configId} limit 1`);
      await recordRankSnapshots(tx, {
        workspaceId: ws, configId, capturedOn: '2026-03-08',
        results: [{ keywordId: k!.id, device: 'desktop', position: 6, url: 'https://example.com/a' }],
      });
      const rows = await tx.execute<{ position: number }>(sql`
        select position from rank_snapshots
         where keyword_id = ${k!.id} and captured_on = '2026-03-08' and device = 'desktop'`);
      expect(rows).toHaveLength(1);
      expect(rows[0]!.position).toBe(6);
    }, { db });
  });
});

describe('which moves are worth an event', () => {
  it('ignores daily wobble', () => {
    // An event for every ±1 trains people to ignore the alerts.
    expect(isNotableMove(14, 15)).toBe(false);
    expect(isNotableMove(4, 5)).toBe(false);
  });

  it('always reports crossing the top ten, however small the step', () => {
    // That is where the traffic is; 10→11 matters more than 40→50.
    expect(isNotableMove(10, 11)).toBe(true);
    expect(isNotableMove(11, 10)).toBe(true);
  });

  it('reports arriving and disappearing', () => {
    expect(isNotableMove(null, 18)).toBe(true);
    expect(isNotableMove(12, null)).toBe(true);
    // Arriving at 60 is not news.
    expect(isNotableMove(null, 60)).toBe(false);
    expect(isNotableMove(null, null)).toBe(false);
  });

  it('reports a real jump', () => {
    expect(isNotableMove(30, 22)).toBe(true);
    expect(isNotableMove(30, 28)).toBe(false);
  });
});

/* ---------------------------------------------------------- opportunities */

describe('opportunities', () => {
  const day = (offset: number) =>
    new Date(Date.UTC(2026, 2, 1) - offset * 86_400_000).toISOString().slice(0, 10);

  beforeAll(async () => {
    await withWorkspace(ws, async (tx) => {
      /*
       * Its own connection, not the one the connections suite made.
       *
       * Order-dependent suites pass locally and fail the moment somebody runs
       * a single test — which is exactly what happened: `-t` skipped the
       * connections block and every insert here went in with an empty uuid.
       */
      const ownConnection = await saveConnection(tx, {
        workspaceId: ws, projectId: project,
        provider: 'google_analytics', externalId: 'ga4:opportunities',
        displayName: 'Opportunities fixture',
      });

      const rows: string[] = [];
      // Recent window: a page-two query worth chasing, and a well-ranked page
      // nobody clicks.
      for (let d = 1; d <= 20; d++) {
        rows.push(`('${ws}','${project}','${ownConnection}','${day(d)}','page two widgets','https://example.com/two',null,'desktop',1,40,13.2)`);
        rows.push(`('${ws}','${project}','${ownConnection}','${day(d)}','ignored widgets','https://example.com/ignored',null,'desktop',2,300,3.1)`);
        rows.push(`('${ws}','${project}','${ownConnection}','${day(d)}','steady widgets','https://example.com/steady',null,'desktop',40,500,4.0)`);
      }
      // Older window: a page that used to earn far more.
      for (let d = 30; d <= 50; d++) {
        rows.push(`('${ws}','${project}','${ownConnection}','${day(d)}','faded widgets','https://example.com/faded',null,'desktop',30,400,3.0)`);
        rows.push(`('${ws}','${project}','${ownConnection}','${day(d)}','steady widgets','https://example.com/steady',null,'desktop',40,500,4.0)`);
      }
      await tx.execute(sql`
        insert into market_search_performance
          (workspace_id, project_id, connection_id, captured_on, query, page, country, device,
           clicks, impressions, position)
        values ${sql.raw(rows.join(','))}
        on conflict do nothing`);
    }, { db });
  });

  it('finds what the maths finds, and records the working', async () => {
    await withWorkspace(ws, async (tx) => {
      const result = await recomputeOpportunities(tx, {
        workspaceId: ws, projectId: project, today: new Date(Date.UTC(2026, 2, 1)), windowDays: 28,
      });
      expect(result.found).toBeGreaterThan(0);

      const rows = await tx.execute<{ kind: string; query: string | null; page: string | null; evidence: Record<string, unknown> }>(sql`
        select kind, query, page, evidence from seo_opportunities where project_id = ${project}`);
      const kinds = new Set(rows.map((r) => r.kind));

      expect(kinds.has('striking_distance'), 'a page-two query with real impressions').toBe(true);
      expect(kinds.has('low_ctr'), 'position 3 earning 0.7%').toBe(true);
      expect(kinds.has('content_decay'), 'a page that used to earn 8,400 clicks').toBe(true);

      const striking = rows.find((r) => r.kind === 'striking_distance');
      // The card has to show its working, or nobody trusts it.
      expect(striking!.evidence).toHaveProperty('position');
      expect(striking!.evidence).toHaveProperty('impressions');
    }, { db });
  });

  it('reports one row per job, not one per observation', async () => {
    await withWorkspace(ws, async (tx) => {
      const rows = await tx.execute<{ kind: string; query: string | null; page: string | null; evidence: Record<string, unknown> }>(sql`
        select kind, query, page, evidence from seo_opportunities where project_id = ${project}`);

      /*
       * The finders overlap deliberately — a query that appeared last month at
       * position 17 really is both "rising" and "page two" — but it is one job,
       * and listing it twice doubles the dismissals somebody has to click.
       */
      const targets = rows
        .filter((r) => r.query)
        .map((r) => `${r.query}\u0000${r.page}`);
      expect(new Set(targets).size, 'each query+page appears once').toBe(targets.length);
    }, { db });
  });

  it('does not resurrect something that was dismissed', async () => {
    await withWorkspace(ws, async (tx) => {
      const [one] = await tx.execute<{ id: string }>(sql`
        select id from seo_opportunities
         where project_id = ${project} and status = 'open'
         -- Ordered, so this test and the next do not race for the same row
         -- depending on how Postgres feels about returning them.
         order by kind, score desc limit 1`);
      await setOpportunityStatus(tx, { workspaceId: ws, id: one!.id, status: 'dismissed' });

      await recomputeOpportunities(tx, {
        workspaceId: ws, projectId: project, today: new Date(Date.UTC(2026, 2, 1)), windowDays: 28,
      });

      const [after] = await tx.execute<{ status: string }>(sql`
        select status from seo_opportunities where id = ${one!.id}`);
      // A rejected suggestion coming back tomorrow is the difference between a
      // useful list and a nag.
      expect(after!.status).toBe('dismissed');
    }, { db });
  });

  it('refreshes the score of one that is still open', async () => {
    await withWorkspace(ws, async (tx) => {
      const [before] = await tx.execute<{ id: string; score: number }>(sql`
        select id, score from seo_opportunities
         where project_id = ${project} and status = 'open'
         -- Any open row: which *kind* survives the merge is the merge's
         -- business, and pinning one here made this test fail whenever the
         -- previous one happened to dismiss it.
         order by kind desc, score desc limit 1`);
      await tx.execute(sql`update seo_opportunities set score = 0 where id = ${before!.id}`);

      await recomputeOpportunities(tx, {
        workspaceId: ws, projectId: project, today: new Date(Date.UTC(2026, 2, 1)), windowDays: 28,
      });
      const [after] = await tx.execute<{ score: number }>(sql`
        select score from seo_opportunities where id = ${before!.id}`);
      expect(after!.score).toBeGreaterThan(0);
    }, { db });
  });
});

/* --------------------------------------------------------------- manifest */

describe('domain normalisation', () => {
  it('produces the same string `sites.host` holds', () => {
    /*
     * One hostname has to be one thing across six tools, or a rank tracker and
     * an audit for the same site never join up. The rules are `sites.host`'s:
     * lowercase, no scheme, no `www.`, no path, no port.
     */
    for (const input of [
      'https://www.Example.com/path?q=1',
      'HTTP://example.com',
      'www.example.com',
      'example.com:8443',
      '  example.com  ',
    ]) {
      expect(normaliseDomain(input), input).toBe('example.com');
    }
  });

  it('leaves a subdomain alone — it is a different site', () => {
    expect(normaliseDomain('https://shop.example.com')).toBe('shop.example.com');
    // Only a leading `www.` is stripped; `www.blog` is somebody's subdomain.
    expect(normaliseDomain('www.blog.example.com')).toBe('blog.example.com');
  });
});

describe('the manifest', () => {
  it('names every event as <tool>.<noun>.<past-tense>', () => {
    // The envelope schema enforces three segments. A subscription to a name it
    // rejects looks correct and can never fire.
    for (const e of marketManifest.events) {
      expect(e.name, e.name).toMatch(/^market\.[a-z_]+\.[a-z_]+$/);
    }
    for (const s of marketManifest.subscriptions) {
      expect(s.event, s.event).toMatch(/^[a-z]+\.[a-z_]+\.[a-z_]+$/);
    }
  });

  it('meters every feature that spends somebody else’s money', () => {
    const metered = new Set(
      marketManifest.features.filter((f) => f.kind === 'metered').map((f) => f.key),
    );
    // §0.5's rule: a marginal cash cost is credits, our own CPU is quota.
    for (const key of ['market.dataforseo', 'market.rank_check', 'market.backlinks']) {
      expect(metered.has(key), key).toBe(true);
    }
    for (const f of marketManifest.features) {
      if (f.isAi) expect(f.kind, f.key).toBe('metered');
    }
  });

  it('keeps the free tier clear of anything that bills', () => {
    for (const f of marketManifest.features) {
      if (f.freeTierAllowed) expect(f.kind, f.key).not.toBe('metered');
    }
  });

  it('gives every resource type a route the palette can open', () => {
    for (const r of marketManifest.resources) {
      expect(r.href, r.type).toMatch(/^\/market\//);
    }
  });
});
