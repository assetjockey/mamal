/**
 * Navigation is DATA, not JSX — so entitlement changes remove items
 * server-side and there is never a flash of forbidden UI.
 *
 * **This list duplicates each tool's `ToolManifest.nav`, and that is a known
 * seam.** `packages/ui` cannot import `tools/*` — the eslint boundary forbids
 * it and the per-tool build matrix compiles with tools absent — so the shell
 * carries its own copy, and the two drifted the first time Market's screens
 * changed: the sidebar advertised groups that no longer existed.
 *
 * `scripts/check-nav.mjs` now fails CI when they disagree, which turns silent
 * drift into a build error. The real fix is for `apps/app` to build this from
 * the installed manifests and pass it in — that is the composition root's job
 * and it is where §0.2b points — but it changes the shell every screen renders
 * inside, so it is deliberately a separate change.
 */
export type NavItem = {
  key: string;
  label: string;
  href: string;
  requires?: string;
  group?: string;
  badge?: number;
};

export type ToolNav = {
  key: string;
  label: string;
  href: string;
  icon: string;
  /** A CSS colour *reference*, not a literal — the token layer owns the value,
   *  so a per-tool accent stays theme-aware instead of freezing a light-mode hex. */
  color: string;
  /** The letter that follows `g` to jump here. Data, not a switch statement in
   *  the key handler, so a new tool brings its own shortcut. `market` and
   *  `monitor` both want `m`; market holds it and monitor takes `o`. */
  jumpKey: string;
  description: string;
  items: NavItem[];
};

