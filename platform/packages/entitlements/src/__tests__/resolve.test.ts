import { describe, expect, it } from 'vitest';
import { resolve } from '../resolve.ts';
import type { EntitlementRow, FeatureRow, ResolveContext } from '../types.ts';

const feature = (over: Partial<FeatureRow> = {}): FeatureRow => ({
  key: 'link.links',
  tool: 'link',
  kind: 'limit',
  isAi: false,
  freeTierAllowed: true,
  defaultCreditCost: 0,
  ...over,
});

const ent = (over: Partial<EntitlementRow> = {}): EntitlementRow => ({
  planId: 'p',
  planKey: 'free',
  planKind: 'free',
  featureKey: 'link.links',
  mode: 'limit',
  limitValue: 25,
  quotaValue: null,
  quotaPeriod: 'month',
  creditCost: null,
  overage: 'block',
  ...over,
});

const ctx = (over: Partial<ResolveContext> = {}): ResolveContext => ({
  workspaceId: 'ws',
  entitlements: [ent()],
  feature: feature(),
  used: 0,
  creditBalance: 0,
  hasLifetimePlan: false,
  hasNonLifetimeAiGrant: false,
  aiInstanceEnabled: true,
  aiTenantEnabled: true,
  aiFeatureEnabled: true,
  lifetimeAiViaCredits: false,
  toolInstalled: true,
  ...over,
});

describe('merge arithmetic', () => {
  it('takes the MAX of limits across plans', () => {
    const d = resolve(
      ctx({
        entitlements: [
          ent({ planKey: 'unified_starter', limitValue: 500 }),
          ent({ planKey: 'link_pro', limitValue: 10_000 }),
          ent({ planKey: 'free', limitValue: 25 }),
        ],
      }),
    );
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.limit).toBe(10_000);
  });

  it('treats -1 as unlimited and lets it win over any finite limit', () => {
    const d = resolve(
      ctx({
        used: 999_999,
        entitlements: [ent({ limitValue: 10 }), ent({ limitValue: -1 })],
      }),
    );
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.limit).toBe(-1);
  });

  it('SUMS quotas across plans, because headroom should stack', () => {
    const f = feature({ key: 'track.pageviews', tool: 'track', kind: 'quota' });
    const d = resolve(
      ctx({
        feature: f,
        used: 120_000,
        entitlements: [
          ent({ featureKey: f.key, mode: 'quota', limitValue: null, quotaValue: 50_000 }),
          ent({ featureKey: f.key, mode: 'quota', limitValue: null, quotaValue: 100_000 }),
        ],
      }),
    );
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.quota).toBe(150_000);
  });

  it('the free floor drops out of the SUM once a paid plan has an opinion', () => {
    const f = feature({ key: 'track.pageviews', tool: 'track', kind: 'quota' });
    const q = (planKind: string, quotaValue: number) =>
      ent({ planKind, featureKey: f.key, mode: 'quota', limitValue: null, quotaValue });
    const d = resolve(
      ctx({
        feature: f,
        entitlements: [q('free', 10_000), q('tool', 50_000), q('unified', 100_000)],
      }),
    );
    // 150k, not 160k — paying customers do not also get the free allowance.
    expect(d.allowed && d.quota).toBe(150_000);
  });

  it('but the free floor still applies when no paid plan mentions the feature', () => {
    const f = feature({ key: 'track.pageviews', tool: 'track', kind: 'quota' });
    const d = resolve(
      ctx({
        feature: f,
        entitlements: [
          ent({ planKind: 'free', featureKey: f.key, mode: 'quota', limitValue: null, quotaValue: 10_000 }),
        ],
      }),
    );
    expect(d.allowed && d.quota).toBe(10_000);
  });

  it('a deny loses when another plan has an opinion', () => {
    const d = resolve(
      ctx({ entitlements: [ent({ mode: 'deny' }), ent({ mode: 'limit', limitValue: 100 })] }),
    );
    expect(d.allowed).toBe(true);
  });

  it('a deny wins when it is the only opinion', () => {
    const d = resolve(ctx({ entitlements: [ent({ mode: 'deny' })] }));
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('not_in_plan');
  });
});

describe('limits and overage', () => {
  it('blocks at the limit and offers a plan upsell', () => {
    const d = resolve(ctx({ used: 25, entitlements: [ent({ limitValue: 25 })] }));
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('limit_reached');
    expect(!d.allowed && d.upsell?.kind).toBe('plan');
  });

  it('converts overage to credits when the plan says so', () => {
    const f = feature({ key: 'audit.crawl_pages', kind: 'quota', defaultCreditCost: 1 });
    const d = resolve(
      ctx({
        feature: f,
        used: 5_000,
        creditBalance: 500,
        entitlements: [
          ent({ featureKey: f.key, mode: 'quota', limitValue: null, quotaValue: 5_000, overage: 'credits', creditCost: 1 }),
        ],
      }),
      10,
    );
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.cost).toBe(10);
  });

  it('soft overage allows and charges nothing', () => {
    const d = resolve(ctx({ used: 30, entitlements: [ent({ limitValue: 25, overage: 'soft' })] }));
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.source).toBe('overage_soft');
  });
});

