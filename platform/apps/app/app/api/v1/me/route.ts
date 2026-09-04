import { sql } from 'drizzle-orm';
import { handle, ok } from '@/lib/api';

/**
 * GET /api/v1/me — what this key can see and do.
 *
 * The first call any client should make: it confirms the key works, names the
 * workspace it is bound to, and lists its scopes, so a permission failure later
 * is diagnosable without guessing. Requires no scope beyond a valid key.
 */
export const GET = handle(null, async ({ tx, key, workspaceId }) => {
  const [ws] = await tx.execute<{ name: string; slug: string }>(sql`
    select name, slug from workspaces where id = ${workspaceId}`);

  return ok({
    workspace: { id: workspaceId, name: ws?.name ?? null, slug: ws?.slug ?? null },
    key: { id: key.id, name: key.name, scopes: key.scopes },
  });
});
