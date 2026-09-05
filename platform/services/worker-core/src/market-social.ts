/**
 * Publishing due social posts.
 *
 *     pnpm --filter @mamal/worker-core market-social
 *
 * Runs every minute, because "scheduled for 09:00" means 09:00 and a
 * half-hourly sweep would make it mean "some time before 09:30".
 *
 * Claimed per *target*, not per post: five networks routinely produce four
 * successes and one rate limit, and the rate-limited one must retry on its own
 * clock without holding up the four that are ready.
 *
 * The transport is deliberately thin here. Each network's API is its own
 * integration and they are added one at a time; a provider with no publisher
 * yet fails its target with a message saying so rather than silently leaving
 * the post pending forever, which is the failure mode that makes people think
 * a scheduler is broken.
 */
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { claimDueTargets, recordFailure, recordPublished, type DueTarget } from '@mamal/tool-market';

const db = unsafeUnscopedDb();

type Published = { remoteId: string; remoteUrl: string | null };
type Failed = { message: string; retryable: boolean; retryAfterSeconds?: number };

/**
 * Hands a target to its network.
 *
 * Returns `Failed` rather than throwing so one network's outage cannot end the
 * sweep for everybody else's.
 */
async function publish(target: DueTarget): Promise<Published | Failed> {
  const publisher = PUBLISHERS[target.provider];
  if (!publisher) {
    return {
      // Not retryable: no amount of waiting adds an integration. Saying it
      // plainly beats a post that sits pending until somebody asks why.
      message: `Publishing to ${target.provider} is not connected on this instance yet.`,
      retryable: false,
    };
  }
  return publisher(target);
}

/**
 * Per-network transports.
 *
 * Empty for now, and that is a statement rather than an oversight: each of the
 * nine networks needs its own OAuth app, review process and media pipeline, and
 * they land one at a time. Everything above this line — scheduling, claiming,
 * retry, the four-of-five outcome — is finished and tested; this is the seam
 * they plug into.
 */
const PUBLISHERS: Record<string, ((target: DueTarget) => Promise<Published | Failed>) | undefined> = {};

const due = await asPlatformAdmin((tx) => claimDueTargets(tx, { limit: 100 }), { db });

let published = 0;
let failed = 0;
let retrying = 0;
const problems: string[] = [];

for (const target of due) {
  const result = await publish(target);

  await withWorkspace(
    target.workspaceId,
    async (tx) => {
      if ('remoteId' in result) {
        await recordPublished(tx, {
          targetId: target.targetId,
          postId: target.postId,
          remoteId: result.remoteId,
          remoteUrl: result.remoteUrl,
        });
        published += 1;
        return;
      }

      const { willRetry } = await recordFailure(tx, {
        targetId: target.targetId,
        postId: target.postId,
        message: result.message,
        retryable: result.retryable,
        retryAfterSeconds: result.retryAfterSeconds,
      });

      if (willRetry) retrying += 1;
      else {
        failed += 1;
        problems.push(`${target.provider}: ${result.message}`);
      }
    },
    { db },
  );
}

console.log(
  `[market-social] ${due.length} due — ${published} published, ${retrying} retrying, ${failed} failed`,
);
for (const problem of problems) console.warn(`[market-social] ${problem}`);

await closeDb();

// A target that will retry is not a problem yet. One that has given up is.
process.exit(failed > 0 ? 1 : 0);
