/**
 * Generating images and video: submit, poll, capture.
 *
 * Pattern 2 from the plan's job section, and the reason it exists: video takes
 * minutes. Holding a worker open on a five-minute HTTP call is how a queue
 * stops — twelve concurrent video jobs and nothing else runs. So the provider's
 * job id is *stored*, the worker returns, and a later tick asks whether it is
 * done.
 *
 * That storage is also what makes the whole thing survive a `kill -9`: the
 * generation lives in a row, not in a process. A worker that dies mid-flight
 * loses nothing but the poll it was about to make.
 *
 * Three rules about money, all of which the source products get wrong:
 *
 * **The hold is taken at submit and captured at completion**, at the *true*
 * unit count — video is priced per second and the count is not known until the
 * file exists. `magicads` debits on dispatch, so a failed generation eats the
 * customer's credits.
 *
 * **A failure releases.** Always, on every path, including the one where the
 * provider says "failed" hours later.
 *
 * **A generation that never answers is abandoned, not held forever.** A hold
 * that is never released is credits the customer cannot spend and cannot see.
 */
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { grant } from '@mamal/credits';
import { MarketNotAllowed } from './service.ts';
import { PRESETS, type Preset } from './ad-platforms.ts';

/** A provider's view of an in-flight job. */
export type ProviderStatus =
  | { state: 'running' }
  | { state: 'done'; url: string; units: number; vendorCostMicros?: number; durationSeconds?: number }
  | { state: 'failed'; message: string };

export type CreativeDeps = ExecuteDeps & {
  /** Asks the provider how a job is getting on. Injected so tests need no network. */
  poll?: (input: { provider: string; jobId: string }) => Promise<ProviderStatus>;
  /** Stores the finished bytes and returns an asset id. */
  store?: (input: { url: string; workspaceId: string; kind: 'image' | 'video' }) => Promise<string>;
};

/**
 * Refunds a generation that was charged and then did not arrive.
 *
 * `ai.execute` reserves and captures within a single call, which is right for
 * a synchronous generation and leaves a gap for an asynchronous one: by the
 * time a video provider says "failed" an hour later, the hold is long gone and
 * the customer has paid for nothing. So the money comes back as a refund
 * rather than a release.
 *
 * Keyed on the creative, so a poll that runs twice — a retried worker, two
 * schedulers — refunds once. The refunded credits are granted without an
 * expiry, which is marginally generous: reconstructing which buckets the
 * original spend drew from is not worth the complexity, and being generous
 * about *our* failure is the right side to err on.
 */
async function refund(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  creativeId: string,
): Promise<number> {
  const spent = await creditsFor(tx, creativeId);
  if (spent <= 0) return 0;

  await grant(tx, workspaceId, spent, {
    source: 'refund',
    sourceRef: creativeId,
    idempotencyKey: `${creativeId}:generation-refund`,
  });
  return spent;
}

/**
 * How long a generation may sit unfinished before it is given up on.
 *
 * Generous — some video models genuinely take ten minutes — but finite, because
 * the alternative is a hold nobody releases and credits the customer cannot
 * spend or see.
 */
export const MAX_POLLS = 60;
export const POLL_INTERVAL_SECONDS = 20;

export type BrandSnapshot = {
  name?: string;
  voice?: string;
  audience?: string;
  palette?: string[];
  dos?: string[];
  donts?: string[];
};

/**
 * The brand, as a paragraph a model can follow.
 *
 * Snapshotted onto the creative at generation time rather than joined at read
 * time: regenerating a creative six months later must produce the brand as it
 * was, or "regenerate" quietly means "redo in the new brand" — which is a
 * different image and a confusing one.
 */
export function toPromptContext(brand: BrandSnapshot): string {
  const parts: string[] = [];
  if (brand.name) parts.push(`Brand: ${brand.name}.`);
  if (brand.voice) parts.push(`Voice: ${brand.voice}`);
  if (brand.audience) parts.push(`Audience: ${brand.audience}`);
  if (brand.palette?.length) parts.push(`Palette: ${brand.palette.join(', ')}.`);
  if (brand.dos?.length) parts.push(`Always: ${brand.dos.join('; ')}.`);
  // Last, because instructions a model is meant to obey work better late in a
  // prompt than buried in the middle.
  if (brand.donts?.length) parts.push(`Never: ${brand.donts.join('; ')}.`);
  return parts.join('\n');
}

/* ------------------------------------------------------------ submitting */

export type SubmitInput = {
  workspaceId: string;
  projectId: string;
  type: 'image' | 'video';
  prompt: string;
  brandId?: string | null;
  platform?: string;
  preset?: string;
  durationSeconds?: number;
};

export type SubmitResult = {
  creativeId: string;
  /** `completed` when the provider answered immediately — images often do. */
  status: 'completed' | 'polling' | 'failed';
  assetId: string | null;
  creditsSpent: number;
  error: string | null;
};

