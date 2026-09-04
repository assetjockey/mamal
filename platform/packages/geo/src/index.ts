import type { VisitorContext } from '@mamal/targeting';

/**
 * Who is on the other end of a request, from the headers alone.
 *
 * This runs on the redirect path, which has a p99 budget measured in
 * milliseconds, so it is a handful of regex tests and no dependency. It is
 * deliberately *not* a full UA database: the question a targeting rule asks is
 * "iOS or Android, phone or desktop, which browser" — not "Chrome 131.0.6778 on
 * Windows 11 22H2". A library that answers the second costs a megabyte and
 * several milliseconds to answer the first.
 *
 * **Nothing here is used for identity.** The visitor hash is salted and rotates
 * daily; these fields exist to route a link and to fill in a report, and the IP
 * they were derived from is never stored.
 */

export type Device = 'desktop' | 'mobile' | 'tablet';

export type Client = {
  device: Device;
  os: string;
  browser: string;
  isBot: boolean;
};

/*
 * Bots first, and generously.
 *
 * A crawler that follows a link must not be counted as a click, must not
 * consume a click limit, and must not be assigned an A/B arm — one aggressive
 * crawler otherwise ends a test with a result that is entirely its own traffic.
 * False positives here cost a row in a report; false negatives corrupt the data
 * everything else is judged on.
 */
const BOT = /bot|crawler|spider|crawling|slurp|facebookexternalhit|preview|fetcher|monitor|curl|wget|python-requests|headless|lighthouse|pingdom|uptime|axios|okhttp|postman|scrapy|semrush|ahrefs|mj12|dotbot|bytespider|gptbot|claudebot|perplexity|ccbot/i;

/** Order matters throughout: every one of these is a substring of a later one. */
const OS_TESTS: [RegExp, string][] = [
  [/windows phone/i, 'Windows Phone'],
  [/windows nt|win64|win32/i, 'Windows'],
  // iPadOS 13+ reports itself as Macintosh, so touch support is the tell.
  [/iphone|ipod/i, 'iOS'],
  [/ipad/i, 'iPadOS'],
  [/android/i, 'Android'],
  [/cros/i, 'ChromeOS'],
  [/mac os x|macintosh/i, 'macOS'],
  [/linux/i, 'Linux'],
];

