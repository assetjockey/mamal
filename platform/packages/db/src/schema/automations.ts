import {
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

const ws = () =>
  uuid()
    .notNull()
    .references(() => workspaces.id, { onDelete: 'cascade' });

export type AutomationCondition = {
  op: string;
  [key: string]: unknown;
};

export type AutomationAction = {
  type: 'command' | 'notify' | 'webhook' | 'http_request' | 'ai.generate' | 'resource.relate' | 'tag' | 'delay' | 'branch';
  [key: string]: unknown;
};

export const automations = pgTable(
  'automations',
  {
    id: primaryId(),
    workspaceId: ws(),
    projectId: uuid()
      .notNull()
      .references(() => projects.id, { onDelete: 'cascade' }),
    templateKey: varchar({ length: 64 }),
    name: text().notNull(),
    description: text(),
    enabled: boolean().notNull().default(true),
    triggerEvent: varchar({ length: 96 }).notNull(),
    triggerFilter: json().notNull().default({}),
    conditions: json<AutomationCondition[]>().notNull().default(emptyJsonArray),
    actions: json<AutomationAction[]>().notNull().default(emptyJsonArray),
    runLimitPerHour: integer().notNull().default(1000),
    lastRunAt: timestamp({ withTimezone: true }),
    version: integer().notNull().default(1),
    createdByUserId: uuid(),
    ...timestamps,
  },
  (t) => [index('automations_trigger_idx').on(t.workspaceId, t.triggerEvent, t.enabled)],
);

export const automationRuns = pgTable(
  'automation_runs',
  {
    id: primaryId(),
    workspaceId: ws(),
    automationId: uuid()
      .notNull()
      .references(() => automations.id, { onDelete: 'cascade' }),
    eventId: uuid(),
    status: varchar({ length: 16 }).notNull().default('running'),
    startedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
    finishedAt: timestamp({ withTimezone: true }),
    steps: json().notNull().default(emptyJsonArray),
    creditsSpent: integer().notNull().default(0),
    error: text(),
  },
  (t) => [index('automation_runs_automation_idx').on(t.automationId, t.startedAt)],
);

/** ~30 seeded recipes — the shipped cross-tool flows. */
export const automationTemplates = pgTable(
  'automation_templates',
  {
    id: primaryId(),
    key: varchar({ length: 64 }).notNull(),
    name: text().notNull(),
    description: text(),
    category: varchar({ length: 32 }),
    /** Which tools must be installed for this template to be offered. */
    requiredTools: text().array().notNull().default([]),
    definition: json().notNull(),
    sortOrder: integer().notNull().default(0),
    ...timestamps,
  },
  (t) => [unique('automation_templates_key').on(t.key)],
);
