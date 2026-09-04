import { sql } from 'drizzle-orm';
import type { Database } from '@mamal/db';
import {
  eventSchema,
  type AggregateBucket,
  type Aggregation,
  type EventInput,
  type EventRow,
  type EventStore,
} from '../schema.ts';

/**
 * The Postgres adapter.
 *
 * Deliberately the phase 0–1 default: one database to run, one to back up, and
 * the same interface the ClickHouse adapter implements. `events_raw` is
 * partition-friendly and indexed for the reads dashboards actually make, which
 * is enough well past the point where the free tier stops being free.
 */
export class PostgresEventStore implements EventStore {
  readonly name = 'postgres';

  constructor(private readonly db: Database) {}

  async insert(rows: EventInput[]): Promise<number> {
    if (rows.length === 0) return 0;
    const parsed = rows.map((r) => eventSchema.parse(r));

    // One multi-row insert; the edge batches before it reaches us.
    const values = parsed.map(
      (r) => sql`(
        ${r.workspaceId}, ${r.projectId}, ${r.kind}, ${r.tool},
        ${r.subjectId}, ${r.subjectType}, ${r.ts.toISOString()}::timestamptz, ${r.eventId},
        ${r.visitorId ?? null}, ${r.sessionId ?? null}, ${r.clickId ?? null},
        ${r.isUnique}, ${r.isBot},
        ${r.url ?? null}, ${r.path ?? null}, ${r.host ?? null},
        ${r.referrerHost ?? null}, ${r.referrerUrl ?? null}, ${JSON.stringify(r.utm)}::jsonb,
        ${r.country ?? null}, ${r.region ?? null}, ${r.city ?? null},
        ${r.browser ?? null}, ${r.os ?? null}, ${r.device ?? null},
        ${r.language ?? null}, ${r.screen ?? null},
        ${r.name ?? null}, ${r.value}, ${r.statusCode ?? null}, ${r.durationMs ?? null},
        ${JSON.stringify(r.props)}::jsonb, ${JSON.stringify(r.relatedUrns)}::jsonb
      )`,
    );

    // `returning` makes the count honest: it reports rows actually written,
    // so a redelivered batch reports 0 rather than claiming success.
    const written = await this.db.execute<{ event_id: string }>(sql`
      insert into events_raw (
        workspace_id, project_id, kind, tool, subject_id, subject_type, ts, event_id,
        visitor_id, session_id, click_id, is_unique, is_bot,
        url, path, host, referrer_host, referrer_url, utm,
        country, region, city, browser, os, device, language, screen,
        name, value, status_code, duration_ms, props, related_urns
      ) values ${sql.join(values, sql`, `)}
      -- The edge can redeliver a batch; the event id is the dedupe key.
      on conflict (event_id) do nothing
      returning event_id`);

    return written.length;
  }

  async aggregate(query: Aggregation): Promise<AggregateBucket[]> {
    const kinds = query.kind ? (Array.isArray(query.kind) ? query.kind : [query.kind]) : null;
    const dimension = query.dimension ? toColumn(query.dimension) : null;

    const rows = await this.db.execute<{ key: string; count: number; uniques: number; value: number }>(sql`
      select
        ${dimension ? sql.raw(`coalesce(${dimension}::text, '(none)')`) : sql`'total'`} as key,
        count(*)::int as count,
        count(distinct visitor_id)::int as uniques,
        coalesce(sum(value), 0)::float as value
      from events_raw
      where workspace_id = ${query.workspaceId}
        and ts >= ${query.range.from.toISOString()}::timestamptz
        and ts < ${query.range.to.toISOString()}::timestamptz
        and not is_bot
        ${kinds ? sql`and kind = any(${kinds})` : sql``}
        ${query.subjectId ? sql`and subject_id = ${query.subjectId}` : sql``}
      ${dimension ? sql`group by 1 order by 2 desc` : sql``}
      limit ${query.limit ?? 100}`);

    return rows.map((r) => ({
      key: r.key,
      count: Number(r.count),
      uniques: Number(r.uniques),
      value: Number(r.value),
    }));
  }

  async count(query: Omit<Aggregation, 'dimension' | 'limit'>) {
    const [row] = await this.aggregate({ ...query, limit: 1 });
    return { total: row?.count ?? 0, uniques: row?.uniques ?? 0 };
  }

  /**
   * The attribution join. One click_id ties a Link click to the Track pageview
   * it caused and the conversion that followed — which is what makes "Link
   * feeds Track" true rather than aspirational.
   */
  async journey(workspaceId: string, clickId: string): Promise<EventRow[]> {
    const rows = await this.db.execute<Record<string, unknown>>(sql`
      select * from events_raw
       where workspace_id = ${workspaceId} and click_id = ${clickId}
       order by ts asc`);
    return rows.map(fromRow);
  }

  async prune(workspaceId: string, olderThan: Date): Promise<number> {
    const rows = await this.db.execute(sql`
      delete from events_raw
       where workspace_id = ${workspaceId} and ts < ${olderThan.toISOString()}::timestamptz
      returning event_id`);
    return Array.isArray(rows) ? rows.length : 0;
  }
}

/** Only the column names the aggregator will accept — no interpolation of user input. */
const DIMENSIONS: Record<string, string> = {
  kind: 'kind', tool: 'tool', path: 'path', host: 'host',
  referrerHost: 'referrer_host', country: 'country', region: 'region', city: 'city',
  browser: 'browser', os: 'os', device: 'device', language: 'language', screen: 'screen',
  name: 'name', subjectType: 'subject_type', subjectId: 'subject_id::text',
};

function toColumn(dimension: string): string {
  const column = DIMENSIONS[dimension];
  if (!column) throw new Error(`"${dimension}" is not an aggregatable dimension`);
  return column;
}

function fromRow(r: Record<string, unknown>): EventRow {
  return eventSchema.parse({
    workspaceId: r.workspace_id, projectId: r.project_id, kind: r.kind, tool: r.tool,
    subjectId: r.subject_id, subjectType: r.subject_type, ts: r.ts instanceof Date ? r.ts : new Date(r.ts as string),
    eventId: r.event_id, visitorId: r.visitor_id ?? undefined,
    sessionId: r.session_id ?? undefined, clickId: r.click_id ?? undefined,
    isUnique: r.is_unique, isBot: r.is_bot,
    url: r.url ?? undefined, path: r.path ?? undefined, host: r.host ?? undefined,
    referrerHost: r.referrer_host ?? undefined, referrerUrl: r.referrer_url ?? undefined,
    utm: r.utm ?? {}, country: r.country ?? undefined, region: r.region ?? undefined,
    city: r.city ?? undefined, browser: r.browser ?? undefined, os: r.os ?? undefined,
    device: r.device ?? undefined, language: r.language ?? undefined,
    screen: r.screen ?? undefined, name: r.name ?? undefined,
    value: Number(r.value ?? 0), statusCode: r.status_code ?? undefined,
    durationMs: r.duration_ms ?? undefined, props: r.props ?? {},
    relatedUrns: r.related_urns ?? [],
  });
}
