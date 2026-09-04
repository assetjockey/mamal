/**
 * The AI visibility probes.
 *
 *     pnpm --filter @mamal/worker-core market-visibility
 *
 * Separate from `market.ts` on purpose. Search Console is free and pulled every
 * six hours; a probe is real money on a weekly cadence, and putting them in one
 * job means either paying for probes four times a day or letting performance
 * data go stale for a week.
 *
 * The claim pushes `next_run_at` out an hour *on claim*, so a provider outage
 * costs one wasted claim rather than a retry loop that spends the balance.
 * Prompts are claimed platform-wide and then run inside each workspace's own
 * scope.
 */
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { decryptCredential, driverFor } from '@mamal/ai';
import { claimDuePrompts, runVisibilityProbes } from '@mamal/tool-market';

const db = unsafeUnscopedDb();

const due = await asPlatformAdmin((tx) => claimDuePrompts(tx, { limit: 50 }), { db });

/*
 * Grouped by project before running.
 *
 * `runVisibilityProbes` reads the brand set and writes one snapshot per
 * assistant for the whole project, so calling it once per prompt would rewrite
 * the same snapshot N times and bill the brand lookup N times over. One call
 * per project, with the claimed prompt ids.
 */
const byProject = new Map<string, { workspaceId: string; projectId: string; promptIds: string[] }>();
for (const prompt of due) {
  const key = `${prompt.workspaceId}:${prompt.projectId}`;
  const existing = byProject.get(key);
  if (existing) existing.promptIds.push(prompt.id);
  else {
    byProject.set(key, {
      workspaceId: prompt.workspaceId,
      projectId: prompt.projectId,
      promptIds: [prompt.id],
    });
  }
}

let answered = 0;
let failed = 0;
const problems: string[] = [];
const shifts: string[] = [];

for (const group of byProject.values()) {
  const result = await withWorkspace(
    group.workspaceId,
    (tx) =>
      runVisibilityProbes(
        tx,
        {
          workspaceId: group.workspaceId,
          projectId: group.projectId,
          promptIds: group.promptIds,
        },
        { driverFor, decrypt: decryptCredential },
      ),
    { db },
  );

  answered += result.answered;
  failed += result.failed;

  if (result.problem) {
    // A configuration the customer has to fix, and until they do every probe
    // in this project is refused before it spends anything.
    problems.push(`${group.projectId}: ${result.problem}`);
  }
  for (const shift of result.shifts) {
    shifts.push(`${group.projectId} ${shift.model}: ${shift.reason}`);
  }
  for (const gap of result.unavailable) {
    problems.push(`${group.projectId}: ${gap.assistant} not asked — ${gap.reason}`);
  }
}

console.log(
  `[market-visibility] ${due.length} prompt(s) across ${byProject.size} project(s): ` +
    `${answered} answered, ${failed} failed`,
);
for (const shift of shifts) console.log(`[market-visibility] ${shift}`);
for (const problem of problems) console.warn(`[market-visibility] ${problem}`);

await closeDb();

/*
 * A misconfigured brand set is worth a non-zero exit — nothing will ever be
 * measured until somebody fixes it. A model that failed is not: that is what
 * `allSettled` and the stored failed run are for.
 */
process.exit(problems.length > 0 ? 1 : 0);
