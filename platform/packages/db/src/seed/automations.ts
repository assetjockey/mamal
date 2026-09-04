
/**
 * Structural shape only. Seed data must not depend on the automations engine,
 * or `db` and `automations` form a cycle. `@mamal/automations` validates these
 * against the real DSL schema in its own test suite.
 */
export type TemplateSeed = {
  key: string;
  name: string;
  description: string;
  category: string;
  /** The rule is offered only when every tool it touches is installed. */
  requiredTools: string[];
  definition: {
    trigger: { event: string; filter?: Record<string, unknown> };
    conditions?: Record<string, unknown>[];
    actions: Record<string, unknown>[];
  };
};

/**
 * The shipped cross-tool recipes.
 *
 * These are the argument for the platform: each one is something none of the
 * 22 source products could do, because none of them could see another's data.
 */
export const AUTOMATION_TEMPLATES: TemplateSeed[] = [
  {
    key: 'broken-link-to-monitor',
    name: 'Watch broken links found by Audit',
    description:
      'When an audit finds a broken internal link, start an uptime check on the target so you learn the moment it comes back.',
    category: 'reliability',
    requiredTools: ['audit', 'monitor'],
    definition: {
      trigger: {
        event: 'audit.issue.detected',
        filter: { 'data.ruleId': 'broken-internal-link', 'data.severity': ['critical'] },
      },
      conditions: [
        // Do not pile up a monitor per audit run for the same URL.
        { op: 'resource.has_relation', subject: '{{subject}}', relation: 'monitors', negate: true, quantity: 1 },
        { op: 'entitlement.allows', feature: 'monitor.monitors', quantity: 1, negate: false },
      ],
      actions: [
        {
          type: 'command',
          name: 'monitor.createCheck',
          input: {
            target: '{{data.targetUrl}}',
            kind: 'http',
            intervalSeconds: 300,
            sourceUrn: '{{subject}}',
          },
          critical: true,
        },
        {
          type: 'notify',
          input: { template: 'audit.broken_link', url: '{{data.targetUrl}}' },
        },
      ],
    },
  },
  {
    key: 'score-drop-alert',
    name: 'Alert on an audit score drop',
    description: 'A drop of more than 10 points notifies your channels and opens a fix task.',
    category: 'seo',
    requiredTools: ['audit'],
    definition: {
      trigger: { event: 'audit.score.changed', filter: {} },
      conditions: [
        { op: 'lt', field: 'data.delta', value: -10, negate: false },
        { op: 'throttle.once_per', window: '12h', key: '{{subject}}', negate: false },
      ],
      actions: [{ type: 'notify', input: { template: 'audit.score_dropped', delta: '{{data.delta}}' } }],
    },
  },
  {
    key: 'downtime-status-banner',
    name: 'Show a status banner while a site is down',
    description:
      'An open incident swaps the site’s Confirm widget to an "we are aware" bar and suppresses purchase proofs until it resolves.',
    category: 'reliability',
    requiredTools: ['monitor', 'confirm'],
    definition: {
      trigger: { event: 'monitor.incident.opened', filter: {} },
      actions: [
        { type: 'command', name: 'confirm.setIncidentMode', input: { siteUrn: '{{subject}}', on: true } },
        { type: 'notify', input: { template: 'monitor.incident_opened' } },
      ],
    },
  },
  {
    key: 'downtime-banner-clear',
    name: 'Clear the status banner on recovery',
    description: 'Resolving the incident restores normal social proof.',
    category: 'reliability',
    requiredTools: ['monitor', 'confirm'],
    definition: {
      trigger: { event: 'monitor.incident.resolved', filter: {} },
      actions: [
        { type: 'command', name: 'confirm.setIncidentMode', input: { siteUrn: '{{subject}}', on: false } },
      ],
    },
  },
  {
    key: 'conversion-to-social-proof',
    name: 'Turn real conversions into social proof',
    description:
      'A completed goal in Track becomes a live "someone just bought" notification in Confirm — the honest version of the feature.',
    category: 'growth',
    requiredTools: ['track', 'confirm'],
    definition: {
      trigger: { event: 'track.goal.converted', filter: {} },
      conditions: [{ op: 'entitlement.allows', feature: 'confirm.live_sources', quantity: 1, negate: false }],
      actions: [
        {
          type: 'command',
          name: 'confirm.pushConversion',
          input: { siteUrn: '{{subject}}', goal: '{{data.goalKey}}', value: '{{data.value}}' },
        },
      ],
    },
  },
  {
    key: 'publish-shorten-track',
    name: 'Shorten and track every published URL',
    description:
      'A published post gets a branded short link with the campaign UTM preset, and Track begins attributing clicks to it.',
    category: 'growth',
    requiredTools: ['market', 'link'],
    definition: {
      trigger: { event: 'market.post.published', filter: {} },
      actions: [
        {
          type: 'command',
          name: 'link.shorten',
          input: { url: '{{data.url}}', campaign: '{{data.campaign}}', sourceUrn: '{{subject}}' },
          critical: true,
        },
        { type: 'resource.relate', input: { from: '{{subject}}', to: '{{data.url}}', relation: 'shortens' } },
      ],
    },
  },
  {
    key: 'lead-to-contact',
    name: 'Route every captured lead to one inbox',
    description:
      'A bio-page form submission becomes a contact, joins a newsletter segment, and increments the Confirm signup counter.',
    category: 'growth',
    requiredTools: ['link'],
    definition: {
      trigger: { event: 'link.lead.captured', filter: {} },
      actions: [
        { type: 'command', name: 'link.attachContact', input: { contactId: '{{data.contactId}}' } },
        { type: 'tag', input: { name: 'biolink-lead', urn: '{{subject}}' } },
      ],
    },
  },
  {
    key: 'ssl-expiry-audit',
    name: 'Re-audit after an SSL renewal',
    description:
      'An expiring certificate raises an incident and schedules a verification audit for the renewal date.',
    category: 'reliability',
    requiredTools: ['monitor', 'audit'],
    definition: {
      trigger: { event: 'monitor.ssl.expiring', filter: {} },
      conditions: [{ op: 'lte', field: 'data.daysRemaining', value: 14, negate: false }],
      actions: [
        { type: 'notify', input: { template: 'monitor.ssl_expiring' } },
        { type: 'command', name: 'audit.scheduleRun', input: { siteUrn: '{{subject}}', reason: 'ssl-renewal' } },
      ],
    },
  },
  {
    key: 'traffic-drop-page-audit',
    name: 'Audit a page that lost traffic',
    description:
      'A week-over-week drop of more than 30% on one page triggers a single-page audit instead of a guess.',
    category: 'seo',
    requiredTools: ['track', 'audit'],
    definition: {
      trigger: { event: 'track.anomaly.detected', filter: { 'data.kind': 'traffic_drop' } },
      conditions: [
        { op: 'gte', field: 'data.dropPercent', value: 30, negate: false },
        { op: 'entitlement.allows', feature: 'audit.crawl_pages', quantity: 1, negate: false },
      ],
      actions: [
        { type: 'command', name: 'audit.runPage', input: { url: '{{data.path}}', siteUrn: '{{subject}}' } },
      ],
    },
  },
  {
    key: 'scan-spike-protect',
    name: 'Protect a landing page from a QR scan spike',
    description:
      'A tenfold scan rate starts a fast uptime check on the destination and turns on a live scan counter.',
    category: 'growth',
    // Three tools in one rule — Link raises it, Monitor protects the target,
    // Confirm shows the counter. Offered only when all three are installed.
    requiredTools: ['link', 'monitor', 'confirm'],
    definition: {
      trigger: { event: 'link.scan.spike_detected', filter: {} },
      conditions: [{ op: 'entitlement.allows', feature: 'monitor.monitors', quantity: 1, negate: false }],
      actions: [
        {
          type: 'command',
          name: 'monitor.createCheck',
          input: { target: '{{data.destinationUrl}}', kind: 'http', intervalSeconds: 60, sourceUrn: '{{subject}}' },
        },
        { type: 'command', name: 'confirm.enableCounter', input: { siteUrn: '{{subject}}', metric: 'qr_scans' } },
      ],
    },
  },
];
