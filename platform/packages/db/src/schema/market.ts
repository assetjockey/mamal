import {
  bigint,
  boolean,
  date,
  index,
  integer,
  numeric,
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
import { projects, workspaces } from './tenancy.ts';
import { assets, links, sites } from './core.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });
const pr = () =>
  uuid()
    .notNull()
    .references(() => projects.id, { onDelete: 'cascade' });

/**
 * Market: six modules under one roof.
 *
 *   4A  SEO             research, rank tracking, backlinks, GSC/GA4
 *   4B  AI visibility   whether the models name you, and for which prompts
 *   4C  Content         briefs, drafts, autoblogging pipelines, trend watch
 *   4D  Social          accounts, composer, calendar, queues, monitoring
 *   4E  Ads             creatives, copy, campaigns, spend joined to results
 *   4F  Local           Google Business, reviews, local rank
 *
 * Each is independently useful — that is the point of the module split — but
 * they share the `projects` grouping and a single connection model, because a
 * customer who connects Search Console once should not connect it again for
 * content briefs.
 *
 * **Where the cash costs live.** Every table here that can be populated from
 * GSC, GA4 or PageSpeed is free-tier reachable; everything sourced from
 * DataForSEO or an AI provider carries `credits_spent` and a
 * `vendor_cost_micros`, because §0.5 anchors credit pricing to our own cost and
 * the monthly margin report is only honest if every row records what it cost.
 */

/* ========================================================================
 * Connections — one model for every third party
 * ===================================================================== */

export const CONNECTION_PROVIDERS = [
  'google_search_console',
  'google_analytics',
  'google_business',
  'google_ads',
  'meta_ads',
  'tiktok_ads',
  'facebook',
  'instagram',
  'threads',
  'x',
  'linkedin',
  'tiktok',
  'youtube',
  'pinterest',
  'wordpress',
  'shopify',
  'woocommerce',
  'ghost',
  'webhook',
] as const;
export type ConnectionProvider = (typeof CONNECTION_PROVIDERS)[number];

/**
 * A connected third-party account.
 *
 * One table for OAuth, API keys and webhooks alike, because the *lifecycle* is
 * identical — connect, refresh, notice it broke, reconnect — and that lifecycle
 * is almost all of the work. What differs is the credential shape, which is
 * encrypted anyway and therefore opaque here.
 *
 * `status` is separate from "has a token" on purpose: a revoked token still
 * exists, and the difference between "never connected" and "was connected and
 * stopped working" is the difference between an onboarding prompt and an alert.
 */
export const connections = pgTable(
  'market_connections',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    provider: varchar({ length: 32 }).$type<ConnectionProvider>().notNull(),
    /** The account as the provider names it — a property id, a page id, a handle. */
    externalId: text().notNull(),
    displayName: text().notNull(),
    avatarUrl: text(),
    /** Envelope-encrypted. Never returned, never logged. */
    credentialsEncrypted: text(),
    scopes: text().array().notNull().default([]),
    expiresAt: timestamp({ withTimezone: true }),
    /** active | expired | revoked | error */
    status: varchar({ length: 16 }).notNull().default('active'),
    lastError: text(),
    lastSyncedAt: timestamp({ withTimezone: true }),
    settings: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [
    // The same provider account connected twice would double every metric it
    // feeds, silently.
    unique('market_connections_key').on(t.workspaceId, t.provider, t.externalId),
    index('market_connections_project_idx').on(t.projectId, t.provider),
    index('market_connections_status_idx').on(t.status, t.expiresAt),
  ],
);

/* ========================================================================
 * 4A — SEO
 * ===================================================================== */

/**
 * A keyword, and what it is worth.
 *
 * Volume, CPC and difficulty come from a paid vendor, so the row records when
 * it was fetched and what it cost. Stale-but-present beats absent: a keyword
 * list is still usable with month-old volumes, and re-fetching every view would
 * be a bill per page load.
 */
export const seoKeywords = pgTable(
  'seo_keywords',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    keyword: text().notNull(),
    /** DataForSEO's numeric location, so a keyword is per-market. */
    locationCode: integer().notNull().default(2840),
    languageCode: varchar({ length: 8 }).notNull().default('en'),
    volume: integer(),
    cpcMicros: bigint({ mode: 'number' }),
    competition: real(),
    difficulty: smallint(),
    /** informational | navigational | commercial | transactional */
    intent: varchar({ length: 16 }),
    /** Twelve months of volume, for the sparkline and for seasonality. */
    monthly: json().notNull().default(emptyJsonArray).$type<{ year: number; month: number; volume: number }[]>(),
    /** Which vendor call produced this, and when — so staleness is visible. */
    source: varchar({ length: 24 }).notNull().default('dataforseo'),
    fetchedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('seo_keywords_key').on(t.workspaceId, t.keyword, t.locationCode, t.languageCode),
    index('seo_keywords_project_idx').on(t.projectId, t.volume),
  ],
);

