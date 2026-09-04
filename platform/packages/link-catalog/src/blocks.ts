import { z } from 'zod';

/**
 * All 84 biolink block types.
 *
 * Same principle as the widget catalogue: **the catalogue is data and the
 * renderers are families**. 84 types share 12 layouts, so a new block is a row
 * here plus a settings schema — not a component, not a migration, not a column.
 *
 * The brief says "82 blocks" in prose and then enumerates 84 across its five
 * category lists: 34 standard + 36 embeds + 4 files + 5 collect + 5 commerce.
 * The embed list is where they part company — `twitter tweet/video/profile` is
 * a slash-compressed triple, so what reads as one entry is three. The lists win,
 * for the same reason they win in the widget catalogue: a customer migrating
 * from the source product cares whether *their* block exists, not what the total
 * is called.
 */

export const BLOCK_CATEGORIES = ['standard', 'embed', 'file', 'collect', 'commerce'] as const;
export type BlockCategory = (typeof BLOCK_CATEGORIES)[number];

/**
 * Twelve render families.
 *
 * `link`     — a tappable row: label, optional icon, optional thumbnail
 * `text`     — headings, paragraphs, markdown, alerts
 * `media`    — an image, video or audio the page hosts
 * `embed`    — a third-party iframe or script, by provider
 * `list`     — repeated rows: FAQ, timeline, business hours, socials
 * `form`     — collects and posts back
 * `commerce` — a price and a pay action
 * `layout`   — dividers, spacers, avatars, headers
 * `card`     — a bounded panel with its own heading and body
 * `widget`   — self-updating: countdown, counter, weather
 * `file`     — a download with a type badge and a size
 * `custom`   — the customer's own HTML, sandboxed
 */
export const BLOCK_FAMILIES = [
  'link', 'text', 'media', 'embed', 'list', 'form',
  'commerce', 'layout', 'card', 'widget', 'file', 'custom',
] as const;
export type BlockFamily = (typeof BLOCK_FAMILIES)[number];

export type BlockDef = {
  key: string;
  category: BlockCategory;
  family: BlockFamily;
  label: string;
  /** The provider an `embed` family block wraps. */
  provider?: string;
  settings: z.ZodTypeAny;
  defaults: Record<string, unknown>;
};

/* ------------------------------------------------------------- shorthands */

const url = () => z.string().url().or(z.literal('')).default('');
const label = (d: string) => z.string().max(160).default(d);

/** Nearly every block has a heading and an optional link. */
const linkish = z.object({
  label: label('Link'),
  url: url(),
  description: z.string().max(280).default(''),
  iconUrl: url(),
  isHighlighted: z.boolean().default(false),
});

const textish = z.object({ text: z.string().max(4000).default('') });
const mediaish = z.object({ url: url(), alt: z.string().max(180).default('') });

/**
 * An embed is a provider plus the thing being embedded.
 *
 * One schema for all 34, because that is genuinely all they differ by — a
 * per-provider schema would be 34 copies of the same two fields, and each one a
 * place for a typo the renderer only discovers at display time.
 */
const embedish = z.object({
  url: url(),
  caption: z.string().max(280).default(''),
  aspectRatio: z.enum(['16:9', '4:3', '1:1', '9:16', 'auto']).default('16:9'),
});

const formish = z.object({
  title: label('Stay in touch'),
  buttonLabel: label('Subscribe'),
  successMessage: label('Thank you.'),
  requireConsent: z.boolean().default(true),
  consentText: label('I agree to be contacted.'),
});

const commerceish = z.object({
  title: label('Support my work'),
  description: z.string().max(280).default(''),
  amount: z.number().nonnegative().optional(),
  currency: z.string().length(3).default('USD'),
  buttonLabel: label('Pay'),
  url: url(),
});

/* ------------------------------------------------------------------ types */

type Row = [key: string, label: string, family: BlockFamily, schema?: z.ZodTypeAny, defaults?: Record<string, unknown>];

const build = (category: BlockCategory, rows: Row[], fallback: z.ZodTypeAny): BlockDef[] =>
  rows.map(([key, label, family, schema, defaults]) => ({
    key,
    category,
    family,
    label,
    settings: schema ?? fallback,
    defaults: defaults ?? {},
  }));

/* --------------------------------------------------------------- standard */

