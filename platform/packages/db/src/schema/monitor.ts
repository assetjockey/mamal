import {
  bigint,
  boolean,
  index,
  integer,
  pgTable,
  real,
  smallint,
  text,
  timestamp,
  unique,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { emptyJsonArray, json, primaryId, softDelete, timestamps } from './_shared.ts';
import { projects, users, workspaces } from './tenancy.ts';
import { customDomains } from './core.ts';
import { resources } from './resources.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });
const pr = () =>
  uuid()
    .notNull()
    .references(() => projects.id, { onDelete: 'cascade' });

/**
 * Monitor: uptime, incidents and status pages.
 *
 * The central decision is one polymorphic `monitors` table rather than
 * `66uptime`'s six parallel ones. Six tables is why that product needs six
 * admin screens, six bulk importers, six alerting paths and six copies of the
 * same "is it down" logic — and why its status pages can only show one kind.
 * A discriminated `kind` with a typed `config` collapses all of that.
 */

/**
 * One monitor, whatever it watches.
 *
 * `nextCheckAt` is the sweep column, and it is what makes 100,000 monitors
 * possible: the scheduler claims due rows with `for update skip locked` rather
 * than holding a repeatable job per monitor, which at this scale is Redis-memory
 * suicide. `66uptime` got this right and it is kept.
 */
export const monitors = pgTable(
  'monitors',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /** The URN this watches, when it came from another tool's finding. */
    resourceId: uuid().references(() => resources.id, { onDelete: 'set null' }),
    /**
     * http | ping | port | dns | domain | ssl | heartbeat | server | game | browser
     *
     * One table, ten kinds. The config is typed per kind in
     * `tools/monitor/src/kinds.ts`, which is the only place that knows the
     * difference.
     */
    kind: varchar({ length: 12 }).notNull(),
    name: text().notNull(),
    /** A URL, a hostname, a host:port — whatever the kind checks. */
    target: text().notNull(),
    config: json().notNull().default({}),
    /** Which regions probe this. Empty means "any one region". */
    regions: text().array().notNull().default([]),
    intervalSeconds: integer().notNull().default(300),
    timeoutSeconds: smallint().notNull().default(15),
    isEnabled: boolean().notNull().default(true),
    /** up | down | degraded | paused | maintenance | pending */
    status: varchar({ length: 12 }).notNull().default('pending'),
    /** Rolling, recomputed from checks — see `uptimePercent`. */
    uptimePct: real(),
    avgResponseMs: integer(),
    checksTotal: bigint({ mode: 'number' }).notNull().default(0),
    checksFailed: bigint({ mode: 'number' }).notNull().default(0),
    lastCheckAt: timestamp({ withTimezone: true }),
    nextCheckAt: timestamp({ withTimezone: true }),
    currentIncidentId: uuid(),
    /** Where alerts go. Empty means the project's defaults. */
    channelIds: uuid().array().notNull().default([]),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    index('monitors_due_idx').on(t.isEnabled, t.nextCheckAt),
    index('monitors_project_idx').on(t.projectId, t.status),
  ],
);

/**
 * One probe result, from one region.
 *
 * Kept relational rather than in ClickHouse *for now* — the plan phases the
 * event store behind `packages/clickhouse`, and this table is the Postgres side
 * of that interface. Rows are aged out by retention rather than kept forever.
 *
 * There is deliberately **no retry**: a failed probe *is* the signal, and
 * retrying one corrupts the uptime maths by turning a real 30 seconds of
 * downtime into no downtime at all.
 */
export const monitorChecks = pgTable(
  'monitor_checks',
  {
    id: primaryId(),
    workspaceId: ws(),
    monitorId: uuid()
      .notNull()
      .references(() => monitors.id, { onDelete: 'cascade' }),
    region: varchar({ length: 16 }).notNull().default('default'),
    ok: boolean().notNull(),
    responseMs: integer(),
    statusCode: smallint(),
    /** Which condition failed — `timeout`, `keyword_missing`, `cert_expired`… */
    failureKind: varchar({ length: 24 }),
    error: text(),
    checkedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('monitor_checks_idx').on(t.monitorId, t.checkedAt)],
);

/**
 * An incident: one continuous period of a monitor being down.
 *
 * Opened only on M-of-N regional agreement, which is the single most valuable
 * behaviour in the tool — a false positive from one flaky probe is the number
 * one support ticket in every uptime product.
 */
