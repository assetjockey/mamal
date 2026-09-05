/**
 * Ad platforms, their fields, and the shapes creative has to fit.
 *
 * `magicads` got this right and it is kept close to wholesale: thirty platforms
 * each with *typed fields*, each field with its own limit and its own count.
 * The taxonomy below — frameworks, tones, objectives — is theirs too, because
 * it is a genuinely good product decision and reinventing it would be worse.
 *
 * The reason this is data rather than a prompt instruction: a model told "keep
 * headlines under 30 characters" produces a 32-character headline often enough
 * to matter, and the rejection arrives at upload time from Google rather than
 * here. So generated copy is *measured* against these limits and the ones that
 * do not fit are marked, not silently shipped.
 *
 * Field counts matter as much as lengths. Google's responsive search ads want
 * up to fifteen headlines and at least three; supplying one is technically
 * valid and performs badly enough that the platform warns about it.
 */

export type FieldSpec = {
  key: string;
  label: string;
  /** Characters, counted as code points. */
  maxLength: number;
  /** How many of this field the format takes. */
  min: number;
  max: number;
  /** Advice rather than a rule — shown, never enforced. */
  guidance?: string;
};

export type AdPlatform = {
  key: string;
  label: string;
  /** search | social | display | video | retail | audio */
  family: 'search' | 'social' | 'display' | 'video' | 'retail' | 'audio';
  fields: FieldSpec[];
  /** Objectives this platform actually offers. */
  objectives: string[];
};

const CTA: FieldSpec = {
  key: 'cta', label: 'Call to action', maxLength: 30, min: 0, max: 1,
};

/**
 * Thirty platforms.
 *
 * Limits are the published ones at the time of writing and they move, which is
 * exactly why they are one table rather than conditionals in a prompt template.
 */
