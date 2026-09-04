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
import { emptyJsonArray, json, primaryId, softDelete, timestamps } from './_shared.ts';
import { workspaces } from './tenancy.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });

// ---------------------------------------------------------------------------
// features — the registry that entitlements are written against.
// A new feature appears with mode='deny' on every plan: opt-in, never
// silently free.
// ---------------------------------------------------------------------------

export const FEATURE_KINDS = ['boolean', 'limit', 'quota', 'metered'] as const;
export type FeatureKind = (typeof FEATURE_KINDS)[number];

export const COST_UNITS = [
  'call',
  'image',
  'video_second',
  '1k_input_tokens',
  '1k_output_tokens',
  '1k_words',
  'check',
  'pageview',
  'crawl_page',
  'gb_month',
  'message',
] as const;
export type CostUnit = (typeof COST_UNITS)[number];

export const features = pgTable(
  'features',
  {
    key: varchar({ length: 96 }).primaryKey(),
    tool: varchar({ length: 32 }).notNull(),
    name: text().notNull(),
    description: text(),
    category: varchar({ length: 48 }),
    kind: varchar({ length: 16 }).$type<FeatureKind>().notNull(),
    /** Drives lifetime exclusion AND the AI master kill switch. */
    isAi: boolean().notNull().default(false),
    /**
     * False for anything touching a metered vendor (DataForSEO, AI providers,
     * Twilio, CF-for-SaaS hostnames). Enforced by the resolver, not the UI.
     */
    freeTierAllowed: boolean().notNull().default(false),
    defaultCreditCost: integer().notNull().default(0),
    unit: varchar({ length: 24 }).$type<CostUnit>(),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('features_tool_idx').on(t.tool)],
);

// ---------------------------------------------------------------------------
// plans — per-tool, unified, lifetime, free
// ---------------------------------------------------------------------------

export const PLAN_KINDS = ['free', 'tool', 'unified', 'lifetime', 'custom'] as const;
export type PlanKind = (typeof PLAN_KINDS)[number];

export const plans = pgTable(
  'plans',
  {
    id: primaryId(),
    key: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    description: text(),
    kind: varchar({ length: 16 }).$type<PlanKind>().notNull(),
    /** Set only when kind='tool'. */
    tool: varchar({ length: 32 }),
    tierRank: integer().notNull().default(0),
    status: varchar({ length: 16 }).notNull().default('active'),
    isPublic: boolean().notNull().default(true),
    isDefaultSignup: boolean().notNull().default(false),
    trialDays: integer().notNull().default(0),
    sortOrder: integer().notNull().default(0),
    marketing: json().notNull().default({}),
    ...timestamps,
    ...softDelete,
  },
  (t) => [unique('plans_key').on(t.key), index('plans_kind_idx').on(t.kind, t.tool)],
);

