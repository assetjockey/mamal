import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { db } from '@/lib/db';

/**
 * POST /api/c/ingest — batched widget events from the runtime.
 *
 * Arrives via `sendBeacon`, so: no response is read, no cookies are sent, and
 * the body is a JSON blob of up to a few dozen events. Counters are
 * denormalised onto the widget rows here; the fact table is the source of truth
 * for reporting.
 */

const KINDS = new Set(['impression', 'click', 'close', 'submit', 'rate', 'hover']);
const MAX_EVENTS = 50;

type Incoming = { t?: string; w?: string; c?: string; p?: string; v?: unknown };

export async function POST(request: Request) {
  let body: { e?: Incoming[] };
  try {
    body = await request.json();
  } catch {
    return new Response(null, { status: 204 });
  }

  const events = Array.isArray(body.e) ? body.e.slice(0, MAX_EVENTS) : [];
  // Everything below is best-effort: a beacon has nobody listening for an
  // error, so the only useful response is 204 either way. Failing loudly here
  // would just fill logs with events we have already lost.
  const valid = events.filter(
    (e): e is Required<Pick<Incoming, 't' | 'w'>> & Incoming =>
      typeof e.t === 'string' && KINDS.has(e.t) && typeof e.w === 'string' && UUID.test(e.w),
  );

  if (valid.length > 0) {
    await asPlatformAdmin(async (tx) => {
      for (const e of valid) {
        const column =
          e.t === 'impression' ? sql`impressions` :
          e.t === 'click' ? sql`clicks` :
          e.t === 'close' ? sql`closes` :
          e.t === 'hover' ? sql`hovers` : sql`submissions`;
        await tx.execute(sql`
          update confirm_widgets set ${column} = ${column} + 1, updated_at = now()
           where id = ${e.w}`);
      }
    }, { db: db() }).catch(() => {});
  }

  return new Response(null, {
    status: 204,
    headers: {
      'access-control-allow-origin': request.headers.get('origin') ?? '*',
      'cache-control': 'no-store',
    },
  });
}

export async function OPTIONS(request: Request) {
  return new Response(null, {
    status: 204,
    headers: {
      'access-control-allow-origin': request.headers.get('origin') ?? '*',
      'access-control-allow-methods': 'POST, OPTIONS',
      'access-control-allow-headers': 'content-type',
      'access-control-max-age': '86400',
    },
  });
}

const UUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
