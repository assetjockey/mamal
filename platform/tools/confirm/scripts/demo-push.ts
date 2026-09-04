/** Enables push for the demo workspace so the screens have something real. */
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, withWorkspace, unsafeUnscopedDb, closeDb } from '@mamal/db';
import { enablePush } from '../src/index.ts';

const db = unsafeUnscopedDb();
const WS = '01a067f5-ab4a-7168-9754-ecbd5a6b2628';

const [proj] = await asPlatformAdmin((tx) => tx.execute<{ id: string }>(sql`
  select id from projects where workspace_id = ${WS} order by is_default desc limit 1`), { db });
const [site] = await asPlatformAdmin((tx) => tx.execute<{ id: string; host: string }>(sql`
  select id, host from sites where workspace_id = ${WS} limit 1`), { db });

const out = await withWorkspace(WS, (tx) => enablePush(tx, {
  workspaceId: WS, projectId: proj!.id, siteId: site!.id,
  // Demo only: the app encrypts properly via lib/crypto.
  encrypt: (s) => Buffer.from(s).toString('base64'),
}), { db });

console.log(JSON.stringify({ pushWebsiteId: out.id, publicKey: out.publicKey, host: site!.host }));
await closeDb();