export const AD_PLATFORMS: Record<string, AdPlatform> = {
  google_search: {
    key: 'google_search', label: 'Google Search', family: 'search',
    fields: [
      {
        key: 'headline', label: 'Headline', maxLength: 30, min: 3, max: 15,
        guidance: 'Three is the floor and fifteen gives the auction room to work.',
      },
      { key: 'description', label: 'Description', maxLength: 90, min: 2, max: 4 },
      { key: 'path', label: 'Display path', maxLength: 15, min: 0, max: 2 },
    ],
    objectives: ['sales', 'leads', 'traffic', 'app_installs'],
  },
  google_pmax: {
    key: 'google_pmax', label: 'Performance Max', family: 'search',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 30, min: 3, max: 15 },
      { key: 'long_headline', label: 'Long headline', maxLength: 90, min: 1, max: 5 },
      { key: 'description', label: 'Description', maxLength: 90, min: 2, max: 5 },
      { key: 'business_name', label: 'Business name', maxLength: 25, min: 1, max: 1 },
    ],
    objectives: ['sales', 'leads', 'traffic'],
  },
  google_display: {
    key: 'google_display', label: 'Google Display', family: 'display',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 30, min: 1, max: 5 },
      { key: 'long_headline', label: 'Long headline', maxLength: 90, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 90, min: 1, max: 5 },
      { key: 'business_name', label: 'Business name', maxLength: 25, min: 1, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'sales'],
  },
  google_demand_gen: {
    key: 'google_demand_gen', label: 'Demand Gen', family: 'display',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 40, min: 1, max: 5 },
      { key: 'description', label: 'Description', maxLength: 90, min: 1, max: 5 },
      CTA,
    ],
    objectives: ['awareness', 'consideration', 'sales'],
  },
  youtube: {
    key: 'youtube', label: 'YouTube', family: 'video',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 90, min: 1, max: 1 },
      { key: 'long_headline', label: 'Long headline', maxLength: 90, min: 0, max: 1 },
      { key: 'description', label: 'Description', maxLength: 70, min: 1, max: 2 },
      CTA,
    ],
    objectives: ['awareness', 'consideration', 'sales', 'app_installs'],
  },
  meta_feed: {
    key: 'meta_feed', label: 'Facebook & Instagram feed', family: 'social',
    fields: [
      {
        key: 'primary_text', label: 'Primary text', maxLength: 500, min: 1, max: 5,
        guidance: 'Truncated around 125 on mobile — put the point first.',
      },
      { key: 'headline', label: 'Headline', maxLength: 40, min: 1, max: 5 },
      { key: 'description', label: 'Description', maxLength: 30, min: 0, max: 5 },
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'leads', 'app_installs', 'sales'],
  },
  meta_stories: {
    key: 'meta_stories', label: 'Facebook & Instagram stories', family: 'social',
    fields: [
      { key: 'primary_text', label: 'Primary text', maxLength: 125, min: 1, max: 1 },
      { key: 'headline', label: 'Headline', maxLength: 40, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'sales'],
  },
  meta_reels: {
    key: 'meta_reels', label: 'Reels', family: 'video',
    fields: [
      { key: 'primary_text', label: 'Primary text', maxLength: 125, min: 1, max: 1 },
      { key: 'headline', label: 'Headline', maxLength: 40, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'engagement', 'sales'],
  },
  instagram_explore: {
    key: 'instagram_explore', label: 'Instagram Explore', family: 'social',
    fields: [
      { key: 'primary_text', label: 'Primary text', maxLength: 125, min: 1, max: 1 },
      { key: 'headline', label: 'Headline', maxLength: 40, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'sales'],
  },
  linkedin_sponsored: {
    key: 'linkedin_sponsored', label: 'LinkedIn sponsored content', family: 'social',
    fields: [
      {
        key: 'intro', label: 'Introductory text', maxLength: 600, min: 1, max: 1,
        guidance: 'Truncated around 150 — the first line is the ad.',
      },
      { key: 'headline', label: 'Headline', maxLength: 200, min: 1, max: 1 },
      CTA,
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'leads'],
  },
  linkedin_message: {
    key: 'linkedin_message', label: 'LinkedIn message ads', family: 'social',
    fields: [
      { key: 'subject', label: 'Subject', maxLength: 60, min: 1, max: 1 },
      { key: 'body', label: 'Message', maxLength: 1500, min: 1, max: 1 },
      CTA,
    ],
    objectives: ['leads', 'traffic'],
  },
  linkedin_text: {
    key: 'linkedin_text', label: 'LinkedIn text ads', family: 'search',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 25, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 75, min: 1, max: 1 },
    ],
    objectives: ['traffic', 'leads'],
  },
  tiktok: {
    key: 'tiktok', label: 'TikTok', family: 'video',
    fields: [
      {
        key: 'text', label: 'Ad text', maxLength: 100, min: 1, max: 1,
        guidance: 'Between 12 and 100; under 12 and TikTok will not accept it.',
      },
      CTA,
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'leads', 'app_installs', 'sales'],
  },
  x_ads: {
    key: 'x_ads', label: 'X', family: 'social',
    fields: [
      { key: 'text', label: 'Post text', maxLength: 280, min: 1, max: 1 },
      { key: 'headline', label: 'Card headline', maxLength: 70, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'app_installs'],
  },
  pinterest: {
    key: 'pinterest', label: 'Pinterest', family: 'social',
    fields: [
      { key: 'title', label: 'Title', maxLength: 100, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 500, min: 1, max: 1 },
    ],
    objectives: ['awareness', 'consideration', 'traffic', 'sales'],
  },
  snapchat: {
    key: 'snapchat', label: 'Snapchat', family: 'video',
    fields: [
      { key: 'brand_name', label: 'Brand name', maxLength: 25, min: 1, max: 1 },
      { key: 'headline', label: 'Headline', maxLength: 34, min: 1, max: 1 },
      CTA,
    ],
    objectives: ['awareness', 'traffic', 'app_installs', 'sales'],
  },
  reddit: {
    key: 'reddit', label: 'Reddit', family: 'social',
    fields: [
      { key: 'title', label: 'Title', maxLength: 300, min: 1, max: 1 },
      CTA,
    ],
    objectives: ['awareness', 'traffic', 'engagement', 'sales'],
  },
  microsoft_search: {
    key: 'microsoft_search', label: 'Microsoft Advertising', family: 'search',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 30, min: 3, max: 15 },
      { key: 'description', label: 'Description', maxLength: 90, min: 2, max: 4 },
      { key: 'path', label: 'Display path', maxLength: 15, min: 0, max: 2 },
    ],
    objectives: ['sales', 'leads', 'traffic'],
  },
  amazon_sponsored: {
    key: 'amazon_sponsored', label: 'Amazon sponsored brands', family: 'retail',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 50, min: 1, max: 1 },
    ],
    objectives: ['sales', 'awareness'],
  },
  amazon_dsp: {
    key: 'amazon_dsp', label: 'Amazon DSP', family: 'display',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 50, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 90, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'consideration', 'sales'],
  },
  spotify: {
    key: 'spotify', label: 'Spotify', family: 'audio',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 25, min: 1, max: 1 },
      {
        key: 'script', label: 'Audio script', maxLength: 300, min: 1, max: 1,
        guidance: 'Around 50 words reads as 30 seconds.',
      },
      CTA,
    ],
    objectives: ['awareness', 'consideration'],
  },
  quora: {
    key: 'quora', label: 'Quora', family: 'social',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 65, min: 1, max: 1 },
      { key: 'body', label: 'Body', maxLength: 105, min: 1, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'leads'],
  },
  taboola: {
    key: 'taboola', label: 'Taboola', family: 'display',
    fields: [
      { key: 'title', label: 'Title', maxLength: 65, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 150, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'sales'],
  },
  outbrain: {
    key: 'outbrain', label: 'Outbrain', family: 'display',
    fields: [
      { key: 'title', label: 'Title', maxLength: 105, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 150, min: 0, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'sales'],
  },
  criteo: {
    key: 'criteo', label: 'Criteo', family: 'retail',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 40, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 90, min: 0, max: 1 },
    ],
    objectives: ['sales', 'consideration'],
  },
  apple_search: {
    key: 'apple_search', label: 'Apple Search Ads', family: 'search',
    fields: [
      { key: 'headline', label: 'Headline', maxLength: 30, min: 1, max: 1 },
      { key: 'description', label: 'Description', maxLength: 45, min: 0, max: 1 },
    ],
    objectives: ['app_installs'],
  },
  bing_shopping: {
    key: 'bing_shopping', label: 'Microsoft Shopping', family: 'retail',
    fields: [
      { key: 'promotion', label: 'Promotional text', maxLength: 45, min: 0, max: 1 },
    ],
    objectives: ['sales'],
  },
  google_shopping: {
    key: 'google_shopping', label: 'Google Shopping', family: 'retail',
    fields: [
      { key: 'title', label: 'Product title', maxLength: 150, min: 1, max: 1 },
      { key: 'description', label: 'Product description', maxLength: 5000, min: 1, max: 1 },
    ],
    objectives: ['sales'],
  },
  threads_ads: {
    key: 'threads_ads', label: 'Threads', family: 'social',
    fields: [
      { key: 'text', label: 'Post text', maxLength: 500, min: 1, max: 1 },
    ],
    objectives: ['awareness', 'traffic', 'engagement'],
  },
  email: {
    key: 'email', label: 'Email', family: 'display',
    fields: [
      {
        key: 'subject', label: 'Subject line', maxLength: 60, min: 1, max: 5,
        guidance: 'Around 40 characters survives a phone’s inbox list.',
      },
      { key: 'preheader', label: 'Preheader', maxLength: 100, min: 0, max: 1 },
      { key: 'body', label: 'Body', maxLength: 2000, min: 1, max: 1 },
    ],
    objectives: ['engagement', 'sales', 'leads'],
  },
};

