import { list, type Finding, type PageFacts, type Rule, type SiteFacts } from '../types.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page' });
const site = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'site' });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });
const header = (p: PageFacts, name: string): string | undefined => p.headers[name.toLowerCase()];

export const securityRules: Rule[] = [
  page({
    id: 'not-https',
    category: 'security',
    severity: 'critical',
    weight: 10,
    title: 'Page is not served over HTTPS',
    why: 'Browsers mark HTTP pages as not secure, and HTTPS has been a ranking signal for a decade.',
    howToFix: 'Install a certificate (Let’s Encrypt is free) and 301 all HTTP traffic to HTTPS.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      return !pg.isHttps ? f('not-https', 'critical', pg.url, {}) : null;
    },
  }),

  page({
    id: 'mixed-content',
    category: 'security',
    severity: 'warning',
    weight: 5,
    title: 'Mixed content',
    why: 'An HTTPS page loading HTTP resources breaks the padlock, and browsers block the request outright.',
    howToFix: 'Change every `http://` resource URL to `https://`, or make them protocol-relative.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!pg.isHttps || pg.mixedContent.length === 0) return null;
      return f('mixed-content', 'warning', pg.url, {
        count: pg.mixedContent.length,
        sample: pg.mixedContent.slice(0, 10),
      });
    },
  }),

  page({
    id: 'missing-hsts',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'No HSTS header',
    why: 'Without HSTS the first request each session can still go over HTTP and be intercepted.',
    howToFix: 'Send `Strict-Transport-Security: max-age=31536000; includeSubDomains`. Start with a short max-age.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!pg.isHttps || pg.statusCode !== 200) return null;
      return !header(pg, 'strict-transport-security') ? f('missing-hsts', 'info', pg.url, {}) : null;
    },
  }),

  page({
    id: 'missing-csp',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'No Content-Security-Policy',
    why: 'A CSP is the main defence against injected scripts. Without one, any XSS becomes full script execution.',
    howToFix: 'Start in report-only mode to find what you actually load, then enforce.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !header(pg, 'content-security-policy') ? f('missing-csp', 'info', pg.url, {}) : null;
    },
  }),

  page({
    id: 'missing-referrer-policy',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'No Referrer-Policy',
    why: 'By default the full URL — including any path that leaks context — is sent to every site you link to.',
    howToFix: 'Send `Referrer-Policy: strict-origin-when-cross-origin`.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !header(pg, 'referrer-policy') ? f('missing-referrer-policy', 'info', pg.url, {}) : null;
    },
  }),

  page({
    id: 'server-version-exposed',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'Server version in headers',
    why: 'Advertising the exact server and version tells an attacker which published vulnerabilities to try first.',
    howToFix: 'Set `server_tokens off` (nginx) or `ServerTokens Prod` (Apache), and remove `X-Powered-By`.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const server = header(pg, 'server');
      const powered = header(pg, 'x-powered-by');
      // The header alone is fine; a version number in it is the problem.
      const leaks = [server, powered].filter((v) => v && /\d+\.\d+/.test(v));
      return leaks.length > 0
        ? f('server-version-exposed', 'info', pg.url, { server, poweredBy: powered })
        : null;
    },
  }),

  page({
    id: 'unsafe-form',
    category: 'security',
    severity: 'critical',
    weight: 10,
    title: 'Form posts over HTTP',
    why: 'Anything typed into this form travels in the clear. Browsers show an explicit warning on it.',
    howToFix: 'Change the form `action` to an `https://` URL.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const unsafe = pg.forms.filter((form) => !form.isSecure);
      return unsafe.length > 0
        ? f('unsafe-form', 'critical', pg.url, { count: unsafe.length, sample: unsafe.slice(0, 5) })
        : null;
    },
  }),

  page({
    id: 'plaintext-email',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'Email address in plain text',
    why: 'Scrapers harvest addresses from markup. A plain `mailto:` is the most common source of a spam problem.',
    howToFix: 'Use a contact form, or obfuscate the address in JavaScript.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      return pg.plaintextEmails.length > 0
        ? f('plaintext-email', 'info', pg.url, { emails: pg.plaintextEmails.slice(0, 5) })
        : null;
    },
  }),

  page({
    id: 'deprecated-html',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'Deprecated HTML tags',
    why: 'Tags removed from the standard behave inconsistently and usually mean the page predates responsive layout.',
    howToFix: 'Replace them with CSS. `<center>` becomes `text-align`, `<font>` becomes a class.',
    defaultThresholds: { tags: 'center,font,marquee,blink,big,strike,tt,frame,frameset,applet' },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const watched = list(ctx.thresholds, 'tags', ['center', 'font', 'marquee', 'blink', 'big', 'strike', 'tt']);
      const found = pg.deprecatedTags.filter((t) => watched.includes(t.toLowerCase()));
      return found.length > 0 ? f('deprecated-html', 'info', pg.url, { tags: [...new Set(found)] }) : null;
    },
  }),

  page({
    id: 'missing-doctype',
    category: 'security',
    severity: 'info',
    weight: 1,
    title: 'No doctype',
    why: 'Without `<!DOCTYPE html>` browsers fall back to quirks mode, where layout rules differ from every modern expectation.',
    howToFix: 'Make `<!DOCTYPE html>` the first line of the document.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !pg.hasDoctype ? f('missing-doctype', 'info', pg.url, {}) : null;
    },
  }),

  site({
    id: 'ssl-expiring',
    category: 'security',
    severity: 'critical',
    weight: 10,
    title: 'TLS certificate expiring',
    why: 'An expired certificate makes every browser show a full-page warning. Traffic goes to zero, not down.',
    howToFix:
      'Renew the certificate and check that auto-renewal actually runs.\n\n' +
      'Monitor can watch this date for you and alert before it lapses.',
    defaultThresholds: { warnDays: 21 },
    evaluate: (s, ctx) => {
      const facts = s as SiteFacts;
      if (!facts.sslValidTo) return null;
      const days = Math.floor((facts.sslValidTo.getTime() - Date.now()) / 86_400_000);
      const warn = typeof ctx.thresholds.warnDays === 'number' ? ctx.thresholds.warnDays : 21;
      return days <= warn
        ? f('ssl-expiring', 'critical', null, { daysRemaining: days, validTo: facts.sslValidTo.toISOString() })
        : null;
    },
  }),
];