const BROWSER_TESTS: [RegExp, string][] = [
  // Every one of these also contains "Chrome" or "Safari", so they go first.
  [/edg(?:e|a|ios)?\//i, 'Edge'],
  [/opr\/|opera/i, 'Opera'],
  [/samsungbrowser/i, 'Samsung Internet'],
  [/ucbrowser/i, 'UC Browser'],
  [/yabrowser/i, 'Yandex'],
  [/firefox|fxios/i, 'Firefox'],
  [/crios|chrome|chromium/i, 'Chrome'],
  [/safari/i, 'Safari'],
];

export function parseClient(userAgent: string | null | undefined): Client {
  const ua = userAgent ?? '';
  if (!ua) return { device: 'desktop', os: 'Unknown', browser: 'Unknown', isBot: false };
  if (BOT.test(ua)) return { device: 'desktop', os: 'Unknown', browser: 'Bot', isBot: true };

  let os = 'Unknown';
  for (const [re, name] of OS_TESTS) {
    if (re.test(ua)) { os = name; break; }
  }

  let browser = 'Unknown';
  for (const [re, name] of BROWSER_TESTS) {
    if (re.test(ua)) { browser = name; break; }
  }

  /*
   * Tablet before mobile.
   *
   * "Android; Tablet" also contains "Android", and an iPad that reports itself
   * as a Mac has neither. Getting this wrong sends tablet users to the mobile
   * app store, which is a support ticket rather than a crash — and therefore
   * the kind of bug that survives for years.
   */
  const device: Device =
    /ipad|tablet|playbook|silk|(android(?!.*mobile))/i.test(ua) || os === 'iPadOS'
      ? 'tablet'
      : /mobi|iphone|ipod|windows phone|blackberry|opera mini/i.test(ua)
        ? 'mobile'
        : 'desktop';

  return { device, os, browser, isBot: false };
}

/**
 * The visitor's language, from `Accept-Language`.
 *
 * Only the primary subtag — a rule targets "German speakers", not `de-AT`
 * specifically, and quality values are stripped rather than ranked because the
 * first entry is the browser's own preference in every implementation.
 */
export function parseLanguage(header: string | null | undefined): string | undefined {
  const first = (header ?? '').split(',')[0]?.trim().split(';')[0]?.trim();
  if (!first || first === '*') return undefined;
  return first.split('-')[0]!.toLowerCase();
}

/** Continent, from a two-letter country code. Enough for a targeting rule. */
const CONTINENTS: Record<string, string> = {
  AF: 'AF', AS: 'AS', EU: 'EU', NA: 'NA', SA: 'SA', OC: 'OC', AN: 'AN',
};

export type EdgeGeo = {
  country?: string;
  region?: string;
  city?: string;
  continent?: string;
};

/**
 * Geo from the CDN's own headers, never from a lookup we perform.
 *
 * Cloudflare and Vercel both attach this to the request; a MaxMind lookup on
 * the redirect path would cost more than the redirect itself. When the headers
 * are absent — a direct origin hit, or local development — the fields are
 * simply undefined, and every targeting rule that reads them fails closed.
 */
export function parseGeo(headers: Headers): EdgeGeo {
  const country = headers.get('cf-ipcountry') ?? headers.get('x-vercel-ip-country') ?? undefined;
  const region = headers.get('cf-region-code') ?? headers.get('x-vercel-ip-country-region') ?? undefined;
  const city = decodeHeader(headers.get('cf-ipcity') ?? headers.get('x-vercel-ip-city'));
  const continent = headers.get('cf-ipcontinent') ?? headers.get('x-vercel-ip-continent') ?? undefined;

  return {
    // `XX` is Cloudflare's "unknown", and `T1` is Tor. Neither is a country.
    country: country && country !== 'XX' && country !== 'T1' ? country.toUpperCase() : undefined,
    region: region || undefined,
    city: city || undefined,
    continent: continent && CONTINENTS[continent.toUpperCase()] ? continent.toUpperCase() : undefined,
  };
}

/** Vercel percent-encodes city names; Cloudflare does not. Handle both. */
function decodeHeader(value: string | null): string | undefined {
  if (!value) return undefined;
  try {
    return decodeURIComponent(value);
  } catch {
    return value;
  }
}

/**
 * Everything a rule can look at, assembled from one request.
 *
 * The shape is `VisitorContext` because that is what both the targeting engine
 * and the redirect resolver take — so the "what a visitor from Germany on iOS
 * sees" simulator in the editor is fed by constructing this by hand, and is
 * therefore testing the real thing.
 */
export function visitorFrom(
  request: { headers: Headers; url: string },
): VisitorContext & { isBot: boolean } {
  const client = parseClient(request.headers.get('user-agent'));
  const geo = parseGeo(request.headers);
  const url = new URL(request.url);
  const referrer = request.headers.get('referer') ?? undefined;

  return {
    path: url.pathname,
    url: request.url,
    referrer,
    referrerHost: referrer ? safeHost(referrer) : undefined,
    utm: {
      utm_source: url.searchParams.get('utm_source') ?? undefined,
      utm_medium: url.searchParams.get('utm_medium') ?? undefined,
      utm_campaign: url.searchParams.get('utm_campaign') ?? undefined,
      utm_term: url.searchParams.get('utm_term') ?? undefined,
      utm_content: url.searchParams.get('utm_content') ?? undefined,
    },
    device: client.device,
    os: client.os,
    browser: client.browser,
    language: parseLanguage(request.headers.get('accept-language')),
    country: geo.country,
    region: geo.region,
    city: geo.city,
    continent: geo.continent,
    dayOfWeek: new Date().getUTCDay(),
    hour: new Date().getUTCHours(),
    isBot: client.isBot,
  };
}

function safeHost(url: string): string | undefined {
  try {
    return new URL(url).host;
  } catch {
    return undefined;
  }
}