export function platformFor(key: string): AdPlatform | null {
  return AD_PLATFORMS[key] ?? null;
}

/* ------------------------------------------------------------- taxonomy */

/**
 * Copy frameworks.
 *
 * Kept from `magicads` because they are the difference between "write an ad"
 * and a brief the model can actually follow. The `shape` is what goes into the
 * prompt; the label is what the customer picks.
 */
export const FRAMEWORKS: Record<string, { label: string; shape: string }> = {
  aida: {
    label: 'AIDA',
    shape: 'Attention, then interest, then desire, then a single action.',
  },
  pas: {
    label: 'PAS',
    shape: 'Name the problem, agitate what it costs, then present the solution.',
  },
  pastor: {
    label: 'PASTOR',
    shape: 'Problem, amplify, story, testimony, offer, response.',
  },
  bab: {
    label: 'Before–After–Bridge',
    shape: 'Where they are now, where they could be, and what gets them there.',
  },
  four_us: {
    label: '4 Us',
    shape: 'Useful, urgent, unique and ultra-specific — in that order of priority.',
  },
  fab: {
    label: 'FAB',
    shape: 'Feature, then the advantage it creates, then the benefit they feel.',
  },
  storytelling: {
    label: 'Storytelling',
    shape: 'One person, one moment, one change. No abstractions.',
  },
  question_hook: {
    label: 'Question hook',
    shape: 'Open on a question the reader has already asked themselves.',
  },
  direct: {
    label: 'Direct',
    shape: 'The offer, the proof, the action. No preamble.',
  },
  soap: {
    label: 'SOAP',
    shape: 'Situation, obstacle, action, product.',
  },
  four_steps: {
    label: 'Four steps',
    shape: 'Promise, picture, proof, push.',
  },
};

