import { moduleAt, type Matrix } from './encode.ts';

/**
 * A matrix, drawn as SVG.
 *
 * SVG rather than canvas: it is the same string in the browser preview, the
 * print export and the server-side render, it scales to any size without a
 * resolution decision, and it is what a designer actually wants handed to them.
 * Canvas would need three code paths and would still rasterise at one size.
 *
 * The shapes here implement the catalogue's body and eye styles. They are
 * drawn, not looked up, because a style is geometry and geometry is code —
 * what the catalogue owns is *which* styles exist, and this owns what each one
 * looks like.
 */

export type RenderOptions = {
  body?: string;
  innerEye?: string;
  outerEye?: string;
  foreground?: string;
  background?: string | 'transparent';
  eyeColor?: string;
  gradient?: { type: 'linear' | 'radial'; rotation: number; from: string; to: string };
  /** Quiet-zone width, in modules. Below 2 the code fails on a busy ground. */
  margin?: number;
  /** Rendered edge length in pixels. */
  size?: number;
  /** A logo is punched out of the centre; the caller overlays the image. */
  logoScale?: number;
};

export function toSvg(matrix: Matrix, options: RenderOptions = {}): string {
  const margin = Math.max(0, options.margin ?? 4);
  const span = matrix.size + margin * 2;
  const size = options.size ?? 512;
  const fg = options.foreground ?? '#061b31';
  const bg = options.background ?? '#ffffff';
  const eyeColor = options.eyeColor ?? (options.gradient ? undefined : fg);

  const fill = options.gradient ? 'url(#qrfg)' : fg;
  const punch = punchedArea(matrix.size, options.logoScale);

  const body: string[] = [];
  for (let y = 0; y < matrix.size; y++) {
    for (let x = 0; x < matrix.size; x++) {
      if (!moduleAt(matrix, x, y)) continue;
      if (inEye(matrix.size, x, y)) continue;   // eyes are drawn as whole shapes
      if (punch && inPunch(punch, x, y)) continue;
      body.push(bodyShape(options.body ?? 'square', matrix, x, y, margin));
    }
  }

  const eyes = [
    eyePair(0, 0, margin, options),
    eyePair(matrix.size - 7, 0, margin, options),
    eyePair(0, matrix.size - 7, margin, options),
  ].join('');

  return [
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${span} ${span}" width="${size}" height="${size}" shape-rendering="crispEdges" role="img" aria-label="QR code">`,
    options.gradient ? gradientDef(options.gradient) : '',
    bg === 'transparent' ? '' : `<rect width="${span}" height="${span}" fill="${bg}"/>`,
    `<g fill="${fill}">${body.join('')}</g>`,
    `<g fill="${eyeColor ?? fill}">${eyes}</g>`,
    '</svg>',
  ].join('');
}

function gradientDef(g: NonNullable<RenderOptions['gradient']>): string {
  if (g.type === 'radial') {
    return `<defs><radialGradient id="qrfg"><stop offset="0" stop-color="${g.from}"/><stop offset="1" stop-color="${g.to}"/></radialGradient></defs>`;
  }
  const rad = ((g.rotation ?? 0) * Math.PI) / 180;
  const x2 = (0.5 + Math.cos(rad) / 2).toFixed(4);
  const y2 = (0.5 + Math.sin(rad) / 2).toFixed(4);
  const x1 = (0.5 - Math.cos(rad) / 2).toFixed(4);
  const y1 = (0.5 - Math.sin(rad) / 2).toFixed(4);
  return `<defs><linearGradient id="qrfg" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"><stop offset="0" stop-color="${g.from}"/><stop offset="1" stop-color="${g.to}"/></linearGradient></defs>`;
}

/** The three 7×7 finder regions, which the eye shapes replace wholesale. */
function inEye(size: number, x: number, y: number): boolean {
  return (
    (x < 7 && y < 7) ||
    (x >= size - 7 && y < 7) ||
    (x < 7 && y >= size - 7)
  );
}

