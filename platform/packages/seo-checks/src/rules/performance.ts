import { list, num, type Finding, type PageFacts, type Rule } from '../types.ts';

const page = (r: Omit<Rule, 'appliesTo'>): Rule => ({ ...r, appliesTo: 'page' });
const f = (ruleId: string, severity: Rule['severity'], url: string | null, evidence: Record<string, unknown> = {}): Finding =>
  ({ ruleId, severity, url, evidence });

export const performanceRules: Rule[] = [
  page({
    id: 'slow-response',
    category: 'performance',
    severity: 'warning',
    weight: 5,
    title: 'Slow server response',
    why: 'Time to first byte is the floor for every other speed metric. Nothing on the page can be fast if the server is slow to start.',
    howToFix: 'Cache the response, add a CDN, or profile the slow query. Under 800ms is the target.',
    defaultThresholds: { maxTtfbMs: 800 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxTtfbMs', 800);
      return pg.ttfbMs > max ? f('slow-response', 'warning', pg.url, { ttfbMs: pg.ttfbMs, max }) : null;
    },
  }),

  page({
    id: 'page-too-large',
    category: 'performance',
    severity: 'warning',
    weight: 5,
    title: 'HTML document is very large',
    why: 'A large HTML payload delays the first paint on every connection, and disproportionately on mobile.',
    howToFix: 'Move inline styles and scripts to files, and check whether you are shipping data the page does not use.',
    defaultThresholds: { maxBytes: 500_000 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxBytes', 500_000);
      return pg.bytes > max
        ? f('page-too-large', 'warning', pg.url, { bytes: pg.bytes, max })
        : null;
    },
  }),

  page({
    id: 'dom-too-large',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Very large DOM',
    why: 'Thousands of nodes make style and layout expensive on every interaction, which shows up as sluggishness.',
    howToFix: 'Virtualise long lists and remove wrapper elements that exist only for styling.',
    defaultThresholds: { maxNodes: 1500 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxNodes', 1500);
      return pg.domNodes > max ? f('dom-too-large', 'info', pg.url, { nodes: pg.domNodes, max }) : null;
    },
  }),

  page({
    id: 'no-compression',
    category: 'performance',
    severity: 'warning',
    weight: 5,
    title: 'Response is not compressed',
    why: 'Text compresses by roughly 70%. Serving HTML uncompressed wastes most of the transfer.',
    howToFix: 'Enable Brotli or gzip at the server or CDN. It is usually one directive.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !pg.compression ? f('no-compression', 'warning', pg.url, { bytes: pg.bytes }) : null;
    },
  }),

  page({
    id: 'render-blocking-scripts',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Render-blocking scripts',
    why: 'A script without `defer` or `async` stops the parser until it downloads and runs.',
    howToFix: 'Add `defer` to scripts that need the DOM, `async` to independent ones. Inline only what is tiny and critical.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      const blocking = pg.scripts.filter((s) => s.src && !s.defer && !s.async);
      return blocking.length > 0
        ? f('render-blocking-scripts', 'info', pg.url, {
            count: blocking.length,
            sample: blocking.slice(0, 10).map((s) => s.src),
          })
        : null;
    },
  }),

  page({
    id: 'legacy-image-format',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Images in legacy formats',
    why: 'AVIF and WebP are typically 30–50% smaller than JPEG or PNG at the same quality.',
    howToFix: 'Serve AVIF or WebP with a `<picture>` fallback. Most image CDNs do this automatically.',
    defaultThresholds: { modernFormats: 'avif,webp,svg' },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const modern = list(ctx.thresholds, 'modernFormats', ['avif', 'webp', 'svg']);
      const legacy = pg.images.filter((i) => i.format && !modern.includes(i.format.toLowerCase()));
      return legacy.length > 0
        ? f('legacy-image-format', 'info', pg.url, {
            count: legacy.length,
            sample: legacy.slice(0, 10).map((i) => i.src),
          })
        : null;
    },
  }),

  page({
    id: 'images-not-lazy',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Images not lazy-loaded',
    why: 'Loading below-the-fold images immediately competes for bandwidth with what the visitor can actually see.',
    howToFix: 'Add `loading="lazy"` to images below the fold. Leave the hero image eager so LCP does not regress.',
    defaultThresholds: { minImages: 5 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const min = num(ctx.thresholds, 'minImages', 5);
      if (pg.images.length < min) return null;
      const eager = pg.images.filter((i) => i.loading !== 'lazy');
      return eager.length > min
        ? f('images-not-lazy', 'info', pg.url, { count: eager.length, total: pg.images.length })
        : null;
    },
  }),

  page({
    id: 'inline-css',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Heavy use of inline styles',
    why: 'Inline `style` attributes cannot be cached, cannot be reused, and bloat every response.',
    howToFix: 'Move repeated styles into a stylesheet. Keep inline styles for genuinely dynamic values only.',
    defaultThresholds: { maxInline: 20 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxInline', 20);
      return pg.inlineStyleCount > max
        ? f('inline-css', 'info', pg.url, { count: pg.inlineStyleCount, max })
        : null;
    },
  }),

  page({
    id: 'too-many-requests',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Many resource requests',
    why: 'Each request costs a round trip. On a slow connection the count matters more than the total size.',
    howToFix: 'Bundle stylesheets and scripts, and use sprites or icon fonts for many small images.',
    defaultThresholds: { maxRequests: 60 },
    evaluate: (p, ctx) => {
      const pg = p as PageFacts;
      const max = num(ctx.thresholds, 'maxRequests', 60);
      return pg.requestCount > max
        ? f('too-many-requests', 'info', pg.url, { count: pg.requestCount, max })
        : null;
    },
  }),

  page({
    id: 'no-http2',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'Served over HTTP/1.1',
    why: 'HTTP/2 multiplexes requests over one connection, which removes most of the cost of having many resources.',
    howToFix: 'Enable HTTP/2 or HTTP/3 at your server or CDN. It is usually already available and off by default.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (!pg.httpVersion || pg.statusCode !== 200) return null;
      return pg.httpVersion.startsWith('1')
        ? f('no-http2', 'info', pg.url, { version: pg.httpVersion })
        : null;
    },
  }),

  page({
    id: 'missing-viewport',
    category: 'performance',
    severity: 'warning',
    weight: 5,
    title: 'No mobile viewport',
    why: 'Without a viewport meta tag, mobile browsers render at desktop width and zoom out. Google treats this as not mobile-friendly.',
    howToFix: 'Add `<meta name="viewport" content="width=device-width, initial-scale=1">`.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !pg.hasViewport ? f('missing-viewport', 'warning', pg.url, {}) : null;
    },
  }),

  page({
    id: 'missing-charset',
    category: 'performance',
    severity: 'info',
    weight: 1,
    title: 'No charset declared',
    why: 'The browser has to guess the encoding, which delays parsing and can mangle non-ASCII text.',
    howToFix: 'Add `<meta charset="utf-8">` as the first element in `<head>`.',
    evaluate: (p) => {
      const pg = p as PageFacts;
      if (pg.statusCode !== 200) return null;
      return !pg.hasCharset ? f('missing-charset', 'info', pg.url, {}) : null;
    },
  }),
];
