import { sql, type SQL } from 'drizzle-orm';
import { inList, type Database } from '@mamal/db';

/**
 * Claim-and-enqueue.
 *
 * NEVER enqueue a per-entity repeatable job. 66uptime's `next_check_datetime`
 * sweep is the right pattern; BullMQ repeatables at 100k monitors is Redis
 * memory suicide.
 *
 * `FOR UPDATE SKIP LOCKED` means N schedulers can run concurrently with no
 * leader election — each takes a disjoint slice, and one that dies mid-claim
 * leaves its rows for the next pass.
 */
export type ClaimSpec = {
  /** Table holding the schedulable entity. */
  table: string;
  /** Column holding the next-due timestamp. */
  dueColumn: string;
  /** Extra predicate as a typed fragment, e.g. sql`is_enabled`. */
  where?: SQL;
  /** Seconds to push the due time forward on claim. */
  intervalSeconds?: number;
  batchSize?: number;
};

export type ClaimedRow = { id: string; workspaceId: string };

export async function claimDue(db: Database, spec: ClaimSpec): Promise<ClaimedRow[]> {
  const table = sql.identifier(spec.table);
  const dueColumn = sql.identifier(spec.dueColumn);
  const batch = spec.batchSize ?? 5_000;
  const interval = spec.intervalSeconds ?? 300;

  return db.transaction(async (tx) => {
    await tx.execute(sql`select set_config('app.is_platform_admin', 'true', true)`);

    const due = await tx.execute<{ id: string; workspace_id: string }>(sql`
      select id, workspace_id from ${table}
       where ${dueColumn} <= now()
         ${spec.where ? sql`and (${spec.where})` : sql``}
       order by ${dueColumn} asc
       limit ${batch}
       for update skip locked`);

    if (due.length === 0) return [];

    // The push happens in the same transaction as the claim, so a crash
    // between claiming and enqueuing re-runs the work rather than losing it.
    const idList = inList(due.map((r) => r.id));
    await tx.execute(sql`
      update ${table}
         set ${dueColumn} = now() + (${interval} * interval '1 second')
       where id in (${idList})`);

    return due.map((r) => ({ id: r.id, workspaceId: r.workspace_id }));
  });
}

/**
 * An advisory lock for the two jobs that must never run twice at once: the
 * outbox relay and the rollup refresh. Everything else uses SKIP LOCKED and
 * needs no coordination at all.
 */
export async function withLeaderLock<T>(
  db: Database,
  key: string,
  fn: () => Promise<T>,
): Promise<T | null> {
  const lockId = hashKey(key);
  const [row] = await db.execute<{ locked: boolean }>(
    sql`select pg_try_advisory_lock(${lockId}::bigint) as locked`,
  );
  if (!row?.locked) return null;
  try {
    return await fn();
  } finally {
    await db.execute(sql`select pg_advisory_unlock(${lockId}::bigint)`);
  }
}

function hashKey(key: string): number {
  let h = 2166136261;
  for (let i = 0; i < key.length; i++) {
    h ^= key.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h | 0;
}
