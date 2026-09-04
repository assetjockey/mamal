import { createServer, type Server } from 'node:http';
import type { AddressInfo } from 'node:net';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { evaluateAll, computeScore } from '@mamal/seo-checks';
import { crawl, initialState, toSiteFacts } from '../crawler.ts';
import { discover } from '../discover.ts';
import { fetchPage } from '../fetch.ts';
import { parsePage } from '../parse.ts';
import { isPrivateIp, normalizeUrl, assertPublicUrl, BlockedUrl } from '../url.ts';

describe('SSRF guard', () => {
  it('rejects loopback, link-local and metadata addresses', () => {
    for (const ip of ['127.0.0.1', '10.0.0.1', '192.168.1.1', '169.254.169.254', '172.16.0.1', '0.0.0.0']) {
      expect(isPrivateIp(ip), ip).toBe(true);
    }
  });

  it('rejects IPv4-mapped IPv6, which is the usual bypass', () => {
    expect(isPrivateIp('::ffff:127.0.0.1')).toBe(true);
    expect(isPrivateIp('::1')).toBe(true);
    expect(isPrivateIp('fd00::1')).toBe(true);
  });

  it('allows public addresses', () => {
    expect(isPrivateIp('8.8.8.8')).toBe(false);
    expect(isPrivateIp('2001:4860:4860::8888')).toBe(false);
  });

  it('refuses non-http protocols and localhost by name', async () => {
    await expect(assertPublicUrl('file:///etc/passwd')).rejects.toBeInstanceOf(BlockedUrl);
    await expect(assertPublicUrl('http://localhost:3000')).rejects.toThrow(/loopback/);
    await expect(assertPublicUrl('http://127.0.0.1/')).rejects.toThrow(/private address/);
  });
});

describe('URL normalization', () => {
  it('is the dedupe key: hash and trailing slash do not create new pages', () => {
    expect(normalizeUrl('https://a.com/x/#top')).toBe(normalizeUrl('https://a.com/x'));
  });

  it('strips tracking parameters, which would otherwise be infinite URLs', () => {
    expect(normalizeUrl('https://a.com/p?utm_source=x&id=2')).toBe('https://a.com/p?id=2');
    expect(normalizeUrl('https://a.com/p?fbclid=abc')).toBe('https://a.com/p');
  });

  it('sorts query parameters so order does not matter', () => {
    expect(normalizeUrl('https://a.com/p?b=2&a=1')).toBe(normalizeUrl('https://a.com/p?a=1&b=2'));
  });
});

describe('parser', () => {
  const parse = (body: string, over: Partial<Parameters<typeof parsePage>[0]> = {}) =>
    parsePage(
      {
        url: 'https://example.com/', finalUrl: 'https://example.com/', status: 200,
        headers: {}, body, redirectChain: [], ttfbMs: 10, responseMs: 20,
        httpVersion: '2', blocked: false, ...over,
      },
      { depth: 0, inSitemap: false, origin: 'https://example.com' },
    );

  it('distinguishes a missing alt from a decorative empty one', () => {
    const page = parse('<img src="/a.png"><img src="/b.png" alt=""><img src="/c.png" alt="A chart">');
    expect(page.images.map((i) => i.alt)).toEqual([null, '', 'A chart']);
  });

  it('reads JSON-LD types, including nested graphs', () => {
    const page = parse(`<script type="application/ld+json">
      {"@context":"https://schema.org","@graph":[{"@type":"Organization"},{"@type":"WebSite"}]}
    </script>`);
    expect(page.schemaTypes).toEqual(expect.arrayContaining(['Organization', 'WebSite']));
  });

  it('marks malformed JSON-LD rather than silently ignoring it', () => {
    const page = parse('<script type="application/ld+json">{"@type": "Article",}</script>');
    expect(page.schemaTypes).toContain('__invalid__');
  });

  it('derives isIndexable from robots meta and status', () => {
    expect(parse('<meta name="robots" content="noindex, follow">').isIndexable).toBe(false);
    expect(parse('<p>hi</p>', { headers: { 'x-robots-tag': 'noindex' } }).isIndexable).toBe(false);
    expect(parse('<p>hi</p>').isIndexable).toBe(true);
  });

  it('resolves relative links and classifies internal vs external', () => {
    const page = parse('<a href="/about">About</a><a href="https://other.com">Other</a>');
    expect(page.links[0]).toMatchObject({ href: 'https://example.com/about', isInternal: true });
    expect(page.links[1]).toMatchObject({ isInternal: false });
  });

  it('finds mixed content only on an HTTPS page', () => {
    const page = parse('<img src="http://cdn.com/a.png">');
    expect(page.mixedContent).toContain('http://cdn.com/a.png');
  });

  it('hashes body text so duplicate pages collide', () => {
    const a = parse('<p>Same words entirely</p>');
    const b = parse('<div><p>Same words   entirely</p></div>');
    expect(a.contentHash).toBe(b.contentHash);
  });
});

