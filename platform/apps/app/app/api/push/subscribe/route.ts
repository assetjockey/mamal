import { sql } from 'drizzle-orm';
import { asPlatformAdmin, textArray } from '@mamal/db';
import { db } from '@/lib/db';

/**
 * POST /api/push/subscribe — a browser has granted permission.
 *
 * Public and cross-origin by necessity: it is called from a customer's site.
 * The `key` identifies which push website the subscription belongs to and
 * authorises nothing else — a forged call can only add an endpoint the caller
 * already controls, which is a subscription to their own notifications.
 *
 * Runs as platform admin because there is no session; the key is the lookup,
 * and every write is constrained to the site it resolves to.
 */

const cors = (origin: string | null) => ({
  'access-control-allow-origin': origin ?? '*',
  'cache-control': 'no-store',
});

type Body = {
  key?: string;
  replaces?: string;
  subscription?: { endpoint?: string; keys?: { p256dh?: string; auth?: string } };
  meta?: { country?: string; browser?: string; os?: string; device?: string; language?: string };
  tags?: string[];
};

export async function POST(request: Request) {
  const origin = request.headers.get('origin');
  let body: Body;
  try {
    body = await request.json();
  } catch {
    return Response.json({ ok: false }, { status: 400, headers: cors(origin) });
  }

  const endpoint = body.subscription?.endpoint;
  const p256dh = body.subscription?.keys?.p256dh;
  const auth = body.subscription?.keys?.auth;
  if (!body.key || !endpoint || !p256dh || !auth) {
    return Response.json({ ok: false }, { status: 400, headers: cors(origin) });
  }

  // Only http(s) endpoints from a real push service. Without this the table
  // becomes a place to store arbitrary strings.
  if (!/^https:\/\//.test(endpoint) || endpoint.length > 2000) {
    return Response.json({ ok: false }, { status: 400, headers: cors(origin) });
  }

  const stored = await asPlatformAdmin(async (tx) => {
    const [site] = await tx.execute<{ id: string; workspace_id: string }>(sql`
      select pw.id, pw.workspace_id from push_websites pw
        join sites s on s.id = pw.site_id
       where pw.vapid_public_key = ${body.key} and pw.is_enabled`);
    if (!site) return null;

    /*
     * The endpoint is the identity, so a browser that re-subscribes updates
     * rather than duplicating. Without this a person who clears their site data
     * a few times receives the same campaign three times — the complaint that
     * makes people block notifications for good.
     */
    if (body.replaces && body.replaces !== endpoint) {
      await tx.execute(sql`
        update push_subscribers set status = 'expired', updated_at = now()
         where push_website_id = ${site.id} and endpoint = ${body.replaces}`);
    }

    const m = body.meta ?? {};
    await tx.execute(sql`
      insert into push_subscribers
        (workspace_id, push_website_id, endpoint, p256dh, auth,
         country, browser, os, device, language, tags, status)
      values (${site.workspace_id}, ${site.id}, ${endpoint}, ${p256dh}, ${auth},
              ${m.country ?? null}, ${m.browser ?? null}, ${m.os ?? null},
              ${m.device ?? null}, ${m.language ?? null},
              ${textArray((body.tags ?? []).slice(0, 20).map(String))}, 'active')
      on conflict (push_website_id, endpoint) do update
        set p256dh = excluded.p256dh,
            auth = excluded.auth,
            -- Re-subscribing revives a previously expired row rather than
            -- leaving the person unreachable.
            status = 'active',
            last_seen_at = now(),
            updated_at = now()`);
    return true;
  }, { db: db() }).catch(() => null);

  return Response.json({ ok: Boolean(stored) }, {
    status: stored ? 200 : 404,
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
      'access-control-max-age': '86400',
    },
  });
}
