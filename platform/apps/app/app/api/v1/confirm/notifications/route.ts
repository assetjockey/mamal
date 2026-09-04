import { handle, ok, fail, page } from '@/lib/api';
import { listWidgets } from '@/lib/confirm-ops';

/** GET /api/v1/confirm/notifications */
export const GET = handle(listWidgets.scope, async ({ tx, url, workspaceId }) => {
  const limit = url.searchParams.has('limit') ? Number(url.searchParams.get('limit')) : 25;
  const r = await listWidgets.call(tx, workspaceId, {
    campaign_id: url.searchParams.get('campaign_id') ?? undefined,
    limit,
    cursor: url.searchParams.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});
