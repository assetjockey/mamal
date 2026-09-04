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
import { json, primaryId, softDelete, timestamps } from './_shared.ts';
import { projects, workspaces } from './tenancy.ts';
import { links, linkRules } from './core.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });
const pr = () =>
  uuid()
    .notNull()
    .references(() => projects.id, { onDelete: 'cascade' });

/**
 * Link's own tables.
 *
 * `links`, `link_rules`, `custom_domains`, `pixels` and `assets` are **core**,
 * not Link's — a short link is a resource other tools address, a custom domain
 * serves status pages and transfer downloads too, and a pixel fires on a
 * biolink, a short link and a status page from one attachment table. Phase 0 put
 * them in `core.ts` deliberately. What lives here is the rest: the shapes only
 * this tool knows about.
 */

// ---------------------------------------------------------------------------
// folders and presets
// ---------------------------------------------------------------------------

export const linkFolders = pgTable(
  'link_folders',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    color: varchar({ length: 16 }),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('link_folders_workspace_idx').on(t.workspaceId, t.projectId)],
);

export const utmPresets = pgTable(
  'utm_presets',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    values: json().notNull().default({}),
    /** Applied to every new link in this project without being chosen. */
    autoApply: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('utm_presets_workspace_idx').on(t.workspaceId, t.projectId)],
);

/** The interstitial a link can be sent through before its destination. */
export const splashPages = pgTable(
  'splash_pages',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    settings: json().notNull().default({}),
    delaySeconds: integer().notNull().default(5),
    /**
     * Whether the visitor can skip.
     *
     * Default true, and the UI says why: an unskippable interstitial on a link
     * someone clicked from a search result is the pattern that gets a domain
     * flagged, not a monetisation strategy.
     */
    isSkippable: boolean().notNull().default(true),
    autoRedirect: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [index('splash_pages_workspace_idx').on(t.workspaceId, t.projectId)],
);

/**
 * Sticky A/B assignment.
 *
 * Without this a rotation is not a test: a visitor who lands on variant B and
 * refreshes onto A has been counted twice and converted once, and the result is
 * noise. Keyed by the daily-rotating visitor hash, so it identifies a session
 * without identifying a person — and expires with it.
 */
export const linkAssignments = pgTable(
  'link_assignments',
  {
    id: primaryId(),
    workspaceId: ws(),
    linkId: uuid()
      .notNull()
      .references(() => links.id, { onDelete: 'cascade' }),
    ruleId: uuid()
      .notNull()
      .references(() => linkRules.id, { onDelete: 'cascade' }),
    visitorHash: varchar({ length: 32 }).notNull(),
    variantIndex: integer().notNull(),
    expiresAt: timestamp({ withTimezone: true }).notNull(),
    ...timestamps,
  },
  (t) => [
    unique('link_assignments_visitor').on(t.ruleId, t.visitorHash),
    index('link_assignments_expiry_idx').on(t.expiresAt),
  ],
);

// ---------------------------------------------------------------------------
// bio pages — 82 block types in one polymorphic table
// ---------------------------------------------------------------------------

