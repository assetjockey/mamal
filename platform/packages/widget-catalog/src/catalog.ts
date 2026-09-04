import { z } from 'zod';
import { template, url, type WidgetDef } from './types.ts';

/**
 * All 44 widget types.
 *
 * The brief says "41" in prose and then enumerates 44 across its five category
 * lists. The lists win: a customer migrating from the source product cares
 * whether their widget exists, not what the total is called.
 *
 * Ordered by category so the type picker reads the way the product does. Every
 * entry is complete — settings schema and defaults — because the catalogue
 * shipping complete is what lets the renderers arrive family by family without
 * anything looking half-built.
 */

const def = (d: WidgetDef): WidgetDef => d;

/* ------------------------------------------------------------------- proof */

const proof: WidgetDef[] = [
  def({
    key: 'recent_conversion',
    category: 'proof',
    family: 'bubble',
    label: 'Recent conversion',
    description: 'Someone in Lisbon just signed up — the classic proof bubble.',
    needs: ['conversions'],
    settings: z.object({
      title: template('{{name}} in {{city}}'),
      body: template('just {{type}}'),
      /** Only show conversions of these types; empty means all. */
      conversionTypes: z.array(z.string()).default([]),
      /** Do not show at all below this many in the window — see the note. */
      minimumCount: z.number().int().min(0).default(3),
      windowHours: z.number().int().min(1).max(720).default(72),
      showAvatar: z.boolean().default(true),
      showTimeAgo: z.boolean().default(true),
    }),
    defaults: { minimumCount: 3, windowHours: 72, showAvatar: true, showTimeAgo: true },
  }),
  def({
    key: 'conversion_counter',
    category: 'proof',
    family: 'bubble',
    label: 'Conversion counter',
    description: '“38 people bought this week.”',
    needs: ['count'],
    settings: z.object({
      title: template('{{count}} people {{type}} this week'),
      windowHours: z.number().int().min(1).max(720).default(168),
      minimumCount: z.number().int().min(0).default(5),
    }),
    defaults: { windowHours: 168, minimumCount: 5 },
  }),
  def({
    key: 'live_visitors',
    category: 'proof',
    family: 'bubble',
    label: 'Live visitor counter',
    description: 'How many people are on the site right now.',
    needs: ['count'],
    settings: z.object({
      title: template('{{count}} people are viewing this page'),
      minimumCount: z.number().int().min(0).default(3),
      windowMinutes: z.number().int().min(1).max(60).default(5),
    }),
    defaults: { minimumCount: 3, windowMinutes: 5 },
  }),
  def({
    key: 'live_visitors_bar',
    category: 'proof',
    family: 'bar',
    label: 'Live counter bar',
    description: 'The live count, as a full-width strip.',
    needs: ['count'],
    settings: z.object({
      title: template('{{count}} people are shopping right now'),
      minimumCount: z.number().int().min(0).default(3),
      windowMinutes: z.number().int().min(1).max(60).default(5),
    }),
    defaults: { minimumCount: 3, windowMinutes: 5 },
  }),
  def({
    key: 'inline_conversions',
    category: 'proof',
    family: 'inline',
    label: 'Inline conversions',
    description: 'A conversion feed rendered into your own page element.',
    needs: ['conversions'],
    settings: z.object({
      selector: z.string().min(1).default('#mamal-proof'),
      title: template('{{name}} in {{city}} just {{type}}'),
      limit: z.number().int().min(1).max(20).default(5),
      windowHours: z.number().int().min(1).max(720).default(168),
    }),
    defaults: { selector: '#mamal-proof', limit: 5, windowHours: 168 },
  }),
  def({
    key: 'inline_counter',
    category: 'proof',
    family: 'inline',
    label: 'Inline counter',
    description: 'A single number, placed in your own markup.',
    needs: ['count'],
    settings: z.object({
      selector: z.string().min(1).default('#mamal-count'),
      title: template('{{count}}'),
      windowHours: z.number().int().min(1).max(720).default(168),
    }),
    defaults: { selector: '#mamal-count', windowHours: 168 },
  }),
  def({
    key: 'reviews',
    category: 'proof',
    family: 'card',
    label: 'Reviews',
    description: 'Rotating customer reviews with a rating.',
    needs: ['reviews'],
    settings: z.object({
      title: template('What people say'),
      rotateSeconds: z.number().int().min(3).max(60).default(8),
      showRating: z.boolean().default(true),
      minRating: z.number().min(1).max(5).default(4),
    }),
    defaults: { rotateSeconds: 8, showRating: true, minRating: 4 },
  }),
  def({
    key: 'rating_summary',
    category: 'proof',
    family: 'rating',
    label: 'Rating summary',
    description: 'Average score and review count.',
    needs: ['reviews'],
    settings: z.object({
      title: template('{{average}} out of 5 from {{count}} reviews'),
      showStars: z.boolean().default(true),
    }),
    defaults: { showStars: true },
  }),
  def({
    key: 'inline_reviews',
    category: 'proof',
    family: 'inline',
    label: 'Inline reviews',
    description: 'Reviews rendered into your own page element.',
    needs: ['reviews'],
    settings: z.object({
      selector: z.string().min(1).default('#mamal-reviews'),
      limit: z.number().int().min(1).max(20).default(3),
      minRating: z.number().min(1).max(5).default(4),
    }),
    defaults: { selector: '#mamal-reviews', limit: 3, minRating: 4 },
  }),
  def({
    key: 'inline_rating_summary',
    category: 'proof',
    family: 'inline',
    label: 'Inline rating summary',
    description: 'The average rating, in your own markup.',
    needs: ['reviews'],
    settings: z.object({
      selector: z.string().min(1).default('#mamal-rating'),
      showStars: z.boolean().default(true),
    }),
    defaults: { selector: '#mamal-rating', showStars: true },
  }),
  def({
    key: 'trust_badge',
    category: 'proof',
    family: 'bubble',
    label: 'Trust badge',
    description: 'A verified badge — pairs with Audit’s site health score.',
    needs: [],
    settings: z.object({
      title: template('Site health verified'),
      body: template('Checked {{date}}'),
      badgeUrl: url(),
      linkUrl: url(),
    }),
    defaults: {},
  }),
];