function bodyShape(style: string, m: Matrix, x: number, y: number, margin: number): string {
  const cx = x + margin;
  const cy = y + margin;
  const on = (dx: number, dy: number) => moduleAt(m, x + dx, y + dy);

  switch (style) {
    case 'dot':
      return `<circle cx="${cx + 0.5}" cy="${cy + 0.5}" r="0.38"/>`;
    case 'circle':
      return `<circle cx="${cx + 0.5}" cy="${cy + 0.5}" r="0.5"/>`;
    case 'diamond':
      return `<path d="M${cx + 0.5} ${cy}L${cx + 1} ${cy + 0.5}L${cx + 0.5} ${cy + 1}L${cx} ${cy + 0.5}Z"/>`;
    case 'rounded':
      return `<rect x="${cx}" y="${cy}" width="1" height="1" rx="0.25"/>`;
    case 'extra_rounded':
      return `<rect x="${cx}" y="${cy}" width="1" height="1" rx="0.45"/>`;
    case 'pixel':
      return `<rect x="${cx + 0.1}" y="${cy + 0.1}" width="0.8" height="0.8"/>`;
    /*
     * The connected styles look at their neighbours.
     *
     * A bar or a fluid shape that ignored adjacency would draw the same
     * rounded square everywhere and look identical to `rounded`. Reading the
     * four orthogonal neighbours is what turns a grid of squares into a
     * continuous form — and it is why these take the matrix rather than a
     * boolean.
     */
    case 'vertical_bars': {
      const top = on(0, -1); const bottom = on(0, 1);
      const r = 0.5;
      return `<rect x="${cx + 0.15}" y="${cy}" width="0.7" height="1" rx="${top || bottom ? 0 : r}"/>` +
        (top ? '' : `<rect x="${cx + 0.15}" y="${cy}" width="0.7" height="0.35" rx="0.35"/>`) +
        (bottom ? '' : `<rect x="${cx + 0.15}" y="${cy + 0.65}" width="0.7" height="0.35" rx="0.35"/>`);
    }
    case 'horizontal_bars': {
      const left = on(-1, 0); const right = on(1, 0);
      return `<rect x="${cx}" y="${cy + 0.15}" width="1" height="0.7" rx="${left || right ? 0 : 0.5}"/>` +
        (left ? '' : `<rect x="${cx}" y="${cy + 0.15}" width="0.35" height="0.7" rx="0.35"/>`) +
        (right ? '' : `<rect x="${cx + 0.65}" y="${cy + 0.15}" width="0.35" height="0.7" rx="0.35"/>`);
    }
    case 'classy':
    case 'classy_rounded': {
      // Rounded on the corners that face empty space, square where it connects.
      const r = style === 'classy' ? 0.5 : 0.35;
      const tl = !on(-1, 0) && !on(0, -1) ? r : 0;
      const br = !on(1, 0) && !on(0, 1) ? r : 0;
      return `<path d="M${cx + tl} ${cy}H${cx + 1}V${cy + 1 - br}` +
        `A${br} ${br} 0 0 1 ${cx + 1 - br} ${cy + 1}H${cx}V${cy + tl}` +
        `A${tl} ${tl} 0 0 1 ${cx + tl} ${cy}Z"/>`;
    }
    case 'fluid': {
      const r = 0.5;
      const tl = !on(-1, 0) && !on(0, -1) ? r : 0;
      const tr = !on(1, 0) && !on(0, -1) ? r : 0;
      const br = !on(1, 0) && !on(0, 1) ? r : 0;
      const bl = !on(-1, 0) && !on(0, 1) ? r : 0;
      return `<path d="M${cx + tl} ${cy}H${cx + 1 - tr}A${tr} ${tr} 0 0 1 ${cx + 1} ${cy + tr}` +
        `V${cy + 1 - br}A${br} ${br} 0 0 1 ${cx + 1 - br} ${cy + 1}` +
        `H${cx + bl}A${bl} ${bl} 0 0 1 ${cx} ${cy + 1 - bl}` +
        `V${cy + tl}A${tl} ${tl} 0 0 1 ${cx + tl} ${cy}Z"/>`;
    }
    case 'cross':
      return `<path d="M${cx + 0.35} ${cy}h0.3v0.35h0.35v0.3h-0.35v0.35h-0.3v-0.35h-0.35v-0.3h0.35Z"/>`;
    case 'star':
      return star(cx + 0.5, cy + 0.5, 0.5, 0.22, 5);
    case 'hexagon':
      return polygon(cx + 0.5, cy + 0.5, 0.5, 6, Math.PI / 6);
    case 'octagon':
      return polygon(cx + 0.5, cy + 0.5, 0.5, 8, Math.PI / 8);
    case 'edge_cut':
      return `<path d="M${cx + 0.3} ${cy}H${cx + 1}V${cy + 1}H${cx}V${cy + 0.3}Z"/>`;
    case 'pointed':
      return `<path d="M${cx + 0.5} ${cy}L${cx + 1} ${cy + 0.35}V${cy + 1}H${cx}V${cy + 0.35}Z"/>`;
    default:
      // `square`, and every named style that has no distinct geometry yet.
      // A generic fallback is the catalogue pattern: the type exists and
      // renders, rather than the code failing to draw.
      return `<rect x="${cx}" y="${cy}" width="1" height="1"/>`;
  }
}