describe('credits', () => {
  const aiImage = feature({ key: 'market.ai_image', tool: 'market', kind: 'metered', isAi: true, freeTierAllowed: false, defaultCreditCost: 8 });

  it('charges the cheapest per-unit rate across plans', () => {
    const d = resolve(
      ctx({
        feature: aiImage,
        creditBalance: 100,
        entitlements: [
          ent({ featureKey: aiImage.key, mode: 'credits', limitValue: null, creditCost: 12 }),
          ent({ featureKey: aiImage.key, mode: 'credits', limitValue: null, creditCost: 8 }),
        ],
      }),
    );
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.cost).toBe(8);
  });

  it('multiplies by quantity', () => {
    const d = resolve(
      ctx({
        feature: aiImage,
        creditBalance: 100,
        entitlements: [ent({ featureKey: aiImage.key, mode: 'credits', limitValue: null, creditCost: 8 })],
      }),
      4,
    );
    expect(d.allowed && d.cost).toBe(32);
  });

  it('denies with a credit upsell naming the shortfall', () => {
    const d = resolve(
      ctx({
        feature: aiImage,
        creditBalance: 3,
        entitlements: [ent({ featureKey: aiImage.key, mode: 'credits', limitValue: null, creditCost: 8 })],
      }),
    );
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('insufficient_credits');
    expect(!d.allowed && d.upsell?.creditsNeeded).toBe(5);
  });
});

describe('AI kill switches — the first failure is what the user is told', () => {
  const ai = feature({ key: 'audit.ai_summary', tool: 'audit', kind: 'metered', isAi: true, defaultCreditCost: 25 });
  const aiCtx = (over: Partial<ResolveContext> = {}) =>
    ctx({
      feature: ai,
      creditBalance: 1000,
      entitlements: [ent({ featureKey: ai.key, mode: 'credits', limitValue: null, creditCost: 25 })],
      ...over,
    });

  it('instance switch wins over everything', () => {
    const d = resolve(aiCtx({ aiInstanceEnabled: false, aiTenantEnabled: false, aiFeatureEnabled: false }));
    expect(!d.allowed && d.reason).toBe('ai_disabled_instance');
  });

  it('tenant switch is reported when the instance is on', () => {
    const d = resolve(aiCtx({ aiTenantEnabled: false, aiFeatureEnabled: false }));
    expect(!d.allowed && d.reason).toBe('ai_disabled_tenant');
    expect(!d.allowed && d.message).toMatch(/workspace/i);
  });

  it('per-feature switch is reported last', () => {
    const d = resolve(aiCtx({ aiFeatureEnabled: false }));
    expect(!d.allowed && d.reason).toBe('ai_disabled_feature');
  });

  it('does not apply the AI switches to non-AI features', () => {
    const d = resolve(ctx({ aiInstanceEnabled: false }));
    expect(d.allowed).toBe(true);
  });
});

describe('lifetime excludes AI (resolver — enforcement point 2 of 3)', () => {
  const ai = feature({ key: 'market.ai_image', tool: 'market', kind: 'metered', isAi: true, defaultCreditCost: 8 });
  const lifetime = (over: Partial<ResolveContext> = {}) =>
    ctx({
      feature: ai,
      creditBalance: 5_000,
      hasLifetimePlan: true,
      entitlements: [ent({ featureKey: ai.key, planKind: 'lifetime', mode: 'credits', limitValue: null, creditCost: 8 })],
      ...over,
    });

  it('blocks AI even when the workspace has plenty of credits', () => {
    const d = resolve(lifetime());
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('ai_excluded_lifetime');
  });

  it('still allows non-AI features on a lifetime plan', () => {
    const d = resolve(ctx({ hasLifetimePlan: true }));
    expect(d.allowed).toBe(true);
  });

  it('a separately purchased subscription un-blocks AI — lifetime does not poison it', () => {
    const d = resolve(lifetime({ hasNonLifetimeAiGrant: true }));
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.cost).toBe(8);
  });

  it('the instance escape hatch lets lifetime holders spend credits on AI', () => {
    const d = resolve(lifetime({ lifetimeAiViaCredits: true }));
    expect(d.allowed).toBe(true);
  });

  it('lifetime cannot buy past AI via a credits OVERAGE either', () => {
    const d = resolve(
      lifetime({
        used: 100,
        entitlements: [
          ent({ featureKey: ai.key, planKind: 'lifetime', mode: 'quota', limitValue: null, quotaValue: 100, overage: 'credits', creditCost: 8 }),
        ],
      }),
    );
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('ai_excluded_lifetime');
  });
});

