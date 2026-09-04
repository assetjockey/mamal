/**
 * Content pipelines against a real database.
 *
 * The behaviours worth a database are the ones about money and repetition: that
 * AI being unavailable still leaves the customer something useful, that a hot
 * trend does not become seven near-identical articles, and that nothing reaches
 * a live site without being asked to.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { claimDuePipelines, nextTrigger, pendingPublishes, runPipeline } from '../pipelines.ts';
import { getDoc, listDocs } from '../content.ts';

const db = unsafeUnscopedDb();
const tag = `pipe${Date.now()}`;

let ws = '';
let project = '';
let pipelineId = '';
let watchId = '';

/** The provider layer, replaced; `execute` itself still runs for real. */
function drivers(answer: string | Error) {
  return {
    decrypt: (value: string) => value,
    driverFor: () => ({
      name: 'anthropic',
      async generate() {
        if (answer instanceof Error) throw answer;
        return {
          ok: true, text: answer, units: 1, inputTokens: 200, outputTokens: 900,
          vendorCostMicros: 3000, latencyMs: 12,
        };
      },
    }),
  } as never;
}

const DRAFT = [
  '# Widget racks: what to know',
  '',
  'A widget rack holds widgets. Here is how to choose one.',
  '',
  '## Price',
  'They start at £19.',
  '',
  '## Fit',
  'Measure the shelf first.',
].join('\n');

const pipeline = () => ({
  id: pipelineId,
  workspaceId: ws,
  projectId: project,
  name: 'Trends',
  source: 'trend',
  sourceConfig: {},
  destinationId: null as string | null,
  autoPublish: false,
});

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Pipe') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Pipe', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'market_pro'`);
    await tx.execute(sql`
      insert into credit_buckets (workspace_id, source, amount, remaining)
      values (${ws}, 'grant', 100000, 100000)`);
    await tx.execute(sql`
      insert into ai_credentials (scope, scope_id, provider_key, encrypted_key, key_hint, is_active)
      values ('workspace', ${ws}, 'anthropic', 'test-key', '••••test', true)`);
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from ai_credentials where scope = 'workspace' and scope_id = ${ws}`);
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from content_runs where workspace_id = ${ws}`);
    await tx.execute(sql`delete from content_docs where workspace_id = ${ws}`);
    await tx.execute(sql`delete from content_pipelines where workspace_id = ${ws}`);
    await tx.execute(sql`delete from trend_watches where workspace_id = ${ws}`);
    await tx.execute(sql`delete from publish_destinations where workspace_id = ${ws}`);

    const [w] = await tx.execute<{ id: string }>(sql`
      insert into trend_watches (workspace_id, project_id, name, keywords)
      values (${ws}, ${project}, 'Racks', array['widget racks']) returning id`);
    watchId = w!.id;

    await tx.execute(sql`
      insert into trend_events
        (workspace_id, watch_id, keyword, geo, previous_value, current_value, delta_pct)
      values (${ws}, ${watchId}, 'widget racks', 'US', 20, 70, 250)`);

    const [p] = await tx.execute<{ id: string }>(sql`
      insert into content_pipelines
        (workspace_id, project_id, name, source, schedule, is_active)
      values (${ws}, ${project}, 'Trends', 'trend', 'weekly', true) returning id`);
    pipelineId = p!.id;
  }, { db });
});

describe('choosing what to write about', () => {
  it('picks a rising trend and says why', async () => {
    const trigger = await withWorkspace(ws, (tx) => nextTrigger(tx, pipeline()), { db });
    expect(trigger).toMatchObject({ kind: 'trend', subject: 'widget racks' });
    // The "because" is shown on the run, so a person can judge the pipeline
    // rather than trusting it.
    expect(trigger!.because).toMatch(/up 250%/);
  });

  it('will not commission an article about a term that is falling', async () => {
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update trend_events set delta_pct = -60, current_value = 8 where watch_id = ${watchId}`), { db });

    const trigger = await withWorkspace(ws, (tx) => nextTrigger(tx, pipeline()), { db });
    // Worth knowing, poor thing to write about.
    expect(trigger).toBeNull();
  });
});

