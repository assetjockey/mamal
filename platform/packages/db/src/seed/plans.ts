import type { EntitlementMode, OverageBehaviour, PlanKind } from '../schema/billing.ts';

export type EntitlementSeed = {
  feature: string;
  mode: EntitlementMode;
  limit?: number; // -1 = unlimited. Merged across plans with MAX.
  quota?: number; // Merged across plans with SUM.
  quotaPeriod?: 'day' | 'month' | 'year' | 'lifetime';
  creditCost?: number;
  overage?: OverageBehaviour;
};

export type PlanSeed = {
  key: string;
  name: string;
  description?: string;
  kind: PlanKind;
  tool?: string;
  tierRank: number;
  isDefaultSignup?: boolean;
  trialDays?: number;
  prices?: { interval: 'month' | 'year' | 'once'; amountCents: number }[];
  creditGrant?: { amount: number; cadence: 'per_period' | 'once'; expiresAfterDays?: number; rollover?: boolean };
  entitlements: EntitlementSeed[];
};

const unlimited = -1;

/**
 * FREE — the floor every workspace always has, and the definition of
 * "costs the platform nothing to run": no metered vendor call, no dedicated
 * compute, no bus subscriptions, no automations.
 */
const FREE: PlanSeed = {
  key: 'free',
  name: 'Free',
  description: 'Everything that runs on already-paid edge capacity.',
  kind: 'free',
  tierRank: 0,
  isDefaultSignup: true,
  entitlements: [
    { feature: 'core.workspaces', mode: 'limit', limit: 1 },
    { feature: 'core.members', mode: 'limit', limit: 1 },
    { feature: 'core.projects', mode: 'limit', limit: 1 },
    { feature: 'core.api', mode: 'deny' },
    { feature: 'core.automations', mode: 'deny' },
    { feature: 'core.custom_domains', mode: 'deny' },
    { feature: 'core.notification_channels', mode: 'limit', limit: 1 },
    { feature: 'core.storage', mode: 'quota', quota: 1 },
    { feature: 'core.white_label', mode: 'deny' },
    { feature: 'core.export', mode: 'allow' },
    { feature: 'core.data_retention_days', mode: 'limit', limit: 7 },

    { feature: 'audit.sites', mode: 'limit', limit: 1 },
    { feature: 'audit.crawl_pages', mode: 'quota', quota: 25, quotaPeriod: 'month', overage: 'block' },
    { feature: 'audit.schedule', mode: 'deny' },
    { feature: 'audit.lighthouse', mode: 'deny' },
    { feature: 'audit.tools', mode: 'quota', quota: 90, quotaPeriod: 'month' },

    { feature: 'confirm.campaigns', mode: 'limit', limit: 1 },
    { feature: 'confirm.widgets', mode: 'limit', limit: 3 },
    { feature: 'confirm.impressions', mode: 'quota', quota: 1_000, quotaPeriod: 'month', overage: 'block' },
    { feature: 'confirm.live_sources', mode: 'deny' },
    // Branding is how the free tier pays for itself; stated rather than implied.
    { feature: 'confirm.branding_removal', mode: 'deny' },
    { feature: 'confirm.custom_css', mode: 'deny' },

    { feature: 'link.links', mode: 'limit', limit: 25 },
    { feature: 'link.qr_codes', mode: 'limit', limit: 25 },
    { feature: 'link.bio_pages', mode: 'limit', limit: 1 },
    { feature: 'link.rules', mode: 'deny' },
    { feature: 'link.transfers', mode: 'limit', limit: 1 },
    { feature: 'link.transfer_size_mb', mode: 'limit', limit: 100 },
    { feature: 'link.qr_server_render', mode: 'deny' },

    { feature: 'market.gsc_connections', mode: 'limit', limit: 1 },
    { feature: 'market.ga4_connections', mode: 'limit', limit: 1 },
    { feature: 'market.trend_watches', mode: 'limit', limit: 3 },
    { feature: 'market.social_accounts', mode: 'limit', limit: 1 },
    { feature: 'market.scheduled_posts', mode: 'quota', quota: 10, quotaPeriod: 'month' },
    { feature: 'market.dataforseo', mode: 'deny' },

    { feature: 'monitor.monitors', mode: 'limit', limit: 3 },
    { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 300 },
    { feature: 'monitor.regions', mode: 'limit', limit: 1 },
    { feature: 'monitor.status_pages', mode: 'limit', limit: 1 },

    { feature: 'track.sites', mode: 'limit', limit: 1 },
    { feature: 'track.pageviews', mode: 'quota', quota: 10_000, quotaPeriod: 'month', overage: 'block' },
    { feature: 'track.replays', mode: 'deny' },
    { feature: 'track.heatmaps', mode: 'deny' },
  ],
};