const STANDARD: Row[] = [
  ['link', 'Link', 'link'],
  ['big_link', 'Big link', 'link'],
  ['featured_link', 'Featured link', 'link'],
  ['external_item', 'External item', 'link'],
  ['heading', 'Heading', 'text', z.object({ text: z.string().max(160).default('Heading'), level: z.enum(['h2', 'h3', 'h4']).default('h2') })],
  ['header', 'Header', 'layout', z.object({ title: label(''), subtitle: z.string().max(280).default(''), imageUrl: url() })],
  ['paragraph', 'Paragraph', 'text', textish],
  ['markdown', 'Markdown', 'text', textish],
  ['divider', 'Divider', 'layout', z.object({ style: z.enum(['line', 'space', 'dots']).default('line') })],
  ['list', 'List', 'list', z.object({ items: z.array(z.object({ text: z.string().max(280) })).max(50).default([]) }), { items: [] }],
  ['alert', 'Alert', 'text', z.object({ text: z.string().max(600).default(''), tone: z.enum(['info', 'success', 'warning', 'error']).default('info') })],
  ['avatar', 'Avatar', 'layout', z.object({ imageUrl: url(), name: label(''), tagline: z.string().max(160).default('') })],
  ['image', 'Image', 'media', mediaish.extend({ linkUrl: url() })],
  ['image_grid', 'Image grid', 'media', z.object({ images: z.array(z.object({ url: z.string().url(), alt: z.string().max(180).default('') })).max(24).default([]), columns: z.number().int().min(2).max(4).default(3) }), { images: [] }],
  ['image_slider', 'Image slider', 'media', z.object({ images: z.array(z.object({ url: z.string().url(), alt: z.string().max(180).default('') })).max(24).default([]), autoplaySeconds: z.number().int().min(0).max(30).default(5) }), { images: [] }],
  ['image_comparison', 'Image comparison', 'media', z.object({ beforeUrl: url(), afterUrl: url(), beforeLabel: label('Before'), afterLabel: label('After') })],
  ['code', 'Code', 'text', z.object({ code: z.string().max(8000).default(''), language: z.string().max(24).default('text') })],
  ['custom_html', 'Custom HTML', 'custom', z.object({ html: z.string().max(20000).default('') })],
  ['iframe', 'Iframe', 'embed', embedish],
  ['share', 'Share', 'list', z.object({ networks: z.array(z.string()).max(10).default(['x', 'facebook', 'copy']) }), { networks: ['x', 'facebook', 'copy'] }],
  ['socials', 'Social icons', 'list', z.object({ links: z.array(z.object({ network: z.string().max(24), url: z.string().url() })).max(20).default([]) }), { links: [] }],
  ['business_hours', 'Business hours', 'list', z.object({ timezone: z.string().max(48).default('UTC'), days: z.array(z.object({ day: z.string().max(12), open: z.string().max(5), close: z.string().max(5) })).max(7).default([]) }), { days: [] }],
  ['map', 'Map', 'embed', z.object({ query: z.string().max(280).default(''), zoom: z.number().int().min(1).max(20).default(14) })],
  ['vcard', 'Contact card', 'card', z.object({ name: label(''), title: z.string().max(120).default(''), phone: z.string().max(40).default(''), email: z.string().max(160).default(''), website: url() })],
  ['faq', 'FAQ', 'list', z.object({ items: z.array(z.object({ question: z.string().max(280), answer: z.string().max(2000) })).max(50).default([]) }), { items: [] }],
  ['timeline', 'Timeline', 'list', z.object({ items: z.array(z.object({ date: z.string().max(40), title: z.string().max(160), body: z.string().max(600).default('') })).max(50).default([]) }), { items: [] }],
  ['counter', 'Counter', 'widget', z.object({ label: label('Followers'), value: z.number().default(0), suffix: z.string().max(12).default('') })],
  ['countdown', 'Countdown', 'widget', z.object({ label: label('Launching in'), endsAt: z.string().datetime().optional(), onExpiry: z.enum(['hide', 'message']).default('hide') })],
  ['cta', 'Call to action', 'link', linkish],
  ['coupon', 'Coupon', 'card', z.object({ code: z.string().max(48).default(''), title: label('Discount'), description: z.string().max(280).default('') })],
  ['loading', 'Loading', 'layout', z.object({ seconds: z.number().int().min(1).max(30).default(3) })],
  ['weather', 'Weather', 'widget', z.object({ location: z.string().max(120).default(''), units: z.enum(['metric', 'imperial']).default('metric') })],
  ['review', 'Review', 'card', z.object({ author: label(''), quote: z.string().max(1000).default(''), rating: z.number().min(1).max(5).default(5) })],
  ['modal_text', 'Modal text', 'card', z.object({ buttonLabel: label('Read more'), title: label(''), body: z.string().max(4000).default('') })],
];

