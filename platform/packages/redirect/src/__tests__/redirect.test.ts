import { describe, expect, it } from 'vitest';
import { decorate, pickVariant, resolve, type Link, type Rule } from '../index.ts';

const link = (over: Partial<Link> = {}): Link => ({
  id: 'l1',
  kind: 'short',
  destinationUrl: 'https://shop.test/product',
  isEnabled: true,
  moderationStatus: 'ok',
  expiresAt: null,
  expiresUrl: null,
  maxClicks: null,
  clicksCount: 0,
  passwordHash: null,
  settings: {},
  ...over,
});

const rule = (over: Partial<Rule> = {}): Rule => ({
  id: 'r1',
  priority: 0,
  match: {},
  action: { type: 'redirect', destinationUrl: 'https://shop.test/uk' },
  sticky: true,
  isEnabled: true,
  ...over,
});

const visitor = (over: Record<string, unknown> = {}) => ({
  country: 'GB', device: 'desktop' as const, os: 'macOS', browser: 'Chrome',
  language: 'en-GB', visitorHash: 'abc123', ...over,
});

const go = (i: Partial<Parameters<typeof resolve>[0]> = {}) =>
  resolve({ link: link(), rules: [], visitor: visitor(), ...i });

describe('the plain case', () => {
  it('redirects to the destination', () => {
    expect(go()).toMatchObject({ kind: 'redirect', url: 'https://shop.test/product' });
  });

  it('uses 302, never 301', () => {
    /*
     * A permanent redirect is cached by the browser forever. Change the
     * destination afterwards and everyone who followed the old one keeps going
     * to the wrong place, unreachable. Every link here is editable, so none of
     * them are permanent.
     */
    const out = go();
    expect(out.kind === 'redirect' && out.status).toBe(302);
  });

  it('is not found when there is nowhere to go', () => {
    expect(go({ link: link({ destinationUrl: null }) })).toEqual({ kind: 'not_found' });
  });
});

describe('states that end the request', () => {
  it('blocks a moderated link', () => {
    expect(go({ link: link({ moderationStatus: 'blocked' }) }))
      .toEqual({ kind: 'blocked', reason: 'moderation' });
  });

  it('blocks a disabled link', () => {
    expect(go({ link: link({ isEnabled: false }) }))
      .toEqual({ kind: 'blocked', reason: 'disabled' });
  });

  it('retires an expired link to its fallback', () => {
    const out = go({
      link: link({ expiresAt: '2020-01-01T00:00:00Z', expiresUrl: 'https://shop.test/gone' }),
    });
    expect(out).toEqual({ kind: 'gone', reason: 'expired', url: 'https://shop.test/gone' });
  });

  it('still works when a link expires with no fallback', () => {
    expect(go({ link: link({ expiresAt: '2020-01-01T00:00:00Z' }) }))
      .toEqual({ kind: 'gone', reason: 'expired', url: null });
  });

  it('honours a click limit', () => {
    expect(go({ link: link({ maxClicks: 5, clicksCount: 5 }) }))
      .toMatchObject({ kind: 'gone', reason: 'click_limit' });
    expect(go({ link: link({ maxClicks: 5, clicksCount: 4 }) })).toMatchObject({ kind: 'redirect' });
  });

  it('treats maxClicks 0 as no limit', () => {
    expect(go({ link: link({ maxClicks: 0, clicksCount: 999 }) })).toMatchObject({ kind: 'redirect' });
  });

  it('ignores an unparseable expiry rather than 500ing', () => {
    expect(go({ link: link({ expiresAt: 'not a date' }) })).toMatchObject({ kind: 'redirect' });
  });
});

describe('the password gate', () => {
  it('gates before any rule is evaluated', () => {
    /*
     * The leak this prevents: a rotation exposes every variant to anyone who
     * reloads enough, so evaluating rules for an unauthenticated visitor would
     * reveal exactly what the password exists to protect.
     */
    const out = go({
      link: link({ passwordHash: 'x' }),
      rules: [rule({ action: { type: 'redirect', destinationUrl: 'https://secret.test' } })],
    });
    expect(out).toEqual({ kind: 'password', linkId: 'l1' });
    expect(JSON.stringify(out)).not.toContain('secret.test');
  });

  it('lets a verified visitor through to the rules', () => {
    const out = go({
      link: link({ passwordHash: 'x' }),
      rules: [rule()],
      passwordVerified: true,
    });
    expect(out).toMatchObject({ kind: 'redirect', url: 'https://shop.test/uk' });
  });
});