export const bioPages = pgTable(
  'bio_pages',
  {
    id: primaryId(),
    workspaceId: ws(),
    linkId: uuid()
      .notNull()
      .references(() => links.id, { onDelete: 'cascade' }),
    template: varchar({ length: 48 }).notNull().default('plain'),
    /** Background, buttons, fonts, radius — the whole look, as values. */
    theme: json().notNull().default({}),
    seo: json().notNull().default({}),
    customCss: text(),
    isPublished: boolean().notNull().default(false),
    /** Points at the page this is a variant of, for A/B. */
    abVariantOf: uuid(),
    views: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [
    unique('bio_pages_link').on(t.linkId),
    index('bio_pages_workspace_idx').on(t.workspaceId),
  ],
);

export const bioBlocks = pgTable(
  'bio_blocks',
  {
    id: primaryId(),
    workspaceId: ws(),
    pageId: uuid()
      .notNull()
      .references(() => bioPages.id, { onDelete: 'cascade' }),
    /** A key from the block catalogue. */
    type: varchar({ length: 48 }).notNull(),
    sortOrder: integer().notNull().default(0),
    settings: json().notNull().default({}),
    /** A block can appear only within a window — a launch banner, say. */
    startsAt: timestamp({ withTimezone: true }),
    endsAt: timestamp({ withTimezone: true }),
    isEnabled: boolean().notNull().default(true),
    clicks: bigint({ mode: 'number' }).notNull().default(0),
    ...timestamps,
  },
  (t) => [index('bio_blocks_page_idx').on(t.pageId, t.sortOrder)],
);

// ---------------------------------------------------------------------------
// QR codes and barcodes
// ---------------------------------------------------------------------------

export const qrCodes = pgTable(
  'qr_codes',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /**
     * Null for a static code.
     *
     * The distinction that matters commercially: a dynamic code points at a
     * link whose destination can change after the code is printed. A static one
     * encodes its payload directly and is unchangeable forever — fine for wifi
     * credentials, fatal for a campaign URL on a poster.
     */
    linkId: uuid().references(() => links.id, { onDelete: 'cascade' }),
    /** A key from the QR catalogue: url, wifi, vcard, upi, … */
    type: varchar({ length: 32 }).notNull(),
    name: text().notNull(),
    /** Type-specific fields, validated against the catalogue entry. */
    payload: json().notNull().default({}),
    /** Body pattern, eye shapes, frame, logo, gradient, error correction. */
    style: json().notNull().default({}),
    batchId: uuid(),
    scans: bigint({ mode: 'number' }).notNull().default(0),
    lastScannedAt: timestamp({ withTimezone: true }),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    index('qr_codes_workspace_idx').on(t.workspaceId, t.projectId),
    index('qr_codes_batch_idx').on(t.batchId),
  ],
);

export const qrBatches = pgTable(
  'qr_batches',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    name: text().notNull(),
    count: integer().notNull().default(0),
    slugPrefix: varchar({ length: 32 }),
    /**
     * Codes minted before their destinations are known — printed on packaging,
     * assigned later. The reason a *dynamic* code may also have no link yet.
     */
    prePrinted: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('qr_batches_workspace_idx').on(t.workspaceId)],
);

export const barcodes = pgTable(
  'barcodes',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    /** One of 29 symbologies — ean13, code128, itf14, … */
    symbology: varchar({ length: 24 }).notNull(),
    value: text().notNull(),
    style: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [index('barcodes_workspace_idx').on(t.workspaceId)],
);

// ---------------------------------------------------------------------------
// transfers
// ---------------------------------------------------------------------------

export const TRANSFER_KINDS = ['send', 'request'] as const;
export type TransferKind = (typeof TRANSFER_KINDS)[number];

export const transfers = pgTable(
  'transfers',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: pr(),
    linkId: uuid().references(() => links.id, { onDelete: 'set null' }),
    kind: varchar({ length: 16 }).$type<TransferKind>().notNull().default('send'),
    /** link | email — whether recipients are notified or just given a URL. */
    delivery: varchar({ length: 16 }).notNull().default('link'),
    senderName: text(),
    senderEmail: text(),
    recipients: text().array().notNull().default([]),
    subject: text(),
    message: text(),
    passwordHash: text(),
    /** AES-256-GCM at rest, per transfer. */
    isEncrypted: boolean().notNull().default(false),
    /**
     * This transfer's data key, wrapped with the instance key.
     *
     * Per transfer rather than per instance so that deleting the row makes the
     * bytes unrecoverable immediately — which is what "delete my data" has to
     * mean when the object store's own deletion is eventually-consistent.
     */
    dataKeyWrapped: text(),
    expiresAt: timestamp({ withTimezone: true }),
    downloadLimit: integer().notNull().default(0),
    downloads: integer().notNull().default(0),
    totalFiles: integer().notNull().default(0),
    totalBytes: bigint({ mode: 'number' }).notNull().default(0),
    /** Branded share page: background, template, logo. */
    branding: json().notNull().default({}),
    notifyOnDownload: boolean().notNull().default(true),
    notifyOnExpiry: boolean().notNull().default(false),
    /** Set when the sender pulls it back, with their reason. */
    cancelledAt: timestamp({ withTimezone: true }),
    cancelReason: text(),
    /** pending | ready | expired | cancelled */
    status: varchar({ length: 16 }).notNull().default('pending'),
    ...timestamps,
  },
  (t) => [
    index('transfers_workspace_idx').on(t.workspaceId, t.status),
    index('transfers_expiry_idx').on(t.expiresAt),
  ],
);

