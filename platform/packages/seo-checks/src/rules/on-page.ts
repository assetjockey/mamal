import { num, type Finding, type PageFacts, type Rule, type SiteFacts } from '../types.ts';
import { normalize } from './crawlability.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page' });
const site = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'site' });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });

/** Only pages that could actually rank are worth judging. */
const rankable = (p: PageFacts) => p.statusCode === 200 && p.isIndexable && p.fetchClass === 'ok';

export const onPageRules: Rule[] = [
  page({
    id: 'missing-title',
    category: 'on-page',
    severity: 'critical',
    weight: 10,
    title: 'Missing title tag',
    why: 'The title is the single strongest on-page signal and the headline of your search result. Without one, search engines invent something.',
    howToFix: 'Add a unique `<title>` describing the page in 50–60 characters, most specific words first.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return !pg.title?.trim() ? f('missing-title', 'critical', pg.url, {}) : null;
    },
  }),

  page({
    id: 'title-too-long',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Title is too long',
    why: 'Google truncates around 60 characters, so anything past that is invisible in the result.',
    howToFix: 'Trim to under 60 characters. Put the distinctive words first — the brand can go last or be dropped.',
    defaultThresholds: { maxLength: 60 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxLength', 60);
      if (!rankable(pg) || !pg.title) return null;
      return pg.title.length > max
        ? f('title-too-long', 'info', pg.url, { length: pg.title.length, max, title: pg.title })
        : null;
    },
  }),

  page({
    id: 'title-too-short',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Title is too short',
    why: 'A very short title wastes the most valuable space on the page and usually means it is generic.',
    howToFix: 'Aim for 30–60 characters. "Home" or "Products" tells neither a person nor a crawler anything.',
    defaultThresholds: { minLength: 30 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const min = num(ctx.thresholds, 'minLength', 30);
      if (!rankable(pg) || !pg.title) return null;
      return pg.title.trim().length < min
        ? f('title-too-short', 'info', pg.url, { length: pg.title.trim().length, min, title: pg.title })
        : null;
    },
  }),

  page({
    id: 'missing-meta-description',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Missing meta description',
    why: 'Without one, search engines pull an arbitrary sentence from the page as your result snippet.',
    howToFix: 'Write 120–158 characters that describe the page and give someone a reason to click.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return !pg.metaDescription?.trim() ? f('missing-meta-description', 'warning', pg.url, {}) : null;
    },
  }),

  page({
    id: 'meta-description-too-long',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Meta description is too long',
    why: 'Anything past roughly 158 characters is cut off mid-sentence in the result.',
    howToFix: 'Trim to under 158 characters and front-load the point.',
    defaultThresholds: { maxLength: 158 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxLength', 158);
      if (!rankable(pg) || !pg.metaDescription) return null;
      return pg.metaDescription.length > max
        ? f('meta-description-too-long', 'info', pg.url, { length: pg.metaDescription.length, max })
        : null;
    },
  }),

  page({
    id: 'meta-description-too-short',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Meta description is too short',
    why: 'A stub description wastes the snippet and rarely earns the click.',
    howToFix: 'Expand to at least 70 characters with something specific about this page.',
    defaultThresholds: { minLength: 70 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const min = num(ctx.thresholds, 'minLength', 70);
      if (!rankable(pg) || !pg.metaDescription?.trim()) return null;
      return pg.metaDescription.trim().length < min
        ? f('meta-description-too-short', 'info', pg.url, { length: pg.metaDescription.trim().length, min })
        : null;
    },
  }),

  page({
    id: 'missing-h1',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Missing H1',
    why: 'The H1 tells readers and crawlers what the page is about before anything else is read.',
    howToFix: 'Add exactly one `<h1>` near the top, describing the page. It should echo the title without duplicating it word for word.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return pg.headings.filter((h) => h.level === 1).length === 0
        ? f('missing-h1', 'warning', pg.url, {})
        : null;
    },
  }),

  page({
    id: 'multiple-h1',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'More than one H1',
    why: 'Several competing H1s blur what the page is about. Modern search engines cope, but it usually signals a template problem.',
    howToFix:
      'Keep one H1 — usually the page title — and demote the rest to H2.\n\n' +
      'When several appear, the cause is normally a template that wraps each section ' +
      'in its own H1, or a rich-text editor letting authors pick heading levels freely.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      const h1s = pg.headings.filter((h) => h.level === 1);
      return h1s.length > 1
        ? f('multiple-h1', 'info', pg.url, { count: h1s.length, headings: h1s.map((h) => h.text).slice(0, 5) })
        : null;
    },
  }),

  page({
    id: 'h1-equals-title',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'H1 is identical to the title',
    why: 'Two identical strings waste a chance to cover a second phrasing people actually search for.',
    howToFix: 'Keep the title search-focused and let the H1 read naturally for a person.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const h1 = pg.headings.find((h) => h.level === 1)?.text?.trim();
      if (!rankable(pg) || !h1 || !pg.title) return null;
      return h1.toLowerCase() === pg.title.trim().toLowerCase()
        ? f('h1-equals-title', 'info', pg.url, { text: h1 })
        : null;
    },
  }),

  page({
    id: 'heading-order-skip',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Heading levels skip',
    why: 'Jumping from H2 to H4 breaks the document outline that screen readers and crawlers use to understand structure.',
    howToFix: 'Use headings in order. Style them with CSS rather than by picking a smaller tag.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      let previous = 0;
      for (const h of pg.headings) {
        if (previous && h.level > previous + 1) {
          return f('heading-order-skip', 'info', pg.url, { from: previous, to: h.level, heading: h.text });
        }
        previous = h.level;
      }
      return null;
    },
  }),

  page({
    id: 'thin-content',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Thin content',
    why: 'Very little text gives search engines almost nothing to rank, and gives an AI answer engine nothing to quote.',
    howToFix: 'Either expand it to genuinely answer the question the page exists for, or merge it into a fuller page and redirect.',
    defaultThresholds: { minWords: 250 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const min = num(ctx.thresholds, 'minWords', 250);
      if (!rankable(pg)) return null;
      return pg.wordCount < min ? f('thin-content', 'warning', pg.url, { words: pg.wordCount, min }) : null;
    },
  }),

  page({
    id: 'low-text-ratio',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'Low text-to-HTML ratio',
    why: 'Far more markup than words usually means the content is buried in layout, which slows the page and dilutes relevance.',
    howToFix: 'Move styling to CSS, trim wrapper markup, and check whether the real content renders server-side.',
    defaultThresholds: { minRatio: 10 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const min = num(ctx.thresholds, 'minRatio', 10);
      if (!rankable(pg) || pg.bytes === 0) return null;
      return pg.textRatio < min
        ? f('low-text-ratio', 'info', pg.url, { ratio: Math.round(pg.textRatio * 10) / 10, min })
        : null;
    },
  }),

  page({
    id: 'missing-lang',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'No language declared',
    why: 'Without `lang`, screen readers guess pronunciation and search engines guess the audience.',
    howToFix: 'Add `lang` to the `<html>` element, e.g. `<html lang="en">`.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return !pg.lang ? f('missing-lang', 'info', pg.url, {}) : null;
    },
  }),

  page({
    id: 'non-seo-friendly-url',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'URL is not readable',
    why: 'Query strings and ids in place of words make a URL harder to trust in a result and harder to link to.',
    howToFix: 'Prefer `/guides/seo-audit` over `/page?id=42`. Change URLs only with a 301 in place.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      try {
        const u = new URL(pg.url);
        const ugly = /[?&=_%]|\/\d+$/.test(u.pathname + u.search);
        return ugly ? f('non-seo-friendly-url', 'info', pg.url, { path: u.pathname + u.search }) : null;
      } catch {
        return null;
      }
    },
  }),

  page({
    id: 'images-missing-alt',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Images without alt text',
    why: 'Alt text is what a screen reader announces and what image search reads. Missing it excludes people and loses traffic.',
    howToFix: 'Describe what the image shows, in a sentence fragment. Decorative images take `alt=""` — empty, not absent.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      const missing = pg.images.filter((i) => i.alt === null);
      return missing.length > 0
        ? f('images-missing-alt', 'warning', pg.url, {
            count: missing.length,
            total: pg.images.length,
            sample: missing.slice(0, 10).map((i) => i.src),
          })
        : null;
    },
  }),

  page({
    id: 'missing-opengraph',
    category: 'on-page',
    severity: 'info',
    weight: 1,
    title: 'No Open Graph tags',
    why: 'Without them, a shared link renders as a bare URL on social and in chat apps.',
    howToFix: 'Add `og:title`, `og:description` and `og:image`. The image should be at least 1200×630.',
    isAiRelevant: true,
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return !pg.ogTitle && !pg.ogImage ? f('missing-opengraph', 'info', pg.url, {}) : null;
    },
  }),

  site({
    id: 'duplicate-title',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Duplicate titles',
    why: 'Pages sharing a title compete with each other, and search engines cannot tell which one to show.',
    howToFix: 'Give each page a title describing what makes it different — usually the template is missing a variable.',
    evaluate: (s) => groupDuplicates(s as SiteFacts, 'title', 'duplicate-title'),
  }),

  site({
    id: 'duplicate-meta-description',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Duplicate meta descriptions',
    why: 'The same snippet on many pages wastes the strongest piece of copy you control in the result.',
    howToFix: 'Write a description per page, or generate one from the page content.',
    evaluate: (s) => groupDuplicates(s as SiteFacts, 'metaDescription', 'duplicate-meta-description'),
  }),

  site({
    id: 'duplicate-content',
    category: 'on-page',
    severity: 'warning',
    weight: 5,
    title: 'Duplicate content',
    why: 'Pages with identical body text split their ranking signals instead of concentrating them.',
    howToFix: 'Keep one canonical version and point the rest at it, or merge them and redirect.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const groups = new Map<string, string[]>();
      for (const p of facts.pages) {
        if (!rankable(p) || !p.contentHash) continue;
        groups.set(p.contentHash, [...(groups.get(p.contentHash) ?? []), p.url]);
      }
      const dupes = [...groups.values()].filter((urls) => urls.length > 1);
      return dupes.length > 0
        ? f('duplicate-content', 'warning', null, { groups: dupes.length, sample: dupes.slice(0, 5) })
        : null;
    },
  }),
];

function groupDuplicates(
  facts: SiteFacts,
  field: 'title' | 'metaDescription',
  ruleId: string,
): Finding | null {
  const groups = new Map<string, string[]>();
  for (const p of facts.pages) {
    if (!rankable(p)) continue;
    const value = p[field]?.trim();
    if (!value) continue;
    const key = value.toLowerCase();
    groups.set(key, [...(groups.get(key) ?? []), p.url]);
  }
  const dupes = [...groups.entries()].filter(([, urls]) => urls.length > 1);
  if (dupes.length === 0) return null;
  return f(ruleId, 'warning', null, {
    groups: dupes.length,
    sample: dupes.slice(0, 5).map(([value, urls]) => ({ value, count: urls.length, urls: urls.slice(0, 5) })),
  });
}

export { normalize };