export const seoKeywordTags = pgTable(
  'seo_keyword_tags',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    color: varchar({ length: 16 }),
    ...timestamps,
  },
  (t) => [unique('seo_keyword_tags_key').on(t.workspaceId, t.projectId, t.name)],
);

export const seoKeywordTagAssignments = pgTable(
  'seo_keyword_tag_assignments',
  {
    id: primaryId(),
    workspaceId: ws(),
    keywordId: uuid()
      .notNull()
      .references(() => seoKeywords.id, { onDelete: 'cascade' }),
    tagId: uuid()
      .notNull()
      .references(() => seoKeywordTags.id, { onDelete: 'cascade' }),
    ...timestamps,
  },
  (t) => [unique('seo_keyword_tag_assignments_key').on(t.keywordId, t.tagId)],
);

/**
 * What to track, how often, and from where.
 *
 * `nextCheckAt` is the claim column: the scheduler selects due rows with
 * `for update skip locked` rather than holding a repeatable job per config,
 * which is the pattern §0.8 insists on and the reason 100,000 tracked keywords
 * is a query rather than a Redis memory problem.
 */
export const rankConfigs = pgTable(
  'rank_configs',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    siteId: uuid().references(() => sites.id, { onDelete: 'cascade' }),
    domain: varchar({ length: 253 }).notNull(),
    locationCode: integer().notNull().default(2840),
    languageCode: varchar({ length: 8 }).notNull().default('en'),
    /** desktop, mobile, or both — each is a separate SERP and a separate call. */
    devices: text().array().notNull().default(['desktop']),
    /** How deep to look. Beyond 100 the position stops meaning anything. */
    serpDepth: smallint().notNull().default(100),
    /** daily | weekly | monthly */
    schedule: varchar({ length: 16 }).notNull().default('weekly'),
    isActive: boolean().notNull().default(true),
    nextCheckAt: timestamp({ withTimezone: true }),
    lastRunAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    /*
     * One tracker per domain per market.
     *
     * Two trackers for the same domain in *different* markets is legitimate —
     * a term is worth different things in different countries — but two
     * identical ones are not: they double the SERP calls, double the bill, and
     * show the customer the same table twice with no way to tell which is
     * which.
     */
    unique('rank_configs_key').on(t.projectId, t.domain, t.locationCode, t.languageCode),
    index('rank_configs_due_idx').on(t.isActive, t.nextCheckAt),
    index('rank_configs_project_idx').on(t.projectId),
  ],
);

