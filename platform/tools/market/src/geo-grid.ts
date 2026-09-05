/**
 * Local rank grids: where you show up, block by block.
 *
 * A single "position 4" for a plumber is close to meaningless. They are first
 * in the suburb they are based in, fourth two miles out and absent five miles
 * away, and *which* of those is true where their customers actually are is the
 * whole question. So the ranking is sampled on a lattice of points around the
 * business and read as a map.
 *
 * **The trap this module exists to avoid:** a degree of latitude is about
 * 111 km everywhere, but a degree of *longitude* shrinks towards the poles — it
 * is 111 km at the equator and about 64 km at 55°N. Spacing a grid by equal
 * degrees produces a lattice that is nearly twice as wide as it is tall in
 * northern Europe, so the customer is told about coverage they do not have and
 * charged for points that are not where they think.
 *
 * Every point costs a lookup, so the geometry is worth getting right: a 7×7
 * grid is 49 paid searches, and a customer who wanted 5 km of coverage and got
 * 9 km of it has paid the same either way for a worse answer.
 */

export type Point = { latitude: number; longitude: number };

export type GridPoint = Point & {
  /** Column and row, 0-indexed from the north-west corner. */
  col: number;
  row: number;
  /** Kilometres from the centre. */
  distanceKm: number;
};

/** Mean radius. Good to a fraction of a percent at the distances a grid covers. */
const EARTH_RADIUS_KM = 6371.0088;

const toRadians = (degrees: number) => (degrees * Math.PI) / 180;

/**
 * Great-circle distance between two points, in kilometres.
 *
 * Haversine rather than the equirectangular approximation: the approximation is
 * fine at grid distances but wrong enough at the scale of a city-wide grid to
 * mislabel which points are inside the radius the customer asked for.
 */
export function distanceKm(a: Point, b: Point): number {
  const dLat = toRadians(b.latitude - a.latitude);
  const dLon = toRadians(b.longitude - a.longitude);
  const lat1 = toRadians(a.latitude);
  const lat2 = toRadians(b.latitude);

  const h =
    Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;
  return 2 * EARTH_RADIUS_KM * Math.asin(Math.min(1, Math.sqrt(h)));
}

export const GRID_SIZES = [3, 5, 7, 9, 11, 13, 15] as const;
export type GridSize = (typeof GRID_SIZES)[number];

/**
 * A square lattice centred on the business.
 *
 * `size` is odd by construction so there is a true centre point — the
 * business's own location, which is the one reading a customer looks at first.
 * An even grid has no centre and the map reads wrong.
 *
 * `radiusKm` is the distance from the centre to the edge along the axes, not
 * the corner: somebody asking for "5 km" means five kilometres out, and the
 * corners of the square reach further, which the returned `distanceKm` makes
 * visible rather than hiding.
 */
export function buildGrid(opts: {
  centre: Point;
  size: GridSize;
  radiusKm: number;
}): GridPoint[] {
  const { centre, size, radiusKm } = opts;
  if (size % 2 === 0) throw new Error('a rank grid needs an odd size so it has a centre');
  if (radiusKm <= 0) throw new Error('a rank grid needs a positive radius');

  const half = (size - 1) / 2;
  const stepKm = radiusKm / half;

  // A degree of latitude is ~111.32 km anywhere.
  const latStep = stepKm / 110.574;

  /*
   * Longitude is the part everyone gets wrong. The length of a degree of
   * longitude is proportional to cos(latitude), so at 55°N it is 64 km, not
   * 111. Dividing by that cosine widens the step in degrees to keep the step in
   * *kilometres* the same as the latitude one — which is what makes the lattice
   * square on the ground rather than square on a Mercator projection.
   */
  const cos = Math.cos(toRadians(centre.latitude));
  // Guarded: within a few metres of a pole the cosine goes to zero and the step
  // to infinity. A rank grid at the pole is not a real case, but a NaN would
  // propagate into stored coordinates.
  const lonStep = stepKm / (111.320 * Math.max(Math.abs(cos), 1e-6));

  const points: GridPoint[] = [];
  for (let row = 0; row < size; row += 1) {
    for (let col = 0; col < size; col += 1) {
      // Row 0 is the north edge, so latitude descends as row increases — the
      // order somebody reads a map in.
      const latitude = centre.latitude + (half - row) * latStep;
      const longitude = centre.longitude + (col - half) * lonStep;
      const point = { latitude: round7(latitude), longitude: round7(longitude) };

      points.push({ ...point, col, row, distanceKm: distanceKm(centre, point) });
    }
  }
  return points;
}