export const TONES = [
  'professional', 'conversational', 'playful', 'urgent', 'authoritative',
  'empathetic', 'witty', 'plain', 'luxurious', 'technical', 'inspirational',
  'reassuring', 'bold',
] as const;

export const OBJECTIVES = [
  'awareness', 'consideration', 'traffic', 'engagement', 'leads',
  'app_installs', 'sales', 'retention', 'signups', 'downloads', 'bookings',
] as const;

/* ------------------------------------------------------------- canvases */

export type Preset = {
  key: string;
  label: string;
  width: number;
  height: number;
  platforms: string[];
};

/** The ratio, reduced — what a designer actually thinks in. */
export function aspectRatio(width: number, height: number): string {
  const divisor = gcd(width, height);
  return `${width / divisor}:${height / divisor}`;
}

function gcd(a: number, b: number): number {
  return b === 0 ? a : gcd(b, a % b);
}

/**
 * Canvas presets.
 *
 * Grouped by shape rather than by platform, because the same 1080×1920 serves
 * Stories, Reels, TikTok and Shorts — listing it four times would imply four
 * different renders of the same image.
 */
export const PRESETS: Preset[] = [
  { key: 'square', label: 'Square', width: 1080, height: 1080,
    platforms: ['meta_feed', 'instagram_explore', 'linkedin_sponsored', 'x_ads', 'pinterest'] },
  { key: 'portrait_4_5', label: 'Portrait', width: 1080, height: 1350,
    platforms: ['meta_feed', 'instagram_explore', 'linkedin_sponsored'] },
  { key: 'vertical_9_16', label: 'Full screen vertical', width: 1080, height: 1920,
    platforms: ['meta_stories', 'meta_reels', 'tiktok', 'snapchat', 'youtube'] },
  { key: 'landscape_16_9', label: 'Landscape', width: 1920, height: 1080,
    platforms: ['youtube', 'google_display', 'x_ads', 'meta_feed'] },
  { key: 'pin_2_3', label: 'Pin', width: 1000, height: 1500, platforms: ['pinterest'] },
  { key: 'pin_tall', label: 'Tall pin', width: 1000, height: 2100, platforms: ['pinterest'] },
  { key: 'leaderboard', label: 'Leaderboard', width: 728, height: 90,
    platforms: ['google_display', 'amazon_dsp'] },
  { key: 'medium_rectangle', label: 'Medium rectangle', width: 300, height: 250,
    platforms: ['google_display', 'amazon_dsp', 'criteo'] },
  { key: 'large_rectangle', label: 'Large rectangle', width: 336, height: 280,
    platforms: ['google_display'] },
  { key: 'wide_skyscraper', label: 'Wide skyscraper', width: 160, height: 600,
    platforms: ['google_display'] },
  { key: 'half_page', label: 'Half page', width: 300, height: 600,
    platforms: ['google_display', 'amazon_dsp'] },
  { key: 'mobile_banner', label: 'Mobile banner', width: 320, height: 50,
    platforms: ['google_display', 'apple_search'] },
  { key: 'large_mobile_banner', label: 'Large mobile banner', width: 320, height: 100,
    platforms: ['google_display'] },
  { key: 'billboard', label: 'Billboard', width: 970, height: 250,
    platforms: ['google_display'] },
  { key: 'email_hero', label: 'Email hero', width: 600, height: 400, platforms: ['email'] },
  { key: 'product_square', label: 'Product shot', width: 1200, height: 1200,
    platforms: ['google_shopping', 'amazon_sponsored', 'criteo'] },
];

