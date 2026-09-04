import { z } from 'zod';
import { defineTool } from '@mamal/tool-kit';

/**
 * Confirm's public surface.
 *
 * Two halves under one tool: on-site widgets and off-site web push. They share
 * the `contacts` spine and one targeting rule model, and they answer the same
 * question — who is here, and what should they be shown.
 */
export const confirmManifest = defineTool({
  key: 'confirm',
  kind: 'tool',
  version: '0.1.0',
  name: 'Confirm',
  description: 'Social proof and push notifications that earn trust rather than fake it.',
  basePath: '/confirm',
  icon: 'BellRing',
  color: 'var(--accent)',

  nav: [
    { key: 'confirm-campaigns', label: 'Campaigns', href: '/confirm', group: 'On-site' },
    { key: 'confirm-widgets', label: 'Notifications', href: '/confirm/widgets', group: 'On-site' },
    { key: 'confirm-leads', label: 'Leads', href: '/confirm/leads', group: 'On-site' },
    {
      key: 'confirm-sources', label: 'Sources', href: '/confirm/sources', group: 'On-site',
      requires: 'confirm.live_sources',
    },
    {
      key: 'confirm-push', label: 'Subscribers', href: '/confirm/push', group: 'Push',
      requires: 'confirm.push_subscribers',
    },
    {
      key: 'confirm-flows', label: 'Flows', href: '/confirm/flows', group: 'Push',
      requires: 'confirm.push_subscribers',
    },
  ],

  resources: [
    { type: 'campaign', label: 'Campaign', searchable: true, href: '/confirm/campaigns/:id' },
    { type: 'widget', label: 'Notification', searchable: true, href: '/confirm/widgets?w=:id' },
  ],

  events: [
    {
      name: 'confirm.lead.captured',
      description: 'Someone submitted a collector widget.',
      payload: z.object({
        widgetId: z.uuid(),
        campaignId: z.uuid(),
        value: z.string(),
        kind: z.enum(['email', 'phone', 'contact', 'feedback']),
        path: z.string().optional(),
      }),
    },
    {
      name: 'confirm.widget.clicked',
      description: 'A widget’s call to action was followed.',
      payload: z.object({ widgetId: z.uuid(), campaignId: z.uuid(), path: z.string().optional() }),
    },
    {
      /**
       * Impressions are high-volume and land in the fact table. This is the
       * *threshold* event a rollup emits — the bus must never see one message
       * per impression.
       */
      name: 'confirm.campaign.threshold',
      description: 'A campaign crossed an impression threshold.',
      payload: z.object({ campaignId: z.uuid(), impressions: z.number().int(), window: z.string() }),
    },
  ],

  subscriptions: [
    {
      event: 'track.goal.converted',
      handlerKey: 'confirm:goal-becomes-proof',
      description: 'A real conversion becomes a real proof notification.',
    },
    {
      event: 'monitor.incident.opened',
      handlerKey: 'confirm:incident-shows-status-bar',
      description: 'Show a status bar on the affected site while it is down.',
    },
    {
      event: 'audit.run.completed',
      handlerKey: 'confirm:good-score-enables-trust-badge',
      description: 'A healthy site can display a verified badge.',
    },
  ],

  commands: [
    {
      name: 'confirm.recordConversion',
      description: 'Add a conversion to the pool proof widgets draw from.',
      input: z.object({
        siteUrn: z.string(),
        type: z.string().default('conversion'),
        data: z.record(z.string(), z.unknown()).default({}),
        path: z.string().optional(),
        sourceUrn: z.string().optional(),
      }),
    },
    {
      name: 'confirm.showBanner',
      description: 'Publish a temporary announcement bar on a site.',
      input: z.object({
        siteUrn: z.string(),
        title: z.string().max(280),
        endsAt: z.string().datetime().optional(),
        key: z.string().max(64),
      }),
    },
    {
      name: 'confirm.hideBanner',
      description: 'Retire a banner raised by showBanner.',
      input: z.object({ siteUrn: z.string(), key: z.string().max(64) }),
    },
  ],

  features: [
    { key: 'confirm.campaigns', name: 'Campaigns', kind: 'limit', freeTierAllowed: true },
    { key: 'confirm.widgets', name: 'Notifications', kind: 'limit', freeTierAllowed: true },
    { key: 'confirm.impressions', name: 'Impressions', kind: 'quota', freeTierAllowed: true },
    { key: 'confirm.branding_removal', name: 'Remove Mamal branding', kind: 'boolean' },
    { key: 'confirm.live_sources', name: 'Live conversion sources', kind: 'boolean' },
    { key: 'confirm.custom_css', name: 'Custom CSS and JS', kind: 'boolean' },
    { key: 'confirm.ab_testing', name: 'A/B testing', kind: 'boolean' },
    { key: 'confirm.push_subscribers', name: 'Push subscribers', kind: 'limit' },
    { key: 'confirm.ai_copy', name: 'AI notification copy', kind: 'metered', isAi: true, defaultCreditCost: 5 },
    { key: 'confirm.ai_translate', name: 'AI translation', kind: 'metered', isAi: true, defaultCreditCost: 2 },
    { key: 'confirm.ai_suggest', name: 'AI widget suggestions', kind: 'metered', isAi: true, defaultCreditCost: 8 },
  ],

  aiFeatures: [
    { key: 'confirm.ai_copy', name: 'Notification copy', modality: 'text' },
    { key: 'confirm.ai_translate', name: 'Translation', modality: 'text' },
    { key: 'confirm.ai_suggest', name: 'Widget suggestions', modality: 'text' },
  ],

  queues: [
    { name: 'confirm.push.send', concurrency: 32 },
    // Rollups, not per-impression work: impressions land in the fact table and
    // are aggregated on a schedule.
    { name: 'confirm.rollup', concurrency: 4 },
  ],

  crons: [
    { key: 'confirm.push.due', schedule: '* * * * *', description: 'Claim due push campaigns' },
    { key: 'confirm.rss', schedule: '*/15 * * * *', description: 'Poll RSS automations' },
  ],
});
