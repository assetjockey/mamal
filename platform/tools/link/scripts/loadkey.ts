/**
 * Mints a full-scope API key for the load harness.
 *
 *     pnpm --filter @mamal/tool-link loadkey <workspaceId>
 *
 * Kept next to the demo seeder because they are used together: seed the links,
 * mint a key, then run `pnpm load` against both.
 */
import { asPlatformAdmin, closeDb, unsafeUnscopedDb } from '@mamal/db';
import { createApiKey } from '@mamal/auth';

const workspaceId = process.argv[2];
if (!workspaceId) throw new Error('usage: loadkey <workspaceId>');

const db = unsafeUnscopedDb();
const minted = await asPlatformAdmin(
  (tx) => createApiKey(tx, { workspaceId, name: 'load harness', scopes: ['*'] }),
  { db },
);
console.log(minted.secret);
await closeDb();
