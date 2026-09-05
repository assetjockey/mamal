/**
 * Local: the geo grid and NAP comparison.
 *
 * Both are pure, and both are places where the obvious implementation is
 * confidently wrong — a grid spaced in degrees is nearly twice as wide as it is
 * tall in northern Europe, and a string comparison of two addresses flags every
 * listing a customer has.
 */
import { describe, expect, it } from 'vitest';
import {
  GRID_SIZES, buildGrid, distanceKm, gridCost, summariseGrid, type GridReading,
} from '../geo-grid.ts';
import {
  actionable, compareNap, normaliseAddress, normaliseName, normalisePhone,
} from '../nap.ts';

const LONDON = { latitude: 51.5074, longitude: -0.1278 };
const QUITO = { latitude: -0.1807, longitude: -78.4678 };
const TROMSO = { latitude: 69.6492, longitude: 18.9553 };

describe('distance', () => {
  it('matches a known great-circle distance', () => {
    // London to Paris is about 344 km.
    const paris = { latitude: 48.8566, longitude: 2.3522 };
    expect(distanceKm(LONDON, paris)).toBeGreaterThan(340);
    expect(distanceKm(LONDON, paris)).toBeLessThan(348);
  });

  it('is zero for a point against itself', () => {
    expect(distanceKm(LONDON, LONDON)).toBe(0);
  });
});

describe('building a grid', () => {
  it('puts the business at the centre', () => {
    const grid = buildGrid({ centre: LONDON, size: 5, radiusKm: 4 });
    expect(grid).toHaveLength(25);

    const centre = grid.find((p) => p.col === 2 && p.row === 2)!;
    // An even grid has no centre, which is why the sizes are all odd.
    expect(centre.distanceKm).toBeLessThan(0.001);
    for (const size of GRID_SIZES) expect(size % 2).toBe(1);
  });

  it('is square on the ground, not square in degrees', () => {
    /*
     * The whole point. At 51°N a degree of longitude is ~69 km against
     * latitude's 111, so spacing by equal degrees would make the grid 1.6×
     * wider than it is tall — and the customer would be told about coverage
     * they do not have.
     */
    const grid = buildGrid({ centre: LONDON, size: 5, radiusKm: 4 });
    const centre = grid.find((p) => p.col === 2 && p.row === 2)!;
    const east = grid.find((p) => p.col === 4 && p.row === 2)!;
    const north = grid.find((p) => p.col === 2 && p.row === 0)!;

    expect(east.distanceKm).toBeCloseTo(4, 1);
    expect(north.distanceKm).toBeCloseTo(4, 1);
    // In raw degrees the longitude step is much larger — that is the correction.
    expect(Math.abs(east.longitude - centre.longitude)).toBeGreaterThan(
      Math.abs(north.latitude - centre.latitude) * 1.4,
    );
  });

  it('holds at the equator, where the correction is nothing', () => {
    const grid = buildGrid({ centre: QUITO, size: 3, radiusKm: 5 });
    const centre = grid.find((p) => p.col === 1 && p.row === 1)!;
    const east = grid.find((p) => p.col === 2 && p.row === 1)!;
    const north = grid.find((p) => p.col === 1 && p.row === 0)!;

    expect(east.distanceKm).toBeCloseTo(5, 1);
    // Degrees of latitude and longitude are nearly equal here.
    expect(Math.abs(east.longitude - centre.longitude)).toBeCloseTo(
      Math.abs(north.latitude - centre.latitude), 2,
    );
  });

  it('holds inside the Arctic Circle, where the correction is large', () => {
    const grid = buildGrid({ centre: TROMSO, size: 3, radiusKm: 3 });
    const east = grid.find((p) => p.col === 2 && p.row === 1)!;
    // 69°N: a degree of longitude is ~40 km. Without the cosine this point
    // would be nearly three times too far out.
    expect(east.distanceKm).toBeCloseTo(3, 0);
  });

  it('runs north to south, the way a map is read', () => {
    const grid = buildGrid({ centre: LONDON, size: 3, radiusKm: 2 });
    const topRow = grid.filter((p) => p.row === 0);
    const bottomRow = grid.filter((p) => p.row === 2);
    expect(topRow[0]!.latitude).toBeGreaterThan(bottomRow[0]!.latitude);
  });

  it('refuses a grid with no centre or no size', () => {
    expect(() => buildGrid({ centre: LONDON, size: 4 as never, radiusKm: 4 })).toThrow(/centre/);
    expect(() => buildGrid({ centre: LONDON, size: 5, radiusKm: 0 })).toThrow(/positive/);
  });

  it('states the cost before the grid is run', () => {
    // 49 paid searches for one keyword on a 7×7 is worth knowing in advance.
    expect(gridCost(7, 1)).toBe(49);
    expect(gridCost(7, 3)).toBe(147);
  });
});