/* ----------------------------------------------------------------- embeds */

const EMBED_PROVIDERS: [key: string, label: string][] = [
  ['youtube', 'YouTube'], ['youtube_feed', 'YouTube feed'], ['vimeo', 'Vimeo'],
  ['twitch', 'Twitch'], ['kick', 'Kick'], ['rumble', 'Rumble'],
  ['tiktok_video', 'TikTok video'], ['tiktok_profile', 'TikTok profile'],
  ['twitter_tweet', 'X post'], ['twitter_video', 'X video'], ['twitter_profile', 'X profile'],
  ['instagram_media', 'Instagram media'], ['threads', 'Threads'],
  ['snapchat', 'Snapchat'], ['bluesky_post', 'Bluesky post'], ['tumblr_post', 'Tumblr post'],
  ['vk_video', 'VK video'], ['pinterest_profile', 'Pinterest profile'],
  ['facebook', 'Facebook'], ['reddit', 'Reddit'], ['discord', 'Discord'],
  ['telegram', 'Telegram'], ['spotify', 'Spotify'], ['apple_music', 'Apple Music'],
  ['tidal', 'Tidal'], ['soundcloud', 'SoundCloud'], ['mixcloud', 'Mixcloud'],
  ['bandcamp', 'Bandcamp'], ['audio', 'Audio'], ['video', 'Video'],
  ['canva', 'Canva'], ['rss_feed', 'RSS feed'], ['google_form', 'Google Form'],
  ['typeform', 'Typeform'], ['calendly', 'Calendly'],
  ['appointment_calendar', 'Appointment calendar'],
];

/* ------------------------------------------------------------------ files */

const FILES: Row[] = [
  ['file', 'File', 'file'],
  ['pdf_document', 'PDF', 'file'],
  ['powerpoint', 'Presentation', 'file'],
  ['excel_spreadsheet', 'Spreadsheet', 'file'],
];

const fileish = z.object({
  label: label('Download'),
  url: url(),
  sizeBytes: z.number().int().nonnegative().optional(),
});

/* ---------------------------------------------------------------- collect */

const COLLECT: Row[] = [
  ['email_collector', 'Email collector', 'form'],
  ['phone_collector', 'Phone collector', 'form'],
  ['contact_collector', 'Contact collector', 'form'],
  ['lead_form', 'Lead form', 'form', formish.extend({
    fields: z.array(z.object({
      name: z.string().max(40),
      label: z.string().max(80),
      type: z.enum(['text', 'email', 'tel', 'number', 'select', 'textarea']),
      required: z.boolean().default(false),
    })).max(12).default([]),
  }), { requireConsent: true, fields: [] }],
  ['review_collector', 'Review collector', 'form'],
];

/* --------------------------------------------------------------- commerce */

const COMMERCE: Row[] = [
  ['paypal', 'PayPal', 'commerce'],
  ['donation', 'Donation', 'commerce'],
  ['product', 'Product', 'commerce'],
  ['service', 'Service', 'commerce'],
  ['digital_wallet', 'Wallet pass', 'commerce', z.object({
    title: label('Add to wallet'),
    platform: z.enum(['apple', 'google', 'both']).default('both'),
    passUrl: url(),
  })],
];

/* ------------------------------------------------------------------ build */

export const BLOCK_CATALOG: BlockDef[] = [
  ...build('standard', STANDARD, linkish),
  ...EMBED_PROVIDERS.map(([key, label]) => ({
    key,
    category: 'embed' as const,
    family: 'embed' as const,
    label,
    provider: key,
    settings: embedish,
    defaults: { aspectRatio: '16:9' as const },
  })),
  ...build('file', FILES, fileish),
  ...build('collect', COLLECT, formish),
  ...build('commerce', COMMERCE, commerceish),
];

const BY_KEY = new Map(BLOCK_CATALOG.map((b) => [b.key, b]));

export function blockDef(key: string): BlockDef | undefined {
  return BY_KEY.get(key);
}

export function blocksIn(category: string): BlockDef[] {
  return BLOCK_CATALOG.filter((b) => b.category === category);
}