describe('rules', () => {
  it('first match wins, by priority not array order', () => {
    const out = go({
      rules: [
        rule({ id: 'second', priority: 10, action: { type: 'redirect', destinationUrl: 'https://b.test' } }),
        rule({ id: 'first', priority: 1, action: { type: 'redirect', destinationUrl: 'https://a.test' } }),
      ],
    });
    expect(out).toMatchObject({ url: 'https://a.test', ruleId: 'first' });
  });

  it('skips a rule whose conditions do not match', () => {
    const out = go({
      rules: [
        rule({
          match: { conditions: [{ field: 'country', op: 'is', value: 'US' }] },
          action: { type: 'redirect', destinationUrl: 'https://us.test' },
        }),
      ],
    });
    expect(out).toMatchObject({ url: 'https://shop.test/product' });
  });

  it('routes on the visitor, using the same engine widgets use', () => {
    const rules = [
      rule({
        id: 'gb',
        match: { conditions: [{ field: 'country', op: 'is', value: 'GB' }] },
        action: { type: 'redirect', destinationUrl: 'https://shop.test/uk' },
      }),
    ];
    expect(go({ rules })).toMatchObject({ url: 'https://shop.test/uk' });
    expect(go({ rules, visitor: visitor({ country: 'DE' }) }))
      .toMatchObject({ url: 'https://shop.test/product' });
  });

  it('a disabled rule is inert', () => {
    const out = go({ rules: [rule({ isEnabled: false })] });
    expect(out).toMatchObject({ url: 'https://shop.test/product' });
  });

  it('a block rule ends the request', () => {
    expect(go({ rules: [rule({ action: { type: 'block' } })] }))
      .toEqual({ kind: 'blocked', reason: 'rule' });
  });
});

describe('rotation', () => {
  const variants = [
    { url: 'https://a.test', weight: 50 },
    { url: 'https://b.test', weight: 50 },
  ];
  const rotating = (over: Partial<Rule> = {}) =>
    rule({ action: { type: 'rotate', variants }, ...over });

  it('picks a variant', () => {
    const out = go({ rules: [rotating()] });
    expect(out.kind).toBe('redirect');
    expect(['https://a.test', 'https://b.test']).toContain((out as { url: string }).url);
  });

  it('is deterministic for the same visitor', () => {
    // Determinism is what lets an assignment be recomputed rather than looked
    // up when the store is cold, and it is why this is not Math.random().
    const a = go({ rules: [rotating()] });
    const b = go({ rules: [rotating()] });
    expect(a).toEqual(b);
  });

  it('honours a prior sticky assignment over a fresh pick', () => {
    /*
     * Without stickiness a rotation is not a test: a visitor who lands on B and
     * refreshes onto A has been counted twice and converted once.
     */
    const forced = go({
      rules: [rotating()],
      assignment: { ruleId: 'r1', variantIndex: 1 },
    });
    expect(forced).toMatchObject({ url: 'https://b.test', variantIndex: 1 });
  });

  it('ignores an assignment belonging to another rule', () => {
    const out = go({
      rules: [rotating({ id: 'mine' })],
      assignment: { ruleId: 'someone-elses', variantIndex: 1 },
    });
    expect(out).toMatchObject({ ruleId: 'mine' });
  });

  it('ignores an out-of-range assignment rather than crashing', () => {
    // A variant removed after somebody was assigned to it.
    const out = go({ rules: [rotating()], assignment: { ruleId: 'r1', variantIndex: 99 } });
    expect(out.kind).toBe('redirect');
    expect(['https://a.test', 'https://b.test']).toContain((out as { url: string }).url);
  });

  it('a declared winner ends the test for everyone', () => {
    // So concluding an experiment does not mean deleting the rule and losing
    // its history.
    const out = go({
      rules: [
        rule({
          action: {
            type: 'rotate',
            variants: [
              { url: 'https://a.test', weight: 50 },
              { url: 'https://b.test', weight: 50, isWinner: true },
            ],
          },
        }),
      ],
      assignment: { ruleId: 'r1', variantIndex: 0 },
    });
    expect(out).toMatchObject({ url: 'https://b.test', variantIndex: 1 });
  });

  it('skips a rotation with no variants instead of dead-ending', () => {
    const out = go({ rules: [rule({ action: { type: 'rotate', variants: [] } })] });
    expect(out).toMatchObject({ url: 'https://shop.test/product' });
  });

  it('respects weights across many visitors', () => {
    const weighted = [
      { url: 'https://a.test', weight: 90 },
      { url: 'https://b.test', weight: 10 },
    ];
    let a = 0;
    for (let i = 0; i < 2000; i++) {
      if (pickVariant(weighted, `visitor-${i}`) === 0) a++;
    }
    // 90/10 with tolerance for hash distribution.
    expect(a / 2000).toBeGreaterThan(0.85);
    expect(a / 2000).toBeLessThan(0.95);
  });

  it('never picks a zero-weight variant', () => {
    const off = [
      { url: 'https://a.test', weight: 0 },
      { url: 'https://b.test', weight: 100 },
    ];
    for (let i = 0; i < 200; i++) expect(pickVariant(off, `v${i}`)).toBe(1);
  });
});

