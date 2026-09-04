import { and, eq, gt, inArray, isNull, or, sql } from 'drizzle-orm';
import {
  aiFeatureState,
  creditBuckets,
  features,
  instanceSettings,
  instanceModules,
  planEntitlements,
  plans,
  subscriptions,
  usageCounters,
  workspaces,
  type WorkspaceScopedDb,
} from '@mamal/db';
import type { EntitlementRow, FeatureRow, ResolveContext } from './types.ts';

const FREE_PLAN_KEY = 'free';

/** Start of the current UTC month — the period every quota is counted against. */
export function currentPeriodStart(now = new Date()): Date {
  return new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), 1));
}

/**
 * Builds the resolver's input from Postgres in one pass.
 *
 * Every workspace always gets the FREE plan as a floor, so a lapsed
 * subscription degrades to free rather than to nothing.
 */
export async function loadContext(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
): Promise<ResolveContext | null> {
  const [feature] = await tx.select().from(features).where(eq(features.key, featureKey)).limit(1);
  if (!feature) return null;

  const [settings] = await tx.select().from(instanceSettings).limit(1);
  const [workspace] = await tx.select().from(workspaces).where(eq(workspaces.id, workspaceId)).limit(1);

  const [module] = await tx
    .select()
    .from(instanceModules)
    .where(eq(instanceModules.key, feature.tool))
    .limit(1);
  // 'core' is not a module; it is always present.
  const toolInstalled =
    feature.tool === 'core' ? true : Boolean(module?.installed && module?.enabled);

  const activePlans = await tx
    .select({
      planId: plans.id,
      planKey: plans.key,
      planKind: plans.kind,
      featureKey: planEntitlements.featureKey,
      mode: planEntitlements.mode,
      limitValue: planEntitlements.limitValue,
      quotaValue: planEntitlements.quotaValue,
      quotaPeriod: planEntitlements.quotaPeriod,
      creditCost: planEntitlements.creditCost,
      overage: planEntitlements.overage,
    })
    .from(planEntitlements)
    .innerJoin(plans, eq(plans.id, planEntitlements.planId))
    .leftJoin(
      subscriptions,
      and(eq(subscriptions.planId, plans.id), eq(subscriptions.workspaceId, workspaceId)),
    )
    .where(
      and(
        eq(planEntitlements.featureKey, featureKey),
        or(
          eq(plans.key, FREE_PLAN_KEY), // the floor, always applied
          inArray(subscriptions.status, ['active', 'trialing']),
        ),
      ),
    );

  const entitlements = activePlans as EntitlementRow[];
  const hasLifetimePlan = entitlements.some((e) => e.planKind === 'lifetime');
  const hasNonLifetimeAiGrant = entitlements.some(
    (e) => e.planKind !== 'lifetime' && e.planKind !== 'free' && e.mode !== 'deny',
  );

  const [usage] = await tx
    .select({ used: usageCounters.used })
    .from(usageCounters)
    .where(
      and(
        eq(usageCounters.workspaceId, workspaceId),
        eq(usageCounters.featureKey, featureKey),
        eq(usageCounters.periodStart, currentPeriodStart()),
      ),
    )
    .limit(1);

  const [balance] = await tx
    .select({ total: sql<number>`coalesce(sum(${creditBuckets.remaining}), 0)::int` })
    .from(creditBuckets)
    .where(
      and(
        eq(creditBuckets.workspaceId, workspaceId),
        gt(creditBuckets.remaining, 0),
        or(isNull(creditBuckets.expiresAt), gt(creditBuckets.expiresAt, new Date())),
      ),
    );

  const aiEnabled = feature.isAi
    ? await resolveAiToggles(tx, workspaceId, featureKey)
    : { instance: true, tenant: true, feature: true };

  return {
    workspaceId,
    entitlements,
    feature: feature as FeatureRow,
    used: Number(usage?.used ?? 0),
    creditBalance: Number(balance?.total ?? 0),
    hasLifetimePlan,
    hasNonLifetimeAiGrant,
    aiInstanceEnabled: (settings?.aiMasterEnabled ?? true) && aiEnabled.instance,
    aiTenantEnabled: (workspace?.aiEnabled ?? true) && aiEnabled.tenant,
    aiFeatureEnabled: aiEnabled.feature,
    lifetimeAiViaCredits: settings?.lifetimeAiViaCredits ?? false,
    toolInstalled,
  };
}

/** Instance-scope and workspace-scope overrides for one AI feature. */
async function resolveAiToggles(tx: WorkspaceScopedDb, workspaceId: string, featureKey: string) {
  const rows = await tx
    .select({ scope: aiFeatureState.scope, scopeId: aiFeatureState.scopeId, isEnabled: aiFeatureState.isEnabled })
    .from(aiFeatureState)
    .where(eq(aiFeatureState.featureKey, featureKey));

  const instanceRow = rows.find((r) => r.scope === 'instance');
  const workspaceRow = rows.find((r) => r.scope === 'workspace' && r.scopeId === workspaceId);
  return {
    instance: instanceRow?.isEnabled ?? true,
    tenant: true,
    feature: workspaceRow?.isEnabled ?? true,
  };
}
