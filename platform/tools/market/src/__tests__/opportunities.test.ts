import { describe, expect, it } from 'vitest';
import {
  cannibalisation, contentDecay, expectedCtr, lowCtr, risingQueries, strikingDistance,
  type PerformanceRow,
} from '../opportunities.ts';

const row = (over: Partial<PerformanceRow>): PerformanceRow => ({
  query: 'widgets', page: 'https://example.com/widgets',
  clicks: 0, impressions: 0, position: 50, ...over,
});

describe('striking distance', () => {
  it('finds page two and ignores page one and page three', () => {
    const found = strikingDistance([
      row({ query: 'page-two', position: 13, impressions: 400 }),
      row({ query: 'already-there', position: 4, impressions: 400 }),
      row({ query: 'far-off', position: 34, impressions: 400 }),
    ]);
    expect(found.map((o) => o.query)).toEqual(['page-two']);
  });

  it('ranks by traffic at stake, not by position', () => {
    /*
     * Being 11th for something nobody searches is not an opportunity. A query
     * at 11 with 500 impressions should still beat one at 19 with 600 — it is
     * nearly there — but both must beat a query at 11 with 60.
     */
    const found = strikingDistance([
      row({ query: 'close-and-big', position: 11, impressions: 500 }),
      row({ query: 'far-and-bigger', position: 19, impressions: 600 }),
      row({ query: 'close-and-tiny', position: 11, impressions: 60 }),
    ]);
    expect(found.map((o) => o.query)).toEqual(['close-and-big', 'far-and-bigger', 'close-and-tiny']);
  });

  it('ignores queries below the impression floor', () => {
    expect(strikingDistance([row({ position: 12, impressions: 10 })])).toEqual([]);
  });
});

describe('the expected CTR curve', () => {
  it('falls steeply through the top ten', () => {
    expect(expectedCtr(1)).toBeGreaterThan(expectedCtr(2));
    expect(expectedCtr(1)).toBeGreaterThan(0.25);
    expect(expectedCtr(10)).toBeLessThan(0.03);
  });

  it('decays rather than snapping to zero past page one', () => {
    // Page two still gets stray clicks; a curve that returns 0 makes every
    // low-CTR calculation beyond position 10 meaningless.
    expect(expectedCtr(25)).toBeGreaterThan(0);
    expect(expectedCtr(25)).toBeLessThan(expectedCtr(11));
    expect(expectedCtr(500)).toBeGreaterThanOrEqual(0.002);
  });

  it('handles a fractional position, which is what GSC reports', () => {
    expect(expectedCtr(2.4)).toBe(expectedCtr(2));
    expect(expectedCtr(0.9)).toBe(expectedCtr(1));
  });
});

describe('low CTR', () => {
  it('finds a good ranking that is being ignored', () => {
    // Position 3 expects ~9.9%; 1.5% is a title problem, not a ranking problem.
    const found = lowCtr([row({ query: 'ignored', position: 3, impressions: 2000, clicks: 30 })]);
    expect(found).toHaveLength(1);
    expect(found[0]!.evidence).toMatchObject({ actualCtr: 0.015 });
    // Scored by the clicks they are actually missing — the number that makes
    // the case for rewriting a title.
    expect(found[0]!.score).toBeGreaterThan(100);
  });

  it('leaves a normal click-through alone', () => {
    expect(lowCtr([row({ position: 3, impressions: 2000, clicks: 200 })])).toEqual([]);
  });

  it('does not fire on ordinary variance', () => {
    // Slightly under expected is not an opportunity; the threshold is half.
    const slightlyLow = Math.floor(2000 * expectedCtr(3) * 0.8);
    expect(lowCtr([row({ position: 3, impressions: 2000, clicks: slightlyLow })])).toEqual([]);
  });

  it('ignores page two, where the curve is guesswork', () => {
    expect(lowCtr([row({ position: 18, impressions: 5000, clicks: 1 })])).toEqual([]);
  });
});

