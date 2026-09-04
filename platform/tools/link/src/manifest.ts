import { z } from 'zod';
import { defineTool } from '@mamal/tool-kit';

/**
 * Link's public surface.
 *
 * The largest tool by surface area, and the one with the strongest unifying
 * idea: **everything is a link**. A redirect, a bio page, a QR target, a file
 * transfer, a contact card and a calendar event are one row in one table with
 * a `kind`, because they answer the same question — somebody typed or scanned
 * an address we own; what happens now.
 *
 * That is also why `link.shorten` is the one command in the platform with a
 * synchronous variant. Market has to hold the short URL *before* it publishes a
 * post, so that path cannot be fire-and-forget.
 */
export const linkManifest = defineTool({
  key: 'link',
  kind: 'tool',
  version: '0.1.0',
  name: 'Link',
  description: 'Short links, bio pages, QR codes and transfers — one address book for everything you publish.',
  basePath: '/link',
  icon: 'Link2',
  color: 'var(--accent)',

  nav: [
    { key: 'link-links', label: 'Links', href: '/link', group: 'Links' },
    { key: 'link-folders', label: 'Folders', href: '/link/folders', group: 'Links' },
    { key: 'link-utm', label: 'UTM presets', href: '/link/utm', group: 'Links' },
    { key: 'link-bio', label: 'Bio pages', href: '/link/bio', group: 'Pages' },
    { key: 'link-splash', label: 'Splash pages', href: '/link/splash', group: 'Pages' },
    { key: 'link-qr', label: 'QR studio', href: '/link/qr', group: 'Codes' },
    { key: 'link-barcodes', label: 'Barcodes', href: '/link/barcodes', group: 'Codes' },
    {
      key: 'link-transfers', label: 'Transfers', href: '/link/transfers', group: 'Files',
      requires: 'link.transfers',
    },
    {
      key: 'link-domains', label: 'Domains', href: '/link/domains', group: 'Settings',
      requires: 'core.custom_domains',
    },
  ],

  resources: [
    { type: 'link', label: 'Link', searchable: true, href: '/link/links/:id' },
    { type: 'bio_page', label: 'Bio page', searchable: true, href: '/link/bio/:id' },
    { type: 'qr_code', label: 'QR code', searchable: true, href: '/link/qr/:id' },
    { type: 'transfer', label: 'Transfer', searchable: true, href: '/link/transfers/:id' },
  ],

  events: [
    {
      name: 'link.link.created',
      description: 'A link was created, by a person or by another tool.',
      payload: z.object({
        linkId: z.uuid(),
        kind: z.string(),
        alias: z.string(),
        shortUrl: z.string(),
        destinationUrl: z.string().nullable(),
      }),
    },
    {
      /**
       * The *threshold* event, not one message per click.
       *
       * Clicks land in the fact table at whatever rate the internet produces
       * them; putting each one on the bus would be tens of millions of
       * messages a day to deliver a number a rollup already has.
       */
      name: 'link.link.threshold',
      description: 'A link crossed a click threshold in a window.',
      payload: z.object({
        linkId: z.uuid(),
        clicks: z.number().int(),
        window: z.string(),
      }),
    },
    {
      name: 'link.lead.captured',
      description: 'Someone submitted a form on a bio page.',
      payload: z.object({
        pageId: z.uuid(),
        blockId: z.uuid(),
        value: z.string(),
        kind: z.enum(['email', 'phone', 'contact', 'review']),
      }),
    },
    {
      name: 'link.transfer.downloaded',
      description: 'A recipient downloaded a transfer.',
      payload: z.object({
        transferId: z.uuid(),
        fileId: z.uuid().optional(),
        downloads: z.number().int(),
      }),
    },
    {
      name: 'link.link.reported',
      description: 'A visitor reported a link for abuse.',
      payload: z.object({ linkId: z.uuid(), reason: z.string(), reportId: z.uuid() }),
    },
  ],

  subscriptions: [
    {
      event: 'monitor.incident.opened',
      handlerKey: 'link:failover-while-target-down',
      description: 'Send a link to its fallback while its destination is down.',
    },
    {
      event: 'monitor.target.recovered',
      handlerKey: 'link:restore-after-recovery',
      description: 'Put the link back once the destination answers again.',
    },
    {
      event: 'audit.issue.detected',
      handlerKey: 'link:offer-managed-link-for-broken-external',
      description: 'A broken external link is a candidate for a managed one.',
    },
  ],

  commands: [
    {
      /**
       * The synchronous one. Market calls this and waits, because a post
       * cannot be published until its links exist.
       */
      name: 'link.shorten',
      description: 'Create a short link for a URL, applying a UTM preset if one is named.',
      input: z.object({
        url: z.string().url(),
        projectId: z.uuid(),
        alias: z.string().max(255).optional(),
        utmPresetId: z.uuid().optional(),
        utm: z.record(z.string(), z.string()).optional(),
        campaign: z.string().max(160).optional(),
        tags: z.array(z.string().max(48)).max(20).optional(),
        sourceUrn: z.string().optional(),
      }),
    },
    {
      name: 'link.createQr',
      description: 'Mint a QR code, dynamic by default so its destination stays editable.',
      input: z.object({
        projectId: z.uuid(),
        type: z.string().default('dynamic_url'),
        name: z.string().max(160),
        url: z.string().url().optional(),
        payload: z.record(z.string(), z.unknown()).default({}),
        sourceUrn: z.string().optional(),
      }),
    },
    {
      name: 'link.setDestination',
      description: 'Re-point an existing link. The printed code does not change.',
      input: z.object({ linkId: z.uuid(), destinationUrl: z.string().url() }),
    },
  ],

  /**
   * Three of Link's gates are deliberately *not* here.
   *
   * `core.custom_domains` is core-owned because one custom domain serves short
   * links, status pages and transfer downloads; `core.white_label` removes the
   * branding on all of them at once. Both would be wrong as Link features — a
   * customer who buys Monitor and adds a domain has not bought Link. And
   * rotation is gated by `link.rules` rather than a separate A/B flag, because
   * a rotation *is* a rule and two switches for one thing is one too many.
   */
  features: [
    { key: 'link.links', name: 'Links', kind: 'limit', freeTierAllowed: true },
    { key: 'link.qr_codes', name: 'QR codes', kind: 'limit', freeTierAllowed: true },
    { key: 'link.bio_pages', name: 'Bio pages', kind: 'limit', freeTierAllowed: true },
    { key: 'link.transfers', name: 'File transfers', kind: 'limit', freeTierAllowed: true },
    { key: 'link.transfer_size_mb', name: 'Max transfer size (MB)', kind: 'limit', freeTierAllowed: true },
    { key: 'link.rules', name: 'Targeting and rotation rules', kind: 'boolean' },
    { key: 'link.deep_links', name: 'Deep links', kind: 'boolean' },
    { key: 'link.cloaking', name: 'Link cloaking', kind: 'boolean' },
    { key: 'link.bulk', name: 'Bulk create and edit', kind: 'boolean' },
    /**
     * Free-tier QR renders on a canvas in the customer's own browser; a
     * server-side raster is CPU we pay for, so it is the paid half of the
     * feature rather than the whole of it.
     */
    { key: 'link.qr_server_render', name: 'Server-rendered QR export', kind: 'boolean' },
    { key: 'link.ai_slug', name: 'AI slug suggestions', kind: 'metered', isAi: true, defaultCreditCost: 1 },
    { key: 'link.ai_og_copy', name: 'AI preview copy', kind: 'metered', isAi: true, defaultCreditCost: 3 },
    { key: 'link.ai_bio_layout', name: 'AI bio page layout', kind: 'metered', isAi: true, defaultCreditCost: 15 },
    { key: 'link.ai_alt_text', name: 'AI alt text', kind: 'metered', isAi: true, defaultCreditCost: 1 },
    { key: 'link.ai_qr_art', name: 'AI artistic QR', kind: 'metered', isAi: true, defaultCreditCost: 20 },
  ],

  aiFeatures: [
    { key: 'link.ai_slug', name: 'Slug suggestions', modality: 'text' },
    { key: 'link.ai_og_copy', name: 'Preview copy', modality: 'text' },
    { key: 'link.ai_bio_layout', name: 'Bio page layout', modality: 'text' },
    { key: 'link.ai_alt_text', name: 'Alt text', modality: 'text' },
    { key: 'link.ai_qr_art', name: 'Artistic QR', modality: 'image' },
  ],

  queues: [
    { name: 'link.render', concurrency: 8 },
    { name: 'link.transfer.scan', concurrency: 4 },
    { name: 'link.rollup', concurrency: 4 },
  ],

  crons: [
    { key: 'link.transfers.expire', schedule: '*/10 * * * *', description: 'Expire and purge transfers' },
    { key: 'link.assignments.prune', schedule: '17 3 * * *', description: 'Drop stale A/B assignments' },
    { key: 'link.domains.verify', schedule: '*/5 * * * *', description: 'Poll pending custom domains' },
  ],
});
