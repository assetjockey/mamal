/**
 * A QR encoder, in byte mode, for versions 1–40 at every error-correction
 * level.
 *
 * Written rather than depended on for one reason: **the free tier renders QR
 * codes in the customer's own browser**, because server-side rasterisation is
 * CPU we pay for and §0.6 says a free action creates no dedicated compute. So
 * this has to run in a browser, in a Worker, and in Node, with no DOM, no
 * canvas and no dependency — and it has to be small enough to ship in a page.
 *
 * Byte mode only. Numeric and alphanumeric modes are denser for their inputs,
 * but every payload here is a URL, a vCard or a wifi string, and byte mode
 * encodes all of them correctly. Adding modes would buy a few modules of
 * density on inputs we do not have, at the cost of the segmentation optimiser
 * being the buggiest part of every QR library ever written.
 *
 * The output is a boolean matrix. Drawing it — SVG, canvas, PNG — is somebody
 * else's problem, which is what lets the same matrix serve a browser preview
 * and a print-resolution export.
 */

export type EcLevel = 'L' | 'M' | 'Q' | 'H';

export type Matrix = {
  size: number;
  /** Row-major; `at(x, y)` is the readable accessor. */
  modules: boolean[];
  version: number;
  ecLevel: EcLevel;
};

export class QrTooLong extends Error {
  constructor(readonly bytes: number) {
    super(`${bytes} bytes is more than a QR code can hold at this error-correction level.`);
    this.name = 'QrTooLong';
  }
}

/* ------------------------------------------------------------------ tables */

/** Total codewords per version (data + error correction). */
const TOTAL_CODEWORDS = [
  0, 26, 44, 70, 100, 134, 172, 196, 242, 292, 346, 404, 466, 532, 581, 655, 733, 815, 901, 991,
  1085, 1156, 1258, 1364, 1474, 1588, 1706, 1828, 1921, 2051, 2185, 2323, 2465, 2611, 2761, 2876,
  3034, 3196, 3362, 3532, 3706,
];

/**
 * `[ecCodewordsPerBlock, group1Blocks, group2Blocks]` per version, per level.
 *
 * Group 2's blocks each hold one more data codeword than group 1's; the split
 * falls out of the totals, so only the block counts are stored.
 */
const EC_BLOCKS: Record<EcLevel, number[][]> = {
  L: [[], [7,1,0],[10,1,0],[15,1,0],[20,1,0],[26,1,0],[18,2,0],[20,2,0],[24,2,0],[30,2,0],[18,2,2],
      [20,4,0],[24,2,2],[26,4,0],[30,3,1],[22,5,1],[24,5,1],[28,1,5],[30,5,1],[28,3,4],[28,3,5],
      [28,4,4],[28,2,7],[30,4,5],[30,6,4],[26,8,4],[28,10,2],[30,8,4],[30,3,10],[30,7,7],[30,5,10],
      [30,13,3],[30,17,0],[30,17,1],[30,13,6],[30,12,7],[30,6,14],[30,17,4],[30,4,18],[30,20,4],
      [30,19,6]],
  M: [[], [10,1,0],[16,1,0],[26,1,0],[18,2,0],[24,2,0],[16,4,0],[18,4,0],[22,2,2],[22,3,2],[26,4,1],
      [30,1,4],[22,6,2],[22,8,1],[24,4,5],[24,5,5],[28,7,3],[28,10,1],[26,9,4],[26,3,11],[26,3,13],
      [26,17,0],[28,17,0],[28,4,14],[28,6,14],[28,8,13],[28,19,4],[28,22,3],[28,3,23],[28,21,7],
      [28,19,10],[28,2,29],[28,10,23],[28,14,21],[28,14,23],[28,12,26],[28,6,34],[28,29,14],
      [28,13,32],[28,40,7],[28,18,31]],
  Q: [[], [13,1,0],[22,1,0],[18,2,0],[26,2,0],[18,2,2],[24,4,0],[18,2,4],[22,4,2],[20,4,4],[24,6,2],
      [28,4,4],[26,4,6],[24,8,4],[20,11,5],[30,5,7],[24,15,2],[28,1,15],[28,17,1],[26,17,4],
      [30,15,5],[28,17,6],[30,7,16],[30,11,14],[30,11,16],[30,7,22],[28,28,6],[30,8,26],[30,4,31],
      [30,1,37],[30,15,25],[30,42,1],[30,10,35],[30,29,19],[30,44,7],[30,39,14],[30,46,10],
      [30,49,10],[30,48,14],[30,43,22],[30,34,34]],
  H: [[], [17,1,0],[28,1,0],[22,2,0],[16,4,0],[22,2,2],[28,4,0],[26,4,1],[26,4,2],[24,4,4],[28,6,2],
      [24,3,8],[28,7,4],[22,12,4],[24,11,5],[24,11,7],[30,3,13],[28,2,17],[28,2,19],[26,9,16],
      [28,15,10],[30,19,6],[24,34,0],[30,16,14],[30,30,2],[30,22,13],[30,33,4],[30,12,28],
      [30,11,31],[30,19,26],[30,23,25],[30,23,28],[30,19,35],[30,11,46],[30,59,1],[30,22,41],
      [30,2,64],[30,24,46],[30,42,32],[30,10,67],[30,20,61]],
};

