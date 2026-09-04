/**
 * Market's scheduled work.
 *
 *     pnpm --filter @mamal/worker-core market
 *
 * Two jobs, in order, because the second depends on the first: pull whatever
 * Search Console has for every connection that is due, then recompute the
 * opportunities over what is now stored. Running them separately would mean a
 * customer seeing yesterday's analysis of the day before yesterday's data.
 *
 * Idempotent and stateless. A missed run costs a few hours of freshness; two
 * runs at once is safe because the claim uses `for update skip locked`.
 */
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { decryptCredential, encryptCredential } from '@mamal/ai';
import { claimDueConnections, recomputeOpportunities, syncSearchConsole } from '@mamal/tool-market';
import type { GoogleCredentials } from '@mamal/integrations';

const oauth = {
  clientId: process.env.GOOGLE_CLIENT_ID ?? '',
  clientSecret: process.env.GOOGLE_CLIENT_SECRET ?? '',
};

const db = unsafeUnscopedDb();

/*
 * Claimed as platform admin because the scheduler serves every tenant, then
 * each sync runs inside that workspace's own scope — so a bug in the sync can
 * only ever touch the workspace it was claimed for.
 */
const due = await asPlatformAdmin((tx) => claimDueConnections(tx, { limit: 50 }), { db });

let synced = 0;
let rows = 0;
const problems: string[] = [];

for (const connection of due) {
  const result = await withWorkspace(
    connection.workspaceId,
    (tx) =>
      syncSearchConsole(tx, { workspaceId: connection.workspaceId, connectionId: connection.id }, {
        oauth,
        decrypt: (encrypted) => JSON.parse(decryptCredential(encrypted)) as GoogleCredentials,
        encrypt: (credentials) => encryptCredential(JSON.stringify(credentials)),
      }),
    { db },
  );

  if (result.failed) {
    problems.push(`${connection.id}: ${result.failed.reason} — ${result.failed.message}`);
    /*
     * A rate limit is not a failure to report loudly; it is the provider
     * pacing us. The next scheduled run picks up where this one stopped, and
     * `last_synced_at` was stamped on claim so it will not spin.
     */
  } else {
    synced += 1;
  }
  rows += result.rows;
}

/*
 * Recompute for every project that has data, not only the ones that just
 * synced: the finders compare windows, so yesterday's rows becoming "the
 * earlier window" changes the answer even when nothing new arrived.
 */
const projects = await asPlatformAdmin(
  (tx) =>
    tx.execute<{ workspace_id: string; project_id: string }>(sql`
      select distinct workspace_id, project_id from market_search_performance`),
  { db },
);

let recomputed = 0;
for (const project of projects) {
  const result = await withWorkspace(
    project.workspace_id,
    (tx) =>
      recomputeOpportunities(tx, {
        workspaceId: project.workspace_id,
        projectId: project.project_id,
      }),
    { db },
  );
  recomputed += result.found;
}

console.log(
  `[market] synced ${synced}/${due.length} connection(s), ${rows} row(s); ` +
    `${recomputed} opportunit${recomputed === 1 ? 'y' : 'ies'} across ${projects.length} project(s)`,
);
for (const problem of problems) console.warn(`[market] ${problem}`);

await closeDb();

// A connection the customer must fix is worth a non-zero exit, so a scheduler
// that watches exit codes surfaces it. A rate limit is not.
process.exit(problems.some((p) => /forbidden|revoked|unreadable/.test(p)) ? 1 : 0);
