import { num, type Finding, type PageFacts, type Rule, type SiteFacts } from '../types.ts';
import { normalize } from './crawlability.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page' });
const site = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'site' });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });

export const linkRules: Rule[] = [
  site({
    id: 'broken-internal-link',
    category: 'links',
    severity: 'critical',
    weight: 10,
    title: 'Broken internal links',
    why: 'A link to a dead page wastes the visit and the crawl. Internal links are entirely within your control, so a broken one is always a mistake.',
    howToFix:
      'Fix the URL, or 301 the target to its new home.\n\n' +
      'This is the finding that pairs with Monitor: once you know a URL is dead, an uptime check tells you the moment it comes back.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const broken = [...facts.brokenTargets.entries()].filter(([, status]) => status >= 400);
      if (broken.length === 0) return null;
      return broken.slice(0, 200).map(([target, status]) => {
        const sources = facts.pages
          .filter((p) => p.links.some((l) => l.isInternal && normalize(l.href) === normalize(target)))
          .map((p) => p.url);
        return f('broken-internal-link', 'critical', sources[0] ?? null, {
          targetUrl: target,
          statusCode: status,
          linkedFrom: sources.slice(0, 10),
        });
      });
    },
  }),

  page({
    id: 'unsafe-cross-origin',
    category: 'links',
    severity: 'warning',
    weight: 5,
    title: 'Unsafe cross-origin links',
    why: '`target="_blank"` without `rel="noopener"` lets the opened page reach back into yours through `window.opener`.',
    howToFix: 'Add `rel="noopener noreferrer"` to every external link that opens in a new tab.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const unsafe = pg.links.filter(
        (l) => !l.isInternal && /_blank/.test(l.rel ?? '') === false && l.rel !== null && !/noopener/.test(l.rel),
      );
      const risky = pg.links.filter((l) => !l.isInternal && !/noopener/i.test(l.rel ?? ''));
      const found = unsafe.length > 0 ? unsafe : risky.filter((l) => l.rel === null);
      return found.length > 0
        ? f('unsafe-cross-origin', 'warning', pg.url, {
            count: found.length,
            sample: found.slice(0, 10).map((l) => l.href),
          })
        : null;
    },
  }),

  page({
    id: 'no-outgoing-links',
    category: 'links',
    severity: 'warning',
    weight: 5,
    title: 'Page links nowhere',
    why: 'A page with no outbound internal links is a dead end: it absorbs authority and passes none on.',
    howToFix: 'Add links to related pages. Even two or three relevant ones fix this.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200 || !pg.isIndexable) return null;
      return pg.links.filter((l) => l.isInternal).length === 0
        ? f('no-outgoing-links', 'warning', pg.url, {})
        : null;
    },
  }),

  page({
    id: 'too-many-links',
    category: 'links',
    severity: 'info',
    weight: 1,
    title: 'Very many links on one page',
    why: 'Hundreds of links dilute how much authority each one carries and usually signal boilerplate rather than content.',
    howToFix: 'Trim navigation and footer links, or paginate long index pages.',
    defaultThresholds: { maxLinks: 150 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxLinks', 150);
      return pg.links.length > max
        ? f('too-many-links', 'info', pg.url, { count: pg.links.length, max })
        : null;
    },
  }),

  page({
    id: 'nofollow-internal',
    category: 'links',
    severity: 'info',
    weight: 1,
    title: 'Internal links marked nofollow',
    why: 'Nofollowing your own pages tells search engines not to pass authority to them. This is almost never intended.',
    howToFix: 'Remove `rel="nofollow"` from internal links. Use `noindex` on the target if you want to keep it out of the index.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const nofollowed = pg.links.filter((l) => l.isInternal && /nofollow/i.test(l.rel ?? ''));
      return nofollowed.length > 0
        ? f('nofollow-internal', 'info', pg.url, {
            count: nofollowed.length,
            sample: nofollowed.slice(0, 10).map((l) => l.href),
          })
        : null;
    },
  }),

  site({
    id: 'redirected-internal-link',
    category: 'links',
    severity: 'info',
    weight: 1,
    title: 'Internal links pointing at redirects',
    why: 'Each redirect costs a round trip. Links inside your own site should point at the final URL.',
    howToFix: 'Update the links to the destination. Keep the redirect for external referrers.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const redirected = facts.pages.filter((p) => p.redirectChain.length > 0).map((p) => p.url);
      if (redirected.length === 0) return null;
      const targets = new Set(redirected.map(normalize));
      const offenders = facts.pages
        .filter((p) => p.links.some((l) => l.isInternal && targets.has(normalize(l.href))))
        .map((p) => p.url);
      return offenders.length > 0
        ? f('redirected-internal-link', 'info', null, { count: offenders.length, sample: offenders.slice(0, 10) })
        : null;
    },
  }),
];
