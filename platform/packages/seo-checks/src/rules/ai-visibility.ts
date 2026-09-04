import { type Finding, type PageFacts, type Rule, type SiteFacts } from '../types.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page', isAiRelevant: true });
const site = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'site', isAiRelevant: true });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });
const rankable = (p: PageFacts) => p.statusCode === 200 && p.isIndexable && p.fetchClass === 'ok';

/**
 * The AI-visibility category — what "search AND AI visibility" in the product
 * promise actually means. These are the checks that decide whether an answer
 * engine can read, attribute and quote your page, which is a different question
 * from whether Google can rank it.
 */
export const aiVisibilityRules: Rule[] = [
  page({
    id: 'missing-structured-data',
    category: 'ai-visibility',
    severity: 'warning',
    weight: 5,
    title: 'No structured data',
    why: 'Schema.org markup is how a machine knows this page is a product, an article or a recipe rather than prose. Answer engines lean on it heavily when deciding what to quote.',
    howToFix: 'Add JSON-LD in `<head>`. Start with `Organization` on the homepage and `Article` or `Product` on content pages.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      return pg.schemaTypes.length === 0 ? f('missing-structured-data', 'warning', pg.url, {}) : null;
    },
  }),

  page({
    id: 'content-not-extractable',
    category: 'ai-visibility',
    severity: 'critical',
    weight: 10,
    title: 'Content only renders with JavaScript',
    why: 'The HTML response is nearly empty — the text arrives via JavaScript. Most AI crawlers do not run JavaScript, so to them this page has no content at all.',
    howToFix: 'Server-render the main content, or pre-render it. This is the single biggest reason a page is invisible to answer engines while ranking normally in Google.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      // Substantial markup, almost no text: the signature of a client-rendered shell.
      const empty = pg.wordCount < 50 && pg.bytes > 15_000 && pg.scripts.length > 3;
      return empty
        ? f('content-not-extractable', 'critical', pg.url, {
            words: pg.wordCount,
            bytes: pg.bytes,
            scripts: pg.scripts.length,
          })
        : null;
    },
  }),

  page({
    id: 'no-author-attribution',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'No author or publisher declared',
    why: 'Answer engines cite sources they can attribute. Content with no named author or organisation is far less likely to be quoted.',
    howToFix: 'Add `author` and `publisher` to your Article schema, and a visible byline.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg) || pg.wordCount < 300) return null;
      const hasAttribution = pg.schemaTypes.some((t) =>
        ['Article', 'BlogPosting', 'NewsArticle', 'Person', 'Organization'].includes(t),
      );
      return !hasAttribution ? f('no-author-attribution', 'info', pg.url, { words: pg.wordCount }) : null;
    },
  }),

  page({
    id: 'missing-faq-schema',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'Q&A content without FAQ schema',
    why: 'The page reads like a set of questions and answers, which is exactly the shape answer engines quote — but without the markup they have to infer it.',
    howToFix: 'Add `FAQPage` schema with each question and answer.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!rankable(pg)) return null;
      const questions = pg.headings.filter((h) => h.text.trim().endsWith('?')).length;
      if (questions < 3) return null;
      return !pg.schemaTypes.includes('FAQPage')
        ? f('missing-faq-schema', 'info', pg.url, { questionHeadings: questions })
        : null;
    },
  }),

  site({
    id: 'ai-crawler-blocked',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'AI crawlers are disallowed',
    why: 'Your robots.txt blocks one or more AI crawlers. That is a legitimate choice — it also means those assistants cannot cite you.',
    howToFix:
      'This is a trade-off, not a mistake, so we report it rather than scoring it as a failure.\n\n' +
      '- **To be citable**: allow `GPTBot`, `ClaudeBot`, `PerplexityBot`, `Google-Extended`.\n' +
      '- **To stay out**: keep the disallow and accept the loss of AI referrals.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const blocked = facts.aiCrawlers.filter((c) => !c.allowed).map((c) => c.agent);
      return blocked.length > 0 ? f('ai-crawler-blocked', 'info', null, { blocked }) : null;
    },
  }),

  site({
    id: 'llms-txt-missing',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'No llms.txt',
    why: 'An emerging convention: a plain-text summary at `/llms.txt` telling assistants what your site is and which pages matter. Cheap to add, and it is read by a growing number of crawlers.',
    howToFix: 'Add `/llms.txt` with a short description of the site and a linked list of your key pages.',
    evaluate: (s) => ((s as SiteFacts).llmsTxt.found ? null : f('llms-txt-missing', 'info', null, {})),
  }),

  site({
    id: 'missing-org-schema',
    category: 'ai-visibility',
    severity: 'warning',
    weight: 5,
    title: 'No Organization schema',
    why: 'Organization markup is how search and answer engines connect your site to a known entity — the thing that makes a knowledge panel and a confident citation possible.',
    howToFix: 'Add `Organization` JSON-LD to the homepage with your name, logo, URL and social profiles.',
    evaluate: (s) => {
      const facts = s as SiteFacts;
      const home = facts.pages.find((p) => p.depth === 0);
      if (!home) return null;
      const hasOrg = home.schemaTypes.some((t) => ['Organization', 'LocalBusiness', 'Corporation'].includes(t));
      return !hasOrg ? f('missing-org-schema', 'warning', home.url, { found: home.schemaTypes }) : null;
    },
  }),

  page({
    id: 'invalid-schema',
    category: 'ai-visibility',
    severity: 'warning',
    weight: 5,
    title: 'Structured data failed to parse',
    why: 'A JSON-LD block with a syntax error is ignored entirely, so you get none of the benefit while believing you do.',
    howToFix: 'Validate the block. A trailing comma or an unescaped quote is usually the cause.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      // The parser records this sentinel when a JSON-LD block throws.
      return pg.schemaTypes.includes('__invalid__')
        ? f('invalid-schema', 'warning', pg.url, {})
        : null;
    },
  }),

  page({
    id: 'missing-hreflang-return',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'Hreflang without a self-reference',
    why: 'An hreflang set must include the page itself. Without it search engines discard the whole cluster.',
    howToFix: 'Add an hreflang entry pointing at this page in its own language.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.hreflang.length === 0) return null;
      const self = pg.hreflang.some((h) => h.href === pg.url);
      return !self ? f('missing-hreflang-return', 'info', pg.url, { entries: pg.hreflang.length }) : null;
    },
  }),

  page({
    id: 'missing-favicon',
    category: 'ai-visibility',
    severity: 'info',
    weight: 1,
    title: 'No favicon',
    why: 'Search results, browser tabs and AI answer cards all show a favicon. Without one you get a blank placeholder next to competitors who have one.',
    howToFix: 'Add `/favicon.ico` and a `<link rel="icon">` with at least a 180×180 PNG.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.depth !== 0) return null;
      return !pg.hasFavicon ? f('missing-favicon', 'info', pg.url, {}) : null;
    },
  }),
];
