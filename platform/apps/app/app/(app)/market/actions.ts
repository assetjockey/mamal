'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  addPrompt, briefFromSearchConsole, composePost, createDoc, createRankConfig, deleteCompetitor,
  deleteDoc, deletePrompt, recomputeOpportunities, runPipeline, runVisibilityProbes,
  saveCompetitor, saveConnection, saveDoc, saveQueue, saveWatch, setApproval,
  setOpportunityStatus, setPromptTracked, setSelfBrand, syncSearchConsole, trackKeywords,
  upsertKeywords, generateCopy, saveBrand, submitCreative, draftReply, runGrid, saveReply,
  MarketNotAllowed,
} from '@mamal/tool-market';
import { createLink, shortUrl, LinkNotAllowed } from '@mamal/tool-link';
import { decryptCredential, driverFor, encryptCredential } from '@mamal/ai';
import type { GoogleCredentials } from '@mamal/integrations';
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

/**
 * Pulls Search Console now, rather than waiting for the six-hourly sweep.
 *
 * It runs the *same* `syncSearchConsole` the cron runs — so the button and the
 * background job cannot disagree about what Google said, which is the mistake
 * the domain "Check now" button originally made.
 *
 * Then recomputes, because the finders compare windows: new rows without a
 * recompute means fresh data and a stale answer, which is worse than neither.
 */
export async function syncNow(connectionId: string): Promise<ActionResult<{
  days: number; rows: number; opportunities: number;
}>> {
  const { ws, database } = await ctx();

  const outcome = await withWorkspace(ws, async (tx) => {
    const result = await syncSearchConsole(tx, { workspaceId: ws, connectionId }, {
      oauth: {
        clientId: process.env.GOOGLE_CLIENT_ID ?? '',
        clientSecret: process.env.GOOGLE_CLIENT_SECRET ?? '',
      },
      decrypt: (encrypted) => JSON.parse(decryptCredential(encrypted)) as GoogleCredentials,
      encrypt: (credentials) => encryptCredential(JSON.stringify(credentials)),
    });
    if (result.failed) return { result, opportunities: 0 };

    const projectId = await defaultProject(tx, ws);
    const found = await recomputeOpportunities(tx, { workspaceId: ws, projectId });
    return { result, opportunities: found.found };
  }, { db: database });

  revalidatePath('/market/connections');
  revalidatePath('/market/opportunities');

  if (outcome.result.failed) {
    const { reason, message } = outcome.result.failed;
    return {
      ok: false,
      error:
        reason === 'misconfigured'
          // An operator's problem, not the customer's, and saying which is the
          // difference between a support ticket and a config change.
          ? `${message} This is an instance setting, not something you can fix from here.`
          : reason === 'rate_limited'
            ? `${message} Google is pacing us — the scheduled sync will pick up where this stopped.`
            : message,
    };
  }

  return {
    ok: true,
    days: outcome.result.days,
    rows: outcome.result.rows,
    opportunities: outcome.opportunities,
  };
}

/* ------------------------------------------------------------- visibility */

export async function addVisibilityPrompt(
  prompt: string,
  schedule: 'daily' | 'weekly' | 'monthly',
): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return addPrompt(tx, { workspaceId: ws, projectId, prompt, schedule });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true, id: result.value };
}

export async function toggleVisibilityPrompt(
  promptId: string,
  tracked: boolean,
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await setPromptTracked(tx, { workspaceId: ws, projectId, promptId, tracked });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true };
}

export async function removeVisibilityPrompt(promptId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await deletePrompt(tx, { projectId, promptId });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true };
}

export async function saveVisibilityBrand(
  brand: string,
  domain: string,
  isSelf: boolean,
): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return saveCompetitor(tx, { workspaceId: ws, projectId, brand, domain, isSelf });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true, id: result.value };
}

export async function setVisibilitySelf(competitorId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await setSelfBrand(tx, { projectId, competitorId });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true };
}

export async function removeVisibilityBrand(competitorId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await deleteCompetitor(tx, { projectId, competitorId });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/visibility');
  return { ok: true };
}

/**
 * Run the probes now, from the button.
 *
 * The cost is stated before the click, not discovered after: four assistants
 * at ten credits each, times the number of tracked prompts. `runVisibilityProbes`
 * holds and releases per call, so an assistant that never answers costs nothing
 * — but the *estimate* has to be the worst case or it is not a warning.
 */
