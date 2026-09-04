import { handle, ok, fail, page } from '@/lib/api';
import { createQr, listQr } from '@/lib/link-ops';

/** GET /api/v1/link/qr */
export const GET = handle(listQr.scope, async ({ tx, url, workspaceId }) => {
  const limit = url.searchParams.has('limit') ? Number(url.searchParams.get('limit')) : 25;
  const r = await listQr.call(tx, workspaceId, {
    limit, cursor: url.searchParams.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});

/** POST /api/v1/link/qr — mint a QR code. */
export const POST = handle(createQr.scope, async ({ tx, body, workspaceId }) => {
  const r = await createQr.call(tx, workspaceId, body);
  if (!r.ok) return fail(400, 'invalid_body', r.issues);
  return ok(r.value, { status: 201 });
});
