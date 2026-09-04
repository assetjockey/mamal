/**
 * Autoblogging pipelines: a source, a schedule, and somewhere to put the result.
 *
 * The shape is `trigger → brief → draft → destination`, and every step is
 * recorded on a `content_runs` row so a customer can see what a pipeline did
 * and what it cost. Four things are load-bearing:
 *
 * **AI off must not break the pipeline.** With generation unavailable — a
 * lifetime plan, an instance kill switch, no credits — the run still produces
 * the *brief*: the trigger, the questions to answer, the topics to cover, a
 * proposed outline and a title. That is a commissioning brief a human can write
 * from, and it is the difference between "the feature is gone" and "the feature
 * needs you today". A run that only got that far completes as `completed` with
 * a note, not `failed`.
 *
 * **Nothing goes live without being asked to.** `auto_publish` is off by
 * default and a destination's `default_status` is `draft`; a pipeline that
 * publishes unreviewed generated prose to a live site is one bad prompt away
 * from an incident the customer hears about from a reader.
 *
 * **The claim moves the schedule, not the success.** Same rule as the rank
 * checks and the visibility probes: a pipeline whose generation fails must not
 * be retried on the next tick, because generation costs money.
 *
 * **One run per trigger.** A trend that stays hot for a week must not produce
 * seven near-identical articles, so a trigger that has already been written
 * about is skipped — and says so, rather than silently doing nothing.
 */
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { createDoc, saveBrief, saveDoc } from './content.ts';
import { worthWriting, type Shift } from './trends.ts';

export type PipelineRow = {
  id: string;
  workspaceId: string;
  projectId: string;
  name: string;
  source: string;
  sourceConfig: Record<string, unknown>;
  destinationId: string | null;
  autoPublish: boolean;
};

export type Trigger = {
  kind: 'trend' | 'keyword' | 'gsc_opportunity' | 'rss' | 'audit_issue';
  /** What to write about, in the customer's words. */
  subject: string;
  /** Why now — shown on the run so a person can judge the pipeline. */
  because: string;
  meta?: Record<string, unknown>;
};

export type RunOutcome = {
  runId: string;
  status: 'completed' | 'failed' | 'skipped';
  docId: string | null;
  /** True when the draft was written; false when only the brief exists. */
  drafted: boolean;
  published: boolean;
  note: string;
  creditsSpent: number;
};

/* --------------------------------------------------------------- triggers */

/**
 * What a pipeline should write about this run.
 *
 * Returns at most one trigger. A pipeline that fired on every rising term would
 * spend a month's credits in an afternoon, and the point of a schedule is that
 * it paces itself.
 */