/** Alignment-pattern centre coordinates, per version. */
const ALIGNMENT = [
  [], [], [6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],
  [6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],
  [6,34,62,90],[6,28,50,72,94],[6,26,50,74,98],[6,30,54,78,102],[6,28,54,80,106],[6,32,58,84,110],
  [6,30,58,86,114],[6,34,62,90,118],[6,26,50,74,98,122],[6,30,54,78,102,126],[6,26,52,78,104,130],
  [6,30,56,82,108,134],[6,34,60,86,112,138],[6,30,58,86,114,142],[6,34,62,90,118,146],
  [6,30,54,78,102,126,150],[6,24,50,76,102,128,154],[6,28,54,80,106,132,158],
  [6,32,58,84,110,136,162],[6,26,54,82,110,138,166],[6,30,58,86,114,142,170],
];

const EC_BITS: Record<EcLevel, number> = { L: 0b01, M: 0b00, Q: 0b11, H: 0b10 };

/* ------------------------------------------------- Galois field arithmetic */

const EXP = new Uint8Array(512);
const LOG = new Uint8Array(256);
(() => {
  let x = 1;
  for (let i = 0; i < 255; i++) {
    EXP[i] = x;
    LOG[x] = i;
    x <<= 1;
    if (x & 0x100) x ^= 0x11d; // the QR field's primitive polynomial
  }
  for (let i = 255; i < 512; i++) EXP[i] = EXP[i - 255]!;
})();

const mul = (a: number, b: number) => (a === 0 || b === 0 ? 0 : EXP[LOG[a]! + LOG[b]!]!);

/** The generator polynomial for `degree` error-correction codewords. */
function generator(degree: number): number[] {
  let poly = [1];
  for (let i = 0; i < degree; i++) {
    const next = new Array<number>(poly.length + 1).fill(0);
    for (let j = 0; j < poly.length; j++) {
      next[j] = next[j]! ^ mul(poly[j]!, EXP[i]!);
      next[j + 1] = next[j + 1]! ^ poly[j]!;
    }
    poly = next;
  }
  return poly;
}

function remainder(data: number[], degree: number): number[] {
  const gen = generator(degree);
  const out = new Array<number>(degree).fill(0);
  for (const byte of data) {
    const factor = byte ^ out[0]!;
    out.shift();
    out.push(0);
    for (let i = 0; i < degree; i++) out[i] = out[i]! ^ mul(gen[i + 1]!, factor);
  }
  return out;
}

/* ------------------------------------------------------------------ encode */

export function encode(text: string, ecLevel: EcLevel = 'M', minVersion = 1): Matrix {
  const bytes = Array.from(new TextEncoder().encode(text));
  const version = pickVersion(bytes.length, ecLevel, minVersion);

  const bits: number[] = [];
  const push = (value: number, length: number) => {
    for (let i = length - 1; i >= 0; i--) bits.push((value >>> i) & 1);
  };

  push(0b0100, 4);                                   // byte mode
  push(bytes.length, version < 10 ? 8 : 16);         // character count
  for (const b of bytes) push(b, 8);

  const capacityBits = dataCodewords(version, ecLevel) * 8;
  push(0, Math.min(4, capacityBits - bits.length));  // terminator
  while (bits.length % 8 !== 0) bits.push(0);

  const codewords: number[] = [];
  for (let i = 0; i < bits.length; i += 8) {
    codewords.push(bits.slice(i, i + 8).reduce((n, b) => (n << 1) | b, 0));
  }
  // The standard's alternating pad bytes, until the capacity is full.
  const PAD = [0xec, 0x11];
  for (let i = 0; codewords.length < capacityBits / 8; i++) codewords.push(PAD[i % 2]!);

  const interleaved = interleave(codewords, version, ecLevel);
  return draw(interleaved, version, ecLevel);
}

function dataCodewords(version: number, ec: EcLevel): number {
  const [ecPer, g1, g2] = EC_BLOCKS[ec][version] as [number, number, number];
  return TOTAL_CODEWORDS[version]! - ecPer * (g1 + g2);
}

function pickVersion(byteLength: number, ec: EcLevel, minVersion: number): number {
  for (let v = Math.max(1, minVersion); v <= 40; v++) {
    const headerBits = 4 + (v < 10 ? 8 : 16);
    if (dataCodewords(v, ec) * 8 >= headerBits + byteLength * 8) return v;
  }
  throw new QrTooLong(byteLength);
}

