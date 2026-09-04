/**
 * Managing what the visibility probes ask, and who they compare you against.
 *
 * Small surface, three constraints that matter:
 *
 * **Exactly one brand is yours.** Share of voice is a ratio with your mentions
 * on top; two "self" brands makes the number meaningless and zero makes it
 * undefined. Rather than validate at run time and refuse a probe the customer
 * has already paid for, marking a brand as yours *unmarks* the previous one in
 * the same statement.
 *
 * **A prompt is a question a buyer would actually type**, so it is stored
 * verbatim and never rewritten. `buildProbe` deliberately does not name the
 * brand — see `visibility.ts` — and rewriting the prompt here would defeat
 * that from the other end.
 *
 * **Deleting a prompt keeps its runs.** The answers are the evidence behind
 * every snapshot already drawn on the chart; cascading them away would silently
 * rewrite history.
 */
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { MarketNotAllowed } from './service.ts';
import { ASSISTANTS } from './visibility-runner.ts';

export type PromptRow = {
  id: string;
  prompt: string;
  intent: string | null;
  schedule: string;
  isTracked: boolean;
  nextRunAt: string | null;
  lastRunAt: string | null;
  /** Assistants that named the brand on the most recent run of this prompt. */
  mentionedBy: string[];
  /** Assistants asked on that run, so "2 of 4" is stateable. */
  askedBy: string[];
};

export type CompetitorRow = {
  id: string;
  brand: string;
  domain: string | null;
  isSelf: boolean;
};

/* ------------------------------------------------------------------ prompts */

export async function addPrompt(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    prompt: string;
    intent?: string;
    schedule?: 'daily' | 'weekly' | 'monthly';
  },
): Promise<string> {
  const prompt = opts.prompt.trim();
  if (prompt.length < 8) {
    throw new MarketNotAllowed(
      'invalid',
      'A prompt needs to be a question somebody would really ask — a few words is not one.',
    );
  }
  if (prompt.length > 500) {
    throw new MarketNotAllowed('invalid', 'Prompts are capped at 500 characters.');
  }

  /*
   * Counted against the tracked-keyword allowance rather than a new one.
   *
   * A tracked prompt and a tracked keyword cost the same kind of money on the
   * same cadence, and inventing `market.tracked_prompts` would mean every
   * existing subscriber silently has zero of them — features are opt-in, so a
   * new key defaults to denied.
   */
  await requireHeadroom(
    tx,
    opts.workspaceId,
    'market.tracked_keywords',
    sql`select count(*)::int as count from market_ai_prompts
         where project_id = ${opts.projectId} and is_tracked`,
  );

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into market_ai_prompts (workspace_id, project_id, prompt, intent, schedule)
    values (${opts.workspaceId}, ${opts.projectId}, ${prompt},
            ${opts.intent ?? null}, ${opts.schedule ?? 'weekly'})
    -- Asking the same question twice a week costs twice and says the same
    -- thing, so a duplicate quietly resolves to the existing row.
    on conflict on constraint market_ai_prompts_key do update
       set is_tracked = true, updated_at = now()
    returning id`);

  return row!.id;
}

export async function setPromptTracked(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; promptId: string; tracked: boolean },
): Promise<void> {
  if (opts.tracked) {
    await requireHeadroom(
      tx,
      opts.workspaceId,
      'market.tracked_keywords',
      sql`select count(*)::int as count from market_ai_prompts
           where project_id = ${opts.projectId} and is_tracked and id <> ${opts.promptId}`,
    );
  }

  await tx.execute(sql`
    update market_ai_prompts
       set is_tracked = ${opts.tracked},
           -- Untracking must also clear the claim, or re-tracking waits out
           -- an hour of the scheduler's back-off for no reason.
           next_run_at = ${opts.tracked ? null : sql`next_run_at`},
           updated_at = now()
     where id = ${opts.promptId} and project_id = ${opts.projectId}`);
}

export async function deletePrompt(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; promptId: string },
): Promise<void> {
  /*
   * Soft: `is_tracked = false` and out of the list, but the runs survive.
   *
   * Those answers are the evidence behind snapshots already drawn on the
   * chart. A hard delete cascades them and quietly rewrites six months of
   * share-of-voice history.
   */
  await tx.execute(sql`
    update market_ai_prompts
       set is_tracked = false, deleted_at = now(), updated_at = now()
     where id = ${opts.promptId} and project_id = ${opts.projectId}`);
}

/* -------------------------------------------------------------- competitors */

export async function saveCompetitor(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    brand: string;
    domain?: string | null;
    isSelf?: boolean;
  },
): Promise<string> {
  const brand = opts.brand.trim();
  if (brand.length < 2) {
    // `spansOf` refuses to match a one-character term — it would hit every
    // sentence — so a brand that short would silently never be found.
    throw new MarketNotAllowed('invalid', 'A brand name needs at least two characters.');
  }

  const domain = opts.domain?.trim().toLowerCase().replace(/^https?:\/\//, '').replace(/^www\./, '')
    .replace(/\/.*$/, '') || null;

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into market_ai_competitors (workspace_id, project_id, brand, domain, is_self)
    values (${opts.workspaceId}, ${opts.projectId}, ${brand}, ${domain}, ${opts.isSelf ?? false})
    on conflict on constraint market_ai_competitors_key do update
       set domain = coalesce(excluded.domain, market_ai_competitors.domain),
           is_self = excluded.is_self,
           updated_at = now()
    returning id`);

  if (opts.isSelf) await markOnlySelf(tx, opts.projectId, row!.id);
  return row!.id;
}