export async function nextTrigger(
  tx: WorkspaceScopedDb,
  pipeline: PipelineRow,
): Promise<Trigger | null> {
  switch (pipeline.source) {
    case 'trend': {
      const watchId = pipeline.sourceConfig.watchId as string | undefined;
      const rows = await tx.execute<{
        keyword: string; geo: string; previous_value: number | null;
        current_value: number | null; delta_pct: number | null;
      }>(sql`
        select e.keyword, e.geo, e.previous_value, e.current_value, e.delta_pct
          from trend_events e
          join trend_watches w on w.id = e.watch_id
         where w.project_id = ${pipeline.projectId}
           ${watchId ? sql`and e.watch_id = ${watchId}` : sql``}
           and e.created_at > now() - interval '7 days'
         order by abs(e.delta_pct) desc nulls last
         limit 20`);

      for (const row of rows) {
        const shift: Shift = {
          keyword: row.keyword,
          geo: row.geo,
          previous: row.previous_value ?? 0,
          current: row.current_value ?? 0,
          deltaPct: row.delta_pct ?? 0,
          direction: (row.delta_pct ?? 0) > 0 ? 'rising' : 'falling',
          reason: '',
        };
        // A falling term is worth knowing and a poor thing to commission.
        if (!worthWriting(shift)) continue;

        return {
          kind: 'trend',
          subject: row.keyword,
          because:
            `Interest in “${row.keyword}” is up ${Math.round(row.delta_pct ?? 0)}% ` +
            `${row.geo ? `in ${row.geo.toUpperCase()}` : 'worldwide'}.`,
          meta: { geo: row.geo, current: row.current_value },
        };
      }
      return null;
    }

    case 'gsc_opportunity': {
      /*
       * The most defensible source in the set: a query the customer already
       * ranks 11th–20th for is demand they have already proven, and the brief
       * writes itself from their own Search Console rows.
       */
      const [row] = await tx.execute<{ query: string; score: number; evidence: Record<string, unknown> }>(sql`
        select query, score, evidence from seo_opportunities
         where project_id = ${pipeline.projectId}
           and status = 'open' and query is not null
           and kind in ('striking_distance', 'rising_query', 'content_decay')
         order by score desc limit 1`);
      if (!row) return null;

      return {
        kind: 'gsc_opportunity',
        subject: row.query,
        because: `You already show up for “${row.query}” — Search Console says so, and it is the strongest open opportunity.`,
        meta: row.evidence,
      };
    }

    case 'keyword': {
      const keywords = (pipeline.sourceConfig.keywords as string[] | undefined) ?? [];
      // Round-robin over the list, skipping anything already written about.
      for (const keyword of keywords) {
        if (!(await alreadyWritten(tx, pipeline.id, keyword))) {
          return { kind: 'keyword', subject: keyword, because: 'From this pipeline’s keyword list.' };
        }
      }
      return null;
    }

    default:
      return null;
  }
}

async function alreadyWritten(
  tx: WorkspaceScopedDb,
  pipelineId: string,
  subject: string,
): Promise<boolean> {
  const [row] = await tx.execute<{ n: number }>(sql`
    select count(*)::int as n from content_runs
     where pipeline_id = ${pipelineId}
       and status in ('completed', 'running')
       and trigger->>'subject' = ${subject}
       -- A quarter, not forever: a topic worth revisiting a year later is a
       -- different article, and blocking it permanently makes the pipeline
       -- go quiet for reasons nobody can see.
       and created_at > now() - interval '90 days'`);
  return (row?.n ?? 0) > 0;
}

/* ------------------------------------------------------------------- runs */

