/**
 * Trend watch: noticing when interest in a term moves.
 *
 * `trends-checker` is the source here, and its one genuinely good idea is that
 * a trend is **a comparison against a stored baseline**, not a reading. Without
 * a saved snapshot every check reports "trending", because there is nothing to
 * be trending against — which is why so many trend alerts are worthless.
 *
 * Three things the sources get wrong and this does not:
 *
 * **Regions are separate facts.** A term rising in Brazil and flat in Germany
 * is two observations, and averaging them produces a number describing nowhere.
 * The baseline is per (keyword, geo).
 *
 * **A percentage change from a small base is not news.** Google Trends is a
 * 0–100 relative index; 1 → 3 is a 200% rise and means nothing. There is an
 * absolute floor as well as a percentage threshold.
 *
 * **The baseline moves, but slowly.** Rebasing to the latest value on every
 * check means a slow climb is never detected — each step is small against the
 * step before. The baseline is only rebased when a shift actually fires, or
 * when it has gone stale, so a sustained rise still trips it.
 */

export type Reading = { keyword: string; geo: string; value: number };

/** The stored baseline: last fired value per keyword and region. */
export type Snapshot = Record<string, { value: number; at: string }>;

export type Shift = {
  keyword: string;
  geo: string;
  previous: number;
  current: number;
  deltaPct: number;
  direction: 'rising' | 'falling';
  /** One sentence, in the terms the customer set the watch in. */
  reason: string;
};

export type DiffResult = {
  shifts: Shift[];
  /** The snapshot to store — only the keys that moved, or were absent, change. */
  snapshot: Snapshot;
  /** Readings with no baseline yet: recorded, never alerted. */
  baselined: string[];
};

/**
 * Below this the index is noise.
 *
 * Google Trends normalises to 0–100 within the requested window, so single
 * digits are the difference between four searches and twelve. Alerting on those
 * is how a trend feature becomes a muted email folder.
 */
export const MIN_INTEREST = 10;

/** How long a baseline stays usable before it is refreshed regardless. */
export const BASELINE_STALE_DAYS = 30;

export function snapshotKey(keyword: string, geo: string): string {
  // Geo is lower-cased and empty means worldwide, which is a real value rather
  // than a missing one.
  return `${keyword.trim().toLowerCase()}::${geo.trim().toLowerCase()}`;
}

/**
 * Compares fresh readings against the stored baseline.
 *
 * Pure: the caller decides what to do with the shifts and persists the returned
 * snapshot. That makes the interesting behaviour testable without a network,
 * a scheduler or the Python sidecar.
 */
export function diffTrends(
  previous: Snapshot,
  readings: Reading[],
  opts: { thresholdPct: number; now?: Date },
): DiffResult {
  const now = opts.now ?? new Date();
  const snapshot: Snapshot = { ...previous };
  const shifts: Shift[] = [];
  const baselined: string[] = [];

  for (const reading of readings) {
    const key = snapshotKey(reading.keyword, reading.geo);
    const base = previous[key];

    if (!base) {
      /*
       * First sight of this term in this region. Recorded, never alerted:
       * "new" is not "rising", and firing here would mean every watch screams
       * on the run that creates it.
       */
      snapshot[key] = { value: reading.value, at: now.toISOString() };
      baselined.push(key);
      continue;
    }

    const stale = daysBetween(new Date(base.at), now) >= BASELINE_STALE_DAYS;

    // Both ends must clear the floor. A fall from 40 to 2 is real; a rise from
    // 2 to 6 is four extra searches.
    const meaningful = Math.max(base.value, reading.value) >= MIN_INTEREST;
    const deltaPct = base.value === 0
      ? (reading.value === 0 ? 0 : 100)
      : ((reading.value - base.value) / base.value) * 100;

    if (meaningful && Math.abs(deltaPct) >= opts.thresholdPct) {
      const direction = deltaPct > 0 ? 'rising' : 'falling';
      shifts.push({
        keyword: reading.keyword,
        geo: reading.geo,
        previous: base.value,
        current: reading.value,
        deltaPct: Math.round(deltaPct * 10) / 10,
        direction,
        reason:
          `“${reading.keyword}” is ${direction} in ${geoLabel(reading.geo)} — ` +
          `interest moved from ${base.value} to ${reading.value}, ` +
          `${Math.abs(Math.round(deltaPct))}%.`,
      });
      // Rebased only now, so the next alert measures from the new plateau
      // rather than repeating this one every run.
      snapshot[key] = { value: reading.value, at: now.toISOString() };
      continue;
    }

    /*
     * Deliberately *not* rebased on a quiet run.
     *
     * Refreshing the baseline to the latest value every time is how a slow
     * climb goes unnoticed: 20 → 24 → 29 → 35 is a 75% rise that never shows a
     * single step above a 25% threshold. Holding the baseline lets the fourth
     * reading trip it. The staleness rule stops it drifting forever.
     */
    if (stale) snapshot[key] = { value: reading.value, at: now.toISOString() };
  }

  return { shifts, snapshot, baselined };
}

/**
 * Whether a shift should start a piece of content.
 *
 * A falling term is worth *knowing* and is a poor thing to write about, so the
 * pipeline trigger is deliberately narrower than the alert.
 */
export function worthWriting(shift: Shift, floor = 40): boolean {
  return shift.direction === 'rising' && shift.current >= floor;
}

function daysBetween(from: Date, to: Date): number {
  return Math.abs(to.getTime() - from.getTime()) / 86_400_000;
}

function geoLabel(geo: string): string {
  return geo.trim() === '' ? 'worldwide' : geo.trim().toUpperCase();
}
