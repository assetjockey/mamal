import { describe, expect, it } from 'vitest';
import { matches, explain, validateTargeting, FIELDS, OPERATORS, type VisitorContext } from '../index.ts';

const visitor = (over: Partial<VisitorContext> = {}): VisitorContext => ({
  path: '/pricing',
  url: 'https://shop.test/pricing?utm_source=google',
  referrer: 'https://www.google.com/search?q=shoes',
  referrerHost: 'www.google.com',
  utm: { source: 'google', medium: 'cpc', campaign: 'spring' },
  device: 'mobile',
  os: 'iOS',
  browser: 'Safari',
  language: 'en-GB',
  country: 'GB',
  region: 'England',
  city: 'London',
  continent: 'EU',
  visitorType: 'returning',
  sessionPages: 4,
  secondsOnPage: 30,
  scrollDepth: 55,
  dayOfWeek: 3,
  hour: 14,
  ...over,
});

const all = (...conditions: { field: string; op: string; value?: unknown }[]) => ({
  match: 'all' as const,
  conditions,
});

describe('operators', () => {
  it('is / is_not compare case-insensitively', () => {
    expect(matches(all({ field: 'country', op: 'is', value: 'gb' }), visitor())).toBe(true);
    expect(matches(all({ field: 'country', op: 'is_not', value: 'gb' }), visitor())).toBe(false);
  });

  it('contains, starts_with, ends_with', () => {
    expect(matches(all({ field: 'path', op: 'contains', value: 'pric' }), visitor())).toBe(true);
    expect(matches(all({ field: 'path', op: 'starts_with', value: '/pri' }), visitor())).toBe(true);
    expect(matches(all({ field: 'path', op: 'ends_with', value: 'ing' }), visitor())).toBe(true);
    expect(matches(all({ field: 'path', op: 'not_contains', value: 'blog' }), visitor())).toBe(true);
  });

  it('in / not_in accept an array or a comma string', () => {
    expect(matches(all({ field: 'country', op: 'in', value: ['GB', 'IE'] }), visitor())).toBe(true);
    expect(matches(all({ field: 'country', op: 'in', value: 'gb, ie' }), visitor())).toBe(true);
    expect(matches(all({ field: 'country', op: 'not_in', value: ['US'] }), visitor())).toBe(true);
  });

  it('numeric comparisons coerce strings but refuse nonsense', () => {
    expect(matches(all({ field: 'session_pages', op: 'gte', value: 4 }), visitor())).toBe(true);
    expect(matches(all({ field: 'session_pages', op: 'gt', value: '3' }), visitor())).toBe(true);
    expect(matches(all({ field: 'session_pages', op: 'lt', value: 'many' }), visitor())).toBe(false);
  });

  it('exists / not_exists treat empty string as absent', () => {
    expect(matches(all({ field: 'utm_source', op: 'exists' }), visitor())).toBe(true);
    expect(matches(all({ field: 'utm_term', op: 'not_exists' }), visitor())).toBe(true);
    expect(matches(all({ field: 'city', op: 'not_exists' }), visitor({ city: '' }))).toBe(true);
  });

  it('matches runs a regex against the raw value', () => {
    expect(matches(all({ field: 'path', op: 'matches', value: '^/pric' }), visitor())).toBe(true);
    expect(matches(all({ field: 'path', op: 'not_matches', value: '^/blog' }), visitor())).toBe(true);
  });
});

