import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { recordConversion } from '@mamal/tool-confirm';
import { db } from '@/lib/db';

/**
 * POST /api/c/conversion — a customer's backend reporting that something happened.
 *
 * Authenticated by the campaign's pixel key, which is public: this endpoint can
 * only *add* a conversion to the caller's own campaign, and the worst a leaked
 * key allows is somebody inflating their own proof feed. Anything that could
 * read or change configuration requires a real API key.
 */
const cors = (origin: string | null) => ({
  'access-control-allow-origin': origin ?? '*',
  'cache-control': 'no-store',
});

export async function POST(request: Request) {
  const origin = request.headers.get('origin');
  let body: {
    key?: string; type?: string; path?: string; country?: string;
    data?: Record<string, unknown>;
  };
  try {
    body = await request.json();
  } catch {
    return Response.json({ ok: false }, { status: 400, headers: cors(origin) });
  }

  if (!body.key) return Response.json({ ok: false }, { status: 400, headers: cors(origin) });

  const stored = await asPlatformAdmin(async (tx) => {
    const [campaign] = await tx.execute<{ id: string; workspace_id: string }>(sql`
      select id, workspace_id from confirm_campaigns
       where pixel_key = ${body.key} and is_enabled`);
    if (!campaign) return null;

    // Bounded: this is an unauthenticated write, so the payload cannot be a
    // place to store arbitrary volumes of data.
    const data = Object.fromEntries(
      Object.entries(body.data ?? {}).slice(0, 20).map(([k, v]) => [
        String(k).slice(0, 40),
        typeof v === 'string' ? v.slice(0, 200) : v,
      ]),
    );

    await recordConversion(tx, {
      workspaceId: campaign.workspace_id,
      campaignId: campaign.id,
      source: 'webhook',
      type: String(body.type ?? 'conversion').slice(0, 48),
      data,
      path: body.path?.slice(0, 500),
      country: body.country?.slice(0, 2),
    });
    return true;
  }, { db: db() }).catch(() => null);

  return Response.json({ ok: Boolean(stored) }, {
    status: stored ? 201 : 404,
    headers: cors(origin),
  });
}

export async function OPTIONS(request: Request) {
  return new Response(null, {
    status: 204,
    headers: {
      ...cors(request.headers.get('origin')),
      'access-control-allow-methods': 'POST, OPTIONS',
      'access-control-allow-headers': 'content-type',
    },
  });
}