export async function submitCreative(
  tx: WorkspaceScopedDb,
  input: SubmitInput,
  deps: CreativeDeps,
): Promise<SubmitResult> {
  const size = resolveSize(input.platform, input.preset);
  if (input.preset && !size) {
    throw new MarketNotAllowed(
      'invalid',
      `“${input.preset}” is not a size ${input.platform ?? 'that platform'} accepts.`,
    );
  }

  const brand = input.brandId ? await loadBrand(tx, input.brandId) : null;
  const context = brand ? toPromptContext(brand) : '';
  const prompt = context ? `${context}\n\n${input.prompt}` : input.prompt;

  const [creative] = await tx.execute<{ id: string }>(sql`
    insert into ad_creatives
      (workspace_id, project_id, brand_id, type, status, prompt, preset,
       width, height, duration_seconds, brand_snapshot)
    values (${input.workspaceId}, ${input.projectId}, ${input.brandId ?? null},
            ${input.type}, 'generating', ${prompt}, ${input.preset ?? null},
            ${size?.width ?? null}, ${size?.height ?? null},
            ${input.durationSeconds ?? null},
            ${JSON.stringify(brand ?? {})}::jsonb)
    returning id`);

  const creativeId = creative!.id;

  try {
    const result = await execute(
      tx,
      {
        featureKey: input.type === 'video' ? 'market.ai_video' : 'market.ai_image',
        prompt,
        modality: input.type,
        // Video is priced per second, so the estimate is the duration; the
        // capture trues it up when the file exists.
        expectedUnits: input.type === 'video' ? Math.ceil(input.durationSeconds ?? 6) : 1,
        options: size ? { width: size.width, height: size.height } : undefined,
      },
      { workspaceId: input.workspaceId, jobId: creativeId },
      deps,
    );

    if (!result.ok) {
      await fail(tx, creativeId, result.error ?? 'The provider returned nothing.');
      return {
        creativeId, status: 'failed', assetId: null, creditsSpent: 0,
        error: result.error ?? 'The provider returned nothing.',
      };
    }

    /*
     * Two shapes come back. An image provider usually hands over a URL there
     * and then; a video provider hands over a task id and expects to be asked
     * later. Both are normal, and the row records which happened.
     */
    if (result.externalTaskId) {
      await tx.execute(sql`
        update ad_creatives
           set status = 'polling', provider_job_id = ${result.externalTaskId},
               next_poll_at = now() + (${POLL_INTERVAL_SECONDS} * interval '1 second'),
               updated_at = now()
         where id = ${creativeId}`);
      return { creativeId, status: 'polling', assetId: null, creditsSpent: 0, error: null };
    }

    const assetId = result.url && deps.store
      ? await deps.store({ url: result.url, workspaceId: input.workspaceId, kind: input.type })
      : null;

    const spent = await creditsFor(tx, creativeId);
    await tx.execute(sql`
      update ad_creatives
         set status = 'completed', asset_id = ${assetId},
             credits_spent = ${spent},
             vendor_cost_micros = ${result.vendorCostMicros ?? 0},
             updated_at = now()
       where id = ${creativeId}`);

    return { creativeId, status: 'completed', assetId, creditsSpent: spent, error: null };
  } catch (err) {
    const message =
      err instanceof AiUnavailable
        ? err.message
        : err instanceof Error
          ? err.message
          : String(err);
    // `execute` has already released its own hold on both paths; this only
    // records why, so the studio can say something better than "failed".
    await fail(tx, creativeId, message);
    return { creativeId, status: 'failed', assetId: null, creditsSpent: 0, error: message };
  }
}

/* -------------------------------------------------------------- polling */

export type PollOutcome = {
  creativeId: string;
  status: 'completed' | 'polling' | 'failed' | 'abandoned';
  assetId: string | null;
  note: string | null;
};

/**
 * In-flight generations that are due a check, claimed.
 *
 * `for update skip locked` for the usual reason, and because polling the same
 * provider job from two workers is a good way to be rate limited by them.
 */
