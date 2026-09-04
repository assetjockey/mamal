/**
 * The opportunity finders.
 *
 * Four pieces of arithmetic over Search Console data, and they are the most
 * valuable thing in Module 4A precisely because they cost nothing to run: GSC
 * is free, so a workspace on the free tier gets real analysis rather than a
 * teaser. Everything a paid vendor would add — volume, difficulty, competitor
 * gaps — is on top of this, not instead of it.
 *
 * Pure functions over rows, no I/O. The callers fetch, these decide, and the
 * tests can therefore assert the *maths* rather than a database round trip.
 */

export type PerformanceRow = {
  query: string;
  page: string;
  clicks: number;
  impressions: number;
  /** Average position over the window. Fractional, as GSC reports it. */
  position: number;
};

export type Opportunity = {
  kind: 'striking_distance' | 'low_ctr' | 'content_decay' | 'cannibalisation' | 'rising_query';
  query: string | null;
  page: string | null;
  /** Higher is more worth doing. Each finder documents its own scale. */
  score: number;
  evidence: Record<string, unknown>;
};

/* ------------------------------------------------------- striking distance */

/**
 * Queries ranking 11–20: page two.
 *
 * The classic, and it works because the click curve is a cliff. A query at 14
 * earns almost nothing; the same query at 8 earns real traffic, and moving six
 * places is usually an afternoon's work rather than a campaign. Ranked by
 * *impressions*, not by position — being 11th for something nobody searches is
 * not an opportunity.
 */
export function strikingDistance(
  rows: PerformanceRow[],
  opts: { minImpressions?: number; from?: number; to?: number } = {},
): Opportunity[] {
  const minImpressions = opts.minImpressions ?? 50;
  const from = opts.from ?? 11;
  const to = opts.to ?? 20;

  return rows
    .filter((r) => r.position >= from && r.position <= to && r.impressions >= minImpressions)
    .map((r) => ({
      kind: 'striking_distance' as const,
      query: r.query,
      page: r.page,
      /*
       * Impressions weighted by how close it already is. A query at 11 with
       * 500 impressions beats one at 19 with 600: the first is nearly there.
       */
      score: r.impressions * (1 - (r.position - from) / (to - from + 1)),
      evidence: {
        position: round(r.position),
        impressions: r.impressions,
        clicks: r.clicks,
        ctr: round(r.clicks / Math.max(1, r.impressions), 4),
      },
    }))
    .sort((a, b) => b.score - a.score);
}

/* ---------------------------------------------------------------- low CTR */

/**
 * The expected click-through rate at a given position.
 *
 * A published curve rather than a fitted one: fitting to a customer's own data
 * would define "expected" as "what you already get", which can never find an
 * underperformer. Positions beyond ten fall off asymptotically rather than to
 * zero, because page two still gets stray clicks.
 */
export function expectedCtr(position: number): number {
  const CURVE = [0.276, 0.152, 0.099, 0.072, 0.055, 0.043, 0.035, 0.030, 0.026, 0.023];
  if (position < 1) return CURVE[0]!;
  const index = Math.min(Math.round(position) - 1, CURVE.length - 1);
  if (position <= CURVE.length) return CURVE[index]!;
  // Beyond the curve, decay gently rather than snapping to zero.
  return Math.max(0.002, CURVE[CURVE.length - 1]! * (10 / position));
}

/**
 * Ranking well and being ignored.
 *
 * A page at position 3 earning a 4% click-through is losing roughly two thirds
 * of what that position is worth, and the fix is a title and a description
 * rather than a link-building campaign. That makes this the cheapest
 * opportunity on the list to act on.
 */
export function lowCtr(
  rows: PerformanceRow[],
  opts: { minImpressions?: number; maxPosition?: number; ratio?: number } = {},
): Opportunity[] {
  const minImpressions = opts.minImpressions ?? 100;
  const maxPosition = opts.maxPosition ?? 10;
  // Half of expected: anything tighter fires on ordinary variance.
  const ratio = opts.ratio ?? 0.5;

  return rows
    .filter((r) => r.impressions >= minImpressions && r.position <= maxPosition)
    .flatMap((r) => {
      const actual = r.clicks / r.impressions;
      const expected = expectedCtr(r.position);
      if (actual >= expected * ratio) return [];

      // What they would earn at the expected rate — the number that makes the
      // case for rewriting a title.
      const missed = Math.round(r.impressions * expected - r.clicks);
      return [{
        kind: 'low_ctr' as const,
        query: r.query,
        page: r.page,
        score: missed,
        evidence: {
          position: round(r.position),
          impressions: r.impressions,
          clicks: r.clicks,
          actualCtr: round(actual, 4),
          expectedCtr: round(expected, 4),
          missedClicks: missed,
        },
      }];
    })
    .filter((o) => o.score > 0)
    .sort((a, b) => b.score - a.score);
}

/* ----------------------------------------------------------- content decay */

/**
 * Pages that used to work.
 *
 * Compares two windows of the same length. Decay is the one finder that needs
 * history, and it is worth the storage: a page sliding from 400 clicks a month
 * to 120 is invisible in any single-period report, and it is usually fixable by
 * updating the page rather than writing a new one.
 */
