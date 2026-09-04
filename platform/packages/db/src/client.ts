import { drizzle } from 'drizzle-orm/postgres-js';
import { sql } from 'drizzle-orm';
import postgres from 'postgres';
import * as schema from './schema/index.ts';

export type Database = ReturnType<typeof createClient>;

/**
 * The raw client is module-private on purpose. Everything tenant-scoped must
 * go through `withWorkspace()`, which sets the RLS GUC. See ADR: isolation.
 */
let _sql: postgres.Sql | undefined;
let _db: ReturnType<typeof drizzle<typeof schema>> | undefined;

function createClient(url?: string) {
  const connectionString =
    url ?? process.env.DATABASE_URL ?? 'postgres://localhost:5432/mamal_dev';
  _sql ??= postgres(connectionString, { max: 10, prepare: false });
  _db ??= drizzle(_sql, { schema, casing: 'snake_case' });
  return _db;
}

/**
 * Unscoped handle. Only legitimate for: migrations, the instance admin app,
 * the bus relay, and cross-tenant maintenance jobs. Every tool feature must
 * use `withWorkspace` instead — the CI isolation test asserts that tool
 * packages never import this symbol.
 */
export function unsafeUnscopedDb(url?: string) {
  return createClient(url);
}

export type WorkspaceScopedDb = Parameters<Parameters<Database['transaction']>[0]>[0];

/**
 * The ONLY sanctioned way to touch tenant data.
 *
 * Opens a transaction, sets `app.current_workspace_id` so every RLS policy
 * resolves, runs the callback, and tears the setting down with the tx. A query
 * that forgets its workspace predicate returns zero rows instead of leaking.
 */
export async function withWorkspace<T>(
  workspaceId: string,
  fn: (tx: WorkspaceScopedDb) => Promise<T>,
  opts: { db?: Database } = {},
): Promise<T> {
  const db = opts.db ?? createClient();
  return db.transaction(async (tx) => {
    await tx.execute(sql`select set_config('app.current_workspace_id', ${workspaceId}, true)`);
    return fn(tx);
  });
}

/** Escape hatch for the platform admin app, which legitimately reads across tenants. */
export async function asPlatformAdmin<T>(
  fn: (tx: WorkspaceScopedDb) => Promise<T>,
  opts: { db?: Database } = {},
): Promise<T> {
  const db = opts.db ?? createClient();
  return db.transaction(async (tx) => {
    await tx.execute(sql`select set_config('app.is_platform_admin', 'true', true)`);
    return fn(tx);
  });
}

export async function closeDb(): Promise<void> {
  await _sql?.end({ timeout: 5 });
  _sql = undefined;
  _db = undefined;
}
