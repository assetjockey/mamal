import {

  boolean,
  index,
  integer,
  pgTable,
  real,
  text,
  timestamp,
  unique,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { emptyJsonArray, json, primaryId, timestamps } from './_shared.ts';
import { projects, workspaces } from './tenancy.ts';
import { sites } from './core.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });
const pr = () =>
  uuid()
    .notNull()
    .references(() => projects.id, { onDelete: 'cascade' });

// ---------------------------------------------------------------------------
// audit_sites — Audit's thin profile on the shared `sites` row.
// Monitor and Track keep their own; the site itself is core-owned.
// ---------------------------------------------------------------------------

export type CrawlConfig = {
  maxPages: number;
  maxDepth: number;
  respectRobots: boolean;
  renderJs: boolean;
  userAgent?: string;
  includePatterns?: string[];
  excludePatterns?: string[];
  basicAuth?: { username: string; password: string };
  lighthouse: 'off' | 'sample' | 'all';
};

export const AUDIT_SCHEDULES = ['manual', '6h', '12h', 'daily', '3d', 'weekly', '30d'] as const;
export type AuditSchedule = (typeof AUDIT_SCHEDULES)[number];

export const auditSites = pgTable(
  'audit_sites',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    siteId: uuid()
      .notNull()
      .references(() => sites.id, { onDelete: 'cascade' }),
    score: integer(),
    previousScore: integer(),
    grade: varchar({ length: 2 }),
    testsTotal: integer().notNull().default(0),
    testsPassed: integer().notNull().default(0),
    criticalCount: integer().notNull().default(0),
    warningCount: integer().notNull().default(0),
    infoCount: integer().notNull().default(0),
    schedule: varchar({ length: 16 }).$type<AuditSchedule>().notNull().default('manual'),
    crawlConfig: json<CrawlConfig>().notNull().default({
      maxPages: 25,
      maxDepth: 5,
      respectRobots: true,
      renderJs: false,
      lighthouse: 'off',
    }),
    notificationChannelIds: text().array().notNull().default([]),
    lastAuditAt: timestamp({ withTimezone: true }),
    /** The scheduler claims on this column; never a repeatable job. */
    nextAuditAt: timestamp({ withTimezone: true }),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [
    unique('audit_sites_site_key').on(t.siteId),
    index('audit_sites_due_idx').on(t.nextAuditAt, t.isEnabled),
  ],
);

// ---------------------------------------------------------------------------
// audits — one crawl run
// ---------------------------------------------------------------------------

export const AUDIT_PHASES = [
  'queued', 'discovering', 'crawling', 'analyzing', 'lighthouse', 'scoring', 'done', 'failed',
] as const;
export type AuditPhase = (typeof AUDIT_PHASES)[number];

export const AUDIT_TRIGGERS = ['manual', 'schedule', 'automation', 'api', 'onboarding'] as const;

export const audits = pgTable(
  'audits',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    auditSiteId: uuid()
      .notNull()
      .references(() => auditSites.id, { onDelete: 'cascade' }),
    trigger: varchar({ length: 16 }).notNull().default('manual'),
    status: varchar({ length: 16 }).notNull().default('queued'),
    phase: varchar({ length: 16 }).$type<AuditPhase>().notNull().default('queued'),
    startUrl: text().notNull(),
    config: json<CrawlConfig>().notNull(),

    pagesCrawled: integer().notNull().default(0),
    pagesTotal: integer().notNull().default(0),
    /**
     * Pages a WAF or bot challenge refused. Reported honestly with the UA to
     * allowlist — the #1 complaint about cloud crawlers is pretending this
     * did not happen.
     */
    pagesBlocked: integer().notNull().default(0),
    lighthouseDone: integer().notNull().default(0),

    score: integer(),
    criticalCount: integer().notNull().default(0),
    warningCount: integer().notNull().default(0),
    infoCount: integer().notNull().default(0),

    /** Frontier + visited set, so a killed worker resumes instead of restarting. */
    crawlCursor: json().notNull().default({}),
    errorCode: varchar({ length: 48 }),
    errorDetail: text(),
    startedAt: timestamp({ withTimezone: true }),
    finishedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    index('audits_site_idx').on(t.auditSiteId, t.createdAt),
    index('audits_status_idx').on(t.status, t.phase),
  ],
);

// ---------------------------------------------------------------------------
// audit_pages — one row per crawled URL
// ---------------------------------------------------------------------------