export async function setSelfBrand(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; competitorId: string },
): Promise<void> {
  await markOnlySelf(tx, opts.projectId, opts.competitorId);
}

/**
 * One brand is yours, and marking a new one unmarks the old.
 *
 * Done in a single statement rather than clear-then-set: between two statements
 * the set has zero self brands, and a probe claiming the row in that window
 * refuses with "nothing to measure" for a configuration that is actually fine.
 */
async function markOnlySelf(
  tx: WorkspaceScopedDb,
  projectId: string,
  competitorId: string,
): Promise<void> {
  await tx.execute(sql`
    update market_ai_competitors
       set is_self = (id = ${competitorId}), updated_at = now()
     where project_id = ${projectId}
       and is_self <> (id = ${competitorId})`);
}

export async function deleteCompetitor(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; competitorId: string },
): Promise<void> {
  const [row] = await tx.execute<{ is_self: boolean }>(sql`
    select is_self from market_ai_competitors
     where id = ${opts.competitorId} and project_id = ${opts.projectId}`);

  if (row?.is_self) {
    throw new MarketNotAllowed(
      'invalid',
      'That is the brand being measured. Mark another one as yours first, or there is nothing to compute a share of.',
    );
  }

  await tx.execute(sql`
    delete from market_ai_competitors
     where id = ${opts.competitorId} and project_id = ${opts.projectId}`);
}

/* ------------------------------------------------------------------ reading */