export const rankKeywords = pgTable(
  'rank_keywords',
  {
    id: primaryId(),
    workspaceId: ws(),
    configId: uuid()
      .notNull()
      .references(() => rankConfigs.id, { onDelete: 'cascade' }),
    keyword: text().notNull(),
    /** The URL the customer wants ranking, if they have an opinion. */
    targetUrl: text(),
    isActive: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [unique('rank_keywords_key').on(t.configId, t.keyword)],
);

export const rankRuns = pgTable(
  'rank_runs',
  {
    id: primaryId(),
    workspaceId: ws(),
    configId: uuid()
      .notNull()
      .references(() => rankConfigs.id, { onDelete: 'cascade' }),
    /** queued | running | completed | failed | cancelled */
    status: varchar({ length: 16 }).notNull().default('queued'),
    keywordsTotal: integer().notNull().default(0),
    keywordsDone: integer().notNull().default(0),
    creditsSpent: integer().notNull().default(0),
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    errorCode: varchar({ length: 48 }),
    startedAt: timestamp({ withTimezone: true }),
    finishedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('rank_runs_config_idx').on(t.configId, t.createdAt)],
);

/**
 * One row per keyword, per device, per day.
 *
 * `position` is nullable and that is meaningful: null means "not in the top
 * `serpDepth`", which is different from zero and different from missing. Storing
 * 101 as a sentinel is how averages quietly become nonsense.
 */
export const rankSnapshots = pgTable(
  'rank_snapshots',
  {
    id: primaryId(),
    workspaceId: ws(),
    configId: uuid()
      .notNull()
      .references(() => rankConfigs.id, { onDelete: 'cascade' }),
    keywordId: uuid()
      .notNull()
      .references(() => rankKeywords.id, { onDelete: 'cascade' }),
    capturedOn: date().notNull(),
    device: varchar({ length: 12 }).notNull().default('desktop'),
    position: smallint(),
    previousPosition: smallint(),
    url: text(),
    /** featured_snippet, people_also_ask, local_pack, … */
    serpFeatures: text().array().notNull().default([]),
    ...timestamps,
  },
  (t) => [
    unique('rank_snapshots_key').on(t.keywordId, t.capturedOn, t.device),
    index('rank_snapshots_trend_idx').on(t.configId, t.capturedOn),
  ],
);

export const backlinkSnapshots = pgTable(
  'backlink_snapshots',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    domain: varchar({ length: 253 }).notNull(),
    capturedOn: date().notNull(),
    rank: integer(),
    backlinks: bigint({ mode: 'number' }),
    referringDomains: integer(),
    newLinks: integer(),
    lostLinks: integer(),
    brokenLinks: integer(),
    creditsSpent: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('backlink_snapshots_key').on(t.workspaceId, t.domain, t.capturedOn)],
);

/**
 * Search Console performance, kept locally.
 *
 * GSC is free but rate-limited and lagging by two to three days, and its API
 * will not answer "what changed since last month" — so the rows are copied here
 * and the opportunity finders run over them. That is also what makes striking
 * distance, decay and cannibalisation work on the **free tier**: they are
 * arithmetic over data Google gives away.
 */
export const searchPerformance = pgTable(
  'market_search_performance',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    connectionId: uuid()
      .notNull()
      .references(() => connections.id, { onDelete: 'cascade' }),
    capturedOn: date().notNull(),
    query: text().notNull(),
    page: text().notNull(),
    country: varchar({ length: 3 }),
    device: varchar({ length: 12 }),
    clicks: integer().notNull().default(0),
    impressions: integer().notNull().default(0),
    position: real(),
    ...timestamps,
  },
  (t) => [
    unique('market_search_performance_key').on(
      t.connectionId, t.capturedOn, t.query, t.page, t.device,
    ),
    index('market_search_performance_project_idx').on(t.projectId, t.capturedOn),
    index('market_search_performance_query_idx').on(t.projectId, t.query),
  ],
);

/**
 * An opportunity the maths found.
 *
 * Materialised rather than computed per request: the finders scan months of
 * search performance, and a customer refreshing a dashboard should not
 * re-run that. `status` lets somebody dismiss one without it coming back
 * tomorrow, which is the difference between a useful list and a nag.
 */
export const OPPORTUNITY_KINDS = [
  'striking_distance',
  'low_ctr',
  'content_decay',
  'cannibalisation',
  'rising_query',
] as const;
export type OpportunityKind = (typeof OPPORTUNITY_KINDS)[number];

export const seoOpportunities = pgTable(
  'seo_opportunities',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    kind: varchar({ length: 24 }).$type<OpportunityKind>().notNull(),
    query: text(),
    page: text(),
    /** Ranked highest-first in the UI; the finders each define their own scale. */
    score: real().notNull().default(0),
    /** Everything the finder used, so the card can show its working. */
    evidence: json().notNull().default({}),
    /** open | actioned | dismissed */
    status: varchar({ length: 16 }).notNull().default('open'),
    detectedOn: date().notNull(),
    ...timestamps,
  },
  (t) => [
    unique('seo_opportunities_key').on(t.projectId, t.kind, t.query, t.page),
    index('seo_opportunities_open_idx').on(t.projectId, t.status, t.score),
  ],
);

/* ========================================================================
 * 4B — AI visibility
 * ===================================================================== */

