import { sql } from 'drizzle-orm';
import { asPlatformAdmin, withWorkspace, type Database } from '@mamal/db';
import { EventRegistry, publish } from '@mamal/bus';
import { enqueue, startWorker, type Job } from '@mamal/jobs';
import { advanceAudit, auditManifest } from '@mamal/tool-audit';

export type CrawlJob = {
  auditId: string;
  workspaceId: string;
  /** Guards against a re-enqueue loop if a slice somehow never advances. */
  slice: number;
};

const MAX_SLICES = 2_000;

/**
 * The crawl worker.
 *
 * One job = one bounded slice. When the slice finishes and there is more to
 * do, the job re-enqueues itself and returns. That keeps every job short, so:
 *
 *   - a killed worker loses at most one slice, not a 10,000-page crawl
 *   - the page counter moves throughout, so progress is real rather than a
 *     spinner
 *   - a stuck crawl is visible as a stalled slice number, not a job that has
 *     been "running" for an hour
 */
export function startCrawlWorker(db: Database, registry: EventRegistry) {
  return startWorker<CrawlJob>('audit.crawl', async (job: Job<CrawlJob>) => {
    const { auditId, workspaceId, slice } = job.data;

    if (slice > MAX_SLICES) {
      await fail(db, auditId, 'slice_limit', `exceeded ${MAX_SLICES} slices`);
      return;
    }

    const result = await withWorkspace(
      workspaceId,
      (tx) => advanceAudit(tx, auditId, workspaceId),
      { db },
    );

    if (result.status === 'continue') {
      await job.updateProgress({
        crawled: result.pagesCrawled,
        queued: result.queued,
        slice,
      });
      await enqueue<CrawlJob>('audit.crawl', 'slice', {
        auditId,
        workspaceId,
        slice: slice + 1,
      });
      return;
    }

    // The run is finished: publish its events through the outbox so
    // automations see them with the same guarantees as any other producer.
    await withWorkspace(
      workspaceId,
      async (tx) => {
        for (const event of result.outcome.events) {
          if (!registry.get(event.name)) continue;
          await publish(tx, registry, {
            name: event.name,
            workspaceId,
            subject: `urn:mamal:audit:run:${auditId}`,
            data: event.data,
            actor: { kind: 'system' },
          });
        }
      },
      { db },
    );

    console.info(
      `[audit.crawl] ${auditId} complete: score ${result.outcome.score}, ` +
        `${result.outcome.pagesCrawled} pages, ${result.outcome.events.length} events`,
    );
  });
}

async function fail(db: Database, auditId: string, code: string, detail: string) {
  await asPlatformAdmin(
    (tx) => tx.execute(sql`
      update audits set status = 'failed', phase = 'failed', finished_at = now(),
                        error_code = ${code}, error_detail = ${detail}
       where id = ${auditId}`),
    { db },
  );
}

export const auditEvents = auditManifest.events;
