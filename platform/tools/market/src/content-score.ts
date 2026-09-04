/**
 * Scoring a draft against its brief.
 *
 * This is 4C's equivalent of 4A's opportunity finders: pure arithmetic over
 * text the customer already has, no vendor call, no credits. That is what makes
 * the editor useful on the free plan and identical with AI switched off — the
 * score, the checks and the advice are all computed here; AI only ever *writes*
 * the draft, never judges it.
 *
 * Three decisions worth stating, because most SEO editors get them wrong:
 *
 * **Keyword density is a band, not a ladder.** Every check that only rewards
 * "more keyword" teaches people to stuff, which is the one on-page mistake that
 * actively costs rankings. Going over the band loses points and says so.
 *
 * **A score with no reasons is noise.** Every check reports what it measured,
 * what it wanted, and one sentence about what to do — so 68/100 is a to-do
 * list rather than a mood.
 *
 * **Readability is English-only, and it says so rather than lying.** Flesch
 * reading ease is calibrated on English syllable counts; running it over German
 * or Japanese produces a confident number that means nothing, so it is withheld
 * instead.
 */

import { appearsInflected, mentionsOf, spansOf } from './text.ts';

export type Check = {
  key: string;
  label: string;
  /** `pass` earns full weight, `partial` half, `fail` none. */
  state: 'pass' | 'partial' | 'fail';
  weight: number;
  /** What the draft actually does, in the writer's terms. */
  detail: string;
  /** What to do about it. Absent when the check passes. */
  fix?: string;
};

export type ContentScore = {
  /** 0–100, weighted over the checks that could be evaluated. */
  score: number;
  checks: Check[];
  wordCount: number;
  /** Flesch reading ease, or null when the text is not scoreable English. */
  readability: number | null;
  readabilityNote: string | null;
  /** Primary keyword occurrences per hundred words. */
  density: number;
  headings: { level: number; text: string }[];
  /** Entities from the brief that appear, and those that do not. */
  entitiesCovered: string[];
  entitiesMissing: string[];
  questionsAnswered: string[];
  questionsMissing: string[];
};

export type Doc = {
  title: string;
  /** Markdown/MDX body. */
  body: string;
  metaDescription?: string;
  targetKeywords: string[];
};

export type Brief = {
  entities?: string[];
  questions?: string[];
  targetWordCount?: number | null;
};

/* ------------------------------------------------------------ markdown */

export type Parsed = {
  /** Body with code, URLs and markup removed — what a reader actually reads. */
  prose: string;
  headings: { level: number; text: string }[];
  links: { href: string; text: string; internal: boolean }[];
  images: { src: string; alt: string }[];
  words: string[];
};

/**
 * Reduces the body to what a reader reads.
 *
 * Fenced code is removed *before* anything else and it matters: a tutorial with
 * a 400-line snippet would otherwise count as long, well-covered and dense in
 * whatever identifiers the code happens to use. Same for URLs — a link to
 * `example.com/best-widget-reviews` is not the phrase "best widget" appearing
 * in the copy, and counting it inflates every keyword check on the page.
 */
