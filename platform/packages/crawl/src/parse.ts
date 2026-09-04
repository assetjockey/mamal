import { createHash } from 'node:crypto';
import type { PageFacts } from '@mamal/seo-checks';
import { normalizeUrl, sameSite } from './url.ts';

/**
 * HTML → PageFacts.
 *
 * Regex parsing rather than a DOM library, deliberately: this runs on every
 * page of every crawl, and we need a fixed set of facts rather than arbitrary
 * traversal. crawlseo's engine takes the same approach for the same reason.
 */

export type FetchResult = {
  url: string;
  finalUrl: string;
  status: number;
  headers: Record<string, string>;
  body: string;
  redirectChain: string[];
  ttfbMs: number;
  responseMs: number;
  httpVersion: string | null;
  blocked: boolean;
  error?: string;
};

export function parsePage(
  fetched: FetchResult,
  context: { depth: number; inSitemap: boolean; origin: string },
): PageFacts {
  const { body, headers } = fetched;
  const head = body.slice(0, 200_000);

  const title = text(match(head, /<title[^>]*>([\s\S]*?)<\/title>/i));
  const metaDescription = attr(head, 'meta', 'name', 'description', 'content');
  const robotsMeta = attr(head, 'meta', 'name', 'robots', 'content');
  const xRobotsTag = headers['x-robots-tag'] ?? null;
  const canonical = absolute(attr(head, 'link', 'rel', 'canonical', 'href'), fetched.finalUrl);
  const headerCanonical = parseLinkHeader(headers.link ?? '', 'canonical');

  const headings = [...body.matchAll(/<h([1-6])[^>]*>([\s\S]*?)<\/h\1>/gi)].map((m) => ({
    level: Number(m[1]),
    text: text(m[2]) ?? '',
  }));

  const bodyText = stripTags(body);
  const wordCount = bodyText ? bodyText.split(/\s+/).filter(Boolean).length : 0;
  const textRatio = body.length > 0 ? (bodyText.length / body.length) * 100 : 0;

  const images = [...body.matchAll(/<img\b[^>]*>/gi)].map((m) => {
    const tag = m[0];
    const src = attrOf(tag, 'src') ?? attrOf(tag, 'data-src') ?? '';
    return {
      src,
      // A missing alt is a failure; alt="" is a deliberate "decorative".
      alt: /\balt\s*=/i.test(tag) ? (attrOf(tag, 'alt') ?? '') : null,
      loading: attrOf(tag, 'loading'),
      format: (src.split('?')[0]?.split('.').pop() ?? '').toLowerCase(),
    };
  });

  const links = [...body.matchAll(/<a\b[^>]*href\s*=\s*["']([^"']+)["'][^>]*>([\s\S]*?)<\/a>/gi)]
    .map((m) => {
      const href = absolute(m[1]!, fetched.finalUrl);
      if (!href) return null;
      return {
        href,
        anchor: text(m[2]) ?? '',
        rel: attrOf(m[0], 'rel'),
        isInternal: sameSite(href, context.origin),
      };
    })
    .filter((l): l is NonNullable<typeof l> => l !== null && /^https?:/.test(l.href));

  const scripts = [...body.matchAll(/<script\b([^>]*)>/gi)].map((m) => {
    const tag = m[1] ?? '';
    const src = attrOf(`<script ${tag}>`, 'src');
    return { src, defer: /\bdefer\b/i.test(tag), async: /\basync\b/i.test(tag), inline: !src };
  });

  const schemaTypes = extractSchemaTypes(body);
  const isHttps = fetched.finalUrl.startsWith('https:');

  return {
    url: fetched.finalUrl,
    statusCode: fetched.status,
    fetchClass: fetched.blocked
      ? 'blocked'
      : fetched.error
        ? 'error'
        : fetched.redirectChain.length > 0
          ? 'redirect'
          : 'ok',
    redirectChain: fetched.redirectChain,
    isHttps,
    depth: context.depth,
    inSitemap: context.inSitemap,

    title,
    metaDescription,
    canonical,
    headerCanonical,
    robotsMeta,
    xRobotsTag,
    isIndexable:
      fetched.status === 200 && !/noindex/i.test(`${robotsMeta ?? ''} ${xRobotsTag ?? ''}`),
    lang: attrOf(head.match(/<html\b[^>]*>/i)?.[0] ?? '', 'lang'),
    ogTitle: attr(head, 'meta', 'property', 'og:title', 'content'),
    ogDescription: attr(head, 'meta', 'property', 'og:description', 'content'),
    ogImage: attr(head, 'meta', 'property', 'og:image', 'content'),

    headings,
    wordCount,
    textRatio,
    contentHash: bodyText ? createHash('sha256').update(bodyText.replace(/\s+/g, ' ').trim()).digest('hex') : null,

    images,
    links,
    scripts,
    inlineStyleCount: (body.match(/\sstyle\s*=/gi) ?? []).length,
    deprecatedTags: [...body.matchAll(/<(center|font|marquee|blink|big|strike|tt|frame|frameset|applet)\b/gi)]
      .map((m) => m[1]!.toLowerCase()),
    forms: [...body.matchAll(/<form\b[^>]*>/gi)].map((m) => {
      const action = attrOf(m[0], 'action');
      const resolved = action ? absolute(action, fetched.finalUrl) : fetched.finalUrl;
      return {
        action: resolved,
        method: (attrOf(m[0], 'method') ?? 'get').toLowerCase(),
        // A form on an HTTPS page posting to HTTP is the actual danger.
        isSecure: resolved ? resolved.startsWith('https:') : isHttps,
      };
    }),

    schemaTypes,
    hreflang: [...head.matchAll(/<link\b[^>]*hreflang\s*=\s*["']([^"']+)["'][^>]*>/gi)].map((m) => ({
      lang: m[1]!,
      href: absolute(attrOf(m[0], 'href') ?? '', fetched.finalUrl) ?? '',
    })),
    hasViewport: /<meta[^>]+name\s*=\s*["']viewport["']/i.test(head),
    hasCharset: /<meta[^>]+charset/i.test(head),
    hasDoctype: /^\s*<!doctype\s+html/i.test(body),
    hasFavicon: /<link[^>]+rel\s*=\s*["'][^"']*icon/i.test(head),
    plaintextEmails: [...new Set(
      (bodyText.match(/[\w.+-]+@[\w-]+\.[\w.]{2,}/g) ?? []).slice(0, 10),
    )],

    responseMs: fetched.responseMs,
    ttfbMs: fetched.ttfbMs,
    bytes: Buffer.byteLength(body, 'utf8'),
    domNodes: (body.match(/<[a-z][^>]*>/gi) ?? []).length,
    httpVersion: fetched.httpVersion,
    compression: headers['content-encoding'] ?? null,
    headers,
    mixedContent: isHttps
      ? [...new Set(
          [...body.matchAll(/(?:src|href)\s*=\s*["'](http:\/\/[^"']+)["']/gi)].map((m) => m[1]!),
        )].slice(0, 20)
      : [],
    requestCount: images.length + scripts.filter((s) => s.src).length +
      (head.match(/<link[^>]+rel\s*=\s*["']stylesheet["']/gi) ?? []).length,
  };
}

// ---------------------------------------------------------------- primitives

function match(source: string, re: RegExp): string | null {
  return source.match(re)?.[1] ?? null;
}

function text(raw: string | null | undefined): string | null {
  if (!raw) return null;
  const cleaned = raw
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/\s+/g, ' ')
    .trim();
  return cleaned || null;
}

function attrOf(tag: string, name: string): string | null {
  const m = tag.match(new RegExp(`\\b${name}\\s*=\\s*["']([^"']*)["']`, 'i'));
  return m ? m[1]! : null;
}

/** Find `<tag key="value" ... take="...">`, e.g. meta[name=description][content]. */
function attr(html: string, tag: string, key: string, value: string, take: string): string | null {
  const re = new RegExp(`<${tag}\\b[^>]*\\b${key}\\s*=\\s*["']${escape(value)}["'][^>]*>`, 'i');
  const found = html.match(re);
  return found ? text(attrOf(found[0], take)) : null;
}

const escape = (s: string) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

function absolute(href: string | null, base: string): string | null {
  if (!href) return null;
  return normalizeUrl(href, base);
}

function stripTags(html: string): string {
  return html
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<noscript[\s\S]*?<\/noscript>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function parseLinkHeader(header: string, rel: string): string | null {
  for (const part of header.split(',')) {
    const m = part.match(/<([^>]+)>\s*;\s*rel\s*=\s*"?([^";]+)"?/);
    if (m && m[2]?.trim() === rel) return m[1]!.trim();
  }
  return null;
}

/**
 * JSON-LD types. A block that fails to parse yields `__invalid__`, which the
 * `invalid-schema` rule reports — silently ignoring it is how sites end up
 * believing they have markup that search engines discard.
 */
function extractSchemaTypes(html: string): string[] {
  const types = new Set<string>();
  for (const m of html.matchAll(
    /<script\b[^>]*type\s*=\s*["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi,
  )) {
    try {
      const parsed = JSON.parse(m[1]!.trim());
      collectTypes(parsed, types);
    } catch {
      types.add('__invalid__');
    }
  }
  if (/\bitemtype\s*=\s*["']https?:\/\/schema\.org\/(\w+)/i.test(html)) {
    const micro = html.match(/\bitemtype\s*=\s*["']https?:\/\/schema\.org\/(\w+)/i);
    if (micro) types.add(micro[1]!);
  }
  return [...types];
}

function collectTypes(node: unknown, into: Set<string>): void {
  if (Array.isArray(node)) {
    for (const item of node) collectTypes(item, into);
    return;
  }
  if (!node || typeof node !== 'object') return;
  const record = node as Record<string, unknown>;
  const type = record['@type'];
  if (typeof type === 'string') into.add(type);
  if (Array.isArray(type)) for (const t of type) if (typeof t === 'string') into.add(t);
  for (const value of Object.values(record)) {
    if (value && typeof value === 'object') collectTypes(value, into);
  }
}
