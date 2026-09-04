import { num, type Finding, type PageFacts, type Rule, type SiteFacts } from '../types.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page' });
const site = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'site' });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });

export const crawlabilityRules: Rule[] = [
  page({
    id: 'blocked-page',
    category: 'crawlability',
    severity: 'critical',
    weight: 10,
    title: 'Page blocked by a bot challenge',
    why: 'A WAF or bot-protection rule refused our crawler, so this page could not be checked at all. Search engines and AI crawlers may be refused the same way.',
    howToFix:
      'Allowlist the user agent `MamalAudit` in your firewall or bot-protection rules.\n\n' +
      '- **Cloudflare**: WAF → Tools → allow the UA, or create a skip rule.\n' +
      '- **AWS WAF**: add a string-match rule on `User-Agent` and set it to Allow.\n\n' +
      'We report this rather than silently scoring the page as fine — a crawler that hides being blocked tells you nothing.',
    evaluate: (p) => ((p as PageFacts).fetchClass === 'blocked' ? f('blocked-page', 'critical', (p as PageFacts).url, { statusCode: (p as PageFacts).statusCode }) : null),
  }),

  page({
    id: 'server-error',
    category: 'crawlability',
    severity: 'critical',
    weight: 10,
    title: 'Server error',
    why: 'The page returned a 5xx. Neither visitors nor crawlers can read it.',
    howToFix: 'Check your application logs for this URL. A 5xx that persists will get the page dropped from the index.',
    evaluate: (p) => {
      const page = p as PageFacts;
      return page.statusCode >= 500 ? f('server-error', 'critical', page.url, { statusCode: page.statusCode }) : null;
    },
  }),

  page({
    id: 'broken-page',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'Page not found',
    why: 'The page returned a 4xx but is still linked from your site.',
    howToFix: 'Either restore the page, or update the links pointing at it. If it moved, return a 301 to the new URL rather than a 404.',
    evaluate: (p) => {
      const page = p as PageFacts;
      return page.statusCode >= 400 && page.statusCode < 500
        ? f('broken-page', 'warning', page.url, { statusCode: page.statusCode })
        : null;
    },
  }),

  page({
    id: 'noindex-page',
    category: 'crawlability',
    severity: 'info',
    weight: 1,
    title: 'Page is set to noindex',
    why: 'This page asks search engines not to index it. Intentional for thank-you and admin pages; a costly mistake anywhere else.',
    howToFix: 'Remove `noindex` from the robots meta tag or the `X-Robots-Tag` header if this page should rank.',
    evaluate: (p) => {
      const page = p as PageFacts;
      const noindex = /noindex/i.test(`${page.robotsMeta ?? ''} ${page.xRobotsTag ?? ''}`);
      return noindex ? f('noindex-page', 'info', page.url, { robotsMeta: page.robotsMeta, xRobotsTag: page.xRobotsTag }) : null;
    },
  }),

  page({
    id: 'canonical-conflict',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'Canonical conflicts with the header',
    why: 'The page declares one canonical in HTML and a different one in the `Link` header. Search engines pick one and it may not be the one you meant.',
    howToFix: 'Remove whichever canonical is wrong so exactly one remains.',
    evaluate: (p) => {
      const page = p as PageFacts;
      if (!page.canonical || !page.headerCanonical) return null;
      return page.canonical !== page.headerCanonical
        ? f('canonical-conflict', 'warning', page.url, { html: page.canonical, header: page.headerCanonical })
        : null;
    },
  }),

  page({
    id: 'canonicalized-page',
    category: 'crawlability',
    severity: 'info',
    weight: 1,
    title: 'Page canonicalises elsewhere',
    why: 'This page points its canonical at a different URL, so it is asking not to rank in its own right.',
    howToFix: 'Expected on filtered or paginated views. If this page should rank, make the canonical self-referential.',
    evaluate: (p) => {
      const page = p as PageFacts;
      if (!page.canonical) return null;
      return normalize(page.canonical) !== normalize(page.url)
        ? f('canonicalized-page', 'info', page.url, { canonical: page.canonical })
        : null;
    },
  }),

  page({
    id: 'redirect-chain',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'Redirect chain',
    why: 'More than one hop before the final page. Every hop costs crawl budget and a little speed.',
    howToFix: 'Point the first URL straight at the destination so the chain becomes a single 301.',
    defaultThresholds: { maxHops: 1 },
    evaluate: (p, ctx) => {
      const page = p as PageFacts;
      const max = num(ctx.thresholds, 'maxHops', 1);
      return page.redirectChain.length > max
        ? f('redirect-chain', 'warning', page.url, { hops: page.redirectChain.length, chain: page.redirectChain })
        : null;
    },
  }),

  page({
    id: 'redirect-loop',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'Redirect loop',
    why: 'The redirects return to a URL already visited, so the page never resolves.',
    howToFix: 'Find the rule redirecting back on itself — usually a trailing-slash or protocol rule fighting another.',
    evaluate: (p) => {
      const page = p as PageFacts;
      const seen = new Set<string>();
      for (const hop of page.redirectChain) {
        const key = normalize(hop);
        if (seen.has(key)) return f('redirect-loop', 'warning', page.url, { chain: page.redirectChain });
        seen.add(key);
      }
      return null;
    },
  }),

  page({
    id: 'deep-page',
    category: 'crawlability',
    severity: 'info',
    weight: 1,
    title: 'Page is buried deep',
    why: 'It takes many clicks from the homepage to reach this page, which signals low importance and wastes crawl budget.',
    howToFix: 'Link to it from a hub page or your navigation so it sits within three clicks of the homepage.',
    defaultThresholds: { maxDepth: 4 },
    evaluate: (p, ctx) => {
      const page = p as PageFacts;
      const max = num(ctx.thresholds, 'maxDepth', 4);
      return page.depth > max ? f('deep-page', 'info', page.url, { depth: page.depth }) : null;
    },
  }),

  site({
    id: 'robots-txt-missing',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'No robots.txt',
    why: 'Crawlers look for it first. Without one they guess, and you lose the chance to point them at your sitemap.',
    howToFix: 'Add `/robots.txt` with at least:\n\n```\nUser-agent: *\nAllow: /\nSitemap: https://example.com/sitemap.xml\n```',
    evaluate: (s) => ((s as SiteFacts).robotsTxt.found ? null : f('robots-txt-missing', 'warning', null, {})),
  }),

  site({
    id: 'robots-blocks-site',
    category: 'crawlability',
    severity: 'critical',
    weight: 10,
    title: 'robots.txt blocks the whole site',
    why: 'A `Disallow: /` for all agents removes your site from search entirely.',
    howToFix: 'Unless this is a staging site, change `Disallow: /` to `Allow: /`. This single line is the most expensive mistake in SEO.',
    evaluate: (s) => ((s as SiteFacts).robotsTxt.disallowsAll ? f('robots-blocks-site', 'critical', null, {}) : null),
  }),

  site({
    id: 'sitemap-missing',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'No XML sitemap',
    why: 'A sitemap is how you tell search engines which pages exist and when they changed, especially the ones nothing links to yet.',
    howToFix: 'Generate `/sitemap.xml` and reference it from robots.txt. Most CMSs do this with a plugin or a setting.',
    evaluate: (s) => ((s as SiteFacts).sitemap.found ? null : f('sitemap-missing', 'warning', null, {})),
  }),

  site({
    id: 'sitemap-incomplete',
    category: 'crawlability',
    severity: 'info',
    weight: 1,
    title: 'Pages missing from the sitemap',
    why: 'Indexable pages we found by crawling are absent from your sitemap, so discovery depends entirely on internal links.',
    howToFix: 'Regenerate the sitemap, or check whether the generator is excluding these paths.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      if (!facts.sitemap.found) return null;
      const missing = facts.pages
        .filter((p) => p.isIndexable && p.statusCode === 200 && !p.inSitemap)
        .map((p) => p.url);
      return missing.length > 0
        ? f('sitemap-incomplete', 'info', null, { count: missing.length, sample: missing.slice(0, 20) })
        : null;
    },
  }),

  site({
    id: 'orphan-page',
    category: 'crawlability',
    severity: 'warning',
    weight: 5,
    title: 'Orphan pages',
    why: 'These pages are in your sitemap but nothing links to them, so visitors cannot reach them by browsing and they inherit no internal authority.',
    howToFix: 'Link each one from a relevant hub page. If it should not exist, remove it from the sitemap.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const orphans = facts.pages
        .filter((p) => p.depth > 0 && !facts.linkedUrls.has(normalize(p.url)))
        .map((p) => p.url);
      return orphans.length > 0
        ? f('orphan-page', 'warning', null, { count: orphans.length, sample: orphans.slice(0, 20) })
        : null;
    },
  }),
];

export function normalize(url: string): string {
  try {
    const u = new URL(url);
    u.hash = '';
    u.hostname = u.hostname.toLowerCase().replace(/^www\./, '');
    if (u.pathname !== '/' && u.pathname.endsWith('/')) u.pathname = u.pathname.slice(0, -1);
    return u.toString();
  } catch {
    return url;
  }
}
