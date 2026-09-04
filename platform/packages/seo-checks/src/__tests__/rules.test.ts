import { describe, expect, it } from 'vitest';
import { ALL_RULES, evaluateAll, ruleById, ruleSeedRows } from '../registry.ts';
import { computeScore, prioritize } from '../score.ts';
import { goodPage, goodSite } from './fixtures.ts';

/** Which rule ids fired for this site. */
const fired = (site: ReturnType<typeof goodSite>) =>
  new Set(evaluateAll(site).findings.map((f) => f.ruleId));

describe('registry', () => {
  it('has rules in every category', () => {
    const categories = new Set(ALL_RULES.map((r) => r.category));
    expect(categories.size).toBe(6);
    expect(ALL_RULES.length).toBeGreaterThanOrEqual(70);
  });

  it('has no duplicate ids', () => {
    expect(new Set(ALL_RULES.map((r) => r.id)).size).toBe(ALL_RULES.length);
  });

  /**
   * Every rule ships its own remediation prose. This is the non-AI fallback:
   * with AI off, the fix guidance is still specific, which is what keeps the
   * lifetime tier a real product.
   */
  it('every rule explains why it matters and how to fix it', () => {
    for (const rule of ALL_RULES) {
      expect(rule.why.length, `${rule.id} has no why`).toBeGreaterThan(40);
      expect(rule.howToFix.length, `${rule.id} has no fix`).toBeGreaterThan(40);
      expect(rule.title.length, `${rule.id} has no title`).toBeGreaterThan(3);
    }
  });

  it('weights match severity, so the score reflects real impact', () => {
    for (const rule of ALL_RULES) {
      if (rule.severity === 'critical') expect(rule.weight, rule.id).toBeGreaterThanOrEqual(10);
      if (rule.severity === 'info') expect(rule.weight, rule.id).toBeLessThanOrEqual(2);
    }
  });

  it('produces seed rows for the editable catalogue', () => {
    const rows = ruleSeedRows();
    expect(rows).toHaveLength(ALL_RULES.length);
    expect(rows[0]).toHaveProperty('howToFix');
  });
});

describe('a healthy site', () => {
  it('fires nothing', () => {
    expect([...fired(goodSite())]).toEqual([]);
  });

  it('scores 100 with an A', () => {
    const site = goodSite();
    const { findings, ruleResults } = evaluateAll(site);
    const score = computeScore(ruleResults, findings);
    expect(score.score).toBe(100);
    expect(score.grade).toBe('A');
    expect(score.counts).toEqual({ critical: 0, warning: 0, info: 0 });
  });
});

describe('crawlability', () => {
  it('reports a WAF block honestly instead of scoring it as fine', () => {
    const site = goodSite([goodPage({ fetchClass: 'blocked', statusCode: 403, depth: 0 })]);
    expect(fired(site).has('blocked-page')).toBe(true);
    // and the fix names the exact UA to allowlist
    expect(ruleById('blocked-page')!.howToFix).toContain('MamalAudit');
  });

  it('catches a site-wide robots.txt disallow', () => {
    const site = goodSite();
    site.robotsTxt.disallowsAll = true;
    expect(fired(site).has('robots-blocks-site')).toBe(true);
  });

  it('detects a redirect loop separately from a long chain', () => {
    const loop = goodSite([goodPage({ depth: 0, redirectChain: ['https://a.com/x', 'https://a.com/y', 'https://a.com/x'] })]);
    const f = fired(loop);
    expect(f.has('redirect-loop')).toBe(true);
    expect(f.has('redirect-chain')).toBe(true);
  });

  it('finds orphans by comparing crawled pages to linked ones', () => {
    const home = goodPage({ depth: 0, url: 'https://example.com/' });
    const orphan = goodPage({ depth: 2, url: 'https://example.com/lonely', contentHash: 'h2' });
    const site = goodSite([home, orphan]);
    site.linkedUrls = new Set(['https://example.com/']);
    expect(fired(site).has('orphan-page')).toBe(true);
  });

  it('does not judge a non-200 page on its content', () => {
    const site = goodSite([goodPage({ depth: 0, statusCode: 404, title: null, metaDescription: null })]);
    const f = fired(site);
    expect(f.has('broken-page')).toBe(true);
    // A 404 with no title is not also a "missing title" problem.
    expect(f.has('missing-title')).toBe(false);
  });
});

