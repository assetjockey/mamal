import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import {
  buildProbe, isNotableShift, prepareBrands, readAnswer, summarise,
  type AnswerReading, type VisibilitySnapshot,
} from './visibility.ts';

/**
 * Running the visibility probes.
 *
 * One prompt, several models, and the answers compared. Three things make this
 * different from every other AI feature in the platform:
 *
 * **One provider failing must not blank the comparison.** The whole point is
 * seeing four models side by side; losing all four because Gemini timed out is
 * the failure mode `open-seo` warned about, so every model is settled
 * independently and a failure is stored as a failed *run* rather than thrown.
 *
 * **It is expensive and it is scheduled.** Forty credits a probe across four
 * models is real money on a weekly cadence, so the entitlement is checked once
 * per model per prompt through `ai.execute` — which holds and releases, so a
 * provider that never answers costs nothing.
 *
 * **The answer is evidence.** The text is stored, because "you are not
 * mentioned" is unactionable without seeing who was.
 */

export type ProbeResult = {
  promptId: string;
  prompt: string;
  runs: { model: string; ok: boolean; error?: string; reading?: AnswerReading }[];
};

export type RunVisibilityResult = {
  probes: number;
  answered: number;
  failed: number;
  snapshots: VisibilitySnapshot[];
  /** Set when the tracked set is unusable — no self brand, or several. */
  problem: string | null;
  shifts: { model: string; reason: string; from: number; to: number }[];
  /**
   * Assistants that could not be asked, and why. A gap in the comparison must
   * carry a reason: silently omitting Perplexity reads as "Perplexity never
   * mentions us", which is a different and much worse claim.
   */
  unavailable: { assistant: string; reason: string }[];
};

/**
 * The assistants a probe asks, and the provider behind each.
 *
 * The customer's question is "does ChatGPT name us", not "does gpt-5.4 name
 * us" — so the *assistant* is the dimension we store and chart, and which
 * concrete model sits behind it is an operator's choice in the AI registry.
 * That also means a model upgrade does not fragment a year of history into two
 * series.
 */
export const ASSISTANTS: Record<string, string> = {
  claude: 'anthropic',
  chatgpt: 'openai',
  gemini: 'google',
  perplexity: 'perplexity',
};

/** Which assistants a probe asks by default. Ordered for the chart legend. */
export const DEFAULT_MODELS = ['claude', 'chatgpt', 'gemini', 'perplexity'] as const;

type Resolved = { assistant: string; modelUuid: string };

/**
 * Each assistant's current model, as a `ai_models.id`.
 *
 * `execute` casts its `modelId` override to `uuid`, so handing it the string
 * "claude" does not merely fail — in Postgres a cast error aborts the whole
 * transaction, taking the other three models' results with it. The mapping has
 * to happen here.
 *
 * An assistant with no enabled model is *reported*, not dropped: a missing
 * series in the comparison needs a reason ("no Perplexity provider is
 * configured"), or the customer reads it as "Perplexity never mentions us".
 */
async function resolveAssistants(
  tx: WorkspaceScopedDb,
  names: readonly string[],
): Promise<{ resolved: Resolved[]; unavailable: { assistant: string; reason: string }[] }> {
  const providers = names.map((n) => ASSISTANTS[n]).filter((p): p is string => Boolean(p));

  const rows = providers.length
    ? await tx.execute<{ provider_key: string; id: string }>(sql`
        select distinct on (m.provider_key) m.provider_key, m.id
          from ai_models m
          join ai_providers p on p.key = m.provider_key
         where m.modality = 'text' and m.is_enabled and p.is_enabled
           and m.provider_key = any(${textArray(providers)}::text[])
         order by m.provider_key, m.is_recommended desc, m.sort_order`)
    : [];

  const byProvider = new Map(rows.map((r) => [r.provider_key, r.id]));
  const resolved: Resolved[] = [];
  const unavailable: { assistant: string; reason: string }[] = [];

  for (const assistant of names) {
    const provider = ASSISTANTS[assistant];
    if (!provider) {
      unavailable.push({ assistant, reason: `${assistant} is not a known assistant.` });
      continue;
    }
    const modelUuid = byProvider.get(provider);
    if (!modelUuid) {
      unavailable.push({
        assistant,
        reason: `No enabled ${provider} model. An admin can add one in Settings → AI.`,
      });
      continue;
    }
    resolved.push({ assistant, modelUuid });
  }

  return { resolved, unavailable };
}