export const visibilityPrompts = pgTable(
  'market_ai_prompts',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    prompt: text().notNull(),
    intent: varchar({ length: 24 }),
    isTracked: boolean().notNull().default(true),
    /** daily | weekly | monthly */
    schedule: varchar({ length: 16 }).notNull().default('weekly'),
    nextRunAt: timestamp({ withTimezone: true }),
    ...timestamps,
    /*
     * Soft, because the runs are the evidence behind snapshots already drawn
     * on the chart. A hard delete cascades them and quietly rewrites months of
     * share-of-voice history.
     */
    ...softDelete,
  },
  (t) => [
    unique('market_ai_prompts_key').on(t.projectId, t.prompt),
    index('market_ai_prompts_due_idx').on(t.isTracked, t.nextRunAt),
  ],
);

/**
 * One model's answer to one prompt, once.
 *
 * The answer text is kept because "you are not mentioned" is unactionable
 * without seeing who was. `citedSources` is the column that matters
 * commercially: being named is nice, being *linked* is traffic.
 */
export const visibilityPromptRuns = pgTable(
  'market_ai_prompt_runs',
  {
    id: primaryId(),
    workspaceId: ws(),
    promptId: uuid()
      .notNull()
      .references(() => visibilityPrompts.id, { onDelete: 'cascade' }),
    model: varchar({ length: 32 }).notNull(),
    answer: text(),
    citedSources: json().notNull().default(emptyJsonArray).$type<{ url: string; title?: string }[]>(),
    brandMentioned: boolean().notNull().default(false),
    /** Where in the answer the brand first appears, 1-based. Null when absent. */
    mentionPosition: smallint(),
    /** positive | neutral | negative */
    sentiment: varchar({ length: 12 }),
    creditsSpent: integer().notNull().default(0),
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    /** ok | failed — one provider failing must not blank the comparison. */
    status: varchar({ length: 12 }).notNull().default('ok'),
    error: text(),
    ...timestamps,
  },
  (t) => [index('market_ai_prompt_runs_idx').on(t.promptId, t.createdAt)],
);

export const visibilitySnapshots = pgTable(
  'market_ai_visibility_snapshots',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    capturedOn: date().notNull(),
    model: varchar({ length: 32 }).notNull(),
    /** Share of all brand mentions across the tracked prompt set. */
    shareOfVoice: real().notNull().default(0),
    mentionRate: real().notNull().default(0),
    avgPosition: real(),
    citationCount: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('market_ai_visibility_snapshots_key').on(t.projectId, t.capturedOn, t.model)],
);

export const visibilityCompetitors = pgTable(
  'market_ai_competitors',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    brand: text().notNull(),
    domain: varchar({ length: 253 }),
    /** Exactly one row per project should be the customer themselves. */
    isSelf: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [unique('market_ai_competitors_key').on(t.projectId, t.brand)],
);

/* ========================================================================
 * 4C — Content
 * ===================================================================== */

export const contentDocs = pgTable(
  'content_docs',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    title: text().notNull(),
    slug: varchar({ length: 255 }),
    /** draft | in_review | approved | published | archived */
    status: varchar({ length: 16 }).notNull().default('draft'),
    body: text().notNull().default(''),
    outline: json().notNull().default(emptyJsonArray),
    targetKeywords: text().array().notNull().default([]),
    /** The editor's live score against the brief, 0–100. */
    seoScore: smallint(),
    readability: real(),
    wordCount: integer().notNull().default(0),
    heroAssetId: uuid().references(() => assets.id, { onDelete: 'set null' }),
    meta: json().notNull().default({}),
    publishedAt: timestamp({ withTimezone: true }),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    index('content_docs_project_idx').on(t.projectId, t.status),
    unique('content_docs_slug_key').on(t.projectId, t.slug),
  ],
);

/**
 * What the draft is being written against.
 *
 * Separated from the doc because a brief is expensive to build — it is SERP
 * analysis plus entity extraction — and it stays valid while the draft is
 * rewritten ten times. Merging them would mean re-fetching on every save.
 */
export const contentBriefs = pgTable(
  'content_briefs',
  {
    id: primaryId(),
    workspaceId: ws(),
    docId: uuid()
      .notNull()
      .references(() => contentDocs.id, { onDelete: 'cascade' }),
    serpAnalysis: json().notNull().default({}),
    entities: text().array().notNull().default([]),
    questions: text().array().notNull().default([]),
    competitorOutlines: json().notNull().default(emptyJsonArray),
    targetWordCount: integer(),
    creditsSpent: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('content_briefs_doc').on(t.docId)],
);

