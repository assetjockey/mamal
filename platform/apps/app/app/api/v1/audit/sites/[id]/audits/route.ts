import { handle, ok, fail, page } from '@/lib/api';
import { listAudits, runSite, AuditNotAllowed } from '@/lib/audit-ops';

const siteIdFrom = (pathname: string) => pathname.split('/').at(-2);

/** GET /api/v1/audit/sites/:id/audits — run history. */
export const GET = handle(listAudits.scope, async ({ tx, url, workspaceId }) => {
  const limit = url.searchParams.has('limit') ? Number(url.searchParams.get('limit')) : 25;
  const r = await listAudits.call(tx, workspaceId, {
    site_id: siteIdFrom(url.pathname),
    limit,
    cursor: url.searchParams.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});

/**
 * POST /api/v1/audit/sites/:id/audits — queue a crawl.
 *
 * 202, not 200: the crawl runs on the `audit.crawl` queue in bounded slices, so
 * "accepted, poll here" is the honest answer. Holding the request open for a
 * 25 000-page site would not be.
 */
export const POST = handle(runSite.scope, async ({ tx, url, workspaceId }) => {
  try {
    const r = await runSite.call(tx, workspaceId, { site_id: siteIdFrom(url.pathname) });
    if (!r.ok) return fail(400, 'invalid_id', 'That is not a site id.');
    const started = r.value as { id: string };
    return ok({ ...started, poll: `/api/v1/audit/audits/${started.id}` }, { status: 202 });
  } catch (e) {
    if (e instanceof AuditNotAllowed) {
      // 402 with the resolver's own reason: the caller must be able to tell
      // "out of quota" from "not on your plan" without parsing prose.
      return fail(402, e.reason, e.message);
    }
    throw e;
  }
});
