/**
 * AI visibility: whether the models name you, and who they name instead.
 *
 * The premise is that a growing share of buying research never reaches a search
 * results page. Somebody asks a model "what's the best widget for a small
 * team", and the answer either contains your brand or it does not — and if it
 * does, it either links you or it does not. Neither fact appears anywhere in
 * Search Console.
 *
 * **The measurement problem is the whole problem.** A model's answer is prose,
 * it changes between runs, and it will happily mention a competitor in passing.
 * So the parsing here is deliberately conservative: it looks for the brand as a
 * *word*, records where in the answer it first appears, and treats a citation
 * as a different and stronger signal than a mention. Anything cleverer would be
 * guessing, and a visibility number nobody trusts is worse than none.
 */

export type Brand = {
  /** How the brand is written. */
  name: string;
  /** Their domain, for matching citations. */
  domain?: string;
  /** Other spellings a model might use — "Acme Inc", "acme.io". */
  aliases?: string[];
  isSelf: boolean;
};

export type ModelAnswer = {
  model: string;
  text: string;
  /** URLs the model offered as sources, if it offered any. */
  citations?: { url: string; title?: string }[];
};

export type BrandReading = {
  brand: string;
  mentioned: boolean;
  /** 1-based order of first appearance among mentioned brands. Null if absent. */
  position: number | null;
  /** Character offset of the first mention, for ordering. */
  firstAt: number | null;
  /** How many times the brand appears. */
  mentions: number;
  /** True when the model linked them, not merely named them. */
  cited: boolean;
  citedUrls: string[];
};

export type AnswerReading = {
  model: string;
  brands: BrandReading[];
  /** Every source the model gave, whether or not it belongs to a tracked brand. */
  sources: { url: string; host: string; brand: string | null }[];
  /** Set when the tracked self-brand appears. */
  self: BrandReading | null;
};

/* ------------------------------------------------------------------ parsing */

/**
 * Finds a brand as a *word*, not as a substring.
 *
 * "Ace" must not match "surface", and a brand called "Go" must not match every
 * sentence containing the verb. Word boundaries around an escaped literal are
 * the whole trick — and the reason this is not a naive `includes`, which is how
 * most visibility tools produce numbers that are quietly nonsense.
 *
 * Boundaries are Unicode-aware: a brand ending in a letter should not match
 * inside a longer word in any script, and `\b` alone is ASCII-only.
 */
function mentionsOf(text: string, term: string): number[] {
  const needle = term.trim();
  if (needle.length < 2) return [];

  const escaped = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  // `(?<![\p{L}\p{N}])` rather than `\b`: "Café" and "Ω-corp" have boundaries
  // that ASCII `\b` gets wrong in both directions.
  const pattern = new RegExp(`(?<![\\p{L}\\p{N}])${escaped}(?![\\p{L}\\p{N}])`, 'giu');

  const out: number[] = [];
  for (const match of text.matchAll(pattern)) {
    if (match.index !== undefined) out.push(match.index);
  }
  return out;
}

/** `https://www.Example.com/x` and `example.com` are the same host. */
export function hostOf(url: string): string {
  try {
    return new URL(url).host.toLowerCase().replace(/^www\./, '');
  } catch {
    return '';
  }
}

/**
 * Reads one model's answer against a set of tracked brands.
 *
 * Position is assigned by *order of first appearance* rather than by
 * prominence or sentiment. It is a crude proxy and the code says so — but it is
 * a stable, explicable one, and a customer can check it by reading the answer.
 * A learned prominence score would be neither.
 */
export function readAnswer(answer: ModelAnswer, brands: Brand[]): AnswerReading {
  const citations = answer.citations ?? [];

  const readings: BrandReading[] = brands.map((brand) => {
    const terms = [brand.name, ...(brand.aliases ?? [])];
    const offsets = terms.flatMap((term) => mentionsOf(answer.text, term)).sort((a, b) => a - b);

    const citedUrls = brand.domain
      ? citations
          .map((c) => c.url)
          .filter((url) => {
            const host = hostOf(url);
            const domain = brand.domain!.toLowerCase().replace(/^www\./, '');
            // A subdomain counts — docs.acme.com is still Acme — but
            // `notacme.com` must not, so the boundary is a dot.
            return host === domain || host.endsWith(`.${domain}`);
          })
      : [];

    return {
      brand: brand.name,
      mentioned: offsets.length > 0,
      position: null,
      firstAt: offsets[0] ?? null,
      mentions: offsets.length,
      cited: citedUrls.length > 0,
      citedUrls,
    };
  });

  /*
   * Ranked among the brands that actually appear.
   *
   * A brand that is absent has no position — not "last". Assigning it one
   * would make an average position improve every time a competitor is dropped
   * from the tracked set, which is the wrong incentive and the wrong number.
   */
  const appearing = readings
    .filter((r) => r.firstAt !== null)
    .sort((a, b) => a.firstAt! - b.firstAt!);
  appearing.forEach((reading, index) => {
    reading.position = index + 1;
  });

  const byHost = new Map<string, string>();
  for (const brand of brands) {
    if (brand.domain) byHost.set(brand.domain.toLowerCase().replace(/^www\./, ''), brand.name);
  }

  const sources = citations.map((c) => {
    const host = hostOf(c.url);
    const owner =
      byHost.get(host) ??
      [...byHost.entries()].find(([domain]) => host.endsWith(`.${domain}`))?.[1] ??
      null;
    return { url: c.url, host, brand: owner };
  });

  const selfBrand = brands.find((b) => b.isSelf);
  return {
    model: answer.model,
    brands: readings,
    sources,
    self: selfBrand ? (readings.find((r) => r.brand === selfBrand.name) ?? null) : null,
  };
}

