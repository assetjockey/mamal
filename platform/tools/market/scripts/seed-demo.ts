/**
 * Fills a workspace with Market data worth looking at.
 *
 *     pnpm --filter @mamal/tool-market seed-demo <workspaceId>
 *
 * The Search Console rows are shaped so every finder has something to find:
 * a page-two query, a well-ranked page nobody clicks, a page that used to earn
 * more, two pages competing, and demand that arrived last month. Otherwise the
 * opportunities screen is an empty state and the maths is invisible.
 */
import { sql } from 'drizzle-orm';
import { closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  createRankConfig, recomputeOpportunities, recordRankSnapshots,
  saveConnection, trackKeywords, upsertKeywords,
} from '@mamal/tool-market';

const workspaceId = process.argv[2];
if (!workspaceId) throw new Error('usage: seed-demo <workspaceId>');

const db = unsafeUnscopedDb();

await withWorkspace(workspaceId, async (tx) => {
  const [p] = await tx.execute<{ id: string }>(sql`
    select id from projects where workspace_id = ${workspaceId} order by is_default desc limit 1`);
  const projectId = p!.id;

  const connectionId = await saveConnection(tx, {
    workspaceId, projectId,
    provider: 'google_search_console',
    externalId: 'sc-domain:example.com',
    displayName: 'example.com',
    credentialsEncrypted: 'demo',
  });
  await tx.execute(sql`
    update market_connections set last_synced_at = now() - interval '2 hours'
     where id = ${connectionId}`);

  /* ------------------------------------------------------------ keywords */

  await upsertKeywords(tx, {
    workspaceId, projectId,
    keywords: [
      { keyword: 'widget reviews', volume: 8100, difficulty: 42, intent: 'commercial', cpcMicros: 1_850_000 },
      { keyword: 'best widgets 2026', volume: 5400, difficulty: 51, intent: 'commercial', cpcMicros: 2_300_000 },
      { keyword: 'how to choose a widget', volume: 2900, difficulty: 24, intent: 'informational', cpcMicros: 640_000 },
      { keyword: 'widget pricing', volume: 1600, difficulty: 38, intent: 'transactional', cpcMicros: 3_100_000 },
      { keyword: 'widgets near me', volume: 12_100, difficulty: 33, intent: 'navigational', cpcMicros: 900_000 },
      { keyword: 'widget alternatives', source: 'manual' },
      { keyword: 'widget vs gadget', source: 'manual' },
    ],
  });

  /* -------------------------------------------------------- rank tracker */

  const configId = await createRankConfig(tx, {
    workspaceId, projectId, domain: 'example.com', schedule: 'weekly',
  });
  await trackKeywords(tx, {
    workspaceId, configId,
    keywords: ['widget reviews', 'best widgets 2026', 'widget pricing', 'widgets near me'],
  });

  const tracked = await tx.execute<{ id: string; keyword: string }>(sql`
    select id, keyword from rank_keywords where config_id = ${configId} order by keyword`);

  // Two weeks, so the movement column has something to say.
  const positions: Record<string, [number | null, number | null]> = {
    'best widgets 2026': [14, 9],
    'widget pricing': [4, 4],
    'widget reviews': [8, 12],
    'widgets near me': [null, 27],
  };
  const day = (offset: number) =>
    new Date(Date.now() - offset * 86_400_000).toISOString().slice(0, 10);

  for (const [index, when] of [day(8), day(1)].entries()) {
    await recordRankSnapshots(tx, {
      workspaceId, configId, capturedOn: when,
      results: tracked.map((k) => ({
        keywordId: k.id,
        device: 'desktop',
        position: positions[k.keyword]?.[index] ?? null,
        url: `https://example.com/${k.keyword.replace(/\s+/g, '-')}`,
      })),
    });
  }
  await tx.execute(sql`
    update rank_configs set last_run_at = now() - interval '1 day' where id = ${configId}`);

  /* -------------------------------------------- search console performance */

  type Row = [query: string, page: string, clicks: number, impressions: number, position: number];

  /** The recent window: what the finders should notice now. */
  const recent: Row[] = [
    // Page two, worth chasing.
    ['best widgets 2026', 'https://example.com/best-widgets', 2, 90, 12.4],
    ['widget comparison', 'https://example.com/compare', 1, 60, 14.8],
    // Ranked well, nobody clicks — a title problem.
    ['widget pricing', 'https://example.com/pricing', 3, 420, 3.2],
    // Two pages competing for one query.
    ['widget reviews', 'https://example.com/reviews', 40, 700, 6.1],
    ['widget reviews', 'https://example.com/blog/reviews-2025', 6, 380, 9.4],
    // Demand that arrived.
    ['widget api', 'https://example.com/docs/api', 9, 260, 17.5],
    // Steady, so the finders have something to leave alone.
    ['widgets near me', 'https://example.com/locations', 90, 1100, 4.0],
    // The page that faded — still present, earning far less.
    ['how to choose a widget', 'https://example.com/guide', 12, 900, 8.8],
  ];

  /** The earlier window: what it used to look like. */
  const earlier: Row[] = [
    ['how to choose a widget', 'https://example.com/guide', 95, 1200, 3.4],
    ['widgets near me', 'https://example.com/locations', 88, 1080, 4.1],
    ['widget reviews', 'https://example.com/reviews', 38, 690, 6.0],
    ['widget pricing', 'https://example.com/pricing', 4, 400, 3.3],
  ];

  const values: string[] = [];
  const push = (rows: Row[], offsets: number[]) => {
    for (const offset of offsets) {
      for (const [query, page, clicks, impressions, position] of rows) {
        values.push(
          `('${workspaceId}','${projectId}','${connectionId}','${day(offset)}',` +
          `'${query.replace(/'/g, "''")}','${page}',null,'desktop',${clicks},${impressions},${position})`,
        );
      }
    }
  };
  push(recent, range(1, 27));
  push(earlier, range(29, 55));

  // One statement: a month of rows for two windows is thousands, and a round
  // trip each would make seeding slower than the thing it is seeding.
  for (let i = 0; i < values.length; i += 500) {
    await tx.execute(sql`
      insert into market_search_performance
        (workspace_id, project_id, connection_id, captured_on, query, page, country, device,
         clicks, impressions, position)
      values ${sql.raw(values.slice(i, i + 500).join(','))}
      on conflict do nothing`);
  }

  const found = await recomputeOpportunities(tx, { workspaceId, projectId });

  console.log(JSON.stringify({
    workspaceId,
    searchPerformanceRows: values.length,
    opportunities: found,
    overview: 'http://localhost:3000/market',
  }));
}, { db });

await closeDb();

function range(from: number, to: number): number[] {
  return Array.from({ length: to - from + 1 }, (_, i) => from + i);
}
