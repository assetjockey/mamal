/**
 * Reading ad spend without asking a model.
 *
 * This is 4E's equivalent of 4A's opportunity finders and 4C's content score:
 * arithmetic over data the customer already has. With AI switched off — a
 * lifetime plan, a flipped kill switch — Market still reports on ad accounts,
 * and this is what that report is made of. `market.ai_insight` narrates these
 * numbers; it never produces them.
 *
 * Three things it refuses to do, each because the alternative is worse than
 * saying nothing:
 *
 * **It does not divide by zero and call it infinity.** A campaign with spend
 * and no conversions has no CPA — not an enormous one — and printing "£∞" or
 * "£0.00" are both lies. Null, and the UI says "no conversions yet".
 *
 * **It does not compare a partial period with a whole one.** Ad platforms
 * restate the last 28 days as conversions attribute late, so today's number is
 * always low. Comparisons run over settled windows of equal length.
 *
 * **It does not call small numbers a trend.** Three conversions becoming five
 * is a 67% rise and noise; the floor is stated rather than implied.
 */

export type MetricRow = {
  entityId: string;
  entityName: string | null;
  level: string;
  capturedOn: string;
  impressions: number;
  clicks: number;
  /** Micros, because money in floats is how ledgers stop balancing. */
  spendMicros: number;
  conversions: number;
  conversionValueMicros: number;
};

export type Totals = {
  impressions: number;
  clicks: number;
  spendMicros: number;
  conversions: number;
  conversionValueMicros: number;
  /** Click-through rate, 0–1. Null when nothing was shown. */
  ctr: number | null;
  /** Cost per click, micros. Null when nothing was clicked. */
  cpcMicros: number | null;
  /** Cost per acquisition, micros. Null when nothing converted. */
  cpaMicros: number | null;
  /** Return on ad spend, a multiple. Null when nothing was spent. */
  roas: number | null;
};

/** Below this, a percentage change is arithmetic rather than news. */
export const MIN_CONVERSIONS_FOR_TREND = 10;
export const MIN_SPEND_MICROS_FOR_TREND = 50_000_000; // 50 units of currency

export function totalsOf(rows: MetricRow[]): Totals {
  const sum = rows.reduce(
    (acc, row) => ({
      impressions: acc.impressions + row.impressions,
      clicks: acc.clicks + row.clicks,
      spendMicros: acc.spendMicros + row.spendMicros,
      conversions: acc.conversions + row.conversions,
      conversionValueMicros: acc.conversionValueMicros + row.conversionValueMicros,
    }),
    { impressions: 0, clicks: 0, spendMicros: 0, conversions: 0, conversionValueMicros: 0 },
  );

  return {
    ...sum,
    // Null rather than zero throughout: "no clicks yet" and "a CPC of zero"
    // are different facts and only one of them is true.
    ctr: sum.impressions === 0 ? null : sum.clicks / sum.impressions,
    cpcMicros: sum.clicks === 0 ? null : Math.round(sum.spendMicros / sum.clicks),
    cpaMicros: sum.conversions === 0 ? null : Math.round(sum.spendMicros / sum.conversions),
    roas: sum.spendMicros === 0 ? null : sum.conversionValueMicros / sum.spendMicros,
  };
}

/* -------------------------------------------------------------- findings */

export type Finding = {
  kind: 'wasting' | 'winning' | 'stalled' | 'creative_fatigue' | 'budget_capped';
  entityId: string;
  entityName: string;
  /** Higher is more worth acting on. Units differ per kind, so never compare across. */
  score: number;
  /** One sentence, with the numbers in it. */
  message: string;
  evidence: Record<string, number | string | null>;
};

export type FindingOptions = {
  currency?: string;
  /** The account's own target, when it has one. */
  targetCpaMicros?: number | null;
  targetRoas?: number | null;
};

const money = (micros: number, currency = 'USD') =>
  new Intl.NumberFormat('en-GB', { style: 'currency', currency, maximumFractionDigits: 0 })
    .format(micros / 1_000_000);

/**
 * Spend with nothing to show for it.
 *
 * The most useful single number in any ad account, and the one every dashboard
 * buries. A campaign past a sensible spend threshold with zero conversions is
 * not underperforming — it is not working, and the money is gone daily until
 * somebody looks.
 */
