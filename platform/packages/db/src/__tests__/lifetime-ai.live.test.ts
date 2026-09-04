/**
 * Enforcement point 1 of 3 for "lifetime plans exclude AI".
 *
 * The other two are the entitlement resolver (packages/entitlements) and the
 * driver boundary (packages/ai). This one is the database trigger, so a bug in
 * the admin plan editor cannot leak AI to a lifetime holder.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { closeDb, unsafeUnscopedDb } from '../client.ts';

const URL = process.env.TEST_DATABASE_URL ?? process.env.DATABASE_URL;
const d = URL ? describe : describe.skip;

d('lifetime plans exclude AI (db trigger)', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `t${Date.now()}`;
  const aiFeature = `${tag}.ai_image`;
  const plainFeature = `${tag}.links`;
  let lifetimePlanId = '';
  let subscriptionPlanId = '';

  beforeAll(async () => {
    await db.execute(sql`
      insert into features (key, tool, name, kind, is_ai) values
        (${aiFeature}, 'market', 'AI image', 'metered', true),
        (${plainFeature}, 'link', 'Links', 'limit', false)`);
    const [lp] = await db.execute<{ id: string }>(sql`
      insert into plans (key, name, kind) values (${tag + '_lifetime'}, 'Lifetime', 'lifetime')
      returning id`);
    const [sp] = await db.execute<{ id: string }>(sql`
      insert into plans (key, name, kind) values (${tag + '_unified'}, 'Unified', 'unified')
      returning id`);
    lifetimePlanId = lp!.id;
    subscriptionPlanId = sp!.id;
  });

  afterAll(async () => {
    await db.execute(sql`delete from plan_entitlements where feature_key in (${aiFeature}, ${plainFeature})`);
    await db.execute(sql`delete from plans where id in (${lifetimePlanId}, ${subscriptionPlanId})`);
    await db.execute(sql`delete from features where key in (${aiFeature}, ${plainFeature})`);
    await closeDb();
  });

  const grant = (planId: string, feature: string, mode: string) =>
    db.execute(sql`
      insert into plan_entitlements (plan_id, feature_key, mode, credit_cost)
      values (${planId}, ${feature}, ${mode}, 8)`);

  /** postgres.js wraps driver errors; the PG message lives on .cause. */
  const failureMessage = async (p: Promise<unknown>): Promise<string | null> =>
    p.then(
      () => null,
      (e: { cause?: { message?: string }; message?: string }) =>
        e.cause?.message ?? e.message ?? 'unknown error',
    );

  it('allows a NON-AI feature on a lifetime plan', async () => {
    await expect(grant(lifetimePlanId, plainFeature, 'limit')).resolves.toBeDefined();
  });

  it('REJECTS an AI feature granted on a lifetime plan', async () => {
    const msg = await failureMessage(grant(lifetimePlanId, aiFeature, 'credits'));
    expect(msg).toMatch(/lifetime plans cannot grant AI feature/i);
  });

  it('rejects every non-deny mode, not just credits', async () => {
    for (const mode of ['allow', 'limit', 'quota', 'credits']) {
      const msg = await failureMessage(grant(lifetimePlanId, aiFeature, mode));
      expect(msg, `mode=${mode} should have been rejected on a lifetime plan`).toMatch(
        /lifetime plans cannot grant AI feature/i,
      );
    }
  });

  it('allows an AI feature on a lifetime plan when mode is deny', async () => {
    await expect(grant(lifetimePlanId, aiFeature, 'deny')).resolves.toBeDefined();
  });

  it('allows an AI feature on a subscription plan', async () => {
    await expect(grant(subscriptionPlanId, aiFeature, 'credits')).resolves.toBeDefined();
  });

  it('also fires on UPDATE, not only INSERT', async () => {
    const msg = await failureMessage(
      db.execute(sql`
        update plan_entitlements set mode = 'credits'
        where plan_id = ${lifetimePlanId} and feature_key = ${aiFeature}`),
    );
    expect(msg).toMatch(/lifetime plans cannot grant AI feature/i);
  });
});
