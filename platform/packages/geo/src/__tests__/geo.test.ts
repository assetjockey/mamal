import { describe, expect, it } from 'vitest';
import { parseClient, parseGeo, parseLanguage, visitorFrom } from '../index.ts';

/** Real strings, copied from real traffic — invented ones test the regex, not the world. */
const UA = {
  iphoneSafari:
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
  iphoneChrome:
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/126.0.6478.54 Mobile/15E148 Safari/604.1',
  ipad:
    'Mozilla/5.0 (iPad; CPU OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
  androidPhone:
    'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',
  androidTablet:
    'Mozilla/5.0 (Linux; Android 13; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
  windowsEdge:
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.2592.68',
  macFirefox:
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',
  samsung:
    'Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0.0.0 Mobile Safari/537.36',
  googlebot:
    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
  slack: 'Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)',
  facebook: 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
};

describe('client detection', () => {
  it('names the browser behind the ones that impersonate Chrome and Safari', () => {
    // Every one of these strings also contains "Chrome" or "Safari"; order in
    // the test table is the whole implementation.
    expect(parseClient(UA.windowsEdge).browser).toBe('Edge');
    expect(parseClient(UA.samsung).browser).toBe('Samsung Internet');
    expect(parseClient(UA.iphoneChrome).browser).toBe('Chrome');
    expect(parseClient(UA.iphoneSafari).browser).toBe('Safari');
    expect(parseClient(UA.macFirefox).browser).toBe('Firefox');
  });

  it('separates tablets from phones, including the Android case with no "Mobile"', () => {
    // Wrong here means tablet users get sent to the phone app store — a support
    // ticket rather than a crash, and so the kind of bug that lives for years.
    expect(parseClient(UA.ipad).device).toBe('tablet');
    expect(parseClient(UA.androidTablet).device).toBe('tablet');
    expect(parseClient(UA.androidPhone).device).toBe('mobile');
    expect(parseClient(UA.iphoneSafari).device).toBe('mobile');
    expect(parseClient(UA.macFirefox).device).toBe('desktop');
  });

  it('reports iOS and Android, which is what a deep link needs', () => {
    expect(parseClient(UA.iphoneSafari).os).toBe('iOS');
    expect(parseClient(UA.androidPhone).os).toBe('Android');
    expect(parseClient(UA.windowsEdge).os).toBe('Windows');
    expect(parseClient(UA.macFirefox).os).toBe('macOS');
  });

  it('flags crawlers and link unfurlers', () => {
    // An unfurler that counted as a click would inflate every link posted to
    // Slack, and could exhaust a click limit before a person ever saw it.
    for (const ua of [UA.googlebot, UA.slack, UA.facebook]) {
      expect(parseClient(ua).isBot, ua.slice(0, 30)).toBe(true);
    }
    expect(parseClient(UA.iphoneSafari).isBot).toBe(false);
  });

  it('survives a missing or nonsense user agent', () => {
    expect(parseClient(null)).toEqual({ device: 'desktop', os: 'Unknown', browser: 'Unknown', isBot: false });
    expect(parseClient('')).toMatchObject({ isBot: false });
    expect(parseClient('!!!').os).toBe('Unknown');
  });
});

describe('language', () => {
  it('keeps the primary subtag and drops quality values', () => {
    expect(parseLanguage('de-AT,de;q=0.9,en;q=0.8')).toBe('de');
    expect(parseLanguage('en-GB')).toBe('en');
    expect(parseLanguage('*')).toBeUndefined();
    expect(parseLanguage(null)).toBeUndefined();
  });
});

describe('geo', () => {
  const headers = (h: Record<string, string>) => new Headers(h);

  it('reads Cloudflare and Vercel headers alike', () => {
    expect(parseGeo(headers({ 'cf-ipcountry': 'de', 'cf-ipcity': 'Berlin' })))
      .toMatchObject({ country: 'DE', city: 'Berlin' });
    expect(parseGeo(headers({ 'x-vercel-ip-country': 'FR', 'x-vercel-ip-city': 'Le%20Havre' })))
      .toMatchObject({ country: 'FR', city: 'Le Havre' });
  });

  it('treats Cloudflare’s unknowns as unknown rather than as countries', () => {
    // `XX` is "we could not tell" and `T1` is Tor. A rule targeting XX would
    // match everyone the CDN failed to place.
    expect(parseGeo(headers({ 'cf-ipcountry': 'XX' })).country).toBeUndefined();
    expect(parseGeo(headers({ 'cf-ipcountry': 'T1' })).country).toBeUndefined();
  });

  it('returns nothing at all when there is no CDN in front', () => {
    // Local development and direct origin hits. Every geo rule then fails
    // closed, which is the right direction.
    expect(parseGeo(headers({}))).toEqual({
      country: undefined, region: undefined, city: undefined, continent: undefined,
    });
  });
});

describe('visitorFrom', () => {
  it('assembles exactly what a rule can look at', () => {
    const visitor = visitorFrom({
      url: 'https://mml.to/promo?utm_source=poster&utm_campaign=spring',
      headers: new Headers({
        'user-agent': UA.androidPhone,
        'accept-language': 'pt-BR,pt;q=0.9',
        'cf-ipcountry': 'BR',
        'cf-ipcity': 'Recife',
        referer: 'https://www.instagram.com/p/abc',
      }),
    });

    expect(visitor).toMatchObject({
      path: '/promo',
      device: 'mobile',
      os: 'Android',
      browser: 'Chrome',
      language: 'pt',
      country: 'BR',
      city: 'Recife',
      referrerHost: 'www.instagram.com',
      isBot: false,
    });
    expect(visitor.utm).toMatchObject({ utm_source: 'poster', utm_campaign: 'spring' });
  });

  it('does not throw on a referrer that is not a web URL', () => {
    // This runs on the redirect path. Nothing here may 500 a redirect, and
    // browsers send referrers that are not http — Android apps send their own
    // scheme, and some clients send a bare token.
    expect(
      visitorFrom({
        url: 'https://mml.to/x',
        headers: new Headers({ referer: 'android-app://com.example' }),
      }).referrerHost,
    ).toBe('com.example');

    expect(
      visitorFrom({
        url: 'https://mml.to/x',
        headers: new Headers({ referer: 'not a url at all' }),
      }).referrerHost,
    ).toBeUndefined();
  });
});
