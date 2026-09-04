import { handle, ok, fail, page } from '@/lib/api';
import { listLinks, shorten } from '@/lib/link-ops';

/** GET /api/v1/link/links */
export const GET = handle(listLinks.scope, async ({ tx, url, workspaceId }) => {
  const limit = url.searchParams.has('limit') ? Number(url.searchParams.get('limit')) : 25;
  const r = await listLinks.call(tx, workspaceId, {
    limit,
    cursor: url.searchParams.get('cursor') ?? undefined,
    kind: url.searchParams.get('kind') ?? undefined,
    campaign: url.searchParams.get('campaign') ?? undefined,
    q: url.searchParams.get('q') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});

/** POST /api/v1/link/links — create a short link. */
export const POST = handle(shorten.scope, async ({ tx, body, workspaceId }) => {
  const r = await shorten.call(tx, workspaceId, body);
  if (!r.ok) return fail(400, 'invalid_body', r.issues);
  // 201: something now exists at a URL the caller can use immediately.
  return ok(r.value, { status: 201 });
});