/** Per-tool subscriptions: two tiers each, so `MAX` merging is observable. */
function toolPlan(
  tool: string,
  tier: 'starter' | 'pro',
  priceMonth: number,
  credits: number,
  entitlements: EntitlementSeed[],
): PlanSeed {
  return {
    key: `${tool}_${tier}`,
    name: `${tool[0]!.toUpperCase()}${tool.slice(1)} ${tier === 'pro' ? 'Pro' : 'Starter'}`,
    kind: 'tool',
    tool,
    tierRank: tier === 'pro' ? 2 : 1,
    trialDays: 14,
    prices: [
      { interval: 'month', amountCents: priceMonth },
      { interval: 'year', amountCents: priceMonth * 10 },
    ],
    creditGrant: { amount: credits, cadence: 'per_period', expiresAfterDays: 30 },
    entitlements: [
      { feature: 'core.api', mode: 'allow' },
      { feature: 'core.export', mode: 'allow' },
      { feature: 'core.white_label', mode: tier === 'pro' ? 'allow' : 'deny' },
      { feature: 'core.automations', mode: 'limit', limit: tier === 'pro' ? 50 : 10 },
      { feature: 'core.data_retention_days', mode: 'limit', limit: tier === 'pro' ? 180 : 90 },
      ...entitlements,
    ],
  };
}