export async function runVisibilityProbes(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    models?: readonly string[];
    /** Only these prompts; defaults to everything tracked and due. */
    promptIds?: string[];
    today?: Date;
  },
  deps: ExecuteDeps,
): Promise<RunVisibilityResult> {
  const requested = opts.models ?? DEFAULT_MODELS;
  const today = opts.today ?? new Date();

  const { resolved, unavailable } = await resolveAssistants(tx, requested);
  const models = resolved.map((r) => r.assistant);

  const competitorRows = await tx.execute<{ brand: string; domain: string | null; is_self: boolean }>(sql`
    select brand, domain, is_self from market_ai_competitors
     where project_id = ${opts.projectId} order by is_self desc, brand`);

  const { brands, problem } = prepareBrands(
    competitorRows.map((r) => ({ brand: r.brand, domain: r.domain, isSelf: r.is_self })),
  );
  if (problem) {
    // Refused up front rather than after spending forty credits a probe on a
    // number that cannot mean anything.
    return { probes: 0, answered: 0, failed: 0, snapshots: [], problem, shifts: [], unavailable };
  }

  const prompts = await tx.execute<{ id: string; prompt: string }>(sql`
    select id, prompt from market_ai_prompts
     where project_id = ${opts.projectId}
       and is_tracked and deleted_at is null
       ${opts.promptIds?.length ? sql`and id = any(${textArray(opts.promptIds)}::uuid[])` : sql``}
     order by created_at`);

  const readingsByModel = new Map<string, AnswerReading[]>();
  let answered = 0;
  let failed = 0;

  for (const prompt of prompts) {
    const probe = buildProbe(prompt.prompt);

    /*
     * `allSettled`, not `all`.
     *
     * Four models asked in parallel; one refusing, timing out or being switched
     * off must leave the other three intact. `all` would reject the lot, and
     * the customer would see an empty comparison with no way to tell which
     * provider was at fault.
     */
    const settled = await Promise.allSettled(
      resolved.map(async ({ assistant: model, modelUuid }) => {
        const result = await execute(
          tx,
          {
            featureKey: 'market.ai_visibility',
            prompt: probe.user,
            system: probe.system,
            modality: 'text',
          },
          // Asking a *specific* model is the whole product here: "does ChatGPT
          // name us, does Claude name us" are different questions.
          { workspaceId: opts.workspaceId, modelId: modelUuid },
          deps,
        );
        if (!result.ok || !result.text) {
          throw new Error(result.error ?? 'The model returned nothing.');
        }
        /*
         * Structured citations when the provider grounds its answer; URLs
         * lifted from the prose otherwise, because most models list their
         * sources inline. The structured form wins where both exist — a URL in
         * prose may be one the model invented.
         */
        const citations = result.citations?.length
          ? result.citations
          : urlsIn(result.text).map((url) => ({ url }));
        return { model, text: result.text, citations };
      }),
    );

    for (const [index, outcome] of settled.entries()) {
      const model = models[index]!;

      if (outcome.status === 'rejected') {
        failed += 1;
        const message = errorMessage(outcome.reason);
        await tx.execute(sql`
          insert into market_ai_prompt_runs
            (workspace_id, prompt_id, model, status, error)
          values (${opts.workspaceId}, ${prompt.id}, ${model}, 'failed', ${message})`);
        continue;
      }

      answered += 1;
      const reading = readAnswer(
        { model, text: outcome.value.text, citations: outcome.value.citations },
        brands,
      );
      readingsByModel.set(model, [...(readingsByModel.get(model) ?? []), reading]);

      const self = reading.self;
      await tx.execute(sql`
        insert into market_ai_prompt_runs
          (workspace_id, prompt_id, model, answer, cited_sources, brand_mentioned,
           mention_position, status)
        values (${opts.workspaceId}, ${prompt.id}, ${model},
                ${outcome.value.text},
                ${JSON.stringify(reading.sources.map((s) => ({ url: s.url, title: s.brand })))}::jsonb,
                ${self?.mentioned ?? false}, ${self?.position ?? null}, 'ok')`);
    }
  }

  /* ------------------------------------------------------------ snapshots */

  const selfName = brands.find((b) => b.isSelf)!.name;
  const capturedOn = today.toISOString().slice(0, 10);
  const snapshots: VisibilitySnapshot[] = [];
  const shifts: RunVisibilityResult['shifts'] = [];

  for (const [model, readings] of readingsByModel) {
    const snapshot = summarise(readings, selfName);
    snapshots.push(snapshot);

    // The previous snapshot, for deciding whether this is worth an event.
    const [previous] = await tx.execute<{
      share_of_voice: number; mention_rate: number; avg_position: number | null;
      citation_count: number;
    }>(sql`
      select share_of_voice, mention_rate, avg_position, citation_count
        from market_ai_visibility_snapshots
       where project_id = ${opts.projectId} and model = ${model}
         and captured_on < ${capturedOn}::date
       order by captured_on desc limit 1`);

    await tx.execute(sql`
      insert into market_ai_visibility_snapshots
        (workspace_id, project_id, captured_on, model, share_of_voice, mention_rate,
         avg_position, citation_count)
      values (${opts.workspaceId}, ${opts.projectId}, ${capturedOn}::date, ${model},
              ${snapshot.shareOfVoice}, ${snapshot.mentionRate},
              ${snapshot.avgPosition}, ${snapshot.citationCount})
      on conflict on constraint market_ai_visibility_snapshots_key do update
         set share_of_voice = excluded.share_of_voice,
             mention_rate = excluded.mention_rate,
             avg_position = excluded.avg_position,
             citation_count = excluded.citation_count,
             updated_at = now()`);

    const shift = isNotableShift(
      previous
        ? {
            model,
            shareOfVoice: previous.share_of_voice,
            mentionRate: previous.mention_rate,
            avgPosition: previous.avg_position,
            citationCount: previous.citation_count,
            promptsRun: 0,
          }
        : null,
      snapshot,
    );
    if (shift.notable) {
      shifts.push({
        model,
        reason: shift.reason,
        from: previous?.share_of_voice ?? 0,
        to: snapshot.shareOfVoice,
      });
    }
  }

  /*
   * `next_run_at` is advanced whatever happened.
   *
   * A prompt whose models all failed must not be retried on the next tick: at
   * forty credits a probe, a provider outage would otherwise drain a
   * workspace's balance in an afternoon.
   */
  if (prompts.length > 0) {
    await tx.execute(sql`
      update market_ai_prompts
         set next_run_at = case schedule
               when 'daily' then now() + interval '1 day'
               when 'monthly' then now() + interval '30 days'
               else now() + interval '7 days'
             end,
             updated_at = now()
       where id = any(${textArray(prompts.map((p) => p.id))}::uuid[])`);
  }

  return { probes: prompts.length, answered, failed, snapshots, problem: null, shifts, unavailable };
}

