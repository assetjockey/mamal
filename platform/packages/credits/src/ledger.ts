import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';

/**
 * The credit ledger.
 *
 * Charging is a HOLD, not a debit. This is the fix for the single worst
 * correctness bug in the source products: magicads debits on dispatch, so a
 * generation that fails still costs the user credits.
 *
 *   reserve()  -> draw from buckets, record a hold
 *   capture()  -> true up to the actual quantity (video is priced per second,
 *                 which is unknown until the job finishes)
 *   release()  -> restore to the SAME buckets, preserving their expiry
 *
 * Every capture writes a credit_entries row with a UNIQUE idempotency key, so
 * a retried job cannot double-charge.
 */

export type Hold = {
  id: string;
  amount: number;
  draws: BucketDraw[];
};

export type BucketDraw = { bucketId: string; amount: number };

export class InsufficientCredits extends Error {
  constructor(
    readonly required: number,
    readonly available: number,
  ) {
    super(`insufficient credits: need ${required}, have ${available}`);
    this.name = 'InsufficientCredits';
  }
}

/** Live balance: non-expired buckets only. */
export async function balance(tx: WorkspaceScopedDb, workspaceId: string): Promise<number> {
  const [row] = await tx.execute<{ total: number }>(sql`
    select coalesce(sum(remaining), 0)::int as total
      from credit_buckets
     where workspace_id = ${workspaceId}
       and remaining > 0
       and (expires_at is null or expires_at > now())`);
  return Number(row?.total ?? 0);
}

/**
 * Draw `amount` credits and record a hold.
 *
 * Spend order is `expires_at ASC NULLS LAST, granted_at ASC` — plan grants
 * burn before purchased credits precisely because they expire first, so a
 * customer's paid-for balance is never wasted.
 *
 * FOR UPDATE SKIP LOCKED keeps two concurrent generations from drawing the
 * same bucket rows.
 */
export async function reserve(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  amount: number,
  opts: { featureKey: string; jobId?: string },
): Promise<Hold> {
  if (amount < 0) throw new Error('reserve amount must be >= 0');
  if (amount === 0) {
    const [empty] = await tx.execute<{ id: string }>(sql`
      insert into credit_holds (workspace_id, amount, feature_key, job_id, bucket_draws)
      values (${workspaceId}, 0, ${opts.featureKey}, ${opts.jobId ?? null}, '[]'::jsonb)
      returning id`);
    return { id: empty!.id, amount: 0, draws: [] };
  }

  const buckets = await tx.execute<{ id: string; remaining: number }>(sql`
    select id, remaining
      from credit_buckets
     where workspace_id = ${workspaceId}
       and remaining > 0
       and (expires_at is null or expires_at > now())
     order by expires_at asc nulls last, granted_at asc
     for update skip locked`);

  const available = buckets.reduce((sum, b) => sum + Number(b.remaining), 0);
  if (available < amount) throw new InsufficientCredits(amount, available);

  const draws: BucketDraw[] = [];
  let outstanding = amount;
  for (const bucket of buckets) {
    if (outstanding === 0) break;
    const take = Math.min(Number(bucket.remaining), outstanding);
    await tx.execute(sql`
      update credit_buckets set remaining = remaining - ${take} where id = ${bucket.id}`);
    draws.push({ bucketId: bucket.id, amount: take });
    outstanding -= take;
  }

  const [hold] = await tx.execute<{ id: string }>(sql`
    insert into credit_holds (workspace_id, amount, feature_key, job_id, bucket_draws, status)
    values (${workspaceId}, ${amount}, ${opts.featureKey}, ${opts.jobId ?? null},
            ${JSON.stringify(draws)}::jsonb, 'held')
    returning id`);

  return { id: hold!.id, amount, draws };
}

/**
 * Settle a hold at the actual cost.
 *
 * `actualAmount` below the held amount refunds the difference to the buckets
 * it came from; above it draws the extra. Writing the entry is idempotent on
 * `idempotencyKey`, so a retried worker settles once.
 */
