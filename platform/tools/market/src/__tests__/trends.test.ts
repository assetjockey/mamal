/**
 * Trend diffing.
 *
 * Every assertion here is about a way trend alerts become worthless: firing on
 * the first run, firing on a 1 → 3 move, firing the same shift every hour, or
 * never firing at all on a climb that happens gradually.
 */
import { describe, expect, it } from 'vitest';
import {
  BASELINE_STALE_DAYS, MIN_INTEREST, diffTrends, snapshotKey, worthWriting,
  type Snapshot,
} from '../trends.ts';

const AT = new Date('2026-03-20T09:00:00Z');
const daysBefore = (n: number) =>
  new Date(AT.getTime() - n * 86_400_000).toISOString();

const base = (entries: [string, string, number, number?][]): Snapshot =>
  Object.fromEntries(
    entries.map(([kw, geo, value, ageDays]) => [
      snapshotKey(kw, geo),
      { value, at: daysBefore(ageDays ?? 1) },
    ]),
  );

describe('the first run', () => {
  it('records a baseline and alerts on nothing', () => {
    const result = diffTrends({}, [{ keyword: 'widgets', geo: 'US', value: 80 }], {
      thresholdPct: 25, now: AT,
    });

    // "New" is not "rising". Alerting here means every watch screams the
    // moment it is created.
    expect(result.shifts).toEqual([]);
    expect(result.baselined).toEqual([snapshotKey('widgets', 'US')]);
    expect(result.snapshot[snapshotKey('widgets', 'US')]!.value).toBe(80);
  });
});

describe('regions are separate facts', () => {
  it('compares each region against its own baseline', () => {
    const previous = base([['widgets', 'BR', 20], ['widgets', 'DE', 60]]);
    const result = diffTrends(
      previous,
      [
        { keyword: 'widgets', geo: 'BR', value: 60 },  // tripled
        { keyword: 'widgets', geo: 'DE', value: 61 },  // flat
      ],
      { thresholdPct: 25, now: AT },
    );

    // Averaged, these two would describe a country that does not exist.
    expect(result.shifts).toHaveLength(1);
    expect(result.shifts[0]).toMatchObject({ geo: 'BR', direction: 'rising' });
  });

  it('treats worldwide as a region rather than a missing one', () => {
    expect(snapshotKey('widgets', '')).toBe('widgets::');
    const result = diffTrends(base([['widgets', '', 20]]), [
      { keyword: 'widgets', geo: '', value: 50 },
    ], { thresholdPct: 25, now: AT });
    expect(result.shifts[0]!.reason).toMatch(/worldwide/);
  });
});

describe('the noise floor', () => {
  it('ignores a large percentage move between tiny numbers', () => {
    const result = diffTrends(base([['widgets', 'US', 1]]), [
      { keyword: 'widgets', geo: 'US', value: 3 },
    ], { thresholdPct: 25, now: AT });

    // 200%, and the difference between four searches and twelve.
    expect(result.shifts).toEqual([]);
  });

  it('reports a collapse from a real level', () => {
    const result = diffTrends(base([['widgets', 'US', 40]]), [
      { keyword: 'widgets', geo: 'US', value: 2 },
    ], { thresholdPct: 25, now: AT });

    // One end above the floor is enough — losing interest you had is news.
    expect(result.shifts[0]).toMatchObject({ direction: 'falling', current: 2 });
    expect(MIN_INTEREST).toBeGreaterThan(2);
  });
});

describe('not repeating itself', () => {
  it('rebases after firing, so a plateau alerts once', () => {
    const first = diffTrends(base([['widgets', 'US', 20]]), [
      { keyword: 'widgets', geo: 'US', value: 60 },
    ], { thresholdPct: 25, now: AT });
    expect(first.shifts).toHaveLength(1);

    const second = diffTrends(first.snapshot, [
      { keyword: 'widgets', geo: 'US', value: 61 },
    ], { thresholdPct: 25, now: AT });
    // Still high, no longer news.
    expect(second.shifts).toEqual([]);
  });
});

describe('a gradual climb', () => {
  it('is caught, because a quiet run does not move the baseline', () => {
    /*
     * 20 → 24 → 29 → 35. No single *step* clears 25%, so a scheduler that
     * rebased on every check would never report this climb at all — the
     * failure mode that makes trend watching feel useless.
     *
     * Holding the baseline through the quiet run means 29 is measured against
     * 20 and trips. It then rebases, so 35 is a 20% step from 29 and stays
     * quiet — one alert for one climb, which is the other half of the design.
     */
    let snapshot = base([['widgets', 'US', 20]]);
    const fired: number[] = [];

    for (const value of [24, 29, 35]) {
      const result = diffTrends(snapshot, [{ keyword: 'widgets', geo: 'US', value }], {
        thresholdPct: 25, now: AT,
      });
      snapshot = result.snapshot;
      if (result.shifts.length > 0) fired.push(value);
    }

    expect(fired).toEqual([29]);
    expect(snapshot[snapshotKey('widgets', 'US')]!.value).toBe(29);
  });

  it('refreshes a baseline that has gone stale, so it cannot drift forever', () => {
    const previous = base([['widgets', 'US', 20, BASELINE_STALE_DAYS + 1]]);
    const result = diffTrends(previous, [{ keyword: 'widgets', geo: 'US', value: 22 }], {
      thresholdPct: 25, now: AT,
    });

    expect(result.shifts).toEqual([]);
    // Quiet, but old enough that holding a month-old number would compare
    // today against a different season.
    expect(result.snapshot[snapshotKey('widgets', 'US')]!.value).toBe(22);
  });
});

describe('what is worth writing about', () => {
  it('is narrower than what is worth knowing', () => {
    const falling = {
      keyword: 'widgets', geo: 'US', previous: 80, current: 20,
      deltaPct: -75, direction: 'falling' as const, reason: '',
    };
    // Worth an alert, a poor thing to commission an article about.
    expect(worthWriting(falling)).toBe(false);

    const rising = { ...falling, previous: 20, current: 70, deltaPct: 250, direction: 'rising' as const };
    expect(worthWriting(rising)).toBe(true);

    const risingButSmall = { ...rising, current: 20 };
    expect(worthWriting(risingButSmall)).toBe(false);
  });
});