export const publishDestinations = pgTable(
  'publish_destinations',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /** wordpress | shopify | woocommerce | ghost | webhook | rss */
    kind: varchar({ length: 24 }).notNull(),
    name: text().notNull(),
    connectionId: uuid().references(() => connections.id, { onDelete: 'set null' }),
    credentialsEncrypted: text(),
    config: json().notNull().default({}),
    /** draft | publish — never default to publish; a bad pipeline goes live. */
    defaultStatus: varchar({ length: 16 }).notNull().default('draft'),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [index('publish_destinations_project_idx').on(t.projectId, t.kind)],
);

export const contentPipelines = pgTable(
  'content_pipelines',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    /** trend | keyword | rss | gsc_opportunity | audit_issue */
    source: varchar({ length: 24 }).notNull(),
    sourceConfig: json().notNull().default({}),
    schedule: varchar({ length: 24 }).notNull().default('weekly'),
    templateId: uuid(),
    destinationId: uuid().references(() => publishDestinations.id, { onDelete: 'set null' }),
    /**
     * Off by default, and the UI says why: a pipeline that publishes
     * unreviewed generated text to a live site is one bad prompt away from an
     * incident the customer finds out about from a reader.
     */
    autoPublish: boolean().notNull().default(false),
    isActive: boolean().notNull().default(false),
    nextRunAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('content_pipelines_due_idx').on(t.isActive, t.nextRunAt)],
);

export const contentRuns = pgTable(
  'content_runs',
  {
    id: primaryId(),
    workspaceId: ws(),
    pipelineId: uuid()
      .notNull()
      .references(() => contentPipelines.id, { onDelete: 'cascade' }),
    /** queued | running | completed | failed | skipped */
    status: varchar({ length: 16 }).notNull().default('queued'),
    docId: uuid().references(() => contentDocs.id, { onDelete: 'set null' }),
    /** What triggered this run — the trend, the query, the issue. */
    trigger: json().notNull().default({}),
    creditsSpent: integer().notNull().default(0),
    error: text(),
    startedAt: timestamp({ withTimezone: true }),
    finishedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('content_runs_pipeline_idx').on(t.pipelineId, t.createdAt)],
);

/**
 * Trend watch.
 *
 * `snapshot` holds the baseline the threshold is measured against, which is the
 * whole mechanism: without a stored baseline every check reports "trending"
 * because it has nothing to compare with. Per-region because a term rising in
 * Brazil and flat in Germany is two different facts.
 */
export const trendWatches = pgTable(
  'trend_watches',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    keywords: text().array().notNull().default([]),
    geos: text().array().notNull().default(['']),
    /** now 7-d | today 1-m | today 3-m | today 12-m */
    timeframe: varchar({ length: 16 }).notNull().default('today 3-m'),
    intervalMinutes: integer().notNull().default(1440),
    thresholdPct: real().notNull().default(25),
    snapshot: json().notNull().default({}),
    isActive: boolean().notNull().default(true),
    nextRunAt: timestamp({ withTimezone: true }),
    lastRunAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('trend_watches_due_idx').on(t.isActive, t.nextRunAt)],
);

export const trendEvents = pgTable(
  'trend_events',
  {
    id: primaryId(),
    workspaceId: ws(),
    watchId: uuid()
      .notNull()
      .references(() => trendWatches.id, { onDelete: 'cascade' }),
    keyword: text().notNull(),
    geo: varchar({ length: 8 }).notNull().default(''),
    previousValue: real(),
    currentValue: real(),
    deltaPct: real(),
    ...timestamps,
  },
  (t) => [index('trend_events_watch_idx').on(t.watchId, t.createdAt)],
);

/* ========================================================================
 * 4D — Social
 * ===================================================================== */

export const socialAccounts = pgTable(
  'social_accounts',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    connectionId: uuid()
      .notNull()
      .references(() => connections.id, { onDelete: 'cascade' }),
    provider: varchar({ length: 24 }).notNull(),
    externalId: text().notNull(),
    handle: text(),
    displayName: text().notNull(),
    avatarUrl: text(),
    followers: integer(),
    ...timestamps,
  },
  (t) => [
    unique('social_accounts_key').on(t.workspaceId, t.provider, t.externalId),
    index('social_accounts_project_idx').on(t.projectId),
  ],
);

export const socialAccountGroups = pgTable(
  'social_account_groups',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    accountIds: uuid().array().notNull().default([]),
    ...timestamps,
  },
  (t) => [unique('social_account_groups_key').on(t.projectId, t.name)],
);

