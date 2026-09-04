import { boolean, integer, pgTable, text, unique, varchar } from 'drizzle-orm/pg-core';
import { json, primaryId, timestamps } from './_shared.ts';

/**
 * Instance-wide singleton settings. Exactly one row, id = 'singleton'.
 * These are the platform-operator controls, not tenant controls.
 */
export const instanceSettings = pgTable('instance_settings', {
  id: varchar({ length: 16 }).primaryKey().default('singleton'),

  /** Master AI kill switch. Cancels in-flight generations and releases holds. */
  aiMasterEnabled: boolean().notNull().default(true),
  /**
   * Bumped on every AI config change. Checked on every AI call so the master
   * switch takes effect in <5s rather than after a 60s cache TTL.
   */
  aiConfigVersion: integer().notNull().default(1),
  /**
   * Whether lifetime-plan holders may spend purchased credits on AI.
   * Default OFF = the literal reading of "lifetime excludes AI".
   * See the plan's Risk 6 for why enabling it is recommended.
   */
  lifetimeAiViaCredits: boolean().notNull().default(false),

  /** 1 credit is anchored to this much of OUR cost, in micros. 10_000 = $0.01. */
  creditCostAnchorMicros: integer().notNull().default(10_000),

  siteName: text().notNull().default('Mamal'),
  siteUrl: text().notNull().default('http://localhost:3000'),
  supportEmail: text(),
  defaultLocale: varchar({ length: 12 }).notNull().default('en'),
  signupsEnabled: boolean().notNull().default(true),

  config: json().notNull().default({}),
  ...timestamps,
});

/**
 * Which tools and plugins this instance has installed. Gates availability
 * before entitlements gate visibility. A tool absent here makes
 * commands.dispatch('<tool>.*') return { ok: false, reason: 'tool_not_installed' }.
 */
export const instanceModules = pgTable(
  'instance_modules',
  {
    id: primaryId(),
    key: varchar({ length: 64 }).notNull(),
    kind: varchar({ length: 16 }).notNull().default('tool'), // 'tool' | 'plugin'
    version: varchar({ length: 32 }).notNull().default('0.0.0'),
    installed: boolean().notNull().default(false),
    enabled: boolean().notNull().default(false),
    config: json().notNull().default({}),
    ...timestamps,
  },
  (t) => [unique('instance_modules_key').on(t.key)],
);
