/**
 * The AI runtime guard — enforcement point 3 of 3 for lifetime's AI exclusion.
 *
 * Points 1 and 2 (the plan_entitlements trigger and the resolver) can both be
 * defeated: the trigger by a direct write, the resolver by a caller that skips
 * it. This one cannot be skipped, because the eslint boundary means a provider
 * SDK cannot be reached from anywhere else. These tests prove it actually
 * re-checks rather than trusting its caller.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { balance } from '@mamal/credits';
import { execute, cancelInFlight, BYO_FEE_RATIO } from '../execute.ts';
import { AiUnavailable, type AiDriver, type GenerationResult } from '../types.ts';
import { decryptCredential, encryptCredential, keyHint } from '../crypto.ts';

describe('credential crypto', () => {
  const secret = 'test-secret-not-used-in-production';
  it('round-trips', () => {
    const key = 'sk-ant-super-secret-value';
    const enc = encryptCredential(key, secret);
    expect(enc).not.toContain('secret');
    expect(decryptCredential(enc, secret)).toBe(key);
  });
  it('detects tampering rather than decrypting to garbage', () => {
    const enc = encryptCredential('sk-ant-abc', secret);
    const [iv, tag] = enc.split('.');
    const tampered = [iv, tag, Buffer.from('evil').toString('base64')].join('.');
    expect(() => decryptCredential(tampered, secret)).toThrow();
  });
  it('exposes only the last four characters', () => {
    expect(keyHint('sk-ant-0000-WXYZ')).toBe('…WXYZ');
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('ai.execute', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `ai${Date.now()}`;
  let ws = '';
  const FEATURE = 'market.ai_copy';

  /** A driver that never touches the network but records that it was reached. */
  let calls = 0;
  let nextResult: GenerationResult = {
    ok: true, text: 'generated', units: 2, inputTokens: 100, outputTokens: 1500,
    vendorCostMicros: 4500, latencyMs: 42,
  };
  const fakeDriver: AiDriver = {
    key: 'anthropic',
    modalities: ['text'],
    async generate() {
      calls++;
      return nextResult;
    },
  };
  const deps = { driverFor: () => fakeDriver, decrypt: (s: string) => s };

  const run = (over: Record<string, unknown> = {}) =>
    withWorkspace(
      ws,
      (tx) =>
        execute(
          tx,
          { featureKey: FEATURE, prompt: 'write an ad', modality: 'text', expectedUnits: 2 },
          { workspaceId: ws, jobId: `${tag}-${Math.random()}`, ...over },
          deps,
        ),
      { db },
    );

  const subscribe = (planKey: string) =>
    asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status)
        select ${ws}, id, 'active' from plans where key = ${planKey}`),
      { db },
    );

  const grant = (amount: number) =>
    asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into credit_buckets (workspace_id, source, amount, remaining)
        values (${ws}, 'admin', ${amount}, ${amount})`),
      { db },
    );

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'AI') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'AI', ${u!.id}) returning id`);
      ws = w!.id;
      await tx.execute(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true)`);
      // an instance-scoped credential, as an operator would configure
      await tx.execute(sql`
        insert into ai_credentials (scope, scope_id, provider_key, encrypted_key, key_hint)
        values ('instance', null, 'anthropic', 'plaintext-in-test', '…test')
        on conflict (scope, scope_id, provider_key) do update set is_active = true`);
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from ai_credentials where scope = 'instance' and provider_key = 'anthropic'`);
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    calls = 0;
    nextResult = {
      ok: true, text: 'generated', units: 2, inputTokens: 100, outputTokens: 1500,
      vendorCostMicros: 4500, latencyMs: 42,
    };
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from ai_generations where workspace_id = ${ws}`);
      await tx.execute(sql`delete from credit_entries where workspace_id = ${ws}`);
      await tx.execute(sql`delete from credit_holds where workspace_id = ${ws}`);
      await tx.execute(sql`delete from credit_buckets where workspace_id = ${ws}`);
      await tx.execute(sql`delete from subscriptions where workspace_id = ${ws}`);
      await tx.execute(sql`update instance_settings set ai_master_enabled = true, lifetime_ai_via_credits = false`);
      await tx.execute(sql`update workspaces set ai_enabled = true where id = ${ws}`);
    }, { db });
  });

  describe('refuses before reaching the vendor', () => {
    it('on the free plan — never calls the driver', async () => {
      await expect(run()).rejects.toBeInstanceOf(AiUnavailable);
      expect(calls, 'the driver must not be reached when entitlements deny').toBe(0);
    });

    it('when the instance kill switch is off, even with credits and a plan', async () => {
      await subscribe('unified_starter');
      await grant(5_000);
      await asPlatformAdmin((tx) => tx.execute(sql`update instance_settings set ai_master_enabled = false`), { db });

      const err = await run().catch((e: AiUnavailable) => e);
      expect(err).toBeInstanceOf(AiUnavailable);
      expect((err as AiUnavailable).reason).toBe('ai_disabled_instance');
      expect(calls).toBe(0);
    });

    it('when the workspace has opted out', async () => {
      await subscribe('unified_starter');
      await grant(5_000);
      await asPlatformAdmin((tx) => tx.execute(sql`update workspaces set ai_enabled = false where id = ${ws}`), { db });

      const err = await run().catch((e: AiUnavailable) => e);
      expect((err as AiUnavailable).reason).toBe('ai_disabled_tenant');
      expect(calls).toBe(0);
    });

    /** The headline case: a lifetime holder with money in the bank. */
    it('on a lifetime plan with 5,000 credits available', async () => {
      await subscribe('lifetime_pro');
      await grant(5_000);

      const err = await run().catch((e: AiUnavailable) => e);
      expect((err as AiUnavailable).reason).toBe('ai_excluded_lifetime');
      expect(calls).toBe(0);
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(5_000);
    });

    it('when the balance cannot cover the cost', async () => {
      await subscribe('unified_starter');
      await grant(1);
      const err = await run().catch((e: AiUnavailable) => e);
      expect((err as AiUnavailable).reason).toBe('insufficient_credits');
      expect(calls).toBe(0);
    });

    /**
     * A key written outside the admin UI, or a rotated CREDENTIALS_SECRET,
     * used to surface a raw crypto error in the product.
     */
    it('when the stored key cannot be decrypted, with something actionable', async () => {
      await subscribe('unified_starter');
      await grant(5_000);
      const throwingDecrypt = {
        driverFor: () => fakeDriver,
        decrypt: () => { throw new Error('malformed credential payload'); },
      };
      const err = await withWorkspace(
        ws,
        (tx) => execute(tx, { featureKey: FEATURE, prompt: 'x', modality: 'text' },
          { workspaceId: ws }, throwingDecrypt),
        { db },
      ).catch((e: AiUnavailable) => e);

      expect(err).toBeInstanceOf(AiUnavailable);
      expect((err as AiUnavailable).reason).toBe('no_credential');
      expect((err as AiUnavailable).message).toMatch(/Re-enter it in Settings/);
      expect(calls, 'the provider must not be called with an unreadable key').toBe(0);
    });

    it('when the feature is not marked isAi — it would escape the kill switch', async () => {
      await subscribe('unified_starter');
      await grant(5_000);
      await expect(
        withWorkspace(
          ws,
          (tx) => execute(tx, { featureKey: 'link.links', prompt: 'x', modality: 'text' },
            { workspaceId: ws }, deps),
          { db },
        ),
      ).rejects.toThrow(/not marked isAi/);
      expect(calls).toBe(0);
    });
  });

  describe('when allowed', () => {
    beforeEach(async () => {
      await subscribe('unified_starter');
      await grant(5_000);
    });

    it('calls the driver and charges the true unit count', async () => {
      const result = await run();
      expect(result.ok).toBe(true);
      expect(calls).toBe(1);
      // unified_starter prices market.ai_copy at 2 credits per 1k words; the
      // driver reported 2 units, so 4 credits.
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(4_996);
    });

    it('records the generation with its cost for the margin report', async () => {
      await run();
      const [gen] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ status: string; credits_charged: number; vendor_cost_micros: number; units: number }>(
          sql`select status, credits_charged, vendor_cost_micros, units from ai_generations
               where workspace_id = ${ws}`),
        { db },
      );
      expect(gen).toMatchObject({ status: 'completed', units: 2 });
      expect(Number(gen!.credits_charged)).toBe(4);
      expect(Number(gen!.vendor_cost_micros)).toBe(4500);
    });

    it('a failed generation costs nothing', async () => {
      nextResult = {
        ok: false, units: 0, inputTokens: 0, outputTokens: 0, vendorCostMicros: 0,
        latencyMs: 10, error: 'provider exploded',
      };
      const result = await run();
      expect(result.ok).toBe(false);
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(5_000);

      const [gen] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ status: string }>(sql`select status from ai_generations where workspace_id = ${ws}`),
        { db },
      );
      expect(gen!.status).toBe('failed');
    });

    it('a thrown driver error releases the hold too', async () => {
      const throwing = { driverFor: () => ({ ...fakeDriver, generate: async () => { throw new Error('socket hang up'); } }), decrypt: (s: string) => s };
      await expect(
        withWorkspace(
          ws,
          (tx) => execute(tx, { featureKey: FEATURE, prompt: 'x', modality: 'text', expectedUnits: 2 },
            { workspaceId: ws }, throwing),
          { db },
        ),
      ).rejects.toThrow('socket hang up');
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(5_000);
    });

    it('trues up when the driver used more than estimated', async () => {
      nextResult = { ...nextResult, units: 10 };
      await run();
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(4_980);
    });
  });

  describe('BYO keys', () => {
    beforeEach(async () => {
      await subscribe('unified_starter');
      await grant(5_000);
      await asPlatformAdmin(
        (tx) => tx.execute(sql`
          insert into ai_credentials (scope, scope_id, provider_key, encrypted_key, key_hint)
          values ('workspace', ${ws}, 'anthropic', 'workspace-own-key', '…mine')
          on conflict (scope, scope_id, provider_key) do update set is_active = true`),
        { db },
      );
    });

    it('charge a reduced platform fee, not zero', async () => {
      await run();
      // 2 credits/unit x 2 units x 0.2 = 0.8, rounded up to 1.
      const spent = 5_000 - (await withWorkspace(ws, (tx) => balance(tx, ws), { db }));
      expect(spent).toBe(Math.max(1, Math.ceil(2 * 2 * BYO_FEE_RATIO)));
    });

    it('record zero vendor cost so the margin report stays honest', async () => {
      await run();
      const [gen] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ byo_key: boolean; vendor_cost_micros: number }>(
          sql`select byo_key, vendor_cost_micros from ai_generations where workspace_id = ${ws}`),
        { db },
      );
      expect(gen!.byo_key).toBe(true);
      expect(Number(gen!.vendor_cost_micros)).toBe(0);
    });

    /** A BYO key is about cost, not entitlement. */
    it('do NOT unlock AI on a lifetime plan', async () => {
      await asPlatformAdmin((tx) => tx.execute(sql`delete from subscriptions where workspace_id = ${ws}`), { db });
      await subscribe('lifetime_pro');
      const err = await run().catch((e: AiUnavailable) => e);
      expect((err as AiUnavailable).reason).toBe('ai_excluded_lifetime');
      expect(calls).toBe(0);
    });
  });

  describe('cancelInFlight', () => {
    it('releases holds when AI is killed mid-generation', async () => {
      await subscribe('unified_starter');
      await grant(5_000);

      // a generation stuck in flight
      const hold = await withWorkspace(
        ws,
        async (tx) => {
          const { reserve } = await import('@mamal/credits');
          return reserve(tx, ws, 40, { featureKey: FEATURE });
        },
        { db },
      );
      await asPlatformAdmin(
        (tx) => tx.execute(sql`
          insert into ai_generations (workspace_id, feature_key, status, hold_id)
          values (${ws}, ${FEATURE}, 'running', ${hold.id})`),
        { db },
      );
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(4_960);

      const cancelled = await withWorkspace(ws, (tx) => cancelInFlight(tx), { db });
      expect(cancelled).toBe(1);
      expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(5_000);
    });
  });
});
