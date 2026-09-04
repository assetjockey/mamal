import { describe, expect, it } from 'vitest';
import { encode, moduleAt, QrTooLong, type EcLevel, type Matrix } from '../encode.ts';
import { toSvg } from '../svg.ts';

/**
 * The read-back below is a **second, independent implementation** of module
 * placement, written from the standard rather than from `encode.ts`.
 *
 * That is deliberate. Structural assertions ("there is a finder pattern in the
 * corner") pass on codes no scanner can read; the only test that means anything
 * is decoding the symbol back to the string that went in. Sharing the placement
 * code with the encoder would make the test agree with the implementation
 * rather than with the specification.
 */

/* --------------------------------------------------------------- read back */

const ALIGNMENT: number[][] = [
  [], [], [6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],
  [6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],
  [6,34,62,90],[6,28,50,72,94],[6,26,50,74,98],[6,30,54,78,102],[6,28,54,80,106],[6,32,58,84,110],
  [6,30,58,86,114],[6,34,62,90,118],[6,26,50,74,98,122],[6,30,54,78,102,126],[6,26,52,78,104,130],
  [6,30,56,82,108,134],[6,34,60,86,112,138],[6,30,58,86,114,142],[6,34,62,90,118,146],
  [6,30,54,78,102,126,150],[6,24,50,76,102,128,154],[6,28,54,80,106,132,158],
  [6,32,58,84,110,136,162],[6,26,54,82,110,138,166],[6,30,58,86,114,142,170],
];