export const transferFiles = pgTable(
  'transfer_files',
  {
    id: primaryId(),
    workspaceId: ws(),
    transferId: uuid()
      .notNull()
      .references(() => transfers.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    sizeBytes: bigint({ mode: 'number' }).notNull(),
    mimeType: varchar({ length: 128 }),
    /** Object key in the storage provider. */
    storageKey: text().notNull(),
    checksumSha256: varchar({ length: 64 }),
    /**
     * Which multipart chunks have landed.
     *
     * The entire resumability contract: a 5 GB upload over hotel wifi will
     * drop, and the client's recovery has to be "carry on from part 41", not
     * "start again". Stored as a set rather than a high-water mark because an
     * interrupted *parallel* upload leaves holes, not a clean truncation.
     */
    parts: integer().array().notNull().default([]),
    /**
     * The provider's multipart identifier, and the ETag it returned per part.
     *
     * S3-compatible providers will not assemble an object without the exact
     * ETags they issued, in order — so they have to survive a client that goes
     * away between part 3 and part 4 and comes back an hour later. Holding them
     * in memory would make resumability a property of process uptime.
     */
    uploadId: text(),
    partEtags: json<Record<string, string>>().notNull().default({}),
    uploadedAt: timestamp({ withTimezone: true }),
    sortOrder: integer().notNull().default(0),
    downloads: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('transfer_files_transfer_idx').on(t.transferId, t.sortOrder)],
);

/**
 * Storage providers as rows, not as a deploy.
 *
 * `swipgle`'s best idea: an operator adds Wasabi or Backblaze by filling in a
 * form rather than shipping code. The handler name selects the adapter; the
 * credentials are encrypted like any other secret. A null workspace means an
 * instance-wide provider.
 */
export const storageProviders = pgTable(
  'storage_providers',
  {
    id: primaryId(),
    workspaceId: uuid().references(() => workspaces.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    /** r2 | s3 | wasabi | backblaze | local */
    handler: varchar({ length: 24 }).notNull(),
    credentialsEncrypted: text(),
    config: json().notNull().default({}),
    isEnabled: boolean().notNull().default(true),
    isDefault: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [index('storage_providers_workspace_idx').on(t.workspaceId)],
);

// ---------------------------------------------------------------------------
// cross-tool suggestions
// ---------------------------------------------------------------------------

/**
 * A link another tool thinks should exist.
 *
 * Audit finds a broken external link; the useful answer is "replace it with a
 * managed link you can re-point later without editing the site again". But
 * *creating* it would spend the customer's link allowance on a decision they
 * have not made, and a large site can produce hundreds of these in one crawl.
 * So the handoff lands here and surfaces as one button in the issue's fix
 * panel, which is where the choice belongs.
 */
export const linkSuggestions = pgTable(
  'link_suggestions',
  {
    id: primaryId(),
    workspaceId: ws(),
    /** replace_broken_external | shorten_published_url | qr_for_print */
    kind: varchar({ length: 32 }).notNull(),
    targetUrl: text().notNull(),
    /** Where it was found — the page carrying the broken link, say. */
    contextUrl: text(),
    /** The resource whose event raised this, so it can be traced back. */
    sourceUrn: text(),
    /** open | accepted | dismissed */
    status: varchar({ length: 16 }).notNull().default('open'),
    createdLinkId: uuid().references(() => links.id, { onDelete: 'set null' }),
    ...timestamps,
  },
  (t) => [
    // One suggestion per target, so a nightly crawl does not re-file the same
    // broken link every night until the queue is unusable.
    unique('link_suggestions_key').on(t.workspaceId, t.kind, t.targetUrl),
    index('link_suggestions_status_idx').on(t.workspaceId, t.status),
  ],
);

// ---------------------------------------------------------------------------
// abuse
// ---------------------------------------------------------------------------

export const abuseReports = pgTable(
  'abuse_reports',
  {
    id: primaryId(),
    /**
     * Workspace-scoped so it appears in the owner's moderation queue, but the
     * *reporter* is outside the tenant — the report arrives from a stranger who
     * followed a link, which is why there is no user reference.
     */
    workspaceId: ws(),
    linkId: uuid()
      .notNull()
      .references(() => links.id, { onDelete: 'cascade' }),
    reporterEmail: text(),
    reason: varchar({ length: 32 }).notNull(),
    detail: text(),
    /** open | upheld | dismissed */
    status: varchar({ length: 16 }).notNull().default('open'),
    reviewedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('abuse_reports_status_idx').on(t.workspaceId, t.status, t.createdAt)],
);