/** The six tools, in the order the rail shows them. */
export const TOOL_NAV: ToolNav[] = [
  {
    key: 'audit',
    label: 'Audit',
    href: '/audit',
    icon: 'ShieldCheck',
    color: 'var(--accent)',
    jumpKey: 'a',
    description: 'Find issues hurting your search and AI visibility',
    items: [
      { key: 'audit-sites', label: 'Websites', href: '/audit', group: 'Overview' },
      { key: 'audit-runs', label: 'Audits', href: '/audit/runs', group: 'Overview' },
      { key: 'audit-issues', label: 'Issues', href: '/audit/issues', group: 'Overview' },
      { key: 'audit-tools', label: 'Free tools', href: '/audit/tools', group: 'Toolbox' },
      { key: 'audit-rules', label: 'Rules', href: '/audit/rules', group: 'Configure' },
      { key: 'audit-reports', label: 'Reports', href: '/audit/reports', group: 'Configure', requires: 'audit.white_label_reports' },
    ],
  },
  {
    key: 'confirm',
    label: 'Confirm',
    href: '/confirm',
    icon: 'BellRing',
    color: 'var(--accent)',
    jumpKey: 'c',
    description: 'Social proof widgets and web push',
    items: [
      { key: 'confirm-campaigns', label: 'Campaigns', href: '/confirm', group: 'On-site' },
      { key: 'confirm-widgets', label: 'Notifications', href: '/confirm/widgets', group: 'On-site' },
      { key: 'confirm-sources', label: 'Sources', href: '/confirm/sources', group: 'On-site', requires: 'confirm.live_sources' },
      { key: 'confirm-push', label: 'Subscribers', href: '/confirm/push', group: 'Push', requires: 'confirm.push_subscribers' },
      { key: 'confirm-flows', label: 'Flows', href: '/confirm/flows', group: 'Push', requires: 'confirm.push_subscribers' },
      { key: 'confirm-leads', label: 'Leads', href: '/confirm/leads', group: 'On-site' },
    ],
  },
  {
    key: 'link',
    label: 'Link',
    href: '/link',
    icon: 'Link2',
    color: 'var(--accent)',
    jumpKey: 'l',
    description: 'Short links, QR codes, bio pages, transfers',
    items: [
      { key: 'link-links', label: 'Links', href: '/link', group: 'Links' },
      { key: 'link-folders', label: 'Folders', href: '/link/folders', group: 'Links' },
      { key: 'link-utm', label: 'UTM presets', href: '/link/utm', group: 'Links' },
      { key: 'link-bio', label: 'Bio pages', href: '/link/bio', group: 'Pages' },
      { key: 'link-splash', label: 'Splash pages', href: '/link/splash', group: 'Pages' },
      { key: 'link-qr', label: 'QR studio', href: '/link/qr', group: 'Codes' },
      { key: 'link-barcodes', label: 'Barcodes', href: '/link/barcodes', group: 'Codes' },
      { key: 'link-transfers', label: 'Transfers', href: '/link/transfers', group: 'Files', requires: 'link.transfers' },
      { key: 'link-domains', label: 'Domains', href: '/link/domains', group: 'Settings', requires: 'core.custom_domains' },
    ],
  },
  {
    key: 'market',
    label: 'Market',
    href: '/market',
    icon: 'TrendingUp',
    color: 'var(--accent)',
    jumpKey: 'm',
    description: 'SEO, AI visibility, content, social, ads',
    items: [
      { key: 'market-overview', label: 'Overview', href: '/market', group: 'Search' },
      { key: 'market-keywords', label: 'Keywords', href: '/market/keywords', group: 'Search' },
      { key: 'market-rank', label: 'Rank tracking', href: '/market/rank', group: 'Search' },
      { key: 'market-opportunities', label: 'Opportunities', href: '/market/opportunities', group: 'Search' },
      { key: 'market-visibility', label: 'AI visibility', href: '/market/visibility', group: 'Search', requires: 'market.ai_visibility' },
      { key: 'market-content', label: 'Content', href: '/market/content', group: 'Publish' },
      { key: 'market-pipelines', label: 'Pipelines', href: '/market/pipelines', group: 'Publish', requires: 'market.publish_destinations' },
      { key: 'market-trends', label: 'Trends', href: '/market/trends', group: 'Publish' },
      { key: 'market-social', label: 'Social', href: '/market/social', group: 'Social' },
      { key: 'market-calendar', label: 'Calendar', href: '/market/calendar', group: 'Social' },
      { key: 'market-inbox', label: 'Mentions', href: '/market/inbox', group: 'Social', requires: 'market.social_monitoring' },
      { key: 'market-ads', label: 'Ads', href: '/market/ads', group: 'Ads' },
      { key: 'market-studio', label: 'Studio', href: '/market/studio', group: 'Ads', requires: 'market.ai_image' },
      { key: 'market-local', label: 'Local', href: '/market/local', group: 'Local', requires: 'market.local' },
      { key: 'market-connections', label: 'Connections', href: '/market/connections', group: 'Settings' },
    ],
  },
  {
    key: 'monitor',
    label: 'Monitor',
    href: '/monitor',
    icon: 'Activity',
    color: 'var(--accent)',
    jumpKey: 'o',
    description: 'Uptime, incidents, status pages',
    items: [
      { key: 'monitor-monitors', label: 'Monitors', href: '/monitor', group: 'Watch' },
      { key: 'monitor-incidents', label: 'Incidents', href: '/monitor/incidents', group: 'Watch' },
      { key: 'monitor-maintenance', label: 'Maintenance', href: '/monitor/maintenance', group: 'Watch' },
      { key: 'monitor-status', label: 'Status pages', href: '/monitor/status', group: 'Publish', requires: 'monitor.status_pages' },
      { key: 'monitor-agents', label: 'Private agents', href: '/monitor/agents', group: 'Publish', requires: 'monitor.private_agents' },
    ],
  },
  {
    key: 'track',
    label: 'Track',
    href: '/track',
    icon: 'BarChart3',
    color: 'var(--accent)',
    jumpKey: 't',
    description: 'Analytics, replays, heatmaps, funnels',
    items: [
      { key: 'track-overview', label: 'Overview', href: '/track', group: 'Reports' },
      { key: 'track-realtime', label: 'Realtime', href: '/track/realtime', group: 'Reports' },
      { key: 'track-pages', label: 'Pages', href: '/track/pages', group: 'Reports' },
      { key: 'track-goals', label: 'Goals', href: '/track/goals', group: 'Behaviour' },
      { key: 'track-funnels', label: 'Funnels', href: '/track/funnels', group: 'Behaviour', requires: 'track.funnels' },
      { key: 'track-replays', label: 'Replays', href: '/track/replays', group: 'Behaviour', requires: 'track.replays' },
      { key: 'track-heatmaps', label: 'Heatmaps', href: '/track/heatmaps', group: 'Behaviour', requires: 'track.heatmaps' },
    ],
  },
];

/** Always-present entries below the tool rail. */
export const PLATFORM_NAV: NavItem[] = [
  { key: 'automations', label: 'Automations', href: '/automations' },
  { key: 'settings', label: 'Settings', href: '/settings' },
];

export function toolFor(pathname: string): ToolNav | undefined {
  return TOOL_NAV.find((t) => pathname === t.href || pathname.startsWith(`${t.href}/`));
}

/** Group a tool's items for the second tier's all-caps section headers. */
export function groupItems(items: NavItem[]): { group: string; items: NavItem[] }[] {
  const out = new Map<string, NavItem[]>();
  for (const item of items) {
    const key = item.group ?? '';
    out.set(key, [...(out.get(key) ?? []), item]);
  }
  return [...out.entries()].map(([group, items]) => ({ group, items }));
}