describe('on-page', () => {
  it('catches a missing title as critical', () => {
    const site = goodSite([goodPage({ depth: 0, title: null })]);
    const { findings } = evaluateAll(site);
    const finding = findings.find((f) => f.ruleId === 'missing-title');
    expect(finding?.severity).toBe('critical');
  });

  it('reports duplicate titles once, with the offending pages', () => {
    const site = goodSite([
      goodPage({ depth: 0, url: 'https://example.com/a', contentHash: 'a' }),
      goodPage({ depth: 1, url: 'https://example.com/b', contentHash: 'b' }),
    ]);
    const { findings } = evaluateAll(site);
    const dupes = findings.filter((f) => f.ruleId === 'duplicate-title');
    expect(dupes).toHaveLength(1);
    expect((dupes[0]!.evidence as { groups: number }).groups).toBe(1);
  });

  it('detects duplicate content by body hash', () => {
    const site = goodSite([
      goodPage({ depth: 0, url: 'https://example.com/a', title: 'Page A about auditing sites' }),
      goodPage({ depth: 1, url: 'https://example.com/b', title: 'Page B about auditing sites too' }),
    ]);
    expect(fired(site).has('duplicate-content')).toBe(true);
  });

  it('respects a threshold override', () => {
    const site = goodSite([goodPage({ depth: 0, wordCount: 300 })]);
    expect(fired(site).has('thin-content')).toBe(false);
    const strict = evaluateAll(site, { 'thin-content': { thresholds: { minWords: 500 } } });
    expect(strict.findings.some((f) => f.ruleId === 'thin-content')).toBe(true);
  });

  it('honours an override that silences a rule', () => {
    const site = goodSite([goodPage({ depth: 0, title: null })]);
    const muted = evaluateAll(site, { 'missing-title': { isEnabled: false } });
    expect(muted.findings.some((f) => f.ruleId === 'missing-title')).toBe(false);
  });

  it('honours an override that changes severity', () => {
    const site = goodSite([goodPage({ depth: 0, wordCount: 10 })]);
    const raised = evaluateAll(site, { 'thin-content': { severity: 'critical' } });
    expect(raised.findings.find((f) => f.ruleId === 'thin-content')?.severity).toBe('critical');
  });
});

describe('links', () => {
  it('names which pages link to a broken target', () => {
    const home = goodPage({
      depth: 0,
      url: 'https://example.com/',
      links: [{ href: 'https://example.com/gone', anchor: 'Old', rel: null, isInternal: true }],
    });
    const site = goodSite([home]);
    site.brokenTargets = new Map([['https://example.com/gone', 404]]);

    const { findings } = evaluateAll(site);
    const broken = findings.find((f) => f.ruleId === 'broken-internal-link');
    expect(broken?.severity).toBe('critical');
    expect((broken!.evidence as { targetUrl: string }).targetUrl).toBe('https://example.com/gone');
    expect((broken!.evidence as { linkedFrom: string[] }).linkedFrom).toContain('https://example.com/');
  });

  it('flags a dead-end page', () => {
    const site = goodSite([goodPage({ depth: 0, links: [] })]);
    expect(fired(site).has('no-outgoing-links')).toBe(true);
  });
});

describe('security', () => {
  it('treats plain HTTP as critical', () => {
    const site = goodSite([goodPage({ depth: 0, isHttps: false, url: 'http://example.com/' })]);
    const { findings } = evaluateAll(site);
    expect(findings.find((f) => f.ruleId === 'not-https')?.severity).toBe('critical');
  });

  it('flags a version number in Server, not the header itself', () => {
    const bare = goodSite([goodPage({ depth: 0, headers: { ...goodPage().headers, server: 'nginx' } })]);
    expect(fired(bare).has('server-version-exposed')).toBe(false);

    const versioned = goodSite([goodPage({ depth: 0, headers: { ...goodPage().headers, server: 'nginx/1.24.0' } })]);
    expect(fired(versioned).has('server-version-exposed')).toBe(true);
  });

  it('warns before the certificate expires, not after', () => {
    const site = goodSite();
    site.sslValidTo = new Date(Date.now() + 10 * 86_400_000);
    const { findings } = evaluateAll(site);
    const ssl = findings.find((f) => f.ruleId === 'ssl-expiring');
    expect((ssl!.evidence as { daysRemaining: number }).daysRemaining).toBeLessThanOrEqual(10);
  });
});

