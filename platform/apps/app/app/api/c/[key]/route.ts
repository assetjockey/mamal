import { asPlatformAdmin } from '@mamal/db';
import { buildPayload } from '@mamal/tool-confirm';
import { db } from '@/lib/db';

/**
 * GET /api/c/:key.json — the one request the widget runtime makes.
 *
 * Public and unauthenticated by necessity: it is fetched by a script tag on a
 * customer's site. The pixel key authorises *reading a widget configuration*
 * and nothing else, and `buildPayload` decides what is safe to send — the
 * conversion feed is projected down to a first name and a city before it
 * leaves the database.
 *
 * Runs as platform admin because there is no session to derive a workspace
 * from; the pixel key IS the lookup, and every query inside is constrained to
 * the campaign it resolves to.
 */
export async function GET(request: Request, { params }: { params: Promise<{ key: string }> }) {
  const { key } = await params;
  const pixelKey = key.replace(/\.json$/, '');

  const origin = request.headers.get('origin');
  const url = new URL(request.url);

  const payload = await asPlatformAdmin(
    (tx) =>
      buildPayload(tx, {
        pixelKey,
        ingestUrl: `${url.origin}/api/c/ingest`,
      }),
    { db: db() },
  );

  if (!payload) {
    // Same answer for "no such key" and "campaign disabled": a 404 that
    // distinguishes them lets anyone enumerate valid pixel keys.
    return new Response('null', {
      status: 404,
      headers: { 'content-type': 'application/json', 'cache-control': 'public, max-age=60' },
    });
  }

  return new Response(JSON.stringify(payload), {
    headers: {
      'content-type': 'application/json',
      /*
       * Edge-cacheable for a minute, and served stale for a day while it
       * revalidates. This is what keeps the free tier free: one origin hit per
       * campaign per minute regardless of traffic, and a widget that keeps
       * working through an origin outage.
       */
      'cache-control': 'public, max-age=60, stale-while-revalidate=86400',
      // The script is loaded cross-origin from every customer site.
      'access-control-allow-origin': origin ?? '*',
      'vary': 'origin',
    },
  });
}
