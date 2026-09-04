import type { EntitlementMode, OverageBehaviour } from '@mamal/db';

export type DenyReason =
  // AI kill switches — checked first, most specific message wins
  | 'ai_disabled_instance'
  | 'ai_disabled_tenant'
  | 'ai_disabled_feature'
  | 'ai_excluded_lifetime'
  // commercial
  | 'not_in_plan'
  | 'limit_reached'
  | 'quota_exhausted'
  | 'insufficient_credits'
  // availability
  | 'tool_unavailable'
  | 'unknown_feature';

export type Decision =
  | {
      allowed: true;
      /** Credits this action will cost. 0 means it is covered by the plan. */
      cost: number;
      /** Resolved numeric allowance, if the feature has one. -1 = unlimited. */
      limit?: number;
      quota?: number;
      used?: number;
      remainingCredits: number;
      source: 'allow' | 'limit' | 'quota' | 'credits' | 'overage_soft';
    }
  | {
      allowed: false;
      reason: DenyReason;
      /** Human-facing sentence. The reason decides which one, so "your admin
       *  disabled AI" is never confused with "not on your plan". */
      message: string;
      limit?: number;
      quota?: number;
      used?: number;
      remainingCredits: number;
      /** What the user can do about it. */
      upsell?: { kind: 'plan' | 'credits'; planKey?: string; creditsNeeded?: number };
    };

export type EntitlementRow = {
  planId: string;
  planKey: string;
  planKind: string;
  featureKey: string;
  mode: EntitlementMode;
  limitValue: number | null;
  quotaValue: number | null;
  quotaPeriod: string | null;
  creditCost: number | null;
  overage: OverageBehaviour;
};

export type FeatureRow = {
  key: string;
  tool: string;
  kind: string;
  isAi: boolean;
  freeTierAllowed: boolean;
  defaultCreditCost: number;
};

export type ResolveContext = {
  workspaceId: string;
  /** Active subscriptions' entitlements, plus the FREE plan floor. */
  entitlements: EntitlementRow[];
  feature: FeatureRow;
  /** Current period usage for quota features. */
  used: number;
  creditBalance: number;
  /** Whether any active plan is a lifetime plan. */
  hasLifetimePlan: boolean;
  /** Whether a NON-lifetime plan grants this AI feature. */
  hasNonLifetimeAiGrant: boolean;
  aiInstanceEnabled: boolean;
  aiTenantEnabled: boolean;
  aiFeatureEnabled: boolean;
  /** Instance escape hatch: lifetime holders may spend credits on AI. */
  lifetimeAiViaCredits: boolean;
  toolInstalled: boolean;
};
