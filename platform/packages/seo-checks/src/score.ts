import { ALL_RULES, type RuleOverride } from './registry.ts';
import type { Finding, Severity } from './types.ts';

export type Score = {
  score: number;
  grade: 'A' | 'B' | 'C' | 'D' | 'F';
  testsTotal: number;
  testsPassed: number;
  counts: Record<Severity, number>;
  byCategory: { category: string; score: number; passed: number; total: number }[];
};

/**
 * Score = weighted pass rate.
 *
 * phprank's model, made explicit: every rule carries a weight, and the score
 * is the fraction of weight that passed. A rule that never applied to this
 * site (no images, so no alt-text check) counts as passed rather than being
 * silently dropped, so two sites' scores stay comparable.
 */
export function computeScore(
  ruleResults: Map<string, boolean>,
  findings: Finding[],
  overrides: Record<string, RuleOverride> = {},
): Score {
  let totalWeight = 0;
  let passedWeight = 0;
  let testsTotal = 0;
  let testsPassed = 0;

  const categories = new Map<string, { weight: number; passed: number; total: number; passedCount: number }>();

  for (const rule of ALL_RULES) {
    if (overrides[rule.id]?.isEnabled === false) continue;
    const passed = ruleResults.get(rule.id) ?? true;

    totalWeight += rule.weight;
    if (passed) passedWeight += rule.weight;
    testsTotal++;
    if (passed) testsPassed++;

    const bucket = categories.get(rule.category) ?? { weight: 0, passed: 0, total: 0, passedCount: 0 };
    bucket.weight += rule.weight;
    bucket.total++;
    if (passed) {
      bucket.passed += rule.weight;
      bucket.passedCount++;
    }
    categories.set(rule.category, bucket);
  }

  const score = totalWeight === 0 ? 100 : Math.round((passedWeight / totalWeight) * 100);

  const counts: Record<Severity, number> = { critical: 0, warning: 0, info: 0 };
  for (const finding of findings) counts[finding.severity]++;

  return {
    score,
    grade: gradeFor(score),
    testsTotal,
    testsPassed,
    counts,
    byCategory: [...categories.entries()].map(([category, b]) => ({
      category,
      score: b.weight === 0 ? 100 : Math.round((b.passed / b.weight) * 100),
      passed: b.passedCount,
      total: b.total,
    })),
  };
}

function gradeFor(score: number): Score['grade'] {
  if (score >= 90) return 'A';
  if (score >= 80) return 'B';
  if (score >= 70) return 'C';
  if (score >= 60) return 'D';
  return 'F';
}

/**
 * What to fix first.
 *
 * Severity, then how many pages it affects, then weight. This is the ordering
 * the UI uses when AI prioritisation is off — and it is good enough that the
 * AI version has to earn its credits.
 */
export function prioritize(findings: Finding[]): Finding[] {
  const rank: Record<Severity, number> = { critical: 0, warning: 1, info: 2 };
  const counts = new Map<string, number>();
  for (const f of findings) counts.set(f.ruleId, (counts.get(f.ruleId) ?? 0) + 1);

  return [...findings].sort((a, b) => {
    const bySeverity = rank[a.severity] - rank[b.severity];
    if (bySeverity !== 0) return bySeverity;
    const byReach = (counts.get(b.ruleId) ?? 0) - (counts.get(a.ruleId) ?? 0);
    if (byReach !== 0) return byReach;
    return (ALL_RULES.find((r) => r.id === b.ruleId)?.weight ?? 0) -
      (ALL_RULES.find((r) => r.id === a.ruleId)?.weight ?? 0);
  });
}