const TOOL_PLANS: PlanSeed[] = [
  toolPlan('audit', 'starter', 1900, 200, [
    { feature: 'audit.sites', mode: 'limit', limit: 5 },
    { feature: 'audit.crawl_pages', mode: 'quota', quota: 5_000, overage: 'credits' },
    { feature: 'audit.schedule', mode: 'allow' },
    { feature: 'audit.lighthouse', mode: 'quota', quota: 100, overage: 'credits' },
    { feature: 'audit.tools', mode: 'quota', quota: -1 },
  ]),
  toolPlan('audit', 'pro', 4900, 750, [
    { feature: 'audit.sites', mode: 'limit', limit: 25 },
    { feature: 'audit.crawl_pages', mode: 'quota', quota: 50_000, overage: 'credits' },
    { feature: 'audit.schedule', mode: 'allow' },
    { feature: 'audit.lighthouse', mode: 'quota', quota: 1_000, overage: 'credits' },
    { feature: 'audit.js_rendering', mode: 'quota', quota: 5_000, overage: 'credits' },
    { feature: 'audit.white_label_reports', mode: 'allow' },
    { feature: 'audit.tools', mode: 'quota', quota: -1 },
    { feature: 'audit.ai_summary', mode: 'credits', creditCost: 25 },
    { feature: 'audit.ai_fix_brief', mode: 'credits', creditCost: 25 },
    { feature: 'audit.ai_alt_text', mode: 'credits', creditCost: 1 },
  ]),

  toolPlan('confirm', 'starter', 1900, 200, [
    { feature: 'confirm.campaigns', mode: 'limit', limit: 3 },
    { feature: 'confirm.widgets', mode: 'limit', limit: 20 },
    { feature: 'confirm.impressions', mode: 'quota', quota: 50_000, overage: 'credits' },
    { feature: 'confirm.branding_removal', mode: 'allow' },
    { feature: 'confirm.custom_css', mode: 'allow' },
    { feature: 'confirm.live_sources', mode: 'allow' },
  ]),
  toolPlan('confirm', 'pro', 4900, 750, [
    { feature: 'confirm.campaigns', mode: 'limit', limit: 25 },
    { feature: 'confirm.widgets', mode: 'limit', limit: unlimited },
    { feature: 'confirm.impressions', mode: 'quota', quota: 500_000, overage: 'credits' },
    { feature: 'confirm.live_sources', mode: 'allow' },
    { feature: 'confirm.push_subscribers', mode: 'limit', limit: 25_000 },
    { feature: 'confirm.ab_testing', mode: 'allow' },
    { feature: 'confirm.ai_copy', mode: 'credits', creditCost: 5 },
    { feature: 'confirm.ai_translate', mode: 'credits', creditCost: 2 },
  ]),

  toolPlan('link', 'starter', 1900, 200, [
    { feature: 'link.links', mode: 'limit', limit: 1_000 },
    { feature: 'link.qr_codes', mode: 'limit', limit: 1_000 },
    { feature: 'link.bio_pages', mode: 'limit', limit: 5 },
    { feature: 'link.rules', mode: 'allow' },
    { feature: 'link.bulk', mode: 'allow' },
    { feature: 'link.qr_server_render', mode: 'allow' },
    { feature: 'core.custom_domains', mode: 'limit', limit: 1 },
    { feature: 'link.transfers', mode: 'limit', limit: 50 },
    { feature: 'link.transfer_size_mb', mode: 'limit', limit: 2_000 },
  ]),
  // The plan's worked example: Link Pro grants 10 000 links and 5 domains.
  toolPlan('link', 'pro', 4900, 750, [
    { feature: 'link.links', mode: 'limit', limit: 10_000 },
    { feature: 'link.qr_codes', mode: 'limit', limit: 10_000 },
    { feature: 'link.bio_pages', mode: 'limit', limit: 50 },
    { feature: 'link.rules', mode: 'allow' },
    { feature: 'link.deep_links', mode: 'allow' },
    { feature: 'link.cloaking', mode: 'allow' },
    { feature: 'link.bulk', mode: 'allow' },
    { feature: 'link.qr_server_render', mode: 'allow' },
    { feature: 'core.custom_domains', mode: 'limit', limit: 5 },
    { feature: 'link.transfers', mode: 'limit', limit: unlimited },
    { feature: 'link.transfer_size_mb', mode: 'limit', limit: 20_000 },
    { feature: 'track.pageviews', mode: 'quota', quota: 50_000, overage: 'block' },
    { feature: 'link.ai_bio_layout', mode: 'credits', creditCost: 15 },
    { feature: 'link.ai_qr_art', mode: 'credits', creditCost: 20 },
  ]),

  toolPlan('market', 'starter', 3900, 500, [
    // Without these a paying customer inherits the free floor of one, which is
    // wrong for anyone with more than one property — and invisible until they
    // try to add the second.
    { feature: 'market.gsc_connections', mode: 'limit', limit: 5 },
    { feature: 'market.ga4_connections', mode: 'limit', limit: 5 },
    { feature: 'market.content_docs', mode: 'limit', limit: 100 },
    { feature: 'market.trend_watches', mode: 'limit', limit: 10 },
    { feature: 'market.tracked_keywords', mode: 'limit', limit: 250 },
    { feature: 'market.social_accounts', mode: 'limit', limit: 5 },
    { feature: 'market.scheduled_posts', mode: 'quota', quota: 500 },
    { feature: 'market.publish_destinations', mode: 'limit', limit: 2 },
    { feature: 'market.dataforseo', mode: 'credits', creditCost: 1 },
  ]),
  toolPlan('market', 'pro', 9900, 2500, [
    { feature: 'market.gsc_connections', mode: 'limit', limit: 25 },
    { feature: 'market.ga4_connections', mode: 'limit', limit: 25 },
    { feature: 'market.content_docs', mode: 'limit', limit: unlimited },
    { feature: 'market.trend_watches', mode: 'limit', limit: 50 },
    { feature: 'market.social_monitoring', mode: 'allow' },
    { feature: 'market.post_approval', mode: 'allow' },
    { feature: 'market.local', mode: 'limit', limit: 10 },
    { feature: 'market.rank_check', mode: 'credits', creditCost: 1 },
    { feature: 'market.backlinks', mode: 'credits', creditCost: 10 },
    { feature: 'market.ai_brief', mode: 'credits', creditCost: 15 },
    { feature: 'market.ai_caption', mode: 'credits', creditCost: 3 },
    { feature: 'market.ai_meta', mode: 'credits', creditCost: 2 },
    { feature: 'market.tracked_keywords', mode: 'limit', limit: 2_500 },
    { feature: 'market.social_accounts', mode: 'limit', limit: 25 },
    { feature: 'market.scheduled_posts', mode: 'quota', quota: unlimited },
    { feature: 'market.publish_destinations', mode: 'limit', limit: 10 },
    { feature: 'market.ad_accounts', mode: 'limit', limit: 10 },
    { feature: 'market.dataforseo', mode: 'credits', creditCost: 1 },
    { feature: 'market.ai_copy', mode: 'credits', creditCost: 2 },
    { feature: 'market.ai_image', mode: 'credits', creditCost: 8 },
    { feature: 'market.ai_video', mode: 'credits', creditCost: 20 },
    { feature: 'market.ai_blog', mode: 'credits', creditCost: 30 },
    { feature: 'market.ai_visibility', mode: 'credits', creditCost: 10 }, // ×4 models = the advertised 40/probe
  ]),

  toolPlan('monitor', 'starter', 1900, 200, [
    { feature: 'monitor.monitors', mode: 'limit', limit: 20 },
    { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 60 },
    { feature: 'monitor.regions', mode: 'limit', limit: 3 },
    { feature: 'monitor.status_pages', mode: 'limit', limit: 3 },
    { feature: 'core.sms', mode: 'credits', creditCost: 2 },
  ]),
  toolPlan('monitor', 'pro', 4900, 750, [
    { feature: 'monitor.monitors', mode: 'limit', limit: 200 },
    { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 30 },
    { feature: 'monitor.regions', mode: 'limit', limit: 6 },
    { feature: 'monitor.status_pages', mode: 'limit', limit: 25 },
    { feature: 'monitor.private_agents', mode: 'limit', limit: 10 },
    { feature: 'monitor.browser_checks', mode: 'credits', creditCost: 2 },
    { feature: 'monitor.fast_checks', mode: 'credits', creditCost: 1 },
    { feature: 'core.sms', mode: 'credits', creditCost: 2 },
    { feature: 'monitor.ai_rca', mode: 'credits', creditCost: 20 },
  ]),

  toolPlan('track', 'starter', 1900, 200, [
    { feature: 'track.sites', mode: 'limit', limit: 5 },
    { feature: 'track.pageviews', mode: 'quota', quota: 100_000, overage: 'block' },
    { feature: 'track.funnels', mode: 'allow' },
    { feature: 'track.public_dashboards', mode: 'allow' },
  ]),
  toolPlan('track', 'pro', 4900, 750, [
    { feature: 'track.sites', mode: 'limit', limit: 50 },
    { feature: 'track.pageviews', mode: 'quota', quota: 1_000_000, overage: 'block' },
    { feature: 'track.identified_mode', mode: 'allow' },
    { feature: 'track.replays', mode: 'quota', quota: 10_000, overage: 'credits' },
    { feature: 'track.heatmaps', mode: 'allow' },
    { feature: 'track.funnels', mode: 'allow' },
    { feature: 'track.public_dashboards', mode: 'allow' },
    { feature: 'track.competitive', mode: 'credits', creditCost: 1 },
    { feature: 'track.ai_digest', mode: 'credits', creditCost: 15 },
  ]),
];

