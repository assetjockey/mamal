/**
 * Polling in-flight generations.
 *
 *     pnpm --filter @mamal/worker-core market-creatives
 *
 * Every minute, because a video finishing at 14:03 should appear at 14:03 and
 * not at 14:30. The work per tick is tiny — one HTTP call per in-flight job —
 * which is the entire point of not holding a worker open for the five minutes
 * a render takes.
 *
 * Safe to kill at any moment: the generation lives in an `ad_creatives` row,
 * not in this process, so the worst a `kill -9` costs is one poll.
 */
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { claimPollable, pollCreative, type ProviderStatus } from '@mamal/tool-market';

const db = unsafeUnscopedDb();

/**
 * Asks a provider how a job is getting on.
 *
 * Unimplemented per provider for now, and it returns `running` rather than
 * throwing: an unknown provider must not fail a render that is genuinely in
 * flight. The `MAX_POLLS` ceiling means such a job is eventually abandoned and
 * *refunded* rather than held forever.
 */
async function poll(input: { provider: string; jobId: string }): Promise<ProviderStatus> {
  void input;
  return { state: 'running' };
}

const due = await asPlatformAdmin((tx) => claimPollable(tx, { limit: 100 }), { db });

let completed = 0;
let failed = 0;
let abandoned = 0;

for (const creative of due) {
  const outcome = await withWorkspace(
    creative.workspaceId,
    (tx) =>
      pollCreative(tx, creative, {
        driverFor: () => undefined,
        decrypt: (value: string) => value,
        poll,
      } as never),
    { db },
  );

  if (outcome.status === 'completed') completed += 1;
  else if (outcome.status === 'failed') failed += 1;
  else if (outcome.status === 'abandoned') {
    abandoned += 1;
    console.warn(`[market-creatives] ${creative.id}: ${outcome.note}`);
  }
}

console.log(
  `[market-creatives] ${due.length} in flight — ${completed} completed, ` +
    `${failed} failed, ${abandoned} abandoned`,
);

await closeDb();

// A render still going is not a problem. One abandoned after an hour of
// silence is worth somebody noticing.
process.exit(abandoned > 0 ? 1 : 0);
