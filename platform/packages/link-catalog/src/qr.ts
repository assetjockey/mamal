import { z } from 'zod';

/**
 * QR code types, and what each one encodes.
 *
 * The distinction that runs through this file is **static versus dynamic**, and
 * it is commercial rather than technical:
 *
 * - A **static** code encodes its payload directly into the modules. Wifi
 *   credentials, a phone number, a crypto address. Once printed it can never be
 *   changed, which is fine for those and fatal for anything else.
 * - A **dynamic** code encodes a short link we own. The destination can change
 *   after ten thousand posters are in the world, and every scan is counted.
 *
 * Types that *can* be either say so. A type that must be static says that too,
 * so the editor can stop somebody putting a campaign URL on packaging in a form
 * they can never edit.
 */

export const QR_CATEGORIES = ['web', 'business', 'contact', 'message', 'payment', 'utility'] as const;
export type QrCategory = (typeof QR_CATEGORIES)[number];

export type QrDef = {
  key: string;
  category: QrCategory;
  label: string;
  description: string;
  /**
   * `dynamic` — always resolves through a short link
   * `static`  — always encodes its payload directly
   * `either`  — the customer chooses, and the editor explains the trade
   */
  addressing: 'dynamic' | 'static' | 'either';
  payload: z.ZodTypeAny;
  /** Builds the encoded string for a static code. */
  encode: (payload: Record<string, string>) => string;
};

const s = (max = 280) => z.string().max(max).default('');
const req = (max = 280) => z.string().min(1).max(max);