export function parseBody(body: string): Parsed {
  // Fences first, so a `#` inside a snippet is never read as a heading.
  let text = body.replace(/^[ \t]*(?:```|~~~)[\s\S]*?(?:```|~~~)[ \t]*$/gm, '\n');

  const headings: { level: number; text: string }[] = [];
  for (const match of text.matchAll(/^(#{1,6})[ \t]+(.+?)[ \t]*#*$/gm)) {
    headings.push({ level: match[1]!.length, text: stripInline(match[2]!) });
  }

  const images: { src: string; alt: string }[] = [];
  for (const match of text.matchAll(/!\[([^\]]*)\]\(([^)\s]+)[^)]*\)/g)) {
    images.push({ alt: match[1]!.trim(), src: match[2]! });
  }
  text = text.replace(/!\[[^\]]*\]\([^)]*\)/g, ' ');

  const links: { href: string; text: string; internal: boolean }[] = [];
  for (const match of text.matchAll(/\[([^\]]+)\]\(([^)\s]+)[^)]*\)/g)) {
    const href = match[2]!;
    links.push({
      text: stripInline(match[1]!),
      href,
      // Relative, root-relative and anchor hrefs are the site's own. A full URL
      // may still be internal, but the doc does not know its own host, so the
      // conservative reading is external.
      internal: !/^[a-z][a-z0-9+.-]*:/i.test(href) && !href.startsWith('//'),
    });
  }
  // Link *text* is prose and stays; the URL is not and goes.
  text = text.replace(/\[([^\]]+)\]\([^)]*\)/g, '$1');

  // Bare URLs, autolinks, and inline code are not prose either.
  text = text
    .replace(/<https?:\/\/[^>]+>/gi, ' ')
    .replace(/https?:\/\/\S+/gi, ' ')
    .replace(/`[^`\n]*`/g, ' ')
    .replace(/^\s{0,3}>[ \t]?/gm, '')
    .replace(/^[ \t]*[-*+][ \t]+/gm, '')
    .replace(/^[ \t]*\d+\.[ \t]+/gm, '')
    .replace(/^#{1,6}[ \t]+/gm, '')
    .replace(/<[^>]+>/g, ' ');

  const prose = stripInline(text).replace(/[ \t]+/g, ' ').trim();

  return { prose, headings, links, images, words: wordsOf(prose) };
}

function stripInline(text: string): string {
  return text
    .replace(/\*\*|__|\*|_|~~/g, '')
    .replace(/\\([\\`*_{}[\]()#+\-.!])/g, '$1');
}

