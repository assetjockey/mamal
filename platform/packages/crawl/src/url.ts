import { lookup } from 'node:dns/promises';
import { isIP } from 'node:net';

/**
 * SSRF guard.
 *
 * A crawler takes a URL from a user and fetches it from inside our network,
 * which is the textbook SSRF setup. Every host is resolved and checked before
 * the first byte is requested — `169.254.169.254` reaches a cloud metadata
 * endpoint, and `localhost` reaches our own services.
 */
const PRIVATE_V4 = [
  /^0\./, /^10\./, /^127\./, /^169\.254\./, /^192\.168\./,
  /^172\.(1[6-9]|2\d|3[01])\./,
  /^100\.(6[4-9]|[7-9]\d|1[01]\d|12[0-7])\./, // CGNAT
  /^198\.(1[89])\./, /^192\.0\.[02]\./, /^203\.0\.113\./, /^24[0-9]\./, /^25[0-5]\./,
];

export function isPrivateIp(ip: string): boolean {
  const version = isIP(ip);
  if (version === 4) return PRIVATE_V4.some((re) => re.test(ip));
  if (version === 6) {
    const normalized = ip.toLowerCase();
    if (normalized === '::1' || normalized === '::') return true;
    // fc00::/7 unique-local, fe80::/10 link-local
    if (/^f[cd]/.test(normalized) || /^fe[89ab]/.test(normalized)) return true;
    // IPv4-mapped: ::ffff:127.0.0.1 must not slip through as "IPv6".
    const mapped = normalized.match(/^::ffff:(\d+\.\d+\.\d+\.\d+)$/);
    if (mapped) return isPrivateIp(mapped[1]!);
    return false;
  }
  return false;
}

export class BlockedUrl extends Error {
  constructor(
    readonly url: string,
    readonly reason: string,
  ) {
    super(`refusing to fetch ${url}: ${reason}`);
    this.name = 'BlockedUrl';
  }
}

export async function assertPublicUrl(
  raw: string,
  opts: { allowPrivate?: boolean } = {},
): Promise<URL> {
  let url: URL;
  try {
    url = new URL(raw);
  } catch {
    throw new BlockedUrl(raw, 'not a valid URL');
  }
  if (url.protocol !== 'http:' && url.protocol !== 'https:') {
    throw new BlockedUrl(raw, `unsupported protocol ${url.protocol}`);
  }
  if (opts.allowPrivate) return url;

  const host = url.hostname;
  if (isIP(host)) {
    if (isPrivateIp(host)) throw new BlockedUrl(raw, 'private address');
    return url;
  }
  if (host === 'localhost' || host.endsWith('.localhost') || host.endsWith('.internal')) {
    throw new BlockedUrl(raw, 'loopback or internal hostname');
  }

  // Resolve before fetching: a public name can still point at a private address.
  let addresses: { address: string }[];
  try {
    addresses = await lookup(host, { all: true });
  } catch {
    throw new BlockedUrl(raw, 'host does not resolve');
  }
  if (addresses.some((a) => isPrivateIp(a.address))) {
    throw new BlockedUrl(raw, 'resolves to a private address');
  }
  return url;
}

/** The dedupe key for a crawl: no hash, no trailing slash, no `www.`. */
export function normalizeUrl(raw: string, base?: string): string | null {
  try {
    const url = new URL(raw, base);
    url.hash = '';
    url.hostname = url.hostname.toLowerCase();
    if (url.pathname !== '/' && url.pathname.endsWith('/')) {
      url.pathname = url.pathname.slice(0, -1);
    }
    // Tracking parameters create infinite distinct URLs for one page.
    for (const key of [...url.searchParams.keys()]) {
      if (/^(utm_|fbclid|gclid|msclkid|mc_[ce]id|_ga|ref)/i.test(key)) url.searchParams.delete(key);
    }
    url.searchParams.sort();
    return url.toString();
  } catch {
    return null;
  }
}

export function sameSite(a: string, b: string): boolean {
  try {
    const strip = (h: string) => h.toLowerCase().replace(/^www\./, '');
    return strip(new URL(a).hostname) === strip(new URL(b).hostname);
  } catch {
    return false;
  }
}
