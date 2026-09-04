import { verifyApiKey, hasScope, touchApiKey, type ApiKeyRecord } from '@mamal/auth';
import { withWorkspace, type WorkspaceScopedDb } from '@mamal/db';
import { db } from '@/lib/db';

/**
 * The shared spine for `/api/v1/*`.
 *
 * Every route goes through `handle`, which authenticates the key, checks the
 * scope, and hands the body a workspace-scoped transaction. There is no code
 * path to the database that skips the workspace binding, so RLS is not a
 * defence the route author has to remember.
 */

export type ApiError = { error: { code: string; message: string; hint?: string } };

export function fail(status: number, code: string, message: string, hint?: string): Response {
  return Response.json({ error: { code, message, ...(hint ? { hint } : {}) } } satisfies ApiError, {
    status,
    headers: { 'cache-control': 'no-store' },
  });
}

export function ok(body: unknown, init: ResponseInit = {}): Response {
  return Response.json(body, {
    ...init,
    headers: { 'cache-control': 'no-store', ...(init.headers ?? {}) },
  });
}

const KEY_FAILURES: Record<string, { status: number; code: string; message: string; hint?: string }> = {
  missing: { status: 401, code: 'unauthenticated', message: 'No API key supplied.', hint: 'Send `Authorization: Bearer mk_…`.' },
  malformed: { status: 401, code: 'unauthenticated', message: 'That is not an API key.', hint: 'Keys start with `mk_`.' },
  unknown: { status: 401, code: 'unauthenticated', message: 'Unknown API key.' },
  revoked: { status: 401, code: 'key_revoked', message: 'This key was revoked.', hint: 'Create a new one in Settings → API.' },
  expired: { status: 401, code: 'key_expired', message: 'This key has expired.' },
};

export type ApiContext = {
  key: ApiKeyRecord;
  workspaceId: string;
  tx: WorkspaceScopedDb;
  url: URL;
  /**
   * The parsed JSON body, or `null` if there was none or it was unparseable.
   *
   * Read once here rather than in each route: a `Request` body is a stream and
   * can only be consumed once, so a route that reads it and then falls through
   * to shared handling would find it empty.
   */
  body: unknown;
};

/**
 * Wraps a route body with auth, scope, and workspace binding.
 *
 * The scope is declared by the route rather than inferred from the method, so
 * a route that reads one thing and writes another cannot accidentally be
 * reachable with only read access.
 */
export function handle(
  /**
   * The scope this route needs, or `null` for "any valid key".
   *
   * Not `'*'` — that is a *grant* meaning full access, so requiring it would
   * refuse every correctly-scoped key. The two read in the same direction and
   * mean opposite things, which is exactly why this is a separate value.
   */
  scope: string | null,
  body: (ctx: ApiContext) => Promise<Response>,
): (request: Request, context: { params: Promise<Record<string, string>> }) => Promise<Response> {
  // The second parameter is Next's route context. It is unused — path params
  // are read off the URL so one wrapper fits every route shape — but the type
  // has to match, or `next build` rejects the route. `tsc --noEmit` does not
  // see this: the validators are generated during the build.
  return async (request: Request) => {
    const database = db();
    const result = await verifyApiKey(database, request.headers.get('authorization'));

    if (!result.ok) {
      const f = KEY_FAILURES[result.reason]!;
      return fail(f.status, f.code, f.message, f.hint);
    }

    if (scope !== null && !hasScope(result.key.scopes, scope)) {
      return fail(
        403,
        'insufficient_scope',
        `This key cannot ${scope}.`,
        `Grant the \`${scope}\` scope, or use a key that has it.`,
      );
    }

    // Deliberately not awaited: last-used is telemetry, and a request should
    // not get slower to record that it happened.
    void touchApiKey(database, result.key.id).catch(() => {});

    let parsedBody: unknown = null;
    if (request.method !== 'GET' && request.method !== 'HEAD') {
      try {
        parsedBody = await request.json();
      } catch {
        parsedBody = null;
      }
    }

    try {
      return await withWorkspace(
        result.key.workspaceId,
        (tx) =>
          body({
            key: result.key,
            workspaceId: result.key.workspaceId,
            tx,
            url: new URL(request.url),
            body: parsedBody,
          }),
        { db: database },
      );
    } catch (e) {
      const refusal = asRefusal(e);
      if (refusal) return fail(refusal.status, refusal.reason, refusal.message);

      // Never leak a driver message to an API consumer; log it and return a
      // stable shape they can branch on.
      console.error('api error', e);
      return fail(500, 'internal', 'The request could not be completed.');
    }
  };
}

/**
 * A tool's deliberate refusal, translated into HTTP.
 *
 * Recognised **structurally** — an `Error` whose class name ends `NotAllowed`
 * and which carries a string `reason` — rather than by importing
 * `LinkNotAllowed`, `ConfirmNotAllowed` and the rest. Importing them would put
 * a direct dependency on every tool into the shared API layer, which the eslint
 * boundary forbids and the per-tool build matrix would fail on: `apps/app` has
 * to compile with any single tool absent.
 *
 * Without this, "that alias is reserved" reached the caller as a 500 saying
 * "the request could not be completed" — the resolver's whole point is that it
 * returns a *reason*, and swallowing it throws away the only useful part.
 */
export function asRefusal(e: unknown): { status: number; reason: string; message: string } | null {
  if (!(e instanceof Error) || !/NotAllowed$/.test(e.name)) return null;
  const reason = (e as Error & { reason?: unknown }).reason;
  if (typeof reason !== 'string') return null;
  return { status: statusFor(reason), reason, message: e.message };
}

function statusFor(reason: string): number {
  switch (reason) {
    case 'not_found':
      return 404;
    // Somebody else already has it. Retrying with the same input cannot work.
    case 'alias_taken':
    case 'host_taken':
      return 409;
    /*
     * 402, not 403. The caller is not forbidden — they are out of allowance,
     * and the message says which one and what it costs. A 403 tells an
     * integrator to check their credentials, which is the wrong next step.
     */
    case 'limit_reached':
    case 'quota_exceeded':
    case 'insufficient_credits':
    case 'tool_unavailable':
      return 402;
    default:
      // Everything else is the caller's input: a bad alias, a payload the type
      // cannot encode, a rotation with no weight, a barcode that will not scan.
      return 400;
  }
}

/* ------------------------------------------------------- shared grammar */

export const MAX_LIMIT = 100;

/** `?limit=` clamped, so one caller cannot ask for the whole table. */
export function limitOf(url: URL, fallback = 25): number {
  const raw = Number(url.searchParams.get('limit'));
  if (!Number.isFinite(raw) || raw <= 0) return fallback;
  return Math.min(Math.floor(raw), MAX_LIMIT);
}

/**
 * Keyset pagination over a uuidv7 primary key.
 *
 * uuidv7 sorts by creation time, so `id > cursor` is both a stable page break
 * and a chronological one — no OFFSET, which degrades on deep pages and skips
 * rows when the underlying set changes between requests.
 */
export function cursorOf(url: URL): string | null {
  const c = url.searchParams.get('cursor');
  return c && /^[0-9a-f-]{36}$/i.test(c) ? c : null;
}

/**
 * Ops fetch `limit + 1` rows, so "is there another page" is answered without a
 * second COUNT query. The extra row is trimmed here and never returned.
 */
export function page(rows: unknown, limit: number) {
  const list = (Array.isArray(rows) ? rows : []) as { id?: string }[];
  const hasMore = list.length > limit;
  const data = hasMore ? list.slice(0, limit) : list;
  return { data, next_cursor: hasMore ? (data[data.length - 1]?.id ?? null) : null };
}
