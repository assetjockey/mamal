import type { NextConfig } from 'next';

const config: NextConfig = {
  // Workspace packages ship TypeScript source, not a build step.
  transpilePackages: [
    '@mamal/ui',
    '@mamal/db',
    '@mamal/entitlements',
    '@mamal/credits',
    '@mamal/resources',
    '@mamal/bus',
    '@mamal/tool-kit',
    '@mamal/automations',
    '@mamal/auth',
    '@mamal/ai',
    '@mamal/tool-audit',
    '@mamal/seo-checks',
    '@mamal/seo-tools',
    '@mamal/crawl',
    '@mamal/jobs',
    '@mamal/tool-confirm',
    '@mamal/widget-catalog',
    '@mamal/targeting',
    '@mamal/tool-link',
    '@mamal/link-catalog',
    '@mamal/redirect',
    '@mamal/geo',
    '@mamal/qr',
    '@mamal/storage',
    '@mamal/domains',
    '@mamal/tool-market',
  ],
  typedRoutes: false,

  /*
   * The widget runtime's public contract is `/c/{pixelKey}.json`.
   *
   * Short, cacheable, and not obviously an API — it ships inside a script tag
   * on customer sites, so it is effectively permanent. Rewriting keeps that URL
   * stable while the handler stays with the rest of the routes, and it is the
   * path the CDN will be configured against.
   */
  async rewrites() {
    return [
      { source: '/c/:key.json', destination: '/api/c/:key' },
      { source: '/c/ingest', destination: '/api/c/ingest' },
    ];
  },
  experimental: { optimizePackageImports: ['lucide-react'] },
};

export default config;
