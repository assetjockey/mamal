import { createHash, randomBytes, timingSafeEqual } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, textArray, type Database, type WorkspaceScopedDb } from '@mamal/db';

/**
 * API keys for the public API and the MCP server.
 *
 * The key is shown once, at creation, and only its SHA-256 is stored — so a
 * database read cannot be replayed as a credential. A short prefix is stored in
 * clear so the UI can say *which* key was used without being able to
 * reconstruct it, and so a leaked key found in a log can be identified and
 * revoked without asking the reporter for the secret.
 *
 * SHA-256 rather than a password hash on purpose: these are 256 bits of CSPRNG
 * output, not user-chosen secrets, so there is nothing for bcrypt's work factor
 * to defend against — and an API key is verified on every request, where a
 * deliberately slow hash would be a self-inflicted rate limit.
 */

export const KEY_PREFIX = 'mk_';

export type ApiKeyRecord = {
  id: string;
  workspaceId: string;
  userId: string | null;
  name: string;
  scopes: string[];
  rateLimitPerMin: number;
};

/** A newly minted key. `secret` is the only time the full value exists. */
export type MintedKey = { record: ApiKeyRecord; secret: string };

function hash(secret: string): string {
  return createHash('sha256').update(secret).digest('hex');
}

/** `mk_` + 32 bytes of base64url. The prefix makes it greppable in logs. */
function generateSecret(): string {
  return KEY_PREFIX + randomBytes(32).toString('base64url');
}

export async function createApiKey(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; userId?: string | null; name: string; scopes: string[]; expiresAt?: Date | null },
): Promise<MintedKey> {
  const secret = generateSecret();
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into api_keys (workspace_id, user_id, name, key_hash, prefix, scopes, expires_at)
    values (${opts.workspaceId}, ${opts.userId ?? null}, ${opts.name}, ${hash(secret)},
            ${secret.slice(0, 12)}, ${textArray(opts.scopes)},
            ${opts.expiresAt ? opts.expiresAt.toISOString() : null})
    returning id`);

  return {
    secret,
    record: {
      id: row!.id,
      workspaceId: opts.workspaceId,
      userId: opts.userId ?? null,
      name: opts.name,
      scopes: opts.scopes,
      rateLimitPerMin: 60,
    },
  };
}

export type KeyFailure =
  | 'missing'
  | 'malformed'
  | 'unknown'
  | 'revoked'
  | 'expired';

export type VerifyResult =
  | { ok: true; key: ApiKeyRecord }
  | { ok: false; reason: KeyFailure };

/**
 * Verifies a bearer token.
 *
 * Runs as platform admin because the lookup has to happen *before* a workspace
 * is known — the key is what establishes it. Nothing else in the request may
 * use this handle; the caller re-enters through `withWorkspace` with the id
 * this returns, so RLS applies to every subsequent read.
 */
export async function verifyApiKey(db: Database, presented: string | null): Promise<VerifyResult> {
  if (!presented) return { ok: false, reason: 'missing' };
  const secret = presented.startsWith('Bearer ') ? presented.slice(7).trim() : presented.trim();
  if (!secret.startsWith(KEY_PREFIX)) return { ok: false, reason: 'malformed' };

  const digest = hash(secret);
  const [row] = await asPlatformAdmin((tx) => tx.execute<{
    id: string; workspace_id: string; user_id: string | null; name: string;
    scopes: string[]; key_hash: string; rate_limit_per_min: string;
    revoked_at: string | null; expires_at: string | null;
  }>(sql`
    select id, workspace_id, user_id, name, scopes, key_hash, rate_limit_per_min,
           revoked_at, expires_at
      from api_keys where key_hash = ${digest} limit 1`), { db });

  if (!row) return { ok: false, reason: 'unknown' };

  // The lookup was already by digest, so this is belt-and-braces rather than
  // the primary defence — but it costs nothing and keeps the comparison
  // constant-time if the lookup is ever loosened.
  const a = Buffer.from(row.key_hash);
  const b = Buffer.from(digest);
  if (a.length !== b.length || !timingSafeEqual(a, b)) return { ok: false, reason: 'unknown' };

  if (row.revoked_at) return { ok: false, reason: 'revoked' };
  if (row.expires_at && new Date(row.expires_at) < new Date()) return { ok: false, reason: 'expired' };

  return {
    ok: true,
    key: {
      id: row.id,
      workspaceId: row.workspace_id,
      userId: row.user_id,
      name: row.name,
      scopes: row.scopes ?? [],
      rateLimitPerMin: Number(row.rate_limit_per_min) || 60,
    },
  };
}

/**
 * Scope check over the `<tool>:<resource>:<action>` grammar.
 *
 * `*` at any position is a wildcard, so `audit:*:read` grants read on every
 * audit resource and `*` alone is full access. Wildcards are matched per
 * segment rather than as a text prefix: `audit:*` must not match
 * `audit_admin:secrets`, and a prefix match would.
 */
export function hasScope(granted: readonly string[], required: string): boolean {
  const want = required.split(':');
  return granted.some((scope) => {
    if (scope === '*') return true;
    const have = scope.split(':');
    if (have.length !== want.length) return false;
    return have.every((seg, i) => seg === '*' || seg === want[i]);
  });
}

/**
 * Records use, at most once a minute per key.
 *
 * This ran on every request and was the dominant cost under load: one row per
 * key, updated by every concurrent request holding it, so the *fastest* routes
 * had the worst tail — p50 134ms against p95 686ms on `/api/v1/me`, which does
 * a single SELECT. Not awaiting the promise hid the write from the response
 * path but not from the row lock, and a queue on one row is a queue on the
 * whole API.
 *
 * `last_used_at` answers "is this key still in use", where a minute of
 * granularity is plenty. The predicate makes the write a no-op almost always,
 * and a no-op update takes no row lock.
 */
export const TOUCH_INTERVAL_SECONDS = 60;

export async function touchApiKey(db: Database, keyId: string): Promise<void> {
  await asPlatformAdmin(
    (tx) => tx.execute(sql`
      update api_keys set last_used_at = now()
       where id = ${keyId}
         and (last_used_at is null
              or last_used_at < now() - ${sql.raw(`interval '${TOUCH_INTERVAL_SECONDS} seconds'`)})`),
    { db },
  );
}

export async function revokeApiKey(tx: WorkspaceScopedDb, keyId: string): Promise<void> {
  await tx.execute(sql`update api_keys set revoked_at = now() where id = ${keyId}`);
}