export async function listPrompts(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<PromptRow[]> {
  const rows = await tx.execute<{
    id: string; prompt: string; intent: string | null; schedule: string;
    is_tracked: boolean; next_run_at: string | null; last_run_at: string | null;
    mentioned_by: string[] | null; asked_by: string[] | null;
  }>(sql`
    with latest as (
      -- The most recent *batch* per prompt, not the most recent row: one probe
      -- writes a row per assistant and they are a single result. Those rows
      -- share a transaction, so now() gives them an identical created_at;
      -- the window is slack in case a future runner ever commits per model.
      select r.prompt_id,
             max(r.created_at) as last_run_at,
             array_agg(distinct r.model) filter (where r.status = 'ok') as asked_by,
             array_agg(distinct r.model) filter (where r.brand_mentioned) as mentioned_by
        from market_ai_prompt_runs r
        join (
          select prompt_id, max(created_at) as at
            from market_ai_prompt_runs group by prompt_id
        ) newest on newest.prompt_id = r.prompt_id
       where r.created_at > newest.at - interval '5 minutes'
       group by r.prompt_id
    )
    select p.id, p.prompt, p.intent, p.schedule, p.is_tracked,
           p.next_run_at, l.last_run_at, l.mentioned_by, l.asked_by
      from market_ai_prompts p
      left join latest l on l.prompt_id = p.id
     where p.project_id = ${opts.projectId} and p.deleted_at is null
     order by p.is_tracked desc, p.created_at`);

  return rows.map((r) => ({
    id: r.id,
    prompt: r.prompt,
    intent: r.intent,
    schedule: r.schedule,
    isTracked: r.is_tracked,
    nextRunAt: r.next_run_at,
    lastRunAt: r.last_run_at,
    mentionedBy: r.mentioned_by ?? [],
    askedBy: r.asked_by ?? [],
  }));
}

export async function listCompetitors(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<CompetitorRow[]> {
  const rows = await tx.execute<{
    id: string; brand: string; domain: string | null; is_self: boolean;
  }>(sql`
    select id, brand, domain, is_self from market_ai_competitors
     where project_id = ${opts.projectId}
     order by is_self desc, brand`);

  return rows.map((r) => ({ id: r.id, brand: r.brand, domain: r.domain, isSelf: r.is_self }));
}

export type VisibilityOverview = {
  /** Latest snapshot per assistant, newest first by capture date. */
  current: {
    model: string;
    capturedOn: string;
    shareOfVoice: number;
    mentionRate: number;
    avgPosition: number | null;
    citationCount: number;
    /** Change in share of voice since the previous snapshot; null if first. */
    delta: number | null;
  }[];
  /** Share of voice per assistant over time, for the trend chart. */
  series: { model: string; capturedOn: string; shareOfVoice: number; mentionRate: number }[];
  /** Assistants configured but never yet asked, and why. */
  unavailable: { assistant: string; reason: string }[];
};

export async function visibilityOverview(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; days?: number },
): Promise<VisibilityOverview> {
  const days = opts.days ?? 90;

  const series = await tx.execute<{
    model: string; captured_on: string; share_of_voice: number; mention_rate: number;
  }>(sql`
    select model, captured_on::text, share_of_voice, mention_rate
      from market_ai_visibility_snapshots
     where project_id = ${opts.projectId}
       and captured_on > current_date - (${days} * interval '1 day')
     order by captured_on, model`);

  const current = await tx.execute<{
    model: string; captured_on: string; share_of_voice: number; mention_rate: number;
    avg_position: number | null; citation_count: number; previous: number | null;
  }>(sql`
    select distinct on (model)
           model, captured_on::text, share_of_voice, mention_rate,
           avg_position, citation_count,
           lag(share_of_voice) over (partition by model order by captured_on) as previous
      from market_ai_visibility_snapshots
     where project_id = ${opts.projectId}
     order by model, captured_on desc`);

  /*
   * Which assistants have a model behind them.
   *
   * Reported rather than hidden: an operator who never configured an OpenAI
   * key should read "no enabled openai model", not conclude from a blank
   * column that ChatGPT has never once named them.
   */
  const enabled = await tx.execute<{ provider_key: string }>(sql`
    select distinct m.provider_key from ai_models m
      join ai_providers p on p.key = m.provider_key
     where m.modality = 'text' and m.is_enabled and p.is_enabled`);
  const have = new Set(enabled.map((r) => r.provider_key));

  const unavailable = Object.entries(ASSISTANTS)
    .filter(([, provider]) => !have.has(provider))
    .map(([assistant, provider]) => ({
      assistant,
      reason: `No enabled ${provider} model — an admin can add one in Settings → AI.`,
    }));

  return {
    current: current.map((r) => ({
      model: r.model,
      capturedOn: r.captured_on,
      shareOfVoice: r.share_of_voice,
      mentionRate: r.mention_rate,
      avgPosition: r.avg_position,
      citationCount: r.citation_count,
      delta: r.previous === null ? null : r.share_of_voice - r.previous,
    })),
    series: series.map((r) => ({
      model: r.model,
      capturedOn: r.captured_on,
      shareOfVoice: r.share_of_voice,
      mentionRate: r.mention_rate,
    })),
    unavailable,
  };
}

/** The stored answers behind one prompt, newest batch first. */
export async function promptAnswers(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; promptId: string; limit?: number },
): Promise<{
  model: string; answer: string | null; status: string; error: string | null;
  mentioned: boolean; position: number | null; sources: { url: string; title?: string }[];
  at: string;
}[]> {
  const rows = await tx.execute<{
    model: string; answer: string | null; status: string; error: string | null;
    brand_mentioned: boolean; mention_position: number | null;
    cited_sources: { url: string; title?: string }[] | null; created_at: string;
  }>(sql`
    select r.model, r.answer, r.status, r.error, r.brand_mentioned,
           r.mention_position, r.cited_sources, r.created_at::text
      from market_ai_prompt_runs r
      join market_ai_prompts p on p.id = r.prompt_id
     where r.prompt_id = ${opts.promptId} and p.project_id = ${opts.projectId}
     order by r.created_at desc, r.model
     limit ${opts.limit ?? 20}`);

  return rows.map((r) => ({
    model: r.model,
    answer: r.answer,
    status: r.status,
    error: r.error,
    mentioned: r.brand_mentioned,
    position: r.mention_position,
    sources: r.cited_sources ?? [],
    at: r.created_at,
  }));
}

async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  countSql: ReturnType<typeof sql>,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const [counted] = await tx.execute<{ count: number }>(countSql);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}
