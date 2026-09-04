'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  createRankConfig, recomputeOpportunities, saveConnection, setOpportunityStatus,
  trackKeywords, upsertKeywords, MarketNotAllowed,
} from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

async function ctx() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  return { ws: session.workspace.id, database: db() };
}

export type ActionResult<T = unknown> = ({ ok: true } & T) | { ok: false; error: string };

/** One catch, so the resolver's own sentence reaches the person who hit the limit. */
async function attempt<T>(run: () => Promise<T>): Promise<ActionResult<{ value: T }>> {
  try {
    return { ok: true, value: await run() };
  } catch (e) {
    if (e instanceof MarketNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

async function defaultProject(
  tx: Parameters<Parameters<typeof withWorkspace>[1]>[0],
  ws: string,
): Promise<string> {
  const [p] = await (tx as { execute: <T>(q: unknown) => Promise<T[]> }).execute<{ id: string }>(sql`
    select id from projects where workspace_id = ${ws} order by is_default desc, created_at limit 1`);
  if (!p) throw new MarketNotAllowed('no_project', 'This workspace has no project yet.');
  return p.id;
}

/* ---------------------------------------------------------- opportunities */

export async function refreshOpportunities(): Promise<ActionResult<{ found: number }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return recomputeOpportunities(tx, { workspaceId: ws, projectId });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/opportunities');
  return { ok: true, found: result.value.found };
}

export async function actOnOpportunity(
  id: string,
  status: 'actioned' | 'dismissed' | 'open',
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => setOpportunityStatus(tx, { workspaceId: ws, id, status }), {
    db: database,
  });
  revalidatePath('/market/opportunities');
  return { ok: true };
}

/* --------------------------------------------------------------- keywords */

export async function addKeywords(input: string): Promise<ActionResult<{ added: number }>> {
  const { ws, database } = await ctx();
  const keywords = [...new Set(
    input.split(/[\n,]/).map((k) => k.trim().toLowerCase()).filter(Boolean),
  )];
  if (keywords.length === 0) return { ok: false, error: 'Nothing to add.' };

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      /*
       * Stored without metrics, deliberately.
       *
       * Volume and difficulty come from a paid vendor, and fetching them the
       * moment somebody pastes a list would bill them for research they have
       * not asked for. The list is useful without: they can tag it, track it,
       * and enrich it when they choose to spend.
       */
      return upsertKeywords(tx, {
        workspaceId: ws, projectId,
        keywords: keywords.map((keyword) => ({ keyword, source: 'manual' })),
      });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/keywords');
  return { ok: true, added: result.value };
}

/* ---------------------------------------------------------- rank tracking */

export async function newRankConfig(domain: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      const [site] = await tx.execute<{ id: string }>(sql`
        select id from sites where workspace_id = ${ws} and deleted_at is null limit 1`);
      return createRankConfig(tx, {
        workspaceId: ws, projectId, domain, siteId: site?.id ?? null,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/rank');
  return { ok: true, id: result.value };
}

export async function addTrackedKeywords(
  configId: string,
  input: string,
): Promise<ActionResult<{ added: number }>> {
  const { ws, database } = await ctx();
  const keywords = input.split(/[\n,]/).map((k) => k.trim()).filter(Boolean);
  const result = await attempt(() =>
    withWorkspace(ws, (tx) => trackKeywords(tx, { workspaceId: ws, configId, keywords }), {
      db: database,
    }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/rank');
  return { ok: true, added: result.value };
}

/* ------------------------------------------------------------ connections */

export async function connectProvider(input: {
  provider: string;
  externalId: string;
  displayName: string;
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return saveConnection(tx, { workspaceId: ws, projectId, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/connections');
  return { ok: true, id: result.value };
}

export async function disconnect(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  /*
   * Marked revoked, not deleted.
   *
   * Everything sourced from this connection — months of Search Console rows,
   * every rank snapshot — references it. Deleting the row would cascade all of
   * that away, and "I disconnected and lost a year of history" is not a
   * recoverable mistake.
   */
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update market_connections
       set status = 'revoked', credentials_encrypted = null, updated_at = now()
     where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/market/connections');
  return { ok: true };
}
