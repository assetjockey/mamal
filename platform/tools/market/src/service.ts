import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { mint, coreUrn, relate } from '@mamal/resources';
import {
  cannibalisation, contentDecay, lowCtr, risingQueries, strikingDistance,
  type Opportunity, type PerformanceRow,
} from './opportunities.ts';

export class MarketNotAllowed extends Error {
  constructor(
    readonly reason: string,
    message: string,
  ) {
    super(message);
    this.name = 'MarketNotAllowed';
  }
}

/** One shape for every "have they got room for another" check. */
async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  countSql: ReturnType<typeof sql>,
  quantity = 1,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const [counted] = await tx.execute<{ count: number }>(countSql);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, quantity);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}

/* ==================================================================== */
/* Connections                                                          */
/* ==================================================================== */

/** Which allowance a provider is counted against. Absent means uncounted. */
const FEATURE_FOR_PROVIDER: Record<string, string | undefined> = {
  google_search_console: 'market.gsc_connections',
  google_analytics: 'market.ga4_connections',
  google_business: 'market.local',
  google_ads: 'market.ad_accounts',
  meta_ads: 'market.ad_accounts',
  tiktok_ads: 'market.ad_accounts',
  facebook: 'market.social_accounts',
  instagram: 'market.social_accounts',
  threads: 'market.social_accounts',
  x: 'market.social_accounts',
  linkedin: 'market.social_accounts',
  tiktok: 'market.social_accounts',
  youtube: 'market.social_accounts',
  pinterest: 'market.social_accounts',
  // Publishing destinations are counted when the destination row is created,
  // not when the CMS is connected — one WordPress can host several sites.
  wordpress: undefined,
  shopify: undefined,
  woocommerce: undefined,
  ghost: undefined,
  webhook: undefined,
};