/** Words, Unicode-aware, so "naïve" and "東京" are not silently dropped. */
export function wordsOf(text: string): string[] {
  return text.match(/[\p{L}\p{N}][\p{L}\p{N}'’-]*/gu) ?? [];
}

/* ---------------------------------------------------------- readability */

/**
 * Flesch reading ease, with an honest refusal.
 *
 * The formula is `206.835 − 1.015 × (words/sentence) − 84.6 × (syllables/word)`
 * and its constants are fitted to English. Two guards keep it from producing
 * confident nonsense: under 50 words the sentence average swings wildly on a
 * single full stop, and text that is mostly non-Latin script is not English at
 * all. In both cases the caller gets `null` and a reason.
 */
export function readingEase(prose: string): { score: number | null; note: string | null } {
  const words = wordsOf(prose);
  if (words.length < 50) {
    return { score: null, note: 'Too short to score reliably — under 50 words.' };
  }

  const latin = words.filter((w) => /^[\p{Script=Latin}\p{N}'’-]+$/u.test(w)).length;
  if (latin / words.length < 0.8) {
    return {
      score: null,
      // Better to withhold than to report a number calibrated on a different
      // language's syllable structure.
      note: 'Readability is calibrated for English and is not shown for this text.',
    };
  }

  const sentences = Math.max(1, (prose.match(/[.!?]+(?=\s|$)/g) ?? []).length);
  const syllables = words.reduce((total, word) => total + syllablesIn(word), 0);

  const raw = 206.835 - 1.015 * (words.length / sentences) - 84.6 * (syllables / words.length);
  return { score: Math.round(Math.max(0, Math.min(100, raw)) * 10) / 10, note: null };
}

/**
 * Syllables in an English word.
 *
 * A vowel-group count with the corrections that matter at this scale: a silent
 * trailing "e" ("game" is one syllable, not two), "-le" after a consonant which
 * *is* its own syllable ("table"), and a floor of one because every word has at
 * least one.
 */
export function syllablesIn(word: string): number {
  const w = word.toLowerCase().replace(/[^a-z]/g, '');
  if (w.length === 0) return 0;
  if (w.length <= 3) return 1;

  let groups = (w.match(/[aeiouy]+/g) ?? []).length;

  // "game" → 1, but "table" → 2 and "the" is already covered by the length floor.
  if (/[^aeiou]e$/.test(w)) {
    groups -= 1;
    if (/[^aeioulr]le$/.test(w)) groups += 1;
  }
  // "-es" and "-ed" are usually not their own syllable: "makes", "walked".
  if (/[^aeiouy](es|ed)$/.test(w)) groups -= 1;

  return Math.max(1, groups);
}

/* --------------------------------------------------------------- scoring */

/** Where keyword density stops helping and starts hurting. */
export const DENSITY_BAND = { min: 0.5, max: 2.5 } as const;

export function scoreContent(doc: Doc, brief: Brief = {}): ContentScore {
  const parsed = parseBody(doc.body);
  const primary = doc.targetKeywords[0]?.trim() ?? '';
  const wordCount = parsed.words.length;
  const checks: Check[] = [];

  const occurrences = primary ? mentionsOf(parsed.prose, [primary]).length : 0;
  const density = wordCount === 0 ? 0 : (occurrences / wordCount) * 100;

  /* ---- the keyword ---- */

  if (!primary) {
    checks.push({
      key: 'keyword_set', label: 'Target keyword', state: 'fail', weight: 10,
      detail: 'No target keyword is set, so most of these checks cannot run.',
      fix: 'Add the phrase you want this page to rank for.',
    });
  } else {
    const inTitle = spansOf(doc.title, primary).length > 0;
    checks.push({
      key: 'keyword_in_title',
      label: 'Keyword in the title',
      state: inTitle ? 'pass' : 'fail',
      weight: 12,
      detail: inTitle
        ? `“${primary}” appears in the title.`
        : `The title does not contain “${primary}”.`,
      fix: inTitle ? undefined : 'Work the phrase into the title — ideally near the front.',
    });

    const firstAt = spansOf(parsed.prose, primary)[0]?.start ?? null;
    const early = firstAt !== null && wordsOf(parsed.prose.slice(0, firstAt)).length <= 100;
    checks.push({
      key: 'keyword_early',
      label: 'Keyword in the opening',
      state: firstAt === null ? 'fail' : early ? 'pass' : 'partial',
      weight: 6,
      detail:
        firstAt === null
          ? 'The phrase does not appear in the body at all.'
          : early
            ? 'It appears in the first hundred words.'
            : `It first appears after about ${wordsOf(parsed.prose.slice(0, firstAt)).length} words.`,
      fix:
        firstAt !== null && early
          ? undefined
          : 'Say what the page is about in the opening paragraph, in the reader’s words.',
    });

    /*
     * A band, deliberately. Rewarding density without a ceiling is how these
     * tools taught a generation of writers to repeat a phrase until the copy
     * reads like a robot wrote it — which is both worse to read and worse to
     * rank.
     */
    const over = density > DENSITY_BAND.max;
    const under = density < DENSITY_BAND.min;
    checks.push({
      key: 'keyword_density',
      label: 'Keyword density',
      state: over || (under && occurrences === 0) ? 'fail' : under ? 'partial' : 'pass',
      weight: 8,
      detail: `${occurrences} use${occurrences === 1 ? '' : 's'} in ${wordCount} words — ${density.toFixed(1)} per hundred.`,
      fix: over
        ? `That is above ${DENSITY_BAND.max} per hundred. Cut some repetitions; over-use reads badly and does not help.`
        : under
          ? `Aim for ${DENSITY_BAND.min}–${DENSITY_BAND.max} per hundred. Use the phrase where it reads naturally, not everywhere.`
          : undefined,
    });
  }

  /* ---- title and meta ---- */

  const titleLength = doc.title.trim().length;
  const titleOk = titleLength >= 30 && titleLength <= 60;
  checks.push({
    key: 'title_length',
    label: 'Title length',
    state: titleLength === 0 ? 'fail' : titleOk ? 'pass' : 'partial',
    weight: 6,
    detail: `${titleLength} characters.`,
    fix: titleOk
      ? undefined
      : titleLength === 0
        ? 'Give the page a title.'
        : titleLength < 30
          ? 'Under about 30 characters wastes the space Google gives you.'
          : 'Over about 60 characters and the end gets cut off in results.',
  });

  const meta = doc.metaDescription?.trim() ?? '';
  const metaOk = meta.length >= 120 && meta.length <= 158;
  checks.push({
    key: 'meta_description',
    label: 'Meta description',
    state: meta.length === 0 ? 'fail' : metaOk ? 'pass' : 'partial',
    weight: 5,
    detail: meta.length === 0 ? 'Not written.' : `${meta.length} characters.`,
    fix: metaOk
      ? undefined
      : meta.length === 0
        ? 'Write one. It rarely changes rankings, but it decides whether anyone clicks.'
        : meta.length < 120
          ? 'Short enough that you are leaving persuasion on the table.'
          : 'Over about 158 characters and it is truncated.',
  });

  /* ---- structure ---- */

  const h1s = parsed.headings.filter((h) => h.level === 1);
  checks.push({
    key: 'single_h1',
    label: 'One H1',
    state: h1s.length === 1 ? 'pass' : 'fail',
    weight: 6,
    detail:
      h1s.length === 0 ? 'The body has no H1.' : `The body has ${h1s.length} H1 headings.`,
    fix:
      h1s.length === 1
        ? undefined
        : h1s.length === 0
          ? 'Add one top-level heading naming the subject.'
          : 'Keep one H1 and demote the rest to H2.',
  });

  /*
   * Only judged when there is an outline to judge. A document with no headings
   * would otherwise "pass" heading order, which is both vacuous and generous —
   * an empty draft would score above zero on the strength of it.
   */
  if (parsed.headings.length >= 2) {
    const skip = firstHeadingSkip(parsed.headings);
    checks.push({
      key: 'heading_order',
      label: 'Heading order',
      state: skip ? 'fail' : 'pass',
      weight: 4,
      detail: skip
        ? `“${skip.text}” is an H${skip.level} under an H${skip.after}.`
        : 'Headings step down one level at a time.',
      fix: skip
        ? 'Skipping a level breaks the outline for screen readers as well as crawlers.'
        : undefined,
    });
  }

  /*
   * Sections are scaled to length, and only judged once the piece is long
   * enough that anyone would skim it. "At least three H2s" is nonsense advice
   * for a 200-word answer post and far too lax for a 3,000-word guide.
   */
  if (wordCount >= 300) {
    const wanted = Math.max(1, Math.round(wordCount / 350));
    const h2s = parsed.headings.filter((h) => h.level === 2).length;
    checks.push({
      key: 'sections',
      label: 'Sections',
      state: h2s >= wanted ? 'pass' : h2s > 0 ? 'partial' : 'fail',
      weight: 5,
      detail: `${h2s} section heading${h2s === 1 ? '' : 's'} for ${wordCount} words.`,
      fix: h2s >= wanted ? undefined : `At this length, around ${wanted} would let a reader skim.`,
    });
  }

  /* ---- length ---- */

  const target = brief.targetWordCount ?? null;
  if (target) {
    const ratio = wordCount / target;
    checks.push({
      key: 'length',
      label: 'Length against the brief',
      state: ratio >= 0.9 ? 'pass' : ratio >= 0.6 ? 'partial' : 'fail',
      weight: 8,
      detail: `${wordCount} words against a target of ${target}.`,
      fix:
        ratio >= 0.9
          ? undefined
          : `The pages ranking for this run to about ${target} words — ${target - wordCount} short.`,
    });
  }

  /* ---- coverage ---- */

  const entities = (brief.entities ?? []).filter((e) => e.trim().length > 1);
  const haystack = `${doc.title}\n${parsed.prose}`;
  const entitiesCovered = entities.filter((e) => appearsInflected(haystack, e));
  const entitiesMissing = entities.filter((e) => !entitiesCovered.includes(e));
  if (entities.length > 0) {
    const share = entitiesCovered.length / entities.length;
    checks.push({
      key: 'entities',
      label: 'Topics covered',
      state: share >= 0.8 ? 'pass' : share >= 0.5 ? 'partial' : 'fail',
      weight: 10,
      detail: `${entitiesCovered.length} of ${entities.length} topics from the brief appear.`,
      fix:
        share >= 0.8
          ? undefined
          : `Missing: ${entitiesMissing.slice(0, 5).join(', ')}${entitiesMissing.length > 5 ? '…' : ''}`,
    });
  }

  const questions = (brief.questions ?? []).filter((q) => q.trim().length > 0);
  const questionsAnswered = questions.filter((q) => answered(q, haystack));
  const questionsMissing = questions.filter((q) => !questionsAnswered.includes(q));
  if (questions.length > 0) {
    const share = questionsAnswered.length / questions.length;
    checks.push({
      key: 'questions',
      label: 'Questions answered',
      state: share >= 0.7 ? 'pass' : share > 0 ? 'partial' : 'fail',
      weight: 7,
      detail: `${questionsAnswered.length} of ${questions.length} questions people ask are addressed.`,
      fix:
        share >= 0.7
          ? undefined
          : `Unanswered: ${questionsMissing.slice(0, 3).join(' · ')}${questionsMissing.length > 3 ? '…' : ''}`,
    });
  }

  /* ---- links and images ---- */

  const internal = parsed.links.filter((l) => l.internal).length;
  checks.push({
    key: 'internal_links',
    label: 'Internal links',
    state: internal >= 2 ? 'pass' : internal === 1 ? 'partial' : 'fail',
    weight: 5,
    detail: `${internal} link${internal === 1 ? '' : 's'} to your own pages.`,
    fix: internal >= 2 ? undefined : 'Link to two or three related pages so this one is not a dead end.',
  });

  const missingAlt = parsed.images.filter((i) => i.alt.length === 0).length;
  if (parsed.images.length > 0) {
    checks.push({
      key: 'image_alt',
      label: 'Image alt text',
      state: missingAlt === 0 ? 'pass' : 'fail',
      weight: 4,
      detail:
        missingAlt === 0
          ? `All ${parsed.images.length} images describe themselves.`
          : `${missingAlt} of ${parsed.images.length} images have no alt text.`,
      fix: missingAlt === 0 ? undefined : 'Describe each image for readers who cannot see it.',
    });
  }

  /* ---- readability ---- */

  const ease = readingEase(parsed.prose);
  if (ease.score !== null) {
    checks.push({
      key: 'readability',
      label: 'Readability',
      state: ease.score >= 50 ? 'pass' : ease.score >= 30 ? 'partial' : 'fail',
      weight: 6,
      detail: `Flesch reading ease ${ease.score} — ${easeBand(ease.score)}.`,
      fix:
        ease.score >= 50
          ? undefined
          : 'Shorter sentences and plainer words. Long clauses are where readers give up.',
    });
  }

  const earned = checks.reduce(
    (total, c) => total + c.weight * (c.state === 'pass' ? 1 : c.state === 'partial' ? 0.5 : 0),
    0,
  );
  const possible = checks.reduce((total, c) => total + c.weight, 0);

  return {
    score: possible === 0 ? 0 : Math.round((earned / possible) * 100),
    checks,
    wordCount,
    readability: ease.score,
    readabilityNote: ease.note,
    density: Math.round(density * 100) / 100,
    headings: parsed.headings,
    entitiesCovered,
    entitiesMissing,
    questionsAnswered,
    questionsMissing,
  };
}

/**
 * Whether a question from the brief is addressed.
 *
 * Not by looking for the question verbatim — nobody writes "what is the best
 * widget for a small team?" into their own prose. The test is whether the
 * question's *content* words all appear, ignoring the interrogative and the
 * stop words that every question shares.
 */
function answered(question: string, text: string): boolean {
  const terms = wordsOf(question)
    .map((w) => w.toLowerCase())
    .filter((w) => w.length > 2 && !STOP_WORDS.has(w));
  if (terms.length === 0) return false;

  const hits = terms.filter((term) => appearsInflected(text, term)).length;
  // Three quarters rather than all: "how much does a widget cost" is answered
  // by a pricing section that never uses the word "much".
  return hits / terms.length >= 0.75;
}

const STOP_WORDS = new Set([
  'the', 'and', 'for', 'are', 'you', 'your', 'what', 'which', 'how', 'why', 'when', 'where',
  'who', 'does', 'did', 'can', 'should', 'would', 'could', 'with', 'from', 'that', 'this',
  'there', 'have', 'has', 'was', 'were', 'will', 'about', 'into', 'than', 'then', 'they',
  'them', 'its', 'it’s', 'but', 'not', 'any', 'all', 'get', 'use', 'used', 'using', 'make',
  // Shape rather than substance: nobody answers "how much" by writing "much".
  'much', 'many', 'long', 'best', 'good', 'need', 'want', 'like', 'take', 'know',
]);

function firstHeadingSkip(
  headings: { level: number; text: string }[],
): { level: number; text: string; after: number } | null {
  let previous = 0;
  for (const heading of headings) {
    if (previous > 0 && heading.level > previous + 1) {
      return { level: heading.level, text: heading.text, after: previous };
    }
    previous = heading.level;
  }
  return null;
}

function easeBand(score: number): string {
  if (score >= 80) return 'very easy';
  if (score >= 60) return 'plain English';
  if (score >= 50) return 'fairly hard';
  if (score >= 30) return 'difficult';
  return 'very difficult';
}
