import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { capture, release, reserve } from '@mamal/credits';
import {
  AiUnavailable,
  generationRequestSchema,
  type AiDenyReason,
  type AiDriver,
  type GenerationRequest,
  type GenerationResult,
} from './types.ts';

export type ExecuteDeps = {
  /** Resolves a driver by provider key. Injected so tests need no network. */
  driverFor: (providerKey: string) => AiDriver | undefined;
  /** Decrypts a stored credential. */
  decrypt: (ciphertext: string) => string;
  now?: () => Date;
};

export type ExecuteOptions = {
  workspaceId: string;
  jobId?: string;
  /** Overrides the feature's default model, when the plan allows a choice. */
  modelId?: string;
};

type ModelRow = {
  id: string;
  provider_key: string;
  model_id: string;
  credit_cost: number;
  cost_unit: string;
  vendor_cost_micros: number;
  base_url: string | null;
  is_enabled: boolean;
  provider_enabled: boolean;
};

/**
 * The ONLY way to run an AI generation.
 *
 * This is enforcement point 3 of 3 for "lifetime plans exclude AI". The other
 * two — the plan_entitlements trigger and the entitlement resolver — can both
 * be defeated by a bug that writes rows directly or by a caller that skips the
 * resolver. This one cannot: the eslint boundary forbids importing a provider
 * SDK anywhere outside this package, so every generation re-resolves
 * entitlements server-side immediately before the vendor call.
 *
 * Credits are HELD before the call and captured at the true unit count after,
 * so a failed generation costs the user nothing.
 */
export async function execute(
  tx: WorkspaceScopedDb,
  request: GenerationRequest,
  options: ExecuteOptions,
  deps: ExecuteDeps,
): Promise<GenerationResult> {
  const parsed = generationRequestSchema.parse(request);
  const { workspaceId } = options;

  // -- 1. re-resolve entitlements, right here, every time --------------------
  const ctx = await loadContext(tx, workspaceId, parsed.featureKey);
  if (!ctx) {
    throw new AiUnavailable('unknown_feature', `${parsed.featureKey} is not a known feature`);
  }
  if (!ctx.feature.isAi) {
    throw new AiUnavailable(
      'unknown_feature',
      `${parsed.featureKey} is not marked isAi — it would bypass the kill switch`,
    );
  }

  const units = Math.max(1, Math.ceil(parsed.expectedUnits ?? 1));
  const decision = resolve(ctx, units);
  if (!decision.allowed) {
    throw new AiUnavailable(decision.reason as AiDenyReason, decision.message);
  }

  // -- 2. pick a model and a credential -------------------------------------
  const model = await selectModel(tx, parsed.featureKey, options.modelId);
  if (!model) {
    throw new AiUnavailable(
      'no_model_available',
      `no enabled model for ${parsed.featureKey}. An admin may have disabled the provider.`,
    );
  }

  const credential = await selectCredential(tx, workspaceId, model.provider_key);
  if (!credential) {
    throw new AiUnavailable(
      'no_credential',
      `no API key configured for ${model.provider_key}`,
    );
  }

  const driver = deps.driverFor(model.provider_key);
  if (!driver) {
    throw new AiUnavailable('no_model_available', `no driver for ${model.provider_key}`);
  }

  // A stored key that will not decrypt is almost always a rotated
  // CREDENTIALS_SECRET or a value written outside the admin UI. Fail here with
  // something actionable rather than leaking a crypto error into the UI.
  let apiKey: string;
  try {
    apiKey = deps.decrypt(credential.encrypted);
  } catch {
    throw new AiUnavailable(
      'no_credential',
      `The stored ${model.provider_key} key could not be read. Re-enter it in Settings → AI.`,
    );
  }

  // BYO keys are charged a reduced platform fee, not zero — we still pay for
  // orchestration, storage and retries.
  const rate = decision.cost > 0 ? decision.cost / units : model.credit_cost;
  const estimated = credential.isByo
    ? Math.max(1, Math.ceil(rate * units * BYO_FEE_RATIO))
    : Math.ceil(rate * units);

  // -- 3. hold, call, settle -------------------------------------------------
  const hold = await reserve(tx, workspaceId, estimated, {
    featureKey: parsed.featureKey,
    jobId: options.jobId,
  });

  const [generation] = await tx.execute<{ id: string }>(sql`
    insert into ai_generations
      (workspace_id, feature_key, model_id, status, input, hold_id, byo_key)
    values (${workspaceId}, ${parsed.featureKey}, ${model.id}, 'running',
            ${JSON.stringify({ prompt: parsed.prompt, options: parsed.options ?? {} })}::jsonb,
            ${hold.id}, ${credential.isByo})
    returning id`);

  let result: GenerationResult;
  try {
    result = await driver.generate(parsed, {
      apiKey,
      modelId: model.model_id,
      baseUrl: model.base_url ?? undefined,
    });
  } catch (err) {
    await release(tx, workspaceId, hold.id);
    await failGeneration(tx, generation!.id, err instanceof Error ? err.message : String(err));
    throw err;
  }

  if (!result.ok) {
    // A failed generation costs nothing. This is the bug the source products
    // all have: they debit on dispatch.
    await release(tx, workspaceId, hold.id);
    await failGeneration(tx, generation!.id, result.error ?? 'generation failed');
    return result;
  }

  const actualUnits = Math.max(1, Math.ceil(result.units || units));
  const actual = credential.isByo
    ? Math.max(1, Math.ceil(rate * actualUnits * BYO_FEE_RATIO))
    : Math.ceil(rate * actualUnits);

  const { charged } = await capture(tx, workspaceId, hold.id, {
    actualAmount: actual,
    idempotencyKey: `${options.jobId ?? generation!.id}:ai-capture`,
    quantity: actualUnits,
  });

  await tx.execute(sql`
    update ai_generations
       set status = 'completed', output_text = ${result.text ?? null},
           input_tokens = ${result.inputTokens}, output_tokens = ${result.outputTokens},
           units = ${actualUnits},
           vendor_cost_micros = ${credential.isByo ? 0 : result.vendorCostMicros},
           credits_charged = ${charged}, latency_ms = ${result.latencyMs},
           external_task_id = ${result.externalTaskId ?? null}, updated_at = now()
     where id = ${generation!.id}`);

  return result;
}

