import type { PageFacts, SiteFacts } from '../types.ts';

/** A page that passes everything, so a test only has to describe the failure. */
export function goodPage(over: Partial<PageFacts> = {}): PageFacts {
  const url = over.url ?? 'https://example.com/guides/seo';
  return {
    url,
    statusCode: 200,
    fetchClass: 'ok',
    redirectChain: [],
    isHttps: true,
    depth: 1,
    inSitemap: true,

    title: 'A complete guide to technical SEO audits for teams',
    metaDescription:
      'Everything a small team needs to run a technical SEO audit, from crawling to fixing the issues that actually move rankings.',
    // Self-referential by default; overriding `url` must not make the page
    // look like it canonicalises somewhere else.
    canonical: url,
    headerCanonical: null,
    robotsMeta: null,
    xRobotsTag: null,
    isIndexable: true,
    lang: 'en',
    ogTitle: 'A complete guide to technical SEO audits',
    ogDescription: 'Everything a small team needs.',
    ogImage: 'https://example.com/og.png',

    headings: [
      { level: 1, text: 'Technical SEO audits, end to end' },
      { level: 2, text: 'Crawling' },
      { level: 3, text: 'Robots and sitemaps' },
    ],
    wordCount: 1200,
    textRatio: 25,
    contentHash: 'hash-guides-seo',

    images: [{ src: '/a.webp', alt: 'A crawl report', loading: 'lazy', format: 'webp' }],
    links: [
      { href: 'https://example.com/pricing', anchor: 'Pricing', rel: null, isInternal: true },
      { href: 'https://other.com', anchor: 'Source', rel: 'noopener noreferrer', isInternal: false },
    ],
    scripts: [{ src: '/app.js', defer: true, async: false, inline: false }],
    inlineStyleCount: 2,
    deprecatedTags: [],
    forms: [{ action: 'https://example.com/subscribe', method: 'post', isSecure: true }],

    schemaTypes: ['Article', 'Organization'],
    hreflang: [],
    hasViewport: true,
    hasCharset: true,
    hasDoctype: true,
    hasFavicon: true,
    plaintextEmails: [],

    responseMs: 240,
    ttfbMs: 180,
    bytes: 48_000,
    domNodes: 700,
    httpVersion: '2',
    compression: 'br',
    headers: {
      'strict-transport-security': 'max-age=31536000',
      'content-security-policy': "default-src 'self'",
      'referrer-policy': 'strict-origin-when-cross-origin',
      server: 'nginx',
    },
    mixedContent: [],
    requestCount: 24,
    ...over,
  };
}

export function goodSite(pages: PageFacts[] = [goodPage({ depth: 0, url: 'https://example.com/' })]): SiteFacts {
  return {
    origin: 'https://example.com',
    pages,
    robotsTxt: { found: true, content: 'User-agent: *\nAllow: /', disallowsAll: false },
    sitemap: { found: true, urls: pages.map((p) => p.url) },
    llmsTxt: { found: true },
    linkedUrls: new Set(pages.map((p) => p.url)),
    brokenTargets: new Map(),
    aiCrawlers: [
      { agent: 'GPTBot', allowed: true },
      { agent: 'ClaudeBot', allowed: true },
    ],
    sslValidTo: new Date(Date.now() + 200 * 86_400_000),
  };
}