export async function runVisibilityNow(): Promise<ActionResult<{
  answered: number; failed: number; probes: number; problem: string | null;
  unavailable: { assistant: string; reason: string }[];
}>> {
  const { ws, database } = await ctx();

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return runVisibilityProbes(tx, { workspaceId: ws, projectId }, {
        driverFor,
        decrypt: decryptCredential,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/visibility');
  const { answered, failed, probes, problem, unavailable } = result.value;
  return { ok: true, answered, failed, probes, problem, unavailable };
}

/* ---------------------------------------------------------------- content */

export async function newDoc(title: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createDoc(tx, { workspaceId: ws, projectId, title });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/content');
  return { ok: true, id: result.value };
}

export async function saveDocument(input: {
  docId: string;
  title?: string;
  body?: string;
  slug?: string;
  metaDescription?: string;
  targetKeywords?: string[];
  status?: 'draft' | 'in_review' | 'approved' | 'published';
}): Promise<ActionResult<{ score: number; warning: string | null }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return saveDoc(tx, { workspaceId: ws, projectId, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/content');
  revalidatePath(`/market/content/${input.docId}`);
  return { ok: true, score: result.value.score.score, warning: result.value.warning };
}

export async function removeDocument(docId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await deleteDoc(tx, { projectId, docId });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/content');
  return { ok: true };
}

/**
 * Build a brief from the workspace's own Search Console rows.
 *
 * Free and un-gated on purpose: the questions are queries the customer already
 * receives impressions for, so this is the useful half of a content brief
 * without a vendor call — which is what makes the editor worth opening on a
 * free or lifetime plan.
 */
export async function buildBrief(
  docId: string,
  keyword: string,
): Promise<ActionResult<{ entities: number; questions: number; rows: number }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return briefFromSearchConsole(tx, { workspaceId: ws, projectId, docId, keyword });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath(`/market/content/${docId}`);
  return {
    ok: true,
    entities: result.value.entities.length,
    questions: result.value.questions.length,
    rows: result.value.rows,
  };
}

/* -------------------------------------------------------------- pipelines */

export async function runPipelineNow(pipelineId: string): Promise<ActionResult<{
  status: string; note: string; docId: string | null; drafted: boolean; creditsSpent: number;
}>> {
  const { ws, database } = await ctx();

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const [row] = await tx.execute<{
        id: string; project_id: string; name: string; source: string;
        source_config: Record<string, unknown>; destination_id: string | null;
        auto_publish: boolean;
      }>(sql`
        select id, project_id, name, source, source_config, destination_id, auto_publish
          from content_pipelines where id = ${pipelineId} and workspace_id = ${ws}`);
      if (!row) throw new MarketNotAllowed('not_found', 'That pipeline no longer exists.');

      return runPipeline(tx, {
        id: row.id,
        workspaceId: ws,
        projectId: row.project_id,
        name: row.name,
        source: row.source,
        sourceConfig: row.source_config,
        destinationId: row.destination_id,
        autoPublish: row.auto_publish,
      }, { driverFor, decrypt: decryptCredential });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/pipelines');
  revalidatePath('/market/content');
  const { status, note, docId, drafted, creditsSpent } = result.value;
  return { ok: true, status, note, docId, drafted, creditsSpent };
}

export async function savePipeline(input: {
  id?: string;
  name: string;
  source: string;
  schedule: string;
  autoPublish: boolean;
  isActive: boolean;
  keywords?: string[];
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      const config = JSON.stringify(input.keywords?.length ? { keywords: input.keywords } : {});

      if (input.id) {
        await tx.execute(sql`
          update content_pipelines
             set name = ${input.name}, source = ${input.source}, schedule = ${input.schedule},
                 source_config = ${config}::jsonb,
                 auto_publish = ${input.autoPublish}, is_active = ${input.isActive},
                 updated_at = now()
           where id = ${input.id} and project_id = ${projectId}`);
        return input.id;
      }

      const [row] = await tx.execute<{ id: string }>(sql`
        insert into content_pipelines
          (workspace_id, project_id, name, source, schedule, source_config, auto_publish, is_active)
        values (${ws}, ${projectId}, ${input.name}, ${input.source}, ${input.schedule},
                ${config}::jsonb, ${input.autoPublish}, ${input.isActive})
        returning id`);
      return row!.id;
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/pipelines');
  return { ok: true, id: result.value };
}

/* ----------------------------------------------------------------- trends */

export async function saveTrendWatch(input: {
  id?: string;
  name: string;
  keywords: string[];
  geos: string[];
  thresholdPct: number;
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return saveWatch(tx, { workspaceId: ws, projectId, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/trends');
  return { ok: true, id: result.value };
}

/* ----------------------------------------------------------------- social */

export async function createPost(input: {
  body: string;
  accountIds: string[];
  link?: string | null;
  images?: number;
  scheduleType?: 'now' | 'scheduled' | 'queue';
  scheduledAt?: string;
  campaign?: string;
}): Promise<ActionResult<{
  postId: string;
  scheduled: { accountId: string; provider: string; at: string | null }[];
  linkNote: string | null;
}>> {
  const { ws, database } = await ctx();

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);

      /*
       * The Link handoff.
       *
       * Injected rather than imported *by Market* — `tools/*` may not import
       * each other, so the app supplies it. It calls Link's own `createLink`
       * rather than inserting a row, so the alias rules and the link allowance
       * apply exactly as they would from Link's own screens.
       *
       * Returning null when Link refuses is deliberate: hitting the link limit
       * should cost the customer their tracking, not their post.
       */
      const shorten = async (opts: { url: string; campaign?: string }) => {
        try {
          const link = await createLink(tx, {
            workspaceId: ws,
            projectId,
            kind: 'short',
            destinationUrl: opts.url,
            campaign: opts.campaign,
          });
          return { linkId: link.id, shortUrl: shortUrl(link.alias) };
        } catch (err) {
          if (err instanceof LinkNotAllowed) return null;
          throw err;
        }
      };

      return composePost(tx, {
        workspaceId: ws,
        projectId,
        body: input.body,
        accountIds: input.accountIds,
        link: input.link ?? null,
        images: input.images ?? 0,
        campaign: input.campaign,
        scheduleType: input.scheduleType ?? 'queue',
        scheduledAt: input.scheduledAt ? new Date(input.scheduledAt) : undefined,
      }, { shorten });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/social');
  revalidatePath('/market/calendar');
  const { postId, scheduled, linkNote } = result.value;
  return { ok: true, postId, scheduled, linkNote };
}

export async function reviewPost(
  postId: string,
  state: 'approved' | 'rejected',
): Promise<ActionResult> {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      await setApproval(tx, { projectId, postId, state, userId: session.user.id });
    }, { db: db() }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/social');
  revalidatePath('/market/calendar');
  return { ok: true };
}

export async function saveAccountQueue(
  accountId: string,
  slots: Record<string, number[]>,
  timezone: string,
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, (tx) => saveQueue(tx, { workspaceId: ws, accountId, slots, timezone }), {
      db: database,
    }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/calendar');
  return { ok: true };
}

/* -------------------------------------------------------------------- ads */

export async function saveBrandKit(input: {
  id?: string;
  name: string;
  voice?: string;
  audience?: string;
  palette?: string[];
  dos?: string[];
  donts?: string[];
  isDefault?: boolean;
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return saveBrand(tx, { workspaceId: ws, projectId, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/market/studio');
  return { ok: true, id: result.value };
}

export async function generateAdCopy(input: {
  platform: string;
  brief: string;
  framework?: string;
  tone?: string;
  objective?: string;
  brandId?: string | null;
}): Promise<ActionResult<{ variants: unknown[]; creditsSpent: number }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return generateCopy(tx, { workspaceId: ws, projectId, ...input }, {
        driverFor,
        decrypt: decryptCredential,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/studio');
  return { ok: true, variants: result.value.variants, creditsSpent: result.value.creditsSpent };
}

export async function generateCreative(input: {
  type: 'image' | 'video';
  prompt: string;
  platform?: string;
  preset?: string;
  brandId?: string | null;
  durationSeconds?: number;
}): Promise<ActionResult<{ creativeId: string; status: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return submitCreative(tx, { workspaceId: ws, projectId, ...input }, {
        driverFor,
        decrypt: decryptCredential,
        /*
         * No store yet: the media pipeline that fetches a provider URL into R2
         * is `worker-media`'s job and lands with it. Until then a completed
         * generation records its cost and status without an asset, which is
         * visible and honest rather than a broken image.
         */
      });
    }, { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/studio');
  return { ok: true, creativeId: result.value.creativeId, status: result.value.status };
}

/* ------------------------------------------------------------------ local */

export async function runRankGrid(input: {
  profileId: string;
  keyword: string;
  size: number;
  radiusKm: number;
}): Promise<ActionResult<{ points: number; coverage: number }>> {
  const { ws, database } = await ctx();

  const result = await attempt(() =>
    withWorkspace(ws, (tx) =>
      runGrid(tx, {
        workspaceId: ws,
        profileId: input.profileId,
        keyword: input.keyword,
        size: input.size as 3 | 5 | 7 | 9 | 11 | 13 | 15,
        radiusKm: input.radiusKm,
      },
      /*
       * The per-point lookup is DataForSEO's local-pack endpoint and lands
       * with that integration. Until then every point reads as "not found",
       * which draws an honest empty map rather than an invented one — and
       * `runGrid` still charges nothing it did not use.
       */
      async () => null),
    { db: database }),
  );
  if (!result.ok) return result;

  revalidatePath('/market/local');
  return {
    ok: true,
    points: result.value.readings.length,
    coverage: result.value.summary.coverage,
  };
}

export async function replyToReview(input: {
  profileId: string;
  reviewId: string;
  reply?: string;
  draft?: boolean;
  businessName?: string;
}): Promise<ActionResult<{ draft?: string }>> {
  const { ws, database } = await ctx();

  if (input.draft) {
    const drafted = await attempt(() =>
      withWorkspace(ws, (tx) =>
        draftReply(tx, {
          workspaceId: ws,
          reviewId: input.reviewId,
          businessName: input.businessName ?? 'the business',
        }, { driverFor, decrypt: decryptCredential }),
      { db: database }),
    );
    if (!drafted.ok) return drafted;
    return { ok: true, draft: drafted.value.draft };
  }

  const saved = await attempt(() =>
    withWorkspace(ws, (tx) =>
      saveReply(tx, {
        profileId: input.profileId,
        reviewId: input.reviewId,
        reply: input.reply ?? '',
      }),
    { db: database }),
  );
  if (!saved.ok) return saved;

  revalidatePath('/market/local');
  return { ok: true };
}