export function contentDecay(
  earlier: PerformanceRow[],
  later: PerformanceRow[],
  opts: { minClicks?: number; dropPct?: number } = {},
): Opportunity[] {
  const minClicks = opts.minClicks ?? 30;
  const dropPct = opts.dropPct ?? 30;

  const before = new Map<string, { clicks: number; impressions: number; position: number }>();
  for (const row of earlier) {
    const seen = before.get(row.page);
    before.set(row.page, {
      clicks: (seen?.clicks ?? 0) + row.clicks,
      impressions: (seen?.impressions ?? 0) + row.impressions,
      // Impression-weighted, so a long tail of tiny queries cannot drag the
      // average around.
      position: weighted(seen, row),
    });
  }

  const after = new Map<string, { clicks: number; impressions: number; position: number }>();
  for (const row of later) {
    const seen = after.get(row.page);
    after.set(row.page, {
      clicks: (seen?.clicks ?? 0) + row.clicks,
      impressions: (seen?.impressions ?? 0) + row.impressions,
      position: weighted(seen, row),
    });
  }

  const out: Opportunity[] = [];
  for (const [page, was] of before) {
    if (was.clicks < minClicks) continue;
    const now = after.get(page) ?? { clicks: 0, impressions: 0, position: 0 };
    const lost = was.clicks - now.clicks;
    const drop = (lost / was.clicks) * 100;
    if (drop < dropPct) continue;

    out.push({
      kind: 'content_decay',
      query: null,
      page,
      score: lost,
      evidence: {
        clicksBefore: was.clicks,
        clicksAfter: now.clicks,
        dropPct: round(drop, 1),
        positionBefore: round(was.position),
        positionAfter: now.impressions > 0 ? round(now.position) : null,
        // Position roughly unchanged while clicks fell means the *search* moved
        // on, not the ranking — a different fix, and worth saying.
        rankingHeld: now.impressions > 0 && Math.abs(now.position - was.position) < 1.5,
      },
    });
  }
  return out.sort((a, b) => b.score - a.score);
}

/* --------------------------------------------------------- cannibalisation */

/**
 * Two pages competing for one query.
 *
 * Reported only when the second page is genuinely in contention — a page
 * ranking 4th and another 60th are not competing, they are simply both
 * relevant. The signal is that Google keeps swapping which one it shows, which
 * shows up as two pages both accumulating impressions for the same query.
 */
export function cannibalisation(
  rows: PerformanceRow[],
  opts: { minImpressions?: number; positionGap?: number } = {},
): Opportunity[] {
  const minImpressions = opts.minImpressions ?? 50;
  const positionGap = opts.positionGap ?? 10;

  const byQuery = new Map<string, PerformanceRow[]>();
  for (const row of rows) {
    if (row.impressions < 5) continue;   // noise
    byQuery.set(row.query, [...(byQuery.get(row.query) ?? []), row]);
  }

  const out: Opportunity[] = [];
  for (const [query, pages] of byQuery) {
    if (pages.length < 2) continue;
    const total = pages.reduce((n, p) => n + p.impressions, 0);
    if (total < minImpressions) continue;

    const sorted = [...pages].sort((a, b) => a.position - b.position);
    const [best, second] = sorted as [PerformanceRow, PerformanceRow];
    if (second.position - best.position > positionGap) continue;

    out.push({
      kind: 'cannibalisation',
      query,
      page: best.page,
      // How much of the query's traffic the runner-up is absorbing.
      score: second.impressions,
      evidence: {
        pages: sorted.slice(0, 4).map((p) => ({
          page: p.page,
          position: round(p.position),
          impressions: p.impressions,
          clicks: p.clicks,
        })),
        totalImpressions: total,
      },
    });
  }
  return out.sort((a, b) => b.score - a.score);
}

/* -------------------------------------------------------------- new demand */

/**
 * Queries that were not there before.
 *
 * The counterpart to decay, and the one that most often produces something to
 * write: a query with hundreds of impressions this month and none last month is
 * demand arriving, and being 20th for it is a starting position rather than a
 * failure.
 */
export function risingQueries(
  earlier: PerformanceRow[],
  later: PerformanceRow[],
  opts: { minImpressions?: number; growth?: number } = {},
): Opportunity[] {
  const minImpressions = opts.minImpressions ?? 100;
  const growth = opts.growth ?? 2;

  const before = new Map<string, number>();
  for (const row of earlier) before.set(row.query, (before.get(row.query) ?? 0) + row.impressions);

  const after = new Map<string, { impressions: number; clicks: number; position: number; page: string }>();
  for (const row of later) {
    const seen = after.get(row.query);
    after.set(row.query, {
      impressions: (seen?.impressions ?? 0) + row.impressions,
      clicks: (seen?.clicks ?? 0) + row.clicks,
      position: seen && seen.position < row.position ? seen.position : row.position,
      page: seen && seen.position < row.position ? seen.page : row.page,
    });
  }

  const out: Opportunity[] = [];
  for (const [query, now] of after) {
    if (now.impressions < minImpressions) continue;
    const was = before.get(query) ?? 0;
    // `was + 1` so a query appearing from nothing is the strongest signal
    // rather than a division by zero.
    if (now.impressions < (was + 1) * growth) continue;

    out.push({
      kind: 'rising_query',
      query,
      page: now.page,
      score: now.impressions - was,
      evidence: {
        impressionsBefore: was,
        impressionsAfter: now.impressions,
        clicks: now.clicks,
        position: round(now.position),
        isNew: was === 0,
      },
    });
  }
  return out.sort((a, b) => b.score - a.score);
}

/* ------------------------------------------------------------------ shared */

function round(value: number, places = 1): number {
  const factor = 10 ** places;
  return Math.round(value * factor) / factor;
}

function weighted(
  seen: { impressions: number; position: number } | undefined,
  row: PerformanceRow,
): number {
  const total = (seen?.impressions ?? 0) + row.impressions;
  if (total === 0) return row.position;
  return ((seen?.position ?? 0) * (seen?.impressions ?? 0) + row.position * row.impressions) / total;
}