export async function capture(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  holdId: string,
  opts: { actualAmount?: number; idempotencyKey: string; resourceUrn?: string; quantity?: number },
): Promise<{ charged: number; alreadySettled: boolean }> {
  const [hold] = await tx.execute<{
    id: string;
    amount: number;
    feature_key: string;
    status: string;
    bucket_draws: BucketDraw[];
  }>(sql`select * from credit_holds where id = ${holdId} and workspace_id = ${workspaceId} for update`);

  if (!hold) throw new Error(`hold ${holdId} not found`);
  if (hold.status !== 'held') {
    return { charged: hold.status === 'captured' ? Number(hold.amount) : 0, alreadySettled: true };
  }

  const held = Number(hold.amount);
  const actual = opts.actualAmount ?? held;
  if (actual < 0) throw new Error('actual amount must be >= 0');

  const draws = [...(hold.bucket_draws ?? [])];

  if (actual < held) {
    // Refund the surplus to the buckets it was taken from, newest draw first,
    // so expiry dates are preserved exactly.
    let surplus = held - actual;
    for (let i = draws.length - 1; i >= 0 && surplus > 0; i--) {
      const give = Math.min(draws[i]!.amount, surplus);
      await tx.execute(sql`
        update credit_buckets set remaining = remaining + ${give} where id = ${draws[i]!.bucketId}`);
      draws[i]!.amount -= give;
      surplus -= give;
    }
  } else if (actual > held) {
    const extra = await reserve(tx, workspaceId, actual - held, { featureKey: hold.feature_key });
    draws.push(...extra.draws);
    await tx.execute(sql`delete from credit_holds where id = ${extra.id}`);
  }

  const remaining = await balance(tx, workspaceId);

  // ON CONFLICT DO NOTHING on the unique idempotency key is what makes a
  // retried job safe.
  const inserted = await tx.execute<{ id: string }>(sql`
    insert into credit_entries
      (workspace_id, hold_id, delta, balance_after, feature_key, resource_urn,
       quantity, idempotency_key, actor)
    values (${workspaceId}, ${holdId}, ${-actual}, ${remaining}, ${hold.feature_key},
            ${opts.resourceUrn ?? null}, ${opts.quantity ?? null}, ${opts.idempotencyKey}, 'system')
    on conflict (idempotency_key) do nothing
    returning id`);

  await tx.execute(sql`
    update credit_holds
       set status = 'captured', amount = ${actual},
           bucket_draws = ${JSON.stringify(draws.filter((d) => d.amount > 0))}::jsonb,
           updated_at = now()
     where id = ${holdId}`);

  return { charged: actual, alreadySettled: inserted.length === 0 };
}

/** Failure path: restore every drawn credit to its original bucket. */
export async function release(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  holdId: string,
): Promise<void> {
  const [hold] = await tx.execute<{ status: string; bucket_draws: BucketDraw[] }>(sql`
    select status, bucket_draws from credit_holds
     where id = ${holdId} and workspace_id = ${workspaceId} for update`);
  if (!hold || hold.status !== 'held') return;

  for (const draw of hold.bucket_draws ?? []) {
    await tx.execute(sql`
      update credit_buckets set remaining = remaining + ${draw.amount} where id = ${draw.bucketId}`);
  }
  await tx.execute(sql`
    update credit_holds set status = 'released', updated_at = now() where id = ${holdId}`);
}

/** Grant credits — a plan's monthly allowance, a purchase, a bonus, a refund. */
export async function grant(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  amount: number,
  opts: {
    source: 'plan_grant' | 'purchase' | 'bonus' | 'refund' | 'admin' | 'affiliate' | 'trial';
    sourceRef?: string;
    expiresAfterDays?: number;
    idempotencyKey: string;
  },
): Promise<{ bucketId: string | null; granted: boolean }> {
  const expiresAt =
    opts.expiresAfterDays != null
      ? sql`now() + ${`${opts.expiresAfterDays} days`}::interval`
      : sql`null`;

  const [bucket] = await tx.execute<{ id: string }>(sql`
    insert into credit_buckets (workspace_id, source, source_ref, amount, remaining, expires_at)
    values (${workspaceId}, ${opts.source}, ${opts.sourceRef ?? null}, ${amount}, ${amount}, ${expiresAt})
    returning id`);

  const after = await balance(tx, workspaceId);
  const entry = await tx.execute<{ id: string }>(sql`
    insert into credit_entries
      (workspace_id, bucket_id, delta, balance_after, idempotency_key, actor)
    values (${workspaceId}, ${bucket!.id}, ${amount}, ${after}, ${opts.idempotencyKey}, 'system')
    on conflict (idempotency_key) do nothing
    returning id`);

  if (entry.length === 0) {
    // A duplicate grant: undo the bucket we just created.
    await tx.execute(sql`delete from credit_buckets where id = ${bucket!.id}`);
    return { bucketId: null, granted: false };
  }
  return { bucketId: bucket!.id, granted: true };
}

/** Sweep expired buckets so the balance query stays honest. */
export async function expireBuckets(tx: WorkspaceScopedDb, workspaceId: string): Promise<number> {
  const rows = await tx.execute<{ id: string }>(sql`
    update credit_buckets set remaining = 0
     where workspace_id = ${workspaceId} and remaining > 0
       and expires_at is not null and expires_at <= now()
    returning id`);
  return rows.length;
}
