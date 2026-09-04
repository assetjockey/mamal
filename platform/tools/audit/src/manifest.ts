import { z } from 'zod';
import { defineTool } from '@mamal/tool-kit';

/**
 * Audit's public surface.
 *
 * Everything another tool can see about Audit is here: the events it
 * publishes, the commands it accepts, the entitlements it contributes, and its
 * navigation. Nothing else in this package is importable from outside.
 */
export const auditManifest = defineTool({
  key: 'audit',
  kind: 'tool',
  version: '0.1.0',
  name: 'Audit',
  description: 'Find website issues impacting your search and AI visibility.',
  basePath: '/audit',
  icon: 'ShieldCheck',
  color: 'var(--accent)',

  nav: [
    { key: 'audit-sites', label: 'Websites', href: '/audit', group: 'Overview' },
    { key: 'audit-runs', label: 'Audits', href: '/audit/runs', group: 'Overview' },
    { key: 'audit-issues', label: 'Issues', href: '/audit/issues', group: 'Overview' },
    { key: 'audit-tools', label: 'Free tools', href: '/audit/tools', group: 'Toolbox' },
    { key: 'audit-rules', label: 'Rules', href: '/audit/rules', group: 'Configure' },
    {
      key: 'audit-reports', label: 'Reports', href: '/audit/reports', group: 'Configure',
      requires: 'audit.white_label_reports',
    },
  ],

  resources: [
    { type: 'audit_site', label: 'Website', searchable: true, href: '/audit/sites/:id' },
    { type: 'run', label: 'Audit run', searchable: false, href: '/audit/runs?run=:id' },
  ],

  events: [
    {
      name: 'audit.run.completed',
      description: 'A crawl finished and was scored.',
      payload: z.object({
        auditId: z.uuid(),
        siteId: z.uuid(),
        score: z.number().int(),
        previousScore: z.number().int().nullable(),
        pagesCrawled: z.number().int(),
        criticalCount: z.number().int(),
      }),
    },
    {
      name: 'audit.run.failed',
      payload: z.object({ auditId: z.uuid(), errorCode: z.string(), detail: z.string().nullable() }),
    },
    {
      name: 'audit.issue.detected',
      description: 'A rule fired. The event automations key off.',
      payload: z.object({
        auditId: z.uuid(),
        ruleId: z.string(),
        severity: z.enum(['critical', 'warning', 'info']),
        pageUrl: z.string().nullable(),
        targetUrl: z.string().optional(),
        evidence: z.record(z.string(), z.unknown()),
      }),
    },
    {
      name: 'audit.issue.resolved',
      payload: z.object({ issueId: z.uuid(), ruleId: z.string(), pageUrl: z.string().nullable() }),
    },
    {
      name: 'audit.score.changed',
      payload: z.object({
        siteId: z.uuid(),
        score: z.number().int(),
        previous: z.number().int(),
        delta: z.number().int(),
      }),
    },
  ],

  commands: [
    {
      name: 'audit.runSite',
      description: 'Queue a full audit for a site.',
      input: z.object({ siteUrn: z.string(), trigger: z.string().default('api') }),
    },
    {
      name: 'audit.runPage',
      description: 'Re-check a single URL — the cheap loop after a fix.',
      input: z.object({ siteUrn: z.string(), url: z.string() }),
      sync: false,
    },
    {
      name: 'audit.scheduleRun',
      input: z.object({ siteUrn: z.string(), reason: z.string().optional() }),
    },
    {
      name: 'audit.resolveIssue',
      description: 'Mark an issue fixed — used when Monitor sees a broken URL recover.',
      input: z.object({ siteUrn: z.string(), ruleId: z.string(), targetUrl: z.string() }),
    },
  ],

  subscriptions: [
    {
      event: 'monitor.target.recovered',
      handlerKey: 'audit:monitor-up-resolves-broken-link',
      description: 'A URL Audit reported broken has recovered.',
    },
  ],

  features: [
    { key: 'audit.sites', name: 'Audited websites', kind: 'limit', freeTierAllowed: true },
    { key: 'audit.crawl_pages', name: 'Pages crawled', kind: 'quota', freeTierAllowed: true, defaultCreditCost: 1 },
    { key: 'audit.schedule', name: 'Scheduled audits', kind: 'boolean' },
    { key: 'audit.lighthouse', name: 'Lighthouse runs', kind: 'quota', defaultCreditCost: 5 },
    { key: 'audit.js_rendering', name: 'JavaScript rendering', kind: 'quota', defaultCreditCost: 2 },
    { key: 'audit.tools', name: 'Free SEO tools', kind: 'quota', freeTierAllowed: true },
    { key: 'audit.white_label_reports', name: 'Branded reports', kind: 'boolean' },
    { key: 'audit.ai_summary', name: 'AI audit summary', kind: 'metered', isAi: true, defaultCreditCost: 25 },
    { key: 'audit.ai_fix_brief', name: 'AI fix instructions', kind: 'metered', isAi: true, defaultCreditCost: 25 },
    { key: 'audit.ai_alt_text', name: 'AI alt text', kind: 'metered', isAi: true, defaultCreditCost: 1 },
  ],

  aiFeatures: [
    { key: 'audit.ai_summary', name: 'Audit summary', modality: 'text' },
    { key: 'audit.ai_fix_brief', name: 'Fix instructions', modality: 'text' },
    { key: 'audit.ai_alt_text', name: 'Alt text', modality: 'vision' },
  ],

  queues: [
    { name: 'audit.crawl', concurrency: 8, attempts: 3 },
    { name: 'audit.lighthouse', concurrency: 4, attempts: 2 },
  ],

  crons: [
    { key: 'audit.due', schedule: '* * * * *', description: 'Claim sites whose next audit is due' },
  ],
});