/** QR text escaping: `\`, `;`, `,` and `:` are structural in several formats. */
const esc = (v: string) => String(v ?? '').replace(/([\\;,:"])/g, '\\$1');

/** A dynamic type's payload is just the destination the short link points at. */
const destination = z.object({ url: z.string().url() });
const viaLink = () => '';

const def = (d: QrDef): QrDef => d;

export const QR_CATALOG: QrDef[] = [
  /* ------------------------------------------------------------------ web */
  def({
    key: 'dynamic_url', category: 'web', label: 'Website (dynamic)',
    description: 'Points at a short link, so the destination can change after printing.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'static_url', category: 'web', label: 'Website (static)',
    description: 'Encodes the URL directly. Cannot be changed once printed, and scans are not counted.',
    addressing: 'static', payload: z.object({ url: z.string().url() }),
    encode: (p) => p.url ?? '',
  }),
  def({
    key: 'bio_link', category: 'web', label: 'Bio page',
    description: 'Opens one of your bio pages.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'website_builder', category: 'web', label: 'Landing page',
    description: 'Opens a page you built here.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'app_download', category: 'web', label: 'App download',
    description: 'Sends iOS and Android to their own stores from one code.',
    addressing: 'dynamic',
    payload: z.object({ ios: s(), android: s(), fallback: z.string().url() }),
    encode: viaLink,
  }),
  def({
    key: 'file_upload', category: 'web', label: 'File',
    description: 'Downloads a file you host here.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'resume', category: 'web', label: 'Résumé',
    description: 'Opens a hosted résumé page.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),

  /* ------------------------------------------------------------- business */
  def({
    key: 'business_profile', category: 'business', label: 'Business profile',
    description: 'Hours, address, phone and links on one page.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'business_review', category: 'business', label: 'Review request',
    description: 'Asks for a review, then routes to the right platform.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'google_review', category: 'business', label: 'Google review',
    description: 'Opens the review dialog for a Google Business listing.',
    addressing: 'dynamic', payload: z.object({ placeId: req(120) }), encode: viaLink,
  }),
  def({
    key: 'restaurant_menu', category: 'business', label: 'Menu',
    description: 'A hosted menu — the destination changes when the menu does.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'product_catalogue', category: 'business', label: 'Product catalogue',
    description: 'A hosted catalogue page.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'event', category: 'business', label: 'Event',
    description: 'Adds an event to a calendar.',
    addressing: 'either',
    payload: z.object({
      title: req(160), start: z.string().datetime(), end: z.string().datetime().optional(),
      location: s(), notes: s(1000),
    }),
    // iCalendar, which every phone camera recognises.
    encode: (p) =>
      [
        'BEGIN:VEVENT',
        `SUMMARY:${esc(p.title!)}`,
        `DTSTART:${icalTime(p.start!)}`,
        p.end ? `DTEND:${icalTime(p.end)}` : '',
        p.location ? `LOCATION:${esc(p.location)}` : '',
        p.notes ? `DESCRIPTION:${esc(p.notes)}` : '',
        'END:VEVENT',
      ].filter(Boolean).join('\n'),
  }),
  def({
    key: 'booking', category: 'business', label: 'Booking',
    description: 'Opens a booking page.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),

  /* -------------------------------------------------------------- contact */
  def({
    key: 'vcard', category: 'contact', label: 'Contact card',
    description: 'Saves straight into the phone’s contacts, with no network needed.',
    addressing: 'static',
    payload: z.object({
      firstName: req(80), lastName: s(80), organisation: s(120), title: s(120),
      phone: s(40), email: s(160), website: s(),
    }),
    encode: (p) =>
      [
        'BEGIN:VCARD', 'VERSION:3.0',
        `N:${esc(p.lastName ?? '')};${esc(p.firstName!)};;;`,
        `FN:${esc([p.firstName, p.lastName].filter(Boolean).join(' '))}`,
        p.organisation ? `ORG:${esc(p.organisation)}` : '',
        p.title ? `TITLE:${esc(p.title)}` : '',
        p.phone ? `TEL:${esc(p.phone)}` : '',
        p.email ? `EMAIL:${esc(p.email)}` : '',
        p.website ? `URL:${p.website}` : '',
        'END:VCARD',
      ].filter(Boolean).join('\n'),
  }),
  def({
    key: 'vcard_plus', category: 'contact', label: 'Contact page',
    description: 'A hosted contact page — editable after printing, unlike a plain card.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'lead_form', category: 'contact', label: 'Lead form',
    description: 'Opens a form and captures into contacts.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),

  /* -------------------------------------------------------------- message */
  def({
    key: 'email', category: 'message', label: 'Email',
    description: 'Opens a pre-filled email.',
    addressing: 'either',
    payload: z.object({ to: req(160), subject: s(160), body: s(2000) }),
    encode: (p) => {
      const q = new URLSearchParams();
      if (p.subject) q.set('subject', p.subject);
      if (p.body) q.set('body', p.body);
      const query = q.toString();
      return `mailto:${p.to}${query ? `?${query}` : ''}`;
    },
  }),
  def({
    key: 'sms', category: 'message', label: 'SMS',
    description: 'Opens a pre-filled text message.',
    addressing: 'either',
    payload: z.object({ phone: req(40), message: s(1000) }),
    encode: (p) => `SMSTO:${p.phone}:${p.message ?? ''}`,
  }),
  def({
    key: 'call', category: 'message', label: 'Phone call',
    description: 'Dials a number.',
    addressing: 'static', payload: z.object({ phone: req(40) }),
    encode: (p) => `tel:${p.phone}`,
  }),
  def({
    key: 'whatsapp', category: 'message', label: 'WhatsApp',
    description: 'Opens a WhatsApp chat.',
    addressing: 'either',
    payload: z.object({ phone: req(40), message: s(1000) }),
    encode: (p) =>
      `https://wa.me/${(p.phone ?? '').replace(/\D/g, '')}${p.message ? `?text=${encodeURIComponent(p.message)}` : ''}`,
  }),
  def({
    key: 'facetime', category: 'message', label: 'FaceTime',
    description: 'Starts a FaceTime call. Apple devices only.',
    addressing: 'static', payload: z.object({ contact: req(160) }),
    encode: (p) => `facetime:${p.contact}`,
  }),
  def({
    key: 'telegram', category: 'message', label: 'Telegram',
    description: 'Opens a Telegram chat.',
    addressing: 'either', payload: z.object({ username: req(64) }),
    encode: (p) => `https://t.me/${(p.username ?? '').replace(/^@/, '')}`,
  }),
  def({
    key: 'messenger', category: 'message', label: 'Messenger',
    description: 'Opens a Messenger chat.',
    addressing: 'either', payload: z.object({ pageId: req(64) }),
    encode: (p) => `https://m.me/${p.pageId}`,
  }),
  def({
    key: 'viber', category: 'message', label: 'Viber',
    description: 'Opens a Viber chat.',
    addressing: 'either', payload: z.object({ phone: req(40) }),
    encode: (p) => `viber://chat?number=${encodeURIComponent(p.phone ?? '')}`,
  }),
  def({
    key: 'zoom', category: 'message', label: 'Zoom',
    description: 'Joins a Zoom meeting.',
    addressing: 'either', payload: z.object({ url: z.string().url() }),
    encode: (p) => p.url ?? '',
  }),

  /* -------------------------------------------------------------- payment */
  def({
    key: 'donation', category: 'payment', label: 'Donation',
    description: 'Opens a donation page.',
    addressing: 'dynamic', payload: destination, encode: viaLink,
  }),
  def({
    key: 'paypal', category: 'payment', label: 'PayPal',
    description: 'Opens a PayPal.me link.',
    addressing: 'either',
    payload: z.object({ handle: req(64), amount: s(16), currency: s(3) }),
    encode: (p) =>
      `https://paypal.me/${p.handle}${p.amount ? `/${p.amount}${p.currency ?? ''}` : ''}`,
  }),
  def({
    key: 'upi', category: 'payment', label: 'UPI',
    description: 'An Indian UPI payment request.',
    addressing: 'either',
    payload: z.object({ vpa: req(120), name: s(120), amount: s(16), note: s(120) }),
    encode: (p) => {
      const q = new URLSearchParams({ pa: p.vpa! });
      if (p.name) q.set('pn', p.name);
      if (p.amount) q.set('am', p.amount);
      if (p.note) q.set('tn', p.note);
      q.set('cu', 'INR');
      return `upi://pay?${q.toString()}`;
    },
  }),
  def({
    key: 'crypto', category: 'payment', label: 'Crypto',
    description: 'A wallet address, optionally with an amount.',
    addressing: 'static',
    payload: z.object({
      chain: z.enum(['bitcoin', 'ethereum', 'litecoin', 'dogecoin']).default('bitcoin'),
      address: req(120), amount: s(32),
    }),
    encode: (p) => `${p.chain}:${p.address}${p.amount ? `?amount=${p.amount}` : ''}`,
  }),
  def({
    key: 'pix', category: 'payment', label: 'PIX',
    description: 'A Brazilian PIX key.',
    addressing: 'static',
    payload: z.object({ key: req(120), name: s(120), city: s(80), amount: s(16) }),
    // The full EMV BR Code is assembled by the encoder service; the key alone
    // is what a wallet needs for a simple transfer.
    encode: (p) => p.key ?? '',
  }),
  def({
    key: 'epc', category: 'payment', label: 'SEPA transfer',
    description: 'A European EPC069-12 credit transfer.',
    addressing: 'static',
    payload: z.object({
      name: req(70), iban: req(34), amount: s(12), reference: s(140), bic: s(11),
    }),
    encode: (p) =>
      [
        'BCD', '002', '1', 'SCT', p.bic ?? '', p.name, p.iban,
        p.amount ? `EUR${p.amount}` : '', '', '', p.reference ?? '',
      ].join('\n'),
  }),

  /* -------------------------------------------------------------- utility */
  def({
    key: 'wifi', category: 'utility', label: 'Wi-Fi',
    description: 'Joins a network. Static by necessity — a phone reads it offline.',
    addressing: 'static',
    payload: z.object({
      ssid: req(64),
      password: s(64),
      encryption: z.enum(['WPA', 'WEP', 'nopass']).default('WPA'),
      hidden: z.boolean().default(false),
    }),
    encode: (p) =>
      `WIFI:T:${p.encryption || 'WPA'};S:${esc(p.ssid!)};` +
      `${p.password ? `P:${esc(p.password)};` : ''}${p.hidden === 'true' ? 'H:true;' : ''};`,
  }),
  def({
    key: 'location', category: 'utility', label: 'Location',
    description: 'Opens a map pin.',
    addressing: 'static',
    payload: z.object({ latitude: req(24), longitude: req(24), label: s(120) }),
    encode: (p) => `geo:${p.latitude},${p.longitude}`,
  }),
  def({
    key: 'text', category: 'utility', label: 'Plain text',
    description: 'Any text at all — shown as-is when scanned.',
    addressing: 'static', payload: z.object({ text: req(2000) }),
    encode: (p) => p.text ?? '',
  }),
];

function icalTime(iso: string): string {
  return new Date(iso).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
}

const BY_KEY = new Map(QR_CATALOG.map((q) => [q.key, q]));

export function qrDef(key: string): QrDef | undefined {
  return BY_KEY.get(key);
}

export function qrTypesIn(category: string): QrDef[] {
  return QR_CATALOG.filter((q) => q.category === category);
}

/**
 * The string a static code encodes.
 *
 * Returns null for a dynamic type: those resolve through a short link, and the
 * caller supplies the URL. Never throws — a half-filled payload in the editor
 * should render an incomplete preview, not an error, and this runs on every
 * keystroke.
 *
 * Values are flattened to strings first. A payload arrives from a JSON body or
 * a form, so `hidden` can be a boolean, the string `"true"`, or a checkbox's
 * `"on"`, and an encoder that compared against only one of those would drop a
 * field depending on which client sent it.
 */
export function encodePayload(key: string, payload: Record<string, unknown>): string | null {
  const def = qrDef(key);
  if (!def) return null;
  if (def.addressing === 'dynamic') return null;

  const flat: Record<string, string> = {};
  for (const [k, v] of Object.entries(payload)) {
    if (v === null || v === undefined) continue;
    flat[k] = v === true ? 'true' : v === false ? 'false' : String(v);
  }

  try {
    return def.encode(flat) || null;
  } catch {
    return null;
  }
}
