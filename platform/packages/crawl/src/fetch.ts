import { assertPublicUrl, BlockedUrl } from './url.ts';
import type { FetchResult } from './parse.ts';

export const DEFAULT_USER_AGENT =
  'Mozilla/5.0 (compatible; MamalAudit/1.0; +https://mamal.dev/bot)';

const MAX_BYTES = 10 * 1024 * 1024;
const MAX_REDIRECTS = 5;

/** Statuses that mean "a bot wall refused us", not "this page is broken". */
const BOT_WALL = new Set([401, 403, 405, 406, 429, 503]);

export type FetchOptions = {
  userAgent?: string;
  timeoutMs?: number;
  allowPrivate?: boolean;
  headers?: Record<string, string>;
  basicAuth?: { username: string; password: string };
};

export async function fetchPage(rawUrl: string, opts: FetchOptions = {}): Promise<FetchResult> {
  const started = Date.now();
  const redirectChain: string[] = [];
  let current = rawUrl;

  for (let hop = 0; hop <= MAX_REDIRECTS; hop++) {
    let url: URL;
    try {
      url = await assertPublicUrl(current, { allowPrivate: opts.allowPrivate ?? false });
    } catch (err) {
      return blocked(rawUrl, current, redirectChain, started, err instanceof BlockedUrl ? err.reason : 'blocked');
    }

    const headers: Record<string, string> = {
      'user-agent': opts.userAgent ?? DEFAULT_USER_AGENT,
      accept: 'text/html,application/xhtml+xml',
      'accept-encoding': 'gzip, deflate, br',
      ...opts.headers,
    };
    if (opts.basicAuth) {
      const token = Buffer.from(`${opts.basicAuth.username}:${opts.basicAuth.password}`).toString('base64');
      headers.authorization = `Basic ${token}`;
    }

    let response: Response;
    try {
      response = await fetch(url, {
        headers,
        redirect: 'manual',
        signal: AbortSignal.timeout(opts.timeoutMs ?? 15_000),
      });
    } catch (err) {
      return {
        url: rawUrl, finalUrl: current, status: 0, headers: {}, body: '',
        redirectChain, ttfbMs: Date.now() - started, responseMs: Date.now() - started,
        httpVersion: null, blocked: false,
        error: err instanceof Error ? err.message : 'fetch failed',
      };
    }

    const ttfbMs = Date.now() - started;
    const responseHeaders = Object.fromEntries(
      [...response.headers.entries()].map(([k, v]) => [k.toLowerCase(), v]),
    );

    if (response.status >= 300 && response.status < 400) {
      const location = response.headers.get('location');
      if (!location) break;
      redirectChain.push(current);
      current = new URL(location, current).toString();
      continue;
    }

    // Read with a cap: a 2GB "HTML" response should not take the worker down.
    const body = await readCapped(response);

    return {
      url: rawUrl,
      finalUrl: current,
      status: response.status,
      headers: responseHeaders,
      body,
      redirectChain,
      ttfbMs,
      responseMs: Date.now() - started,
      httpVersion: null,
      /**
       * A bot wall with no HTML is reported as blocked rather than as a broken
       * page. Conflating the two is why cloud crawlers get accused of lying.
       */
      blocked: BOT_WALL.has(response.status) && body.length < 2000,
    };
  }

  return blocked(rawUrl, current, redirectChain, started, 'too many redirects');
}

async function readCapped(response: Response): Promise<string> {
  if (!response.body) return '';
  const reader = response.body.getReader();
  const chunks: Uint8Array[] = [];
  let total = 0;
  while (total < MAX_BYTES) {
    const { done, value } = await reader.read();
    if (done) break;
    chunks.push(value);
    total += value.length;
  }
  void reader.cancel().catch(() => {});
  return Buffer.concat(chunks).toString('utf8');
}

function blocked(
  url: string, finalUrl: string, redirectChain: string[], started: number, reason: string,
): FetchResult {
  return {
    url, finalUrl, status: 0, headers: {}, body: '', redirectChain,
    ttfbMs: Date.now() - started, responseMs: Date.now() - started,
    httpVersion: null, blocked: true, error: reason,
  };
}
