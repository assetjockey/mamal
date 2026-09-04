import type { PageFacts, SiteFacts } from '@mamal/seo-checks';
import { discover, type Discovery } from './discover.ts';
import { fetchPage, type FetchOptions } from './fetch.ts';
import { parsePage } from './parse.ts';
import { normalizeUrl, sameSite } from './url.ts';

export type CrawlOptions = FetchOptions & {
  maxPages: number;
  maxDepth: number;
  respectRobots: boolean;
  concurrency?: number;
  batchDelayMs?: number;
  includePatterns?: string[];
  excludePatterns?: string[];
};

/**
 * Resumable state.
 *
 * A crawl is a state machine, not a held connection. The frontier and visited
 * set are persisted after every batch, so a worker that is killed at page 900
 * of 1,000 resumes rather than starting again — and progress is reportable
 * while it runs.
 */
export type CrawlState = {
  frontier: { url: string; depth: number }[];
  visited: string[];
  pagesCrawled: number;
  pagesBlocked: number;
};

export type CrawlProgress = {
  crawled: number;
  queued: number;
  blocked: number;
  currentUrl: string;
};

export type CrawlResult = {
  pages: PageFacts[];
  state: CrawlState;
  discovery: Discovery;
  brokenTargets: Map<string, number>;
  linkedUrls: Set<string>;
  /** Nothing left to crawl — the site is fully covered. */
  done: boolean;
  /**
   * This pass hit `maxPages`. Distinct from `done`: a resumed pass with a
   * larger budget would find more. The caller decides whether that budget is
   * the plan's cap (stop, and say so) or just this job's slice (continue).
   */
  budgetExhausted: boolean;
};

export function initialState(startUrl: string): CrawlState {
  const normalized = normalizeUrl(startUrl);
  return {
    frontier: normalized ? [{ url: normalized, depth: 0 }] : [],
    visited: [],
    pagesCrawled: 0,
    pagesBlocked: 0,
  };
}

/**
 * One BFS pass. Returns after `maxPages` or an exhausted frontier, so the
 * caller controls how long a single job runs.
 */
export async function crawl(
  origin: string,
  state: CrawlState,
  opts: CrawlOptions,
  hooks: {
    onPage?: (page: PageFacts, progress: CrawlProgress) => Promise<void> | void;
    shouldStop?: () => boolean;
  } = {},
): Promise<CrawlResult> {
  const discovery = await discover(origin, opts);
  const sitemapSet = new Set(discovery.sitemap.urls);

  // Sitemap URLs are seeded so orphans are discoverable: a page nothing links
  // to is only findable this way, and finding it is the point of the check.
  for (const url of discovery.sitemap.urls.slice(0, opts.maxPages)) {
    if (!state.visited.includes(url) && !state.frontier.some((f) => f.url === url)) {
      state.frontier.push({ url, depth: 1 });
    }
  }

  const visited = new Set(state.visited);
  const pages: PageFacts[] = [];
  const linkedUrls = new Set<string>();
  const brokenTargets = new Map<string, number>();
  const concurrency = opts.concurrency ?? 8;

  while (state.frontier.length > 0 && state.pagesCrawled < opts.maxPages) {
    if (hooks.shouldStop?.()) break;

    const batch: { url: string; depth: number }[] = [];
    while (batch.length < concurrency && state.frontier.length > 0) {
      const next = state.frontier.shift()!;
      if (visited.has(next.url)) continue;
      if (next.depth > opts.maxDepth) continue;
      if (opts.respectRobots && !discovery.isAllowed(next.url)) continue;
      if (!matchesPatterns(next.url, opts)) continue;
      visited.add(next.url);
      batch.push(next);
      if (state.pagesCrawled + batch.length >= opts.maxPages) break;
    }
    if (batch.length === 0) continue;

    const results = await Promise.all(
      batch.map(async (item) => {
        const fetched = await fetchPage(item.url, opts);
        return { item, fetched };
      }),
    );

    for (const { item, fetched } of results) {
      const page = parsePage(fetched, {
        depth: item.depth,
        inSitemap: sitemapSet.has(item.url),
        origin,
      });
      pages.push(page);
      state.pagesCrawled++;
      if (page.fetchClass === 'blocked') state.pagesBlocked++;

      for (const link of page.links) {
        if (!link.isInternal) continue;
        const normalized = normalizeUrl(link.href);
        if (!normalized) continue;
        linkedUrls.add(normalized);
        if (!visited.has(normalized) && sameSite(normalized, origin)) {
          state.frontier.push({ url: normalized, depth: item.depth + 1 });
        }
      }

      if (page.statusCode >= 400) brokenTargets.set(page.url, page.statusCode);
      await hooks.onPage?.(page, {
        crawled: state.pagesCrawled,
        queued: state.frontier.length,
        blocked: state.pagesBlocked,
        currentUrl: page.url,
      });
    }

    // Politeness: a crawler that hammers a small server is a denial of service
    // with good intentions.
    if (opts.batchDelayMs) await sleep(opts.batchDelayMs);
  }

  state.visited = [...visited];

  return {
    pages,
    state,
    discovery,
    brokenTargets,
    linkedUrls,
    done: state.frontier.length === 0,
    budgetExhausted: state.pagesCrawled >= opts.maxPages,
  };
}

/** Assemble what the site-wide rules need. */
export function toSiteFacts(
  origin: string,
  result: CrawlResult,
  extra: { sslValidTo?: Date | null } = {},
): SiteFacts {
  return {
    origin,
    pages: result.pages,
    robotsTxt: result.discovery.robotsTxt,
    sitemap: result.discovery.sitemap,
    llmsTxt: result.discovery.llmsTxt,
    linkedUrls: result.linkedUrls,
    brokenTargets: result.brokenTargets,
    aiCrawlers: result.discovery.aiCrawlers,
    sslValidTo: extra.sslValidTo ?? null,
  };
}

function matchesPatterns(url: string, opts: CrawlOptions): boolean {
  if (opts.excludePatterns?.some((p) => new RegExp(p).test(url))) return false;
  if (opts.includePatterns?.length) {
    return opts.includePatterns.some((p) => new RegExp(p).test(url));
  }
  return true;
}

const sleep = (ms: number) => new Promise((r) => setTimeout(r, ms));
