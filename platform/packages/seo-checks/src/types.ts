export const SEVERITIES = ['critical', 'warning', 'info'] as const;
export type Severity = (typeof SEVERITIES)[number];

export const CATEGORIES = [
  'crawlability',
  'on-page',
  'links',
  'performance',
  'security',
  'ai-visibility',
] as const;
export type Category = (typeof CATEGORIES)[number];

/** What the crawler produces for one URL. */
export type PageFacts = {
  url: string;
  statusCode: number;
  fetchClass: 'ok' | 'blocked' | 'error' | 'timeout' | 'redirect';
  redirectChain: string[];
  isHttps: boolean;
  depth: number;
  inSitemap: boolean;

  title: string | null;
  metaDescription: string | null;
  canonical: string | null;
  headerCanonical: string | null;
  robotsMeta: string | null;
  xRobotsTag: string | null;
  /** Derived from robots meta, X-Robots-Tag and the response status. */
  isIndexable: boolean;
  lang: string | null;
  ogTitle: string | null;
  ogDescription: string | null;
  ogImage: string | null;

  headings: { level: number; text: string }[];
  wordCount: number;
  textRatio: number;
  contentHash: string | null;

  images: { src: string; alt: string | null; loading: string | null; format: string }[];
  links: { href: string; anchor: string; rel: string | null; isInternal: boolean }[];
  scripts: { src: string | null; defer: boolean; async: boolean; inline: boolean }[];
  inlineStyleCount: number;
  deprecatedTags: string[];
  forms: { action: string | null; method: string; isSecure: boolean }[];

  schemaTypes: string[];
  hreflang: { lang: string; href: string }[];
  hasViewport: boolean;
  hasCharset: boolean;
  hasDoctype: boolean;
  hasFavicon: boolean;
  plaintextEmails: string[];

  responseMs: number;
  ttfbMs: number;
  bytes: number;
  domNodes: number;
  httpVersion: string | null;
  compression: string | null;
  headers: Record<string, string>;
  mixedContent: string[];
  requestCount: number;
};

/** Everything a site-wide rule needs, computed after the crawl finishes. */
export type SiteFacts = {
  origin: string;
  pages: PageFacts[];
  robotsTxt: { found: boolean; content: string | null; disallowsAll: boolean };
  sitemap: { found: boolean; urls: string[] };
  llmsTxt: { found: boolean };
  /** URLs linked from somewhere, so orphans are `crawled - linked`. */
  linkedUrls: Set<string>;
  brokenTargets: Map<string, number>;
  aiCrawlers: { agent: string; allowed: boolean }[];
  sslValidTo: Date | null;
};

export type Finding = {
  ruleId: string;
  severity: Severity;
  /** Null for site-wide findings. */
  url: string | null;
  evidence: Record<string, unknown>;
};

export type Thresholds = Record<string, number | string | boolean>;

export type RuleContext = {
  thresholds: Thresholds;
  site: SiteFacts;
};

export type Rule = {
  id: string;
  category: Category;
  severity: Severity;
  /** Contribution to the score. Critical 10, warning 5, info 1 by default. */
  weight: number;
  title: string;
  why: string;
  /** Markdown. Rendered verbatim when AI is off — the non-AI fallback. */
  howToFix: string;
  appliesTo: 'page' | 'site';
  defaultThresholds?: Thresholds;
  /** Surfaced under the AI-visibility lens. */
  isAiRelevant?: boolean;
  /** Page rules see one page; site rules see the whole crawl. */
  evaluate(subject: PageFacts | SiteFacts, ctx: RuleContext): Finding[] | Finding | null;
};

export const num = (t: Thresholds, key: string, fallback: number): number =>
  typeof t[key] === 'number' ? (t[key] as number) : fallback;

export const list = (t: Thresholds, key: string, fallback: string[]): string[] =>
  typeof t[key] === 'string' ? String(t[key]).split(',').map((s) => s.trim()) : fallback;
