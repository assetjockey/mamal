import { sql } from 'drizzle-orm';
import { asPlatformAdmin, withWorkspace, type Database, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';

/**
 * Retention: turning the `core.data_retention_days` entitlement into deletion.
 *
 * The entitlement existed on every plan and nothing consulted it, which means
 * "7-day retention" on the free tier was a line on a pricing page and nowhere
 * else. Data that is sold as expiring and does not expire is a storage bill
 * that grows forever and, once GDPR is in scope, a promise that is not kept.
 *
 * Two decisions worth stating:
 *
 * **Per workspace, resolved not assumed.** The window comes from the same
 * resolver the rest of the platform uses, so an add-on that buys 24 months
 * applies here without this file knowing the add-on exists.
 *
 * **Sweepers are registered, not imported.** Each tool owns the shape of its
 * own data, so it contributes a sweeper rather than having this module reach
 * into `audit_*` tables. Adding a tool adds a sweeper; it does not edit this
 * file.
 */

export type Sweeper = {
  /** Stable name, used in the report and the logs. */
  key: string;
  /** Deletes rows older than `cutoff` for this workspace. Returns the count. */
  sweep: (tx: WorkspaceScopedDb, workspaceId: string, cutoff: Date) => Promise<number>;
};

export type WorkspaceOutcome = {
  workspaceId: string;
  retentionDays: number;
  cutoff: Date;
  deleted: Record<string, number>;
  error?: string;
};

export type RetentionReport = {
  workspaces: number;
  deleted: number;
  outcomes: WorkspaceOutcome[];
};

/** Belt and braces: a bug that resolves 0 must not delete everything. */
const MIN_RETENTION_DAYS = 1;

export async function runRetention(
  db: Database,
  sweepers: readonly Sweeper[],
  opts: { now?: Date; workspaceIds?: string[] } = {},
): Promise<RetentionReport> {
  const now = opts.now ?? new Date();

  const workspaces =
    opts.workspaceIds ??
    (
      await asPlatformAdmin(
        (tx) => tx.execute<{ id: string }>(sql`select id from workspaces order by id`),
        { db },
      )
    ).map((r) => r.id);

  const outcomes: WorkspaceOutcome[] = [];

  for (const workspaceId of workspaces) {
    try {
      const days = await withWorkspace(
        workspaceId,
        async (tx) => {
          const ctx = await loadContext(tx, workspaceId, 'core.data_retention_days');
          if (!ctx) return null;
          const decision = resolve(ctx, 1);
          // limits merge with MAX, so a workspace holding two plans keeps the
          // longer window — which is the only answer that cannot delete data
          // someone paid to keep.
          const value = decision.limit ?? decision.quota ?? null;
          if (value === null) return null;
          return value < 0 ? Number.POSITIVE_INFINITY : value; // -1 = unlimited
        },
        { db },
      );

      // No opinion, or unlimited, means nothing to do. Deleting on a missing
      // entitlement would make a seed gap look like a data-loss bug.
      if (days === null || !Number.isFinite(days)) continue;
      if (days < MIN_RETENTION_DAYS) {
        outcomes.push({
          workspaceId,
          retentionDays: days,
          cutoff: now,
          deleted: {},
          error: `refusing to sweep: resolved retention of ${days} days is below the ${MIN_RETENTION_DAYS}-day floor`,
        });
        continue;
      }

      const cutoff = new Date(now.getTime() - days * 86_400_000);
      const deleted: Record<string, number> = {};

      await withWorkspace(
        workspaceId,
        async (tx) => {
          for (const sweeper of sweepers) {
            deleted[sweeper.key] = await sweeper.sweep(tx, workspaceId, cutoff);
          }
        },
        { db },
      );

      outcomes.push({ workspaceId, retentionDays: days, cutoff, deleted });
    } catch (e) {
      // One workspace's failure must not stop the sweep for the rest — a single
      // bad row would otherwise freeze retention platform-wide.
      outcomes.push({
        workspaceId,
        retentionDays: 0,
        cutoff: now,
        deleted: {},
        error: e instanceof Error ? e.message : String(e),
      });
    }
  }

  const deleted = outcomes.reduce(
    (sum, o) => sum + Object.values(o.deleted).reduce((a, b) => a + b, 0),
    0,
  );
  return { workspaces: outcomes.length, deleted, outcomes };
}

/**
 * The event fact table. Core-owned because every tool writes into it.
 *
 * Rollups are deliberately not swept: they are aggregates with no personal data
 * and they are what makes a year-over-year chart possible after the raw rows
 * are gone.
 */
export const eventSweeper: Sweeper = {
  key: 'events_raw',
  sweep: async (tx, workspaceId, cutoff) => {
    const rows = await tx.execute(sql`
      delete from events_raw
       where workspace_id = ${workspaceId} and ts < ${cutoff.toISOString()}::timestamptz
      returning event_id`);
    return Array.isArray(rows) ? rows.length : 0;
  },
};
