import { describe, expect, it } from 'vitest';
import { ALL_TOOLS, isRateLimited, toolBySlug } from '../registry.ts';
import type { ToolOutput } from '../types.ts';

const run = async (slug: string, input: Record<string, string>): Promise<ToolOutput> => {
  const tool = toolBySlug(slug);
  if (!tool) throw new Error(`no tool ${slug}`);
  return tool.run(input);
};

const textOf = (out: ToolOutput) => (out.kind === 'text' ? out.value : '');
const pairOf = (out: ToolOutput, label: string) =>
  out.kind === 'pairs' ? out.pairs.find((p) => p.label === label)?.value : undefined;

describe('registry', () => {
  it('has no duplicate slugs', () => {
    expect(new Set(ALL_TOOLS.map((t) => t.slug)).size).toBe(ALL_TOOLS.length);
  });

  it('every tool explains why someone would use it', () => {
    for (const tool of ALL_TOOLS) {
      expect(tool.why.length, `${tool.slug} has no rationale`).toBeGreaterThan(30);
      expect(tool.fields.length, `${tool.slug} has no inputs`).toBeGreaterThan(0);
    }
  });

  /** Only fetching tools need rate limiting; the pure ones cost nothing. */
  it('marks exactly the tools that make outbound requests', () => {
    expect(isRateLimited('meta-tags')).toBe(true);
    expect(isRateLimited('word-counter')).toBe(false);
    expect(isRateLimited('base64')).toBe(false);
  });
});

describe('content tools', () => {
  it('counts words, sentences and reading time', async () => {
    const out = await run('word-counter', {
      text: 'One two three. Four five six seven! Eight?',
    });
    expect(pairOf(out, 'Words')).toBe('8');
    expect(pairOf(out, 'Sentences')).toBe('3');
    expect(pairOf(out, 'Reading time')).toBe('1 min');
  });

  it('handles empty input without dividing by zero', async () => {
    const out = await run('word-counter', { text: '   ' });
    expect(pairOf(out, 'Words')).toBe('0');
    expect(pairOf(out, 'Average sentence')).toBe('—');
  });

  it('excludes stop words from density, or "the" tops every report', async () => {
    const out = await run('keyword-density', {
      text: 'The audit found the issue. The audit engine reports the audit findings clearly.',
    });
    expect(out.kind).toBe('table');
    const rows = out.kind === 'table' ? out.rows : [];
    expect(rows[0]![0]).toBe('audit');
    expect(rows.map((r) => r[0])).not.toContain('the');
  });

  it('strips accents rather than dropping the letter', async () => {
    expect(textOf(await run('slug-generator', { text: 'Café Münster — Best Brunch!' })))
      .toBe('cafe-munster-best-brunch');
  });

  it('converts case in both directions', async () => {
    const out = await run('case-converter', { text: 'technicalSeoAudit' });
    expect(pairOf(out, 'kebab-case')).toBe('technical-seo-audit');
    expect(pairOf(out, 'Title Case')).toBe('Technical Seo Audit');
  });

  it('normalises the punctuation a word processor drags in', async () => {
    const cleaned = textOf(await run('text-cleaner', {
      text: '<p>It’s a “test”—really</p>',
    }));
    expect(cleaned).toContain("It's");
    expect(cleaned).toContain('"test"');
    expect(cleaned).not.toContain('<p>');
  });
});

describe('developer tools', () => {
  it('builds a UTM URL, preserving existing query parameters', async () => {
    const out = textOf(await run('utm-builder', {
      url: 'https://example.com/pricing?plan=pro',
      source: 'newsletter', medium: 'email', campaign: 'spring',
    }));
    expect(out).toContain('plan=pro');
    expect(out).toContain('utm_source=newsletter');
    expect(out).toContain('utm_campaign=spring');
  });

  it('rejects a malformed URL rather than producing nonsense', async () => {
    const out = await run('utm-builder', { url: 'not a url', source: 'x', medium: 'y' });
    expect(out.kind).toBe('error');
  });

  it('round-trips base64', async () => {
    const encoded = textOf(await run('base64', { text: 'héllo wörld', mode: 'encode' }));
    expect(textOf(await run('base64', { text: encoded, mode: 'decode' }))).toBe('héllo wörld');
  });

  it('reports the JSON error rather than just failing', async () => {
    const out = await run('json-validator', { text: '{"a": 1,}' });
    expect(out.kind).toBe('error');
    expect(out.kind === 'error' && out.message.length).toBeGreaterThan(5);
  });

  describe('robots.txt tester', () => {
    const robots = 'User-agent: *\nDisallow: /admin/\nAllow: /admin/public/';

    it('blocks a disallowed path', async () => {
      expect(pairOf(await run('robots-tester', { robots, path: '/admin/settings' }), 'Verdict'))
        .toBe('Blocked');
    });

    /** Longest match wins — how real crawlers resolve conflicting rules. */
    it('lets a more specific Allow beat a broader Disallow', async () => {
      expect(pairOf(await run('robots-tester', { robots, path: '/admin/public/docs' }), 'Verdict'))
        .toBe('Allowed');
    });

    it('allows anything no rule matches', async () => {
      const out = await run('robots-tester', { robots, path: '/blog/post' });
      expect(pairOf(out, 'Verdict')).toBe('Allowed');
      expect(pairOf(out, 'Matching rule')).toContain('none');
    });

    it('honours a rule written for a specific agent', async () => {
      const specific = 'User-agent: GPTBot\nDisallow: /';
      expect(pairOf(await run('robots-tester', { robots: specific, path: '/any', agent: 'GPTBot' }), 'Verdict'))
        .toBe('Blocked');
    });
  });

  it('shows where a title gets truncated and by how much', async () => {
    const long = 'A'.repeat(75);
    const out = await run('serp-preview', { title: long, description: 'short' });
    expect(pairOf(out, 'Title shown')).toHaveLength(61); // 60 + ellipsis
    expect(pairOf(out, 'Title length')).toContain('15 over');
  });

  it('says plainly when a description is missing', async () => {
    const out = await run('serp-preview', { title: 'Fine', description: '' });
    expect(pairOf(out, 'Description shown')).toContain('invent one');
  });
});
