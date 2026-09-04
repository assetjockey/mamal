import { z } from 'zod';

/**
 * ONE fact table for five logical streams.
 *
 * Every source product's stats table is the same ~12 columns plus a
 * discriminator; phpanalytics needed 13 composite indexes to serve them because
 * MySQL cannot do this. A columnar sort key does it with one structure.
 */
export const STREAMS = [
  'pageview', 'event', 'click', 'scan', 'impression', 'conversion', 'download', 'outbound',
] as const;
export type Stream = (typeof STREAMS)[number];

export const eventSchema = z.object({
  workspaceId: z.uuid(),
  projectId: z.uuid(),
  kind: z.enum(STREAMS),
  tool: z.string().max(32),

  /** The link, site, campaign or QR this is about. */
  subjectId: z.uuid(),
  subjectType: z.string().max(32),

  ts: z.date().default(() => new Date()),
  eventId: z.uuid(),

  /** Rotating daily salt; never an IP. */
  visitorId: z.string().max(32).optional(),
  sessionId: z.uuid().optional(),

  /**
   * THE cross-tool attribution key.
   *
   * The redirect worker mints it, stamps ?_mm= on the destination, and Track's
   * pixel reads it onto the resulting pageview and the conversion that follows.
   * So a Link click, the Track session it caused and the Market campaign that
   * paid for it join with GROUP BY click_id — no ETL, no cross-tool queries.
   */
  clickId: z.uuid().optional(),

  isUnique: z.boolean().default(false),
  isBot: z.boolean().default(false),

  url: z.string().optional(),
  path: z.string().optional(),
  host: z.string().optional(),
  referrerHost: z.string().optional(),
  referrerUrl: z.string().optional(),
  utm: z.record(z.string(), z.string()).default({}),

  country: z.string().length(2).optional(),
  region: z.string().optional(),
  city: z.string().optional(),
  browser: z.string().optional(),
  os: z.string().optional(),
  device: z.string().optional(),
  language: z.string().optional(),
  screen: z.string().optional(),

  name: z.string().optional(),
  value: z.number().default(0),
  statusCode: z.number().int().optional(),
  durationMs: z.number().int().optional(),

  /** New dimensions land here first; promoted to a column when 2+ tools query it. */
  props: z.record(z.string(), z.string()).default({}),
  relatedUrns: z.array(z.string()).default([]),
});

export type EventRow = z.infer<typeof eventSchema>;
export type EventInput = z.input<typeof eventSchema>;

export type TimeRange = { from: Date; to: Date };

export type Aggregation = {
  workspaceId: string;
  kind?: Stream | Stream[];
  subjectId?: string;
  range: TimeRange;
  /** Group by one dimension; undefined returns a single total. */
  dimension?: keyof EventRow;
  limit?: number;
};

export type AggregateBucket = {
  key: string;
  count: number;
  uniques: number;
  value: number;
};

/**
 * The storage contract.
 *
 * Phase 0–1 run the Postgres adapter so the platform needs one database; the
 * ClickHouse adapter is a drop-in once volume justifies it. Phasing the
 * INFRASTRUCTURE is safe. Phasing the interface would not be.
 */
export interface EventStore {
  readonly name: string;
  insert(rows: EventInput[]): Promise<number>;
  aggregate(query: Aggregation): Promise<AggregateBucket[]>;
  count(query: Omit<Aggregation, 'dimension' | 'limit'>): Promise<{ total: number; uniques: number }>;
  /** Cross-tool attribution: everything sharing one click_id, in order. */
  journey(workspaceId: string, clickId: string): Promise<EventRow[]>;
  /** Retention is an entitlement; this is the enforcement. */
  prune(workspaceId: string, olderThan: Date): Promise<number>;
}
