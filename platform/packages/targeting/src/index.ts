/**
 * Targeting: deciding whether a widget should be shown to *this* visitor.
 *
 * Runs in the browser, once per widget, on every page view — so it is written
 * to be small, synchronous, allocation-light and dependency-free. The same
 * module runs on the server to power the editor's "who would see this?"
 * preview, which is the only way that preview can be trusted.
 *
 * Two rules govern the whole file:
 *
 * **Unknown means no match, never a match.** A condition the runtime does not
 * understand — an older script meeting a newer rule — must not show a widget to
 * someone it was not meant for. Failing closed makes a stale script show *less*,
 * which is recoverable; failing open leaks an offer to the wrong audience.
 *
 * **Nothing here throws.** A malformed rule disables its widget; it never takes
 * down the host page. This script runs on other people's websites.
 */

export const OPERATORS = [
  'is', 'is_not',
  'contains', 'not_contains',
  'starts_with', 'ends_with',
  'matches', 'not_matches',
  'in', 'not_in',
  'gt', 'gte', 'lt', 'lte',
  'exists', 'not_exists',
] as const;
export type Operator = (typeof OPERATORS)[number];

/** Everything a rule may look at. Deliberately a closed set. */
export const FIELDS = [
  'path', 'url', 'referrer', 'referrer_host',
  'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
  'device', 'os', 'browser', 'language',
  'country', 'region', 'city', 'continent',
  'visitor_type', 'session_pages', 'seconds_on_page', 'scroll_depth',
  'day_of_week', 'hour',
] as const;
export type Field = (typeof FIELDS)[number];

export type Condition = { field: Field | string; op: Operator | string; value?: unknown };

export type RuleGroup = {
  /** How the conditions inside this group combine. */
  match?: 'all' | 'any';
  conditions?: Condition[];
  /** Nested groups, so "A and (B or C)" is expressible. */
  groups?: RuleGroup[];
  /** Invert the whole group's result. */
  negate?: boolean;
};

/** What the runtime knows about the current visitor and page. */
export type VisitorContext = {
  path?: string;
  url?: string;
  referrer?: string;
  referrerHost?: string;
  utm?: Record<string, string | undefined>;
  device?: 'desktop' | 'mobile' | 'tablet';
  os?: string;
  browser?: string;
  language?: string;
  country?: string;
  region?: string;
  city?: string;
  continent?: string;
  visitorType?: 'new' | 'returning';
  sessionPages?: number;
  secondsOnPage?: number;
  scrollDepth?: number;
  /** 0 = Sunday, matching Date#getDay. */
  dayOfWeek?: number;
  hour?: number;
};

/* ------------------------------------------------------------ field access */

/**
 * Fields a caller may add for its own domain.
 *
 * Push segments target on `tags` and `days_subscribed`, which a *visitor* does
 * not have. Rather than pushing subscriber concepts into the visitor engine, a
 * caller declares its extras and they are read straight off the context.
 *
 * This does not weaken the fail-closed rule: a field that is neither built in
 * nor declared still returns UNKNOWN_FIELD and never matches. What it prevents
 * is the worse failure — a segment on `tags` silently matching nobody, which
 * looks like "no subscribers" rather than like a bug.
 */
export type Extensions = ReadonlySet<string> | readonly string[];

function read(field: string, ctx: VisitorContext, extras?: Extensions): unknown {
  if (extras) {
    const has = Array.isArray(extras) ? extras.includes(field) : (extras as ReadonlySet<string>).has(field);
    if (has) return (ctx as Record<string, unknown>)[field];
  }
  switch (field) {
    case 'path': return ctx.path;
    case 'url': return ctx.url;
    case 'referrer': return ctx.referrer;
    case 'referrer_host': return ctx.referrerHost;
    case 'utm_source': return ctx.utm?.source;
    case 'utm_medium': return ctx.utm?.medium;
    case 'utm_campaign': return ctx.utm?.campaign;
    case 'utm_term': return ctx.utm?.term;
    case 'utm_content': return ctx.utm?.content;
    case 'device': return ctx.device;
    case 'os': return ctx.os;
    case 'browser': return ctx.browser;
    case 'language': return ctx.language;
    case 'country': return ctx.country;
    case 'region': return ctx.region;
    case 'city': return ctx.city;
    case 'continent': return ctx.continent;
    case 'visitor_type': return ctx.visitorType;
    case 'session_pages': return ctx.sessionPages;
    case 'seconds_on_page': return ctx.secondsOnPage;
    case 'scroll_depth': return ctx.scrollDepth;
    case 'day_of_week': return ctx.dayOfWeek;
    case 'hour': return ctx.hour;
    // An unknown field is not an error and not a match. See the header.
    default: return UNKNOWN_FIELD;
  }
}

const UNKNOWN_FIELD = Symbol('unknown-field');

/* --------------------------------------------------------------- operators */

const text = (v: unknown): string =>
  v === null || v === undefined ? '' : String(v).toLowerCase();

