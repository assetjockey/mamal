import { createHash, randomUUID } from 'node:crypto';
import type { Tool, ToolOutput } from '../types.ts';

const text = (value: string): ToolOutput => ({ kind: 'text', value });
const pairs = (p: { label: string; value: string }[]): ToolOutput => ({ kind: 'pairs', pairs: p });

export const developerTools: Tool[] = [
  {
    slug: 'utm-builder',
    name: 'UTM builder',
    category: 'development',
    description: 'Build a campaign-tagged URL.',
    why: 'Without UTM tags, analytics files paid, social and email traffic under one meaningless bucket.',
    fields: [
      { name: 'url', label: 'Destination URL', type: 'url', required: true, placeholder: 'https://example.com/pricing' },
      { name: 'source', label: 'Source', type: 'text', required: true, placeholder: 'newsletter', hint: 'Where the traffic comes from.' },
      { name: 'medium', label: 'Medium', type: 'text', required: true, placeholder: 'email', hint: 'The channel type.' },
      { name: 'campaign', label: 'Campaign', type: 'text', placeholder: 'spring-launch' },
      { name: 'content', label: 'Content', type: 'text', placeholder: 'header-cta', hint: 'Which link, when several point at the same page.' },
    ],
    run: (input) => {
      try {
        const url = new URL(input.url ?? '');
        for (const [key, param] of [
          ['source', 'utm_source'], ['medium', 'utm_medium'],
          ['campaign', 'utm_campaign'], ['content', 'utm_content'],
        ] as const) {
          const value = input[key]?.trim();
          if (value) url.searchParams.set(param, value);
        }
        return text(url.toString());
      } catch {
        return { kind: 'error', message: 'That is not a valid URL.' };
      }
    },
  },

  {
    slug: 'url-parser',
    name: 'URL parser',
    category: 'development',
    description: 'Break a URL into its parts.',
    why: 'Useful when a long tracking URL is misbehaving and you need to see exactly what is in it.',
    fields: [{ name: 'url', label: 'URL', type: 'url', required: true, placeholder: 'https://example.com/path?a=1#top' }],
    run: (input) => {
      try {
        const url = new URL(input.url ?? '');
        const params = [...url.searchParams.entries()];
        return pairs([
          { label: 'Protocol', value: url.protocol.replace(':', '') },
          { label: 'Host', value: url.hostname },
          { label: 'Port', value: url.port || '(default)' },
          { label: 'Path', value: url.pathname },
          { label: 'Fragment', value: url.hash.replace('#', '') || '(none)' },
          ...params.map(([k, v]) => ({ label: `?${k}`, value: v })),
        ]);
      } catch {
        return { kind: 'error', message: 'That is not a valid URL.' };
      }
    },
  },

  {
    slug: 'base64',
    name: 'Base64 encoder / decoder',
    category: 'development',
    description: 'Encode or decode Base64.',
    why: 'Turns up constantly in auth headers, data URIs and webhook payloads.',
    fields: [
      { name: 'text', label: 'Input', type: 'textarea', required: true },
      {
        name: 'mode', label: 'Direction', type: 'select',
        options: [{ value: 'encode', label: 'Encode' }, { value: 'decode', label: 'Decode' }],
      },
    ],
    run: ({ text: input, mode }) => {
      try {
        return mode === 'decode'
          ? text(Buffer.from(input ?? '', 'base64').toString('utf8'))
          : text(Buffer.from(input ?? '', 'utf8').toString('base64'));
      } catch {
        return { kind: 'error', message: 'That is not valid Base64.' };
      }
    },
  },

  {
    slug: 'hash-generator',
    name: 'Hash generator',
    category: 'development',
    description: 'MD5, SHA-1, SHA-256 and SHA-512.',
    why: 'For verifying a file downloaded intact, building a cache key, or checking a webhook signature by hand.',
    fields: [{ name: 'text', label: 'Input', type: 'textarea', required: true }],
    run: ({ text: input }) =>
      pairs(
        ['md5', 'sha1', 'sha256', 'sha512'].map((algo) => ({
          label: algo.toUpperCase(),
          value: createHash(algo).update(input ?? '').digest('hex'),
        })),
      ),
  },

  {
    slug: 'uuid-generator',
    name: 'UUID generator',
    category: 'development',
    description: 'Generate random UUIDs.',
    why: 'For seeding test fixtures, or generating identifiers you need before a database round-trip.',
    fields: [{ name: 'count', label: 'How many', type: 'number', placeholder: '5' }],
    run: ({ count }) => {
      const n = Math.min(50, Math.max(1, Number(count) || 5));
      return text(Array.from({ length: n }, () => randomUUID()).join('\n'));
    },
  },

  {
    slug: 'json-validator',
    name: 'JSON validator',
    category: 'development',
    description: 'Check JSON is valid and pretty-print it.',
    why: 'A single trailing comma makes a JSON-LD block invalid, and search engines discard it silently — which is exactly the failure the audit engine reports.',
    fields: [{ name: 'text', label: 'JSON', type: 'textarea', required: true }],
    run: ({ text: input }) => {
      try {
        return text(JSON.stringify(JSON.parse(input ?? ''), null, 2));
      } catch (err) {
        return {
          kind: 'error',
          message: err instanceof Error ? err.message : 'Invalid JSON.',
        };
      }
    },
  },

  {
    slug: 'robots-tester',
    name: 'robots.txt tester',
    category: 'development',
    description: 'Check whether a robots.txt rule blocks a path.',
    why: 'One stray `Disallow: /` removes a site from search. Test before you deploy.',
    fields: [
      {
        name: 'robots', label: 'robots.txt', type: 'textarea', required: true,
        placeholder: 'User-agent: *\nDisallow: /admin/',
      },
      { name: 'path', label: 'Path to test', type: 'text', required: true, placeholder: '/admin/settings' },
      { name: 'agent', label: 'User agent', type: 'text', placeholder: 'Googlebot' },
    ],
    run: ({ robots, path, agent }) => {
      const ua = (agent || '*').toLowerCase();
      const lines = (robots ?? '').split('\n').map((l) => l.trim());
      const target = path || '/';

      // Walk the groups matching this agent, then apply longest-match-wins,
      // which is how the real crawlers resolve conflicting rules.
      let inGroup = false;
      const rules: { allow: boolean; pattern: string }[] = [];
      for (const line of lines) {
        const [rawKey, ...rest] = line.split(':');
        if (!rawKey || rest.length === 0) continue;
        const key = rawKey.trim().toLowerCase();
        const value = rest.join(':').trim();
        if (key === 'user-agent') {
          inGroup = value === '*' || value.toLowerCase() === ua;
        } else if (inGroup && (key === 'allow' || key === 'disallow')) {
          rules.push({ allow: key === 'allow', pattern: value });
        }
      }

      const matches = rules.filter(
        (r) => r.pattern !== '' && target.startsWith(r.pattern.replace(/\*$/, '')),
      );
      const winner = matches.sort((a, b) => b.pattern.length - a.pattern.length)[0];
      const allowed = !winner || winner.allow;

      return pairs([
        { label: 'Path', value: target },
        { label: 'User agent', value: agent || '*' },
        { label: 'Verdict', value: allowed ? 'Allowed' : 'Blocked' },
        { label: 'Matching rule', value: winner ? `${winner.allow ? 'Allow' : 'Disallow'}: ${winner.pattern}` : '(none — allowed by default)' },
      ]);
    },
  },

  {
    slug: 'serp-preview',
    name: 'Search result preview',
    category: 'development',
    description: 'See how a title and description will be truncated.',
    why: 'Anything past the cut-off is invisible in the result, so the important words have to come first.',
    fields: [
      { name: 'title', label: 'Title', type: 'text', required: true },
      { name: 'description', label: 'Meta description', type: 'textarea' },
      { name: 'url', label: 'URL', type: 'url', placeholder: 'https://example.com/page' },
    ],
    run: ({ title, description, url }) => {
      const TITLE_LIMIT = 60;
      const DESC_LIMIT = 158;
      const t = title ?? '';
      const d = description ?? '';
      return pairs([
        { label: 'Title shown', value: t.length > TITLE_LIMIT ? `${t.slice(0, TITLE_LIMIT)}…` : t },
        {
          label: 'Title length',
          value: `${t.length} / ${TITLE_LIMIT}${t.length > TITLE_LIMIT ? ` — ${t.length - TITLE_LIMIT} over` : ''}`,
        },
        { label: 'URL shown', value: url ?? '(none)' },
        { label: 'Description shown', value: d.length > DESC_LIMIT ? `${d.slice(0, DESC_LIMIT)}…` : d || '(none — search engines will invent one)' },
        {
          label: 'Description length',
          value: `${d.length} / ${DESC_LIMIT}${d.length > DESC_LIMIT ? ` — ${d.length - DESC_LIMIT} over` : ''}`,
        },
      ]);
    },
  },
];
