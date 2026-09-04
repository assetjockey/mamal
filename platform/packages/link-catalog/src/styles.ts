import { z } from 'zod';

/**
 * QR styling — body patterns, eye shapes, frames, and the schema that binds
 * them.
 *
 * These are names, not drawings. The rasteriser in `worker-media` owns the
 * geometry; what lives here is the closed set the editor offers and the
 * validator the API enforces, so a style saved by the UI and a style posted to
 * the API cannot diverge.
 *
 * One constraint runs through all of it: **a QR code has to scan**. Every
 * decorative option below reduces the margin the decoder has to work with, so
 * the schema carries an error-correction level and the editor raises it when
 * a logo is added rather than letting somebody print an unreadable code.
 */

export const BODY_PATTERNS = [
  'square', 'dot', 'circle', 'rounded', 'extra_rounded', 'classy', 'classy_rounded',
  'diamond', 'star', 'cross', 'heart', 'leaf', 'shield', 'hexagon', 'octagon',
  'vertical_bars', 'horizontal_bars', 'grid', 'mosaic', 'fluid', 'pointed',
  'edge_cut', 'sharp', 'japanese', 'pixel',
] as const;

export const INNER_EYE_SHAPES = [
  'square', 'dot', 'circle', 'rounded', 'extra_rounded', 'diamond', 'star',
  'leaf', 'leaf_flipped', 'cross', 'heart', 'shield', 'flower', 'gear',
  'hexagon', 'octagon', 'pointed', 'pointed_flipped', 'rounded_left',
  'rounded_right', 'edge_cut', 'plus',
] as const;

export const OUTER_EYE_SHAPES = [
  'square', 'circle', 'rounded', 'extra_rounded', 'leaf', 'leaf_flipped',
  'shield', 'diamond', 'edge_cut', 'pointed',
] as const;

export const FRAMES = [
  'none', 'bottom_label', 'top_label', 'both_labels', 'balloon', 'balloon_bottom',
  'ribbon', 'ribbon_bottom', 'rounded_box', 'square_box', 'ticket', 'badge',
  'circle_badge', 'arrow_down', 'arrow_up', 'phone', 'card', 'tag', 'banner',
  'scan_me',
] as const;

export const FRAME_FONTS = [
  'inter_tight', 'inter', 'roboto', 'open_sans', 'lato', 'montserrat',
  'poppins', 'raleway', 'nunito', 'oswald', 'merriweather', 'playfair',
  'source_serif', 'space_mono', 'courier', 'system',
] as const;

/**
 * Error correction, and what each level costs.
 *
 * L recovers 7% of the code, H recovers 30% — but H also makes the code denser
 * for the same payload, so it is not a free upgrade. The rule the editor
 * applies: a logo overlays the centre, so any code with a logo goes to Q or H.
 */
export const ERROR_CORRECTION = ['L', 'M', 'Q', 'H'] as const;

const hex = z
  .string()
  .regex(/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/, 'Needs a hex colour like #533afd');

const fill = z.discriminatedUnion('kind', [
  z.object({ kind: z.literal('solid'), color: hex }),
  z.object({
    kind: z.literal('gradient'),
    type: z.enum(['linear', 'radial']).default('linear'),
    rotation: z.number().min(0).max(360).default(0),
    from: hex,
    to: hex,
  }),
]);

export const qrStyleSchema = z.object({
  body: z.enum(BODY_PATTERNS).default('square'),
  innerEye: z.enum(INNER_EYE_SHAPES).default('square'),
  outerEye: z.enum(OUTER_EYE_SHAPES).default('square'),

  foreground: fill.default({ kind: 'solid', color: '#061b31' }),
  /**
   * Background is a colour or nothing.
   *
   * Transparent is offered because customers want it for print overlays, and
   * refused nowhere — but the preview renders it on a checkerboard so nobody
   * exports a code that vanishes onto a dark substrate.
   */
  background: z.union([hex, z.literal('transparent')]).default('#ffffff'),
  /** Eyes may differ from the body; unset means they follow the foreground. */
  eyeColor: hex.optional(),

  frame: z.enum(FRAMES).default('none'),
  frameText: z.string().max(48).default('SCAN ME'),
  frameFont: z.enum(FRAME_FONTS).default('inter_tight'),
  frameColor: hex.default('#061b31'),

  logoAssetId: z.string().uuid().optional(),
  /** Fraction of the code's width the logo covers. Above 0.3 nothing scans. */
  logoScale: z.number().min(0.1).max(0.3).default(0.2),
  logoBackground: z.boolean().default(true),

  errorCorrection: z.enum(ERROR_CORRECTION).default('M'),
  /** Quiet-zone modules. Below 2 the code fails against a busy background. */
  margin: z.number().int().min(0).max(10).default(4),
});

export type QrStyle = z.infer<typeof qrStyleSchema>;

export const EXPORT_FORMATS = ['png', 'svg', 'pdf', 'eps'] as const;
export type ExportFormat = (typeof EXPORT_FORMATS)[number];

/**
 * Style problems worth stopping a print run for.
 *
 * Warnings rather than errors on purpose — a customer who genuinely wants a
 * low-contrast code for a mock-up should get one. But they should be told, and
 * `scan_risk` is what the editor renders next to the download button.
 */
export function styleWarnings(style: QrStyle): string[] {
  const out: string[] = [];

  if (style.logoAssetId && (style.errorCorrection === 'L' || style.errorCorrection === 'M')) {
    out.push('A logo covers the centre of the code. Raise error correction to Q or H.');
  }
  if (style.logoScale > 0.25 && style.errorCorrection !== 'H') {
    out.push('A logo this large needs error correction H to stay readable.');
  }
  if (style.margin < 2) {
    out.push('A quiet zone below 2 modules fails against a busy background.');
  }
  if (style.background === 'transparent') {
    out.push('Transparent backgrounds scan only if what is behind them is light.');
  }
  const fg = style.foreground.kind === 'solid' ? style.foreground.color : style.foreground.from;
  if (style.background !== 'transparent' && contrast(fg, style.background) < 3) {
    out.push('Foreground and background are too close in tone to scan reliably.');
  }
  return out;
}

/* Contrast ratio, same maths as the design tokens' accessibility checks. */
function contrast(a: string, b: string): number {
  const la = luminance(a);
  const lb = luminance(b);
  const [hi, lo] = la > lb ? [la, lb] : [lb, la];
  return (hi + 0.05) / (lo + 0.05);
}

function luminance(hexColor: string): number {
  const h = hexColor.replace('#', '');
  const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h.slice(0, 6);
  const [r, g, b] = [0, 2, 4].map((i) => {
    const c = parseInt(full.slice(i, i + 2), 16) / 255;
    return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
  }) as [number, number, number];
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