describe('content decay', () => {
  const earlier = [
    row({ page: '/guide', query: 'a', clicks: 300, impressions: 4000, position: 3 }),
    row({ page: '/steady', query: 'b', clicks: 200, impressions: 3000, position: 4 }),
  ];

  it('finds a page that used to work', () => {
    const found = contentDecay(earlier, [
      row({ page: '/guide', query: 'a', clicks: 90, impressions: 3800, position: 3.2 }),
      row({ page: '/steady', query: 'b', clicks: 195, impressions: 2900, position: 4 }),
    ]);
    expect(found.map((o) => o.page)).toEqual(['/guide']);
    expect(found[0]!.evidence).toMatchObject({ clicksBefore: 300, clicksAfter: 90, dropPct: 70 });
  });

  it('says whether the ranking held, because the fix differs', () => {
    // Clicks fell, position did not: the *search* moved on, and rewriting for
    // rank would be the wrong response.
    const held = contentDecay(earlier, [
      row({ page: '/guide', query: 'a', clicks: 80, impressions: 1000, position: 3.1 }),
    ]);
    expect(held[0]!.evidence).toMatchObject({ rankingHeld: true });

    const slipped = contentDecay(earlier, [
      row({ page: '/guide', query: 'a', clicks: 80, impressions: 3900, position: 14 }),
    ]);
    expect(slipped[0]!.evidence).toMatchObject({ rankingHeld: false });
  });

  it('reports a page that vanished entirely', () => {
    const found = contentDecay(earlier, []);
    expect(found.map((o) => o.page)).toEqual(['/guide', '/steady']);
    expect(found[0]!.evidence).toMatchObject({ clicksAfter: 0, positionAfter: null });
  });

  it('ignores a page that never had traffic to lose', () => {
    expect(contentDecay([row({ page: '/tiny', clicks: 4, impressions: 40 })], [])).toEqual([]);
  });

  it('weights the position by impressions, so a long tail cannot skew it', () => {
    /*
     * One query at position 2 with 10,000 impressions and forty at position 80
     * with one each. A plain mean says ~78; the honest answer is ~2.3.
     */
    const before = [
      row({ page: '/p', query: 'main', clicks: 900, impressions: 10_000, position: 2 }),
      ...Array.from({ length: 40 }, (_, i) =>
        row({ page: '/p', query: `tail-${i}`, clicks: 0, impressions: 1, position: 80 })),
    ];
    const found = contentDecay(before, []);
    expect(found[0]!.evidence.positionBefore as number).toBeLessThan(3);
  });
});

describe('cannibalisation', () => {
  it('finds two pages genuinely competing for one query', () => {
    const found = cannibalisation([
      row({ query: 'best widgets', page: '/a', position: 5, impressions: 600, clicks: 40 }),
      row({ query: 'best widgets', page: '/b', position: 9, impressions: 400, clicks: 10 }),
    ]);
    expect(found).toHaveLength(1);
    expect(found[0]!.page).toBe('/a');
    expect((found[0]!.evidence.pages as unknown[]).length).toBe(2);
  });

  it('leaves alone two pages that are merely both relevant', () => {
    // 4th and 60th are not competing — Google has made its choice.
    expect(cannibalisation([
      row({ query: 'best widgets', page: '/a', position: 4, impressions: 600 }),
      row({ query: 'best widgets', page: '/b', position: 60, impressions: 400 }),
    ])).toEqual([]);
  });

  it('ignores a query nobody searches', () => {
    expect(cannibalisation([
      row({ query: 'obscure', page: '/a', position: 5, impressions: 12 }),
      row({ query: 'obscure', page: '/b', position: 7, impressions: 8 }),
    ])).toEqual([]);
  });
});

describe('rising queries', () => {
  it('finds demand that was not there before', () => {
    const found = risingQueries(
      [row({ query: 'old thing', impressions: 500 })],
      [
        row({ query: 'new thing', impressions: 900, clicks: 12, position: 19, page: '/x' }),
        row({ query: 'old thing', impressions: 520 }),
      ],
    );
    expect(found.map((o) => o.query)).toEqual(['new thing']);
    expect(found[0]!.evidence).toMatchObject({ isNew: true, impressionsBefore: 0 });
  });

  it('requires real growth, not noise', () => {
    expect(risingQueries(
      [row({ query: 'steady', impressions: 800 })],
      [row({ query: 'steady', impressions: 900 })],
    )).toEqual([]);
  });

  it('keeps the best-ranking page for the query', () => {
    const found = risingQueries([], [
      row({ query: 'new', impressions: 300, position: 30, page: '/worse' }),
      row({ query: 'new', impressions: 300, position: 8, page: '/better' }),
    ]);
    expect(found[0]!.page).toBe('/better');
    expect(found[0]!.evidence).toMatchObject({ position: 8 });
  });
});
