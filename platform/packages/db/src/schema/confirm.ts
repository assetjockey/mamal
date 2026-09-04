import {
  bigint,
  boolean,
  index,
  integer,
  pgTable,
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

/**
 * Confirm: on-site social proof, and off-site web push.
 *
 * Two halves that share one `contacts` spine and one targeting rule model. The
 * widget half is 41 types; the push half is subscribers, segments, campaigns
 * and flows. They are one tool because they answer the same question — "who is
 * on this site, and what should they be shown" — and because a lead captured by
 * a widget is the same person a push campaign later targets.
 */

// ---------------------------------------------------------------------------
// campaigns — one per site, holding the pixel key the runtime authenticates by
// ---------------------------------------------------------------------------

export const confirmCampaigns = pgTable(
  'confirm_campaigns',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    siteId: uuid()
      .notNull()
      .references(() => sites.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    /**
     * The public identifier in the embed snippet. Public by necessity — it ships
     * in a script tag — so it authorises *reading* a widget config and nothing
     * else. `hostAllowlist` is what stops it being lifted onto another site.
     */
    pixelKey: varchar({ length: 32 }).notNull(),
    hostAllowlist: text().array().notNull().default([]),
    brandingRemoved: boolean().notNull().default(false),
    isEnabled: boolean().notNull().default(true),
    /** Denormalised counters; the fact table stays the source of truth. */
    impressions: bigint({ mode: 'number' }).notNull().default(0),
    clicks: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [
    unique('confirm_campaigns_pixel_key').on(t.pixelKey),
    index('confirm_campaigns_workspace_idx').on(t.workspaceId, t.projectId),
    index('confirm_campaigns_site_idx').on(t.siteId),
  ],
);

// ---------------------------------------------------------------------------
// widgets — the 41 types, distinguished by `type` and a schema'd settings blob
// ---------------------------------------------------------------------------

/** Where a widget sits. Eight positions, as in the source products. */
export const WIDGET_POSITIONS = [
  'bottom-left', 'bottom-center', 'bottom-right',
  'top-left', 'top-center', 'top-right',
  'center', 'inline',
] as const;
export type WidgetPosition = (typeof WIDGET_POSITIONS)[number];

export const DISPLAY_FREQUENCIES = ['always', 'once_per_session', 'once_per_hours', 'n_times'] as const;
export type DisplayFrequency = (typeof DISPLAY_FREQUENCIES)[number];

export const confirmWidgets = pgTable(
  'confirm_widgets',
  {
    id: primaryId(),
    workspaceId: ws(),
    campaignId: uuid()
      .notNull()
      .references(() => confirmCampaigns.id, { onDelete: 'cascade' }),
    /** A key from the widget catalogue, e.g. `recent_conversion`. */
    type: varchar({ length: 48 }).notNull(),
    name: text().notNull(),
    /**
     * Design + content + behaviour, validated against the catalogue entry's
     * schema on write. One column rather than 41 tables: the source products
     * proved polymorphism works here, and a column per type would make adding
     * one a migration.
     */
    settings: json().notNull().default({}),
    /** Rule set evaluated in the browser — see `packages/targeting`. */
    targeting: json().notNull().default({}),
    theme: varchar({ length: 32 }).notNull().default('stockholm'),
    position: varchar({ length: 16 }).$type<WidgetPosition>().notNull().default('bottom-left'),
    /** Per-locale overrides of the text fields; falls back to `settings`. */
    translations: json().notNull().default({}),
    displayFrequency: varchar({ length: 24 })
      .$type<DisplayFrequency>()
      .notNull()
      .default('always'),
    displayLimit: integer().notNull().default(0),
    delaySeconds: integer().notNull().default(3),
    durationSeconds: integer().notNull().default(8),
    startsAt: timestamp({ withTimezone: true }),
    endsAt: timestamp({ withTimezone: true }),
    isEnabled: boolean().notNull().default(true),
    sortOrder: integer().notNull().default(0),
    impressions: bigint({ mode: 'number' }).notNull().default(0),
    hovers: bigint({ mode: 'number' }).notNull().default(0),
    clicks: bigint({ mode: 'number' }).notNull().default(0),
    submissions: bigint({ mode: 'number' }).notNull().default(0),
    closes: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [
    index('confirm_widgets_campaign_idx').on(t.campaignId, t.isEnabled),
    index('confirm_widgets_workspace_idx').on(t.workspaceId),
  ],
);

// ---------------------------------------------------------------------------
// conversions — the pool proof widgets draw from
// ---------------------------------------------------------------------------

export const CONVERSION_SOURCES = [
  'form_capture', 'webhook', 'api', 'automation', 'bus', 'manual', 'csv',
] as const;
export type ConversionSource = (typeof CONVERSION_SOURCES)[number];

export const confirmConversions = pgTable(
  'confirm_conversions',
  {
    id: primaryId(),
    workspaceId: ws(),
    campaignId: uuid()
      .notNull()
      .references(() => confirmCampaigns.id, { onDelete: 'cascade' }),
    source: varchar({ length: 24 }).$type<ConversionSource>().notNull(),
    /** Free-form label, e.g. 'purchase', 'signup'. Widgets filter on it. */
    type: varchar({ length: 48 }).notNull().default('conversion'),
    /**
     * Whatever the source supplied — first name, city, product, amount. Widget
     * copy interpolates from here with `{{name}}` style tokens.
     *
     * Deliberately not a fixed set of columns: every source shape in the six
     * products differed, and a widget that says "Someone in Lisbon" needs the
     * city while one that says "Ana bought the Pro plan" needs two other
     * fields.
     */
    data: json().notNull().default({}),
    path: text(),
    pageTitle: text(),
    country: varchar({ length: 2 }),
    city: text(),
    /** Set when the conversion came from another tool, so it can be traced. */
    sourceUrn: text(),
    occurredAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    ...timestamps,
  },
  (t) => [
    // The runtime asks for "the most recent N for this campaign" on every
    // payload build; this is that query.
    index('confirm_conversions_recent_idx').on(t.campaignId, t.occurredAt),
    index('confirm_conversions_workspace_idx').on(t.workspaceId),
  ],
);

// ---------------------------------------------------------------------------
// sources — where conversions come from
// ---------------------------------------------------------------------------

export const SOURCE_KINDS = [
  'form_capture', 'webhook', 'zapier', 'shopify', 'woocommerce', 'stripe', 'bus',
] as const;
export type SourceKind = (typeof SOURCE_KINDS)[number];

export const confirmSources = pgTable(
  'confirm_sources',
  {
    id: primaryId(),
    workspaceId: ws(),
    campaignId: uuid()
      .notNull()
      .references(() => confirmCampaigns.id, { onDelete: 'cascade' }),
    kind: varchar({ length: 24 }).$type<SourceKind>().notNull(),
    name: text().notNull(),
    config: json().notNull().default({}),
    /** HMAC secret for inbound webhooks. Never returned to the browser. */
    secret: text(),
    isEnabled: boolean().notNull().default(true),
    lastReceivedAt: timestamp({ withTimezone: true }),
    receivedCount: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [index('confirm_sources_campaign_idx').on(t.campaignId)],
);

// ---------------------------------------------------------------------------
// push — the 66pusher half
// ---------------------------------------------------------------------------

export const pushWebsites = pgTable(
  'push_websites',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    siteId: uuid()
      .notNull()
      .references(() => sites.id, { onDelete: 'cascade' }),
    /** VAPID pair. The private half is encrypted at rest, like any credential. */
    vapidPublicKey: text().notNull(),
    vapidPrivateKeyEncrypted: text().notNull(),
    serviceWorkerPath: text().notNull().default('/mamal-sw.js'),
    /** button | widget | native — how consent is asked for. */
    promptStyle: varchar({ length: 16 }).notNull().default('widget'),
    promptSettings: json().notNull().default({}),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [
    unique('push_websites_site').on(t.siteId),
    index('push_websites_workspace_idx').on(t.workspaceId),
  ],
);

export const pushSubscribers = pgTable(
  'push_subscribers',
  {
    id: primaryId(),
    workspaceId: ws(),
    pushWebsiteId: uuid()
      .notNull()
      .references(() => pushWebsites.id, { onDelete: 'cascade' }),
    endpoint: text().notNull(),
    p256dh: text().notNull(),
    auth: text().notNull(),
    country: varchar({ length: 2 }),
    browser: varchar({ length: 32 }),
    os: varchar({ length: 32 }),
    device: varchar({ length: 16 }),
    language: varchar({ length: 12 }),
    tags: text().array().notNull().default([]),
    /** active | expired | revoked — a 410 from the push service means expired. */
    status: varchar({ length: 16 }).notNull().default('active'),
    lastSeenAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    subscribedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    ...timestamps,
  },
  (t) => [
    // A browser re-subscribing must update, not duplicate: the endpoint IS the
    // identity, and duplicates mean sending the same person the same push twice.
    unique('push_subscribers_endpoint').on(t.pushWebsiteId, t.endpoint),
    index('push_subscribers_site_status_idx').on(t.pushWebsiteId, t.status),
  ],
);

export const pushSegments = pgTable(
  'push_segments',
  {
    id: primaryId(),
    workspaceId: ws(),
    pushWebsiteId: uuid()
      .notNull()
      .references(() => pushWebsites.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    /** Same rule grammar as widget targeting, over subscriber attributes. */
    filter: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [index('push_segments_site_idx').on(t.pushWebsiteId)],
);

export const pushCampaigns = pgTable(
  'push_campaigns',
  {
    id: primaryId(),
    workspaceId: ws(),
    pushWebsiteId: uuid()
      .notNull()
      .references(() => pushWebsites.id, { onDelete: 'cascade' }),
    segmentId: uuid().references(() => pushSegments.id, { onDelete: 'set null' }),
    title: text().notNull(),
    body: text().notNull(),
    iconUrl: text(),
    imageUrl: text(),
    url: text(),
    actions: json().notNull().default(emptyJsonArray),
    /** draft | scheduled | sending | sent | failed */
    status: varchar({ length: 16 }).notNull().default('draft'),
    scheduledAt: timestamp({ withTimezone: true }),
    sentAt: timestamp({ withTimezone: true }),
    ttlSeconds: integer().notNull().default(86_400),
    /** Recurrence, when this is a repeating campaign rather than a one-off. */
    recurrence: json(),
    nextRunAt: timestamp({ withTimezone: true }),
    sent: integer().notNull().default(0),
    delivered: integer().notNull().default(0),
    clicked: integer().notNull().default(0),
    failed: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [
    index('push_campaigns_site_idx').on(t.pushWebsiteId, t.status),
    // The scheduler claims on this, the same claim-and-enqueue pattern the
    // audit scheduler uses — never a per-campaign repeatable job.
    index('push_campaigns_due_idx').on(t.nextRunAt),
  ],
);

export const pushFlows = pgTable(
  'push_flows',
  {
    id: primaryId(),
    workspaceId: ws(),
    pushWebsiteId: uuid()
      .notNull()
      .references(() => pushWebsites.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    /** subscribe | tag_added | rss | event — what starts the sequence. */
    trigger: varchar({ length: 24 }).notNull(),
    triggerConfig: json().notNull().default({}),
    isEnabled: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('push_flows_site_idx').on(t.pushWebsiteId, t.isEnabled)],
);

export const pushFlowSteps = pgTable(
  'push_flow_steps',
  {
    id: primaryId(),
    workspaceId: ws(),
    flowId: uuid()
      .notNull()
      .references(() => pushFlows.id, { onDelete: 'cascade' }),
    stepOrder: integer().notNull(),
    delaySeconds: integer().notNull().default(0),
    title: text().notNull(),
    body: text().notNull(),
    url: text(),
    /** Continue only if the previous step was opened or clicked. */
    branchOn: varchar({ length: 16 }),
    ...timestamps,
  },
  (t) => [unique('push_flow_steps_order').on(t.flowId, t.stepOrder)],
);

export const pushRssAutomations = pgTable(
  'push_rss_automations',
  {
    id: primaryId(),
    workspaceId: ws(),
    pushWebsiteId: uuid()
      .notNull()
      .references(() => pushWebsites.id, { onDelete: 'cascade' }),
    feedUrl: text().notNull(),
    checkIntervalMinutes: integer().notNull().default(60),
    titleTemplate: text().notNull().default('{{title}}'),
    bodyTemplate: text().notNull().default('{{summary}}'),
    /** The last item already sent, so a feed re-order does not re-notify. */
    lastGuid: text(),
    isEnabled: boolean().notNull().default(true),
    nextCheckAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    ...timestamps,
  },
  (t) => [index('push_rss_due_idx').on(t.nextCheckAt, t.isEnabled)],
);

/**
 * Where each subscriber is in each flow.
 *
 * Per subscriber, not per flow: someone who subscribes today must start at step
 * one regardless of when the flow was created. Tracking only a flow-level
 * position would send step four to a person who never received steps one to
 * three, which is the classic drip-sequence bug.
 */
export const pushFlowProgress = pgTable(
  'push_flow_progress',
  {
    id: primaryId(),
    workspaceId: ws(),
    flowId: uuid()
      .notNull()
      .references(() => pushFlows.id, { onDelete: 'cascade' }),
    subscriberId: uuid()
      .notNull()
      .references(() => pushSubscribers.id, { onDelete: 'cascade' }),
    /** The step that fires next. */
    nextStep: integer().notNull(),
    dueAt: timestamp({ withTimezone: true }).notNull(),
    /** Set rather than deleted, so re-enrolling is a deliberate act. */
    completedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    // One enrolment per person per flow — enrolling twice would double every
    // remaining step.
    unique('push_flow_progress_once').on(t.flowId, t.subscriberId),
    index('push_flow_progress_due_idx').on(t.dueAt, t.completedAt),
  ],
);
