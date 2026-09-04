/**
 * The custom-domain sweep, as a cron entry point.
 *
 *     pnpm --filter @mamal/worker-core domains
 *
 * Runs every few minutes. It is idempotent and holds no state, so a missed run
 * costs a customer a few minutes of "waiting for DNS" and nothing else — and
 * two instances running it at once is safe, because the claim uses
 * `for update skip locked` rather than a leader lock.
 */
import { asPlatformAdmin, closeDb, unsafeUnscopedDb } from '@mamal/db';
import { sweepPendingDomains } from '@mamal/domains';

const target = process.env.CUSTOM_DOMAIN_TARGET ?? 'cname.mamal.app';
const addresses = (process.env.CUSTOM_DOMAIN_ADDRESSES ?? '')
  .split(',')
  .map((a) => a.trim())
  .filter(Boolean);

const db = unsafeUnscopedDb();
const result = await asPlatformAdmin(
  (tx) => sweepPendingDomains(tx, { target, addresses }),
  { db },
);

console.log(
  `[domains] checked ${result.checked}, verified ${result.verified.length}` +
    (result.verified.length > 0 ? `: ${result.verified.join(', ')}` : '') +
    `, ${result.stillWaiting} still waiting`,
);

await closeDb();