/**
 * Which prompts are due, claimed so two schedulers cannot both pay for one.
 *
 * The claim matters more here than anywhere else in Market: a duplicate rank
 * check wastes a credit, a duplicate visibility probe wastes forty across four
 * models.
 */
export async function claimDuePrompts(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<{ id: string; workspaceId: string; projectId: string }[]> {
  const rows = await tx.execute<{ id: string; workspace_id: string; project_id: string }>(sql`
    with claimed as (
      select id from market_ai_prompts
       where is_tracked and deleted_at is null
         and (next_run_at is null or next_run_at <= now())
       order by next_run_at nulls first
       limit ${opts.limit ?? 20}
       for update skip locked
    )
    -- Pushed out on claim, not on success: an outage must not become a
    -- retry loop that spends the balance.
    update market_ai_prompts p
       set next_run_at = now() + interval '1 hour', updated_at = now()
      from claimed
     where p.id = claimed.id
    returning p.id, p.workspace_id, p.project_id`);

  return rows.map((r) => ({ id: r.id, workspaceId: r.workspace_id, projectId: r.project_id }));
}

/**
 * The URLs the models cite, and whether they are yours.
 *
 * The most directly actionable view in 4B: a page that four models keep citing
 * is worth matching, and one of *your* pages that they cite is worth keeping
 * exactly as it is.
 */
export async function citedSources(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; sinceDays?: number; limit?: number },
): Promise<{ url: string; host: string; brand: string | null; citations: number; models: string[] }[]> {
  const rows = await tx.execute<{
    url: string; brand: string | null; citations: number; models: string[];
  }>(sql`
    select source->>'url' as url,
           source->>'title' as brand,
           count(*)::int as citations,
           array_agg(distinct r.model) as models
      from market_ai_prompt_runs r
      join market_ai_prompts p on p.id = r.prompt_id
      cross join lateral jsonb_array_elements(r.cited_sources) as source
     where p.project_id = ${opts.projectId}
       and r.status = 'ok'
       and r.created_at > now() - (${opts.sinceDays ?? 30} * interval '1 day')
     group by 1, 2
     order by citations desc
     limit ${opts.limit ?? 50}`);

  return rows.map((r) => ({
    url: r.url,
    host: hostOfUrl(r.url),
    brand: r.brand,
    citations: r.citations,
    models: r.models,
  }));
}

function hostOfUrl(url: string): string {
  try {
    return new URL(url).host.replace(/^www\./, '');
  } catch {
    return '';
  }
}

/**
 * URLs written into the answer.
 *
 * Trailing punctuation is stripped because a model writes "see acme.com." and
 * the full stop is a sentence, not part of the host. Deduplicated, so a source
 * repeated three times in one answer counts once.
 */
export function urlsIn(text: string): string[] {
  const found = text.matchAll(/https?:\/\/[^\s<>()\[\]"']+/gi);
  const seen = new Set<string>();
  for (const match of found) {
    seen.add(match[0].replace(/[.,;:!?)\]]+$/, ''));
  }
  return [...seen];
}

function errorMessage(reason: unknown): string {
  if (reason instanceof AiUnavailable) return `${reason.reason}: ${reason.message}`;
  return reason instanceof Error ? reason.message : 'The model failed.';
}
