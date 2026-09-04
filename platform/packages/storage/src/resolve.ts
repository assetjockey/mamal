import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { localAdapter } from './local.ts';
import { s3Adapter } from './s3.ts';
import { StorageError, type StorageAdapter } from './types.ts';
import { unwrapKey } from './crypto.ts';

/**
 * Which backend a workspace's files go to.
 *
 * `swipgle`'s best idea, kept: a provider is a **row**, so an operator adds
 * Wasabi by filling in a form rather than shipping code. A workspace-scoped row
 * wins over an instance-wide one, which is what lets a large customer bring
 * their own bucket without a separate deployment.
 *
 * Credentials are envelope-encrypted like any other secret and are unwrapped
 * here, at the moment of use — never held in a module-level cache, so rotating
 * the instance key takes effect on the next request rather than the next
 * restart.
 */

export type ProviderRow = {
  id: string;
  handler: string;
  config: Record<string, unknown>;
  credentials_encrypted: string | null;
};

export async function resolveAdapter(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  fallback?: StorageAdapter,
): Promise<StorageAdapter> {
  const [row] = await tx.execute<ProviderRow>(sql`
    select id, handler, config, credentials_encrypted
      from storage_providers
     where is_enabled
       and (workspace_id = ${workspaceId} or workspace_id is null)
     order by
       -- The workspace's own provider first, then the instance default, then
       -- whatever else is enabled. Deterministic, so two requests in the same
       -- second cannot write to different buckets.
       (workspace_id is not null) desc, is_default desc, created_at
     limit 1`);

  if (!row) {
    if (fallback) return fallback;
    throw new StorageError(
      'no_provider',
      'No storage provider is configured. Add one in Admin → Integrations → Storage.',
    );
  }
  return adapterFor(row);
}

export function adapterFor(row: ProviderRow): StorageAdapter {
  const config = row.config ?? {};

  if (row.handler === 'local') {
    return localAdapter({
      root: String(config.root ?? process.env.STORAGE_LOCAL_ROOT ?? './.storage'),
      secret: credentialsOf(row).secret ?? process.env.STORAGE_URL_SECRET ?? 'dev-storage-secret',
      baseUrl: String(config.baseUrl ?? process.env.STORAGE_LOCAL_URL ?? 'http://localhost:3000/api/storage'),
    });
  }

  const credentials = credentialsOf(row);
  const host = String(config.host ?? '');
  if (!host) {
    throw new StorageError('misconfigured', `Provider "${row.handler}" has no host configured.`);
  }
  if (!credentials.accessKeyId || !credentials.secretAccessKey) {
    throw new StorageError('misconfigured', `Provider "${row.handler}" has no credentials.`);
  }

  return s3Adapter({
    handler: row.handler,
    host,
    // R2 uses `auto`; S3-alikes want a real region. Defaulting to `auto` makes
    // the common case work and the uncommon one explicit.
    region: String(config.region ?? 'auto'),
    accessKeyId: credentials.accessKeyId,
    secretAccessKey: credentials.secretAccessKey,
    bucketInPath: config.bucketInPath ? String(config.bucketInPath) : undefined,
  });
}

function credentialsOf(row: ProviderRow): Record<string, string> {
  if (!row.credentials_encrypted) return {};
  try {
    return JSON.parse(unwrapKey(row.credentials_encrypted).toString('utf8')) as Record<string, string>;
  } catch {
    throw new StorageError(
      'unreadable_credentials',
      `Could not decrypt the credentials for "${row.handler}". Has STORAGE_KEK changed?`,
    );
  }
}
