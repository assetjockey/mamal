import {
  bigint,
  boolean,
  index,
  integer,
  pgTable,
  text,
  timestamp,
  unique,
  uniqueIndex,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { sql } from 'drizzle-orm';
import { json, primaryId, softDelete, timestamps } from './_shared.ts';
import { projects, workspaces } from './tenancy.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });
const pr = () =>
  uuid()
    .notNull()
    .references(() => projects.id, { onDelete: 'cascade' });

// ---------------------------------------------------------------------------
// sites — ONE row per hostname you own. Core-owned, not per-tool.
// Audit / Track / Monitor each keep a thin profile keyed by siteId.
// ---------------------------------------------------------------------------

export const sites = pgTable(
  'sites',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /** Normalized: lowercase, no scheme, no leading `www.`, no trailing slash. */
    host: varchar({ length: 253 }).notNull(),
    rootUrl: text().notNull(),
    displayName: text(),
    faviconUrl: text(),
    verifiedAt: timestamp({ withTimezone: true }),
    verificationMethod: varchar({ length: 24 }),
    verificationToken: text(),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    unique('sites_host_key').on(t.workspaceId, t.host),
    index('sites_project_idx').on(t.projectId),
  ],
);

// ---------------------------------------------------------------------------
// TWO domain nouns, deliberately split.
//   domain_names  = an asset you WATCH   (WHOIS/SSL expiry, NS changes)
//   custom_domains = a vhost you SERVE FROM
// Every source product conflated these and every one has bugs from it.
// ---------------------------------------------------------------------------

export const domainNames = pgTable(
  'domain_names',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    apex: varchar({ length: 253 }).notNull(),
    registrar: text(),
    whois: json(),
    nameservers: text().array().notNull().default([]),
    expiresAt: timestamp({ withTimezone: true }),
    sslExpiresAt: timestamp({ withTimezone: true }),
    lastCheckedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [unique('domain_names_key').on(t.workspaceId, t.apex)],
);

export const CUSTOM_DOMAIN_KINDS = [
  'link',
  'biolink',
  'status',
  'transfer',
  'report',
  'email',
] as const;
export type CustomDomainKind = (typeof CUSTOM_DOMAIN_KINDS)[number];

export const customDomains = pgTable(
  'custom_domains',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    host: varchar({ length: 253 }).notNull(),
    kind: varchar({ length: 16 }).$type<CustomDomainKind>().notNull().default('link'),
    verificationToken: text().notNull(),
    dnsStatus: varchar({ length: 16 }).notNull().default('pending'),
    sslStatus: varchar({ length: 16 }).notNull().default('pending'),
    /** Cloudflare for SaaS custom hostname id — a per-hostname cash cost, never free-tier. */
    cfHostnameId: text(),
    /** Throttles the sweep: DNS providers rate-limit, and a second lookup in
        the same minute answers nothing the first did not. */
    dnsCheckedAt: timestamp({ withTimezone: true }),
    /**
     * What the last lookup actually saw.
     *
     * Kept because "pending" on its own is unactionable: a customer who has
     * added a CNAME and a TXT and still sees pending needs to know which one we
     * cannot find, and support needs it without asking them to run `dig`.
     */
    lastCheck: json().notNull().default({}),
    isPrimary: boolean().notNull().default(false),
    homepageUrl: text(),
    notFoundUrl: text(),
    verifiedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('custom_domains_host_key').on(t.host),
    index('custom_domains_workspace_idx').on(t.workspaceId, t.kind),
  ],
);

// ---------------------------------------------------------------------------
// links — one polymorphic table. 66biolinks already proved polymorphism works.
// ---------------------------------------------------------------------------

export const LINK_KINDS = [
  'short',
  'biolink',
  'file',
  'transfer',
  'static',
  'vcard',
  'event',
  'qr',
  'splash',
] as const;
export type LinkKind = (typeof LINK_KINDS)[number];

export type LinkSettings = {
  cloak?: { title?: string; description?: string; favicon?: string; customJs?: string };
  deepLink?: { ios?: string; android?: string; fallback?: string };
  splashPageId?: string;
  forwardQuery?: boolean;
  sensitiveContent?: boolean;
  utm?: Record<string, string>;
};