/** Every module that carries a function pattern rather than data. */
function functionMap(size: number, version: number): boolean[] {
  const reserved = new Array<boolean>(size * size).fill(false);
  const mark = (x: number, y: number) => {
    if (x >= 0 && y >= 0 && x < size && y < size) reserved[y * size + x] = true;
  };

  for (const [ox, oy] of [[0, 0], [size - 7, 0], [0, size - 7]] as [number, number][]) {
    for (let y = -1; y <= 7; y++) for (let x = -1; x <= 7; x++) mark(ox + x, oy + y);
  }
  for (let i = 0; i < size; i++) { mark(i, 6); mark(6, i); }

  const centres = ALIGNMENT[version]!;
  for (const cy of centres) {
    for (const cx of centres) {
      const onFinder = (cx <= 8 && cy <= 8) || (cx >= size - 9 && cy <= 8) || (cx <= 8 && cy >= size - 9);
      if (onFinder) continue;
      for (let y = -2; y <= 2; y++) for (let x = -2; x <= 2; x++) mark(cx + x, cy + y);
    }
  }

  for (let i = 0; i < 9; i++) { mark(i, 8); mark(8, i); }
  for (let i = 0; i < 8; i++) { mark(size - 1 - i, 8); mark(8, size - 1 - i); }

  if (version >= 7) {
    for (let i = 0; i < 18; i++) {
      const a = Math.floor(i / 3);
      const b = (i % 3) + size - 11;
      mark(a, b); mark(b, a);
    }
  }
  return reserved;
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

/** Recovers the format information written around the top-left finder. */
function readFormat(m: Matrix): { ecLevel: EcLevel; mask: number } {
  let raw = 0;
  for (let i = 0; i < 15; i++) {
    const on =
      i < 6 ? moduleAt(m, 8, i)
      : i < 8 ? moduleAt(m, 8, i + 1)
      : i === 8 ? moduleAt(m, 7, 8)
      : moduleAt(m, 14 - i, 8);
    if (on) raw |= 1 << i;
  }
  const bits = (raw ^ 0x5412) >> 10;
  const levels: Record<number, EcLevel> = { 0b01: 'L', 0b00: 'M', 0b11: 'Q', 0b10: 'H' };
  return { ecLevel: levels[bits >> 3]!, mask: bits & 0b111 };
}

/** The interleaved codeword stream, read out of the symbol. */
function readCodewords(m: Matrix): number[] {
  const { mask } = readFormat(m);
  const reserved = functionMap(m.size, m.version);
  const bits: number[] = [];

  let upward = true;
  for (let right = m.size - 1; right >= 1; right -= 2) {
    if (right === 6) right = 5;
    for (let step = 0; step < m.size; step++) {
      const y = upward ? m.size - 1 - step : step;
      for (const x of [right, right - 1]) {
        if (reserved[y * m.size + x]) continue;
        bits.push((moduleAt(m, x, y) !== maskAt(mask, x, y)) ? 1 : 0);
      }
    }
    upward = !upward;
  }

  const out: number[] = [];
  for (let i = 0; i + 8 <= bits.length; i += 8) {
    out.push(bits.slice(i, i + 8).reduce((n, b) => (n << 1) | b, 0));
  }
  return out;
}

const EC_BLOCKS: Record<EcLevel, number[][]> = {
  L: [[], [7,1,0],[10,1,0],[15,1,0],[20,1,0],[26,1,0],[18,2,0],[20,2,0],[24,2,0],[30,2,0],[18,2,2],
      [20,4,0],[24,2,2],[26,4,0],[30,3,1],[22,5,1],[24,5,1],[28,1,5],[30,5,1],[28,3,4],[28,3,5]],
  M: [[], [10,1,0],[16,1,0],[26,1,0],[18,2,0],[24,2,0],[16,4,0],[18,4,0],[22,2,2],[22,3,2],[26,4,1],
      [30,1,4],[22,6,2],[22,8,1],[24,4,5],[24,5,5],[28,7,3],[28,10,1],[26,9,4],[26,3,11],[26,3,13]],
  Q: [[], [13,1,0],[22,1,0],[18,2,0],[26,2,0],[18,2,2],[24,4,0],[18,2,4],[22,4,2],[20,4,4],[24,6,2],
      [28,4,4],[26,4,6],[24,8,4],[20,11,5],[30,5,7],[24,15,2],[28,1,15],[28,17,1],[26,17,4],[30,15,5]],
  H: [[], [17,1,0],[28,1,0],[22,2,0],[16,4,0],[22,2,2],[28,4,0],[26,4,1],[26,4,2],[24,4,4],[28,6,2],
      [24,3,8],[28,7,4],[22,12,4],[24,11,5],[24,11,7],[30,3,13],[28,2,17],[28,2,19],[26,9,16],[28,15,10]],
};
const TOTAL = [0,26,44,70,100,134,172,196,242,292,346,404,466,532,581,655,733,815,901,991,1085];

/** Reverses the interleave and returns the data codewords in order. */
function deinterleave(stream: number[], version: number, ec: EcLevel): number[] {
  const [ecPer, g1, g2] = EC_BLOCKS[ec][version] as [number, number, number];
  const blocks = g1 + g2;
  const dataTotal = TOTAL[version]! - ecPer * blocks;
  const g1Size = Math.floor(dataTotal / blocks);

  const sizes = Array.from({ length: blocks }, (_, i) => (i < g1 ? g1Size : g1Size + 1));
  const out: number[][] = sizes.map(() => []);
  let cursor = 0;
  for (let i = 0; i < Math.max(...sizes); i++) {
    for (let b = 0; b < blocks; b++) {
      if (i < sizes[b]!) out[b]!.push(stream[cursor++]!);
    }
  }
  return out.flat();
}

/** Mode, length header and payload, as a decoder would read them. */
function readPayload(m: Matrix): string {
  const data = deinterleave(readCodewords(m), m.version, m.ecLevel);
  const bits = data.flatMap((b) => [7, 6, 5, 4, 3, 2, 1, 0].map((s) => (b >> s) & 1));
  const take = (n: number) => bits.splice(0, n).reduce((acc, b) => (acc << 1) | b, 0);

  expect(take(4), 'mode indicator must be byte mode').toBe(0b0100);
  const length = take(m.version < 10 ? 8 : 16);
  const bytes = Array.from({ length }, () => take(8));
  return new TextDecoder().decode(Uint8Array.from(bytes));
}

/* -------------------------------------------------- Reed–Solomon soundness */

const EXP = new Uint8Array(512);
const LOG = new Uint8Array(256);
(() => {
  let x = 1;
  for (let i = 0; i < 255; i++) { EXP[i] = x; LOG[x] = i; x <<= 1; if (x & 0x100) x ^= 0x11d; }
  for (let i = 255; i < 512; i++) EXP[i] = EXP[i - 255]!;
})();
const mul = (a: number, b: number) => (a === 0 || b === 0 ? 0 : EXP[LOG[a]! + LOG[b]!]!);

/**
 * Every codeword block, read as a polynomial, must be exactly divisible by the
 * generator polynomial. That is the *definition* of a valid Reed–Solomon
 * codeword, so this checks the encoder against the maths rather than against a
 * table of expected bytes copied from the same source.
 */
function blocksAreValidCodewords(m: Matrix): boolean {
  const stream = readCodewords(m);
  const [ecPer, g1, g2] = EC_BLOCKS[m.ecLevel][m.version] as [number, number, number];
  const blocks = g1 + g2;
  const dataTotal = TOTAL[m.version]! - ecPer * blocks;
  const g1Size = Math.floor(dataTotal / blocks);
  const sizes = Array.from({ length: blocks }, (_, i) => (i < g1 ? g1Size : g1Size + 1));

  const dataBlocks: number[][] = sizes.map(() => []);
  let cursor = 0;
  for (let i = 0; i < Math.max(...sizes); i++) {
    for (let b = 0; b < blocks; b++) if (i < sizes[b]!) dataBlocks[b]!.push(stream[cursor++]!);
  }
  const ecBlocks: number[][] = Array.from({ length: blocks }, () => []);
  for (let i = 0; i < ecPer; i++) {
    for (let b = 0; b < blocks; b++) ecBlocks[b]!.push(stream[cursor++]!);
  }

  let gen = [1];
  for (let i = 0; i < ecPer; i++) {
    const next = new Array<number>(gen.length + 1).fill(0);
    for (let j = 0; j < gen.length; j++) { next[j] = next[j]! ^ mul(gen[j]!, EXP[i]!); next[j + 1] = next[j + 1]! ^ gen[j]!; }
    gen = next;
  }

  return dataBlocks.every((block, b) => {
    const full = [...block, ...ecBlocks[b]!];
    const rem = new Array<number>(ecPer).fill(0);
    for (const byte of full.slice(0, block.length)) {
      const factor = byte ^ rem[0]!;
      rem.shift(); rem.push(0);
      for (let i = 0; i < ecPer; i++) rem[i] = rem[i]! ^ mul(gen[i + 1]!, factor);
    }
    return rem.every((v, i) => v === ecBlocks[b]![i]);
  });
}

/* ------------------------------------------------------------------- tests */

const PAYLOADS = [
  'https://mml.to/promo',
  'WIFI:T:WPA;S:Cafe Mamal;P:hunter2;;',
  'BEGIN:VCARD\nVERSION:3.0\nFN:Ada Lovelace\nEMAIL:ada@example.com\nEND:VCARD',
  'https://example.com/a-rather-longer-path?utm_source=poster&utm_medium=print&utm_campaign=spring-2026',
  'Grüße aus München — ünïcödé 😀',
  'x',
];

describe('the QR encoder', () => {
  it('round-trips every payload at every error-correction level', () => {
    // The assertion that matters: what a decoder reads back is what went in.
    for (const text of PAYLOADS) {
      for (const ec of ['L', 'M', 'Q', 'H'] as EcLevel[]) {
        const m = encode(text, ec);
        expect(readPayload(m), `${ec}: ${text.slice(0, 24)}`).toBe(text);
      }
    }
  });

  it('writes the error-correction level and mask it actually used', () => {
    for (const ec of ['L', 'M', 'Q', 'H'] as EcLevel[]) {
      const m = encode('https://mml.to/x', ec);
      const format = readFormat(m);
      expect(format.ecLevel).toBe(ec);
      expect(format.mask).toBeGreaterThanOrEqual(0);
      expect(format.mask).toBeLessThan(8);
    }
  });

  it('produces genuine Reed–Solomon codewords', () => {
    // Divisibility by the generator polynomial is the definition of a valid
    // codeword — this checks the maths, not a copied byte table.
    for (const ec of ['L', 'M', 'Q', 'H'] as EcLevel[]) {
      expect(blocksAreValidCodewords(encode('https://mml.to/promo', ec)), ec).toBe(true);
    }
  });

  it('round-trips through the multi-block versions, where interleaving bites', () => {
    // Version 1 has one block and hides every interleaving bug. These do not.
    for (const length of [40, 120, 300, 600]) {
      const text = 'https://mml.to/' + 'a'.repeat(length);
      const m = encode(text, 'M');
      expect(m.version).toBeGreaterThan(1);
      expect(readPayload(m), `${length} chars → v${m.version}`).toBe(text);
    }
  });

  it('places the three finder patterns and both timing lines', () => {
    const m = encode('https://mml.to/x', 'M');
    for (const [ox, oy] of [[0, 0], [m.size - 7, 0], [0, m.size - 7]] as [number, number][]) {
      expect(moduleAt(m, ox, oy)).toBe(true);          // outer ring
      expect(moduleAt(m, ox + 1, oy + 1)).toBe(false); // the light gap
      expect(moduleAt(m, ox + 3, oy + 3)).toBe(true);  // solid centre
    }
    for (let i = 8; i < m.size - 8; i++) {
      expect(moduleAt(m, i, 6), `timing row ${i}`).toBe(i % 2 === 0);
      expect(moduleAt(m, 6, i), `timing column ${i}`).toBe(i % 2 === 0);
    }
    expect(moduleAt(m, 8, m.size - 8), 'the dark module').toBe(true);
  });

  it('sizes the symbol to the payload, and to the level', () => {
    expect(encode('x', 'L').size).toBe(21);                       // version 1
    // Higher correction means less room, so the same text needs a bigger symbol.
    const text = 'https://mml.to/' + 'a'.repeat(60);
    expect(encode(text, 'H').version).toBeGreaterThan(encode(text, 'L').version);
  });

  it('refuses a payload no symbol can hold, rather than truncating it', () => {
    // Silently dropping the tail would produce a code that scans and is wrong,
    // which is far worse than one that never gets printed.
    expect(() => encode('a'.repeat(3000), 'H')).toThrow(QrTooLong);
  });
});

describe('SVG output', () => {
  it('draws a well-formed, sized, labelled document', () => {
    const matrix = encode('https://mml.to/x', 'M');
    const svg = toSvg(matrix, { size: 256, margin: 4 });
    expect(svg.startsWith('<svg')).toBe(true);
    expect(svg.endsWith('</svg>')).toBe(true);
    expect(svg).toContain('role="img"');
    expect(svg).toContain('width="256"');
    // The viewBox is the module grid plus a quiet zone on each side — so the
    // caller picks the pixel size and the geometry never changes.
    expect(svg).toContain(`viewBox="0 0 ${matrix.size + 8} ${matrix.size + 8}"`);
    expect(toSvg(matrix, { margin: 0 })).toContain(`viewBox="0 0 ${matrix.size} ${matrix.size}"`);
  });

  it('renders every body style without falling over', () => {
    const m = encode('https://mml.to/x', 'M');
    for (const body of [
      'square', 'dot', 'circle', 'rounded', 'extra_rounded', 'classy', 'classy_rounded',
      'diamond', 'star', 'cross', 'hexagon', 'octagon', 'vertical_bars', 'horizontal_bars',
      'fluid', 'pointed', 'edge_cut', 'pixel', 'a_style_we_have_not_drawn_yet',
    ]) {
      const svg = toSvg(m, { body });
      expect(svg.length, body).toBeGreaterThan(200);
      expect(svg.includes('NaN'), `${body} produced NaN coordinates`).toBe(false);
    }
  });

  it('renders every eye style without NaN coordinates', () => {
    const m = encode('https://mml.to/x', 'M');
    for (const outerEye of ['square', 'circle', 'rounded', 'extra_rounded', 'leaf', 'leaf_flipped', 'shield', 'diamond', 'edge_cut', 'pointed']) {
      for (const innerEye of ['square', 'dot', 'circle', 'rounded', 'extra_rounded', 'diamond', 'star', 'hexagon', 'octagon', 'plus', 'edge_cut', 'leaf', 'leaf_flipped']) {
        const svg = toSvg(m, { outerEye, innerEye });
        expect(svg.includes('NaN'), `${outerEye}/${innerEye}`).toBe(false);
      }
    }
  });

  it('omits the background rect when it is transparent', () => {
    expect(toSvg(encode('x', 'M'), { background: 'transparent' })).not.toContain('<rect width=');
    expect(toSvg(encode('x', 'M'), { background: '#ffffff' })).toContain('<rect width=');
  });

  it('punches the logo area out rather than covering it', () => {
    // A print workflow that removes the logo layer must not be left with modules
    // underneath that the error correction was never told about.
    const m = encode('https://mml.to/x', 'M');
    const plain = toSvg(m, { logoScale: 0 });
    const punched = toSvg(m, { logoScale: 0.25 });
    expect(punched.length).toBeLessThan(plain.length);
  });
});
