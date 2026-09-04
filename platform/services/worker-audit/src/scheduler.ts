import { sql } from 'drizzle-orm';
import { asPlatformAdmin, type Database } from '@mamal/db';
import { claimDue, enqueue } from '@mamal/jobs';
import { startAudit, AuditNotAllowed } from '@mamal/tool-audit';
import type { CrawlJob } from './crawl-worker.ts';

/**
 * Claims sites whose next audit is due and enqueues one crawl each.
 *
 * Claim-and-enqueue, never a repeatable job per site: at 100k sites, BullMQ
 * repeatables are Redis-memory suicide. `FOR UPDATE SKIP LOCKED` means several
 * schedulers can run without coordinating.
 */
export async function scheduleDueAudits(db: Database): Promise<number> {
  const due = await claimDue(db, {
    table: 'audit_sites',
    dueColumn: 'next_audit_at',
    where: sql`is_enabled`,
    // Pushed a day out on claim; the real cadence is set when the run finishes.
    intervalSeconds: 86_400,
    batchSize: 500,
  });

  let queued = 0;
  for (const row of due) {
    try {
      const started = await asPlatformAdmin(async (tx) => {
        await tx.execute(sql`select set_config('app.current_workspace_id', ${row.workspaceId}, true)`);
        const [site] = await tx.execute<{ project_id: string }>(sql`
          select project_id from audit_sites where id = ${row.id}`);
        return startAudit(tx, {
          workspaceId: row.workspaceId,
          projectId: site!.project_id,
          auditSiteId: row.id,
          trigger: 'schedule',
        });
      }, { db });

      await enqueue<CrawlJob>('audit.crawl', 'slice', {
        auditId: started.auditId,
        workspaceId: row.workspaceId,
        slice: 0,
      });
      queued++;
    } catch (err) {
      // A workspace over quota should not stop the batch — and should not be
      // retried every minute either, so its next_audit_at stays pushed out.
      if (err instanceof AuditNotAllowed) {
        console.warn(`[audit.schedule] skipping ${row.id}: ${err.reason}`);
        continue;
      }
      console.error(`[audit.schedule] ${row.id} failed:`, err);
    }
  }
  return queued;
}

/** Sets the next run from the site's schedule after a completed audit. */
export const SCHEDULE_SECONDS: Record<string, number | null> = {
  manual: null,
  '6h': 21_600,
  '12h': 43_200,
  daily: 86_400,
  '3d': 259_200,
  weekly: 604_800,
  '30d': 2_592_000,
};

export async function reschedule(db: Database, auditSiteId: string): Promise<void> {
  await asPlatformAdmin(
    (tx) => tx.execute(sql`
      update audit_sites
         set next_audit_at = case
               when schedule = 'manual' then null
               else now() + (
                 case schedule
                   when '6h' then interval '6 hours'
                   when '12h' then interval '12 hours'
                   when 'daily' then interval '1 day'
                   when '3d' then interval '3 days'
                   when 'weekly' then interval '7 days'
                   when '30d' then interval '30 days'
                 end)
             end
       where id = ${auditSiteId}`),
    { db },
  );
}