export async function runPipeline(
  tx: WorkspaceScopedDb,
  pipeline: PipelineRow,
  deps: ExecuteDeps,
  opts: { trigger?: Trigger } = {},
): Promise<RunOutcome> {
  const trigger = opts.trigger ?? (await nextTrigger(tx, pipeline));

  if (!trigger) {
    const runId = await openRun(tx, pipeline, null);
    await closeRun(tx, runId, 'skipped', {
      note: 'Nothing new to write about — no trigger cleared this pipeline’s bar.',
    });
    return {
      runId, status: 'skipped', docId: null, drafted: false, published: false,
      note: 'Nothing new to write about — no trigger cleared this pipeline’s bar.',
      creditsSpent: 0,
    };
  }

  if (await alreadyWritten(tx, pipeline.id, trigger.subject)) {
    const runId = await openRun(tx, pipeline, trigger);
    const note = `“${trigger.subject}” was already written about in the last 90 days.`;
    await closeRun(tx, runId, 'skipped', { note });
    return { runId, status: 'skipped', docId: null, drafted: false, published: false, note, creditsSpent: 0 };
  }

  const runId = await openRun(tx, pipeline, trigger);

  /* -- 1. the doc and its brief, both free ------------------------------- */

  const title = workingTitle(trigger);
  let docId: string;
  try {
    docId = await createDoc(tx, {
      workspaceId: pipeline.workspaceId,
      projectId: pipeline.projectId,
      title,
      targetKeywords: [trigger.subject],
    });
  } catch (err) {
    const note = err instanceof Error ? err.message : 'Could not create the document.';
    await closeRun(tx, runId, 'failed', { note });
    return { runId, status: 'failed', docId: null, drafted: false, published: false, note, creditsSpent: 0 };
  }

  const questions = await questionsFor(tx, pipeline, trigger.subject);
  await saveBrief(tx, {
    workspaceId: pipeline.workspaceId,
    docId,
    entities: [trigger.subject],
    questions,
    targetWordCount: null,
  });
  await tx.execute(sql`update content_runs set doc_id = ${docId} where id = ${runId}`);

  /* -- 2. the draft, which is the only part that costs ------------------- */

  let drafted = false;
  let creditsSpent = 0;
  let note = '';

  try {
    const result = await execute(
      tx,
      {
        featureKey: 'market.ai_blog',
        system:
          'You write for a company blog. Use plain English, short sentences and concrete ' +
          'detail. Return Markdown with one H1 and H2 sections. Do not invent statistics, ' +
          'quotes, prices or product capabilities — if you do not know something, leave it out.',
        prompt: draftPrompt(trigger, title, questions),
        modality: 'text',
      },
      { workspaceId: pipeline.workspaceId, jobId: runId },
      deps,
    );

    if (result.ok && result.text) {
      await saveDoc(tx, {
        workspaceId: pipeline.workspaceId,
        projectId: pipeline.projectId,
        docId,
        body: result.text,
        status: 'in_review',
      });
      drafted = true;
      note = 'Drafted and waiting for review.';
    } else {
      note = `The brief is ready; the draft was not written: ${result.error ?? 'the model returned nothing'}.`;
    }
  } catch (err) {
    /*
     * The whole point of the non-AI path.
     *
     * A lifetime plan, a flipped kill switch or an empty credit balance leaves
     * a real deliverable — the trigger, the questions, the topic — rather than
     * a failed run and an empty screen.
     */
    if (err instanceof AiUnavailable) {
      note =
        `The brief is ready to write from. The draft was not generated: ${err.message}`;
    } else {
      const message = err instanceof Error ? err.message : String(err);
      await closeRun(tx, runId, 'failed', { note: message, docId });
      return { runId, status: 'failed', docId, drafted: false, published: false, note: message, creditsSpent: 0 };
    }
  }

  /*
   * `delta` is negative for a spend, and only spends are counted — a release
   * writes a positive row, and netting them would report a failed generation
   * as having cost nothing *and* a successful one as costing less than it did.
   * `execute` keys its capture on the job id, which is this run.
   */
  const [spend] = await tx.execute<{ credits: number }>(sql`
    select coalesce(-sum(delta), 0)::int as credits from credit_entries
     where idempotency_key like ${`${runId}:%`} and delta < 0`);
  creditsSpent = spend?.credits ?? 0;

  /* -- 3. publishing, only if asked ------------------------------------- */

  let published = false;
  if (drafted && pipeline.autoPublish && pipeline.destinationId) {
    // Marked for the publisher rather than pushed from here: the HTTP call
    // belongs outside the transaction that just wrote the draft.
    await saveDoc(tx, {
      workspaceId: pipeline.workspaceId,
      projectId: pipeline.projectId,
      docId,
      status: 'approved',
    });
    published = true;
    note = 'Drafted and queued to publish.';
  }

  await closeRun(tx, runId, 'completed', { note, docId, creditsSpent });
  return { runId, status: 'completed', docId, drafted, published, note, creditsSpent };
}

/**
 * Questions the draft should answer, from the customer's own search data.
 *
 * Free, and better than anything a model would guess: these are queries the
 * workspace actually receives impressions for.
 */
async function questionsFor(
  tx: WorkspaceScopedDb,
  pipeline: PipelineRow,
  subject: string,
): Promise<string[]> {
  const rows = await tx.execute<{ query: string }>(sql`
    select query from market_search_performance
     where workspace_id = ${pipeline.workspaceId}
       and captured_on > current_date - interval '90 days'
       and query ilike ${'%' + subject + '%'}
     group by query
     order by sum(impressions) desc
     limit 10`);
  return rows.map((r) => r.query);
}

function workingTitle(trigger: Trigger): string {
  const subject = trigger.subject.trim();
  const titled = subject.charAt(0).toUpperCase() + subject.slice(1);
  // A working title, and it says so — a generated headline the writer has not
  // seen should never look like a decision somebody made.
  return titled.length >= 20 ? titled : `${titled}: what to know`;
}