/** Unified plans — every tool, one price. */
const UNIFIED: PlanSeed[] = [
  {
    key: 'unified_starter',
    name: 'Starter',
    kind: 'unified',
    tierRank: 1,
    trialDays: 14,
    prices: [{ interval: 'month', amountCents: 4900 }, { interval: 'year', amountCents: 49000 }],
    creditGrant: { amount: 1_000, cadence: 'per_period', expiresAfterDays: 30 },
    entitlements: [
      { feature: 'core.api', mode: 'allow' },
      { feature: 'core.members', mode: 'limit', limit: 3 },
      { feature: 'core.projects', mode: 'limit', limit: 5 },
      { feature: 'core.automations', mode: 'limit', limit: 25 },
      { feature: 'core.custom_domains', mode: 'limit', limit: 1 },
      { feature: 'core.notification_channels', mode: 'limit', limit: 10 },
      { feature: 'core.data_retention_days', mode: 'limit', limit: 90 },
      { feature: 'core.storage', mode: 'quota', quota: 50 },
      { feature: 'audit.sites', mode: 'limit', limit: 5 },
      { feature: 'audit.crawl_pages', mode: 'quota', quota: 10_000, overage: 'credits' },
      { feature: 'audit.schedule', mode: 'allow' },
      { feature: 'audit.lighthouse', mode: 'quota', quota: 200, overage: 'credits' },
      { feature: 'confirm.campaigns', mode: 'limit', limit: 3 },
      { feature: 'confirm.widgets', mode: 'limit', limit: 20 },
      { feature: 'confirm.impressions', mode: 'quota', quota: 250_000, overage: 'credits' },
      { feature: 'confirm.live_sources', mode: 'allow' },
      { feature: 'link.links', mode: 'limit', limit: 500 },
      { feature: 'link.qr_codes', mode: 'limit', limit: 500 },
      { feature: 'link.bio_pages', mode: 'limit', limit: 5 },
      { feature: 'link.rules', mode: 'allow' },
      { feature: 'link.qr_server_render', mode: 'allow' },
      { feature: 'market.tracked_keywords', mode: 'limit', limit: 250 },
      { feature: 'market.social_accounts', mode: 'limit', limit: 5 },
      { feature: 'market.dataforseo', mode: 'credits', creditCost: 1 },
      { feature: 'monitor.monitors', mode: 'limit', limit: 20 },
      { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 60 },
      { feature: 'monitor.regions', mode: 'limit', limit: 3 },
      { feature: 'monitor.status_pages', mode: 'limit', limit: 3 },
      { feature: 'track.sites', mode: 'limit', limit: 5 },
      { feature: 'track.pageviews', mode: 'quota', quota: 100_000, overage: 'block' },
      { feature: 'track.funnels', mode: 'allow' },
      // AI available on subscription plans.
      { feature: 'audit.ai_summary', mode: 'credits', creditCost: 25 },
      { feature: 'market.ai_copy', mode: 'credits', creditCost: 2 },
      { feature: 'market.ai_image', mode: 'credits', creditCost: 8 },
    ],
  },
  {
    key: 'unified_growth',
    name: 'Growth',
    kind: 'unified',
    tierRank: 2,
    trialDays: 14,
    prices: [{ interval: 'month', amountCents: 14900 }, { interval: 'year', amountCents: 149000 }],
    creditGrant: { amount: 5_000, cadence: 'per_period', expiresAfterDays: 30, rollover: true },
    entitlements: [
      { feature: 'core.api', mode: 'allow' },
      { feature: 'core.white_label', mode: 'allow' },
      { feature: 'core.members', mode: 'limit', limit: 15 },
      { feature: 'core.projects', mode: 'limit', limit: 25 },
      { feature: 'core.automations', mode: 'limit', limit: 200 },
      { feature: 'core.custom_domains', mode: 'limit', limit: 10 },
      { feature: 'core.data_retention_days', mode: 'limit', limit: 365 },
      { feature: 'core.storage', mode: 'quota', quota: 500 },
      { feature: 'audit.sites', mode: 'limit', limit: 50 },
      { feature: 'audit.crawl_pages', mode: 'quota', quota: 100_000, overage: 'credits' },
      { feature: 'confirm.impressions', mode: 'quota', quota: 2_000_000, overage: 'credits' },
      { feature: 'link.links', mode: 'limit', limit: 25_000 },
      { feature: 'market.tracked_keywords', mode: 'limit', limit: 5_000 },
      { feature: 'monitor.monitors', mode: 'limit', limit: 500 },
      { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 30 },
      { feature: 'track.pageviews', mode: 'quota', quota: 5_000_000, overage: 'block' },
      { feature: 'track.replays', mode: 'quota', quota: 50_000, overage: 'credits' },
      { feature: 'track.heatmaps', mode: 'allow' },
      { feature: 'track.identified_mode', mode: 'allow' },
    ],
  },
];