describe('running', () => {
  it('drafts, and leaves it in review rather than live', async () => {
    const result = await withWorkspace(
      ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db },
    );

    expect(result.status).toBe('completed');
    expect(result.drafted).toBe(true);
    expect(result.published).toBe(false);

    const docs = await withWorkspace(ws, (tx) => listDocs(tx, { projectId: project }), { db });
    expect(docs).toHaveLength(1);
    // Never `published`: nothing generated reaches a reader without a person.
    expect(docs[0]!.status).toBe('in_review');
    expect(docs[0]!.wordCount).toBeGreaterThan(10);
    // The score is computed on save, so the list is sortable immediately.
    expect(docs[0]!.seoScore).not.toBeNull();
  });

  it('charges for the draft and records what it cost', async () => {
    const result = await withWorkspace(
      ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db },
    );
    expect(result.creditsSpent).toBeGreaterThan(0);

    const [run] = await withWorkspace(ws, (tx) => tx.execute<{ credits_spent: number }>(sql`
      select credits_spent from content_runs where id = ${result.runId}`), { db });
    expect(run!.credits_spent).toBe(result.creditsSpent);
  });

  it('leaves a usable brief when AI is switched off', async () => {
    // The instance kill switch: the same thing a lifetime plan sees.
    await asPlatformAdmin((tx) => tx.execute(sql`
      update instance_settings set ai_master_enabled = false`), { db });

    try {
      const result = await withWorkspace(
        ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db },
      );

      /*
       * The whole non-AI promise in one assertion: a completed run, a real
       * document, the trigger recorded, the questions to answer — everything a
       * writer needs except the prose. Not a failure and not an empty screen.
       */
      expect(result.status).toBe('completed');
      expect(result.drafted).toBe(false);
      expect(result.docId).not.toBeNull();
      expect(result.creditsSpent).toBe(0);
      expect(result.note).toMatch(/brief is ready/i);

      const loaded = await withWorkspace(
        ws, (tx) => getDoc(tx, { projectId: project, docId: result.docId! }), { db },
      );
      expect(loaded!.doc.status).toBe('draft');
      expect(loaded!.brief!.entities).toContain('widget racks');
    } finally {
      await asPlatformAdmin((tx) => tx.execute(sql`
        update instance_settings set ai_master_enabled = true`), { db });
    }
  });

  it('does not write about the same trend twice', async () => {
    await withWorkspace(ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db });
    const second = await withWorkspace(
      ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db },
    );

    // A trend stays hot for a week; seven near-identical articles is how an
    // autoblog destroys a site rather than growing it.
    expect(second.status).toBe('skipped');
    expect(second.note).toMatch(/already written about/i);

    const docs = await withWorkspace(ws, (tx) => listDocs(tx, { projectId: project }), { db });
    expect(docs).toHaveLength(1);
  });

  it('records a skip rather than doing nothing silently', async () => {
    await withWorkspace(ws, (tx) => tx.execute(sql`delete from trend_events where watch_id = ${watchId}`), { db });
    const result = await withWorkspace(
      ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db },
    );

    expect(result.status).toBe('skipped');
    const [run] = await withWorkspace(ws, (tx) => tx.execute<{ status: string; error: string | null }>(sql`
      select status, error from content_runs where id = ${result.runId}`), { db });
    // A skip is not an error, so nothing shows red for a quiet week.
    expect(run).toMatchObject({ status: 'skipped', error: null });
  });

  it('fails the run, not the whole job, when the provider breaks', async () => {
    const result = await withWorkspace(
      ws, (tx) => runPipeline(tx, pipeline(), drivers(new Error('upstream 500'))), { db },
    );
    expect(result.status).toBe('failed');
    expect(result.note).toMatch(/upstream 500/);
    // The hold is released by `execute`, so a failed generation costs nothing.
    expect(result.creditsSpent).toBe(0);
  });
});

describe('publishing', () => {
  it('queues only what a pipeline was told to publish', async () => {
    const destinationId = await withWorkspace(ws, async (tx) => {
      const [d] = await tx.execute<{ id: string }>(sql`
        insert into publish_destinations (workspace_id, project_id, kind, name, config)
        values (${ws}, ${project}, 'webhook', 'Our site', '{"url":"https://example.test/hook"}'::jsonb)
        returning id`);
      await tx.execute(sql`
        update content_pipelines set auto_publish = true, destination_id = ${d!.id}
         where id = ${pipelineId}`);
      return d!.id;
    }, { db });

    const result = await withWorkspace(
      ws,
      (tx) => runPipeline(tx, { ...pipeline(), autoPublish: true, destinationId }, drivers(DRAFT)),
      { db },
    );
    expect(result.published).toBe(true);

    const pending = await withWorkspace(ws, (tx) => pendingPublishes(tx, { projectId: project }), { db });
    expect(pending).toHaveLength(1);
    expect(pending[0]).toMatchObject({ docId: result.docId, destinationId });
  });

  it('queues nothing when auto-publish is off', async () => {
    await withWorkspace(ws, (tx) => runPipeline(tx, pipeline(), drivers(DRAFT)), { db });
    const pending = await withWorkspace(ws, (tx) => pendingPublishes(tx, { projectId: project }), { db });
    expect(pending).toEqual([]);
  });
});

describe('scheduling', () => {
  it('claims a pipeline once and pushes its next run out', async () => {
    const first = await withWorkspace(ws, (tx) => claimDuePipelines(tx), { db });
    expect(first.map((p) => p.id)).toContain(pipelineId);

    // Moved on claim, so a generation that fails is not retried on the next
    // tick at full price.
    const second = await withWorkspace(ws, (tx) => claimDuePipelines(tx), { db });
    expect(second.map((p) => p.id)).not.toContain(pipelineId);
  });
});