/** BYO keys still pay for orchestration — 20% of the normal rate. */
export const BYO_FEE_RATIO = 0.2;

async function failGeneration(tx: WorkspaceScopedDb, id: string, error: string) {
  await tx.execute(sql`
    update ai_generations set status = 'failed', error = ${error}, updated_at = now()
     where id = ${id}`);
}

async function selectModel(
  tx: WorkspaceScopedDb,
  featureKey: string,
  override?: string,
): Promise<ModelRow | null> {
  const [row] = await tx.execute<ModelRow>(sql`
    select m.id, m.provider_key, m.model_id, m.credit_cost, m.cost_unit,
           m.vendor_cost_micros, p.base_url, m.is_enabled,
           p.is_enabled as provider_enabled
      from ai_features f
      join ai_models m
        on m.id = coalesce(
             ${override ?? null}::uuid,
             f.default_model_id,
             f.fallback_model_id,
             (select id from ai_models m2
               where m2.modality = f.modality and m2.is_enabled
               order by m2.is_recommended desc, m2.sort_order limit 1))
      join ai_providers p on p.key = m.provider_key
     where f.key = ${featureKey}
       -- A disabled model or provider falls through to the fallback rather
       -- than failing the feature outright.
       and m.is_enabled and p.is_enabled
     limit 1`);
  return row ?? null;
}

async function selectCredential(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  providerKey: string,
): Promise<{ encrypted: string; isByo: boolean } | null> {
  // A workspace's own key wins over the instance key.
  const [byo] = await tx.execute<{ encrypted_key: string }>(sql`
    select encrypted_key from ai_credentials
     where scope = 'workspace' and scope_id = ${workspaceId}
       and provider_key = ${providerKey} and is_active
     limit 1`);
  if (byo) return { encrypted: byo.encrypted_key, isByo: true };

  const [instance] = await tx.execute<{ encrypted_key: string }>(sql`
    select encrypted_key from ai_credentials
     where scope = 'instance' and provider_key = ${providerKey} and is_active
     limit 1`);
  return instance ? { encrypted: instance.encrypted_key, isByo: false } : null;
}

/**
 * Flipping the instance master switch is a kill, not a hide: in-flight
 * generations are cancelled and their holds released, so nobody is billed for
 * work that will never finish.
 */
export async function cancelInFlight(tx: WorkspaceScopedDb): Promise<number> {
  const rows = await tx.execute<{ id: string; workspace_id: string; hold_id: string | null }>(sql`
    select id, workspace_id, hold_id from ai_generations
     where status in ('pending', 'running')`);

  for (const row of rows) {
    if (row.hold_id) await release(tx, row.workspace_id, row.hold_id);
    await tx.execute(sql`
      update ai_generations
         set status = 'cancelled', error = 'AI was disabled while this was running',
             updated_at = now()
       where id = ${row.id}`);
  }
  return rows.length;
}