export async function saveConnection(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    provider: string;
    externalId: string;
    displayName: string;
    avatarUrl?: string;
    credentialsEncrypted?: string;
    scopes?: string[];
    expiresAt?: Date | null;
    settings?: Record<string, unknown>;
  },
): Promise<string> {
  /*
   * The allowance applies to *adding* a connection, not to repairing one.
   *
   * Reconnecting after a token expires is the commonest thing that happens
   * here, and it is not a new connection — checking the limit first meant a
   * customer on "1 Search Console connection" could never fix the one they
   * had, which is the worst possible time to be told they are at their limit.
   */
  const [existing] = await tx.execute<{ id: string }>(sql`
    select id from market_connections
     where workspace_id = ${opts.workspaceId}
       and provider = ${opts.provider} and external_id = ${opts.externalId}`);

  /*
   * Counted against the feature that owns the provider, not one shared
   * "connections" limit. Social accounts and ad accounts already carry their
   * own — a single counter would charge a customer twice for one connection,
   * and would make "1 Search Console connection" mean something different
   * depending on how many Instagram pages they had linked.
   */
  const feature = FEATURE_FOR_PROVIDER[opts.provider];
  if (!existing && feature) {
    await requireHeadroom(
      tx,
      opts.workspaceId,
      feature,
      sql`select count(*)::int as count from market_connections
           where workspace_id = ${opts.workspaceId}
             and provider = ${opts.provider} and status <> 'revoked'`,
    );
  }

  /*
   * Upsert on (workspace, provider, external id).
   *
   * Reconnecting is the common case — a token expired, somebody re-authorised —
   * and inserting a second row would double every metric the connection feeds
   * without anything on screen to say why the numbers jumped.
   */
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into market_connections
      (workspace_id, project_id, provider, external_id, display_name, avatar_url,
       credentials_encrypted, scopes, expires_at, settings, status, last_error)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.provider}, ${opts.externalId},
            ${opts.displayName}, ${opts.avatarUrl ?? null},
            ${opts.credentialsEncrypted ?? null}, ${textArray(opts.scopes ?? [])},
            ${opts.expiresAt ? opts.expiresAt.toISOString() : null},
            ${JSON.stringify(opts.settings ?? {})}::jsonb, 'active', null)
    on conflict on constraint market_connections_key do update
       set display_name = excluded.display_name,
           avatar_url = excluded.avatar_url,
           credentials_encrypted = coalesce(excluded.credentials_encrypted,
                                            market_connections.credentials_encrypted),
           scopes = excluded.scopes,
           expires_at = excluded.expires_at,
           settings = market_connections.settings || excluded.settings,
           status = 'active',
           last_error = null,
           updated_at = now()
    returning id`);

  return row!.id;
}

/**
 * Marks a connection broken, with the provider's own words.
 *
 * `status` is separate from "has a token" deliberately: a revoked token still
 * exists, and the difference between "never connected" and "was connected and
 * stopped working" is the difference between an onboarding prompt and an alert
 * somebody has to act on.
 */
export async function markConnectionFailed(
  tx: WorkspaceScopedDb,
  opts: { connectionId: string; status: 'expired' | 'revoked' | 'error'; error: string },
): Promise<void> {
  await tx.execute(sql`
    update market_connections
       set status = ${opts.status}, last_error = ${opts.error.slice(0, 500)}, updated_at = now()
     where id = ${opts.connectionId}`);
}

/* ==================================================================== */
/* Keywords                                                             */
/* ==================================================================== */

export type KeywordInput = {
  keyword: string;
  locationCode?: number;
  languageCode?: string;
  volume?: number | null;
  cpcMicros?: number | null;
  competition?: number | null;
  difficulty?: number | null;
  intent?: string | null;
  monthly?: { year: number; month: number; volume: number }[];
  source?: string;
};

/**
 * Adds keywords, or refreshes what is known about them.
 *
 * Upserts on the natural key so re-running research does not duplicate a list,
 * and `coalesce`s each metric so a cheap source (autocomplete, Search Console)
 * cannot blank out figures a paid source already supplied. The reverse — a rich
 * source overwriting with nulls — is the bug this shape exists to prevent.
 */
export async function upsertKeywords(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; keywords: KeywordInput[] },
): Promise<number> {
  if (opts.keywords.length === 0) return 0;

  let written = 0;
  const CHUNK = 500;
  for (let i = 0; i < opts.keywords.length; i += CHUNK) {
    const chunk = opts.keywords.slice(i, i + CHUNK);
    const values = chunk.map((k) => sql`(
      ${opts.workspaceId}, ${opts.projectId}, ${k.keyword.trim().toLowerCase()},
      ${k.locationCode ?? 2840}, ${k.languageCode ?? 'en'},
      ${k.volume ?? null}, ${k.cpcMicros ?? null}, ${k.competition ?? null},
      ${k.difficulty ?? null}, ${k.intent ?? null},
      ${JSON.stringify(k.monthly ?? [])}::jsonb,
      ${k.source ?? 'dataforseo'},
      ${k.volume !== undefined && k.volume !== null ? sql`now()` : sql`null`}
    )`);

    const rows = await tx.execute<{ id: string }>(sql`
      insert into seo_keywords
        (workspace_id, project_id, keyword, location_code, language_code,
         volume, cpc_micros, competition, difficulty, intent, monthly, source, fetched_at)
      values ${sql.join(values, sql`, `)}
      on conflict on constraint seo_keywords_key do update
         set volume = coalesce(excluded.volume, seo_keywords.volume),
             cpc_micros = coalesce(excluded.cpc_micros, seo_keywords.cpc_micros),
             competition = coalesce(excluded.competition, seo_keywords.competition),
             difficulty = coalesce(excluded.difficulty, seo_keywords.difficulty),
             intent = coalesce(excluded.intent, seo_keywords.intent),
             monthly = case when jsonb_array_length(excluded.monthly) > 0
                            then excluded.monthly else seo_keywords.monthly end,
             fetched_at = coalesce(excluded.fetched_at, seo_keywords.fetched_at),
             updated_at = now()
      returning id`);
    written += rows.length;
  }
  return written;
}

/* ==================================================================== */
/* Rank tracking                                                        */
/* ==================================================================== */

export async function createRankConfig(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; projectId: string; domain: string; siteId?: string | null;
    locationCode?: number; languageCode?: string; devices?: string[]; schedule?: string;
  },
): Promise<string> {
  /*
   * Returns the existing tracker rather than making a second one.
   *
   * Two identical trackers double the SERP calls and the bill, and show the
   * same table twice — and "add a tracker for example.com" twice is something
   * a person does by accident, not a request for two.
   */
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into rank_configs
      (workspace_id, project_id, site_id, domain, location_code, language_code,
       devices, schedule, next_check_at)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.siteId ?? null},
            ${normaliseDomain(opts.domain)}, ${opts.locationCode ?? 2840},
            ${opts.languageCode ?? 'en'}, ${textArray(opts.devices ?? ['desktop'])},
            ${opts.schedule ?? 'weekly'}, now())
    on conflict on constraint rank_configs_key do update
       set is_active = true, updated_at = now()
    returning id`);

  const resource = await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'market',
    type: 'rank_config',
    externalId: row!.id,
    label: normaliseDomain(opts.domain),
  });

  if (opts.siteId) {
    // Puts the tracker on the site's Connected panel — the visible payoff of
    // one `sites` table across six tools.
    await relate(tx, {
      workspaceId: opts.workspaceId,
      from: resource.urn,
      to: coreUrn.site(opts.siteId),
      relation: 'tracks',
      createdBy: 'system',
    });
  }
  return row!.id;
}