/**
 * A post, once, whatever it is published to.
 *
 * The body and media live here; per-network overrides live on the target. That
 * split is what makes "the same announcement, shorter on X" one post rather
 * than five, and it is why editing the text does not silently un-schedule the
 * networks that were already fine.
 */
export const socialPosts = pgTable(
  'social_posts',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    body: text().notNull().default(''),
    mediaAssetIds: uuid().array().notNull().default([]),
    /** Shortened through /link, so every post is measurable by construction. */
    linkId: uuid().references(() => links.id, { onDelete: 'set null' }),
    /** draft | scheduled | publishing | published | failed | cancelled */
    status: varchar({ length: 16 }).notNull().default('draft'),
    /** now | scheduled | queue | recurring */
    scheduleType: varchar({ length: 16 }).notNull().default('now'),
    scheduledAt: timestamp({ withTimezone: true }),
    /** none | pending | approved | rejected — the team review workflow. */
    approvalState: varchar({ length: 16 }).notNull().default('none'),
    approvedBy: uuid(),
    campaign: varchar({ length: 160 }),
    firstComment: text(),
    hashtagSetId: uuid(),
    batchId: uuid(),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    index('social_posts_project_idx').on(t.projectId, t.status, t.scheduledAt),
    index('social_posts_batch_idx').on(t.batchId),
  ],
);

/**
 * One post, one account, one outcome.
 *
 * Separate rows because publishing to five networks succeeds four times and
 * fails once, routinely — a single `status` on the post would either lie or
 * lose the four that worked. `nextRunAt` is per target for the same reason:
 * a rate-limited network retries on its own clock.
 */
export const socialTargets = pgTable(
  'social_targets',
  {
    id: primaryId(),
    workspaceId: ws(),
    postId: uuid()
      .notNull()
      .references(() => socialPosts.id, { onDelete: 'cascade' }),
    accountId: uuid()
      .notNull()
      .references(() => socialAccounts.id, { onDelete: 'cascade' }),
    /** Per-network text, media crop, thread split. Empty means "use the post". */
    overrides: json().notNull().default({}),
    /** pending | publishing | published | failed | skipped */
    status: varchar({ length: 16 }).notNull().default('pending'),
    nextRunAt: timestamp({ withTimezone: true }),
    attempts: smallint().notNull().default(0),
    remoteId: text(),
    remoteUrl: text(),
    error: text(),
    publishedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('social_targets_key').on(t.postId, t.accountId),
    index('social_targets_due_idx').on(t.status, t.nextRunAt),
  ],
);

export const socialQueues = pgTable(
  'social_queues',
  {
    id: primaryId(),
    workspaceId: ws(),
    accountId: uuid()
      .notNull()
      .references(() => socialAccounts.id, { onDelete: 'cascade' }),
    /** A day/hour grid: which slots this account posts in. */
    slots: json().notNull().default({}),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    ...timestamps,
  },
  (t) => [unique('social_queues_account').on(t.accountId)],
);

export const socialMentions = pgTable(
  'social_mentions',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    provider: varchar({ length: 24 }).notNull(),
    externalId: text().notNull(),
    author: text(),
    text: text(),
    /** positive | neutral | negative */
    sentiment: varchar({ length: 12 }),
    reach: integer(),
    url: text(),
    occurredAt: timestamp({ withTimezone: true }).notNull(),
    handledBy: uuid(),
    handledAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('social_mentions_key').on(t.workspaceId, t.provider, t.externalId),
    index('social_mentions_project_idx').on(t.projectId, t.occurredAt),
  ],
);

export const influencers = pgTable(
  'market_influencers',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    provider: varchar({ length: 24 }).notNull(),
    handle: text().notNull(),
    displayName: text(),
    followers: integer(),
    engagementRate: real(),
    topics: text().array().notNull().default([]),
    contact: text(),
    score: real(),
    listName: text(),
    /** none | contacted | replied | agreed | declined */
    outreachState: varchar({ length: 16 }).notNull().default('none'),
    ...timestamps,
  },
  (t) => [unique('market_influencers_key').on(t.projectId, t.provider, t.handle)],
);

/* ========================================================================
 * 4E — Ads
 * ===================================================================== */

/**
 * A brand kit, injected into every generation.
 *
 * `magicads` proved this is what separates usable output from generic output,
 * and it is cheap: a paragraph of voice and a palette turn "write an ad" into
 * "write an ad that sounds like us".
 */
