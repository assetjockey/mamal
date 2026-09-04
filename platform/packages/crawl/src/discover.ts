import robotsParser from 'robots-parser';
import { fetchPage, DEFAULT_USER_AGENT, type FetchOptions } from './fetch.ts';
import { normalizeUrl } from './url.ts';

/** The AI crawlers the ai-crawler-blocked rule reports on. */
export const AI_CRAWLERS = ['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended', 'CCBot'];

export type Discovery = {
  robotsTxt: { found: boolean; content: string | null; disallowsAll: boolean };
  sitemap: { found: boolean; urls: string[] };
  llmsTxt: { found: boolean };
  aiCrawlers: { agent: string; allowed: boolean }[];
  isAllowed: (url: string) => boolean;
};

/**
 * Everything discoverable before the crawl starts: robots.txt, the sitemap it
 * points at, /llms.txt, and which AI crawlers are permitted.
 */
export async function discover(origin: string, opts: FetchOptions = {}): Promise<Discovery> {
  const robotsUrl = new URL('/robots.txt', origin).toString();
  const robotsResponse = await fetchPage(robotsUrl, opts);
  const found = robotsResponse.status === 200 && robotsResponse.body.length > 0;
  const content = found ? robotsResponse.body : null;

  const robots = content ? robotsParser(robotsUrl, content) : null;
  const ua = opts.userAgent ?? DEFAULT_USER_AGENT;

  const disallowsAll = Boolean(
    content && /user-agent:\s*\*/i.test(content) && /^\s*disallow:\s*\/\s*$/im.test(content),
  );

  const sitemapUrls = new Set<string>();
  for (const candidate of [
    ...(robots?.getSitemaps() ?? []),
    new URL('/sitemap.xml', origin).toString(),
  ]) {
    for (const url of await readSitemap(candidate, opts, 0)) sitemapUrls.add(url);
  }

  const llms = await fetchPage(new URL('/llms.txt', origin).toString(), opts);

  return {
    robotsTxt: { found, content, disallowsAll },
    sitemap: { found: sitemapUrls.size > 0, urls: [...sitemapUrls] },
    llmsTxt: { found: llms.status === 200 && llms.body.length > 0 },
    aiCrawlers: AI_CRAWLERS.map((agent) => ({
      agent,
      allowed: robots ? robots.isAllowed(origin, agent) !== false : true,
    })),
    isAllowed: (url: string) => (robots ? robots.isAllowed(url, ua) !== false : true),
  };
}

/** Sitemap indexes nest; one level is enough and stops a hostile file looping. */
async function readSitemap(url: string, opts: FetchOptions, depth: number): Promise<string[]> {
  if (depth > 1) return [];
  const response = await fetchPage(url, opts);
  if (response.status !== 200 || !response.body.includes('<')) return [];

  const locs = [...response.body.matchAll(/<loc>\s*([^<]+?)\s*<\/loc>/gi)].map((m) => m[1]!);
  const isIndex = /<sitemapindex/i.test(response.body);

  if (!isIndex) {
    return locs.map((l) => normalizeUrl(l)).filter((u): u is string => u !== null);
  }

  const nested: string[] = [];
  for (const child of locs.slice(0, 50)) {
    nested.push(...(await readSitemap(child, opts, depth + 1)));
  }
  return nested;
}
