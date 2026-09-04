/**
 * Idempotent seed. Safe to re-run: everything upserts on its natural key, so
 * `pnpm db:seed` after adding a feature or model only writes the delta.
 */
import { sql } from 'drizzle-orm';
import { closeDb, unsafeUnscopedDb } from '../src/client.ts';
import { FEATURES } from '../src/seed/features.ts';
import { PLANS, CREDIT_PACKS, type PlanSeed } from '../src/seed/plans.ts';
import { AI_FEATURES, AI_MODELS, AI_PROVIDERS, INSTANCE_MODULES } from '../src/seed/ai.ts';
import { AUTOMATION_TEMPLATES } from '../src/seed/automations.ts';
import { ruleSeedRows } from '@mamal/seo-checks';
import {
  aiFeatures,
  aiModels,
  aiProviders,
  automationTemplates,
  auditRules,
  creditPacks,
  features,
  instanceModules,
  instanceSettings,
  planCreditGrants,
  planEntitlements,
  planPrices,
  plans,
} from '../src/schema/index.ts';

const db = unsafeUnscopedDb();

async function main() {
  // ---- instance ---------------------------------------------------------
  await db
    .insert(instanceSettings)
    .values({ id: 'singleton' })
    .onConflictDoNothing({ target: instanceSettings.id });

  for (const m of INSTANCE_MODULES) {
    await db
      .insert(instanceModules)
      .values(m)
      .onConflictDoUpdate({
        target: instanceModules.key,
        set: { version: m.version, updatedAt: new Date() },
      });
  }
  console.log(`instance_modules: ${INSTANCE_MODULES.length}`);

  // ---- features ---------------------------------------------------------
  for (const f of FEATURES) {
    await db
      .insert(features)
      .values({
        key: f.key,
        tool: f.tool,
        name: f.name,
        description: f.description ?? null,
        category: f.category ?? null,
        kind: f.kind,
        isAi: f.isAi ?? false,
        freeTierAllowed: f.freeTierAllowed ?? false,
        defaultCreditCost: f.defaultCreditCost ?? 0,
        unit: f.unit ?? null,
      })
      .onConflictDoUpdate({
        target: features.key,
        set: {
          name: f.name,
          kind: f.kind,
          isAi: f.isAi ?? false,
          freeTierAllowed: f.freeTierAllowed ?? false,
          defaultCreditCost: f.defaultCreditCost ?? 0,
          updatedAt: new Date(),
        },
      });
  }
  console.log(`features: ${FEATURES.length} (${FEATURES.filter((f) => f.isAi).length} AI)`);

  // ---- plans ------------------------------------------------------------
  for (const p of PLANS) await seedPlan(p);
  console.log(`plans: ${PLANS.length}`);

  for (const pack of CREDIT_PACKS) {
    await db
      .insert(creditPacks)
      .values(pack)
      .onConflictDoUpdate({ target: creditPacks.key, set: { ...pack, updatedAt: new Date() } });
  }
  console.log(`credit_packs: ${CREDIT_PACKS.length}`);

  // ---- AI registry ------------------------------------------------------
  for (const p of AI_PROVIDERS) {
    await db
      .insert(aiProviders)
      .values(p)
      .onConflictDoUpdate({ target: aiProviders.key, set: { ...p, updatedAt: new Date() } });
  }
  for (const m of AI_MODELS) {
    await db
      .insert(aiModels)
      .values(m as typeof aiModels.$inferInsert)
      .onConflictDoUpdate({
        target: [aiModels.providerKey, aiModels.modelId],
        set: { label: m.label, creditCost: m.creditCost, vendorCostMicros: m.vendorCostMicros, updatedAt: new Date() },
      });
  }
  for (const f of AI_FEATURES) {
    await db
      .insert(aiFeatures)
      .values(f as typeof aiFeatures.$inferInsert)
      .onConflictDoUpdate({ target: aiFeatures.key, set: { name: f.name, updatedAt: new Date() } });
  }
  console.log(
    `ai: ${AI_PROVIDERS.length} providers, ${AI_MODELS.length} models, ${AI_FEATURES.length} features`,
  );

  // ---- automation templates --------------------------------------------
  for (const t of AUTOMATION_TEMPLATES) {
    await db
      .insert(automationTemplates)
      .values({
        key: t.key,
        name: t.name,
        description: t.description,
        category: t.category,
        requiredTools: t.requiredTools,
        definition: t.definition as Record<string, unknown>,
      })
      .onConflictDoUpdate({
        target: automationTemplates.key,
        set: {
          name: t.name,
          description: t.description,
          requiredTools: t.requiredTools,
          definition: t.definition as Record<string, unknown>,
          updatedAt: new Date(),
        },
      });
  }
  console.log(
    `automation_templates: ${AUTOMATION_TEMPLATES.length} ` +
      `(${AUTOMATION_TEMPLATES.filter((t) => t.requiredTools.length > 1).length} cross-tool)`,
  );

  // ---- audit rule catalogue ---------------------------------------------
  const rules = ruleSeedRows();
  for (const rule of rules) {
    await db
      .insert(auditRules)
      .values(rule)
      .onConflictDoUpdate({
        target: auditRules.id,
        set: {
          // Titles and guidance are ours to update; thresholds and enablement
          // may have been tuned by an operator, so they are left alone.
          title: rule.title,
          why: rule.why,
          howToFix: rule.howToFix,
          severity: rule.severity,
          weight: rule.weight,
          category: rule.category,
          isAiRelevant: rule.isAiRelevant,
          updatedAt: new Date(),
        },
      });
  }
  console.log(
    `audit_rules: ${rules.length} ` +
      `(${rules.filter((r) => r.isAiRelevant).length} AI-visibility)`,
  );

  await verify();
  await closeDb();
}