export const brands = pgTable(
  'market_brands',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    description: text(),
    voice: text(),
    audience: text(),
    palette: text().array().notNull().default([]),
    fonts: text().array().notNull().default([]),
    logoAssetId: uuid().references(() => assets.id, { onDelete: 'set null' }),
    dos: text().array().notNull().default([]),
    donts: text().array().notNull().default([]),
    isDefault: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [unique('market_brands_key').on(t.projectId, t.name)],
);

/**
 * A generated image or video.
 *
 * `providerJobId` and `pollCount` are the submit-poll-capture pattern from
 * §0.8: video takes minutes, and blocking a worker on a five-minute HTTP call
 * is how a queue stops. Credits are held at submit and captured here at the
 * true unit count, because video is priced per second and the count is not
 * known until it finishes.
 */
export const adCreatives = pgTable(
  'ad_creatives',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    brandId: uuid().references(() => brands.id, { onDelete: 'set null' }),
    /** image | video */
    type: varchar({ length: 12 }).notNull(),
    /** queued | generating | polling | completed | failed | cancelled */
    status: varchar({ length: 16 }).notNull().default('queued'),
    provider: varchar({ length: 32 }),
    modelId: varchar({ length: 64 }),
    prompt: text().notNull(),
    preset: varchar({ length: 48 }),
    width: integer(),
    height: integer(),
    durationSeconds: real(),
    assetId: uuid().references(() => assets.id, { onDelete: 'set null' }),
    /** The brand as it was at generation time — regenerating later must match. */
    brandSnapshot: json().notNull().default({}),
    creditHoldId: uuid(),
    creditsSpent: integer().notNull().default(0),
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    providerJobId: text(),
    pollCount: smallint().notNull().default(0),
    nextPollAt: timestamp({ withTimezone: true }),
    error: text(),
    ...timestamps,
  },
  (t) => [
    index('ad_creatives_project_idx').on(t.projectId, t.status),
    index('ad_creatives_poll_idx').on(t.status, t.nextPollAt),
  ],
);

export const adCopies = pgTable(
  'ad_copies',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    brandId: uuid().references(() => brands.id, { onDelete: 'set null' }),
    /** One of the 30 platforms, each with its own typed fields and limits. */
    platform: varchar({ length: 32 }).notNull(),
    objective: varchar({ length: 32 }),
    /** AIDA, PAS, PASTOR, BAB, 4Us, FAB, … */
    framework: varchar({ length: 24 }),
    tone: varchar({ length: 24 }),
    language: varchar({ length: 8 }).notNull().default('en'),
    brief: json().notNull().default({}),
    /** Several variants per generation — that is what makes them comparable. */
    variants: json().notNull().default(emptyJsonArray),
    wordCount: integer().notNull().default(0),
    creditsSpent: integer().notNull().default(0),
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    isFavorite: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('ad_copies_project_idx').on(t.projectId, t.platform)],
);

export const adAccounts = pgTable(
  'ad_accounts',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    connectionId: uuid()
      .notNull()
      .references(() => connections.id, { onDelete: 'cascade' }),
    platform: varchar({ length: 24 }).notNull(),
    externalId: text().notNull(),
    name: text().notNull(),
    currency: varchar({ length: 3 }).notNull().default('USD'),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    ...timestamps,
  },
  (t) => [unique('ad_accounts_key').on(t.workspaceId, t.platform, t.externalId)],
);

export const adCampaigns = pgTable(
  'ad_campaigns',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    accountId: uuid().references(() => adAccounts.id, { onDelete: 'set null' }),
    name: text().notNull(),
    platform: varchar({ length: 24 }).notNull(),
    objective: varchar({ length: 32 }),
    budgetMicros: bigint({ mode: 'number' }),
    /** daily | lifetime */
    budgetKind: varchar({ length: 12 }).notNull().default('daily'),
    audience: json().notNull().default({}),
    creativeIds: uuid().array().notNull().default([]),
    copyIds: uuid().array().notNull().default([]),
    /** draft | syncing | live | paused | ended | failed */
    status: varchar({ length: 16 }).notNull().default('draft'),
    externalId: text(),
    syncError: text(),
    startsAt: timestamp({ withTimezone: true }),
    endsAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('ad_campaigns_project_idx').on(t.projectId, t.status)],
);

