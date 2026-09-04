import type { Decision, DenyReason, ResolveContext } from './types.ts';

export const UNLIMITED = -1;

/**
 * The entitlement resolver.
 *
 * Three selling motions coexist — per-tool plans, a unified plan, and lifetime
 * — and a workspace may hold several at once. The merge rule is "most generous
 * wins", with two deliberately different arithmetics:
 *
 *   limits  -> MAX   (Link Pro's 10,000 links beats Starter's 500)
 *   quotas  -> SUM   (50k + 100k pageviews = 150k)
 *
 * AI is the exception to "most generous wins": a lifetime plan can never
 * contribute an AI grant, and the kill switches are evaluated FIRST so the
 * message the user sees names the actual cause.
 */
export function resolve(ctx: ResolveContext, quantity = 1): Decision {
  const { feature, creditBalance } = ctx;
  const base = { remainingCredits: creditBalance };

  // -- 0. availability ------------------------------------------------------
  if (!ctx.toolInstalled) {
    return {
      ...base,
      allowed: false,
      reason: 'tool_unavailable',
      message: `The ${feature.tool} tool is not installed on this instance.`,
    };
  }

  // -- 1. AI kill switches — fail closed, first failure is what we report ----
  if (feature.isAi) {
    const aiDenial = resolveAiSwitches(ctx);
    if (aiDenial) return { ...base, allowed: false, ...aiDenial };
  }

  // -- 2. merge across every active plan ------------------------------------
  const rows = ctx.entitlements.filter((e) => e.featureKey === feature.key);
  if (rows.length === 0) {
    return {
      ...base,
      allowed: false,
      reason: 'not_in_plan',
      message: `${feature.key} is not included in your plan.`,
      upsell: { kind: 'plan' },
    };
  }

  // A 'deny' only wins when it is the sole opinion — otherwise a plan that
  // grants the feature overrides a plan that is silent about it.
  let opinions = rows.filter((r) => r.mode !== 'deny');

  // The FREE plan is a floor, not a contributor. It exists so a lapsed
  // subscription degrades to free rather than to nothing — but once a paid
  // plan has an opinion, free must drop out of the merge. Otherwise SUM
  // silently hands every paying customer the free tier's quota on top of
  // what they bought, which is not what the pricing page says.
  const paidOpinions = opinions.filter((r) => r.planKind !== 'free');
  if (paidOpinions.length > 0) opinions = paidOpinions;
  if (opinions.length === 0) {
    return {
      ...base,
      allowed: false,
      reason: 'not_in_plan',
      message: `${feature.key} is not included in your plan.`,
      upsell: { kind: 'plan' },
    };
  }

  if (opinions.some((r) => r.mode === 'allow')) {
    return { ...base, allowed: true, cost: 0, source: 'allow' };
  }

  // limits: MAX, with -1 (unlimited) winning outright
  const limitRows = opinions.filter((r) => r.mode === 'limit');
  if (limitRows.length > 0) {
    const values = limitRows.map((r) => r.limitValue ?? 0);
    const limit = values.includes(UNLIMITED) ? UNLIMITED : Math.max(...values);
    if (limit !== UNLIMITED && ctx.used + quantity > limit) {
      return overageOrDeny(ctx, limitRows, quantity, {
        ...base,
        allowed: false,
        reason: 'limit_reached',
        message: shortfall(ctx.used, limit, quantity),
        limit,
        used: ctx.used,
        upsell: { kind: 'plan' },
      });
    }
    return { ...base, allowed: true, cost: 0, limit, used: ctx.used, source: 'limit' };
  }

  // quotas: SUM, because two plans each buying headroom should stack
  const quotaRows = opinions.filter((r) => r.mode === 'quota');
  if (quotaRows.length > 0) {
    const values = quotaRows.map((r) => r.quotaValue ?? 0);
    const quota = values.includes(UNLIMITED)
      ? UNLIMITED
      : values.reduce((a, b) => a + b, 0);
    if (quota !== UNLIMITED && ctx.used + quantity > quota) {
      return overageOrDeny(ctx, quotaRows, quantity, {
        ...base,
        allowed: false,
        reason: 'quota_exhausted',
        message: `${shortfall(ctx.used, quota, quantity)} This resets next period.`,
        quota,
        used: ctx.used,
        upsell: { kind: 'plan' },
      });
    }
    return { ...base, allowed: true, cost: 0, quota, used: ctx.used, source: 'quota' };
  }

  // credits: cheapest plan rate wins
  const creditRows = opinions.filter((r) => r.mode === 'credits');
  if (creditRows.length > 0) {
    const perUnit = Math.min(
      ...creditRows.map((r) => r.creditCost ?? feature.defaultCreditCost),
    );
    return chargeCredits(ctx, perUnit * quantity);
  }

  return {
    ...base,
    allowed: false,
    reason: 'not_in_plan',
    message: `${feature.key} is not included in your plan.`,
    upsell: { kind: 'plan' },
  };
}

