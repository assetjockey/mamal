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
import { json, primaryId, timestamps } from './_shared.ts';
import { workspaces } from './tenancy.ts';

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });

/**
 * Provider + model registries as DB ROWS, not config arrays. This is the best
 * single idea in the 18 source products (magicads' media_models/text_models):
 * adding a model on launch day is an admin form, not a deploy.
 */

export const aiProviders = pgTable(
  'ai_providers',
  {
    key: varchar({ length: 32 }).primaryKey(),
    label: text().notNull(),
    driver: varchar({ length: 64 }).notNull(),
    credentialField: varchar({ length: 64 }).notNull(),
    baseUrl: text(),
    authStyle: varchar({ length: 24 }).notNull().default('bearer'),
    isEnabled: boolean().notNull().default(true),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [index('ai_providers_enabled_idx').on(t.isEnabled)],
);

export const AI_MODALITIES = ['text', 'image', 'video', 'audio', 'embedding', 'vision'] as const;
export type AiModality = (typeof AI_MODALITIES)[number];

export type AiCapabilities = {
  audio?: boolean;
  durations?: number[];
  maxDuration?: number;
  maxResolution?: string;
  textRendering?: 'best' | 'good' | 'weak' | 'native';
  contextWindow?: number;
  tools?: boolean;
  jsonMode?: boolean;
};

export const aiModels = pgTable(
  'ai_models',
  {
    id: primaryId(),
    providerKey: varchar({ length: 32 })
      .notNull()
      .references(() => aiProviders.key, { onDelete: 'cascade' }),
    modelId: varchar({ length: 128 }).notNull(),
    label: text().notNull(),
    subLabel: text(),
    description: text(),
    modality: varchar({ length: 16 }).$type<AiModality>().notNull(),
    capabilities: json<AiCapabilities>().notNull().default({}),
    tier: varchar({ length: 16 }).notNull().default('standard'),
    creditCost: integer().notNull().default(1),
    costUnit: varchar({ length: 24 }).notNull().default('call'),
    /** OUR real cost. Margin reporting only — never shown to users. */
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    iconSvg: text(),
    tint: varchar({ length: 16 }),
    isRecommended: boolean().notNull().default(false),
    isEnabled: boolean().notNull().default(true),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [
    unique('ai_models_key').on(t.providerKey, t.modelId),
    index('ai_models_modality_idx').on(t.modality, t.isEnabled),
  ],
);

/** One row per toggleable AI feature. Contributed by each tool's manifest. */
export const aiFeatures = pgTable(
  'ai_features',
  {
    key: varchar({ length: 96 }).primaryKey(),
    tool: varchar({ length: 32 }).notNull(),
    name: text().notNull(),
    description: text(),
    modality: varchar({ length: 16 }).$type<AiModality>().notNull(),
    defaultModelId: uuid().references(() => aiModels.id, { onDelete: 'set null' }),
    fallbackModelId: uuid().references(() => aiModels.id, { onDelete: 'set null' }),
    promptKey: varchar({ length: 96 }),
    isEnabledDefault: boolean().notNull().default(true),
    creditCostOverride: integer(),
    ...timestamps,
  },
  (t) => [index('ai_features_tool_idx').on(t.tool)],
);

/**
 * Per-scope override. scope='instance' rows have scopeId NULL.
 * Precedence is AND across every level — any false wins.
 */
export const aiFeatureState = pgTable(
  'ai_feature_state',
  {
    id: primaryId(),
    scope: varchar({ length: 16 }).notNull(), // 'instance' | 'workspace'
    scopeId: uuid(),
    featureKey: varchar({ length: 96 })
      .notNull()
      .references(() => aiFeatures.key, { onDelete: 'cascade' }),
    isEnabled: boolean().notNull().default(true),
    modelId: uuid().references(() => aiModels.id, { onDelete: 'set null' }),
    credentialId: uuid(),
    monthlyCreditCap: integer(),
    ...timestamps,
  },
  (t) => [unique('ai_feature_state_key').on(t.scope, t.scopeId, t.featureKey)],
);

/** BYO keys. Envelope-encrypted, never logged, never returned — only keyHint. */
export const aiCredentials = pgTable(
  'ai_credentials',
  {
    id: primaryId(),
    scope: varchar({ length: 16 }).notNull(), // 'instance' | 'workspace'
    scopeId: uuid(),
    providerKey: varchar({ length: 32 })
      .notNull()
      .references(() => aiProviders.key, { onDelete: 'cascade' }),
    encryptedKey: text().notNull(),
    keyHint: varchar({ length: 16 }).notNull(),
    isActive: boolean().notNull().default(true),
    verifiedAt: timestamp({ withTimezone: true }),
    lastError: text(),
    ...timestamps,
  },
  (t) => [unique('ai_credentials_key').on(t.scope, t.scopeId, t.providerKey)],
);

export const aiPrompts = pgTable(
  'ai_prompts',
  {
    id: primaryId(),
    key: varchar({ length: 96 }).notNull(),
    version: integer().notNull().default(1),
    system: text(),
    userTemplate: text().notNull(),
    variables: json().notNull().default({}),
    modelHint: varchar({ length: 128 }),
    isActive: boolean().notNull().default(true),
    ...timestamps,
  },
  (t) => [unique('ai_prompts_key').on(t.key, t.version)],
);

export const aiGenerations = pgTable(
  'ai_generations',
  {
    id: primaryId(),
    workspaceId: ws(),
    featureKey: varchar({ length: 96 }).notNull(),
    modelId: uuid().references(() => aiModels.id, { onDelete: 'set null' }),
    status: varchar({ length: 16 }).notNull().default('pending'),
    input: json().notNull().default({}),
    outputAssetId: uuid(),
    outputText: text(),
    inputTokens: integer().notNull().default(0),
    outputTokens: integer().notNull().default(0),
    units: integer().notNull().default(0),
    vendorCostMicros: bigint({ mode: 'number' }).notNull().default(0),
    creditsCharged: integer().notNull().default(0),
    holdId: uuid(),
    byoKey: boolean().notNull().default(false),
    /** Async providers: store the task id, poll, then capture. */
    externalTaskId: text(),
    pollCount: integer().notNull().default(0),
    lastPolledAt: timestamp({ withTimezone: true }),
    latencyMs: integer(),
    error: text(),
    ...timestamps,
  },
  (t) => [
    index('ai_generations_workspace_idx').on(t.workspaceId, t.createdAt),
    index('ai_generations_status_idx').on(t.status, t.lastPolledAt),
  ],
);
