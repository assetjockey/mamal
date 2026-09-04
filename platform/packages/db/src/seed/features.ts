import type { CostUnit, FeatureKind } from '../schema/billing.ts';

export type FeatureSeed = {
  key: string;
  tool: string;
  name: string;
  description?: string;
  category?: string;
  kind: FeatureKind;
  isAi?: boolean;
  /**
   * False for anything that touches a metered vendor — DataForSEO, an AI
   * provider, Twilio, a Cloudflare-for-SaaS hostname. The resolver enforces
   * this, so no free-tier action can ever produce a third-party invoice line.
   */
  freeTierAllowed?: boolean;
  defaultCreditCost?: number;
  unit?: CostUnit;
};

/**
 * The feature registry. Entitlements are rows written against these keys.
 *
 * Rule for `defaultCreditCost`: anything with a marginal CASH cost is credits;
 * anything that is only our own CPU or storage is a quota. Keep that line
 * visible — it is the sentence that goes in the admin UI.
 */
export const FEATURES: FeatureSeed[] = [
  // ---------------------------------------------------------------- platform
  { key: 'core.workspaces', tool: 'core', name: 'Workspaces', kind: 'limit', freeTierAllowed: true },
  { key: 'core.members', tool: 'core', name: 'Team members', kind: 'limit', freeTierAllowed: true },
  { key: 'core.projects', tool: 'core', name: 'Projects', kind: 'limit', freeTierAllowed: true },
  { key: 'core.api', tool: 'core', name: 'REST API + MCP access', kind: 'boolean' },
  { key: 'core.automations', tool: 'core', name: 'Automations', kind: 'limit',
    description: 'Free tier gets none: one action fanning out to N jobs is what breaks O(1) cost.' },
  { key: 'core.custom_domains', tool: 'core', name: 'Custom domains', kind: 'limit',
    description: 'Cloudflare for SaaS bills per hostname — never free-tier.' },
  { key: 'core.notification_channels', tool: 'core', name: 'Alert channels', kind: 'limit', freeTierAllowed: true },
  { key: 'core.sms', tool: 'core', name: 'SMS / voice / WhatsApp alerts', kind: 'metered',
    defaultCreditCost: 2, unit: 'message' },
  { key: 'core.storage', tool: 'core', name: 'Storage', kind: 'quota', unit: 'gb_month', freeTierAllowed: true },
  { key: 'core.white_label', tool: 'core', name: 'Remove branding', kind: 'boolean' },
  { key: 'core.export', tool: 'core', name: 'Data export', kind: 'boolean', freeTierAllowed: true },
  { key: 'core.data_retention_days', tool: 'core', name: 'Event retention (days)', kind: 'limit', freeTierAllowed: true },

  // ------------------------------------------------------------------- audit
  { key: 'audit.sites', tool: 'audit', name: 'Audited websites', kind: 'limit', freeTierAllowed: true },
  { key: 'audit.crawl_pages', tool: 'audit', name: 'Pages crawled', kind: 'quota',
    unit: 'crawl_page', defaultCreditCost: 1, freeTierAllowed: true },
  { key: 'audit.schedule', tool: 'audit', name: 'Scheduled audits', kind: 'boolean' },
  { key: 'audit.lighthouse', tool: 'audit', name: 'Lighthouse runs', kind: 'quota',
    unit: 'call', defaultCreditCost: 5,
    description: '30-60s in a 2GB container — structurally excluded from the free tier.' },
  { key: 'audit.js_rendering', tool: 'audit', name: 'JavaScript rendering', kind: 'quota',
    unit: 'crawl_page', defaultCreditCost: 2 },
  { key: 'audit.tools', tool: 'audit', name: 'Free SEO tools', kind: 'quota', unit: 'call', freeTierAllowed: true },
  { key: 'audit.white_label_reports', tool: 'audit', name: 'Branded reports', kind: 'boolean' },
  { key: 'audit.ai_summary', tool: 'audit', name: 'AI audit summary', kind: 'metered',
    isAi: true, defaultCreditCost: 25, unit: 'call' },
  { key: 'audit.ai_fix_brief', tool: 'audit', name: 'AI fix instructions', kind: 'metered',
    isAi: true, defaultCreditCost: 25, unit: 'call' },
  { key: 'audit.ai_alt_text', tool: 'audit', name: 'AI alt text', kind: 'metered',
    isAi: true, defaultCreditCost: 1, unit: 'image' },

  // ----------------------------------------------------------------- confirm
  { key: 'confirm.campaigns', tool: 'confirm', name: 'Campaigns', kind: 'limit', freeTierAllowed: true },
  { key: 'confirm.widgets', tool: 'confirm', name: 'Notifications', kind: 'limit', freeTierAllowed: true },
  { key: 'confirm.impressions', tool: 'confirm', name: 'Impressions', kind: 'quota',
    unit: 'call', defaultCreditCost: 1, freeTierAllowed: true },
  { key: 'confirm.live_sources', tool: 'confirm', name: 'Live conversion sources', kind: 'boolean',
    description: 'A bus subscription is compute; manual/CSV sources are free.' },
  { key: 'confirm.push_subscribers', tool: 'confirm', name: 'Push subscribers', kind: 'limit' },
  { key: 'confirm.ab_testing', tool: 'confirm', name: 'A/B testing', kind: 'boolean' },
  { key: 'confirm.ai_copy', tool: 'confirm', name: 'AI notification copy', kind: 'metered',
    isAi: true, defaultCreditCost: 5, unit: 'call' },
  { key: 'confirm.ai_translate', tool: 'confirm', name: 'AI translation', kind: 'metered',
    isAi: true, defaultCreditCost: 2, unit: 'call' },
  { key: 'confirm.ai_suggest', tool: 'confirm', name: 'AI widget suggestions', kind: 'metered',
    isAi: true, defaultCreditCost: 8, unit: 'call' },
  { key: 'confirm.branding_removal', tool: 'confirm', name: 'Remove Mamal branding',
    kind: 'boolean' },
  { key: 'confirm.custom_css', tool: 'confirm', name: 'Custom CSS and JS', kind: 'boolean' },

  // -------------------------------------------------------------------- link
  { key: 'link.links', tool: 'link', name: 'Short links', kind: 'limit', freeTierAllowed: true },
  { key: 'link.qr_codes', tool: 'link', name: 'QR codes', kind: 'limit', freeTierAllowed: true },
  { key: 'link.bio_pages', tool: 'link', name: 'Bio pages', kind: 'limit', freeTierAllowed: true },
  { key: 'link.rules', tool: 'link', name: 'Targeting + rotation rules', kind: 'boolean' },
  { key: 'link.deep_links', tool: 'link', name: 'Deep links', kind: 'boolean' },
  { key: 'link.cloaking', tool: 'link', name: 'Link cloaking', kind: 'boolean' },
  { key: 'link.transfers', tool: 'link', name: 'File transfers', kind: 'limit', freeTierAllowed: true },
  { key: 'link.transfer_size_mb', tool: 'link', name: 'Max transfer size (MB)', kind: 'limit', freeTierAllowed: true },
  { key: 'link.bulk', tool: 'link', name: 'Bulk create + CSV import', kind: 'boolean' },
  { key: 'link.qr_server_render', tool: 'link', name: 'Server-rendered QR export', kind: 'boolean',
    description: 'Free tier renders QR client-side on canvas; rasterization is paid.' },
  { key: 'link.ai_slug', tool: 'link', name: 'AI slug suggestions', kind: 'metered',
    isAi: true, defaultCreditCost: 1, unit: 'call' },
  { key: 'link.ai_og_copy', tool: 'link', name: 'AI link preview copy', kind: 'metered',
    isAi: true, defaultCreditCost: 3, unit: 'call' },
  { key: 'link.ai_alt_text', tool: 'link', name: 'AI alt text', kind: 'metered',
    isAi: true, defaultCreditCost: 1, unit: 'image' },
  { key: 'link.ai_bio_layout', tool: 'link', name: 'AI bio page layout', kind: 'metered',
    isAi: true, defaultCreditCost: 15, unit: 'call' },
  { key: 'link.ai_qr_art', tool: 'link', name: 'AI artistic QR', kind: 'metered',
    isAi: true, defaultCreditCost: 20, unit: 'image' },

  // ------------------------------------------------------------------ market
  { key: 'market.gsc_connections', tool: 'market', name: 'Search Console connections', kind: 'limit', freeTierAllowed: true },
  { key: 'market.ga4_connections', tool: 'market', name: 'Analytics connections', kind: 'limit', freeTierAllowed: true },
  { key: 'market.tracked_keywords', tool: 'market', name: 'Tracked keywords', kind: 'limit' },
  { key: 'market.trend_watches', tool: 'market', name: 'Trend watches', kind: 'limit', freeTierAllowed: true },
  { key: 'market.social_accounts', tool: 'market', name: 'Social accounts', kind: 'limit', freeTierAllowed: true },
  { key: 'market.scheduled_posts', tool: 'market', name: 'Scheduled posts', kind: 'quota', freeTierAllowed: true },
  { key: 'market.ad_accounts', tool: 'market', name: 'Ad accounts', kind: 'limit' },
  { key: 'market.publish_destinations', tool: 'market', name: 'Publishing destinations', kind: 'limit' },
  { key: 'market.dataforseo', tool: 'market', name: 'Keyword + backlink research', kind: 'metered',
    unit: 'call', defaultCreditCost: 1,
    description: 'Passthrough at a 1.3x cost basis. A per-call invoice, so never free.' },
  { key: 'market.ai_copy', tool: 'market', name: 'AI ad copy', kind: 'metered',
    isAi: true, defaultCreditCost: 2, unit: '1k_words' },
  { key: 'market.ai_image', tool: 'market', name: 'AI image', kind: 'metered',
    isAi: true, defaultCreditCost: 8, unit: 'image' },
  { key: 'market.ai_video', tool: 'market', name: 'AI video', kind: 'metered',
    isAi: true, defaultCreditCost: 20, unit: 'video_second' },
  { key: 'market.ai_blog', tool: 'market', name: 'AI blog writer', kind: 'metered',
    isAi: true, defaultCreditCost: 30, unit: 'call' },
  { key: 'market.content_docs', tool: 'market', name: 'Documents', kind: 'limit', freeTierAllowed: true },
  { key: 'market.social_monitoring', tool: 'market', name: 'Mention monitoring', kind: 'boolean' },
  { key: 'market.post_approval', tool: 'market', name: 'Post review workflow', kind: 'boolean' },
  { key: 'market.local', tool: 'market', name: 'Local profiles', kind: 'limit' },
  { key: 'market.rank_check', tool: 'market', name: 'Rank check', kind: 'metered',
    defaultCreditCost: 1, unit: 'call' },
  { key: 'market.backlinks', tool: 'market', name: 'Backlink profile', kind: 'metered',
    defaultCreditCost: 10, unit: 'call' },
  { key: 'market.ai_brief', tool: 'market', name: 'AI content brief', kind: 'metered',
    isAi: true, defaultCreditCost: 15, unit: 'call' },
  { key: 'market.ai_rewrite', tool: 'market', name: 'AI rewrite and humanize', kind: 'metered',
    isAi: true, defaultCreditCost: 10, unit: '1k_words' },
  { key: 'market.ai_meta', tool: 'market', name: 'AI meta description', kind: 'metered',
    isAi: true, defaultCreditCost: 2, unit: 'call' },
  { key: 'market.ai_schema', tool: 'market', name: 'AI schema generator', kind: 'metered',
    isAi: true, defaultCreditCost: 5, unit: 'call' },
  { key: 'market.ai_clustering', tool: 'market', name: 'AI keyword clustering', kind: 'metered',
    isAi: true, defaultCreditCost: 8, unit: 'call' },
  { key: 'market.ai_caption', tool: 'market', name: 'AI social caption', kind: 'metered',
    isAi: true, defaultCreditCost: 3, unit: 'call' },
  { key: 'market.ai_reply', tool: 'market', name: 'AI comment and review reply', kind: 'metered',
    isAi: true, defaultCreditCost: 3, unit: 'call' },
  { key: 'market.ai_insight', tool: 'market', name: 'AI ad insight', kind: 'metered',
    isAi: true, defaultCreditCost: 20, unit: 'call' },
  { key: 'market.ai_visibility', tool: 'market', name: 'AI visibility probes', kind: 'metered',
    isAi: true, defaultCreditCost: 40, unit: 'call' },

  // ----------------------------------------------------------------- monitor
  { key: 'monitor.monitors', tool: 'monitor', name: 'Monitors', kind: 'limit', freeTierAllowed: true },
  { key: 'monitor.min_interval_seconds', tool: 'monitor', name: 'Fastest check interval', kind: 'limit', freeTierAllowed: true },
  { key: 'monitor.regions', tool: 'monitor', name: 'Probe regions', kind: 'limit', freeTierAllowed: true },
  { key: 'monitor.status_pages', tool: 'monitor', name: 'Status pages', kind: 'limit', freeTierAllowed: true },
  { key: 'monitor.browser_checks', tool: 'monitor', name: 'Browser synthetics', kind: 'metered',
    unit: 'check', defaultCreditCost: 2 },
  { key: 'monitor.private_agents', tool: 'monitor', name: 'Private / firewall agents', kind: 'limit' },
  { key: 'monitor.fast_checks', tool: 'monitor', name: 'Sub-minute checks', kind: 'metered',
    unit: 'check', defaultCreditCost: 1 },
  { key: 'monitor.ai_rca', tool: 'monitor', name: 'AI root-cause analysis', kind: 'metered',
    isAi: true, defaultCreditCost: 20, unit: 'call' },

  // ------------------------------------------------------------------- track
  { key: 'track.sites', tool: 'track', name: 'Tracked websites', kind: 'limit', freeTierAllowed: true },
  { key: 'track.pageviews', tool: 'track', name: 'Pageviews', kind: 'quota', unit: 'pageview', freeTierAllowed: true },
  { key: 'track.identified_mode', tool: 'track', name: 'Identified visitors', kind: 'boolean' },
  { key: 'track.replays', tool: 'track', name: 'Session replays', kind: 'quota',
    unit: 'call', defaultCreditCost: 5 },
  { key: 'track.heatmaps', tool: 'track', name: 'Heatmaps', kind: 'boolean' },
  { key: 'track.funnels', tool: 'track', name: 'Funnels', kind: 'boolean' },
  { key: 'track.public_dashboards', tool: 'track', name: 'Public dashboards', kind: 'boolean' },
  { key: 'track.competitive', tool: 'track', name: 'Competitive benchmarking', kind: 'metered',
    unit: 'call', defaultCreditCost: 1 },
  { key: 'track.ai_digest', tool: 'track', name: 'AI insight digest', kind: 'metered',
    isAi: true, defaultCreditCost: 15, unit: 'call' },
];

export const TOOLS = ['audit', 'confirm', 'link', 'market', 'monitor', 'track'] as const;
export type ToolKey = (typeof TOOLS)[number];
