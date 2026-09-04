import { handle, ok, fail } from '@/lib/api';
import { getAudit } from '@/lib/audit-ops';

/** GET /api/v1/audit/audits/:id */
export const GET = handle(getAudit.scope, async ({ tx, url, workspaceId }) => {
  const r = await getAudit.call(tx, workspaceId, { audit_id: url.pathname.split('/').at(-1) });
  if (!r.ok) return fail(400, 'invalid_id', 'That is not an audit id.');
  // RLS makes "another tenant's audit" and "no such audit" the same answer,
  // which is the correct one to give: existence is itself information.
  if (!r.value) return fail(404, 'not_found', 'No such audit.');
  return ok(r.value);
});