describe('ai visibility', () => {
  it('catches a client-rendered shell that Google ranks but an answer engine cannot read', () => {
    const site = goodSite([
      goodPage({
        depth: 0,
        wordCount: 12,
        bytes: 40_000,
        scripts: [
          { src: '/a.js', defer: false, async: false, inline: false },
          { src: '/b.js', defer: false, async: false, inline: false },
          { src: '/c.js', defer: false, async: false, inline: false },
          { src: '/d.js', defer: false, async: false, inline: false },
        ],
      }),
    ]);
    const { findings } = evaluateAll(site);
    const finding = findings.find((f) => f.ruleId === 'content-not-extractable');
    expect(finding?.severity).toBe('critical');
  });

  /** A choice, not a mistake — so it is reported as info and says so. */
  it('reports blocked AI crawlers as informational with the trade-off stated', () => {
    const site = goodSite();
    site.aiCrawlers = [{ agent: 'GPTBot', allowed: false }, { agent: 'ClaudeBot', allowed: true }];
    const { findings } = evaluateAll(site);
    const finding = findings.find((f) => f.ruleId === 'ai-crawler-blocked');
    expect(finding?.severity).toBe('info');
    expect((finding!.evidence as { blocked: string[] }).blocked).toEqual(['GPTBot']);
    expect(ruleById('ai-crawler-blocked')!.howToFix).toMatch(/trade-off/i);
  });

  it('wants FAQ schema only when the page actually reads as Q&A', () => {
    const prose = goodSite([goodPage({ depth: 0 })]);
    expect(fired(prose).has('missing-faq-schema')).toBe(false);

    const qa = goodSite([
      goodPage({
        depth: 0,
        headings: [
          { level: 1, text: 'Billing help' },
          { level: 2, text: 'How do I cancel?' },
          { level: 2, text: 'Can I get a refund?' },
          { level: 2, text: 'What happens to my data?' },
        ],
      }),
    ]);
    expect(fired(qa).has('missing-faq-schema')).toBe(true);
  });

  it('flags a homepage with no Organization schema', () => {
    const site = goodSite([goodPage({ depth: 0, schemaTypes: ['Article'] })]);
    expect(fired(site).has('missing-org-schema')).toBe(true);
  });
});

describe('scoring', () => {
  it('weights critical failures far more heavily than info ones', () => {
    const base = goodSite([goodPage({ depth: 0 })]);
    const withInfo = goodSite([goodPage({ depth: 0, lang: null })]);
    const withCritical = goodSite([goodPage({ depth: 0, isHttps: false })]);

    const scoreOf = (s: ReturnType<typeof goodSite>) => {
      const { findings, ruleResults } = evaluateAll(s);
      return computeScore(ruleResults, findings).score;
    };

    expect(scoreOf(base)).toBe(100);
    expect(100 - scoreOf(withCritical)).toBeGreaterThan(100 - scoreOf(withInfo));
  });

  it('breaks the score down by category', () => {
    const site = goodSite([goodPage({ depth: 0, isHttps: false })]);
    const { findings, ruleResults } = evaluateAll(site);
    const score = computeScore(ruleResults, findings);
    const security = score.byCategory.find((c) => c.category === 'security');
    expect(security!.score).toBeLessThan(100);
    expect(score.byCategory.find((c) => c.category === 'on-page')!.score).toBe(100);
  });

  it('grades on the usual bands', () => {
    const site = goodSite([goodPage({ depth: 0, title: null, metaDescription: null, isHttps: false, headings: [] })]);
    const { findings, ruleResults } = evaluateAll(site);
    const score = computeScore(ruleResults, findings);
    expect(score.score).toBeLessThan(100);
    expect(['A', 'B', 'C', 'D', 'F']).toContain(score.grade);
  });

  it('orders fixes by severity, then by how many pages they affect', () => {
    const pages = [
      goodPage({ depth: 0, url: 'https://example.com/', lang: null }),
      goodPage({ depth: 1, url: 'https://example.com/a', lang: null, contentHash: 'a', title: 'Page A guide to auditing' }),
      goodPage({ depth: 1, url: 'https://example.com/b', isHttps: false, contentHash: 'b', title: 'Page B guide to auditing' }),
    ];
    const { findings } = evaluateAll(goodSite(pages));
    const ordered = prioritize(findings);
    expect(ordered[0]!.severity).toBe('critical');
  });
});
