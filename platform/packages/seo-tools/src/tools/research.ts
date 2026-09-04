import { fetchPage, normalizeUrl } from '@mamal/crawl';
import type { Tool, ToolOutput } from '../types.ts';

const pairs = (p: { label: string; value: string }[]): ToolOutput => ({ kind: 'pairs', pairs: p });
const URL_FIELD = {
  name: 'url', label: 'URL', type: 'url' as const, required: true,
  placeholder: 'https://example.com',
};

/**
 * Tools that make an outbound request.
 *
 * Every one goes through `fetchPage`, which resolves the host and refuses
 * private addresses — these take a URL from an anonymous visitor and fetch it
 * from inside our network, which is the textbook SSRF setup.
 */
export const researchTools: Tool[] = [
  {
    slug: 'meta-tags',
    name: 'Meta tag checker',
    category: 'research',
    description: 'Read the title, description, canonical and Open Graph tags of any page.',
    why: 'The fastest way to see what a search engine and a social preview will actually show.',
    fields: [URL_FIELD],
    fetches: true,
    run: async ({ url }) => {
      const result = await fetchPage(url ?? '');
      if (result.blocked) return { kind: 'error', message: `Could not fetch: ${result.error}` };
      if (result.status !== 200) {
        return { kind: 'error', message: `The page returned ${result.status}.` };
      }
      const head = result.body.slice(0, 100_000);
      const meta = (attr: string, value: string) =>
        head.match(new RegExp(`<meta[^>]+${attr}=["']${value}["'][^>]*content=["']([^"']*)["']`, 'i'))?.[1]
        ?? head.match(new RegExp(`<meta[^>]+content=["']([^"']*)["'][^>]*${attr}=["']${value}["']`, 'i'))?.[1];

      const title = head.match(/<title[^>]*>([\s\S]*?)<\/title>/i)?.[1]?.trim();
      const description = meta('name', 'description');

      return pairs([
        { label: 'Title', value: title ?? '(missing)' },
        { label: 'Title length', value: title ? `${title.length} characters` : '—' },
        { label: 'Description', value: description ?? '(missing)' },
        { label: 'Description length', value: description ? `${description.length} characters` : '—' },
        { label: 'Canonical', value: head.match(/<link[^>]+rel=["']canonical["'][^>]*href=["']([^"']*)["']/i)?.[1] ?? '(none)' },
        { label: 'Robots', value: meta('name', 'robots') ?? '(none — indexable by default)' },
        { label: 'Viewport', value: meta('name', 'viewport') ?? '(missing — not mobile friendly)' },
        { label: 'og:title', value: meta('property', 'og:title') ?? '(none)' },
        { label: 'og:description', value: meta('property', 'og:description') ?? '(none)' },
        { label: 'og:image', value: meta('property', 'og:image') ?? '(none)' },
      ]);
    },
  },

  {
    slug: 'http-headers',
    name: 'HTTP header checker',
    category: 'research',
    description: 'Every response header, with the security ones called out.',
    why: 'Missing security headers and absent compression are invisible in a browser but cost you on both speed and safety.',
    fields: [URL_FIELD],
    fetches: true,
    run: async ({ url }) => {
      const result = await fetchPage(url ?? '');
      if (result.blocked) return { kind: 'error', message: `Could not fetch: ${result.error}` };

      const notable = [
        'content-type', 'content-encoding', 'cache-control', 'server',
        'strict-transport-security', 'content-security-policy',
        'referrer-policy', 'x-frame-options', 'x-content-type-options',
      ];

      return {
        kind: 'table',
        columns: ['Header', 'Value'],
        rows: [
          ['status', String(result.status)],
          ...notable.map((h) => [h, result.headers[h] ?? '(not set)']),
          ...Object.entries(result.headers)
            .filter(([k]) => !notable.includes(k))
            .map(([k, v]) => [k, v]),
        ],
      };
    },
  },

  {
    slug: 'redirect-checker',
    name: 'Redirect checker',
    category: 'research',
    description: 'Follow a URL through every hop to its destination.',
    why: 'Every extra hop costs crawl budget and a little speed. Chains build up quietly after a migration.',
    fields: [URL_FIELD],
    fetches: true,
    run: async ({ url }) => {
      const result = await fetchPage(url ?? '');
      if (result.blocked) return { kind: 'error', message: `Could not fetch: ${result.error}` };

      const hops = [...result.redirectChain, result.finalUrl];
      return {
        kind: 'table',
        columns: ['#', 'URL', 'Note'],
        rows: hops.map((hop, i) => [
          i + 1,
          hop,
          i === hops.length - 1
            ? `final — ${result.status}`
            : 'redirect',
        ]),
      };
    },
  },

  {
    slug: 'robots-fetcher',
    name: 'robots.txt viewer',
    category: 'research',
    description: 'Read a site’s robots.txt, including which AI crawlers it blocks.',
    why: 'Blocking GPTBot or ClaudeBot is a legitimate choice — but it should be a choice, not something a plugin did for you.',
    fields: [{ ...URL_FIELD, label: 'Website', placeholder: 'example.com' }],
    fetches: true,
    run: async ({ url }) => {
      const normalized = normalizeUrl(/^https?:\/\//i.test(url ?? '') ? url! : `https://${url}`);
      if (!normalized) return { kind: 'error', message: 'That is not a valid address.' };

      const robotsUrl = new URL('/robots.txt', normalized).toString();
      const result = await fetchPage(robotsUrl);
      if (result.blocked) return { kind: 'error', message: `Could not fetch: ${result.error}` };
      if (result.status !== 200) {
        return { kind: 'error', message: `No robots.txt found (${result.status}). Crawlers will assume everything is allowed.` };
      }

      const body = result.body;
      const aiAgents = ['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended', 'CCBot'];
      const blocked = aiAgents.filter((agent) => {
        const group = body.match(
          new RegExp(`user-agent:\\s*${agent}[\\s\\S]*?(?=user-agent:|$)`, 'i'),
        )?.[0];
        return group ? /^\s*disallow:\s*\/\s*$/im.test(group) : false;
      });

      return pairs([
        { label: 'Sitemaps', value: [...body.matchAll(/sitemap:\s*(\S+)/gi)].map((m) => m[1]).join('\n') || '(none declared)' },
        { label: 'Blocks everything', value: /user-agent:\s*\*/i.test(body) && /^\s*disallow:\s*\/\s*$/im.test(body) ? 'YES — the site is hidden from search' : 'no' },
        { label: 'AI crawlers blocked', value: blocked.length ? blocked.join(', ') : 'none' },
        { label: 'Contents', value: body.slice(0, 2000) },
      ]);
    },
  },

  {
    slug: 'ssl-checker',
    name: 'HTTPS checker',
    category: 'research',
    description: 'Confirm a site serves HTTPS and redirects from HTTP.',
    why: 'Browsers mark HTTP pages as not secure, and an HTTP page that never redirects splits your ranking signals across two URLs.',
    fields: [{ ...URL_FIELD, label: 'Website', placeholder: 'example.com' }],
    fetches: true,
    run: async ({ url }) => {
      const host = (url ?? '').replace(/^https?:\/\//i, '').split('/')[0];
      if (!host) return { kind: 'error', message: 'That is not a valid address.' };

      const [secure, insecure] = await Promise.all([
        fetchPage(`https://${host}/`),
        fetchPage(`http://${host}/`),
      ]);

      const redirectsToHttps =
        insecure.finalUrl.startsWith('https:') || insecure.redirectChain.some((h) => h.startsWith('https:'));

      return pairs([
        { label: 'HTTPS works', value: secure.status > 0 && !secure.blocked ? `yes — ${secure.status}` : `no — ${secure.error ?? 'unreachable'}` },
        { label: 'HTTP redirects to HTTPS', value: redirectsToHttps ? 'yes' : 'NO — fix this first' },
        { label: 'HSTS header', value: secure.headers['strict-transport-security'] ?? '(not set)' },
        { label: 'Final URL', value: secure.finalUrl },
      ]);
    },
  },
];