export function presetsFor(platform: string): Preset[] {
  return PRESETS.filter((p) => p.platforms.includes(platform));
}

/* ----------------------------------------------------------- validation */

export type CopyProblem = {
  level: 'error' | 'warning';
  field: string;
  index: number | null;
  message: string;
};

/**
 * Measures generated copy against the platform's own fields.
 *
 * Run on what the model produced, not on the instruction given to it. A model
 * told "under 30 characters" writes 32 often enough to matter, and the
 * rejection would otherwise arrive from Google at upload time — by which point
 * the customer has already paid for the generation.
 *
 * Over-length is an error. Too *few* of a field is a warning: three Google
 * headlines instead of fifteen is a valid ad that will underperform, and
 * refusing to save it would be us overruling the customer about their own work.
 */
export function validateCopy(
  platform: AdPlatform,
  values: Record<string, string[]>,
): CopyProblem[] {
  const problems: CopyProblem[] = [];

  for (const field of platform.fields) {
    const supplied = (values[field.key] ?? []).filter((v) => v.trim().length > 0);

    if (supplied.length < field.min) {
      problems.push({
        level: supplied.length === 0 ? 'error' : 'warning',
        field: field.key,
        index: null,
        message:
          supplied.length === 0
            ? `${platform.label} needs at least ${field.min} ${field.label.toLowerCase()}.`
            : `${supplied.length} of a recommended ${field.min} ${field.label.toLowerCase()}.` +
              (field.guidance ? ` ${field.guidance}` : ''),
      });
    }

    if (supplied.length > field.max) {
      problems.push({
        level: 'error',
        field: field.key,
        index: null,
        message: `${platform.label} takes at most ${field.max} ${field.label.toLowerCase()}; there are ${supplied.length}.`,
      });
    }

    supplied.forEach((value, index) => {
      // Code points, for the same reason as the social composer: an emoji is
      // two UTF-16 units and one character to the platform counting it.
      const length = [...value].length;
      if (length > field.maxLength) {
        problems.push({
          level: 'error',
          field: field.key,
          index,
          message: `${field.label} ${index + 1} is ${length - field.maxLength} over ${field.maxLength}.`,
        });
      }
    });
  }

  const unknown = Object.keys(values).filter(
    (key) => !platform.fields.some((f) => f.key === key),
  );
  for (const key of unknown) {
    problems.push({
      level: 'warning',
      field: key,
      index: null,
      // Usually a model inventing a field. Worth saying, not worth refusing.
      message: `${platform.label} has no “${key}” field — it will be ignored.`,
    });
  }

  return problems;
}

export function copyIsUsable(problems: CopyProblem[]): boolean {
  return !problems.some((p) => p.level === 'error');
}