const num = (v: unknown): number | null => {
  const n = typeof v === 'number' ? v : Number(v);
  return Number.isFinite(n) ? n : null;
};

const list = (v: unknown): string[] =>
  Array.isArray(v) ? v.map(text) : text(v).split(',').map((s) => s.trim()).filter(Boolean);

/**
 * A user-supplied regex, run against a visitor's URL, on their machine.
 *
 * Bounded and compiled defensively: an unanchored `(a+)+` against a long path
 * is catastrophic backtracking, and the tab it freezes belongs to the
 * customer's customer. A pattern that will not compile disables its condition
 * rather than throwing.
 */
const MAX_PATTERN = 200;
function safeRegex(pattern: unknown): RegExp | null {
  const p = typeof pattern === 'string' ? pattern : '';
  if (!p || p.length > MAX_PATTERN) return null;
  try {
    return new RegExp(p, 'i');
  } catch {
    return null;
  }
}

function evaluateCondition(condition: Condition, ctx: VisitorContext, extras?: Extensions): boolean {
  const actual = read(condition.field, ctx, extras);
  if (actual === UNKNOWN_FIELD) return false;

  const { op, value } = condition;

  // Presence checks come first: they are the only ones meaningful when the
  // value is absent.
  if (op === 'exists') return actual !== undefined && actual !== null && actual !== '';
  if (op === 'not_exists') return actual === undefined || actual === null || actual === '';

  switch (op) {
    case 'is': return text(actual) === text(value);
    case 'is_not': return text(actual) !== text(value);
    case 'contains': return text(actual).includes(text(value));
    case 'not_contains': return !text(actual).includes(text(value));
    case 'starts_with': return text(actual).startsWith(text(value));
    case 'ends_with': return text(actual).endsWith(text(value));
    case 'in': return list(value).includes(text(actual));
    case 'not_in': return !list(value).includes(text(actual));

    case 'matches':
    case 'not_matches': {
      const re = safeRegex(value);
      // An uncompilable pattern is a broken rule. `matches` fails, and
      // `not_matches` fails too — a broken rule must never be the reason a
      // widget appears.
      if (!re) return false;
      const hit = re.test(String(actual ?? ''));
      return op === 'matches' ? hit : !hit;
    }

    case 'gt':
    case 'gte':
    case 'lt':
    case 'lte': {
      const a = num(actual);
      const b = num(value);
      if (a === null || b === null) return false;
      return op === 'gt' ? a > b : op === 'gte' ? a >= b : op === 'lt' ? a < b : a <= b;
    }

    // An operator this build does not know. Fail closed.
    default:
      return false;
  }
}

/* ------------------------------------------------------------------ groups */

const MAX_DEPTH = 5;

function evaluateGroup(group: RuleGroup, ctx: VisitorContext, depth: number, extras?: Extensions): boolean {
  // A cycle cannot occur in JSON, but a hand-written 400-level nest can still
  // blow the stack on someone else's page.
  if (depth > MAX_DEPTH) return false;

  const conditions = Array.isArray(group.conditions) ? group.conditions : [];
  const groups = Array.isArray(group.groups) ? group.groups : [];
  const mode = group.match === 'any' ? 'any' : 'all';

  // An empty group targets everyone. That is the default a new widget has, and
  // "show to all visitors" is the right meaning for "I have set no rules".
  if (conditions.length === 0 && groups.length === 0) return true;

  const results = [
    ...conditions.map((c) => evaluateCondition(c, ctx, extras)),
    ...groups.map((g) => evaluateGroup(g, ctx, depth + 1, extras)),
  ];

  const result = mode === 'any' ? results.some(Boolean) : results.every(Boolean);
  return group.negate ? !result : result;
}

/**
 * Should this widget show?
 *
 * Never throws: a malformed rule set returns `false`, because a widget that
 * fails to evaluate must stay hidden rather than take the page with it.
 */
export function matches(targeting: unknown, ctx: VisitorContext, extras?: Extensions): boolean {
  if (!targeting || typeof targeting !== 'object') return true; // no rules = everyone
  try {
    return evaluateGroup(targeting as RuleGroup, ctx, 0, extras);
  } catch {
    return false;
  }
}

/**
 * Explains the decision, for the editor's "who would see this?" panel.
 *
 * Kept separate from `matches` so the hot path allocates nothing: the runtime
 * never needs the reasons, and building them on every page view for every
 * widget would be the most expensive thing this module does.
 */
/* ---------------------------------------------------------------- validate */

export type TargetingProblem = { path: string; message: string };

/**
 * Checks a rule *before* it is saved.
 *
 * `matches` fails closed on an unknown condition, which is right at display
 * time — a stale script shows less rather than showing the wrong people an
 * offer. But it also means a group whose shape is not recognised has no
 * conditions to fail, so it matches *everyone*, silently.
 *
 * For a Confirm widget that is a visible mistake. For a Link rule it is not:
 * a typo in `country` sends the entire world to the German site and the only
 * symptom is traffic going somewhere it should not. So the editor and the API
 * both run this at write time, where the author is still present to be told.
 *
 * An empty rule is fine and returns nothing — "no targeting" legitimately means
 * everyone. What is refused is a rule that *looks* like it says something and
 * does not.
 */