async function seedPlan(p: PlanSeed) {
  const [plan] = await db
    .insert(plans)
    .values({
      key: p.key,
      name: p.name,
      description: p.description ?? null,
      kind: p.kind,
      tool: p.tool ?? null,
      tierRank: p.tierRank,
      isDefaultSignup: p.isDefaultSignup ?? false,
      trialDays: p.trialDays ?? 0,
    })
    .onConflictDoUpdate({
      target: plans.key,
      set: { name: p.name, tierRank: p.tierRank, updatedAt: new Date() },
    })
    .returning();
  if (!plan) throw new Error(`failed to upsert plan ${p.key}`);

  for (const price of p.prices ?? []) {
    await db
      .insert(planPrices)
      .values({ planId: plan.id, interval: price.interval, amountCents: price.amountCents })
      .onConflictDoUpdate({
        target: [planPrices.planId, planPrices.interval, planPrices.currency],
        set: { amountCents: price.amountCents, updatedAt: new Date() },
      });
  }

  if (p.creditGrant) {
    await db
      .insert(planCreditGrants)
      .values({
        planId: plan.id,
        amount: p.creditGrant.amount,
        cadence: p.creditGrant.cadence,
        expiresAfterDays: p.creditGrant.expiresAfterDays ?? null,
        rollover: p.creditGrant.rollover ?? false,
      })
      .onConflictDoUpdate({
        target: [planCreditGrants.planId, planCreditGrants.cadence],
        set: { amount: p.creditGrant.amount, updatedAt: new Date() },
      });
  }

  // Entitlements are append-only in production; the seed replaces its own rows.
  await db.delete(planEntitlements).where(sql`plan_id = ${plan.id}`);
  for (const e of p.entitlements) {
    await db.insert(planEntitlements).values({
      planId: plan.id,
      featureKey: e.feature,
      mode: e.mode,
      limitValue: e.limit ?? null,
      quotaValue: e.quota ?? null,
      quotaPeriod: e.quotaPeriod ?? 'month',
      creditCost: e.creditCost ?? null,
      overage: e.overage ?? 'block',
    });
  }
}

/** The seed asserts its own invariants — a broken catalogue fails loudly here. */
async function verify() {
  const [leak] = await db.execute<{ n: number }>(sql`
    select count(*)::int as n
      from plan_entitlements pe
      join plans p on p.id = pe.plan_id
      join features f on f.key = pe.feature_key
     where p.kind = 'lifetime' and f.is_ai and pe.mode <> 'deny'`);
  if ((leak?.n ?? 0) > 0) throw new Error(`lifetime plans grant ${leak!.n} AI features`);

  const [orphan] = await db.execute<{ n: number }>(sql`
    select count(*)::int as n from plan_entitlements pe
     where not exists (select 1 from features f where f.key = pe.feature_key)`);
  if ((orphan?.n ?? 0) > 0) throw new Error(`${orphan!.n} entitlements reference unknown features`);

  const [freeLeak] = await db.execute<{ n: number; keys: string }>(sql`
    select count(*)::int as n, coalesce(string_agg(f.key, ', '), '') as keys
      from plan_entitlements pe
      join plans p on p.id = pe.plan_id
      join features f on f.key = pe.feature_key
     where p.kind = 'free'
       and not f.free_tier_allowed
       and pe.mode <> 'deny'
       -- a zero limit or quota is a denial in all but name
       and coalesce(pe.limit_value, 1) <> 0
       and coalesce(pe.quota_value, 1) <> 0`);
  if ((freeLeak?.n ?? 0) > 0) {
    throw new Error(
      `free plan grants ${freeLeak!.n} metered feature(s): ${freeLeak!.keys}. ` +
        `Free tier must never trigger a third-party invoice.`,
    );
  }

  console.log('invariants OK: lifetime excludes AI, no orphan entitlements, free tier is unmetered');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
