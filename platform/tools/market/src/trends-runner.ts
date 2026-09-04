/**
 * Trend watches, against the database.
 *
 * The diffing is pure and lives in `trends.ts`; this claims due watches, asks
 * for readings, stores the events and persists the new baseline.
 *
 * **Where readings come from is injected.** The plan keeps `services/trends` as
 * a Python sidecar because there is no TypeScript equivalent of pytrends, with
 * DataForSEO as the paid fallback. Passing the source in means the interesting
 * behaviour — thresholds, baselines, per-region isolation — is testable without
 * either, and means an operator running neither still gets a working watch list
 * rather than a screen that throws.
 *
 * **The baseline and the events are written together.** They are one fact: a
 * stored event says "this moved from X" and the baseline is what makes the next
 * comparison correct. A crash between them would either alert twice on the same
 * move or lose it.
 */
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { diffTrends, type Reading, type Shift, type Snapshot } from './trends.ts';

export type TrendSource = (request: {
  keywords: string[];
  geos: string[];
  timeframe: string;
}) => Promise<Reading[]>;

export type WatchRow = {
  id: string;
  workspaceId: string;
  projectId: string;
  name: string;
  keywords: string[];
  geos: string[];
  timeframe: string;
  thresholdPct: number;
  snapshot: Snapshot;
};

export type WatchOutcome = {
  watchId: string;
  readings: number;
  shifts: Shift[];
  baselined: number;
  error: string | null;
};

export async function runWatch(
  tx: WorkspaceScopedDb,
  watch: WatchRow,
  source: TrendSource,
  opts: { now?: Date } = {},
): Promise<WatchOutcome> {
  const now = opts.now ?? new Date();

  let readings: Reading[];
  try {
    readings = await source({
      keywords: watch.keywords,
      geos: watch.geos,
      timeframe: watch.timeframe,
    });
  } catch (err) {
    /*
     * A source that is down is not a broken watch. The baseline is untouched,
     * so when the sidecar comes back the comparison is still against the right
     * number rather than against a gap.
     */
    const message = err instanceof Error ? err.message : String(err);
    await tx.execute(sql`
      update trend_watches set last_run_at = now(), updated_at = now() where id = ${watch.id}`);
    return { watchId: watch.id, readings: 0, shifts: [], baselined: 0, error: message };
  }

  const { shifts, snapshot, baselined } = diffTrends(watch.snapshot, readings, {
    thresholdPct: watch.thresholdPct,
    now,
  });

  for (const shift of shifts) {
    await tx.execute(sql`
      insert into trend_events
        (workspace_id, watch_id, keyword, geo, previous_value, current_value, delta_pct)
      values (${watch.workspaceId}, ${watch.id}, ${shift.keyword}, ${shift.geo},
              ${shift.previous}, ${shift.current}, ${shift.deltaPct})`);
  }

  // Written in the same transaction as the events above — see the header.
  await tx.execute(sql`
    update trend_watches
       set snapshot = ${JSON.stringify(snapshot)}::jsonb,
           last_run_at = now(),
           updated_at = now()
     where id = ${watch.id}`);

  return {
    watchId: watch.id,
    readings: readings.length,
    shifts,
    baselined: baselined.length,
    error: null,
  };
}

/**
 * Due watches, claimed.
 *
 * Trend readings are free from the sidecar and metered from DataForSEO, so the
 * claim matters for the same reason as everywhere else: two schedulers must not
 * both pay.
 */
export async function claimDueWatches(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<WatchRow[]> {
  const rows = await tx.execute<{
    id: string; workspace_id: string; project_id: string; name: string;
    keywords: string[]; geos: string[]; timeframe: string;
    threshold_pct: number; snapshot: Snapshot; interval_minutes: number;
  }>(sql`
    with claimed as (
      select id, interval_minutes from trend_watches
       where is_active and (next_run_at is null or next_run_at <= now())
       order by next_run_at nulls first
       limit ${opts.limit ?? 50}
       for update skip locked
    )
    update trend_watches w
       set next_run_at = now() + (claimed.interval_minutes * interval '1 minute'),
           updated_at = now()
      from claimed
     where w.id = claimed.id
    returning w.id, w.workspace_id, w.project_id, w.name, w.keywords, w.geos,
              w.timeframe, w.threshold_pct, w.snapshot, w.interval_minutes`);

  return rows.map((r) => ({
    id: r.id,
    workspaceId: r.workspace_id,
    projectId: r.project_id,
    name: r.name,
    keywords: r.keywords,
    geos: r.geos,
    timeframe: r.timeframe,
    thresholdPct: r.threshold_pct,
    snapshot: r.snapshot ?? {},
  }));
}

export async function saveWatch(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    id?: string;
    name: string;
    keywords: string[];
    geos?: string[];
    timeframe?: string;
    thresholdPct?: number;
    intervalMinutes?: number;
  },
): Promise<string> {
  const keywords = [...new Set(opts.keywords.map((k) => k.trim()).filter(Boolean))];
  // Empty string is "worldwide", which is a real region rather than a missing
  // one — see `snapshotKey`.
  const geos = opts.geos?.length ? opts.geos.map((g) => g.trim()) : [''];

  if (opts.id) {
    await tx.execute(sql`
      update trend_watches
         set name = ${opts.name},
             keywords = ${textArray(keywords)}::text[],
             geos = ${textArray(geos)}::text[],
             timeframe = ${opts.timeframe ?? 'today 3-m'},
             threshold_pct = ${opts.thresholdPct ?? 25},
             interval_minutes = ${opts.intervalMinutes ?? 1440},
             updated_at = now()
       where id = ${opts.id} and project_id = ${opts.projectId}`);
    return opts.id;
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into trend_watches
      (workspace_id, project_id, name, keywords, geos, timeframe, threshold_pct, interval_minutes)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.name},
            ${textArray(keywords)}::text[], ${textArray(geos)}::text[],
            ${opts.timeframe ?? 'today 3-m'}, ${opts.thresholdPct ?? 25},
            ${opts.intervalMinutes ?? 1440})
    returning id`);
  return row!.id;
}

export async function listWatches(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<{
  id: string; name: string; keywords: string[]; geos: string[]; thresholdPct: number;
  lastRunAt: string | null; recent: { keyword: string; geo: string; deltaPct: number; at: string }[];
}[]> {
  const rows = await tx.execute<{
    id: string; name: string; keywords: string[]; geos: string[];
    threshold_pct: number; last_run_at: string | null;
  }>(sql`
    select id, name, keywords, geos, threshold_pct, last_run_at::text
      from trend_watches where project_id = ${opts.projectId} and is_active
     order by created_at`);

  if (rows.length === 0) return [];

  const events = await tx.execute<{
    watch_id: string; keyword: string; geo: string; delta_pct: number | null; created_at: string;
  }>(sql`
    select watch_id, keyword, geo, delta_pct, created_at::text
      from trend_events
     where watch_id = any(${textArray(rows.map((r) => r.id))}::uuid[])
       and created_at > now() - interval '30 days'
     order by created_at desc
     limit 200`);

  return rows.map((r) => ({
    id: r.id,
    name: r.name,
    keywords: r.keywords,
    geos: r.geos,
    thresholdPct: r.threshold_pct,
    lastRunAt: r.last_run_at,
    recent: events
      .filter((e) => e.watch_id === r.id)
      .slice(0, 5)
      .map((e) => ({
        keyword: e.keyword,
        geo: e.geo,
        deltaPct: e.delta_pct ?? 0,
        at: e.created_at,
      })),
  }));
}
