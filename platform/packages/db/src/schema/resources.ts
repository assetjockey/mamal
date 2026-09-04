import { index, pgTable, text, unique, uuid, varchar } from 'drizzle-orm/pg-core';
import { json, primaryId, timestamps } from './_shared.ts';
import { projects, workspaces } from './tenancy.ts';

/**
 * The URN registry — the thing that lets Audit, Monitor and Track all point at
 * ONE site row, and lets any tool reference any other tool's object without
 * importing it.
 *
 *   urn = urn:mamal:<tool>:<type>:<id>
 */
export const resources = pgTable(
  'resources',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    projectId: uuid()
      .notNull()
      .references(() => projects.id, { onDelete: 'cascade' }),
    urn: text().notNull(),
    tool: varchar({ length: 32 }).notNull(),
    type: varchar({ length: 48 }).notNull(),
    /** The owning table's primary key, as text. */
    externalId: text().notNull(),
    label: text(),
    status: varchar({ length: 24 }).notNull().default('active'),
    attrs: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [
    unique('resources_urn_key').on(t.workspaceId, t.urn),
    unique('resources_external_key').on(t.workspaceId, t.tool, t.type, t.externalId),
    index('resources_type_idx').on(t.workspaceId, t.type, t.status),
    index('resources_project_idx').on(t.projectId),
  ],
);

/**
 * Typed edges between resources. `neighbors(urn)` over this table powers the
 * "Connected" panel that appears on every detail page in every tool.
 */
export const RESOURCE_RELATIONS = [
  'monitors',
  'tracks',
  'shortens',
  'audits',
  'derived_from',
  'publishes_to',
  'attributed_to',
  'contains',
] as const;
export type ResourceRelation = (typeof RESOURCE_RELATIONS)[number];

export const resourceLinks = pgTable(
  'resource_links',
  {
    id: primaryId(),
    workspaceId: uuid()
      .notNull()
      .references(() => workspaces.id, { onDelete: 'cascade' }),
    fromResourceId: uuid()
      .notNull()
      .references(() => resources.id, { onDelete: 'cascade' }),
    toResourceId: uuid()
      .notNull()
      .references(() => resources.id, { onDelete: 'cascade' }),
    relation: varchar({ length: 32 }).$type<ResourceRelation>().notNull(),
    /** 'user' | 'system' | 'automation:<id>' — so auto-created edges are removable. */
    createdBy: varchar({ length: 64 }).notNull().default('system'),
    metadata: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [
    unique('resource_links_key').on(t.workspaceId, t.fromResourceId, t.toResourceId, t.relation),
    index('resource_links_from_idx').on(t.fromResourceId, t.relation),
    index('resource_links_to_idx').on(t.toResourceId, t.relation),
  ],
);