/**
 * Splits into blocks, computes each block's EC codewords, and interleaves.
 *
 * Interleaving is what makes a QR code survive a coffee ring: consecutive
 * codewords of one block end up spread across the symbol, so damage to any one
 * region is spread thinly across every block rather than destroying one
 * entirely.
 */
function interleave(codewords: number[], version: number, ec: EcLevel): number[] {
  const [ecPer, g1Count, g2Count] = EC_BLOCKS[ec][version] as [number, number, number];
  const total = g1Count + g2Count;
  const g1Size = Math.floor(dataCodewords(version, ec) / total);

  const dataBlocks: number[][] = [];
  const ecBlocks: number[][] = [];
  let offset = 0;
  for (let i = 0; i < total; i++) {
    const size = i < g1Count ? g1Size : g1Size + 1;
    const block = codewords.slice(offset, offset + size);
    offset += size;
    dataBlocks.push(block);
    ecBlocks.push(remainder(block, ecPer));
  }

  const out: number[] = [];
  const longest = Math.max(...dataBlocks.map((b) => b.length));
  for (let i = 0; i < longest; i++) {
    for (const block of dataBlocks) if (i < block.length) out.push(block[i]!);
  }
  for (let i = 0; i < ecPer; i++) {
    for (const block of ecBlocks) out.push(block[i]!);
  }
  return out;
}

/* ------------------------------------------------------------------- draw */

function draw(codewords: number[], version: number, ec: EcLevel): Matrix {
  const size = version * 4 + 17;
  const modules = new Array<boolean>(size * size).fill(false);
  const reserved = new Array<boolean>(size * size).fill(false);
  const at = (x: number, y: number) => y * size + x;

  const set = (x: number, y: number, on: boolean, keep = true) => {
    modules[at(x, y)] = on;
    if (keep) reserved[at(x, y)] = true;
  };

  // Finder patterns and their separators.
  for (const [ox, oy] of [[0, 0], [size - 7, 0], [0, size - 7]] as [number, number][]) {
    for (let y = -1; y <= 7; y++) {
      for (let x = -1; x <= 7; x++) {
        const px = ox + x;
        const py = oy + y;
        if (px < 0 || py < 0 || px >= size || py >= size) continue;
        const inner = x >= 0 && x <= 6 && y >= 0 && y <= 6;
        const on = inner && (x === 0 || x === 6 || y === 0 || y === 6 || (x >= 2 && x <= 4 && y >= 2 && y <= 4));
        set(px, py, on);
      }
    }
  }

  // Timing patterns.
  for (let i = 8; i < size - 8; i++) {
    set(i, 6, i % 2 === 0);
    set(6, i, i % 2 === 0);
  }

  // Alignment patterns, skipping the three that would sit on a finder.
  const centres = ALIGNMENT[version]!;
  for (const cy of centres) {
    for (const cx of centres) {
      const onFinder =
        (cx <= 8 && cy <= 8) || (cx >= size - 9 && cy <= 8) || (cx <= 8 && cy >= size - 9);
      if (onFinder) continue;
      for (let y = -2; y <= 2; y++) {
        for (let x = -2; x <= 2; x++) {
          set(cx + x, cy + y, Math.max(Math.abs(x), Math.abs(y)) !== 1);
        }
      }
    }
  }

  // The dark module, and the format-information areas (filled after masking).
  set(8, size - 8, true);
  for (let i = 0; i < 9; i++) {
    if (i !== 6) { reserved[at(i, 8)] = true; reserved[at(8, i)] = true; }
  }
  for (let i = 0; i < 8; i++) {
    reserved[at(size - 1 - i, 8)] = true;
    reserved[at(8, size - 1 - i)] = true;
  }

  // Version information, versions 7 and up.
  if (version >= 7) {
    const bits = versionBits(version);
    for (let i = 0; i < 18; i++) {
      const on = ((bits >> i) & 1) === 1;
      const a = Math.floor(i / 3);
      const b = (i % 3) + size - 11;
      set(a, b, on);
      set(b, a, on);
    }
  }

  // Data, in the standard's upward-then-downward two-column zigzag.
  let bitIndex = 0;
  let upward = true;
  for (let right = size - 1; right >= 1; right -= 2) {
    if (right === 6) right = 5; // the vertical timing column is skipped entirely
    for (let step = 0; step < size; step++) {
      const y = upward ? size - 1 - step : step;
      for (const x of [right, right - 1]) {
        if (reserved[at(x, y)]) continue;
        const byte = codewords[bitIndex >> 3];
        const on = byte !== undefined && ((byte >> (7 - (bitIndex & 7))) & 1) === 1;
        modules[at(x, y)] = on;
        bitIndex++;
      }
    }
    upward = !upward;
  }

  /*
   * Mask selection by penalty, as the standard requires.
   *
   * Skipping it and hard-coding a mask "works" on most payloads and then fails
   * on the one where the data happens to produce large uniform blocks — which
   * is a code that scans on the developer's phone and not on the customer's.
   */
  let best = { mask: 0, penalty: Infinity, modules };
  for (let mask = 0; mask < 8; mask++) {
    const candidate = modules.map((on, i) => {
      if (reserved[i]) return on;
      const x = i % size;
      const y = Math.floor(i / size);
      return on !== maskAt(mask, x, y);
    });
    applyFormat(candidate, size, ec, mask);
    const penalty = score(candidate, size);
    if (penalty < best.penalty) best = { mask, penalty, modules: candidate };
  }

  return { size, modules: best.modules, version, ecLevel: ec };
}