export const incidents = pgTable(
  'incidents',
  {
    id: primaryId(),
    workspaceId: ws(),
    monitorId: uuid()
      .notNull()
      .references(() => monitors.id, { onDelete: 'cascade' }),
    /** What actually failed, from the check that opened it. */
    cause: text(),
    failureKind: varchar({ length: 24 }),
    /** minor | major | critical */
    severity: varchar({ length: 12 }).notNull().default('major'),
    startedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    /** Acknowledging mutes escalation without resolving. */
    acknowledgedAt: timestamp({ withTimezone: true }),
    acknowledgedBy: uuid().references(() => users.id, { onDelete: 'set null' }),
    resolvedAt: timestamp({ withTimezone: true }),
    durationSeconds: integer(),
    failedChecks: integer().notNull().default(0),
    /** How far up the escalation ladder this got. */
    escalationLevel: smallint().notNull().default(0),
    lastNotifiedAt: timestamp({ withTimezone: true }),
    rootCause: text(),
    postmortem: text(),
    /** Shown on the status page. Off by default — not every blip is public. */
    isPublic: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [
    index('incidents_monitor_idx').on(t.monitorId, t.startedAt),
    /*
     * At most one unresolved incident per monitor. Without this a slow
     * notification path or two schedulers racing produces two open incidents
     * for one outage, and every downstream count doubles.
     */
    unique('incidents_open_key').on(t.monitorId, t.resolvedAt).nullsNotDistinct(),
  ],
);

/** The public timeline: investigating → identified → monitoring → resolved. */
export const incidentUpdates = pgTable(
  'incident_updates',
  {
    id: primaryId(),
    workspaceId: ws(),
    incidentId: uuid()
      .notNull()
      .references(() => incidents.id, { onDelete: 'cascade' }),
    status: varchar({ length: 16 }).notNull(),
    body: text().notNull(),
    authorId: uuid().references(() => users.id, { onDelete: 'set null' }),
    ...timestamps,
  },
  (t) => [index('incident_updates_idx').on(t.incidentId, t.createdAt)],
);

/**
 * Planned work.
 *
 * A window suppresses alerts *and* excludes the period from uptime — otherwise
 * every planned deploy costs the customer their SLA number, and they learn to
 * stop declaring maintenance.
 */
export const maintenanceWindows = pgTable(
  'maintenance_windows',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    monitorIds: uuid().array().notNull().default([]),
    title: text().notNull(),
    body: text(),
    startsAt: timestamp({ withTimezone: true }).notNull(),
    endsAt: timestamp({ withTimezone: true }).notNull(),
    /** none | daily | weekly | monthly */
    recurrence: varchar({ length: 12 }).notNull().default('none'),
    notifySubscribers: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [index('maintenance_windows_idx').on(t.projectId, t.startsAt)],
);

/**
 * A public status page.
 *
 * Rendered statically and served from the edge, because the one thing it must
 * do is stay up when the application is down — a status page that shares a fate
 * with the thing it reports on is worse than no status page.
 */
export const statusPages = pgTable(
  'status_pages',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    domainId: uuid().references(() => customDomains.id, { onDelete: 'set null' }),
    slug: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    description: text(),
    logoUrl: text(),
    /** Grouped monitors: `[{ name, monitorIds }]`. */
    sections: json().notNull().default(emptyJsonArray),
    passwordHash: text(),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    showUptimeDays: smallint().notNull().default(90),
    subscribersEnabled: boolean().notNull().default(true),
    isPublic: boolean().notNull().default(true),
    customCss: text(),
    ...timestamps,
    ...softDelete,
  },
  (t) => [unique('status_pages_slug_key').on(t.slug)],
);

export const statusSubscribers = pgTable(
  'status_subscribers',
  {
    id: primaryId(),
    workspaceId: ws(),
    statusPageId: uuid()
      .notNull()
      .references(() => statusPages.id, { onDelete: 'cascade' }),
    /** email | sms | webhook | rss | slack */
    kind: varchar({ length: 12 }).notNull(),
    address: text().notNull(),
    /** Which sections they care about. Empty means all of them. */
    components: uuid().array().notNull().default([]),
    confirmedAt: timestamp({ withTimezone: true }),
    unsubscribeToken: varchar({ length: 64 }).notNull(),
    ...timestamps,
  },
  (t) => [unique('status_subscribers_key').on(t.statusPageId, t.kind, t.address)],
);

/**
 * Where probes run from.
 *
 * Instance-level rather than per workspace: an operator runs the regions, and a
 * customer chooses among them.
 */
export const probeRegions = pgTable(
  'probe_regions',
  {
    id: primaryId(),
    code: varchar({ length: 16 }).notNull().unique(),
    name: text().notNull(),
    country: varchar({ length: 2 }),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
);

/**
 * A self-hosted probe, for things behind a firewall.
 *
 * It polls us for work rather than us reaching in — which is the only design
 * that works through a NAT and the only one a security team will approve.
 */
export const monitorAgents = pgTable(
  'monitor_agents',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    tokenHash: varchar({ length: 64 }).notNull(),
    hostname: text(),
    version: varchar({ length: 24 }),
    lastSeenAt: timestamp({ withTimezone: true }),
    metrics: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [unique('monitor_agents_token').on(t.tokenHash)],
);
