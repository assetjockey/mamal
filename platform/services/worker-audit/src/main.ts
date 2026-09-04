import { closeDb, unsafeUnscopedDb } from '@mamal/db';
import { Dispatcher, EventRegistry, coreEvents } from '@mamal/bus';
import { closeQueues } from '@mamal/jobs';
import { auditManifest, auditSubscriptions } from '@mamal/tool-audit';
import { startCrawlWorker } from './crawl-worker.ts';
import { scheduleDueAudits } from './scheduler.ts';

/**
 * The audit worker process.
 *
 * Two responsibilities: consume `audit.crawl` slices, and sweep for due audits
 * once a minute. Both are safe to run in several replicas — the queue
 * distributes slices, and the scheduler claims with SKIP LOCKED.
 */
const db = unsafeUnscopedDb();
const registry = new EventRegistry().register(...coreEvents, ...auditManifest.events);

const worker = startCrawlWorker(db, registry);

/*
 * Audit's subscriptions are registered now even though Monitor arrives in
 * Phase 5. A subscriber does not need its publisher to exist — that is what the
 * bus buys — so the day Monitor ships, broken links start closing themselves
 * with no change here.
 */
const dispatcher = new Dispatcher(db);
for (const handler of auditSubscriptions) dispatcher.on(handler);

console.info(
  `[worker-audit] consuming audit.crawl, subscribed to ` +
    auditSubscriptions.map((h) => h.event).join(', '),
);

const sweep = setInterval(() => {
  scheduleDueAudits(db)
    .then((n) => {
      if (n > 0) console.info(`[worker-audit] queued ${n} scheduled audit(s)`);
    })
    .catch((err) => console.error('[worker-audit] sweep failed:', err));
}, 60_000);

async function shutdown(signal: string) {
  console.info(`[worker-audit] ${signal}: draining`);
  clearInterval(sweep);
  // Let the in-flight slice finish: it is bounded, and finishing it means the
  // next process resumes from a clean boundary rather than mid-batch.
  await worker.close();
  await closeQueues();
  await closeDb();
  process.exit(0);
}

process.on('SIGTERM', () => void shutdown('SIGTERM'));
process.on('SIGINT', () => void shutdown('SIGINT'));
