import {
  bigint,
  boolean,
  doublePrecision,
  index,
  integer,
  pgTable,
  text,
  timestamp,
  uniqueIndex,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { emptyJsonArray, json } from './_shared.ts';

/**
 * The event fact table — one shape for pageviews, clicks, scans, impressions
 * and conversions.
 *
 * This is the Postgres form of the ClickHouse table in the plan. It is NOT
 * tenant-RLS'd the same way as the entity tables: it is written by the ingest
 * worker under platform scope and read through `packages/events`, which always
 * filters by workspace. The isolation test exempts it explicitly.
 *
 * Deliberately no foreign keys: this table is append-only and hot, and a FK
 * check per row on a write path that batches hundreds at a time is the wrong
 * trade.
 */
export const eventsRaw = pgTable(
  'events_raw',
  {
    workspaceId: uuid().notNull(),
    projectId: uuid().notNull(),
    kind: varchar({ length: 24 }).notNull(),
    tool: varchar({ length: 32 }).notNull(),

    subjectId: uuid().notNull(),
    subjectType: varchar({ length: 32 }).notNull(),

    ts: timestamp({ withTimezone: true }).notNull().defaultNow(),
    eventId: uuid().notNull(),

    visitorId: varchar({ length: 32 }),
    sessionId: uuid(),
    /** The cross-tool attribution join key. */
    clickId: uuid(),

    isUnique: boolean().notNull().default(false),
    isBot: boolean().notNull().default(false),

    url: text(),
    path: text(),
    host: varchar({ length: 253 }),
    referrerHost: varchar({ length: 253 }),
    referrerUrl: text(),
    utm: json<Record<string, string>>().notNull().default({}),

    country: varchar({ length: 2 }),
    region: varchar({ length: 64 }),
    city: varchar({ length: 128 }),
    browser: varchar({ length: 64 }),
    os: varchar({ length: 64 }),
    device: varchar({ length: 32 }),
    language: varchar({ length: 16 }),
    screen: varchar({ length: 16 }),

    name: varchar({ length: 128 }),
    value: doublePrecision().notNull().default(0),
    statusCode: integer(),
    durationMs: integer(),

    props: json<Record<string, string>>().notNull().default({}),
    /** jsonb rather than text[]: containment still works with @>, and the
     *  driver has no empty-array edge case on the hot insert path. */
    relatedUrns: json<string[]>().notNull().default(emptyJsonArray),
  },
  (t) => [
    // The edge can redeliver a batch; this is the dedupe barrier.
    uniqueIndex('events_raw_event_id_key').on(t.eventId),
    // The read path every dashboard takes.
    index('events_raw_subject_idx').on(t.workspaceId, t.subjectId, t.kind, t.ts),
    index('events_raw_workspace_ts_idx').on(t.workspaceId, t.ts),
    // Attribution.
    index('events_raw_click_idx').on(t.workspaceId, t.clickId),
  ],
);

/** Daily rollup — dashboards read this for anything older than 48 hours. */
export const eventsDaily = pgTable(
  'events_daily',
  {
    workspaceId: uuid().notNull(),
    subjectId: uuid().notNull(),
    kind: varchar({ length: 24 }).notNull(),
    date: timestamp({ withTimezone: true }).notNull(),
    dimension: varchar({ length: 32 }).notNull(),
    dimensionValue: varchar({ length: 255 }).notNull(),
    count: bigint({ mode: 'number' }).notNull().default(0),
    uniques: bigint({ mode: 'number' }).notNull().default(0),
    value: doublePrecision().notNull().default(0),
  },
  (t) => [
    uniqueIndex('events_daily_pk').on(
      t.workspaceId, t.subjectId, t.kind, t.date, t.dimension, t.dimensionValue,
    ),
    index('events_daily_read_idx').on(t.workspaceId, t.kind, t.date),
  ],
);