export function wasting(
  current: MetricRow[],
  opts: FindingOptions = {},
): Finding[] {
  const byEntity = groupBy(current, (r) => r.entityId);
  const findings: Finding[] = [];

  for (const [entityId, rows] of byEntity) {
    const totals = totalsOf(rows);
    if (totals.spendMicros < MIN_SPEND_MICROS_FOR_TREND) continue;

    if (totals.conversions === 0) {
      findings.push({
        kind: 'wasting',
        entityId,
        entityName: nameOf(rows),
        score: totals.spendMicros / 1_000_000,
        message:
          `${money(totals.spendMicros, opts.currency)} spent, no conversions. ` +
          `${totals.clicks.toLocaleString()} clicks arrived and none of them converted.`,
        evidence: {
          spendMicros: totals.spendMicros,
          clicks: totals.clicks,
          conversions: 0,
          ctr: totals.ctr,
        },
      });
      continue;
    }

    // Converting, but at a price the customer has said is too high.
    if (opts.targetCpaMicros && totals.cpaMicros && totals.cpaMicros > opts.targetCpaMicros * 1.5) {
      findings.push({
        kind: 'wasting',
        entityId,
        entityName: nameOf(rows),
        score: (totals.cpaMicros - opts.targetCpaMicros) / 1_000_000,
        message:
          `Costing ${money(totals.cpaMicros, opts.currency)} per conversion against a ` +
          `target of ${money(opts.targetCpaMicros, opts.currency)}.`,
        evidence: { cpaMicros: totals.cpaMicros, targetCpaMicros: opts.targetCpaMicros },
      });
    }
  }

  return findings.sort((a, b) => b.score - a.score);
}

/** Earning its keep, and worth more budget. */
export function winning(current: MetricRow[], opts: FindingOptions = {}): Finding[] {
  const findings: Finding[] = [];

  for (const [entityId, rows] of groupBy(current, (r) => r.entityId)) {
    const totals = totalsOf(rows);
    if (totals.conversions < MIN_CONVERSIONS_FOR_TREND) continue;
    if (totals.roas === null) continue;

    const target = opts.targetRoas ?? 2;
    if (totals.roas < target) continue;

    findings.push({
      kind: 'winning',
      entityId,
      entityName: nameOf(rows),
      score: totals.roas,
      message:
        `Returning ${totals.roas.toFixed(1)}× on ${money(totals.spendMicros, opts.currency)} ` +
        `across ${totals.conversions.toLocaleString()} conversions.`,
      evidence: {
        roas: totals.roas,
        spendMicros: totals.spendMicros,
        conversions: totals.conversions,
      },
    });
  }

  return findings.sort((a, b) => b.score - a.score);
}

/**
 * Was working, has stopped.
 *
 * Compares two equal, settled windows. The comparison is on *cost per
 * acquisition* rather than conversion count, because a campaign whose budget
 * was cut converts less without anything being wrong — CPA getting worse is the
 * signal that something changed.
 */
export function stalled(
  earlier: MetricRow[],
  later: MetricRow[],
  opts: FindingOptions = {},
): Finding[] {
  const before = groupBy(earlier, (r) => r.entityId);
  const after = groupBy(later, (r) => r.entityId);
  const findings: Finding[] = [];

  for (const [entityId, laterRows] of after) {
    const earlierRows = before.get(entityId);
    if (!earlierRows) continue;

    const was = totalsOf(earlierRows);
    const now = totalsOf(laterRows);

    // Both windows need enough conversions to mean anything. Three becoming
    // five is a 67% move and noise.
    if (was.conversions < MIN_CONVERSIONS_FOR_TREND) continue;
    if (was.cpaMicros === null) continue;

    if (now.conversions === 0) {
      findings.push({
        kind: 'stalled',
        entityId,
        entityName: nameOf(laterRows),
        score: was.conversions,
        message:
          `Converted ${was.conversions.toLocaleString()} times in the previous period and ` +
          `none in this one, on ${money(now.spendMicros, opts.currency)} of spend.`,
        evidence: { wasConversions: was.conversions, nowConversions: 0, nowSpendMicros: now.spendMicros },
      });
      continue;
    }

    if (now.cpaMicros === null) continue;
    const change = (now.cpaMicros - was.cpaMicros) / was.cpaMicros;
    if (change < 0.3) continue;

    findings.push({
      kind: 'stalled',
      entityId,
      entityName: nameOf(laterRows),
      score: change * 100,
      message:
        `Cost per conversion is up ${Math.round(change * 100)}% — ` +
        `${money(was.cpaMicros, opts.currency)} to ${money(now.cpaMicros, opts.currency)}.`,
      evidence: { wasCpaMicros: was.cpaMicros, nowCpaMicros: now.cpaMicros, changePct: change * 100 },
    });
  }

  return findings.sort((a, b) => b.score - a.score);
}

