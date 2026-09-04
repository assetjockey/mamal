import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { balance, capture, grant, InsufficientCredits, release, reserve } from '../ledger.ts';

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('credit ledger', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `cr${Date.now()}`;
  let ws = '';

  const inWs = <T>(fn: Parameters<typeof withWorkspace<T>>[1]) => withWorkspace(ws, fn, { db });

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Credit Fixture') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Credits', ${u!.id}) returning id`);
      ws = w!.id;
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    await inWs(async (tx) => {
      await tx.execute(sql`delete from credit_entries where workspace_id = ${ws}`);
      await tx.execute(sql`delete from credit_holds where workspace_id = ${ws}`);
      await tx.execute(sql`delete from credit_buckets where workspace_id = ${ws}`);
    });
  });

  describe('grant + balance', () => {
    it('grants and reads back', async () => {
      await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: `${tag}-g1` }));
      expect(await inWs((tx) => balance(tx, ws))).toBe(500);
    });

    it('is idempotent — a replayed webhook does not double-grant', async () => {
      const key = `${tag}-dup`;
      const a = await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: key }));
      const b = await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: key }));
      expect(a.granted).toBe(true);
      expect(b.granted).toBe(false);
      expect(await inWs((tx) => balance(tx, ws))).toBe(500);
    });

    it('excludes expired buckets from the balance', async () => {
      await inWs(async (tx) => {
        await tx.execute(sql`
          insert into credit_buckets (workspace_id, source, amount, remaining, expires_at)
          values (${ws}, 'plan_grant', 100, 100, now() - interval '1 day')`);
      });
      expect(await inWs((tx) => balance(tx, ws))).toBe(0);
    });
  });

  describe('reserve → capture', () => {
    it('spends expiring buckets first, so purchased credits are never wasted', async () => {
      await inWs(async (tx) => {
        // purchased, never expires
        await grant(tx, ws, 100, { source: 'purchase', idempotencyKey: `${tag}-p` });
        // plan grant, expires in 30 days — must burn FIRST
        await grant(tx, ws, 100, { source: 'plan_grant', expiresAfterDays: 30, idempotencyKey: `${tag}-pg` });
      });

      const hold = await inWs((tx) => reserve(tx, ws, 60, { featureKey: 'market.ai_image' }));
      await inWs((tx) => capture(tx, ws, hold.id, { idempotencyKey: `${tag}-c1` }));

      const rows = await inWs((tx) =>
        tx.execute<{ source: string; remaining: number }>(sql`
          select source, remaining from credit_buckets where workspace_id = ${ws} order by source`),
      );
      const bySource = Object.fromEntries(rows.map((r) => [r.source, Number(r.remaining)]));
      expect(bySource.plan_grant).toBe(40); // drained first
      expect(bySource.purchase).toBe(100); // untouched
    });

    it('refuses to overdraw', async () => {
      await inWs((tx) => grant(tx, ws, 10, { source: 'purchase', idempotencyKey: `${tag}-small` }));
      await expect(
        inWs((tx) => reserve(tx, ws, 50, { featureKey: 'market.ai_image' })),
      ).rejects.toBeInstanceOf(InsufficientCredits);
      expect(await inWs((tx) => balance(tx, ws))).toBe(10);
    });

    it('a hold removes credits from the balance before the job runs', async () => {
      await inWs((tx) => grant(tx, ws, 100, { source: 'purchase', idempotencyKey: `${tag}-h` }));
      await inWs((tx) => reserve(tx, ws, 30, { featureKey: 'market.ai_image' }));
      expect(await inWs((tx) => balance(tx, ws))).toBe(70);
    });
  });

  describe('release — a failed generation costs nothing', () => {
    it('restores the full amount to the original buckets', async () => {
      await inWs((tx) => grant(tx, ws, 100, { source: 'purchase', idempotencyKey: `${tag}-r` }));
      const hold = await inWs((tx) => reserve(tx, ws, 40, { featureKey: 'market.ai_video' }));
      expect(await inWs((tx) => balance(tx, ws))).toBe(60);

      await inWs((tx) => release(tx, ws, hold.id));
      expect(await inWs((tx) => balance(tx, ws))).toBe(100);
    });

    it('preserves bucket expiry on release — a refund does not become immortal', async () => {
      await inWs((tx) =>
        grant(tx, ws, 100, { source: 'plan_grant', expiresAfterDays: 30, idempotencyKey: `${tag}-e` }),
      );
      const hold = await inWs((tx) => reserve(tx, ws, 40, { featureKey: 'market.ai_video' }));
      await inWs((tx) => release(tx, ws, hold.id));

      const rows = await inWs((tx) =>
        tx.execute<{ n: number }>(sql`
          select count(*)::int as n from credit_buckets
           where workspace_id = ${ws} and expires_at is not null and remaining = 100`),
      );
      expect(Number(rows[0]!.n)).toBe(1);
    });

    it('releasing twice is a no-op', async () => {
      await inWs((tx) => grant(tx, ws, 100, { source: 'purchase', idempotencyKey: `${tag}-r2` }));
      const hold = await inWs((tx) => reserve(tx, ws, 40, { featureKey: 'x' }));
      await inWs((tx) => release(tx, ws, hold.id));
      await inWs((tx) => release(tx, ws, hold.id));
      expect(await inWs((tx) => balance(tx, ws))).toBe(100);
    });
  });

  describe('capture true-up — video is priced per second and only known at the end', () => {
    it('refunds the surplus when the job cost less than held', async () => {
      await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: `${tag}-t1` }));
      const hold = await inWs((tx) => reserve(tx, ws, 200, { featureKey: 'market.ai_video' }));
      const res = await inWs((tx) =>
        capture(tx, ws, hold.id, { actualAmount: 120, idempotencyKey: `${tag}-t1c` }),
      );
      expect(res.charged).toBe(120);
      expect(await inWs((tx) => balance(tx, ws))).toBe(380);
    });

    it('draws more when the job cost more than held', async () => {
      await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: `${tag}-t2` }));
      const hold = await inWs((tx) => reserve(tx, ws, 100, { featureKey: 'market.ai_video' }));
      const res = await inWs((tx) =>
        capture(tx, ws, hold.id, { actualAmount: 180, idempotencyKey: `${tag}-t2c` }),
      );
      expect(res.charged).toBe(180);
      expect(await inWs((tx) => balance(tx, ws))).toBe(320);
    });

    it('a retried worker cannot double-charge', async () => {
      await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: `${tag}-t3` }));
      const hold = await inWs((tx) => reserve(tx, ws, 100, { featureKey: 'market.ai_image' }));
      const key = `${tag}-job-42:capture`;

      const first = await inWs((tx) => capture(tx, ws, hold.id, { idempotencyKey: key }));
      const second = await inWs((tx) => capture(tx, ws, hold.id, { idempotencyKey: key }));

      expect(first.alreadySettled).toBe(false);
      expect(second.alreadySettled).toBe(true);
      expect(await inWs((tx) => balance(tx, ws))).toBe(400);

      const entries = await inWs((tx) =>
        tx.execute<{ n: number }>(sql`
          select count(*)::int as n from credit_entries
           where workspace_id = ${ws} and idempotency_key = ${key}`),
      );
      expect(Number(entries[0]!.n)).toBe(1);
    });

    it('writes an auditable entry with the resulting balance', async () => {
      await inWs((tx) => grant(tx, ws, 500, { source: 'purchase', idempotencyKey: `${tag}-t4` }));
      const hold = await inWs((tx) => reserve(tx, ws, 75, { featureKey: 'market.ai_image' }));
      await inWs((tx) =>
        capture(tx, ws, hold.id, {
          idempotencyKey: `${tag}-t4c`,
          resourceUrn: 'urn:mamal:market:creative:abc',
          quantity: 3,
        }),
      );
      const [entry] = await inWs((tx) =>
        tx.execute<{ delta: number; balance_after: number; resource_urn: string; quantity: number }>(sql`
          select delta, balance_after, resource_urn, quantity from credit_entries
           where workspace_id = ${ws} and idempotency_key = ${`${tag}-t4c`}`),
      );
      expect(Number(entry!.delta)).toBe(-75);
      expect(Number(entry!.balance_after)).toBe(425);
      expect(entry!.resource_urn).toBe('urn:mamal:market:creative:abc');
      expect(Number(entry!.quantity)).toBe(3);
    });
  });
});