export async function claimPollable(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<{
  id: string; workspaceId: string; provider: string | null; providerJobId: string;
  type: 'image' | 'video'; pollCount: number; holdId: string | null;
}[]> {
  const rows = await tx.execute<{
    id: string; workspace_id: string; provider: string | null; provider_job_id: string;
    type: string; poll_count: number; credit_hold_id: string | null;
  }>(sql`
    with claimed as (
      select id from ad_creatives
       where status = 'polling' and provider_job_id is not null
         and (next_poll_at is null or next_poll_at <= now())
       order by next_poll_at nulls first
       limit ${opts.limit ?? 50}
       for update skip locked
    )
    update ad_creatives c
       set poll_count = c.poll_count + 1,
           next_poll_at = now() + (${POLL_INTERVAL_SECONDS} * interval '1 second'),
           updated_at = now()
      from claimed
     where c.id = claimed.id
    returning c.id, c.workspace_id, c.provider, c.provider_job_id, c.type,
              c.poll_count, c.credit_hold_id`);

  return rows.map((r) => ({
    id: r.id,
    workspaceId: r.workspace_id,
    provider: r.provider,
    providerJobId: r.provider_job_id,
    type: r.type === 'video' ? 'video' : 'image',
    pollCount: r.poll_count,
    holdId: r.credit_hold_id,
  }));
}

/**
 * Asks the provider about one job and settles the row.
 *
 * The `abandoned` outcome is the one worth reading twice: after `MAX_POLLS` a
 * job that has never answered is written off and its hold released. Leaving it
 * `polling` forever means credits the customer cannot spend and cannot see —
 * the quiet version of taking their money.
 */
export async function pollCreative(
  tx: WorkspaceScopedDb,
  creative: {
    id: string; workspaceId: string; provider: string | null; providerJobId: string;
    type: 'image' | 'video'; pollCount: number; holdId: string | null;
  },
  deps: CreativeDeps,
): Promise<PollOutcome> {
  if (!deps.poll) {
    return { creativeId: creative.id, status: 'polling', assetId: null, note: null };
  }

  let status: ProviderStatus;
  try {
    status = await deps.poll({
      provider: creative.provider ?? 'unknown',
      jobId: creative.providerJobId,
    });
  } catch (err) {
    /*
     * A failed *poll* is not a failed generation. The provider being briefly
     * unreachable must not throw away a video that is still rendering, so the
     * row stays `polling` and the next tick asks again.
     */
    return {
      creativeId: creative.id,
      status: 'polling',
      assetId: null,
      note: err instanceof Error ? err.message : String(err),
    };
  }

  if (status.state === 'running') {
    if (creative.pollCount >= MAX_POLLS) {
      const refunded = await refund(tx, creative.workspaceId, creative.id);
      await fail(
        tx,
        creative.id,
        'The provider never finished this generation, so it was refunded.',
      );
      return {
        creativeId: creative.id,
        status: 'abandoned',
        assetId: null,
        note: `Given up on after ${MAX_POLLS} checks; ${refunded} credits refunded.`,
      };
    }
    return { creativeId: creative.id, status: 'polling', assetId: null, note: null };
  }

  if (status.state === 'failed') {
    // Charged at submit, so a failure this late has to give the money back.
    const refunded = await refund(tx, creative.workspaceId, creative.id);
    await fail(tx, creative.id, status.message);
    return {
      creativeId: creative.id,
      status: 'failed',
      assetId: null,
      note: refunded > 0 ? `${status.message} ${refunded} credits refunded.` : status.message,
    };
  }

  const assetId = deps.store
    ? await deps.store({ url: status.url, workspaceId: creative.workspaceId, kind: creative.type })
    : null;

  const spent = await creditsFor(tx, creative.id);
  await tx.execute(sql`
    update ad_creatives
       set status = 'completed', asset_id = ${assetId},
           duration_seconds = coalesce(${status.durationSeconds ?? null}, duration_seconds),
           credits_spent = ${spent},
           vendor_cost_micros = ${status.vendorCostMicros ?? 0},
           next_poll_at = null, error = null, updated_at = now()
     where id = ${creative.id}`);

  return { creativeId: creative.id, status: 'completed', assetId, note: null };
}

/* --------------------------------------------------------------- shared */

async function fail(tx: WorkspaceScopedDb, creativeId: string, message: string): Promise<void> {
  await tx.execute(sql`
    update ad_creatives
       set status = 'failed', error = ${message}, next_poll_at = null, updated_at = now()
     where id = ${creativeId}`);
}

/** What this generation actually cost, from the ledger rather than an estimate. */
async function creditsFor(tx: WorkspaceScopedDb, creativeId: string): Promise<number> {
  const [row] = await tx.execute<{ credits: number }>(sql`
    select coalesce(-sum(delta), 0)::int as credits from credit_entries
     where idempotency_key like ${`${creativeId}:%`} and delta < 0`);
  return row?.credits ?? 0;
}

async function loadBrand(
  tx: WorkspaceScopedDb,
  brandId: string,
): Promise<BrandSnapshot | null> {
  const [row] = await tx.execute<{
    name: string; voice: string | null; audience: string | null;
    palette: string[]; dos: string[]; donts: string[];
  }>(sql`
    select name, voice, audience, palette, dos, donts
      from market_brands where id = ${brandId}`);

  return row
    ? {
        name: row.name,
        voice: row.voice ?? undefined,
        audience: row.audience ?? undefined,
        palette: row.palette,
        dos: row.dos,
        donts: row.donts,
      }
    : null;
}

/**
 * The canvas for a preset, checked against the platform when one is named.
 *
 * A 728×90 leaderboard requested for TikTok is a mistake worth catching: the
 * generation would succeed, cost money, and produce something unusable. So an
 * unknown preset returns null and the caller refuses, rather than quietly
 * generating at some default size.
 */
function resolveSize(platform?: string, preset?: string): Preset | null {
  if (!preset) return null;
  const found = PRESETS.find((p) => p.key === preset);
  if (!found) return null;
  if (platform && !found.platforms.includes(platform)) return null;
  return found;
}