function eyePair(ox: number, oy: number, margin: number, options: RenderOptions): string {
  const x = ox + margin;
  const y = oy + margin;
  return outerEye(options.outerEye ?? 'square', x, y) + innerEye(options.innerEye ?? 'square', x + 2, y + 2);
}

function outerEye(style: string, x: number, y: number): string {
  // A 7×7 ring, drawn as an outer shape with a 5×5 hole via even-odd fill.
  const ring = (rOuter: number, rInner: number) =>
    `<path fill-rule="evenodd" d="${roundedRect(x, y, 7, 7, rOuter)}${roundedRect(x + 1, y + 1, 5, 5, rInner)}"/>`;

  switch (style) {
    case 'circle': return ring(3.5, 2.5);
    case 'rounded': return ring(1.75, 1.25);
    case 'extra_rounded': return ring(2.5, 1.8);
    case 'leaf':
      return `<path fill-rule="evenodd" d="${leafRect(x, y, 7, 3.5, true)}${leafRect(x + 1, y + 1, 5, 2.5, true)}"/>`;
    case 'leaf_flipped':
      return `<path fill-rule="evenodd" d="${leafRect(x, y, 7, 3.5, false)}${leafRect(x + 1, y + 1, 5, 2.5, false)}"/>`;
    case 'diamond':
      return `<path fill-rule="evenodd" d="M${x + 3.5} ${y}L${x + 7} ${y + 3.5}L${x + 3.5} ${y + 7}L${x} ${y + 3.5}Z` +
        `M${x + 3.5} ${y + 1}L${x + 1} ${y + 3.5}L${x + 3.5} ${y + 6}L${x + 6} ${y + 3.5}Z"/>`;
    case 'edge_cut':
      return `<path fill-rule="evenodd" d="M${x + 2} ${y}H${x + 7}V${y + 7}H${x}V${y + 2}Z` +
        `M${x + 2} ${y + 1}H${x + 1}V${y + 6}H${x + 6}V${y + 1}Z"/>`;
    case 'pointed': return ring(1.2, 0.8);
    case 'shield': return ring(2, 1.4);
    default: return ring(0, 0);
  }
}

