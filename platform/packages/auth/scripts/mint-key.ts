/**
 * Mints an API key for a workspace.
 *
 * `pnpm --filter @mamal/auth mint-key -- <workspace-slug-or-id> [scope ...]`
 * The secret is printed once and never recoverable — the same contract the UI
 * gives a user, so this script cannot become a back door to an existing key.
 */
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, withWorkspace, unsafeUnscopedDb, closeDb } from '@mamal/db';
import { createApiKey } from '../src/api-keys.ts';

const [target, ...scopes] = process.argv.slice(2);
const db = unsafeUnscopedDb();

const [ws] = await asPlatformAdmin(
  (tx) =>
    tx.execute<{ id: string; name: string }>(
      target
        ? sql`select w.id, w.name from workspaces w
                left join users u on u.id = w.owner_user_id
               where w.slug = ${target} or w.id::text = ${target} or u.email = ${target}
               limit 1`
        : sql`select id, name from workspaces order by created_at limit 1`,
    ),
  { db },
);
if (!ws) throw new Error(`no workspace matching ${target ?? '(first)'}`);

const minted = await withWorkspace(
  ws.id,
  (tx) =>
    createApiKey(tx, {
      workspaceId: ws.id,
      name: 'cli',
      scopes: scopes.length ? scopes : ['*'],
    }),
  { db },
);

console.log(JSON.stringify({ workspace: ws.name, workspaceId: ws.id, secret: minted.secret }));
await closeDb();