/**
 * LIFETIME — every AI feature is explicitly denied. The database trigger in
 * scripts/migrate.ts rejects any other mode on a lifetime plan, so this list
 * cannot drift into granting AI by accident.
 */
const LIFETIME: PlanSeed = {
  key: 'lifetime_pro',
  name: 'Lifetime',
  description: 'The whole platform, once. AI is pay-as-you-go with credits.',
  kind: 'lifetime',
  tierRank: 3,
  prices: [{ interval: 'once', amountCents: 49900 }],
  creditGrant: { amount: 0, cadence: 'once' },
  entitlements: [
    { feature: 'core.api', mode: 'allow' },
    { feature: 'core.white_label', mode: 'allow' },
    { feature: 'core.members', mode: 'limit', limit: 5 },
    { feature: 'core.projects', mode: 'limit', limit: 10 },
    { feature: 'core.automations', mode: 'limit', limit: 100 },
    { feature: 'core.custom_domains', mode: 'limit', limit: 3 },
    { feature: 'core.data_retention_days', mode: 'limit', limit: 365 },
    { feature: 'core.storage', mode: 'quota', quota: 100 },
    { feature: 'audit.sites', mode: 'limit', limit: 25 },
    { feature: 'audit.crawl_pages', mode: 'quota', quota: 25_000, overage: 'block' },
    { feature: 'audit.schedule', mode: 'allow' },
    { feature: 'confirm.campaigns', mode: 'limit', limit: 10 },
    { feature: 'confirm.impressions', mode: 'quota', quota: 500_000, overage: 'block' },
    { feature: 'link.links', mode: 'limit', limit: 10_000 },
    { feature: 'link.rules', mode: 'allow' },
    { feature: 'monitor.monitors', mode: 'limit', limit: 100 },
    { feature: 'monitor.min_interval_seconds', mode: 'limit', limit: 60 },
    { feature: 'track.pageviews', mode: 'quota', quota: 500_000, overage: 'block' },
    { feature: 'track.funnels', mode: 'allow' },
    // Every AI feature, explicitly denied.
    { feature: 'audit.ai_summary', mode: 'deny' },
    { feature: 'audit.ai_fix_brief', mode: 'deny' },
    { feature: 'audit.ai_alt_text', mode: 'deny' },
    { feature: 'confirm.ai_copy', mode: 'deny' },
    { feature: 'confirm.ai_translate', mode: 'deny' },
    { feature: 'link.ai_bio_layout', mode: 'deny' },
    { feature: 'link.ai_qr_art', mode: 'deny' },
    { feature: 'market.ai_copy', mode: 'deny' },
    { feature: 'market.ai_image', mode: 'deny' },
    { feature: 'market.ai_video', mode: 'deny' },
    { feature: 'market.ai_blog', mode: 'deny' },
    { feature: 'market.ai_visibility', mode: 'deny' },
    { feature: 'monitor.ai_rca', mode: 'deny' },
    { feature: 'track.ai_digest', mode: 'deny' },
  ],
};

export const PLANS: PlanSeed[] = [FREE, ...TOOL_PLANS, ...UNIFIED, LIFETIME];

export const CREDIT_PACKS = [
  { key: 'pack_1k', name: '1,000 credits', credits: 1_000, bonusCredits: 0, priceCents: 1000, expiresAfterDays: 365 },
  { key: 'pack_5k', name: '5,000 credits', credits: 5_000, bonusCredits: 250, priceCents: 4500, expiresAfterDays: 365 },
  { key: 'pack_25k', name: '25,000 credits', credits: 25_000, bonusCredits: 2_500, priceCents: 20000, expiresAfterDays: 365 },
];