export const planPrices = pgTable(
  'plan_prices',
  {
    id: primaryId(),
    planId: uuid()
      .notNull()
      .references(() => plans.id, { onDelete: 'cascade' }),
    interval: varchar({ length: 16 }).notNull(), // month | quarter | year | once
    currency: varchar({ length: 3 }).notNull().default('USD'),
    amountCents: integer().notNull(),
    gatewayPriceIds: json().notNull().default({}),
    isActive: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [unique('plan_prices_key').on(t.planId, t.interval, t.currency)],
);

/**
 * The admin-CRUD surface. Append-only with effectiveFrom, so existing
 * subscribers grandfather cleanly.
 *
 * A database trigger (see migrations) forbids rows on a lifetime plan where
 * features.isAi = true and mode != 'deny'. Lifetime AI exclusion cannot be
 * mis-configured in admin.
 */
export const ENTITLEMENT_MODES = ['allow', 'deny', 'limit', 'quota', 'credits'] as const;
export type EntitlementMode = (typeof ENTITLEMENT_MODES)[number];

export const OVERAGE_BEHAVIOURS = ['block', 'credits', 'soft'] as const;
export type OverageBehaviour = (typeof OVERAGE_BEHAVIOURS)[number];

export const planEntitlements = pgTable(
  'plan_entitlements',
  {
    id: primaryId(),
    planId: uuid()
      .notNull()
      .references(() => plans.id, { onDelete: 'cascade' }),
    featureKey: varchar({ length: 96 })
      .notNull()
      .references(() => features.key, { onDelete: 'cascade' }),
    mode: varchar({ length: 16 }).$type<EntitlementMode>().notNull(),
    /** mode='limit' — concurrent/total. -1 = unlimited. Merged with MAX. */
    limitValue: bigint({ mode: 'number' }),
    /** mode='quota' — per period. Merged with SUM. */
    quotaValue: bigint({ mode: 'number' }),
    quotaPeriod: varchar({ length: 16 }).default('month'),
    /** mode='credits' — per-plan override of features.defaultCreditCost. */
    creditCost: integer(),
    overage: varchar({ length: 16 }).$type<OverageBehaviour>().notNull().default('block'),
    config: json().notNull().default({}),
    effectiveFrom: timestamp({ withTimezone: true }).notNull().defaultNow(),
    ...timestamps,
  },
  (t) => [
    unique('plan_entitlements_key').on(t.planId, t.featureKey, t.effectiveFrom),
    index('plan_entitlements_plan_idx').on(t.planId),
  ],
);

export const planCreditGrants = pgTable(
  'plan_credit_grants',
  {
    id: primaryId(),
    planId: uuid()
      .notNull()
      .references(() => plans.id, { onDelete: 'cascade' }),
    amount: integer().notNull(),
    cadence: varchar({ length: 16 }).notNull().default('per_period'), // per_period | once
    expiresAfterDays: integer(),
    rollover: boolean().notNull().default(false),
    ...timestamps,
  },
  (t) => [unique('plan_credit_grants_key').on(t.planId, t.cadence)],
);

// ---------------------------------------------------------------------------
// subscriptions — a workspace may hold several at once (tool + unified + lifetime)
// ---------------------------------------------------------------------------

export const subscriptions = pgTable(
  'subscriptions',
  {
    id: primaryId(),
    workspaceId: ws(),
    planId: uuid()
      .notNull()
      .references(() => plans.id, { onDelete: 'restrict' }),
    /** Frozen copy of the plan at purchase time, for grandfathering. */
    planSnapshot: json().notNull().default({}),
    status: varchar({ length: 16 }).notNull().default('active'),
    interval: varchar({ length: 16 }).notNull().default('month'),
    gateway: varchar({ length: 32 }),
    gatewaySubscriptionId: text(),
    currentPeriodStart: timestamp({ withTimezone: true }),
    currentPeriodEnd: timestamp({ withTimezone: true }),
    trialEndsAt: timestamp({ withTimezone: true }),
    cancelAt: timestamp({ withTimezone: true }),
    seats: integer().notNull().default(1),
    ...timestamps,
  },
  (t) => [
    index('subscriptions_workspace_idx').on(t.workspaceId, t.status),
    unique('subscriptions_gateway_key').on(t.gateway, t.gatewaySubscriptionId),
  ],
);

// ---------------------------------------------------------------------------
// credits — FIFO expiring buckets + holds. Charging is a hold, not a debit.
// ---------------------------------------------------------------------------

export const CREDIT_SOURCES = [
  'plan_grant',
  'purchase',
  'bonus',
  'refund',
  'admin',
  'affiliate',
  'trial',
] as const;
export type CreditSource = (typeof CREDIT_SOURCES)[number];

export const creditBuckets = pgTable(
  'credit_buckets',
  {
    id: primaryId(),
    workspaceId: ws(),
    source: varchar({ length: 24 }).$type<CreditSource>().notNull(),
    sourceRef: text(),
    amount: integer().notNull(),
    /** Spend order: expiresAt ASC NULLS LAST, grantedAt ASC. */
    remaining: integer().notNull(),
    grantedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    expiresAt: timestamp({ withTimezone: true }),
    metadata: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [index('credit_buckets_spend_idx').on(t.workspaceId, t.expiresAt, t.grantedAt)],
);

export const creditHolds = pgTable(
  'credit_holds',
  {
    id: primaryId(),
    workspaceId: ws(),
    amount: integer().notNull(),
    featureKey: varchar({ length: 96 }).notNull(),
    jobId: text(),
    status: varchar({ length: 16 }).notNull().default('held'), // held | captured | released
    /** Which buckets were drawn from, so release restores expiry correctly. */
    bucketDraws: json<{ bucketId: string; amount: number }[]>().notNull().default(emptyJsonArray),
    expiresAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('credit_holds_workspace_idx').on(t.workspaceId, t.status)],
);

export const creditEntries = pgTable(
  'credit_entries',
  {
    id: primaryId(),
    workspaceId: ws(),
    bucketId: uuid().references(() => creditBuckets.id, { onDelete: 'set null' }),
    holdId: uuid().references(() => creditHolds.id, { onDelete: 'set null' }),
    /** Negative = spend, positive = grant. */
    delta: integer().notNull(),
    balanceAfter: integer().notNull(),
    featureKey: varchar({ length: 96 }),
    resourceUrn: text(),
    quantity: integer(),
    unitCost: integer(),
    /** `<jobId>:<stable-suffix>` — a retried job cannot double-charge. */
    idempotencyKey: text().notNull(),
    actor: varchar({ length: 64 }).notNull().default('system'),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [
    unique('credit_entries_idempotency_key').on(t.idempotencyKey),
    index('credit_entries_workspace_idx').on(t.workspaceId, t.createdAt),
  ],
);

export const usageCounters = pgTable(
  'usage_counters',
  {
    workspaceId: ws(),
    featureKey: varchar({ length: 96 }).notNull(),
    periodStart: timestamp({ withTimezone: true }).notNull(),
    used: bigint({ mode: 'number' }).notNull().default(0),
    updatedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [unique('usage_counters_pk').on(t.workspaceId, t.featureKey, t.periodStart)],
);

export const creditPacks = pgTable(
  'credit_packs',
  {
    id: primaryId(),
    key: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    credits: integer().notNull(),
    bonusCredits: integer().notNull().default(0),
    priceCents: integer().notNull(),
    currency: varchar({ length: 3 }).notNull().default('USD'),
    expiresAfterDays: integer(),
    isActive: boolean().notNull().default(true),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('credit_packs_key').on(t.key)],
);

// ---------------------------------------------------------------------------
// payments
// ---------------------------------------------------------------------------

export const payments = pgTable(
  'payments',
  {
    id: primaryId(),
    workspaceId: ws(),
    subscriptionId: uuid().references(() => subscriptions.id, { onDelete: 'set null' }),
    planId: uuid().references(() => plans.id, { onDelete: 'set null' }),
    creditPackId: uuid().references(() => creditPacks.id, { onDelete: 'set null' }),
    gateway: varchar({ length: 32 }).notNull(),
    gatewayPaymentId: text(),
    kind: varchar({ length: 16 }).notNull().default('subscription'),
    status: varchar({ length: 16 }).notNull().default('pending'),
    baseAmountCents: integer().notNull().default(0),
    discountCents: integer().notNull().default(0),
    taxCents: integer().notNull().default(0),
    totalCents: integer().notNull().default(0),
    currency: varchar({ length: 3 }).notNull().default('USD'),
    billingDetails: json().notNull().default({}),
    proofAssetId: uuid(),
    refundedCents: integer().notNull().default(0),
    paidAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    index('payments_workspace_idx').on(t.workspaceId, t.createdAt),
    unique('payments_gateway_key').on(t.gateway, t.gatewayPaymentId),
  ],
);

export const invoices = pgTable(
  'invoices',
  {
    id: primaryId(),
    workspaceId: ws(),
    paymentId: uuid().references(() => payments.id, { onDelete: 'cascade' }),
    number: varchar({ length: 32 }).notNull(),
    issuedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    lines: json().notNull().default(emptyJsonArray),
    totalCents: integer().notNull().default(0),
    currency: varchar({ length: 3 }).notNull().default('USD'),
    pdfAssetId: uuid(),
    ...timestamps,
  },
  (t) => [unique('invoices_number_key').on(t.number)],
);

export const coupons = pgTable(
  'coupons',
  {
    id: primaryId(),
    code: varchar({ length: 64 }).notNull(),
    name: text(),
    kind: varchar({ length: 16 }).notNull().default('percent'), // percent | fixed | free_days
    value: integer().notNull(),
    planIds: text().array().notNull().default([]),
    quantity: integer(),
    redeemed: integer().notNull().default(0),
    startsAt: timestamp({ withTimezone: true }),
    endsAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [unique('coupons_code_key').on(t.code)],
);

export const couponRedemptions = pgTable(
  'coupon_redemptions',
  {
    id: primaryId(),
    workspaceId: ws(),
    couponId: uuid()
      .notNull()
      .references(() => coupons.id, { onDelete: 'cascade' }),
    paymentId: uuid().references(() => payments.id, { onDelete: 'set null' }),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [unique('coupon_redemptions_key').on(t.couponId, t.workspaceId)],
);

export const taxRates = pgTable(
  'tax_rates',
  {
    id: primaryId(),
    name: text().notNull(),
    countryCode: varchar({ length: 2 }),
    region: varchar({ length: 64 }),
    percent: integer().notNull(),
    inclusive: boolean().notNull().default(false),
    isActive: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [index('tax_rates_country_idx').on(t.countryCode)],
);