/**
 * The refusal, phrased for what was actually asked.
 *
 * "You have used 26 of 10,000" is true and useless when somebody asked to
 * import ten thousand rows: it reads as though there is room. For a batch the
 * useful sentence names the request and the shortfall, because that is the
 * number they have to change.
 */
function shortfall(used: number, allowance: number, quantity: number): string {
  if (quantity <= 1) return `You have used ${used.toLocaleString()} of ${allowance.toLocaleString()}.`;
  const room = Math.max(0, allowance - used);
  return (
    `This would add ${quantity.toLocaleString()} to the ${used.toLocaleString()} you have, ` +
    `and your plan allows ${allowance.toLocaleString()} — ` +
    (room > 0 ? `room for ${room.toLocaleString()} more.` : 'no room left.')
  );
}

/**
 * Lifetime plans exclude AI structurally. This is enforcement point 2 of 3 —
 * the others are the plan_entitlements trigger and the driver boundary in
 * packages/ai.
 */
type AiDenial = { reason: DenyReason; message: string };

function resolveAiSwitches(ctx: ResolveContext): AiDenial | null {
  if (!ctx.aiInstanceEnabled) {
    return {
      reason: 'ai_disabled_instance' as const,
      message: 'AI features are switched off for this installation.',
    };
  }
  if (!ctx.aiTenantEnabled) {
    return {
      reason: 'ai_disabled_tenant' as const,
      message: 'AI features are switched off for this workspace. An admin can re-enable them in Settings → AI.',
    };
  }
  if (!ctx.aiFeatureEnabled) {
    return {
      reason: 'ai_disabled_feature' as const,
      message: 'This AI feature has been switched off.',
    };
  }
  // A lifetime plan never contributes an AI grant. It also does not poison a
  // separately purchased AI subscription — hence hasNonLifetimeAiGrant.
  if (ctx.hasLifetimePlan && !ctx.hasNonLifetimeAiGrant && !ctx.lifetimeAiViaCredits) {
    return {
      reason: 'ai_excluded_lifetime' as const,
      message:
        'Lifetime plans cover the platform but not AI. Add a subscription, or enable pay-as-you-go credits.',
    };
  }
  return null;
}

function overageOrDeny(
  ctx: ResolveContext,
  rows: { overage: string; creditCost: number | null }[],
  quantity: number,
  denial: Extract<Decision, { allowed: false }>,
): Decision {
  // Being over an allowance is exactly when the caller most needs to render the
  // numbers — "each further page now costs a credit" is only meaningful next to
  // the allowance it passed. So every overage path carries the allowance through
  // rather than dropping it on the way to `allowed: true`.
  const allowance = { limit: denial.limit, quota: denial.quota };

  // The most permissive overage across the merged plans wins, same as limits.
  if (rows.some((r) => r.overage === 'soft')) {
    return {
      allowed: true,
      cost: 0,
      remainingCredits: ctx.creditBalance,
      used: ctx.used,
      ...allowance,
      source: 'overage_soft',
    };
  }
  if (rows.some((r) => r.overage === 'credits')) {
    const perUnit = Math.min(
      ...rows.filter((r) => r.overage === 'credits').map((r) => r.creditCost ?? ctx.feature.defaultCreditCost),
    );
    // Lifetime holders can buy their way past a quota, but never past AI.
    if (ctx.feature.isAi && ctx.hasLifetimePlan && !ctx.hasNonLifetimeAiGrant && !ctx.lifetimeAiViaCredits) {
      return denial;
    }
    return chargeCredits(ctx, perUnit * quantity, allowance);
  }
  return denial;
}

function chargeCredits(
  ctx: ResolveContext,
  cost: number,
  allowance?: { limit?: number; quota?: number },
): Decision {
  if (ctx.creditBalance < cost) {
    return {
      allowed: false,
      reason: 'insufficient_credits',
      message: `This costs ${cost} credit${cost === 1 ? '' : 's'} and you have ${ctx.creditBalance}.`,
      remainingCredits: ctx.creditBalance,
      used: ctx.used,
      ...allowance,
      upsell: { kind: 'credits', creditsNeeded: cost - ctx.creditBalance },
    };
  }
  return {
    allowed: true,
    cost,
    remainingCredits: ctx.creditBalance,
    used: ctx.used,
    ...allowance,
    source: 'credits',
  };
}