export function validateTargeting(targeting: unknown, extras?: Extensions): TargetingProblem[] {
  const problems: TargetingProblem[] = [];
  if (targeting === null || targeting === undefined) return problems;
  if (typeof targeting !== 'object' || Array.isArray(targeting)) {
    return [{ path: '', message: 'Targeting must be a group object.' }];
  }
  walk(targeting as Record<string, unknown>, '', problems, extras);
  return problems;
}

const KNOWN_KEYS = new Set(['match', 'conditions', 'groups', 'negate']);
const NO_VALUE = new Set(['exists', 'not_exists']);

function walk(
  group: Record<string, unknown>,
  path: string,
  problems: TargetingProblem[],
  extras?: Extensions,
): void {
  const at = (suffix: string) => (path ? `${path}.${suffix}` : suffix);

  const stray = Object.keys(group).filter((k) => !KNOWN_KEYS.has(k));
  if (stray.length > 0) {
    // The silent-match-everyone case: `{ all: [...] }` has no `conditions`, so
    // it evaluates as an empty group and matches everybody.
    problems.push({
      path: path || 'targeting',
      message: `Unrecognised ${stray.length === 1 ? 'key' : 'keys'} ${stray.map((k) => `“${k}”`).join(', ')}. A group takes match, conditions, groups and negate.`,
    });
  }

  if (group.match !== undefined && group.match !== 'all' && group.match !== 'any') {
    problems.push({ path: at('match'), message: 'Must be “all” or “any”.' });
  }

  if (group.conditions !== undefined) {
    if (!Array.isArray(group.conditions)) {
      problems.push({ path: at('conditions'), message: 'Must be a list of conditions.' });
    } else {
      group.conditions.forEach((c, i) => checkCondition(c, at(`conditions[${i}]`), problems, extras));
    }
  }

  if (group.groups !== undefined) {
    if (!Array.isArray(group.groups)) {
      problems.push({ path: at('groups'), message: 'Must be a list of groups.' });
    } else {
      group.groups.forEach((g, i) => {
        if (typeof g !== 'object' || g === null || Array.isArray(g)) {
          problems.push({ path: at(`groups[${i}]`), message: 'Must be a group object.' });
        } else {
          walk(g as Record<string, unknown>, at(`groups[${i}]`), problems, extras);
        }
      });
    }
  }
}

function checkCondition(
  condition: unknown,
  path: string,
  problems: TargetingProblem[],
  extras?: Extensions,
): void {
  if (typeof condition !== 'object' || condition === null || Array.isArray(condition)) {
    problems.push({ path, message: 'Must be an object with field, op and value.' });
    return;
  }
  const c = condition as { field?: unknown; op?: unknown; value?: unknown };

  const known = extras
    ? (extras instanceof Set ? extras.has(String(c.field)) : (extras as readonly string[]).includes(String(c.field)))
    : false;
  if (typeof c.field !== 'string' || (!known && !(FIELDS as readonly string[]).includes(c.field))) {
    problems.push({ path: `${path}.field`, message: `“${String(c.field)}” is not something a rule can look at.` });
  }
  if (typeof c.op !== 'string' || !(OPERATORS as readonly string[]).includes(c.op)) {
    problems.push({ path: `${path}.op`, message: `“${String(c.op)}” is not an operator.` });
    return;
  }
  if (!NO_VALUE.has(c.op) && (c.value === undefined || c.value === null || c.value === '')) {
    problems.push({ path: `${path}.value`, message: `“${c.op}” needs a value to compare against.` });
  }
}

export type Explanation = {
  matched: boolean;
  reasons: { field: string; op: string; value: unknown; actual: unknown; matched: boolean }[];
};

export function explain(targeting: unknown, ctx: VisitorContext, extras?: Extensions): Explanation {
  const reasons: Explanation['reasons'] = [];
  const walk = (group: RuleGroup, depth: number): boolean => {
    if (depth > MAX_DEPTH) return false;
    const conditions = Array.isArray(group.conditions) ? group.conditions : [];
    const groups = Array.isArray(group.groups) ? group.groups : [];
    if (conditions.length === 0 && groups.length === 0) return true;

    const results = [
      ...conditions.map((c) => {
        const matched = evaluateCondition(c, ctx, extras);
        const actual = read(c.field, ctx, extras);
        reasons.push({
          field: c.field,
          op: c.op,
          value: c.value,
          actual: actual === UNKNOWN_FIELD ? undefined : actual,
          matched,
        });
        return matched;
      }),
      ...groups.map((g) => walk(g, depth + 1)),
    ];
    const result = group.match === 'any' ? results.some(Boolean) : results.every(Boolean);
    return group.negate ? !result : result;
  };

  let matched = false;
  try {
    matched = !targeting || typeof targeting !== 'object' ? true : walk(targeting as RuleGroup, 0);
  } catch {
    matched = false;
  }
  return { matched, reasons };
}
