import { z } from 'zod';

/**
 * The automation DSL.
 *
 * WHEN <event> [matching <filter>] IF <conditions> THEN <actions>
 *
 * Stored as JSON and validated on save, so a malformed rule fails in the
 * builder rather than at 3am inside a worker.
 */

export const CONDITION_OPS = [
  'equals', 'not_equals', 'in', 'not_in', 'contains', 'gt', 'lt', 'gte', 'lte',
  'regex', 'exists',
  'resource.has_relation',
  'entitlement.allows',
  'schedule.within',
  'throttle.once_per',
  'ai.classify',
] as const;
export type ConditionOp = (typeof CONDITION_OPS)[number];

export const conditionSchema = z.object({
  op: z.enum(CONDITION_OPS),
  /** Dot path into the envelope, e.g. "data.severity". */
  field: z.string().optional(),
  value: z.unknown().optional(),
  negate: z.boolean().default(false),
  // op-specific
  subject: z.string().optional(),
  relation: z.string().optional(),
  feature: z.string().optional(),
  quantity: z.number().int().positive().default(1),
  window: z.string().optional(),
  key: z.string().optional(),
  timezone: z.string().optional(),
  days: z.array(z.number().int().min(0).max(6)).optional(),
  from: z.string().optional(),
  to: z.string().optional(),
});
export type Condition = z.infer<typeof conditionSchema>;
export type ConditionInput = z.input<typeof conditionSchema>;

export const ACTION_TYPES = [
  'command', 'notify', 'webhook', 'http_request', 'ai.generate',
  'resource.relate', 'tag', 'delay', 'branch',
] as const;
export type ActionType = (typeof ACTION_TYPES)[number];

/** `branch` nests actions, so the schema is recursive and needs an explicit type. */
export type Action = {
  type: ActionType;
  name?: string;
  input: Record<string, unknown>;
  conditions?: Condition[];
  then?: Action[];
  otherwise?: Action[];
  duration?: string;
  critical: boolean;
};

export type ActionInput = {
  type: ActionType;
  name?: string;
  input?: Record<string, unknown>;
  conditions?: ConditionInput[];
  then?: ActionInput[];
  otherwise?: ActionInput[];
  duration?: string;
  critical?: boolean;
};

export const actionSchema: z.ZodType<Action, ActionInput> = z.lazy(() =>
  z.object({
    type: z.enum(ACTION_TYPES),
    name: z.string().optional(),
    input: z.record(z.string(), z.unknown()).default({}),
    conditions: z.array(conditionSchema).optional(),
    then: z.array(actionSchema).optional(),
    otherwise: z.array(actionSchema).optional(),
    duration: z.string().optional(),
    critical: z.boolean().default(false),
  }),
) as z.ZodType<Action, ActionInput>;

export const definitionSchema = z.object({
  trigger: z.object({
    event: z.string(),
    filter: z.record(z.string(), z.unknown()).default({}),
  }),
  conditions: z.array(conditionSchema).default([]),
  actions: z.array(actionSchema).min(1),
});
export type Definition = z.infer<typeof definitionSchema>;
/** What an author writes: defaults are still optional. */
export type DefinitionInput = z.input<typeof definitionSchema>;

export function parseDefinition(input: unknown): Definition {
  return definitionSchema.parse(input);
}

/** Read a dot path out of a nested object. Returns undefined, never throws. */
export function readPath(source: unknown, path: string): unknown {
  return path.split('.').reduce<unknown>((acc, key) => {
    if (acc === null || acc === undefined) return undefined;
    if (typeof acc !== 'object') return undefined;
    return (acc as Record<string, unknown>)[key];
  }, source);
}

/**
 * `{{data.target_url}}` interpolation.
 *
 * A lone `{{path}}` returns the RAW value, so numbers and arrays survive;
 * anything embedded in surrounding text is stringified.
 */
export function interpolate(template: unknown, scope: Record<string, unknown>): unknown {
  if (typeof template === 'string') {
    const whole = template.match(/^\{\{\s*([\w.]+)\s*\}\}$/);
    if (whole) return readPath(scope, whole[1]!);
    return template.replace(/\{\{\s*([\w.]+)\s*\}\}/g, (_, path: string) => {
      const value = readPath(scope, path);
      return value === undefined || value === null ? '' : String(value);
    });
  }
  if (Array.isArray(template)) return template.map((t) => interpolate(t, scope));
  if (template && typeof template === 'object') {
    return Object.fromEntries(
      Object.entries(template as Record<string, unknown>).map(([k, v]) => [k, interpolate(v, scope)]),
    );
  }
  return template;
}

/** "15m" / "2h" / "7d" -> milliseconds. */
export function parseDuration(input: string): number {
  const m = input.trim().match(/^(\d+)\s*(s|m|h|d)$/i);
  if (!m) throw new Error(`invalid duration "${input}" — use forms like 30s, 15m, 2h, 7d`);
  const n = Number(m[1]);
  const unit = m[2]!.toLowerCase() as 's' | 'm' | 'h' | 'd';
  return n * { s: 1000, m: 60_000, h: 3_600_000, d: 86_400_000 }[unit];
}