/**
 * Spend and results at one grain: account, level, entity, day.
 *
 * Deliberately not a fact table in ClickHouse: ad metrics are thousands of rows
 * a day, not millions, and they are *restated* — platforms revise the last
 * 28 days as conversions attribute late. An upsertable relational row handles
 * restatement; an append-only stream would need a dedupe pass on every read.
 */
export const adMetrics = pgTable(
  'ad_metrics',
  {
    id: primaryId(),
    workspaceId: ws(),
    accountId: uuid()
      .notNull()
      .references(() => adAccounts.id, { onDelete: 'cascade' }),
    /** account | campaign | adset | ad */
    level: varchar({ length: 12 }).notNull(),
    entityId: text().notNull(),
    entityName: text(),
    capturedOn: date().notNull(),
    impressions: bigint({ mode: 'number' }).notNull().default(0),
    clicks: integer().notNull().default(0),
    spendMicros: bigint({ mode: 'number' }).notNull().default(0),
    conversions: real().notNull().default(0),
    conversionValueMicros: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [
    unique('ad_metrics_key').on(t.accountId, t.level, t.entityId, t.capturedOn),
    index('ad_metrics_day_idx').on(t.accountId, t.capturedOn),
  ],
);

export const adInsights = pgTable(
  'ad_insights',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    accountId: uuid().references(() => adAccounts.id, { onDelete: 'cascade' }),
    periodStart: date().notNull(),
    periodEnd: date().notNull(),
    summary: text().notNull(),
    recommendations: json().notNull().default(emptyJsonArray),
    creditsSpent: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('ad_insights_project_idx').on(t.projectId, t.periodEnd)],
);

export const promptLibrary = pgTable(
  'market_prompt_library',
  {
    id: primaryId(),
    /** Null for the seeded global library; set for a workspace's own. */
    workspaceId: uuid().references(() => workspaces.id, { onDelete: 'cascade' }),
    type: varchar({ length: 24 }).notNull(),
    title: text().notNull(),
    body: text().notNull(),
    isGlobal: boolean().notNull().default(false),
    favorites: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('market_prompt_library_idx').on(t.type, t.isGlobal)],
);

/* ========================================================================
 * 4F — Local
 * ===================================================================== */

export const localProfiles = pgTable(
  'market_local_profiles',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    connectionId: uuid()
      .notNull()
      .references(() => connections.id, { onDelete: 'cascade' }),
    /** Google's place id. */
    externalId: text().notNull(),
    name: text().notNull(),
    address: text(),
    latitude: numeric({ precision: 10, scale: 7 }),
    longitude: numeric({ precision: 10, scale: 7 }),
    primaryCategory: text(),
    categories: text().array().notNull().default([]),
    rating: real(),
    reviewCount: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('market_local_profiles_key').on(t.workspaceId, t.externalId)],
);

export const localReviews = pgTable(
  'market_local_reviews',
  {
    id: primaryId(),
    workspaceId: ws(),
    profileId: uuid()
      .notNull()
      .references(() => localProfiles.id, { onDelete: 'cascade' }),
    externalId: text().notNull(),
    author: text(),
    rating: smallint(),
    comment: text(),
    reply: text(),
    repliedAt: timestamp({ withTimezone: true }),
    occurredAt: timestamp({ withTimezone: true }).notNull(),
    ...timestamps,
  },
  (t) => [
    unique('market_local_reviews_key').on(t.profileId, t.externalId),
    index('market_local_reviews_idx').on(t.profileId, t.occurredAt),
  ],
);

/**
 * A local rank grid.
 *
 * One row per point on a geographic lattice: local results differ block by
 * block, and a single "position 4" for a plumber is meaningless when they are
 * first in one suburb and absent in the next.
 */
export const localRankPoints = pgTable(
  'market_local_rank_points',
  {
    id: primaryId(),
    workspaceId: ws(),
    profileId: uuid()
      .notNull()
      .references(() => localProfiles.id, { onDelete: 'cascade' }),
    keyword: text().notNull(),
    capturedOn: date().notNull(),
    latitude: numeric({ precision: 10, scale: 7 }).notNull(),
    longitude: numeric({ precision: 10, scale: 7 }).notNull(),
    position: smallint(),
    creditsSpent: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [
    index('market_local_rank_points_idx').on(t.profileId, t.keyword, t.capturedOn),
  ],
);