/* ---------------------------------------------------------------- announce */

const announce: WidgetDef[] = [
  def({
    key: 'informational',
    category: 'announce',
    family: 'card',
    label: 'Informational',
    description: 'A message with a heading and body.',
    needs: [],
    settings: z.object({
      title: template('Something worth knowing'),
      body: template('A sentence or two of detail.'),
      imageUrl: url(),
      linkUrl: url(),
      linkLabel: template('Learn more'),
    }),
    defaults: {},
  }),
  def({
    key: 'informational_mini',
    category: 'announce',
    family: 'bubble',
    label: 'Informational mini',
    description: 'The same message, in the small bubble form.',
    needs: [],
    settings: z.object({
      title: template('Heads up'),
      body: template('A short line.'),
      linkUrl: url(),
    }),
    defaults: {},
  }),
  def({
    key: 'informational_bar',
    category: 'announce',
    family: 'bar',
    label: 'Informational bar',
    description: 'A full-width announcement strip.',
    needs: [],
    settings: z.object({
      title: template('Free delivery on orders over £50'),
      linkUrl: url(),
      linkLabel: template('Shop now'),
      sticky: z.boolean().default(true),
    }),
    defaults: { sticky: true },
  }),
  def({
    key: 'informational_bar_mini',
    category: 'announce',
    family: 'bar',
    label: 'Informational bar mini',
    description: 'A slimmer strip, for secondary notices.',
    needs: [],
    settings: z.object({
      title: template('We use cookies for analytics only.'),
      linkUrl: url(),
    }),
    defaults: {},
  }),
  def({
    key: 'coupon',
    category: 'announce',
    family: 'card',
    label: 'Coupon',
    description: 'A discount code with copy-to-clipboard.',
    needs: [],
    settings: z.object({
      title: template('10% off your first order'),
      code: z.string().max(48).default('WELCOME10'),
      body: template('Use at checkout.'),
      linkUrl: url(),
      copyLabel: template('Copy code'),
    }),
    defaults: { code: 'WELCOME10' },
  }),
  def({
    key: 'coupon_bar',
    category: 'announce',
    family: 'bar',
    label: 'Coupon bar',
    description: 'The same offer, as a strip.',
    needs: [],
    settings: z.object({
      title: template('10% off with code {{code}}'),
      code: z.string().max(48).default('WELCOME10'),
      linkUrl: url(),
    }),
    defaults: { code: 'WELCOME10' },
  }),
  def({
    key: 'countdown',
    category: 'announce',
    family: 'bar',
    label: 'Countdown',
    description: 'A deadline, counting down.',
    needs: ['countdown'],
    settings: z.object({
      title: template('Sale ends in'),
      /** ISO instant, or a per-visitor duration in seconds. */
      endsAt: z.string().datetime().optional(),
      durationSeconds: z.number().int().min(60).optional(),
      onExpiry: z.enum(['hide', 'restart', 'message']).default('hide'),
      expiredMessage: template('This offer has ended.'),
    }),
    defaults: { onExpiry: 'hide' },
  }),
  def({
    key: 'image',
    category: 'announce',
    family: 'card',
    label: 'Image',
    description: 'A single image, optionally linked.',
    needs: ['media'],
    settings: z.object({
      imageUrl: url(),
      alt: z.string().max(180).default(''),
      linkUrl: url(),
    }),
    defaults: {},
  }),
  def({
    key: 'video',
    category: 'announce',
    family: 'card',
    label: 'Video',
    description: 'An embedded video.',
    needs: ['media'],
    settings: z.object({
      videoUrl: url(),
      autoplay: z.boolean().default(false),
      muted: z.boolean().default(true),
    }),
    defaults: { autoplay: false, muted: true },
  }),
  def({
    key: 'audio',
    category: 'announce',
    family: 'card',
    label: 'Audio',
    description: 'An audio clip with a play control.',
    needs: ['media'],
    settings: z.object({ audioUrl: url(), title: template('Listen') }),
    defaults: {},
  }),
  def({
    key: 'button_bar',
    category: 'announce',
    family: 'bar',
    label: 'Button bar',
    description: 'A strip whose whole point is one call to action.',
    needs: [],
    settings: z.object({
      title: template('Ready to start?'),
      linkLabel: template('Get started'),
      linkUrl: url(),
    }),
    defaults: {},
  }),
  def({
    key: 'button_modal',
    category: 'announce',
    family: 'modal',
    label: 'Button modal',
    description: 'A centred call to action over a backdrop.',
    needs: [],
    settings: z.object({
      title: template('One more thing'),
      body: template('A short pitch.'),
      linkLabel: template('Continue'),
      linkUrl: url(),
      dismissLabel: template('No thanks'),
    }),
    defaults: {},
  }),
];

