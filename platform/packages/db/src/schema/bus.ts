import {
  index,
  integer,
  pgTable,
  text,
  timestamp,
  unique,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { json, primaryId } from './_shared.ts';
import { workspaces } from './tenancy.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });

/**
 * Transactional outbox. The producer writes the domain row AND this row in one
 * transaction, which gives exactly-once PRODUCTION. A leader-elected relay
 * XADDs to the Redis Stream, which gives at-least-once DELIVERY.
 */
export const eventOutbox = pgTable(
  'event_outbox',
  {
    id: primaryId(),
    workspaceId: ws(),
    name: varchar({ length: 96 }).notNull(),
    envelope: json().notNull(),
    status: varchar({ length: 16 }).notNull().default('pending'),
    attempts: integer().notNull().default(0),
    publishedAt: timestamp({ withTimezone: true }),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [
    // Partial index on pending only — this table is append-mostly and the
    // relay only ever scans the unpublished tail.
    index('event_outbox_pending_idx').on(t.status, t.createdAt),
    index('event_outbox_workspace_idx').on(t.workspaceId, t.createdAt),
  ],
);

/**
 * The idempotency barrier: one row per (handler, event).
 * INSERT ... ON CONFLICT DO NOTHING; 0 rows + status='done' means skip.
 * At-least-once delivery + this table = effectively-once per handler.
 */
export const busDeliveries = pgTable(
  'bus_deliveries',
  {
    handlerKey: varchar({ length: 128 }).notNull(),
    eventId: uuid().notNull(),
    status: varchar({ length: 16 }).notNull().default('pending'),
    attempts: integer().notNull().default(0),
    firstSeenAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    completedAt: timestamp({ withTimezone: true }),
    error: text(),
  },
  (t) => [
    unique('bus_deliveries_pk').on(t.handlerKey, t.eventId),
    index('bus_deliveries_status_idx').on(t.status, t.firstSeenAt),
  ],
);

export const busDeadLetters = pgTable(
  'bus_dead_letters',
  {
    id: primaryId(),
    workspaceId: uuid(),
    handlerKey: varchar({ length: 128 }).notNull(),
    eventId: uuid().notNull(),
    envelope: json().notNull(),
    error: text(),
    attempts: integer().notNull().default(0),
    replayedAt: timestamp({ withTimezone: true }),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('bus_dead_letters_handler_idx').on(t.handlerKey, t.createdAt)],
);

export const jobDeadLetters = pgTable(
  'job_dead_letters',
  {
    id: primaryId(),
    workspaceId: uuid(),
    queue: varchar({ length: 64 }).notNull(),
    jobName: varchar({ length: 96 }).notNull(),
    payload: json().notNull(),
    error: text(),
    attempts: integer().notNull().default(0),
    replayedAt: timestamp({ withTimezone: true }),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('job_dead_letters_queue_idx').on(t.queue, t.createdAt)],
);
