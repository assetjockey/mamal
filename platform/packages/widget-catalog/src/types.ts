import { z } from 'zod';

/**
 * The widget catalogue.
 *
 * 41 types across five categories, merged from `66socialproof`'s set. The
 * decision that makes this tractable: **the catalogue is data and the renderers
 * are families**. Forty-one types share eight layouts, so a new type is a
 * catalogue entry plus a settings schema — not a new component, not a
 * migration, and not a column.
 *
 * A type declares which family draws it, which fields it takes, and what it
 * needs at runtime (a conversion feed, a live count, a form). The editor form,
 * the live preview and the browser runtime are all generated from that one
 * declaration, which is what keeps the three of them in agreement.
 */

export const WIDGET_CATEGORIES = ['proof', 'announce', 'collect', 'feedback', 'engage'] as const;
export type WidgetCategory = (typeof WIDGET_CATEGORIES)[number];

/**
 * The eight layout families.
 *
 * `bubble`   — small floating card, avatar/image left, two lines of text
 * `bar`      — full-width strip pinned top or bottom
 * `card`     — larger panel with a heading, body and optional media
 * `modal`    — centred overlay with a backdrop
 * `inline`   — rendered into a host element rather than floating
 * `form`     — any collector: fields, submit, success state
 * `rating`   — stars/emoji/score input or summary
 * `chat`     — a launcher button that opens a third-party chat
 */
export const WIDGET_FAMILIES = [
  'bubble', 'bar', 'card', 'modal', 'inline', 'form', 'rating', 'chat',
] as const;
export type WidgetFamily = (typeof WIDGET_FAMILIES)[number];

/**
 * What a widget needs from the payload at runtime.
 *
 * The runtime uses this to decide what to request and what to withhold: a
 * widget that needs no conversion feed must not be shipped one, because that
 * feed is other customers' data and every byte of it crosses to the browser.
 */
export const WIDGET_NEEDS = [
  'conversions', // a rolling window of recent conversions
  'count',       // an aggregate number (visitors, signups)
  'reviews',     // review content
  'form',        // collects and posts back
  'countdown',   // a deadline
  'media',       // an image, video or audio URL
] as const;
export type WidgetNeed = (typeof WIDGET_NEEDS)[number];

export type WidgetDef = {
  key: string;
  category: WidgetCategory;
  family: WidgetFamily;
  label: string;
  description: string;
  needs: WidgetNeed[];
  /** Settings contract. Drives the editor form AND validates writes. */
  settings: z.ZodTypeAny;
  /** Sensible starting point, so a new widget renders before it is edited. */
  defaults: Record<string, unknown>;
};

/* ------------------------------------------------------------ field helpers */

/** Text that may contain `{{token}}` interpolations from conversion data. */
const template = (fallback: string) => z.string().max(280).default(fallback);
const url = () => z.string().url().or(z.literal('')).default('');
const colour = () => z.string().regex(/^#[0-9a-fA-F]{6}$/).optional();

export const commonSettings = z.object({
  /** Shown on every widget unless branding is removed by entitlement. */
  showBranding: z.boolean().default(true),
  closable: z.boolean().default(true),
  accentColor: colour(),
});

export { template, url, colour };
