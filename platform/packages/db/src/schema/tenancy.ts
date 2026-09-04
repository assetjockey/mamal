import { relations } from 'drizzle-orm';
import {
  boolean,
  index,
  pgTable,
  text,
  timestamp,
  unique,
  uuid,
  varchar,
} from 'drizzle-orm/pg-core';
import { json, primaryId, softDelete, timestamps } from './_shared.ts';

// ---------------------------------------------------------------------------
// Identity (Better Auth compatible)
// ---------------------------------------------------------------------------

export const users = pgTable(
  'users',
  {
    id: primaryId(),
    email: varchar({ length: 320 }).notNull(),
    emailVerified: boolean().notNull().default(false),
    name: text(),
    image: text(),
    /** Instance superadmin — gates /admin. Distinct from any workspace role. */
    isPlatformAdmin: boolean().notNull().default(false),
    locale: varchar({ length: 12 }).notNull().default('en'),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    twoFactorEnabled: boolean().notNull().default(false),
    lastSeenAt: timestamp({ withTimezone: true }),
    ...timestamps,
    ...softDelete,
  },
  (t) => [unique('users_email_key').on(t.email)],
);

export const sessions = pgTable(
  'sessions',
  {
    id: primaryId(),
    userId: uuid()
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    token: text().notNull(),
    expiresAt: timestamp({ withTimezone: true }).notNull(),
    /** Which workspace this session is currently acting in. */
    activeWorkspaceId: uuid(),
    impersonatedBy: uuid().references(() => users.id, { onDelete: 'set null' }),
    ipAddress: varchar({ length: 64 }),
    userAgent: text(),
    ...timestamps,
  },
  (t) => [unique('sessions_token_key').on(t.token), index('sessions_user_idx').on(t.userId)],
);

export const accounts = pgTable(
  'accounts',
  {
    id: primaryId(),
    userId: uuid()
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    providerId: varchar({ length: 64 }).notNull(),
    /** Better Auth resolves an OAuth account by (issuer, accountId). */
    issuer: varchar({ length: 255 }).notNull().default(''),
    accountId: varchar({ length: 255 }).notNull(),
    accessToken: text(),
    refreshToken: text(),
    idToken: text(),
    accessTokenExpiresAt: timestamp({ withTimezone: true }),
    refreshTokenExpiresAt: timestamp({ withTimezone: true }),
    scope: text(),
    password: text(),
    ...timestamps,
  },
  (t) => [unique('accounts_provider_key').on(t.providerId, t.accountId)],
);

export const verifications = pgTable(
  'verifications',
  {
    id: primaryId(),
    identifier: varchar({ length: 320 }).notNull(),
    value: text().notNull(),
    expiresAt: timestamp({ withTimezone: true }).notNull(),
    ...timestamps,
  },
  (t) => [index('verifications_identifier_idx').on(t.identifier)],
);

// ---------------------------------------------------------------------------
// Workspace — the billing root
// ---------------------------------------------------------------------------

export type WorkspaceSettings = {
  brandColor?: string;
  logoUrl?: string;
  defaultChannelIds?: string[];
};

export const workspaces = pgTable(
  'workspaces',
  {
    id: primaryId(),
    slug: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    logoUrl: text(),
    /** 'personal' is auto-created at signup; 'team' is user-created. */
    kind: varchar({ length: 16 }).notNull().default('personal'),
    ownerUserId: uuid()
      .notNull()
      .references(() => users.id, { onDelete: 'restrict' }),
    /** Tenant-level AI kill switch. See packages/ai — any false wins. */
    aiEnabled: boolean().notNull().default(true),
    settings: json<WorkspaceSettings>().notNull().default({}),
    ...timestamps,
    ...softDelete,
  },
  (t) => [unique('workspaces_slug_key').on(t.slug), index('workspaces_owner_idx').on(t.ownerUserId)],
);

/**
 * Base role plus per-tool grants, so a member can hold Link and not Market.
 * toolGrants: { link: 'admin', market: 'viewer', monitor: null }
 */
export type ToolGrants = Partial<
  Record<'audit' | 'confirm' | 'link' | 'market' | 'monitor' | 'track', string | null>
>;

export const workspaceMembers = pgTable(
  'workspace_members',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    userId: uuid()
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    role: varchar({ length: 16 }).notNull().default('member'),
    toolGrants: json<ToolGrants>().notNull().default({}),
    invitedByUserId: uuid().references(() => users.id, { onDelete: 'set null' }),
    ...timestamps,
  },
  (t) => [
    unique('workspace_members_key').on(t.workspaceId, t.userId),
    index('workspace_members_user_idx').on(t.userId),
  ],
);

export const invitations = pgTable(
  'invitations',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    email: varchar({ length: 320 }).notNull(),
    role: varchar({ length: 16 }).notNull().default('member'),
    toolGrants: json<ToolGrants>().notNull().default({}),
    token: text().notNull(),
    status: varchar({ length: 16 }).notNull().default('pending'),
    inviterUserId: uuid().references(() => users.id, { onDelete: 'set null' }),
    expiresAt: timestamp({ withTimezone: true }).notNull(),
    ...timestamps,
  },
  (t) => [
    unique('invitations_token_key').on(t.token),
    index('invitations_workspace_idx').on(t.workspaceId, t.email),
  ],
);