function maskAt(mask: number, x: number, y: number): boolean {
  switch (mask) {
    case 0: return (x + y) % 2 === 0;
    case 1: return y % 2 === 0;
    case 2: return x % 3 === 0;
    case 3: return (x + y) % 3 === 0;
    case 4: return (Math.floor(y / 2) + Math.floor(x / 3)) % 2 === 0;
    case 5: return ((x * y) % 2) + ((x * y) % 3) === 0;
    case 6: return (((x * y) % 2) + ((x * y) % 3)) % 2 === 0;
    default: return (((x + y) % 2) + ((x * y) % 3)) % 2 === 0;
  }
}

function applyFormat(modules: boolean[], size: number, ec: EcLevel, mask: number): void {
  const data = (EC_BITS[ec] << 3) | mask;
  let rem = data;
  for (let i = 0; i < 10; i++) rem = (rem << 1) ^ ((rem >> 9) * 0x537);
  const bits = ((data << 10) | rem) ^ 0x5412;

  const at = (x: number, y: number) => y * size + x;
  for (let i = 0; i < 15; i++) {
    const on = ((bits >> i) & 1) === 1;
    // Around the top-left finder.
    if (i < 6) modules[at(8, i)] = on;
    else if (i < 8) modules[at(8, i + 1)] = on;
    else if (i === 8) modules[at(7, 8)] = on;
    else modules[at(14 - i, 8)] = on;
    // And the copy split across the other two.
    if (i < 8) modules[at(size - 1 - i, 8)] = on;
    else modules[at(8, size - 15 + i)] = on;
  }
}

/** The four penalty rules from the standard. Lower is a more scannable code. */
function score(modules: boolean[], size: number): number {
  const at = (x: number, y: number) => modules[y * size + x]!;
  let penalty = 0;

  // Rule 1 — runs of five or more of the same colour.
  for (let i = 0; i < size; i++) {
    for (const row of [true, false]) {
      let run = 1;
      for (let j = 1; j < size; j++) {
        const a = row ? at(j, i) : at(i, j);
        const b = row ? at(j - 1, i) : at(i, j - 1);
        if (a === b) run++;
        else { if (run >= 5) penalty += run - 2; run = 1; }
      }
      if (run >= 5) penalty += run - 2;
    }
  }

  // Rule 2 — 2×2 blocks of one colour.
  for (let y = 0; y < size - 1; y++) {
    for (let x = 0; x < size - 1; x++) {
      const v = at(x, y);
      if (v === at(x + 1, y) && v === at(x, y + 1) && v === at(x + 1, y + 1)) penalty += 3;
    }
  }

  // Rule 3 — the finder-lookalike sequence, which confuses a decoder badly.
  const PATTERN = [true, false, true, true, true, false, true, false, false, false, false];
  const REVERSED = [...PATTERN].reverse();
  for (let i = 0; i < size; i++) {
    for (let j = 0; j + 11 <= size; j++) {
      for (const row of [true, false]) {
        const window = Array.from({ length: 11 }, (_, k) => (row ? at(j + k, i) : at(i, j + k)));
        if (window.every((v, k) => v === PATTERN[k]) || window.every((v, k) => v === REVERSED[k])) {
          penalty += 40;
        }
      }
    }
  }

  // Rule 4 — imbalance between dark and light.
  const dark = modules.reduce((n, v) => n + (v ? 1 : 0), 0);
  penalty += Math.floor(Math.abs((dark * 100) / modules.length - 50) / 5) * 10;

  return penalty;
}

function versionBits(version: number): number {
  let rem = version;
  for (let i = 0; i < 12; i++) rem = (rem << 1) ^ ((rem >> 11) * 0x1f25);
  return (version << 12) | rem;
}

/* ------------------------------------------------------------------ output */

export const moduleAt = (m: Matrix, x: number, y: number): boolean =>
  x >= 0 && y >= 0 && x < m.size && y < m.size && m.modules[y * m.size + x]!;