describe('availability', () => {
  it('reports an uninstalled tool distinctly from an unentitled one', () => {
    const d = resolve(ctx({ toolInstalled: false }));
    expect(!d.allowed && d.reason).toBe('tool_unavailable');
  });
});

describe('the worked example: Link Pro + Unified Starter + 5,000 purchased credits', () => {
  const plans: EntitlementRow[] = [
    ent({ planKey: 'link_pro', planKind: 'tool', featureKey: 'link.links', mode: 'limit', limitValue: 10_000 }),
    ent({ planKey: 'unified_starter', planKind: 'unified', featureKey: 'link.links', mode: 'limit', limitValue: 500 }),
    ent({ planKey: 'free', planKind: 'free', featureKey: 'link.links', mode: 'limit', limitValue: 25 }),
    ent({ planKey: 'unified_starter', planKind: 'unified', featureKey: 'monitor.monitors', mode: 'limit', limitValue: 20 }),
    ent({ planKey: 'free', planKind: 'free', featureKey: 'monitor.monitors', mode: 'limit', limitValue: 3 }),
    ent({ planKey: 'link_pro', planKind: 'tool', featureKey: 'track.pageviews', mode: 'quota', limitValue: null, quotaValue: 50_000 }),
    ent({ planKey: 'unified_starter', planKind: 'unified', featureKey: 'track.pageviews', mode: 'quota', limitValue: null, quotaValue: 100_000 }),
    ent({ planKey: 'unified_starter', planKind: 'unified', featureKey: 'market.ai_image', mode: 'credits', limitValue: null, creditCost: 8 }),
  ];
  const base = (f: FeatureRow, used = 0) =>
    ctx({ feature: f, entitlements: plans, used, creditBalance: 6_500 });

  it('link.links resolves to 10,000 (MAX, Link Pro wins)', () => {
    const d = resolve(base(feature({ key: 'link.links' })));
    expect(d.allowed && d.limit).toBe(10_000);
  });

  it('monitor.monitors resolves to 20 — Link Pro is silent, Unified grants', () => {
    const d = resolve(base(feature({ key: 'monitor.monitors', tool: 'monitor' })));
    expect(d.allowed && d.limit).toBe(20);
  });

  it('track.pageviews resolves to 150,000 (SUM)', () => {
    const d = resolve(base(feature({ key: 'track.pageviews', tool: 'track', kind: 'quota' }), 120_000));
    expect(d.allowed && d.quota).toBe(150_000);
  });

  it('market.ai_image costs 8 credits from the 6,500 balance', () => {
    const f = feature({ key: 'market.ai_image', tool: 'market', kind: 'metered', isAi: true, defaultCreditCost: 8 });
    const d = resolve(base(f));
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.cost).toBe(8);
  });
});

describe('an exceeded allowance still reports the allowance', () => {
  // Regression: the overage paths used to return `allowed: true` with only a
  // cost, dropping quota/limit/used. The UI then went silent at exactly the
  // moment the meter started running, so a user was charged per page with
  // nothing on screen saying so.
  const quotaFeature = feature({ key: 'audit.crawl_pages', tool: 'audit', kind: 'quota', defaultCreditCost: 1 });

  const overQuota = (overage: 'credits' | 'soft' | 'block', creditBalance = 100) =>
    ctx({
      feature: quotaFeature,
      entitlements: [
        ent({
          featureKey: 'audit.crawl_pages',
          planKey: 'starter',
          planKind: 'subscription',
          mode: 'quota',
          limitValue: null,
          quotaValue: 500,
          overage,
        }),
      ],
      used: 600,
      creditBalance,
    });

  it('carries quota and used through a credits overage', () => {
    const d = resolve(overQuota('credits'));
    expect(d.allowed).toBe(true);
    expect(d.allowed && d.cost).toBe(1);
    expect(d.quota).toBe(500);
    expect(d.used).toBe(600);
  });

  it('carries quota and used through a soft overage', () => {
    const d = resolve(overQuota('soft'));
    expect(d.allowed).toBe(true);
    expect(d.quota).toBe(500);
    expect(d.used).toBe(600);
  });

  it('carries quota and used when the overage cannot be paid for', () => {
    const d = resolve(overQuota('credits', 0));
    expect(d.allowed).toBe(false);
    expect(!d.allowed && d.reason).toBe('insufficient_credits');
    expect(d.quota).toBe(500);
    expect(d.used).toBe(600);
  });

  it('says "1 credit", not "1 credits"', () => {
    const d = resolve(overQuota('credits', 0));
    expect(!d.allowed && d.message).toContain('1 credit and');
  });
});
