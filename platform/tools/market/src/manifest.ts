import { z } from 'zod';
import { defineTool } from '@mamal/tool-kit';

/**
 * Market's public surface.
 *
 * Six modules — SEO, AI visibility, content, social, ads, local — and the
 * reason they are one tool rather than six is that they share the same nouns:
 * a keyword informs a brief, a brief becomes a post, a post carries a tracked
 * link, and the ad spend behind it lands on the same campaign. Splitting them
 * would mean reconnecting Search Console once per module.
 *
 * **This is the tool most exposed to third-party cost**, and the manifest says
 * so: every DataForSEO-backed and every generative feature is `metered`, while
 * everything reachable from Search Console, Analytics and PageSpeed is a plain
 * limit. That split is what makes a genuinely useful free tier possible — the
 * opportunity finders are arithmetic over data Google gives away.
 */
export const marketManifest = defineTool({
  key: 'market',
  kind: 'tool',
  version: '0.1.0',
  name: 'Market',
  description:
    'Rank on search, get named by AI models, publish content, run social and make ad spend legible.',
  basePath: '/market',
  icon: 'TrendingUp',
  color: 'var(--accent)',

  nav: [
    { key: 'market-overview', label: 'Overview', href: '/market', group: 'Search' },
    { key: 'market-keywords', label: 'Keywords', href: '/market/keywords', group: 'Search' },
    { key: 'market-rank', label: 'Rank tracking', href: '/market/rank', group: 'Search' },
    { key: 'market-opportunities', label: 'Opportunities', href: '/market/opportunities', group: 'Search' },
    {
      key: 'market-visibility', label: 'AI visibility', href: '/market/visibility', group: 'Search',
      requires: 'market.ai_visibility',
    },
    { key: 'market-content', label: 'Content', href: '/market/content', group: 'Publish' },
    { key: 'market-pipelines', label: 'Pipelines', href: '/market/pipelines', group: 'Publish',
      requires: 'market.publish_destinations' },
    { key: 'market-trends', label: 'Trends', href: '/market/trends', group: 'Publish' },
    { key: 'market-social', label: 'Social', href: '/market/social', group: 'Social' },
    { key: 'market-calendar', label: 'Calendar', href: '/market/calendar', group: 'Social' },
    { key: 'market-inbox', label: 'Mentions', href: '/market/inbox', group: 'Social',
      requires: 'market.social_monitoring' },
    { key: 'market-ads', label: 'Ads', href: '/market/ads', group: 'Ads' },
    { key: 'market-studio', label: 'Studio', href: '/market/studio', group: 'Ads',
      requires: 'market.ai_image' },
    { key: 'market-local', label: 'Local', href: '/market/local', group: 'Local',
      requires: 'market.local' },
    { key: 'market-connections', label: 'Connections', href: '/market/connections', group: 'Settings' },
  ],

  resources: [
    { type: 'keyword_set', label: 'Keyword list', searchable: true, href: '/market/keywords?tag=:id' },
    { type: 'rank_config', label: 'Rank tracker', searchable: true, href: '/market/rank/:id' },
    { type: 'content_doc', label: 'Document', searchable: true, href: '/market/content/:id' },
    { type: 'social_post', label: 'Post', searchable: true, href: '/market/social/:id' },
    { type: 'ad_campaign', label: 'Ad campaign', searchable: true, href: '/market/ads/:id' },
  ],

  events: [
    {
      name: 'market.content.published',
      description: 'A document went live on a destination.',
      payload: z.object({
        docId: z.uuid(),
        title: z.string(),
        url: z.string().url(),
        destinationKind: z.string(),
      }),
    },
    {
      name: 'market.post.published',
      description: 'A social post reached at least one network.',
      payload: z.object({
        postId: z.uuid(),
        accounts: z.array(z.object({ provider: z.string(), remoteUrl: z.string().optional() })),
      }),
    },
    {
      name: 'market.post.failed',
      description: 'Publishing failed on every target.',
      payload: z.object({ postId: z.uuid(), reason: z.string() }),
    },
    {
      name: 'market.rank.changed',
      description: 'A tracked keyword moved enough to be worth saying.',
      payload: z.object({
        configId: z.uuid(),
        keyword: z.string(),
        from: z.number().int().nullable(),
        to: z.number().int().nullable(),
        device: z.string(),
      }),
    },
    {
      name: 'market.visibility.changed',
      description: 'The models started, or stopped, naming the brand.',
      payload: z.object({
        projectId: z.uuid(),
        model: z.string(),
        shareOfVoice: z.number(),
        delta: z.number(),
      }),
    },
    {
      name: 'market.trend.shifted',
      description: 'A watched term moved past its threshold.',
      payload: z.object({
        watchId: z.uuid(),
        keyword: z.string(),
        geo: z.string(),
        deltaPct: z.number(),
      }),
    },
    {
      name: 'market.campaign.launched',
      description: 'An ad campaign went live on a platform.',
      payload: z.object({
        campaignId: z.uuid(),
        platform: z.string(),
        budgetMicros: z.number().int().nullable(),
      }),
    },
  ],

  subscriptions: [
    {
      event: 'audit.issue.detected',
      handlerKey: 'market:issue-becomes-task',
      description: 'Turn a content-shaped audit finding into a draft.',
    },
    {
      event: 'monitor.incident.opened',
      handlerKey: 'market:hold-publishing-while-down',
      description: 'Do not publish links to a site that is down.',
    },
    {
      event: 'monitor.target.recovered',
      handlerKey: 'market:release-publishing',
      description: 'Release the held queue once it answers again.',
    },
    {
      event: 'link.link.created',
      handlerKey: 'market:attach-link-to-post',
      description: 'Attribute a post to the short link created for it.',
    },
  ],

  commands: [
    {
      name: 'market.draftContent',
      description: 'Start a document from a keyword, a trend or an audit finding.',
      input: z.object({
        projectId: z.uuid(),
        title: z.string().max(200),
        targetKeywords: z.array(z.string().max(120)).max(20).default([]),
        sourceUrn: z.string().optional(),
      }),
    },
    {
      name: 'market.schedulePost',
      description: 'Queue a social post to one or more connected accounts.',
      input: z.object({
        projectId: z.uuid(),
        body: z.string().max(5000),
        accountIds: z.array(z.uuid()).min(1),
        scheduledAt: z.string().datetime().optional(),
        linkId: z.uuid().optional(),
      }),
    },
    {
      name: 'market.trackKeyword',
      description: 'Add a keyword to an existing rank tracker.',
      input: z.object({ configId: z.uuid(), keyword: z.string().max(200) }),
    },
  ],

  features: [
    /*
     * Free-tier reachable. Everything here is computed from Search Console,
     * Analytics and PageSpeed — which are free APIs with generous quotas — so
     * a free workspace gets real opportunity analysis rather than a demo.
     */
    /*
     * Search Console and Analytics are counted separately, and neither is
     * "connections" in general: social accounts and ad accounts carry their own
     * limits, so one shared counter would charge a customer twice for the same
     * connection.
     */
    { key: 'market.gsc_connections', name: 'Search Console connections', kind: 'limit', freeTierAllowed: true },
    { key: 'market.ga4_connections', name: 'Analytics connections', kind: 'limit', freeTierAllowed: true },
    { key: 'market.social_accounts', name: 'Social accounts', kind: 'limit', freeTierAllowed: true },
    { key: 'market.scheduled_posts', name: 'Scheduled posts', kind: 'quota', freeTierAllowed: true },
    { key: 'market.trend_watches', name: 'Trend watches', kind: 'limit', freeTierAllowed: true },
    { key: 'market.content_docs', name: 'Documents', kind: 'limit', freeTierAllowed: true },

    /*
     * Tracked keywords are *not* free-tier: every check is a SERP call we pay
     * for. The free tier gets the opportunity finders instead, which run on
     * Search Console data and cost nothing.
     */
    { key: 'market.tracked_keywords', name: 'Tracked keywords', kind: 'limit' },

    /* Paid, but not metered: our own compute and storage, not a vendor's bill. */
    { key: 'market.publish_destinations', name: 'Publishing destinations', kind: 'limit' },
    { key: 'market.social_monitoring', name: 'Mention monitoring', kind: 'boolean' },
    { key: 'market.post_approval', name: 'Post review workflow', kind: 'boolean' },
    { key: 'market.local', name: 'Local profiles', kind: 'limit' },
    { key: 'market.ad_accounts', name: 'Connected ad accounts', kind: 'limit' },

    /*
     * Metered, because each one is somebody else's invoice. §0.5's rule
     * verbatim: anything with a marginal cash cost is credits, anything that is
     * only our own CPU is quota.
     */
    { key: 'market.dataforseo', name: 'Keyword and SERP data', kind: 'metered', defaultCreditCost: 1 },
    { key: 'market.rank_check', name: 'Rank check', kind: 'metered', defaultCreditCost: 1 },
    { key: 'market.backlinks', name: 'Backlink profile', kind: 'metered', defaultCreditCost: 10 },
    { key: 'market.ai_visibility', name: 'Prompt visibility probe', kind: 'metered', isAi: true, defaultCreditCost: 40 },
    { key: 'market.ai_brief', name: 'AI content brief', kind: 'metered', isAi: true, defaultCreditCost: 15 },
    { key: 'market.ai_blog', name: 'AI blog writer', kind: 'metered', isAi: true, defaultCreditCost: 25 },
    { key: 'market.ai_rewrite', name: 'AI rewrite and humanize', kind: 'metered', isAi: true, defaultCreditCost: 10 },
    { key: 'market.ai_meta', name: 'AI meta description', kind: 'metered', isAi: true, defaultCreditCost: 2 },
    { key: 'market.ai_schema', name: 'AI schema generator', kind: 'metered', isAi: true, defaultCreditCost: 5 },
    { key: 'market.ai_clustering', name: 'AI keyword clustering', kind: 'metered', isAi: true, defaultCreditCost: 8 },
    { key: 'market.ai_caption', name: 'AI social caption', kind: 'metered', isAi: true, defaultCreditCost: 3 },
    { key: 'market.ai_reply', name: 'AI comment and review reply', kind: 'metered', isAi: true, defaultCreditCost: 3 },
    { key: 'market.ai_copy', name: 'AI ad copy', kind: 'metered', isAi: true, defaultCreditCost: 5 },
    { key: 'market.ai_image', name: 'AI image', kind: 'metered', isAi: true, defaultCreditCost: 8 },
    { key: 'market.ai_video', name: 'AI video', kind: 'metered', isAi: true, defaultCreditCost: 60 },
    { key: 'market.ai_insight', name: 'AI ad insight', kind: 'metered', isAi: true, defaultCreditCost: 20 },
  ],

  aiFeatures: [
    { key: 'market.ai_visibility', name: 'Prompt visibility', modality: 'text' },
    { key: 'market.ai_brief', name: 'Content brief', modality: 'text' },
    { key: 'market.ai_blog', name: 'Blog writer', modality: 'text' },
    { key: 'market.ai_rewrite', name: 'Rewrite and humanize', modality: 'text' },
    { key: 'market.ai_meta', name: 'Meta description', modality: 'text' },
    { key: 'market.ai_schema', name: 'Schema generator', modality: 'text' },
    { key: 'market.ai_clustering', name: 'Keyword clustering', modality: 'text' },
    { key: 'market.ai_caption', name: 'Social caption', modality: 'text' },
    { key: 'market.ai_reply', name: 'Comment and review reply', modality: 'text' },
    { key: 'market.ai_copy', name: 'Ad copy', modality: 'text' },
    { key: 'market.ai_image', name: 'Ad image', modality: 'image' },
    { key: 'market.ai_video', name: 'Ad video', modality: 'video' },
    { key: 'market.ai_insight', name: 'Ad insight', modality: 'text' },
  ],

  queues: [
    // Split by latency, per §0.8: a two-second text call and a five-minute
    // video render must not share a concurrency budget.
    { name: 'market.sync', concurrency: 8 },
    { name: 'market.rank', concurrency: 8 },
    { name: 'market.publish', concurrency: 16 },
    { name: 'market.generate', concurrency: 8 },
    { name: 'market.poll', concurrency: 16 },
  ],

  crons: [
    { key: 'market.gsc.sync', schedule: '17 */6 * * *', description: 'Pull Search Console performance' },
    { key: 'market.rank.due', schedule: '*/10 * * * *', description: 'Claim due rank checks' },
    { key: 'market.opportunities', schedule: '40 4 * * *', description: 'Recompute opportunities' },
    { key: 'market.publish.due', schedule: '* * * * *', description: 'Publish due social posts' },
    { key: 'market.trends', schedule: '*/30 * * * *', description: 'Poll trend watches' },
    { key: 'market.creatives.poll', schedule: '* * * * *', description: 'Poll in-flight generations' },
    { key: 'market.ads.sync', schedule: '25 */4 * * *', description: 'Pull ad spend and results' },
  ],
});