/**
 * Adds keywords to a tracker, counting them against the plan as one batch.
 *
 * Per keyword the check would pass for the first N and fail for the rest,
 * leaving a half-added list — and the resolver's message would say "you have
 * used 240 of 250" when somebody pasted a hundred.
 */
export async function trackKeywords(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; configId: string; keywords: string[] },
): Promise<number> {
  const wanted = [...new Set(opts.keywords.map((k) => k.trim()).filter(Boolean))];
  if (wanted.length === 0) return 0;

  await requireHeadroom(
    tx,
    opts.workspaceId,
    'market.tracked_keywords',
    sql`select count(*)::int as count from rank_keywords
         where workspace_id = ${opts.workspaceId} and is_active`,
    wanted.length,
  );

  const values = wanted.map((k) => sql`(${opts.workspaceId}, ${opts.configId}, ${k})`);
  const rows = await tx.execute<{ id: string }>(sql`
    insert into rank_keywords (workspace_id, config_id, keyword)
    values ${sql.join(values, sql`, `)}
    on conflict on constraint rank_keywords_key do nothing
    returning id`);
  return rows.length;
}

/**
 * Records a day's positions and reports what moved.
 *
 * `previousPosition` is filled from the last snapshot rather than recomputed at
 * read time: it is what the movement events and the trend arrows read, and
 * deriving it in every query means a self-join on the largest table here.
 */
export async function recordRankSnapshots(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    configId: string;
    capturedOn: string;
    results: { keywordId: string; device: string; position: number | null; url?: string; serpFeatures?: string[] }[];
  },
): Promise<{ keywordId: string; device: string; from: number | null; to: number | null }[]> {
  if (opts.results.length === 0) return [];

  const moved: { keywordId: string; device: string; from: number | null; to: number | null }[] = [];

  for (const result of opts.results) {
    const [previous] = await tx.execute<{ position: number | null }>(sql`
      select position from rank_snapshots
       where keyword_id = ${result.keywordId} and device = ${result.device}
         and captured_on < ${opts.capturedOn}::date
       order by captured_on desc limit 1`);

    await tx.execute(sql`
      insert into rank_snapshots
        (workspace_id, config_id, keyword_id, captured_on, device, position,
         previous_position, url, serp_features)
      values (${opts.workspaceId}, ${opts.configId}, ${result.keywordId},
              ${opts.capturedOn}::date, ${result.device}, ${result.position},
              ${previous?.position ?? null}, ${result.url ?? null},
              ${textArray(result.serpFeatures ?? [])})
      on conflict on constraint rank_snapshots_key do update
         set position = excluded.position,
             url = excluded.url,
             serp_features = excluded.serp_features,
             updated_at = now()`);

    const from = previous?.position ?? null;
    if (from !== result.position) {
      moved.push({ keywordId: result.keywordId, device: result.device, from, to: result.position });
    }
  }
  return moved;
}

