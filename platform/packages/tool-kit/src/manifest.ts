import { z } from 'zod';

/**
 * The tool contract.
 *
 * "Each tool functions independently" has to be a build constraint, not an
 * intention. A tool's manifest is its ONLY public surface: navigation is data,
 * cross-tool calls go through `commands`, and everything a tool contributes to
 * the platform (entitlement keys, AI toggles, queues, crons) is declared here
 * rather than reached for.
 *
 * Plugins use the same type with kind: 'plugin' — they are simply tools
 * without a slot on the navigation rail.
 */

export const TOOL_KEYS = ['audit', 'confirm', 'link', 'market', 'monitor', 'track'] as const;
export type ToolKey = (typeof TOOL_KEYS)[number];

export const navItemSchema: z.ZodType<NavItem> = z.lazy(() =>
  z.object({
    key: z.string(),
    label: z.string(),
    href: z.string(),
    icon: z.string().optional(),
    /** Hidden server-side when the workspace lacks this entitlement. */
    requires: z.string().optional(),
    group: z.string().optional(),
    badge: z.enum(['count', 'dot']).optional(),
    children: z.array(navItemSchema).optional(),
  }),
);

export type NavItem = {
  key: string;
  label: string;
  href: string;
  icon?: string;
  requires?: string;
  group?: string;
  badge?: 'count' | 'dot';
  children?: NavItem[];
};

export const resourceTypeSchema = z.object({
  type: z.string(),
  label: z.string(),
  /** URN types this tool mints; used by the automations resource picker. */
  searchable: z.boolean().default(true),
  /**
   * Where one of these opens, as a template over the resource's `externalId`
   * — e.g. `/audit/sites/:id`.
   *
   * The tool declares its own routes because nothing else can: `externalId` is
   * whatever primary key that tool happens to key on, and only the tool knows
   * it. A shared lookup table elsewhere would have to be edited by every new
   * tool, which is precisely the cross-tool coupling the manifest exists to
   * prevent. Omit it and the resource is searchable but not linkable.
   */
  href: z.string().optional(),
});

export const eventDefSchema = z.object({
  name: z.string().regex(/^[a-z_]+\.[a-z_]+\.[a-z_]+$/, 'events are <tool>.<noun>.<past-tense>'),
  description: z.string().optional(),
  payload: z.custom<z.ZodTypeAny>((v) => v instanceof z.ZodType, 'payload must be a zod schema'),
  /**
   * High-volume streams land in ClickHouse and must NOT emit per-occurrence
   * events — 50M clicks/day would flood the bus. Rollup jobs emit threshold
   * events instead.
   */
  highVolume: z.boolean().default(false),
});

export const subscriptionDefSchema = z.object({
  /**
   * Held to the same `<tool>.<noun>.<past-tense>` rule as `eventDefSchema`.
   *
   * It was a bare string, and a subscription to `monitor.up` sat here looking
   * correct — but the envelope schema rejects a two-segment name, so nothing
   * could ever have published it and the handler would never have fired. The
   * publisher's contract and the subscriber's have to be the same contract.
   */
  event: z.string().regex(/^[a-z_]+\.[a-z_]+\.[a-z_]+$/, 'events are <tool>.<noun>.<past-tense>'),
  handlerKey: z.string(),
  description: z.string().optional(),
});

export const commandDefSchema = z.object({
  name: z.string().regex(/^[a-z_]+\.[a-zA-Z]+$/, 'commands are <tool>.<camelCaseVerb>'),
  description: z.string().optional(),
  input: z.custom<z.ZodTypeAny>((v) => v instanceof z.ZodType),
  /** Sync commands block the caller — use only when ordering truly matters. */
  sync: z.boolean().default(false),
});

export const featureDefSchema = z.object({
  key: z.string(),
  name: z.string(),
  kind: z.enum(['boolean', 'limit', 'quota', 'metered']),
  isAi: z.boolean().default(false),
  freeTierAllowed: z.boolean().default(false),
  defaultCreditCost: z.number().int().min(0).default(0),
});

export const aiFeatureDefSchema = z.object({
  key: z.string(),
  name: z.string(),
  modality: z.enum(['text', 'image', 'video', 'audio', 'embedding', 'vision']),
  description: z.string().optional(),
});

export const queueDefSchema = z.object({
  name: z.string(),
  concurrency: z.number().int().positive(),
  /** Probes set 0: a retry corrupts uptime maths — a failed check IS the signal. */
  attempts: z.number().int().min(0).default(3),
});

export const cronDefSchema = z.object({
  key: z.string(),
  schedule: z.string(),
  description: z.string().optional(),
});

export const toolManifestSchema = z.object({
  key: z.string(),
  kind: z.enum(['tool', 'plugin']).default('tool'),
  version: z.string().default('0.0.0'),
  name: z.string(),
  description: z.string().optional(),
  basePath: z.string().startsWith('/'),
  icon: z.string().optional(),
  color: z.string().optional(),
  nav: z.array(navItemSchema).default([]),
  resources: z.array(resourceTypeSchema).default([]),
  events: z.array(eventDefSchema).default([]),
  subscriptions: z.array(subscriptionDefSchema).default([]),
  commands: z.array(commandDefSchema).default([]),
  features: z.array(featureDefSchema).default([]),
  aiFeatures: z.array(aiFeatureDefSchema).default([]),
  queues: z.array(queueDefSchema).default([]),
  crons: z.array(cronDefSchema).default([]),
});

export type ToolManifest = z.infer<typeof toolManifestSchema>;

export function defineTool(manifest: z.input<typeof toolManifestSchema>): ToolManifest {
  const parsed = toolManifestSchema.parse(manifest);

  // A tool's events must be namespaced to it, or the bus registry collides.
  for (const e of parsed.events) {
    if (!e.name.startsWith(`${parsed.key}.`)) {
      throw new Error(`event "${e.name}" must be namespaced under "${parsed.key}."`);
    }
  }
  for (const c of parsed.commands) {
    if (!c.name.startsWith(`${parsed.key}.`)) {
      throw new Error(`command "${c.name}" must be namespaced under "${parsed.key}."`);
    }
  }
  // An AI feature must also be declared as a billable feature with isAi, or it
  // escapes both the kill switch and the lifetime exclusion.
  const featureKeys = new Map(parsed.features.map((f) => [f.key, f]));
  for (const a of parsed.aiFeatures) {
    const f = featureKeys.get(a.key);
    if (!f) {
      throw new Error(
        `AI feature "${a.key}" has no matching entitlement feature. ` +
          `Declare it in features[] with isAi: true, or it bypasses the AI kill switch.`,
      );
    }
    if (!f.isAi) {
      throw new Error(`feature "${a.key}" backs an AI feature but is not marked isAi: true`);
    }
  }
  return parsed;
}