describe('failing closed', () => {
  it('an unknown field never matches', () => {
    // An older script meeting a newer rule must show LESS, not more.
    expect(matches(all({ field: 'astrological_sign', op: 'is', value: 'leo' }), visitor())).toBe(false);
  });

  it('an unknown operator never matches', () => {
    expect(matches(all({ field: 'country', op: 'sounds_like', value: 'GB' }), visitor())).toBe(false);
  });

  it('a broken regex fails BOTH matches and not_matches', () => {
    // The tempting bug: treat an uncompilable pattern as "no match", which makes
    // not_matches true — and a broken rule becomes the reason a widget appears.
    const bad = '(((';
    expect(matches(all({ field: 'path', op: 'matches', value: bad }), visitor())).toBe(false);
    expect(matches(all({ field: 'path', op: 'not_matches', value: bad }), visitor())).toBe(false);
  });

  it('refuses an over-long pattern rather than compiling it', () => {
    const huge = 'a'.repeat(500);
    expect(matches(all({ field: 'path', op: 'matches', value: huge }), visitor())).toBe(false);
  });

  it('malformed targeting hides the widget instead of throwing', () => {
    expect(matches({ conditions: 'not-an-array' }, visitor())).toBe(true); // ignored, no rules
    expect(matches({ groups: [{ groups: null }] } as never, visitor())).toBe(true);
  });

  it('caps nesting depth', () => {
    let deep: Record<string, unknown> = { conditions: [{ field: 'country', op: 'is', value: 'GB' }] };
    for (let i = 0; i < 10; i++) deep = { groups: [deep] };
    expect(matches(deep, visitor())).toBe(false);
  });
});

describe('groups', () => {
  it('no rules means everyone', () => {
    expect(matches({}, visitor())).toBe(true);
    expect(matches(null, visitor())).toBe(true);
    expect(matches({ match: 'all', conditions: [] }, visitor())).toBe(true);
  });

  it('all requires every condition; any requires one', () => {
    const conds = [
      { field: 'country', op: 'is', value: 'GB' },
      { field: 'device', op: 'is', value: 'desktop' },
    ];
    expect(matches({ match: 'all', conditions: conds }, visitor())).toBe(false);
    expect(matches({ match: 'any', conditions: conds }, visitor())).toBe(true);
  });

  it('expresses "A and (B or C)"', () => {
    const rule = {
      match: 'all' as const,
      conditions: [{ field: 'country', op: 'is', value: 'GB' }],
      groups: [
        {
          match: 'any' as const,
          conditions: [
            { field: 'device', op: 'is', value: 'desktop' },
            { field: 'device', op: 'is', value: 'mobile' },
          ],
        },
      ],
    };
    expect(matches(rule, visitor())).toBe(true);
    expect(matches(rule, visitor({ country: 'US' }))).toBe(false);
  });

  it('negate inverts a whole group', () => {
    const rule = { negate: true, conditions: [{ field: 'country', op: 'is', value: 'GB' }] };
    expect(matches(rule, visitor())).toBe(false);
    expect(matches(rule, visitor({ country: 'US' }))).toBe(true);
  });
});

describe('explain', () => {
  it('reports each condition with what the visitor actually had', () => {
    const rule = all(
      { field: 'country', op: 'is', value: 'GB' },
      { field: 'device', op: 'is', value: 'desktop' },
    );
    const out = explain(rule, visitor());
    expect(out.matched).toBe(false);
    expect(out.reasons).toHaveLength(2);
    expect(out.reasons[0]).toMatchObject({ field: 'country', matched: true, actual: 'GB' });
    expect(out.reasons[1]).toMatchObject({ field: 'device', matched: false, actual: 'mobile' });
  });

  it('agrees with matches on every case the suite covers', () => {
    // The editor preview is only trustworthy if these two never disagree.
    const rules = [
      {},
      all({ field: 'country', op: 'is', value: 'GB' }),
      all({ field: 'device', op: 'is', value: 'desktop' }),
      { match: 'any' as const, conditions: [{ field: 'hour', op: 'gte', value: 20 }] },
      { negate: true, conditions: [{ field: 'city', op: 'is', value: 'London' }] },
    ];
    for (const r of rules) expect(explain(r, visitor()).matched).toBe(matches(r, visitor()));
  });
});

