import { crawlabilityRules } from './rules/crawlability.ts';
import { onPageRules } from './rules/on-page.ts';
import { linkRules } from './rules/links.ts';
import { performanceRules } from './rules/performance.ts';
import { securityRules } from './rules/security.ts';
import { aiVisibilityRules } from './rules/ai-visibility.ts';
import type { Category, Finding, PageFacts, Rule, SiteFacts, Thresholds } from './types.ts';

export const ALL_RULES: Rule[] = [
  ...crawlabilityRules,
  ...onPageRules,
  ...linkRules,
  ...performanceRules,
  ...securityRules,
  ...aiVisibilityRules,
];

const BY_ID = new Map(ALL_RULES.map((r) => [r.id, r]));

// Two rules sharing an id would silently shadow each other in the map.
if (BY_ID.size !== ALL_RULES.length) {
  const seen = new Set<string>();
  const dupes = ALL_RULES.map((r) => r.id).filter((id) => (seen.has(id) ? true : (seen.add(id), false)));
  throw new Error(`duplicate rule ids: ${[...new Set(dupes)].join(', ')}`);
}

export function ruleById(id: string): Rule | undefined {
  return BY_ID.get(id);
}

export function rulesByCategory(category: Category): Rule[] {
  return ALL_RULES.filter((r) => r.category === category);
}

export type RuleOverride = {
  isEnabled?: boolean;
  severity?: Rule['severity'];
  thresholds?: Thresholds;
};

/**
 * Runs the registry over a completed crawl.
 *
 * Page rules see one page; site rules see everything. Overrides come from
 * `audit_rule_overrides`, so a workspace can retune a threshold or silence a
 * rule without a deploy.
 */
/**
 * Page rules, run against a slice of freshly crawled pages.
 *
 * These must run while the facts are complete. Scripts, forms, inline styles
 * and DOM size are not worth persisting per page, so a page rule evaluated
 * later from stored columns would silently under-report.
 */
export function evaluatePages(
  pages: PageFacts[],
  site: SiteFacts,
  overrides: Record<string, RuleOverride> = {},
): { findings: Finding[]; ruleResults: Map<string, boolean> } {
  return run(
    ALL_RULES.filter((r) => r.appliesTo === 'page'),
    pages,
    site,
    overrides,
  );
}

/**
 * Site-wide rules, run once the crawl is complete.
 *
 * These need the whole picture — duplicates across pages, orphans, sitemap
 * coverage — so they cannot run per slice.
 */
export function evaluateSite(
  site: SiteFacts,
  overrides: Record<string, RuleOverride> = {},
): { findings: Finding[]; ruleResults: Map<string, boolean> } {
  return run(
    ALL_RULES.filter((r) => r.appliesTo === 'site'),
    [],
    site,
    overrides,
  );
}

/** Both halves at once — the path a single-pass caller (and the tests) take. */
export function evaluateAll(
  site: SiteFacts,
  overrides: Record<string, RuleOverride> = {},
): { findings: Finding[]; ruleResults: Map<string, boolean> } {
  return run(ALL_RULES, site.pages, site, overrides);
}

function run(
  rules: Rule[],
  pages: PageFacts[],
  site: SiteFacts,
  overrides: Record<string, RuleOverride>,
): { findings: Finding[]; ruleResults: Map<string, boolean> } {
  const findings: Finding[] = [];
  const ruleResults = new Map<string, boolean>();

  for (const rule of rules) {
    const override = overrides[rule.id];
    if (override?.isEnabled === false) continue;

    const thresholds = { ...(rule.defaultThresholds ?? {}), ...(override?.thresholds ?? {}) };
    const ctx = { thresholds, site };
    const severity = override?.severity ?? rule.severity;

    const produced: Finding[] = [];
    if (rule.appliesTo === 'site') {
      pushAll(produced, rule.evaluate(site, ctx));
    } else {
      for (const page of pages) pushAll(produced, rule.evaluate(page, ctx));
    }

    // An overridden severity has to reach the stored finding, not just the rule.
    for (const finding of produced) findings.push({ ...finding, severity });
    ruleResults.set(rule.id, produced.length === 0);
  }

  return { findings, ruleResults };
}

function pushAll(target: Finding[], result: Finding[] | Finding | null): void {
  if (!result) return;
  if (Array.isArray(result)) target.push(...result);
  else target.push(result);
}

/** Seed rows for `audit_rules`, so the catalogue is data the admin can edit. */
export function ruleSeedRows() {
  return ALL_RULES.map((rule, index) => ({
    id: rule.id,
    category: rule.category,
    severity: rule.severity,
    weight: rule.weight,
    title: rule.title,
    why: rule.why,
    howToFix: rule.howToFix,
    appliesTo: rule.appliesTo,
    thresholds: rule.defaultThresholds ?? {},
    isEnabled: true,
    isAiRelevant: rule.isAiRelevant ?? false,
    sortOrder: index,
  }));
}

export type { PageFacts, SiteFacts };
