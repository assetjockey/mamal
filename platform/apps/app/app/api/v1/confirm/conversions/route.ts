import { handle, ok, fail } from '@/lib/api';
import { recordConversionOp } from '@/lib/confirm-ops';

/**
 * POST /api/v1/confirm/conversions — record a conversion.
 *
 * Distinct from the public `/api/c/conversion` webhook: that one is
 * pixel-key-authenticated for a customer's own backend, this one is API-key
 * authenticated and scoped. Both record *which* they were, so a proof line can
 * always be traced back to how it arrived.
 */
export const POST = handle(recordConversionOp.scope, async ({ tx, workspaceId, body }) => {
  const r = await recordConversionOp.call(tx, workspaceId, body ?? {});
  if (!r.ok) return fail(400, 'invalid_body', r.issues);
  return ok(r.value, { status: 201 });
});