describe('reading a grid', () => {
  /** A 3×3 where the business is strong in the west and absent in the east. */
  const readings: GridReading[] = [
    { col: 0, row: 0, latitude: 0, longitude: 0, position: 1 },
    { col: 1, row: 0, latitude: 0, longitude: 0, position: 2 },
    { col: 2, row: 0, latitude: 0, longitude: 0, position: null },
    { col: 0, row: 1, latitude: 0, longitude: 0, position: 1 },
    { col: 1, row: 1, latitude: 0, longitude: 0, position: 1 },
    { col: 2, row: 1, latitude: 0, longitude: 0, position: null },
    { col: 0, row: 2, latitude: 0, longitude: 0, position: 2 },
    { col: 1, row: 2, latitude: 0, longitude: 0, position: 4 },
    { col: 2, row: 2, latitude: 0, longitude: 0, position: null },
  ];

  it('averages over where it appeared, and reports coverage separately', () => {
    const summary = summariseGrid(readings, 3);

    /*
     * Six appearances averaging 1.8. Substituting 21 for the three absences
     * would give 8.3 — a number that says the business ranks badly when in fact
     * it ranks first almost everywhere it appears at all. Coverage carries the
     * absences.
     */
    expect(summary.averagePosition).toBe(1.8);
    expect(summary.found).toBe(6);
    expect(summary.coverage).toBeCloseTo(6 / 9);
  });

  it('measures the pack against the whole area, not against where it was found', () => {
    // Five of nine points in the top three — "in the pack across 56% of the
    // area" is the honest reading.
    expect(summariseGrid(readings, 3).inTopThree).toBeCloseTo(5 / 9);
  });

  it('reports the business’s own doorstep separately', () => {
    expect(summariseGrid(readings, 3).atCentre).toBe(1);
  });

  it('names the direction to act on', () => {
    const summary = summariseGrid(readings, 3);
    // "Average position 1.8" is not a decision. "You are invisible to the east"
    // is a service area, a landing page, or a second location.
    expect(summary.weakest[0]!.direction).toMatch(/east/);
    expect(summary.weakest[0]!.coverage).toBe(0);
  });

  it('says nothing rather than zero when it appears nowhere', () => {
    const absent = readings.map((r) => ({ ...r, position: null }));
    const summary = summariseGrid(absent, 3);
    expect(summary.averagePosition).toBeNull();
    expect(summary.coverage).toBe(0);
    expect(summary.atCentre).toBeNull();
  });
});

/* -------------------------------------------------------------------- NAP */

describe('normalising a business name', () => {
  it('ignores a legal suffix', () => {
    expect(normaliseName('Acme Widgets Ltd.')).toBe(normaliseName('Acme Widgets'));
    expect(normaliseName('Acme Widgets, Inc')).toBe(normaliseName('Acme Widgets'));
  });

  it('keeps a suffix that is part of the name', () => {
    // "Co-op Widgets" must not become "-op Widgets".
    expect(normaliseName('Co-op Widgets')).toContain('co-op');
  });

  it('treats & and and as the same', () => {
    expect(normaliseName('Smith & Sons')).toBe(normaliseName('Smith and Sons'));
  });

  it('folds accents', () => {
    expect(normaliseName('Café Rouge')).toBe(normaliseName('Cafe Rouge'));
  });
});

describe('normalising an address', () => {
  it('reads the same building written two ways', () => {
    expect(normaliseAddress('123 High Street, Suite 4')).toBe(
      normaliseAddress('123 High St #4'),
    );
  });

  it('never confuses two different numbers', () => {
    // Differs by less than most cosmetic differences, and matters far more.
    expect(normaliseAddress('123 High Street')).not.toBe(normaliseAddress('132 High Street'));
  });

  it('expands compass abbreviations and ordinals', () => {
    expect(normaliseAddress('400 N 1st Ave')).toBe(normaliseAddress('400 North 1 Avenue'));
  });

  it('handles a unit written as a hash', () => {
    expect(normaliseAddress('12 Mill Road, Unit 7')).toBe(normaliseAddress('12 Mill Rd #7'));
  });
});

describe('normalising a phone number', () => {
  it('reads one number written three ways as one number', () => {
    const international = normalisePhone('+44 20 7946 0958');
    expect(normalisePhone('0044 20 7946 0958')).toBe(international);
    expect(normalisePhone('(020) 7946-0958', '+44')).toBe(international);
  });

  it('needs to know the country before it can compare a national number', () => {
    // Without it, `020 7946 0958` and `+44 20 7946 0958` are simply different
    // digit strings — and guessing a country would be worse than saying so.
    expect(normalisePhone('020 7946 0958')).not.toBe(normalisePhone('+44 20 7946 0958'));
  });

  it('does not confuse two different numbers', () => {
    expect(normalisePhone('+44 20 7946 0958')).not.toBe(normalisePhone('+44 20 7946 0959'));
  });
});

describe('comparing a listing', () => {
  const ours = {
    name: 'Acme Widgets Ltd',
    address: '123 High Street, Suite 4, London',
    phone: '+44 20 7946 0958',
  };

  it('says nothing when the listing agrees', () => {
    expect(compareNap(ours, ours)).toEqual([]);
  });

  it('separates a cosmetic difference from a real one', () => {
    const differences = compareNap(ours, {
      name: 'Acme Widgets',
      address: '123 High St #4, London',
      phone: '020 7946 0958',
    }, { defaultCountry: '+44' });

    // All three normalise to the same thing, so none of them is worth an alert.
    expect(differences.every((d) => d.kind === 'formatting')).toBe(true);
    expect(actionable(differences)).toEqual([]);
  });

  it('catches the difference that matters', () => {
    const differences = compareNap(ours, {
      name: 'Acme Widgets',
      address: '132 High Street, Suite 4, London',
      phone: '+44 20 7946 0958',
    });

    const real = actionable(differences);
    expect(real).toHaveLength(1);
    expect(real[0]).toMatchObject({ field: 'address', kind: 'differs' });
    expect(real[0]!.note).toMatch(/check the number/i);
  });

  it('distinguishes a wrong value from a missing one', () => {
    const differences = compareNap(ours, { name: 'Acme Widgets Ltd', address: ours.address });
    const phone = differences.find((d) => d.field === 'phone')!;
    // Nothing on file and the wrong thing on file need different fixes.
    expect(phone.kind).toBe('missing');
    expect(phone.note).toMatch(/no phone on file/i);
  });
});
