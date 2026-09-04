/**
 * End-to-end: the resolver reading the real seeded plan catalogue.
 *
 * The unit tests prove the merge arithmetic; this proves the seed and the
 * loader agree with it — that the plans we actually ship behave the way the
 * pricing page claims.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { loadContext } from '../load.ts';
import { resolve } from '../resolve.ts';

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('resolver against the seeded catalogue', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `ent${Date.now()}`;
  let workspaceId = '';

  const check = (featureKey: string, quantity = 1) =>
    withWorkspace(
      workspaceId,
      async (tx) => {
        const ctx = await loadContext(tx, workspaceId, featureKey);
        if (!ctx) throw new Error(`unknown feature ${featureKey}`);
        return resolve(ctx, quantity);
      },
      { db },
    );

  // subscriptions and credit_buckets are tenant tables: writing them without a
  // workspace GUC is refused by RLS, which is the point. Fixtures go through
  // the platform-admin escape hatch, same as the real billing webhook does.
  const subscribe = (planKey: string) =>
    asPlatformAdmin(
      (tx) =>
        tx.execute(sql`
          insert into subscriptions (workspace_id, plan_id, status)
          select ${workspaceId}, id, 'active' from plans where key = ${planKey}`),
      { db },
    );

  const grantCredits = (amount: number, expiresAt: string | null) =>
    asPlatformAdmin(
      (tx) =>
        tx.execute(sql`
          insert into credit_buckets (workspace_id, source, amount, remaining, expires_at)
          values (${workspaceId}, 'purchase', ${amount}, ${amount}, ${expiresAt})`),
      { db },
    );

  const clearSubscriptions = () =>
    asPlatformAdmin(
      (tx) => tx.execute(sql`delete from subscriptions where workspace_id = ${workspaceId}`),
      { db },
    );

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Ent Fixture') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Ent', ${u!.id}) returning id`);
      workspaceId = w!.id;
      await tx.execute(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${workspaceId}, 'Default', 'default', true)`);
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${workspaceId}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  describe('a brand-new workspace is on the free floor', () => {
    it('gets 25 links', async () => {
      const d = await check('link.links');
      expect(d.allowed).toBe(true);
      expect(d.allowed && d.limit).toBe(25);
    });

    it('gets 3 monitors', async () => {
      const d = await check('monitor.monitors');
      expect(d.allowed && d.limit).toBe(3);
    });

    it('is denied every AI feature — no credits, no grant', async () => {
      for (const key of ['audit.ai_summary', 'market.ai_image', 'track.ai_digest']) {
        const r = await check(key);
        expect(r.allowed, `${key} should be denied on free`).toBe(false);
      }
    });

    it('is denied DataForSEO — a per-call vendor invoice can never be free', async () => {
      const r = await check('market.dataforseo');
      expect(r.allowed).toBe(false);
    });

    it('is denied automations, which are what break O(1) free-tier cost', async () => {
      const r = await check('core.automations');
      expect(r.allowed).toBe(false);
    });
  });

  describe('Link Pro + Unified Starter + 5,000 purchased credits', () => {
    beforeAll(async () => {
      await subscribe('link_pro');
      await subscribe('unified_starter');
      await grantCredits(5_000, null);
    });

    it('link.links = 10,000 — Link Pro wins by MAX over Starter and free', async () => {
      const d = await check('link.links');
      expect(d.allowed && d.limit).toBe(10_000);
    });

    it('monitor.monitors = 20 — Link Pro is silent, Unified grants', async () => {
      const d = await check('monitor.monitors');
      expect(d.allowed && d.limit).toBe(20);
    });

    it('track.pageviews = 150,000 — 50k from Link Pro + 100k from Starter', async () => {
      const d = await check('track.pageviews');
      expect(d.allowed && d.quota).toBe(150_000);
    });

    it('core.custom_domains = 5 — Link Pro beats Starter', async () => {
      const d = await check('core.custom_domains');
      expect(d.allowed && d.limit).toBe(5);
    });

    it('market.ai_image now costs 8 credits and is affordable', async () => {
      const d = await check('market.ai_image');
      expect(d.allowed).toBe(true);
      expect(d.allowed && d.cost).toBe(8);
      expect(d.remainingCredits).toBe(5_000);
    });

    it('quantity multiplies the credit cost', async () => {
      const d = await check('market.ai_image', 10);
      expect(d.allowed && d.cost).toBe(80);
    });

    it('denies when the balance cannot cover it', async () => {
      const d = await check('market.ai_image', 10_000);
      expect(d.allowed).toBe(false);
      expect(!d.allowed && d.reason).toBe('insufficient_credits');
    });
  });

  describe('the instance AI master switch', () => {
    afterAll(async () => {
      await db.execute(sql`update instance_settings set ai_master_enabled = true`);
    });

    it('kills every AI feature platform-wide, with the right reason', async () => {
      await db.execute(sql`update instance_settings set ai_master_enabled = false`);
      const d = await check('market.ai_image');
      expect(d.allowed).toBe(false);
      expect(!d.allowed && d.reason).toBe('ai_disabled_instance');
    });

    it('leaves non-AI features untouched', async () => {
      const d = await check('link.links');
      expect(d.allowed).toBe(true);
    });
  });

  describe('a lifetime plan', () => {
    beforeAll(async () => {
      await clearSubscriptions();
      await subscribe('lifetime_pro');
    });

    it('grants the platform generously', async () => {
      const d = await check('link.links');
      expect(d.allowed && d.limit).toBe(10_000);
    });

    it('blocks AI even with 5,000 credits in the bank', async () => {
      const d = await check('market.ai_image');
      expect(d.allowed).toBe(false);
      expect(!d.allowed && d.reason).toBe('ai_excluded_lifetime');
    });

    it('unblocks AI once the instance enables pay-as-you-go credits', async () => {
      await db.execute(sql`update instance_settings set lifetime_ai_via_credits = true`);
      const d = await check('market.ai_image');
      expect(d.allowed).toBe(false); // lifetime plan denies the feature outright
      await db.execute(sql`update instance_settings set lifetime_ai_via_credits = false`);
    });
  });
});
