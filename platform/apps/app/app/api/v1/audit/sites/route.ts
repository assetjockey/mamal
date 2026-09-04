import { handle, ok, fail, page } from '@/lib/api';
import { listSites } from '@/lib/audit-ops';

const num = (v: string | null) => (v === null ? undefined : Number(v));

/** GET /api/v1/audit/sites */
export const GET = handle(listSites.scope, async ({ tx, url, workspaceId }) => {
  const limit = num(url.searchParams.get('limit')) ?? 25;
  const r = await listSites.call(tx, workspaceId, {
    limit,
    cursor: url.searchParams.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});