/**
 * Whether a move is worth telling anyone about.
 *
 * Positions wobble daily; an event for every ±1 would train people to ignore
 * the alerts. Entering or leaving the top ten always counts, because that is
 * where the traffic is — and a first appearance counts because it is news.
 */
export function isNotableMove(from: number | null, to: number | null, threshold = 3): boolean {
  if (from === null && to === null) return false;
  if (from === null) return to !== null && to <= 20;     // arrived from nowhere
  if (to === null) return from <= 20;                     // fell out of sight
  const crossedTopTen = (from > 10 && to <= 10) || (from <= 10 && to > 10);
  return crossedTopTen || Math.abs(from - to) >= threshold;
}

/* ==================================================================== */
/* Opportunities                                                        */
/* ==================================================================== */

/**
 * Runs every finder and persists the result.
 *
 * Materialised rather than computed per request: the finders scan months of
 * search performance, and a customer refreshing a dashboard should not re-run
 * that. Dismissals survive — the upsert leaves `status` alone — so a rejected
 * suggestion does not come back tomorrow, which is the difference between a
 * useful list and a nag.
 */
export async function recomputeOpportunities(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; today?: Date; windowDays?: number },
): Promise<{ found: number; byKind: Record<string, number> }> {
  const today = opts.today ?? new Date();
  const windowDays = opts.windowDays ?? 28;
  const iso = (d: Date) => d.toISOString().slice(0, 10);
  const daysAgo = (n: number) => new Date(today.getTime() - n * 86_400_000);

  const later = await fetchWindow(tx, opts.projectId, daysAgo(windowDays), today);
  const earlier = await fetchWindow(tx, opts.projectId, daysAgo(windowDays * 2), daysAgo(windowDays));

  const found: Opportunity[] = [
    ...strikingDistance(later),
    ...lowCtr(later),
    ...contentDecay(earlier, later),
    ...cannibalisation(later),
    ...risingQueries(earlier, later),
  ];

  const merged = mergeOverlapping(found);

  // Cap per kind. A big site produces thousands, and a list nobody can read is
  // the same as no list.
  const TOP = 50;
  const kept: Opportunity[] = [];
  const byKind: Record<string, number> = {};
  for (const kind of new Set(merged.map((o) => o.kind))) {
    const forKind = merged.filter((o) => o.kind === kind).slice(0, TOP);
    byKind[kind] = forKind.length;
    kept.push(...forKind);
  }

  for (const opportunity of kept) {
    await tx.execute(sql`
      insert into seo_opportunities
        (workspace_id, project_id, kind, query, page, score, evidence, detected_on)
      values (${opts.workspaceId}, ${opts.projectId}, ${opportunity.kind},
              ${opportunity.query}, ${opportunity.page}, ${opportunity.score},
              ${JSON.stringify(opportunity.evidence)}::jsonb, ${iso(today)}::date)
      on conflict on constraint seo_opportunities_key do update
         set score = excluded.score,
             evidence = excluded.evidence,
             detected_on = excluded.detected_on,
             updated_at = now()`);
  }

  return { found: kept.length, byKind };
}