// ---------------------------------------------------------------------------
// Project — the shared grouping every tool uses. Never null downstream.
// ---------------------------------------------------------------------------

export const projects = pgTable(
  'projects',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    name: text().notNull(),
    slug: varchar({ length: 64 }).notNull(),
    color: varchar({ length: 16 }).notNull().default('indigo'),
    description: text(),
    /** Exactly one per workspace; auto-created at signup. */
    isDefault: boolean().notNull().default(false),
    timezone: varchar({ length: 64 }).notNull().default('UTC'),
    ...timestamps,
    ...softDelete,
  },
  (t) => [
    unique('projects_slug_key').on(t.workspaceId, t.slug),
    index('projects_workspace_idx').on(t.workspaceId),
  ],
);

export const projectMembers = pgTable(
  'project_members',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    projectId: uuid()
      .notNull()
      .references(() => projects.id, { onDelete: 'cascade' }),
    userId: uuid()
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    role: varchar({ length: 16 }).notNull().default('member'),
    ...timestamps,
  },
  (t) => [unique('project_members_key').on(t.projectId, t.userId)],
);

// ---------------------------------------------------------------------------
// Access + audit
// ---------------------------------------------------------------------------

export const apiKeys = pgTable(
  'api_keys',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    userId: uuid().references(() => users.id, { onDelete: 'set null' }),
    name: text().notNull(),
    keyHash: text().notNull(),
    prefix: varchar({ length: 16 }).notNull(),
    /** `<tool>:<resource>:<action>` grammar. */
    scopes: text().array().notNull().default([]),
    rateLimitPerMin: text().notNull().default('60'),
    lastUsedAt: timestamp({ withTimezone: true }),
    expiresAt: timestamp({ withTimezone: true }),
    revokedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [
    unique('api_keys_hash_key').on(t.keyHash),
    index('api_keys_workspace_idx').on(t.workspaceId),
  ],
);

export const auditLogs = pgTable(
  'audit_logs',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    actorUserId: uuid().references(() => users.id, { onDelete: 'set null' }),
    actorApiKeyId: uuid().references(() => apiKeys.id, { onDelete: 'set null' }),
    action: varchar({ length: 128 }).notNull(),
    resourceUrn: text(),
    before: json(),
    after: json(),
    ipAddress: varchar({ length: 64 }),
    userAgent: text(),
    createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index('audit_logs_workspace_idx').on(t.workspaceId, t.createdAt)],
);

export const oauthConnections = pgTable(
  'oauth_connections',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    provider: varchar({ length: 32 }).notNull(),
    externalAccountId: varchar({ length: 255 }).notNull(),
    label: text(),
    scopes: text().array().notNull().default([]),
    encryptedTokens: text().notNull(),
    expiresAt: timestamp({ withTimezone: true }),
    status: varchar({ length: 16 }).notNull().default('active'),
    lastError: text(),
    ...timestamps,
  },
  (t) => [
    unique('oauth_connections_key').on(t.workspaceId, t.provider, t.externalAccountId),
    index('oauth_connections_workspace_idx').on(t.workspaceId),
  ],
);

// ---------------------------------------------------------------------------
// Relations
// ---------------------------------------------------------------------------

export const workspacesRelations = relations(workspaces, ({ one, many }) => ({
  owner: one(users, { fields: [workspaces.ownerUserId], references: [users.id] }),
  members: many(workspaceMembers),
  projects: many(projects),
}));

export const projectsRelations = relations(projects, ({ one, many }) => ({
  workspace: one(workspaces, { fields: [projects.workspaceId], references: [workspaces.id] }),
  members: many(projectMembers),
}));

export const workspaceMembersRelations = relations(workspaceMembers, ({ one }) => ({
  workspace: one(workspaces, { fields: [workspaceMembers.workspaceId], references: [workspaces.id] }),
  user: one(users, { fields: [workspaceMembers.userId], references: [users.id] }),
}));


/**
 * Onboarding answers.
 *
 * One question, six answers, mapped to the six tools. It determines the
 * initial sidebar order and the checklist and NOTHING else — no gating, no
 * branching flows. (open-seo's user_onboarding_answers, kept deliberately
 * small: the wow moment is the first audit, not the questionnaire.)
 */
export const onboarding = pgTable(
  'onboarding',
  {
    workspaceId: uuid()
      .primaryKey()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    /** Tool keys the user said they came for. */
    interests: text().array().notNull().default([]),
    role: varchar({ length: 64 }),
    /** Checklist steps completed, e.g. ['verify_site','install_snippet']. */
    completedSteps: text().array().notNull().default([]),
    firstResourceUrl: text(),
    dismissedAt: timestamp({ withTimezone: true }),
    completedAt: timestamp({ withTimezone: true }),
    ...timestamps,
  },
  (t) => [index('onboarding_workspace_idx').on(t.workspaceId)],
);