export const links = pgTable(
  'links',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /** NULL = the platform domain. See the partial indexes below for why. */
    customDomainId: uuid().references(() => customDomains.id, { onDelete: 'set null' }),
    folderId: uuid(),
    kind: varchar({ length: 16 }).$type<LinkKind>().notNull().default('short'),
    alias: varchar({ length: 255 }).notNull(),
    destinationUrl: text(),
    title: text(),
    description: text(),
    imageUrl: text(),
    passwordHash: text(),
    isEnabled: boolean().notNull().default(true),
    expiresAt: timestamp({ withTimezone: true }),
    expiresUrl: text(),
    maxClicks: integer(),
    tags: text().array().notNull().default([]),
    campaign: varchar({ length: 160 }),
    settings: json<LinkSettings>().notNull().default({}),
    /** Denormalized counter — authoritative counts live in ClickHouse. */
    clicksCount: bigint({ mode: 'number' }).notNull().default(0),
    lastClickedAt: timestamp({ withTimezone: true }),
    moderationStatus: varchar({ length: 16 }).notNull().default('ok'),
    ...timestamps,
    ...softDelete,
  },
  /*
   * Two partial unique indexes, not one plain constraint.
   *
   * A plain `unique(custom_domain_id, alias)` does not hold on the platform
   * domain, because Postgres treats NULLs as distinct: two rows of
   * `(NULL, 'promo')` both insert, and the redirect then resolves to whichever
   * one the planner happens to return. The bug is silent and it hands one
   * workspace another's traffic.
   *
   * `deleted_at is null` is on both on purpose: deleting a link should give its
   * alias back. Without it a customer cannot re-create the link they just
   * removed, which reads as a bug every time.
   */
  (t) => [
    uniqueIndex('links_alias_domain_key')
      .on(t.customDomainId, t.alias)
      .where(sql`custom_domain_id is not null and deleted_at is null`),
    uniqueIndex('links_alias_platform_key')
      .on(t.alias)
      .where(sql`custom_domain_id is null and deleted_at is null`),
    index('links_workspace_idx').on(t.workspaceId, t.kind),
    index('links_project_idx').on(t.projectId),
  ],
);

/**
 * Ordered targeting rules, evaluated at the edge. First match wins.
 * Union of phpshort's targets_type/targets and linkqr's redirect_rules.
 */
export type LinkRuleMatch = {
  continent?: string[];
  country?: string[];
  region?: string[];
  city?: string[];
  os?: string[];
  browser?: string[];
  device?: string[];
  language?: string[];
  referrerRegex?: string;
  utm?: Record<string, string>;
  dateRange?: { from?: string; to?: string };
  timeOfDay?: { from: string; to: string; tz: string };
};

export type LinkRuleAction =
  | { type: 'redirect'; destinationUrl: string }
  | { type: 'rotate'; variants: { url: string; weight: number; isWinner?: boolean }[] }
  | { type: 'block' };

export const linkRules = pgTable(
  'link_rules',
  {
    id: primaryId(),
    workspaceId: ws(),
    linkId: uuid()
      .notNull()
      .references(() => links.id, { onDelete: 'cascade' }),
    priority: integer().notNull().default(0),
    match: json<LinkRuleMatch>().notNull().default({}),
    action: json<LinkRuleAction>().notNull(),
    /** Sticky rotation assignment is keyed on visitorId, not a cookie. */
    sticky: boolean().notNull().default(true),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [index('link_rules_link_idx').on(t.linkId, t.priority)],
);

// ---------------------------------------------------------------------------
// pixels — one table + attachment join, so a pixel fires on a biolink,
// a short link AND a status page with zero new code.
// ---------------------------------------------------------------------------

export const pixels = pgTable(
  'pixels',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    provider: varchar({ length: 32 }).notNull(),
    name: text().notNull(),
    externalId: text(),
    script: text(),
    consentRequired: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [unique('pixels_name_key').on(t.workspaceId, t.name)],
);

export const pixelAttachments = pgTable(
  'pixel_attachments',
  {
    id: primaryId(),
    workspaceId: ws(),
    pixelId: uuid()
      .notNull()
      .references(() => pixels.id, { onDelete: 'cascade' }),
    /** Points at resources.id — that's what makes this work across tools. */
    resourceId: uuid().notNull(),
    isEnabled: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [unique('pixel_attachments_key').on(t.pixelId, t.resourceId)],
);

// ---------------------------------------------------------------------------
// notification_channels — 17 transports, shared by every tool.
// ---------------------------------------------------------------------------

export const CHANNEL_TRANSPORTS = [
  'email',
  'webhook',
  'slack',
  'discord',
  'telegram',
  'teams',
  'google_chat',
  'matrix',
  'flock',
  'ntfy',
  'gotify',
  'pushover',
  'sms',
  'voice',
  'whatsapp',
  'webpush',
  'pagerduty',
  'opsgenie',
] as const;
export type ChannelTransport = (typeof CHANNEL_TRANSPORTS)[number];

export const notificationChannels = pgTable(
  'notification_channels',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    transport: varchar({ length: 24 }).$type<ChannelTransport>().notNull(),
    name: text().notNull(),
    /** Encrypted at rest — webhook secrets, bot tokens, phone numbers. */
    config: text().notNull(),
    isEnabled: boolean().notNull().default(true),
    verifiedAt: timestamp({ withTimezone: true }),
    failureCount: integer().notNull().default(0),
    lastError: text(),
    ...timestamps,
  },
  (t) => [index('notification_channels_workspace_idx').on(t.workspaceId, t.transport)],
);

/**
 * unique(channelId, dedupeKey) is what stops one incident from sending
 * forty Slack messages.
 */
export const notificationDeliveries = pgTable(
  'notification_deliveries',
  {
    id: primaryId(),
    workspaceId: ws(),
    channelId: uuid()
      .notNull()
      .references(() => notificationChannels.id, { onDelete: 'cascade' }),
    templateKey: varchar({ length: 64 }).notNull(),
    dedupeKey: varchar({ length: 128 }),
    eventId: uuid(),
    status: varchar({ length: 16 }).notNull().default('pending'),
    attempts: integer().notNull().default(0),
    error: text(),
    sentAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('notification_deliveries_dedupe_key').on(t.channelId, t.dedupeKey),
    index('notification_deliveries_workspace_idx').on(t.workspaceId, t.createdAt),
  ],
);

// ---------------------------------------------------------------------------
// contacts — the sleeper unification. Biolink leads, newsletter subscribers,
// transfer recipients, Confirm collectors and Market audiences are all
// "people who touched this workspace". Consent is per-contact, with a trail.
// ---------------------------------------------------------------------------

export type ContactConsent = {
  marketing: boolean;
  at?: string;
  ip?: string;
  method?: 'form' | 'import' | 'api' | 'double_opt_in';
};

export const contacts = pgTable(
  'contacts',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    email: varchar({ length: 320 }),
    phone: varchar({ length: 32 }),
    name: text(),
    avatarUrl: text(),
    /** Which tool/object first captured them. */
    sourceUrn: text(),
    consent: json<ContactConsent>().notNull().default({ marketing: false }),
    attributes: json().notNull().default({}),
    firstSeenAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    lastSeenAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    unsubscribedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('contacts_email_key').on(t.workspaceId, t.email),
    index('contacts_project_idx').on(t.projectId),
  ],
);