describe('the declared surface', () => {
  it('every documented field is reachable', () => {
    /*
     * A field named in FIELDS but not wired into `read()` would silently never
     * match — the widget just quietly stops appearing and nobody knows why.
     *
     * Asserted against a visitor with *every* field populated: "absent on this
     * fixture" and "unreadable by the engine" look identical otherwise, and
     * only the second is a bug.
     */
    const full = visitor({
      utm: { source: 's', medium: 'm', campaign: 'c', term: 't', content: 'x' },
    });
    for (const field of FIELDS) {
      const out = explain(all({ field, op: 'exists' }), full);
      expect(out.reasons[0]!.actual, `${field} is declared but not wired into read()`)
        .toBeDefined();
      expect(out.matched, `${field} is declared but never matches`).toBe(true);
    }
  });

  it('declares 23 fields and 16 operators', () => {
    // Pinned so adding one to the union without wiring it up fails here rather
    // than in a customer's browser. The brief asks for 20; these are the 23 the
    // source products actually targeted on.
    expect(FIELDS.length).toBe(23);
    expect(OPERATORS.length).toBe(16);
  });
});

describe('validateTargeting', () => {
  it('accepts an empty rule — no targeting legitimately means everyone', () => {
    expect(validateTargeting(undefined)).toEqual([]);
    expect(validateTargeting({})).toEqual([]);
    expect(validateTargeting({ match: 'all', conditions: [] })).toEqual([]);
  });

  it('accepts a well-formed rule', () => {
    expect(
      validateTargeting({
        match: 'all',
        conditions: [{ field: 'country', op: 'is', value: 'DE' }],
        groups: [{ match: 'any', conditions: [{ field: 'os', op: 'is', value: 'iOS' }] }],
      }),
    ).toEqual([]);
  });

  it('catches the shape that silently matches everyone', () => {
    /*
     * The bug this exists for. `{ all: [...] }` reads like it says Germany, but
     * `matches` sees a group with no `conditions` — an empty group — and an
     * empty group matches the whole world. On a widget that is visible. On a
     * redirect it sends everybody to the wrong site and nothing looks broken.
     */
    const problems = validateTargeting({ all: [{ field: 'country', op: 'is', value: 'DE' }] });
    expect(problems).toHaveLength(1);
    expect(problems[0]!.message).toContain('“all”');
  });

  it('catches a misspelled field and a bogus operator', () => {
    const field = validateTargeting({
      conditions: [{ field: 'countrie', op: 'is', value: 'DE' }],
    });
    expect(field[0]!.path).toBe('conditions[0].field');

    const op = validateTargeting({
      conditions: [{ field: 'country', op: 'equals', value: 'DE' }],
    });
    expect(op[0]!.path).toBe('conditions[0].op');
  });

  it('accepts a declared extension field', () => {
    // Push segments target on `tags` and `days_subscribed`, which are not in
    // the closed browser-side list. Declared extensions must pass.
    expect(
      validateTargeting({ conditions: [{ field: 'tags', op: 'contains', value: 'vip' }] }, ['tags']),
    ).toEqual([]);
    expect(
      validateTargeting({ conditions: [{ field: 'tags', op: 'contains', value: 'vip' }] }),
    ).toHaveLength(1);
  });

  it('requires a value for every operator that compares one', () => {
    expect(validateTargeting({ conditions: [{ field: 'country', op: 'is' }] })).toHaveLength(1);
    // …and not for the two that do not.
    expect(validateTargeting({ conditions: [{ field: 'city', op: 'exists' }] })).toEqual([]);
  });

  it('reports the path into a nested group', () => {
    const problems = validateTargeting({
      match: 'all',
      groups: [{ conditions: [{ field: 'nope', op: 'is', value: 1 }] }],
    });
    expect(problems[0]!.path).toBe('groups[0].conditions[0].field');
  });

  it('refuses a rule that is not a group at all', () => {
    expect(validateTargeting('country = DE')).toHaveLength(1);
    expect(validateTargeting([{ field: 'country', op: 'is', value: 'DE' }])).toHaveLength(1);
    expect(validateTargeting({ match: 'either' })).toHaveLength(1);
  });
});