describe('decoration', () => {
  it('applies the link’s UTM', () => {
    const out = decorate('https://shop.test/p', { source: 'newsletter' }, undefined, false);
    expect(out).toContain('utm_source=newsletter');
  });

  it('does not double-prefix an already-prefixed key', () => {
    expect(decorate('https://shop.test/p', { utm_medium: 'email' }, undefined, false))
      .toContain('utm_medium=email');
  });

  it('forwards the incoming query when asked', () => {
    const out = decorate('https://shop.test/p', undefined, 'ref=twitter&x=1', true);
    expect(out).toContain('ref=twitter');
    expect(out).toContain('x=1');
  });

  it('the link’s own UTM beats what arrived on the request', () => {
    // The campaign the link belongs to is what the customer is measuring.
    const out = decorate('https://shop.test/p', { source: 'ours' }, 'utm_source=theirs', true);
    expect(out).toContain('utm_source=ours');
    expect(out).not.toContain('utm_source=theirs');
  });

  it('leaves a malformed destination alone rather than throwing', () => {
    // A redirect that 500s is worse than one that goes somewhere slightly odd.
    expect(decorate('not a url', { source: 'x' }, undefined, true)).toBe('not a url');
  });

  it('does nothing when there is nothing to add', () => {
    expect(decorate('https://shop.test/p', undefined, undefined, true)).toBe('https://shop.test/p');
  });
});

describe('deep links, splash and interstitials', () => {
  const deepLink = { ios: 'myapp://product/1', android: 'intent://product/1' };

  it('sends iOS to the iOS scheme', () => {
    const out = go({
      link: link({ settings: { deepLink } }),
      visitor: visitor({ os: 'iOS' }),
    });
    expect(out).toMatchObject({ url: 'myapp://product/1' });
  });

  it('leaves desktop on the web destination', () => {
    const out = go({ link: link({ settings: { deepLink } }), visitor: visitor({ os: 'macOS' }) });
    expect(out).toMatchObject({ url: 'https://shop.test/product' });
  });

  it('routes through a splash page when one is set', () => {
    const out = go({ link: link({ settings: { splashPageId: 'sp1' } }) });
    expect(out).toMatchObject({ kind: 'splash', splashPageId: 'sp1', url: 'https://shop.test/product' });
  });

  it('warns before sensitive content, ahead of any splash', () => {
    const out = go({
      link: link({ settings: { sensitiveContent: true, splashPageId: 'sp1' } }),
    });
    expect(out).toMatchObject({ kind: 'interstitial', reason: 'sensitive' });
  });
});

describe('links that render rather than redirect', () => {
  it.each([
    ['biolink', 'biolink'],
    ['vcard', 'vcard'],
    ['event', 'event'],
    ['transfer', 'transfer'],
    ['file', 'transfer'],
    ['static', 'static'],
  ])('%s renders', (kind, what) => {
    expect(go({ link: link({ kind }) })).toEqual({ kind: 'render', linkId: 'l1', what });
  });

  it('still gates a private biolink behind its password', () => {
    expect(go({ link: link({ kind: 'biolink', passwordHash: 'x' }) }))
      .toEqual({ kind: 'password', linkId: 'l1' });
  });

  it('still refuses an expired biolink', () => {
    expect(go({ link: link({ kind: 'biolink', expiresAt: '2020-01-01T00:00:00Z' }) }))
      .toMatchObject({ kind: 'gone' });
  });
});
