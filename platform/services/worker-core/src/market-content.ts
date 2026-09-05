/**
 * Market's content work: trend watches, content pipelines, and publishing.
 *
 *     pnpm --filter @mamal/worker-core market-content
 *
 * Three jobs in dependency order, because each feeds the next: read the trends,
 * run the pipelines those trends trigger, then push whatever a pipeline was
 * explicitly told to publish. Running them apart would mean a pipeline acting
 * on last week's trends.
 *
 * Idempotent and safe to run twice: every stage claims with
 * `for update skip locked` and moves its own schedule on claim.
 */
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { decryptCredential, driverFor } from '@mamal/ai';
import {
  claimDuePipelines, claimDueWatches, pendingPublishes, runPipeline, runWatch,
  saveDoc, type Reading,
} from '@mamal/tool-market';
import {
  ghostPublisher, shopifyPublisher, webhookPublisher, wordpressPublisher,
  type Publisher,
} from '@mamal/integrations';
import { marked } from 'marked';

const db = unsafeUnscopedDb();

/* ------------------------------------------------------------- 1. trends */

/**
 * Where readings come from.
 *
 * `services/trends` is a Python sidecar because there is no TypeScript pytrends;
 * DataForSEO is the paid fallback. With neither configured this returns nothing,
 * which leaves every baseline untouched and every watch intact — the watch list
 * still works, it simply has nothing new to say. Silently inventing readings
 * would be far worse than saying nothing.
 */
async function readTrends(request: {
  keywords: string[]; geos: string[]; timeframe: string;
}): Promise<Reading[]> {
  const endpoint = process.env.TRENDS_SERVICE_URL;
  if (!endpoint) return [];

  const response = await fetch(`${endpoint.replace(/\/+$/, '')}/interest`, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify(request),
    signal: AbortSignal.timeout(30_000),
  });
  if (!response.ok) {
    throw new Error(`the trends service returned ${response.status}`);
  }
  const body = (await response.json()) as { readings?: Reading[] };
  return body.readings ?? [];
}

const dueWatches = await asPlatformAdmin((tx) => claimDueWatches(tx, { limit: 50 }), { db });

let shifts = 0;
const problems: string[] = [];

for (const watch of dueWatches) {
  const outcome = await withWorkspace(
    watch.workspaceId,
    (tx) => runWatch(tx, watch, readTrends),
    { db },
  );
  shifts += outcome.shifts.length;
  // A source that is down is not a broken watch; the baseline is untouched, so
  // the next comparison is still against the right number.
  if (outcome.error) problems.push(`watch ${watch.id}: ${outcome.error}`);
  for (const shift of outcome.shifts) console.log(`[market-content] ${shift.reason}`);
}

/* ---------------------------------------------------------- 2. pipelines */

const duePipelines = await asPlatformAdmin((tx) => claimDuePipelines(tx, { limit: 20 }), { db });

let drafted = 0;
let skipped = 0;

for (const pipeline of duePipelines) {
  const outcome = await withWorkspace(
    pipeline.workspaceId,
    (tx) => runPipeline(tx, pipeline, { driverFor, decrypt: decryptCredential }),
    { db },
  );

  if (outcome.status === 'failed') problems.push(`pipeline ${pipeline.id}: ${outcome.note}`);
  else if (outcome.status === 'skipped') skipped += 1;
  else if (outcome.drafted) drafted += 1;
  else {
    // Completed without a draft: AI is unavailable and the customer has a brief.
    // Worth logging, not worth a non-zero exit.
    console.log(`[market-content] ${pipeline.name}: ${outcome.note}`);
  }
}

/* --------------------------------------------------------- 3. publishing */

const projects = await asPlatformAdmin(
  (tx) =>
    tx.execute<{ workspace_id: string }>(sql`
      select distinct workspace_id from content_docs where status = 'approved' and deleted_at is null`),
  { db },
);

let published = 0;

for (const { workspace_id: workspaceId } of projects) {
  const queued = await withWorkspace(workspaceId, (tx) => pendingPublishes(tx), { db });

  for (const item of queued) {
    const destination = await withWorkspace(
      workspaceId,
      (tx) =>
        tx.execute<{
          kind: string; name: string; config: Record<string, string>;
          credentials_encrypted: string | null; default_status: string;
        }>(sql`
          select kind, name, config, credentials_encrypted, default_status
            from publish_destinations where id = ${item.destinationId} and is_enabled`),
      { db },
    ).then((rows) => rows[0]);

    if (!destination) continue;

    const publisher = publisherFor(destination);
    if (!publisher) {
      problems.push(`destination ${item.destinationId}: no publisher for ${destination.kind}`);
      continue;
    }

    const result = await publisher({
      title: item.title,
      body: item.body,
      html: await marked.parse(item.body),
      slug: item.slug ?? undefined,
      // The destination's own setting, which defaults to `draft` in the schema.
      status: destination.default_status === 'publish' ? 'publish' : 'draft',
    });

    if (!result.ok) {
      /*
       * Left as `approved`, so the next run tries again. A transient 502 must
       * not silently drop an article the customer approved — and a genuine
       * rejection keeps saying so until somebody looks.
       */
      problems.push(`publish ${item.docId} → ${destination.name}: ${result.message}`);
      continue;
    }

    await withWorkspace(
      workspaceId,
      async (tx) => {
        const [doc] = await tx.execute<{ project_id: string; meta: Record<string, unknown> }>(sql`
          select project_id, meta from content_docs where id = ${item.docId}`);
        if (!doc) return;
        await tx.execute(sql`
          update content_docs
             set meta = ${JSON.stringify({
               ...doc.meta,
               publishedTo: destination.name,
               externalId: result.externalId,
               externalUrl: result.url,
               // What the remote actually did, which may be less than we asked:
               // WordPress downgrades a publish to pending without erroring.
               externalStatus: result.status,
             })}::jsonb
           where id = ${item.docId}`);
        await saveDoc(tx, {
          workspaceId,
          projectId: doc.project_id,
          docId: item.docId,
          status: 'published',
        });
      },
      { db },
    );
    published += 1;
  }
}

function publisherFor(destination: {
  kind: string; config: Record<string, string>; credentials_encrypted: string | null;
}): Publisher | null {
  const secret = destination.credentials_encrypted
    ? decryptCredential(destination.credentials_encrypted)
    : '';

  switch (destination.kind) {
    case 'wordpress':
      return wordpressPublisher({
        siteUrl: destination.config.siteUrl ?? '',
        username: destination.config.username ?? '',
        applicationPassword: secret,
      });
    case 'ghost':
      return ghostPublisher({ adminApiUrl: destination.config.adminApiUrl ?? '', token: secret });
    case 'shopify':
      return shopifyPublisher({
        shop: destination.config.shop ?? '',
        blogId: destination.config.blogId ?? '',
        accessToken: secret,
      });
    case 'webhook':
      return webhookPublisher({ url: destination.config.url ?? '', secret: secret || undefined });
    default:
      return null;
  }
}

console.log(
  `[market-content] ${dueWatches.length} watch(es), ${shifts} shift(s); ` +
    `${duePipelines.length} pipeline(s), ${drafted} drafted, ${skipped} skipped; ` +
    `${published} published`,
);
for (const problem of problems) console.warn(`[market-content] ${problem}`);

await closeDb();

/*
 * Non-zero only for things somebody must act on. A pipeline that skipped
 * because nothing was worth writing about is the system working.
 */
process.exit(problems.length > 0 ? 1 : 0);
