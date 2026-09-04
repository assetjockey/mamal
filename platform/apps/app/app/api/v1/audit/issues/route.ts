import { handle, ok, fail, page } from '@/lib/api';
import { listIssues } from '@/lib/audit-ops';

/**
 * GET /api/v1/audit/issues
 *
 * Filters are validated by the op's own schema rather than an allow-list kept
 * here, so REST and MCP accept exactly the same values and an unknown severity
 * is rejected identically by both.
 */
export const GET = handle(listIssues.scope, async ({ tx, url, workspaceId }) => {
  const q = url.searchParams;
  const limit = q.has('limit') ? Number(q.get('limit')) : 25;
  const r = await listIssues.call(tx, workspaceId, {
    status: q.get('status') ?? undefined,
    severity: q.get('severity') ?? undefined,
    rule_id: q.get('rule_id') ?? undefined,
    audit_id: q.get('audit_id') ?? undefined,
    limit,
    cursor: q.get('cursor') ?? undefined,
  });
  if (!r.ok) return fail(400, 'invalid_query', r.issues);
  return ok(page(r.value, limit));
});