/**
 * One row per thing to do, not per observation.
 *
 * The finders overlap on purpose — a query that appeared last month at position
 * 17 is genuinely both "rising" and "page two" — but they are the same job, and
 * listing it twice makes a nine-item list read as padded and doubles the
 * dismissals somebody has to click.
 *
 * The winner is the finder with an *action*: "rewrite this title" and "this
 * page slipped" tell you what to do, where "this is growing" tells you why to
 * care. The loser is folded into `alsoSeen`, so the card can still say "and
 * rising" — which is exactly the priority signal it was carrying.
 */
function mergeOverlapping(found: Opportunity[]): Opportunity[] {
  // Most actionable first. Ties are impossible: low_ctr only fires at or above
  // position 10 and striking_distance only below it.
  const PRECEDENCE = ['content_decay', 'cannibalisation', 'low_ctr', 'striking_distance', 'rising_query'];
  const rank = (kind: string) => {
    const at = PRECEDENCE.indexOf(kind);
    return at === -1 ? PRECEDENCE.length : at;
  };

  const byTarget = new Map<string, Opportunity[]>();
  for (const opportunity of found) {
    // Page-level findings (decay) have no query, so they never merge with a
    // query-level one — which is right: "this page is fading" and "this query
    // is rising" are different facts even on the same URL.
    const key = `${opportunity.query ?? ''}\u0000${opportunity.page ?? ''}`;
    byTarget.set(key, [...(byTarget.get(key) ?? []), opportunity]);
  }

  const out: Opportunity[] = [];
  for (const group of byTarget.values()) {
    if (group.length === 1) {
      out.push(group[0]!);
      continue;
    }
    const sorted = [...group].sort((a, b) => rank(a.kind) - rank(b.kind));
    const [primary, ...rest] = sorted as [Opportunity, ...Opportunity[]];
    out.push({
      ...primary,
      /*
       * The strongest signal's score wins rather than a sum: the scores are in
       * different units — impressions, missed clicks, lost clicks — and adding
       * them would produce a number that sorts confidently and means nothing.
       */
      score: Math.max(...group.map((o) => o.score)),
      evidence: {
        ...primary.evidence,
        alsoSeen: rest.map((o) => ({ kind: o.kind, ...o.evidence })),
      },
    });
  }
  return out;
}

async function fetchWindow(
  tx: WorkspaceScopedDb,
  projectId: string,
  from: Date,
  to: Date,
): Promise<PerformanceRow[]> {
  /*
   * Aggregated in the database, not in Node.
   *
   * A month of Search Console rows for a mid-sized site is hundreds of
   * thousands; pulling them across to sum them would be the slowest thing in
   * the tool. The position is impression-weighted here for the same reason it
   * is in `contentDecay` — a plain average lets a long tail of one-impression
   * queries drag it to nonsense.
   */
  return tx.execute<PerformanceRow>(sql`
    select query, page,
           sum(clicks)::int as clicks,
           sum(impressions)::int as impressions,
           (sum(position * impressions) / nullif(sum(impressions), 0))::real as position
      from market_search_performance
     where project_id = ${projectId}
       and captured_on >= ${from.toISOString().slice(0, 10)}::date
       and captured_on < ${to.toISOString().slice(0, 10)}::date
     group by query, page
    having sum(impressions) > 0`);
}

export async function setOpportunityStatus(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; id: string; status: 'open' | 'actioned' | 'dismissed' },
): Promise<void> {
  await tx.execute(sql`
    update seo_opportunities set status = ${opts.status}, updated_at = now()
     where id = ${opts.id} and workspace_id = ${opts.workspaceId}`);
}

/* ==================================================================== */
/* Shared                                                               */
/* ==================================================================== */

/** Lowercase, no scheme, no `www.`, no trailing slash — matching `sites.host`. */
export function normaliseDomain(input: string): string {
  return input
    .trim()
    .toLowerCase()
    .replace(/^https?:\/\//, '')
    .replace(/^www\./, '')
    .replace(/\/.*$/, '')
    .replace(/:\d+$/, '');
}
