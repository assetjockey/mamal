import { handle, ok, fail, page } from '@/lib/api';
import { listCampaigns } from '@/lib/confirm-ops';

/** GET /api/v1/confirm/campaigns */
export const GET = handle(listCampaigns.scope, async ({ tx, url, workspaceId }) => {
  const limit = url.searchParams.has('limit') ? Number(url.searchParams.get('limit')) : 25;
  const r = await listCampaigns.call(tx, workspaceId, {
    limit, cursor: url.searchParams.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});