/**
 * The audience has seen it too often.
 *
 * Click-through falling while impressions hold is the classic shape of creative
 * fatigue, and it is worth separating from "the campaign is bad" because the
 * fix is different: new creative, not a new campaign.
 */
export function creativeFatigue(
  earlier: MetricRow[],
  later: MetricRow[],
): Finding[] {
  const before = groupBy(earlier, (r) => r.entityId);
  const findings: Finding[] = [];

  for (const [entityId, laterRows] of groupBy(later, (r) => r.entityId)) {
    const earlierRows = before.get(entityId);
    if (!earlierRows) continue;

    const was = totalsOf(earlierRows);
    const now = totalsOf(laterRows);
    if (was.ctr === null || now.ctr === null) continue;
    // Enough impressions that the rate means something.
    if (was.impressions < 5000 || now.impressions < 5000) continue;

    const drop = (was.ctr - now.ctr) / was.ctr;
    if (drop < 0.25) continue;

    // Impressions holding is what separates fatigue from simply being shown
    // less; a campaign that got quieter has a different problem.
    if (now.impressions < was.impressions * 0.7) continue;

    findings.push({
      kind: 'creative_fatigue',
      entityId,
      entityName: nameOf(laterRows),
      score: drop * 100,
      message:
        `Click-through is down ${Math.round(drop * 100)}% on similar impressions — ` +
        `${(was.ctr * 100).toFixed(2)}% to ${(now.ctr * 100).toFixed(2)}%. Usually new creative, not a new campaign.`,
      evidence: {
        wasCtr: was.ctr, nowCtr: now.ctr,
        wasImpressions: was.impressions, nowImpressions: now.impressions,
      },
    });
  }

  return findings.sort((a, b) => b.score - a.score);
}

/** Everything, in one call, ordered so the money question is answered first. */
export function findAll(
  earlier: MetricRow[],
  later: MetricRow[],
  opts: FindingOptions = {},
): Finding[] {
  return [
    ...wasting(later, opts),
    ...stalled(earlier, later, opts),
    ...creativeFatigue(earlier, later),
    ...winning(later, opts),
  ];
}

/* ---------------------------------------------------------------- windows */

/**
 * Two equal, settled windows to compare.
 *
 * `settleDays` is the gap left at the end. Ad platforms revise the last stretch
 * as conversions attribute late, so comparing yesterday against last month
 * reliably shows a decline that is not there. Three days is the usual floor;
 * some accounts need more and it is a parameter for that reason.
 */
export function comparisonWindows(opts: {
  today: Date;
  days?: number;
  settleDays?: number;
}): { earlier: { from: string; to: string }; later: { from: string; to: string } } {
  const days = opts.days ?? 14;
  const settle = opts.settleDays ?? 3;
  const day = 86_400_000;

  const end = new Date(opts.today.getTime() - settle * day);
  const laterFrom = new Date(end.getTime() - (days - 1) * day);
  const earlierTo = new Date(laterFrom.getTime() - day);
  const earlierFrom = new Date(earlierTo.getTime() - (days - 1) * day);

  const iso = (d: Date) => d.toISOString().slice(0, 10);
  return {
    earlier: { from: iso(earlierFrom), to: iso(earlierTo) },
    later: { from: iso(laterFrom), to: iso(end) },
  };
}

/* ----------------------------------------------------------------- shared */

function groupBy<T>(rows: T[], key: (row: T) => string): Map<string, T[]> {
  const out = new Map<string, T[]>();
  for (const row of rows) {
    const k = key(row);
    out.set(k, [...(out.get(k) ?? []), row]);
  }
  return out;
}

function nameOf(rows: MetricRow[]): string {
  return rows.find((r) => r.entityName)?.entityName ?? rows[0]?.entityId ?? 'unknown';
}