/** Coordinates are stored at 7 decimal places — about a centimetre. */
function round7(value: number): number {
  return Math.round(value * 1e7) / 1e7;
}

/* -------------------------------------------------------------- reading */

export type GridReading = {
  latitude: number;
  longitude: number;
  col: number;
  row: number;
  /** Null when the business did not appear at that point at all. */
  position: number | null;
};

export type GridSummary = {
  points: number;
  /** Points where the business appeared. */
  found: number;
  /** Fraction of points where it appeared at all, 0–1. */
  coverage: number;
  /** Fraction of points in the top three — the pack a customer actually sees. */
  inTopThree: number;
  /** Mean position across the points where it appeared. Null if nowhere. */
  averagePosition: number | null;
  /** Position at the business's own location, which is usually its best. */
  atCentre: number | null;
  /** The compass directions where it is weakest, for somebody to act on. */
  weakest: { direction: string; averagePosition: number | null; coverage: number }[];
};

/**
 * Rolls a grid into the numbers a customer acts on.
 *
 * `averagePosition` is taken over the points where the business *appeared*.
 * Substituting a sentinel — 21, or the grid depth — for "absent" is how these
 * numbers become nonsense: a business visible in three places at position 1
 * would score worse than one visible everywhere at position 8, purely because
 * the absences were counted as bad rankings rather than as absences. Coverage
 * is the number that carries that, and it is reported alongside.
 */
export function summariseGrid(readings: GridReading[], size: number): GridSummary {
  const found = readings.filter((r) => r.position !== null);
  const half = (size - 1) / 2;
  const centre = readings.find((r) => r.col === half && r.row === half);

  const positions = found.map((r) => r.position!);
  const topThree = found.filter((r) => r.position! <= 3).length;

  return {
    points: readings.length,
    found: found.length,
    coverage: readings.length === 0 ? 0 : found.length / readings.length,
    // Over *all* points, not over the ones where it was found: "in the pack in
    // 40% of the area" is the honest reading.
    inTopThree: readings.length === 0 ? 0 : topThree / readings.length,
    averagePosition:
      positions.length === 0
        ? null
        : Math.round((positions.reduce((a, b) => a + b, 0) / positions.length) * 10) / 10,
    atCentre: centre?.position ?? null,
    weakest: weakestDirections(readings, size),
  };
}

const DIRECTIONS = ['north', 'north-east', 'east', 'south-east', 'south', 'south-west', 'west', 'north-west'] as const;

/**
 * Which way to look.
 *
 * A grid summary that says "average position 6.2" tells somebody nothing they
 * can do. "You are invisible to the north-east" is a service-area setting, a
 * landing page, or a second location — an actual decision.
 */
function weakestDirections(
  readings: GridReading[],
  size: number,
): { direction: string; averagePosition: number | null; coverage: number }[] {
  const half = (size - 1) / 2;
  const buckets = new Map<string, GridReading[]>();

  for (const reading of readings) {
    const dx = reading.col - half;
    const dy = half - reading.row; // north is positive
    if (dx === 0 && dy === 0) continue;

    // atan2 with north at 0 and angles running clockwise, which is how compass
    // bearings work and how the eight-way split below is indexed.
    const bearing = (Math.atan2(dx, dy) * 180) / Math.PI;
    const normalised = (bearing + 360) % 360;
    const index = Math.round(normalised / 45) % 8;
    const direction = DIRECTIONS[index]!;
    buckets.set(direction, [...(buckets.get(direction) ?? []), reading]);
  }

  const summaries = [...buckets.entries()].map(([direction, group]) => {
    const found = group.filter((r) => r.position !== null);
    return {
      direction,
      averagePosition:
        found.length === 0
          ? null
          : Math.round(
              (found.reduce((a, r) => a + r.position!, 0) / found.length) * 10,
            ) / 10,
      coverage: found.length / group.length,
    };
  });

  /*
   * Worst first: no coverage at all beats a poor average, because "you do not
   * appear there" is a bigger problem than "you appear ninth there".
   */
  return summaries
    .sort((a, b) => {
      if (a.coverage !== b.coverage) return a.coverage - b.coverage;
      return (b.averagePosition ?? 0) - (a.averagePosition ?? 0);
    })
    .slice(0, 3);
}

/** What a grid will cost before it is run. */
export function gridCost(size: GridSize, keywords: number, creditsPerPoint = 1): number {
  return size * size * Math.max(1, keywords) * creditsPerPoint;
}

/** One grid, as read back for display. */
export type GridRun = {
  keyword: string;
  capturedOn: string;
  size: number;
  summary: GridSummary;
  readings: GridReading[];
};