/* ----------------------------------------------------------------- collect */

/** Every collector shares these; only the fields differ. */
const collectorBase = {
  title: template('Stay in touch'),
  body: template('We send one email a month.'),
  submitLabel: template('Subscribe'),
  successMessage: template('Thank you — check your inbox.'),
  consentText: template('I agree to receive emails.'),
  requireConsent: z.boolean().default(true),
};

const collect: WidgetDef[] = [
  def({
    key: 'email_collector',
    category: 'collect',
    family: 'form',
    label: 'Email collector',
    description: 'One field: an email address.',
    needs: ['form'],
    settings: z.object({ ...collectorBase }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'phone_collector',
    category: 'collect',
    family: 'form',
    label: 'Phone collector',
    description: 'One field: a phone number.',
    needs: ['form'],
    settings: z.object({ ...collectorBase, submitLabel: template('Send me a text') }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'contact_collector',
    category: 'collect',
    family: 'form',
    label: 'Contact collector',
    description: 'Name, email and an optional message.',
    needs: ['form'],
    settings: z.object({ ...collectorBase, includeMessage: z.boolean().default(true) }),
    defaults: { requireConsent: true, includeMessage: true },
  }),
  def({
    key: 'collector_bar',
    category: 'collect',
    family: 'bar',
    label: 'Collector bar',
    description: 'An inline signup field in a full-width strip.',
    needs: ['form'],
    settings: z.object({ ...collectorBase }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'collector_modal',
    category: 'collect',
    family: 'modal',
    label: 'Collector modal',
    description: 'A centred signup over a backdrop.',
    needs: ['form'],
    settings: z.object({ ...collectorBase, imageUrl: url() }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'two_step_collector_modal',
    category: 'collect',
    family: 'modal',
    label: 'Two-step collector',
    description: 'Ask for the click first, the address second — it converts better.',
    needs: ['form'],
    settings: z.object({
      ...collectorBase,
      stepOneTitle: template('Want 10% off?'),
      stepOneYes: template('Yes please'),
      stepOneNo: template('No thanks'),
    }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'request_collector',
    category: 'collect',
    family: 'form',
    label: 'Request collector',
    description: 'A callback or demo request.',
    needs: ['form'],
    settings: z.object({ ...collectorBase, submitLabel: template('Request a call') }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'countdown_collector',
    category: 'collect',
    family: 'form',
    label: 'Countdown collector',
    description: 'A signup form with a deadline on it.',
    needs: ['form', 'countdown'],
    settings: z.object({
      ...collectorBase,
      endsAt: z.string().datetime().optional(),
      durationSeconds: z.number().int().min(60).optional(),
    }),
    defaults: { requireConsent: true },
  }),
  def({
    key: 'lead_form',
    category: 'collect',
    family: 'form',
    label: 'Lead form',
    description: 'A configurable multi-field form.',
    needs: ['form'],
    settings: z.object({
      ...collectorBase,
      fields: z
        .array(
          z.object({
            name: z.string().min(1).max(40),
            label: z.string().min(1).max(80),
            type: z.enum(['text', 'email', 'tel', 'number', 'select', 'textarea']),
            required: z.boolean().default(false),
            options: z.array(z.string()).default([]),
          }),
        )
        .max(12)
        .default([]),
    }),
    defaults: { requireConsent: true, fields: [] },
  }),
];

/* ---------------------------------------------------------------- feedback */

const feedback: WidgetDef[] = [
  def({
    key: 'emoji_feedback',
    category: 'feedback',
    family: 'rating',
    label: 'Emoji feedback',
    description: 'How was this page? Five faces.',
    needs: ['form'],
    settings: z.object({
      title: template('How was this page?'),
      followUp: template('Thanks — anything else?'),
      askFollowUp: z.boolean().default(true),
    }),
    defaults: { askFollowUp: true },
  }),
  def({
    key: 'score_feedback',
    category: 'feedback',
    family: 'rating',
    label: 'Score feedback (NPS)',
    description: '0–10, with the standard follow-up.',
    needs: ['form'],
    settings: z.object({
      title: template('How likely are you to recommend us?'),
      lowLabel: template('Not likely'),
      highLabel: template('Very likely'),
      followUp: template('What is the main reason for your score?'),
    }),
    defaults: {},
  }),
  def({
    key: 'text_feedback',
    category: 'feedback',
    family: 'form',
    label: 'Text feedback',
    description: 'An open comment box.',
    needs: ['form'],
    settings: z.object({
      title: template('Tell us what you think'),
      placeholder: template('Your feedback…'),
      submitLabel: template('Send'),
      successMessage: template('Thank you.'),
    }),
    defaults: {},
  }),
  def({
    key: 'social_share',
    category: 'feedback',
    family: 'bubble',
    label: 'Social share',
    description: 'Share buttons for the current page.',
    needs: [],
    settings: z.object({
      title: template('Share this'),
      networks: z
        .array(z.enum(['x', 'facebook', 'linkedin', 'whatsapp', 'email', 'copy']))
        .default(['x', 'linkedin', 'copy']),
    }),
    defaults: { networks: ['x', 'linkedin', 'copy'] },
  }),
];

/* ------------------------------------------------------------------ engage */

const chat = (key: string, label: string, field: string, placeholder: string): WidgetDef =>
  def({
    key,
    category: 'engage',
    family: 'chat',
    label,
    description: `Opens a ${label} conversation.`,
    needs: [],
    settings: z.object({
      title: template(`Chat on ${label}`),
      [field]: z.string().max(120).default(placeholder),
      prefill: template('Hello — I have a question about'),
    }),
    defaults: {},
  });

const engage: WidgetDef[] = [
  def({
    key: 'engagement_links',
    category: 'engage',
    family: 'card',
    label: 'Engagement links',
    description: 'A short list of places to go next.',
    needs: [],
    settings: z.object({
      title: template('Popular right now'),
      links: z
        .array(z.object({ label: z.string().max(80), url: z.string().url() }))
        .max(8)
        .default([]),
    }),
    defaults: { links: [] },
  }),
  chat('whatsapp_chat', 'WhatsApp', 'phone', '+441234567890'),
  chat('telegram_chat', 'Telegram', 'username', 'yourhandle'),
  chat('messenger_chat', 'Messenger', 'pageId', 'yourpage'),
  def({
    key: 'help_center',
    category: 'engage',
    family: 'card',
    label: 'Help centre',
    description: 'Searchable links into your documentation.',
    needs: [],
    settings: z.object({
      title: template('How can we help?'),
      searchUrl: url(),
      articles: z
        .array(z.object({ label: z.string().max(120), url: z.string().url() }))
        .max(12)
        .default([]),
    }),
    defaults: { articles: [] },
  }),
  def({
    key: 'contact_us',
    category: 'engage',
    family: 'form',
    label: 'Contact us',
    description: 'A contact form that lands in the conversions inbox.',
    needs: ['form'],
    settings: z.object({
      title: template('Get in touch'),
      submitLabel: template('Send'),
      successMessage: template('Thanks — we will reply by email.'),
    }),
    defaults: {},
  }),
  def({
    key: 'cookie_notice',
    category: 'engage',
    family: 'bar',
    label: 'Cookie notice',
    description: 'Consent, with a genuine decline.',
    needs: [],
    settings: z.object({
      title: template('We use cookies to measure traffic.'),
      acceptLabel: template('Accept'),
      declineLabel: template('Decline'),
      policyUrl: url(),
      /**
       * Declining must be one click, same as accepting. A notice that buries
       * "decline" behind a settings pane is not consent, and in most of the
       * places this ships it is not lawful either.
       */
      showDecline: z.literal(true).default(true),
    }),
    defaults: { showDecline: true },
  }),
  def({
    key: 'custom_html',
    category: 'engage',
    family: 'card',
    label: 'Custom HTML',
    description: 'Your own markup, rendered inside the widget shell.',
    needs: [],
    settings: z.object({
      html: z.string().max(8000).default('<p>Hello</p>'),
    }),
    defaults: { html: '<p>Hello</p>' },
  }),
];

export const WIDGET_CATALOG: WidgetDef[] = [
  ...proof,
  ...announce,
  ...collect,
  ...feedback,
  ...engage,
];

const BY_KEY = new Map(WIDGET_CATALOG.map((w) => [w.key, w]));

export function widgetDef(key: string): WidgetDef | undefined {
  return BY_KEY.get(key);
}

export function widgetsIn(category: string): WidgetDef[] {
  return WIDGET_CATALOG.filter((w) => w.category === category);
}