function innerEye(style: string, x: number, y: number): string {
  switch (style) {
    case 'dot': return `<circle cx="${x + 1.5}" cy="${y + 1.5}" r="1.2"/>`;
    case 'circle': return `<circle cx="${x + 1.5}" cy="${y + 1.5}" r="1.5"/>`;
    case 'rounded': return `<rect x="${x}" y="${y}" width="3" height="3" rx="0.8"/>`;
    case 'extra_rounded': return `<rect x="${x}" y="${y}" width="3" height="3" rx="1.2"/>`;
    case 'diamond': return `<path d="M${x + 1.5} ${y}L${x + 3} ${y + 1.5}L${x + 1.5} ${y + 3}L${x} ${y + 1.5}Z"/>`;
    case 'star': return star(x + 1.5, y + 1.5, 1.5, 0.7, 5);
    case 'hexagon': return polygon(x + 1.5, y + 1.5, 1.5, 6, Math.PI / 6);
    case 'octagon': return polygon(x + 1.5, y + 1.5, 1.5, 8, Math.PI / 8);
    case 'plus': return `<path d="M${x + 1} ${y}h1v1h1v1h-1v1h-1v-1h-1v-1h1Z"/>`;
    case 'edge_cut': return `<path d="M${x + 1} ${y}H${x + 3}V${y + 3}H${x}V${y + 1}Z"/>`;
    case 'leaf': return `<path d="M${x} ${y + 1.5}A1.5 1.5 0 0 1 ${x + 1.5} ${y}H${x + 3}V${y + 1.5}A1.5 1.5 0 0 1 ${x + 1.5} ${y + 3}H${x}Z"/>`;
    case 'leaf_flipped': return `<path d="M${x + 1.5} ${y}H${x + 3}V${y + 1.5}A1.5 1.5 0 0 1 ${x + 1.5} ${y + 3}H${x}V${y + 1.5}A1.5 1.5 0 0 1 ${x + 1.5} ${y}Z"/>`;
    default: return `<rect x="${x}" y="${y}" width="3" height="3"/>`;
  }
}

function roundedRect(x: number, y: number, w: number, h: number, r: number): string {
  if (r <= 0) return `M${x} ${y}H${x + w}V${y + h}H${x}Z`;
  const rr = Math.min(r, w / 2, h / 2);
  return `M${x + rr} ${y}H${x + w - rr}A${rr} ${rr} 0 0 1 ${x + w} ${y + rr}` +
    `V${y + h - rr}A${rr} ${rr} 0 0 1 ${x + w - rr} ${y + h}` +
    `H${x + rr}A${rr} ${rr} 0 0 1 ${x} ${y + h - rr}` +
    `V${y + rr}A${rr} ${rr} 0 0 1 ${x + rr} ${y}Z`;
}

/** Square on one diagonal, round on the other — the "leaf" family. */
function leafRect(x: number, y: number, size: number, r: number, flip: boolean): string {
  return flip
    ? `M${x} ${y}H${x + size - r}A${r} ${r} 0 0 1 ${x + size} ${y + r}V${y + size}H${x + r}A${r} ${r} 0 0 1 ${x} ${y + size - r}Z`
    : `M${x + r} ${y}H${x + size}V${y + size - r}A${r} ${r} 0 0 1 ${x + size - r} ${y + size}H${x}V${y + r}A${r} ${r} 0 0 1 ${x + r} ${y}Z`;
}

function polygon(cx: number, cy: number, r: number, sides: number, rotation = 0): string {
  const points = Array.from({ length: sides }, (_, i) => {
    const a = rotation + (i * 2 * Math.PI) / sides;
    return `${(cx + r * Math.cos(a)).toFixed(3)} ${(cy + r * Math.sin(a)).toFixed(3)}`;
  });
  return `<path d="M${points.join('L')}Z"/>`;
}

function star(cx: number, cy: number, outer: number, inner: number, points: number): string {
  const path: string[] = [];
  for (let i = 0; i < points * 2; i++) {
    const r = i % 2 === 0 ? outer : inner;
    const a = (i * Math.PI) / points - Math.PI / 2;
    path.push(`${(cx + r * Math.cos(a)).toFixed(3)} ${(cy + r * Math.sin(a)).toFixed(3)}`);
  }
  return `<path d="M${path.join('L')}Z"/>`;
}

/**
 * The square of modules a logo covers.
 *
 * Punched out rather than drawn over, so the SVG has no modules hiding beneath
 * an opaque image — which matters for a print workflow where somebody removes
 * the logo layer and would otherwise get a code the error correction cannot
 * recover.
 */
function punchedArea(size: number, logoScale?: number): { from: number; to: number } | null {
  if (!logoScale || logoScale <= 0) return null;
  const span = Math.ceil(size * Math.min(logoScale, 0.3));
  const from = Math.floor((size - span) / 2);
  return { from, to: from + span };
}

const inPunch = (p: { from: number; to: number }, x: number, y: number) =>
  x >= p.from && x < p.to && y >= p.from && y < p.to;