/**
 * A deliberately broken site, served locally.
 *
 * open-seo keeps a `badseo` fixture for exactly this: an audit engine that is
 * never run against a site with real problems is untested.
 */
describe('crawl against a broken fixture site', () => {
  let server: Server;
  let origin = '';

  const PAGES: Record<string, { status?: number; headers?: Record<string, string>; body: string }> = {
    '/': {
      body: `<!DOCTYPE html><html lang="en"><head>
        <title>Home page of the example shop for testing</title>
        <meta name="description" content="A deliberately broken fixture site used to exercise every rule in the audit engine end to end.">
        <meta name="viewport" content="width=device-width"><meta charset="utf-8">
        <link rel="icon" href="/favicon.ico">
        </head><body><h1>Welcome</h1>
        <p>${'word '.repeat(300)}</p>
        <a href="/good">Good page</a><a href="/broken">Broken</a><a href="/thin">Thin</a>
        </body></html>`,
    },
    '/good': {
      body: `<!DOCTYPE html><html lang="en"><head>
        <title>A properly written page about testing audit engines</title>
        <meta name="description" content="This page is here to prove the engine does not flag things that are actually fine, which matters as much as catching what is broken.">
        <meta name="viewport" content="width=device-width"><meta charset="utf-8">
        <script type="application/ld+json">{"@type":"Article","author":{"@type":"Person","name":"A"}}</script>
        </head><body><h1>A good page</h1><p>${'content '.repeat(400)}</p>
        <a href="/">Home</a></body></html>`,
    },
    // No title, no description, no H1, thin, an image with no alt.
    '/thin': {
      body: `<!DOCTYPE html><html><head><meta charset="utf-8"></head>
        <body><img src="/x.png"><p>Short.</p><a href="/">Home</a></body></html>`,
    },
    '/broken': { status: 404, body: 'Not found' },
    '/robots.txt': { headers: { 'content-type': 'text/plain' }, body: 'User-agent: *\nAllow: /\nUser-agent: GPTBot\nDisallow: /\nSitemap: {ORIGIN}/sitemap.xml' },
    '/sitemap.xml': {
      headers: { 'content-type': 'application/xml' },
      body: '<urlset><url><loc>{ORIGIN}/</loc></url><url><loc>{ORIGIN}/good</loc></url><url><loc>{ORIGIN}/orphan</loc></url></urlset>',
    },
    // In the sitemap, linked from nowhere.
    '/orphan': {
      body: `<!DOCTYPE html><html lang="en"><head><title>An orphan page nobody links to at all</title>
        <meta name="description" content="This page exists only in the sitemap, so the orphan rule has something real to find during the test.">
        <meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
        <body><h1>Orphan</h1><p>${'text '.repeat(300)}</p><a href="/">Home</a></body></html>`,
    },
  };

  beforeAll(async () => {
    server = createServer((req, res) => {
      const path = (req.url ?? '/').split('?')[0]!;
      const page = PAGES[path];
      if (!page) {
        res.writeHead(404, { 'content-type': 'text/html' });
        res.end('<html><body>Missing</body></html>');
        return;
      }
      res.writeHead(page.status ?? 200, { 'content-type': 'text/html', ...page.headers });
      res.end(page.body.replaceAll('{ORIGIN}', origin));
    });
    await new Promise<void>((resolve) => server.listen(0, '127.0.0.1', resolve));
    origin = `http://127.0.0.1:${(server.address() as AddressInfo).port}`;
  });

  afterAll(() => new Promise<void>((resolve) => server.close(() => resolve())));

  const run = () =>
    crawl(origin, initialState(origin), {
      maxPages: 20, maxDepth: 3, respectRobots: true, concurrency: 4,
      // The fixture is on loopback; only a test may opt out of the SSRF guard.
      allowPrivate: true,
    });

  it('discovers robots.txt, the sitemap and blocked AI crawlers', async () => {
    const found = await discover(origin, { allowPrivate: true });
    expect(found.robotsTxt.found).toBe(true);
    expect(found.sitemap.found).toBe(true);
    expect(found.sitemap.urls.length).toBe(3);
    expect(found.aiCrawlers.find((c) => c.agent === 'GPTBot')?.allowed).toBe(false);
    expect(found.aiCrawlers.find((c) => c.agent === 'ClaudeBot')?.allowed).toBe(true);
  });

  it('crawls the site and seeds sitemap-only pages so orphans are findable', async () => {
    const result = await run();
    const urls = result.pages.map((p) => new URL(p.url).pathname);
    expect(urls).toContain('/');
    expect(urls).toContain('/good');
    expect(urls).toContain('/orphan');
    expect(result.done, 'the whole fixture site fits in the budget').toBe(true);
    expect(result.budgetExhausted).toBe(false);
  });

  it('finds the issues that are actually there, and not the ones that are not', async () => {
    const result = await run();
    const site = toSiteFacts(origin, result);
    const { findings, ruleResults } = evaluateAll(site);
    const fired = new Set(findings.map((f) => f.ruleId));

    // present by construction
    expect(fired.has('missing-title')).toBe(true);
    expect(fired.has('missing-meta-description')).toBe(true);
    expect(fired.has('missing-h1')).toBe(true);
    expect(fired.has('thin-content')).toBe(true);
    expect(fired.has('images-missing-alt')).toBe(true);
    expect(fired.has('broken-page')).toBe(true);
    expect(fired.has('ai-crawler-blocked')).toBe(true);
    expect(fired.has('orphan-page')).toBe(true);
    expect(fired.has('not-https')).toBe(true); // fixture is plain http

    // absent by construction — a rule that fires on a healthy page is worse
    // than one that misses, because it destroys trust in the whole report
    expect(fired.has('robots-txt-missing')).toBe(false);
    expect(fired.has('sitemap-missing')).toBe(false);
    expect(fired.has('llms-txt-missing')).toBe(true); // the fixture has none

    const score = computeScore(ruleResults, findings);
    expect(score.score).toBeLessThan(90);
    expect(score.counts.critical).toBeGreaterThan(0);
  });

  it('reports progress as it goes, so a long crawl is watchable', async () => {
    const seen: number[] = [];
    await crawl(origin, initialState(origin), {
      maxPages: 20, maxDepth: 3, respectRobots: true, concurrency: 2, allowPrivate: true,
    }, {
      onPage: (_page, progress) => { seen.push(progress.crawled); },
    });
    expect(seen.length).toBeGreaterThan(3);
    expect(seen).toEqual([...seen].sort((a, b) => a - b));
  });

  it('resumes from persisted state instead of starting again', async () => {
    const state = initialState(origin);
    const first = await crawl(origin, state, {
      maxPages: 2, maxDepth: 3, respectRobots: true, concurrency: 1, allowPrivate: true,
    });
    expect(first.pages).toHaveLength(2);
    // Budget spent, but the site is not covered — two different answers.
    expect(first.budgetExhausted).toBe(true);
    expect(first.done).toBe(false);

    // The same state object, carried across what would be a worker restart.
    const second = await crawl(origin, first.state, {
      maxPages: 20, maxDepth: 3, respectRobots: true, concurrency: 4, allowPrivate: true,
    });
    const revisited = second.pages.filter((p) => first.pages.some((q) => q.url === p.url));
    expect(revisited, 'a resumed crawl must not re-fetch pages it already has').toHaveLength(0);
  });

  it('stops at maxPages rather than crawling the whole site', async () => {
    const result = await crawl(origin, initialState(origin), {
      maxPages: 2, maxDepth: 3, respectRobots: true, allowPrivate: true,
    });
    expect(result.pages.length).toBeLessThanOrEqual(2);
  });

  it('refuses the fixture when the SSRF guard is on, which is the default', async () => {
    const blocked = await fetchPage(origin, {});
    expect(blocked.blocked).toBe(true);
    expect(blocked.error).toMatch(/private address/);
  });
});