export const FETCH_CLASSES = ['ok', 'blocked', 'error', 'timeout', 'redirect'] as const;
export type FetchClass = (typeof FETCH_CLASSES)[number];

export const auditPages = pgTable(
  'audit_pages',
  {
    id: primaryId(),
    workspaceId: ws(),
    auditId: uuid()
      .notNull()
      .references(() => audits.id, { onDelete: 'cascade' }),
    url: text().notNull(),
    /** sha256 of the normalized URL — the dedupe key within a run. */
    urlHash: varchar({ length: 64 }).notNull(),
    statusCode: integer(),
    fetchClass: varchar({ length: 16 }).$type<FetchClass>().notNull().default('ok'),
    redirectChain: json<string[]>().notNull().default(emptyJsonArray),

    title: text(),
    metaDescription: text(),
    canonical: text(),
    headerCanonical: text(),
    robotsMeta: varchar({ length: 128 }),
    xRobotsTag: varchar({ length: 128 }),
    ogTitle: text(),
    ogDescription: text(),
    ogImage: text(),

    h1Count: integer().notNull().default(0),
    h2Count: integer().notNull().default(0),
    h3Count: integer().notNull().default(0),
    headings: json<{ level: number; text: string }[]>().notNull().default(emptyJsonArray),
    h1Text: text(),

    wordCount: integer().notNull().default(0),
    textRatio: real().notNull().default(0),
    imagesTotal: integer().notNull().default(0),
    imagesMissingAlt: integer().notNull().default(0),
    linksInternal: integer().notNull().default(0),
    linksExternal: integer().notNull().default(0),

    hasStructuredData: boolean().notNull().default(false),
    schemaTypes: text().array().notNull().default([]),
    hreflang: json<{ lang: string; href: string }[]>().notNull().default(emptyJsonArray),
    lang: varchar({ length: 16 }),

    isIndexable: boolean().notNull().default(true),
    depth: integer().notNull().default(0),
    inSitemap: boolean().notNull().default(false),
    /** sha256 of normalized body text — how duplicate content is detected. */
    contentHash: varchar({ length: 64 }),

    responseMs: integer(),
    ttfbMs: integer(),
    bytes: integer(),
    httpVersion: varchar({ length: 8 }),
    compression: varchar({ length: 16 }),
    isHttps: boolean().notNull().default(false),
    headers: json<Record<string, string>>().notNull().default({}),

    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [
    unique('audit_pages_url_key').on(t.auditId, t.urlHash),
    index('audit_pages_audit_idx').on(t.auditId, t.depth),
    index('audit_pages_hash_idx').on(t.auditId, t.contentHash),
  ],
);

/**
 * The link graph, persisted.
 *
 * crawlseo keeps it; open-seo throws it away at the end of the crawl. Keeping
 * it is what makes orphan detection, internal-authority and "what links to
 * this broken page" answerable after the fact.
 */
export const auditLinks = pgTable(
  'audit_links',
  {
    id: primaryId(),
    workspaceId: ws(),
    auditId: uuid()
      .notNull()
      .references(() => audits.id, { onDelete: 'cascade' }),
    sourceUrl: text().notNull(),
    targetUrl: text().notNull(),
    anchor: text(),
    rel: varchar({ length: 64 }),
    isInternal: boolean().notNull().default(true),
    statusCode: integer(),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [
    index('audit_links_audit_idx').on(t.auditId, t.isInternal),
    index('audit_links_target_idx').on(t.auditId, t.targetUrl),
  ],
);

// ---------------------------------------------------------------------------
// audit_issues — what the run found
// ---------------------------------------------------------------------------

export const ISSUE_STATUSES = ['open', 'fixed', 'ignored', 'wontfix'] as const;
export type IssueStatus = (typeof ISSUE_STATUSES)[number];

export const SEVERITIES = ['critical', 'warning', 'info'] as const;
export type Severity = (typeof SEVERITIES)[number];

export const auditIssues = pgTable(
  'audit_issues',
  {
    id: primaryId(),
    workspaceId: ws(),
    auditId: uuid()
      .notNull()
      .references(() => audits.id, { onDelete: 'cascade' }),
    auditSiteId: uuid()
      .notNull()
      .references(() => auditSites.id, { onDelete: 'cascade' }),
    /** Null for site-wide findings. */
    pageId: uuid().references(() => auditPages.id, { onDelete: 'cascade' }),
    pageUrl: text(),
    ruleId: varchar({ length: 64 }).notNull(),
    severity: varchar({ length: 16 }).$type<Severity>().notNull(),
    /** The exact tag, header or value that failed — never a generic message. */
    evidence: json().notNull().default({}),
    status: varchar({ length: 16 }).$type<IssueStatus>().notNull().default('open'),
    note: text(),
    assignedToUserId: uuid(),
    firstSeenAuditId: uuid(),
    resolvedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    index('audit_issues_audit_idx').on(t.auditId, t.severity),
    index('audit_issues_rule_idx').on(t.auditSiteId, t.ruleId, t.status),
    index('audit_issues_page_idx').on(t.pageId),
  ],
);

/**
 * Rules as ROWS, not a 1,200-line trait.
 *
 * phprank puts all 36 checks in one `model()` method; 66audit in one includes
 * array. Adding a rule here is a registry entry plus a seed row, and the
 * thresholds are workspace-overridable without a deploy.
 */
export const auditRules = pgTable(
  'audit_rules',
  {
    id: varchar({ length: 64 }).primaryKey(),
    category: varchar({ length: 32 }).notNull(),
    severity: varchar({ length: 16 }).$type<Severity>().notNull(),
    weight: integer().notNull().default(5),
    title: text().notNull(),
    why: text().notNull(),
    howToFix: text().notNull(),
    docsUrl: text(),
    appliesTo: varchar({ length: 8 }).notNull().default('page'),
    thresholds: json<Record<string, number | string | boolean>>().notNull().default({}),
    isEnabled: boolean().notNull().default(true),
    /** Surfaced under "AI visibility" in the UI. */
    isAiRelevant: boolean().notNull().default(false),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('audit_rules_category_idx').on(t.category, t.isEnabled)],
);

/** Per-workspace threshold and enable overrides. */
export const auditRuleOverrides = pgTable(
  'audit_rule_overrides',
  {
    id: primaryId(),
    workspaceId: ws(),
    ruleId: varchar({ length: 64 })
      .notNull()
      .references(() => auditRules.id, { onDelete: 'cascade' }),
    isEnabled: boolean(),
    severity: varchar({ length: 16 }).$type<Severity>(),
    thresholds: json<Record<string, number | string | boolean>>(),
    ...timestamps,
  },
  (t) => [unique('audit_rule_overrides_key').on(t.workspaceId, t.ruleId)],
);

export const auditLighthouse = pgTable(
  'audit_lighthouse',
  {
    id: primaryId(),
    workspaceId: ws(),
    auditId: uuid()
      .notNull()
      .references(() => audits.id, { onDelete: 'cascade' }),
    pageId: uuid().references(() => auditPages.id, { onDelete: 'cascade' }),
    url: text().notNull(),
    strategy: varchar({ length: 16 }).notNull().default('mobile'),
    performance: integer(),
    accessibility: integer(),
    bestPractices: integer(),
    seo: integer(),
    lcpMs: integer(),
    cls: real(),
    inpMs: integer(),
    ttfbMs: integer(),
    tbtMs: integer(),
    reportAssetId: uuid(),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('audit_lighthouse_audit_idx').on(t.auditId)],
);

/** The trend series — score history survives audit pruning. */
export const auditSnapshots = pgTable(
  'audit_snapshots',
  {
    id: primaryId(),
    workspaceId: ws(),
    auditSiteId: uuid()
      .notNull()
      .references(() => auditSites.id, { onDelete: 'cascade' }),
    auditId: uuid(),
    capturedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    score: integer().notNull(),
    criticalCount: integer().notNull().default(0),
    warningCount: integer().notNull().default(0),
    infoCount: integer().notNull().default(0),
    pagesCrawled: integer().notNull().default(0),
  },
  (t) => [index('audit_snapshots_site_idx').on(t.auditSiteId, t.capturedAt)],
);

/** Usage + ratings for the free public tools. */
export const auditToolRuns = pgTable(
  'audit_tool_runs',
  {
    id: primaryId(),
    workspaceId: uuid(),
    slug: varchar({ length: 64 }).notNull(),
    input: json().notNull().default({}),
    output: json(),
    durationMs: integer(),
    ipHash: varchar({ length: 64 }),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('audit_tool_runs_slug_idx').on(t.slug, t.createdAt)],
);