/* ---------------------------------------------------------------- scoring */

export type VisibilitySnapshot = {
  model: string;
  /** Share of all brand mentions across the prompt set, 0–1. */
  shareOfVoice: number;
  /** Fraction of prompts where the brand appeared at all, 0–1. */
  mentionRate: number;
  /** Mean position across the prompts where it appeared. Null if never. */
  avgPosition: number | null;
  /** How many prompts linked the brand, not merely named it. */
  citationCount: number;
  promptsRun: number;
};

/**
 * Rolls a model's readings into the four numbers the dashboard shows.
 *
 * Share of voice is computed over *mentions*, not over prompts: a model that
 * names a competitor three times and you once in the same answer has told you
 * something, and counting each answer as one vote would hide it.
 *
 * `mentionRate` is the honest headline, though — it answers "if somebody asks
 * this, will they hear about us", which is the question the product exists for.
 */
export function summarise(readings: AnswerReading[], brandName: string): VisibilitySnapshot {
  const model = readings[0]?.model ?? 'unknown';
  const promptsRun = readings.length;

  if (promptsRun === 0) {
    return { model, shareOfVoice: 0, mentionRate: 0, avgPosition: null, citationCount: 0, promptsRun: 0 };
  }

  let ourMentions = 0;
  let allMentions = 0;
  let appearedIn = 0;
  let citedIn = 0;
  const positions: number[] = [];

  for (const reading of readings) {
    for (const brand of reading.brands) {
      allMentions += brand.mentions;
      if (brand.brand !== brandName) continue;

      ourMentions += brand.mentions;
      if (brand.mentioned) appearedIn += 1;
      if (brand.cited) citedIn += 1;
      if (brand.position !== null) positions.push(brand.position);
    }
  }

  return {
    model,
    // Zero mentions anywhere is 0, not NaN — a model that named nobody is a
    // real outcome and it should not blank the chart.
    shareOfVoice: allMentions === 0 ? 0 : ourMentions / allMentions,
    mentionRate: appearedIn / promptsRun,
    avgPosition: positions.length === 0
      ? null
      : positions.reduce((a, b) => a + b, 0) / positions.length,
    citationCount: citedIn,
    promptsRun,
  };
}

/**
 * Whether a change is worth telling somebody about.
 *
 * Model answers vary between runs even with the same prompt, so a threshold
 * that fires on any movement produces an alert every day and gets muted. What
 * is genuinely notable: appearing where you did not, disappearing where you
 * did, or a sustained shift in share.
 */
export function isNotableShift(
  previous: VisibilitySnapshot | null,
  current: VisibilitySnapshot,
  threshold = 0.1,
): { notable: boolean; reason: string } {
  if (!previous) {
    return current.mentionRate > 0
      ? { notable: true, reason: 'First measurement — the models are naming you.' }
      : { notable: false, reason: 'First measurement, no mentions yet.' };
  }

  if (previous.mentionRate > 0 && current.mentionRate === 0) {
    return { notable: true, reason: 'The models have stopped naming you entirely.' };
  }
  if (previous.mentionRate === 0 && current.mentionRate > 0) {
    return { notable: true, reason: 'The models have started naming you.' };
  }

  const delta = current.shareOfVoice - previous.shareOfVoice;
  if (Math.abs(delta) >= threshold) {
    return {
      notable: true,
      reason:
        delta > 0
          ? `Share of voice is up ${(delta * 100).toFixed(0)} points.`
          : `Share of voice is down ${(Math.abs(delta) * 100).toFixed(0)} points.`,
    };
  }

  return { notable: false, reason: 'Within normal run-to-run variation.' };
}

/* -------------------------------------------------------------- prompting */

/**
 * The question put to each model.
 *
 * Deliberately does not name the brand. Asking "is Acme a good widget?"
 * guarantees the answer contains "Acme" and measures nothing — the number that
 * matters is whether the brand comes up *unprompted*, which is the only version
 * of this that reflects what a real buyer would see.
 */
export function buildProbe(prompt: string): { system: string; user: string } {
  return {
    system:
      'Answer as you would for someone researching a purchase. Be specific and name ' +
      'real products or companies where they are relevant. If you are not confident a ' +
      'company exists, do not name it. Where you have sources, list them.',
    user: prompt,
  };
}

/**
 * The tracked brands as the reader needs them, with the customer marked.
 *
 * Exactly one `isSelf` is expected; more than one makes share of voice
 * meaningless, and the caller is told rather than left with a wrong number.
 */
export function prepareBrands(
  rows: { brand: string; domain: string | null; isSelf: boolean; aliases?: string[] }[],
): { brands: Brand[]; problem: string | null } {
  const brands = rows.map((r) => ({
    name: r.brand,
    domain: r.domain ?? undefined,
    aliases: r.aliases,
    isSelf: r.isSelf,
  }));

  const selves = brands.filter((b) => b.isSelf);
  if (selves.length === 0) {
    return { brands, problem: 'No brand is marked as yours, so there is nothing to measure.' };
  }
  if (selves.length > 1) {
    return {
      brands,
      problem: `${selves.length} brands are marked as yours. Share of voice needs exactly one.`,
    };
  }
  return { brands, problem: null };
}