export const contactLists = pgTable(
  'contact_lists',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    description: text(),
    doubleOptIn: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('contact_lists_workspace_idx').on(t.workspaceId)],
);

export const contactListMembers = pgTable(
  'contact_list_members',
  {
    id: primaryId(),
    workspaceId: ws(),
    listId: uuid()
      .notNull()
      .references(() => contactLists.id, { onDelete: 'cascade' }),
    contactId: uuid()
      .notNull()
      .references(() => contacts.id, { onDelete: 'cascade' }),
    status: varchar({ length: 16 }).notNull().default('subscribed'),
    ...timestamps,
  },
  (t) => [unique('contact_list_members_key').on(t.listId, t.contactId)],
);

// ---------------------------------------------------------------------------
// assets — one R2-backed store for transfers, creatives, replays, exports.
// ---------------------------------------------------------------------------

export const assets = pgTable(
  'assets',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    kind: varchar({ length: 32 }).notNull(),
    storageKey: text().notNull(),
    bucket: varchar({ length: 64 }).notNull().default('default'),
    filename: text().notNull(),
    mimeType: varchar({ length: 128 }),
    sizeBytes: bigint({ mode: 'number' }).notNull().default(0),
    checksumSha256: varchar({ length: 64 }),
    encryption: varchar({ length: 24 }).notNull().default('none'),
    encryptedDek: text(),
    width: integer(),
    height: integer(),
    durationMs: integer(),
    sourceUrn: text(),
    expiresAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    index('assets_workspace_idx').on(t.workspaceId, t.kind),
    index('assets_checksum_idx').on(t.workspaceId, t.checksumSha256),
  ],
);

// ---------------------------------------------------------------------------
// goals / annotations / tags — cross-tool by construction
// ---------------------------------------------------------------------------

export const goals = pgTable(
  'goals',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    siteId: uuid().references(() => sites.id, { onDelete: 'cascade' }),
    key: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    matchKind: varchar({ length: 16 }).notNull().default('pageview'),
    match: json().notNull().default({}),
    valueCents: integer().notNull().default(0),
    currency: varchar({ length: 3 }).notNull().default('USD'),
    ...timestamps,
  },
  (t) => [unique('goals_key').on(t.workspaceId, t.siteId, t.key)],
);

/** An annotation carries a resourceUrn, so Monitor downtime and Audit runs both land on Track's charts. */
export const annotations = pgTable(
  'annotations',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    resourceUrn: text(),
    occurredAt: timestamp({ withTimezone: true }).notNull(),
    kind: varchar({ length: 24 }).notNull().default('note'),
    text: text().notNull(),
    ...timestamps,
  },
  (t) => [index('annotations_workspace_idx').on(t.workspaceId, t.occurredAt)],
);

export const tags = pgTable(
  'tags',
  {
    id: primaryId(),
    workspaceId: ws(),
    name: varchar({ length: 64 }).notNull(),
    color: varchar({ length: 16 }).notNull().default('slate'),
    ...timestamps,
  },
  (t) => [unique('tags_name_key').on(t.workspaceId, t.name)],
);

export const taggables = pgTable(
  'taggables',
  {
    id: primaryId(),
    workspaceId: ws(),
    tagId: uuid()
      .notNull()
      .references(() => tags.id, { onDelete: 'cascade' }),
    resourceId: uuid().notNull(),
    ...timestamps,
  },
  (t) => [unique('taggables_key').on(t.tagId, t.resourceId)],
);