function draftPrompt(trigger: Trigger, title: string, questions: string[]): string {
  return [
    `Write a blog post titled “${title}”.`,
    `Context: ${trigger.because}`,
    questions.length > 0
      ? `Readers are searching for these, so answer them:\n${questions.map((q) => `- ${q}`).join('\n')}`
      : 'Cover what somebody researching this would need to decide.',
    'Around 900 words. Open by answering the question directly rather than with background.',
  ].join('\n\n');
}

async function openRun(
  tx: WorkspaceScopedDb,
  pipeline: PipelineRow,
  trigger: Trigger | null,
): Promise<string> {
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into content_runs (workspace_id, pipeline_id, status, trigger, started_at)
    values (${pipeline.workspaceId}, ${pipeline.id}, 'running',
            ${JSON.stringify(trigger ?? {})}::jsonb, now())
    returning id`);
  return row!.id;
}

async function closeRun(
  tx: WorkspaceScopedDb,
  runId: string,
  status: 'completed' | 'failed' | 'skipped',
  opts: { note: string; docId?: string; creditsSpent?: number },
): Promise<void> {
  await tx.execute(sql`
    update content_runs
       set status = ${status},
           error = ${status === 'failed' ? opts.note : null},
           doc_id = ${opts.docId ?? null},
           credits_spent = ${opts.creditsSpent ?? 0},
           finished_at = now(),
           updated_at = now()
     where id = ${runId}`);
}

/**
 * Due pipelines, claimed so two schedulers cannot both pay for one article.
 *
 * `next_run_at` is pushed out on claim rather than on success — the same rule
 * as every other paid job in Market.
 */
export async function claimDuePipelines(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<PipelineRow[]> {
  const rows = await tx.execute<{
    id: string; workspace_id: string; project_id: string; name: string;
    source: string; source_config: Record<string, unknown>;
    destination_id: string | null; auto_publish: boolean; schedule: string;
  }>(sql`
    with claimed as (
      select id from content_pipelines
       where is_active and (next_run_at is null or next_run_at <= now())
       order by next_run_at nulls first
       limit ${opts.limit ?? 20}
       for update skip locked
    )
    update content_pipelines p
       set next_run_at = case p.schedule
             when 'daily' then now() + interval '1 day'
             when 'monthly' then now() + interval '30 days'
             else now() + interval '7 days'
           end,
           updated_at = now()
      from claimed
     where p.id = claimed.id
    returning p.id, p.workspace_id, p.project_id, p.name, p.source, p.source_config,
              p.destination_id, p.auto_publish, p.schedule`);

  return rows.map((r) => ({
    id: r.id,
    workspaceId: r.workspace_id,
    projectId: r.project_id,
    name: r.name,
    source: r.source,
    sourceConfig: r.source_config,
    destinationId: r.destination_id,
    autoPublish: r.auto_publish,
  }));
}

/** Documents a pipeline approved for publishing, waiting on the HTTP call. */
export async function pendingPublishes(
  tx: WorkspaceScopedDb,
  opts: { projectId?: string; limit?: number } = {},
): Promise<{ docId: string; destinationId: string; title: string; body: string; slug: string | null }[]> {
  const rows = await tx.execute<{
    doc_id: string; destination_id: string; title: string; body: string; slug: string | null;
  }>(sql`
    select distinct on (d.id)
           d.id as doc_id, p.destination_id, d.title, d.body, d.slug
      from content_docs d
      join content_runs r on r.doc_id = d.id
      join content_pipelines p on p.id = r.pipeline_id
     where d.status = 'approved' and d.deleted_at is null
       and p.destination_id is not null
       ${opts.projectId ? sql`and d.project_id = ${opts.projectId}` : sql``}
     order by d.id, r.created_at desc
     limit ${opts.limit ?? 20}`);

  return rows.map((r) => ({
    docId: r.doc_id,
    destinationId: r.destination_id,
    title: r.title,
    body: r.body,
    slug: r.slug,
  }));
}

