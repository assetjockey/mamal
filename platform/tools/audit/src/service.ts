import { createHash } from 'node:crypto';
import { and, eq, sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { inList, textArray } from '@mamal/db';
import { currentPeriodStart, loadContext, resolve } from '@mamal/entitlements';
import { coreUrn, mint, relate } from '@mamal/resources';
import { crawl, discover, initialState, type CrawlState, type FetchOptions } from '@mamal/crawl';
import {
  computeScore,
  evaluatePages,
  evaluateSite,
  type Finding,
  type PageFacts,
  type RuleOverride,
  type SiteFacts,
} from '@mamal/seo-checks';

export type RunOptions = {
  workspaceId: string;
  projectId: string;
  auditSiteId: string;
  trigger?: string;
  /** Free tier crawls fewer pages and runs on a throttled queue. */
  maxPagesOverride?: number;
};

export type RunOutcome = {
  auditId: string;
  score: number;
  previousScore: number | null;
  pagesCrawled: number;
  pagesBlocked: number;
  counts: { critical: number; warning: number; info: number };
  budgetExhausted: boolean;
  /** Emitted by the caller so publishing stays inside its transaction. */
  events: { name: string; data: Record<string, unknown> }[];
};

/**
 * Run an audit end to end: entitlement check, crawl, evaluate, score, persist.
 *
 * The entitlement check happens before the first fetch, not after — a
 * workspace over its page quota should never cost us a crawl.
 */
/** How many pages one job processes before persisting and re-enqueuing. */
export const SLICE_SIZE = 25;

/**
 * Phase 1: create the audit row and check entitlements.
 *
 * Runs inside the request. The entitlement check happens BEFORE any fetch, so
 * a workspace over quota never costs us a crawl, and the caller learns
 * immediately rather than by polling a job that will fail.
 */
export async function startAudit(
  tx: WorkspaceScopedDb,
  opts: RunOptions,
): Promise<{ auditId: string; maxPages: number; startUrl: string }> {
  const site = await loadSite(tx, opts);
  const config = site.crawl_config as CrawlConfigShape;
  const requested = opts.maxPagesOverride ?? config.maxPages;

  /*
   * Ask for ONE page, not the whole budget.
   *
   * Requesting the full configured budget up front means a workspace is
   * refused the moment it has any usage at all — on the free tier's 25 pages,
   * that is exactly one audit ever. What matters is whether there is any
   * headroom left; the crawl is then capped to what remains.
   */
  const entitlement = await loadContext(tx, opts.workspaceId, 'audit.crawl_pages');
  if (!entitlement) throw new Error('audit.crawl_pages is not a known feature');
  const decision = resolve(entitlement, 1);
  if (!decision.allowed) throw new AuditNotAllowed(decision.reason, decision.message);

  const remaining =
    decision.quota && decision.quota > 0
      ? Math.max(0, decision.quota - (decision.used ?? 0))
      : Number.POSITIVE_INFINITY;

  if (remaining <= 0) {
    throw new AuditNotAllowed(
      'quota_exhausted',
      `You have used all ${decision.quota} pages this month.`,
    );
  }

  const maxPages = Math.max(1, Math.min(requested, remaining));

  const [audit] = await tx.execute<{ id: string }>(sql`
    insert into audits
      (workspace_id, project_id, audit_site_id, trigger, status, phase, start_url, config,
       started_at, crawl_cursor)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.auditSiteId},
            ${opts.trigger ?? 'manual'}, 'queued', 'queued', ${site.root_url},
            ${JSON.stringify({ ...config, maxPages })}::jsonb, now(),
            ${JSON.stringify(initialState(site.root_url))}::jsonb)
    returning id`);

  return { auditId: audit!.id, maxPages, startUrl: site.root_url };
}

export type SliceOutcome =
  | { status: 'continue'; pagesCrawled: number; queued: number }
  | { status: 'complete'; outcome: RunOutcome };

/**
 * Phase 2: crawl one bounded slice, persist, and say whether to continue.
 *
 * This is what keeps a 10,000-page crawl out of a single job: each call fetches
 * at most SLICE_SIZE pages, writes them, and saves the frontier. A worker
 * killed between slices loses nothing and resumes where it stopped — and the
 * page counter moves the whole time, so the UI has something true to show.
 */
export async function advanceAudit(
  tx: WorkspaceScopedDb,
  auditId: string,
  workspaceId: string,
): Promise<SliceOutcome> {
  const [audit] = await tx.execute<{
    id: string; audit_site_id: string; project_id: string; start_url: string;
    config: CrawlConfigShape; crawl_cursor: CrawlState & { pagesTotal?: number };
    pages_crawled: number; status: string;
  }>(sql`
    select id, audit_site_id, project_id, start_url, config, crawl_cursor, pages_crawled, status
      from audits where id = ${auditId} and workspace_id = ${workspaceId}`);

  if (!audit) throw new Error(`audit ${auditId} not found`);
  if (audit.status === 'cancelled') {
    return { status: 'complete', outcome: await finalize(tx, auditId, workspaceId, 'cancelled') };
  }

  const config = audit.config;
  const state: CrawlState = {
    frontier: audit.crawl_cursor.frontier ?? [],
    visited: audit.crawl_cursor.visited ?? [],
    pagesCrawled: audit.crawl_cursor.pagesCrawled ?? 0,
    pagesBlocked: audit.crawl_cursor.pagesBlocked ?? 0,
  };

  const budgetLeft = Math.max(0, config.maxPages - state.pagesCrawled);
  const sliceBudget = Math.min(SLICE_SIZE, budgetLeft);

  await tx.execute(sql`
    update audits set status = 'running', phase = 'crawling' where id = ${auditId}`);

  const result = await crawl(audit.start_url, state, {
    ...fetchOptionsFrom(config),
    maxPages: state.pagesCrawled + sliceBudget,
    maxDepth: config.maxDepth,
    respectRobots: config.respectRobots,
    concurrency: 8,
    batchDelayMs: 100,
    ...(config.includePatterns ? { includePatterns: config.includePatterns } : {}),
    ...(config.excludePatterns ? { excludePatterns: config.excludePatterns } : {}),
  });

  await persistPages(tx, workspaceId, auditId, result.pages);
  await persistLinks(tx, workspaceId, auditId, result.pages);

  /*
   * Page rules run HERE, not at finalize: scripts, forms, inline styles and
   * DOM size are not persisted per page, so a page rule evaluated later from
   * stored columns would silently under-report. Running them per slice also
   * means issues appear while the crawl is still going.
   */
  const overrides = await loadOverrides(tx, workspaceId);
  const sliceFacts: SiteFacts = {
    origin: audit.start_url,
    pages: result.pages,
    robotsTxt: result.discovery.robotsTxt,
    sitemap: result.discovery.sitemap,
    llmsTxt: result.discovery.llmsTxt,
    linkedUrls: result.linkedUrls,
    brokenTargets: result.brokenTargets,
    aiCrawlers: result.discovery.aiCrawlers,
    sslValidTo: null,
  };
  const pageFindings = evaluatePages(result.pages, sliceFacts, overrides);
  await persistIssues(tx, workspaceId, auditId, audit.audit_site_id, pageFindings.findings);

  /*
   * Rule outcomes accumulate across slices, merged in the SAME statement as
   * the crawl state — writing them separately let the state update clobber
   * them, which silently defaulted every page rule to "passed" and inflated
   * the score.
   *
   * A rule that failed on any slice has failed for the run, so a stored false
   * is never overwritten by a later true.
   */
  const priorResults = (audit.crawl_cursor as { ruleResults?: Record<string, boolean> })
    .ruleResults ?? {};
  const mergedResults: Record<string, boolean> = { ...priorResults };
  for (const [ruleId, passed] of pageFindings.ruleResults) {
    mergedResults[ruleId] = (mergedResults[ruleId] ?? true) && passed;
  }

  await tx.execute(sql`
    update audits
       set pages_crawled = ${result.state.pagesCrawled},
           pages_blocked = ${result.state.pagesBlocked},
           pages_total = ${result.state.pagesCrawled + result.state.frontier.length},
           crawl_cursor = ${JSON.stringify({ ...result.state, ruleResults: mergedResults })}::jsonb
     where id = ${auditId}`);

  const exhausted = result.state.pagesCrawled >= config.maxPages;
  if (result.done || exhausted) {
    return { status: 'complete', outcome: await finalize(tx, auditId, workspaceId, 'completed') };
  }

  return {
    status: 'continue',
    pagesCrawled: result.state.pagesCrawled,
    queued: result.state.frontier.length,
  };
}

/**
 * Phase 3: evaluate every stored page, score, and emit events.
 *
 * Site-wide rules need the whole crawl, so this runs once at the end — reading
 * the pages back out of the database rather than holding them in memory across
 * slices.
 */
async function finalize(
  tx: WorkspaceScopedDb,
  auditId: string,
  workspaceId: string,
  status: 'completed' | 'cancelled',
): Promise<RunOutcome> {
  await tx.execute(sql`update audits set phase = 'analyzing' where id = ${auditId}`);

  const [audit] = await tx.execute<{
    audit_site_id: string; start_url: string; site_id: string;
    previous_score: number | null; pages_crawled: number; pages_blocked: number;
    crawl_cursor: Record<string, unknown>;
  }>(sql`
    select a.audit_site_id, a.start_url, s.site_id, s.score as previous_score,
           a.pages_crawled, a.pages_blocked, a.crawl_cursor
      from audits a join audit_sites s on s.id = a.audit_site_id
     where a.id = ${auditId}`);

  const pages = await readPages(tx, auditId);
  const discovery = await rediscover(tx, auditId, audit!.start_url);
  const overrides = await loadOverrides(tx, workspaceId);

  const facts: SiteFacts = {
    origin: audit!.start_url,
    pages,
    ...discovery,
    linkedUrls: await readLinkedUrls(tx, auditId),
    brokenTargets: new Map(pages.filter((p) => p.statusCode >= 400).map((p) => [p.url, p.statusCode])),
    sslValidTo: null,
  };

  const sitePass = evaluateSite(facts, overrides);
  const issueEvents = await persistIssues(
    tx, workspaceId, auditId, audit!.audit_site_id, sitePass.findings,
  );

  // Merge the per-slice page results with this pass. A page rule that failed
  // on ANY slice has failed for the run.
  const ruleResults = await readRuleResults(tx, auditId);
  for (const [ruleId, passed] of sitePass.ruleResults) ruleResults.set(ruleId, passed);

  const allFindings = await readFindings(tx, auditId);
  const score = computeScore(ruleResults, allFindings, overrides);

  await tx.execute(sql`
    update audits
       set status = ${status}, phase = 'done', finished_at = now(),
           score = ${score.score}, critical_count = ${score.counts.critical},
           warning_count = ${score.counts.warning}, info_count = ${score.counts.info}
     where id = ${auditId}`);

  await tx.execute(sql`
    update audit_sites
       set score = ${score.score}, previous_score = ${audit!.previous_score},
           grade = ${score.grade}, tests_total = ${score.testsTotal},
           tests_passed = ${score.testsPassed},
           critical_count = ${score.counts.critical},
           warning_count = ${score.counts.warning},
           info_count = ${score.counts.info},
           last_audit_at = now(), updated_at = now()
     where id = ${audit!.audit_site_id}`);

  await tx.execute(sql`
    insert into audit_snapshots
      (workspace_id, audit_site_id, audit_id, score, critical_count, warning_count, info_count, pages_crawled)
    values (${workspaceId}, ${audit!.audit_site_id}, ${auditId}, ${score.score},
            ${score.counts.critical}, ${score.counts.warning}, ${score.counts.info},
            ${audit!.pages_crawled})`);

  await tx.execute(sql`
    insert into usage_counters (workspace_id, feature_key, period_start, used)
    values (${workspaceId}, 'audit.crawl_pages',
            ${currentPeriodStart().toISOString()}::timestamptz, ${audit!.pages_crawled})
    on conflict (workspace_id, feature_key, period_start)
      do update set used = usage_counters.used + excluded.used, updated_at = now()`);

  const events: RunOutcome['events'] = [
    {
      name: 'audit.run.completed',
      data: {
        auditId, siteId: audit!.site_id, score: score.score,
        previousScore: audit!.previous_score, pagesCrawled: audit!.pages_crawled,
        criticalCount: score.counts.critical,
      },
    },
    ...issueEvents,
  ];
  if (audit!.previous_score !== null && audit!.previous_score !== score.score) {
    events.push({
      name: 'audit.score.changed',
      data: {
        siteId: audit!.site_id, score: score.score, previous: audit!.previous_score,
        delta: score.score - audit!.previous_score,
      },
    });
  }

  return {
    auditId,
    score: score.score,
    previousScore: audit!.previous_score,
    pagesCrawled: audit!.pages_crawled,
    pagesBlocked: audit!.pages_blocked,
    counts: score.counts,
    budgetExhausted: Boolean((audit!.crawl_cursor as { frontier?: unknown[] }).frontier?.length),
    events,
  };
}



async function readRuleResults(
  tx: WorkspaceScopedDb,
  auditId: string,
): Promise<Map<string, boolean>> {
  const [row] = await tx.execute<{ results: Record<string, boolean> | null }>(sql`
    select crawl_cursor->'ruleResults' as results from audits where id = ${auditId}`);
  return new Map(Object.entries(row?.results ?? {}));
}

/** Every finding stored for this run, for the severity counts. */
async function readFindings(tx: WorkspaceScopedDb, auditId: string) {
  const rows = await tx.execute<{ rule_id: string; severity: string; page_url: string | null }>(sql`
    select rule_id, severity, page_url from audit_issues where audit_id = ${auditId}`);
  return rows.map((r) => ({
    ruleId: r.rule_id,
    severity: r.severity as 'critical' | 'warning' | 'info',
    url: r.page_url,
    evidence: {},
  }));
}

// ------------------------------------------------------------------ helpers

type CrawlConfigShape = {
  maxPages: number; maxDepth: number; respectRobots: boolean; renderJs: boolean;
  includePatterns?: string[]; excludePatterns?: string[];
  userAgent?: string; timeoutMs?: number;
  basicAuth?: { username: string; password: string };
  /** Loopback targets. Only set by tests and self-hosted installs. */
  allowPrivate?: boolean;
};

function fetchOptionsFrom(config: CrawlConfigShape): FetchOptions {
  return {
    ...(config.userAgent ? { userAgent: config.userAgent } : {}),
    ...(config.timeoutMs ? { timeoutMs: config.timeoutMs } : {}),
    ...(config.basicAuth ? { basicAuth: config.basicAuth } : {}),
    ...(config.allowPrivate ? { allowPrivate: true } : {}),
  };
}

async function loadSite(tx: WorkspaceScopedDb, opts: RunOptions) {
  const [site] = await tx.execute<{
    audit_site_id: string; site_id: string; host: string; root_url: string;
    crawl_config: Record<string, unknown>; score: number | null;
  }>(sql`
    select a.id as audit_site_id, s.id as site_id, s.host, s.root_url,
           a.crawl_config, a.score
      from audit_sites a join sites s on s.id = a.site_id
     where a.id = ${opts.auditSiteId} and a.workspace_id = ${opts.workspaceId}`);
  if (!site) throw new Error(`audit site ${opts.auditSiteId} not found`);
  return site;
}

/**
 * Read the crawl back out of the database for the site-wide rules.
 *
 * Holding every page in memory across slices is exactly what the slicing was
 * meant to avoid, so the finalize phase re-reads them instead.
 */
async function readPages(tx: WorkspaceScopedDb, auditId: string): Promise<PageFacts[]> {
  const rows = await tx.execute<Record<string, unknown>>(sql`
    select * from audit_pages where audit_id = ${auditId} order by depth, url`);

  return rows.map((r) => ({
    url: String(r.url),
    statusCode: Number(r.status_code ?? 0),
    fetchClass: String(r.fetch_class) as PageFacts['fetchClass'],
    redirectChain: (r.redirect_chain as string[]) ?? [],
    isHttps: Boolean(r.is_https),
    depth: Number(r.depth ?? 0),
    inSitemap: Boolean(r.in_sitemap),
    title: (r.title as string) ?? null,
    metaDescription: (r.meta_description as string) ?? null,
    canonical: (r.canonical as string) ?? null,
    headerCanonical: (r.header_canonical as string) ?? null,
    robotsMeta: (r.robots_meta as string) ?? null,
    xRobotsTag: (r.x_robots_tag as string) ?? null,
    isIndexable: Boolean(r.is_indexable),
    lang: (r.lang as string) ?? null,
    ogTitle: (r.og_title as string) ?? null,
    ogDescription: (r.og_description as string) ?? null,
    ogImage: (r.og_image as string) ?? null,
    headings: (r.headings as { level: number; text: string }[]) ?? [],
    wordCount: Number(r.word_count ?? 0),
    textRatio: Number(r.text_ratio ?? 0),
    contentHash: (r.content_hash as string) ?? null,
    // Per-page detail the page rules already consumed during the slice; the
    // site-wide rules need counts and links, not the raw elements again.
    images: Array.from({ length: Number(r.images_missing_alt ?? 0) }, () => ({
      src: '', alt: null, loading: null, format: '',
    })),
    links: [],
    scripts: [],
    inlineStyleCount: 0,
    deprecatedTags: [],
    forms: [],
    schemaTypes: (r.schema_types as string[]) ?? [],
    hreflang: (r.hreflang as { lang: string; href: string }[]) ?? [],
    hasViewport: true,
    hasCharset: true,
    hasDoctype: true,
    hasFavicon: true,
    plaintextEmails: [],
    responseMs: Number(r.response_ms ?? 0),
    ttfbMs: Number(r.ttfb_ms ?? 0),
    bytes: Number(r.bytes ?? 0),
    domNodes: 0,
    httpVersion: (r.http_version as string) ?? null,
    compression: (r.compression as string) ?? null,
    headers: (r.headers as Record<string, string>) ?? {},
    mixedContent: [],
    requestCount: 0,
  }));
}

async function readLinkedUrls(tx: WorkspaceScopedDb, auditId: string): Promise<Set<string>> {
  const rows = await tx.execute<{ target_url: string }>(sql`
    select distinct target_url from audit_links
     where audit_id = ${auditId} and is_internal`);
  return new Set(rows.map((r) => r.target_url));
}

async function rediscover(tx: WorkspaceScopedDb, auditId: string, origin: string) {
  const [audit] = await tx.execute<{ config: CrawlConfigShape }>(sql`
    select config from audits where id = ${auditId}`);
  const found = await discover(origin, fetchOptionsFrom(audit!.config));
  return {
    robotsTxt: found.robotsTxt,
    sitemap: found.sitemap,
    llmsTxt: found.llmsTxt,
    aiCrawlers: found.aiCrawlers,
  };
}

export class AuditNotAllowed extends Error {
  constructor(
    readonly reason: string,
    message: string,
  ) {
    super(message);
    this.name = 'AuditNotAllowed';
  }
}

/** Register a site with Audit, minting its URN so other tools can address it. */
export async function addSite(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; siteId: string; host: string },
): Promise<string> {
  /*
   * The site limit is enforced here, not in the UI.
   *
   * `audit.sites` was a declared entitlement that nothing consulted, so every
   * tier had unlimited sites. Re-registering an existing site is idempotent and
   * must stay free, so the check only applies when this would create a row —
   * otherwise a workspace exactly at its limit could never re-save a site it
   * already owns.
   */
  const [existing] = await tx.execute<{ id: string }>(sql`
    select id from audit_sites
     where site_id = ${opts.siteId} and workspace_id = ${opts.workspaceId}`);

  if (!existing) {
    const entitlement = await loadContext(tx, opts.workspaceId, 'audit.sites');
    if (!entitlement) throw new Error('audit.sites is not a known feature');
    const [counted] = await tx.execute<{ count: number }>(sql`
      select count(*)::int as count from audit_sites
       where workspace_id = ${opts.workspaceId}`);
    const decision = resolve({ ...entitlement, used: counted?.count ?? 0 }, 1);
    if (!decision.allowed) throw new AuditNotAllowed(decision.reason, decision.message);
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into audit_sites (workspace_id, project_id, site_id)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.siteId})
    on conflict (site_id) do update set updated_at = now()
    returning id`);

  const resource = await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'audit',
    type: 'audit_site',
    externalId: row!.id,
    label: opts.host,
  });

  // The edge that makes the Connected panel show this on the site's page.
  await relate(tx, {
    workspaceId: opts.workspaceId,
    from: resource.urn,
    to: coreUrn.site(opts.siteId),
    relation: 'audits',
    createdBy: 'system',
  });

  return row!.id;
}

// ------------------------------------------------------------- persistence

async function loadOverrides(
  tx: WorkspaceScopedDb,
  workspaceId: string,
): Promise<Record<string, RuleOverride>> {
  const rows = await tx.execute<{
    rule_id: string; is_enabled: boolean | null;
    severity: string | null; thresholds: Record<string, number | string | boolean> | null;
  }>(sql`
    select rule_id, is_enabled, severity, thresholds
      from audit_rule_overrides where workspace_id = ${workspaceId}`);

  const out: Record<string, RuleOverride> = {};
  for (const row of rows) {
    out[row.rule_id] = {
      ...(row.is_enabled !== null ? { isEnabled: row.is_enabled } : {}),
      ...(row.severity ? { severity: row.severity as RuleOverride['severity'] } : {}),
      ...(row.thresholds ? { thresholds: row.thresholds } : {}),
    };
  }
  return out;
}

const hash = (s: string) => createHash('sha256').update(s).digest('hex');

async function persistPages(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  auditId: string,
  pages: Awaited<ReturnType<typeof crawl>>['pages'],
): Promise<void> {
  for (const p of pages) {
    await tx.execute(sql`
      insert into audit_pages (
        workspace_id, audit_id, url, url_hash, status_code, fetch_class, redirect_chain,
        title, meta_description, canonical, header_canonical, robots_meta, x_robots_tag,
        og_title, og_description, og_image, h1_count, h2_count, h3_count, headings, h1_text,
        word_count, text_ratio, images_total, images_missing_alt, links_internal, links_external,
        has_structured_data, schema_types, hreflang, lang, is_indexable, depth, in_sitemap,
        content_hash, response_ms, ttfb_ms, bytes, http_version, compression, is_https, headers
      ) values (
        ${workspaceId}, ${auditId}, ${p.url}, ${hash(p.url)}, ${p.statusCode}, ${p.fetchClass},
        ${JSON.stringify(p.redirectChain)}::jsonb,
        ${p.title}, ${p.metaDescription}, ${p.canonical}, ${p.headerCanonical},
        ${p.robotsMeta}, ${p.xRobotsTag}, ${p.ogTitle}, ${p.ogDescription}, ${p.ogImage},
        ${p.headings.filter((h) => h.level === 1).length},
        ${p.headings.filter((h) => h.level === 2).length},
        ${p.headings.filter((h) => h.level === 3).length},
        ${JSON.stringify(p.headings)}::jsonb,
        ${p.headings.find((h) => h.level === 1)?.text ?? null},
        ${p.wordCount}, ${p.textRatio}, ${p.images.length},
        ${p.images.filter((i) => i.alt === null).length},
        ${p.links.filter((l) => l.isInternal).length},
        ${p.links.filter((l) => !l.isInternal).length},
        ${p.schemaTypes.length > 0}, ${textArray(p.schemaTypes)},
        ${JSON.stringify(p.hreflang)}::jsonb, ${p.lang}, ${p.isIndexable}, ${p.depth},
        ${p.inSitemap}, ${p.contentHash}, ${p.responseMs}, ${p.ttfbMs}, ${p.bytes},
        ${p.httpVersion}, ${p.compression}, ${p.isHttps}, ${JSON.stringify(p.headers)}::jsonb
      )
      on conflict (audit_id, url_hash) do nothing`);
  }
}

/**
 * The link graph. Kept rather than discarded, so "what links to this broken
 * page" is answerable after the crawl rather than only during it.
 */
async function persistLinks(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  auditId: string,
  pages: Awaited<ReturnType<typeof crawl>>['pages'],
): Promise<void> {
  const rows = pages.flatMap((p) =>
    p.links.slice(0, 500).map((l) => ({ source: p.url, ...l })),
  );
  for (let i = 0; i < rows.length; i += 200) {
    const chunk = rows.slice(i, i + 200);
    if (chunk.length === 0) continue;
    const values = chunk.map(
      (r) => sql`(${workspaceId}, ${auditId}, ${r.source}, ${r.href}, ${r.anchor.slice(0, 500)}, ${r.rel}, ${r.isInternal})`,
    );
    await tx.execute(sql`
      insert into audit_links (workspace_id, audit_id, source_url, target_url, anchor, rel, is_internal)
      values ${sql.join(values, sql`, `)}`);
  }
}

async function persistIssues(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  auditId: string,
  auditSiteId: string,
  findings: Finding[],
): Promise<{ name: string; data: Record<string, unknown> }[]> {
  const events: { name: string; data: Record<string, unknown> }[] = [];

  for (const finding of findings) {
    const [row] = await tx.execute<{ id: string }>(sql`
      insert into audit_issues
        (workspace_id, audit_id, audit_site_id, page_url, rule_id, severity, evidence)
      values (${workspaceId}, ${auditId}, ${auditSiteId}, ${finding.url},
              ${finding.ruleId}, ${finding.severity}, ${JSON.stringify(finding.evidence)}::jsonb)
      returning id`);

    // Only critical findings become events. Emitting one per info finding on a
    // 10,000-page crawl would flood the bus for no benefit.
    if (finding.severity === 'critical') {
      events.push({
        name: 'audit.issue.detected',
        data: {
          auditId,
          ruleId: finding.ruleId,
          severity: finding.severity,
          pageUrl: finding.url,
          ...(typeof (finding.evidence as { targetUrl?: string }).targetUrl === 'string'
            ? { targetUrl: (finding.evidence as { targetUrl: string }).targetUrl }
            : {}),
          evidence: finding.evidence,
          issueId: row!.id,
        },
      });
    }
  }
  return events;
}

export { inList, and, eq };
