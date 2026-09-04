/**
 * The nightly retention sweep.
 *
 * `pnpm --filter @mamal/worker-core retention [--dry-run]`
 *
 * This lives in a service, not in `packages/retention`, because it is the one
 * place allowed to know about every tool at once. Retention's runner has to
 * import each tool's sweeper; a tool must never import retention. Putting the
 * runner inside the package made that a build cycle — a composition root is
 * exactly where that knowledge belongs.
 *
 * Deliberately a script rather than a long-lived worker: it is idempotent,
 * holds nothing between runs, and a missed night is caught up by the next one.
 * The failure mode is "data lives a day longer", not a stuck consumer nobody
 * notices. It exits non-zero if any workspace errored, so cron mail or a job
 * monitor sees it.
 */
import { sql } from 'drizzle-orm';
import { unsafeUnscopedDb, closeDb } from '@mamal/db';
import { runRetention, eventSweeper, type Sweeper } from '@mamal/retention';
import { auditSweeper } from '@mamal/tool-audit';
import { confirmSweeper } from '@mamal/tool-confirm';

const dryRun = process.argv.includes('--dry-run');
const db = unsafeUnscopedDb();

/** Core owns the event fact table; each tool contributes its own sweeper. */
const REAL: Sweeper[] = [eventSweeper, auditSweeper, confirmSweeper];

/**
 * A dry run still resolves every workspace's window and counts what would go.
 * A dry run that always reports zero is worse than none — it is the reassurance
 * without the check.
 */
const COUNTERS: Record<string, (workspaceId: string, cutoff: Date) => ReturnType<typeof sql>> = {
  events_raw: (workspaceId, cutoff) => sql`
    select count(*)::int as n from events_raw
     where workspace_id = ${workspaceId} and ts < ${cutoff.toISOString()}::timestamptz`,
  audits: (workspaceId, cutoff) => sql`
    select count(*)::int as n from audits
     where workspace_id = ${workspaceId} and created_at < ${cutoff.toISOString()}::timestamptz`,
  confirm_conversions: (workspaceId, cutoff) => sql`
    select count(*)::int as n from confirm_conversions
     where workspace_id = ${workspaceId} and occurred_at < ${cutoff.toISOString()}::timestamptz`,
};

const sweepers: Sweeper[] = dryRun
  ? REAL.map((s) => ({
      key: s.key,
      sweep: async (tx, workspaceId, cutoff) => {
        const build = COUNTERS[s.key];
        if (!build) return 0;
        const rows = await tx.execute<{ n: number }>(build(workspaceId, cutoff));
        return Number(rows[0]?.n ?? 0);
      },
    }))
  : REAL;

const report = await runRetention(db, sweepers);
const errors = report.outcomes.filter((o) => o.error);

for (const o of report.outcomes) {
  if (o.error) {
    console.error(`  ✗ ${o.workspaceId}: ${o.error}`);
  } else {
    const detail = Object.entries(o.deleted)
      .filter(([, n]) => n > 0)
      .map(([k, n]) => `${k}=${n}`)
      .join(' ');
    if (detail) console.log(`  ${o.workspaceId}  ${o.retentionDays}d  ${detail}`);
  }
}

console.log(
  `${dryRun ? '[dry run] ' : ''}retention: ${report.workspaces} workspace(s), ` +
    `${report.deleted} row(s) ${dryRun ? 'would be removed' : 'removed'}, ${errors.length} error(s)`,
);

await closeDb();
process.exit(errors.length > 0 ? 1 : 0);
