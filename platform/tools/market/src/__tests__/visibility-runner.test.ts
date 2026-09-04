/**
 * The visibility probe runner, against a real database.
 *
 * The unit tests cover the parsing; what needs a database here is the
 * behaviour around it — that one provider failing leaves the others intact,
 * that a run is not retried into a credit hole, and that "no self brand" is
 * refused before anything is spent rather than after.
 *
 * `execute` is injected rather than mocked at the module level: `vi.mock` on a
 * workspace package resolves to a different physical path under pnpm and
 * silently does nothing, which cost an afternoon in Phase 2.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  ASSISTANTS, claimDuePrompts, citedSources, runVisibilityProbes, urlsIn,
} from '../visibility-runner.ts';

const db = unsafeUnscopedDb();
const tag = `vis${Date.now()}`;
const TODAY = new Date('2026-03-20T09:00:00Z');

let ws = '';
let project = '';

/**
 * Stands in for `packages/ai`'s driver layer.
 *
 * `execute` still runs for real — entitlements, credit holds, the kill switch —
 * and only the provider call is replaced, so the tests exercise the gate rather
 * than skipping past it.
 */
function drivers(perAssistant: Record<string, string | Error>) {
  // Keyed by assistant in the tests because that is how the product talks
  // about it; mapped to the provider `driverFor` actually receives.
  const byProvider = new Map(
    Object.entries(perAssistant).map(([assistant, answer]) => [ASSISTANTS[assistant], answer]),
  );
  return {
    decrypt: (value: string) => value,
    driverFor: (provider: string) => ({
      name: provider,
      async generate(request: { prompt: string }) {
        const answer = byProvider.get(provider);
        if (answer instanceof Error) throw answer;
        if (answer === undefined) throw new Error(`no fixture for ${provider}`);
        void request;
        return {
          ok: true, text: answer, units: 1, inputTokens: 10, outputTokens: 50,
          vendorCostMicros: 200, latencyMs: 5,
        };
      },
    }),
  } as never;
}

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Vis') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Vis', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'market_pro'`);
    // Probes cost 40 credits a model; without a balance every run is refused
    // for the right reason and the wrong test.
    await tx.execute(sql`
      insert into credit_buckets (workspace_id, source, amount, remaining)
      values (${ws}, 'grant', 100000, 100000)`);

    /*
     * `execute` refuses without a key, and the test's `decrypt` is the identity
     * so the stored value is a marker rather than ciphertext.
     *
     * Workspace scope, not instance: an instance row is visible to every
     * workspace on the box, survives this suite's cleanup, and would leave a
     * shared dev database believing OpenAI is configured with the string
     * "test-openai". Workspace rows cascade with the workspace.
     */
    for (const provider of ['anthropic', 'openai', 'google']) {
      await tx.execute(sql`
        insert into ai_credentials (scope, scope_id, provider_key, encrypted_key, key_hint, is_active)
        values ('workspace', ${ws}, ${provider}, ${`test-${provider}`}, '••••test', true)`);
    }
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    // `ai_credentials.scope_id` is a plain uuid, not a foreign key — it holds a
    // workspace id or nothing, depending on `scope` — so dropping the workspace
    // strands the rows rather than cascading them. Deleted explicitly.
    await tx.execute(sql`delete from ai_credentials where scope = 'workspace' and scope_id = ${ws}`);
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from market_ai_prompt_runs where workspace_id = ${ws}`);
    await tx.execute(sql`delete from market_ai_visibility_snapshots where workspace_id = ${ws}`);
    await tx.execute(sql`delete from market_ai_prompts where workspace_id = ${ws}`);
    await tx.execute(sql`delete from market_ai_competitors where workspace_id = ${ws}`);

    await tx.execute(sql`
      insert into market_ai_competitors (workspace_id, project_id, brand, domain, is_self)
      values (${ws}, ${project}, 'Acme', 'acme.com', true),
             (${ws}, ${project}, 'Widgetly', 'widgetly.io', false)`);
    await tx.execute(sql`
      insert into market_ai_prompts (workspace_id, project_id, prompt, schedule)
      values (${ws}, ${project}, 'What is the best widget for a small team?', 'weekly')`);
  }, { db });
});

const run = (perModel: Record<string, string | Error>, models = ['claude', 'chatgpt']) =>
  withWorkspace(
    ws,
    (tx) =>
      runVisibilityProbes(
        tx,
        { workspaceId: ws, projectId: project, models, today: TODAY },
        drivers(perModel),
      ),
    { db },
  );

describe('running probes', () => {
  it('reads every model’s answer and stores it as evidence', async () => {
    const result = await run({
      claude: 'Acme is the strongest option. Widgetly is cheaper. See https://acme.com/pricing',
      chatgpt: 'Widgetly, mostly.',
    });

    expect(result.problem).toBeNull();
    expect(result.answered).toBe(2);
    expect(result.failed).toBe(0);

    await withWorkspace(ws, async (tx) => {
      const runs = await tx.execute<{
        model: string; brand_mentioned: boolean; mention_position: number | null; answer: string;
      }>(sql`
        select model, brand_mentioned, mention_position, answer
          from market_ai_prompt_runs where workspace_id = ${ws} order by model`);

      const claude = runs.find((r) => r.model === 'claude')!;
      expect(claude.brand_mentioned).toBe(true);
      expect(claude.mention_position).toBe(1);
      // "You are not mentioned" is unactionable without seeing who was.
      expect(claude.answer).toContain('Widgetly is cheaper');

      const chatgpt = runs.find((r) => r.model === 'chatgpt')!;
      expect(chatgpt.brand_mentioned).toBe(false);
      expect(chatgpt.mention_position).toBeNull();
    }, { db });
  });

  it('keeps the other models when one fails', async () => {
    /*
     * The whole point is four models side by side. Losing all of them because
     * one timed out is the failure `open-seo` warned about — so each is settled
     * independently and the failure is stored as a failed run.
     */
    const result = await run({
      claude: 'Acme leads.',
      chatgpt: new Error('upstream timeout'),
    });

    expect(result.answered).toBe(1);
    expect(result.failed).toBe(1);
    expect(result.snapshots.map((s) => s.model)).toEqual(['claude']);

    await withWorkspace(ws, async (tx) => {
      const [failedRun] = await tx.execute<{ model: string; status: string; error: string }>(sql`
        select model, status, error from market_ai_prompt_runs
         where workspace_id = ${ws} and status = 'failed'`);
      // Which provider, and why — otherwise the gap in the chart is a mystery.
      expect(failedRun).toMatchObject({ model: 'chatgpt', status: 'failed' });
      expect(failedRun!.error).toMatch(/timeout/);
    }, { db });
  });

  it('writes a snapshot per model', async () => {
    await run({ claude: 'Acme and Widgetly.', chatgpt: 'Widgetly only.' });

    await withWorkspace(ws, async (tx) => {
      const snapshots = await tx.execute<{ model: string; mention_rate: number; share_of_voice: number }>(sql`
        select model, mention_rate, share_of_voice from market_ai_visibility_snapshots
         where project_id = ${project} order by model`);
      expect(snapshots).toHaveLength(2);
      expect(snapshots.find((s) => s.model === 'claude')!.mention_rate).toBe(1);
      expect(snapshots.find((s) => s.model === 'chatgpt')!.mention_rate).toBe(0);
    }, { db });
  });

  it('re-running a day corrects the snapshot rather than duplicating it', async () => {
    await run({ claude: 'Widgetly only.', chatgpt: 'Widgetly only.' });
    await run({ claude: 'Acme leads.', chatgpt: 'Acme leads.' });

    await withWorkspace(ws, async (tx) => {
      const snapshots = await tx.execute<{ mention_rate: number }>(sql`
        select mention_rate from market_ai_visibility_snapshots
         where project_id = ${project} and model = 'claude' and captured_on = '2026-03-20'`);
      expect(snapshots).toHaveLength(1);
      expect(snapshots[0]!.mention_rate).toBe(1);
    }, { db });
  });

  it('reports a shift worth telling somebody about', async () => {
    await withWorkspace(ws, async (tx) => {
      // Yesterday: named nowhere.
      await tx.execute(sql`
        insert into market_ai_visibility_snapshots
          (workspace_id, project_id, captured_on, model, share_of_voice, mention_rate, citation_count)
        values (${ws}, ${project}, '2026-03-13'::date, 'claude', 0, 0, 0)`);
    }, { db });

    const result = await run({ claude: 'Acme leads.' }, ['claude']);
    expect(result.shifts).toHaveLength(1);
    expect(result.shifts[0]!.reason).toMatch(/started naming you/i);
  });

  it('says nothing about ordinary variation', async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`
        insert into market_ai_visibility_snapshots
          (workspace_id, project_id, captured_on, model, share_of_voice, mention_rate, citation_count)
        values (${ws}, ${project}, '2026-03-13'::date, 'claude', 0.5, 1, 0)`);
    }, { db });

    // Same outcome, same numbers — an alert here would train people to mute it.
    const result = await run({ claude: 'Acme and Widgetly.' }, ['claude']);
    expect(result.shifts).toEqual([]);
  });
});

describe('assistants without a model', () => {
  it('reports the gap rather than leaving a blank column', async () => {
    /*
     * No Perplexity provider is seeded. Silently dropping it reads as
     * "Perplexity never mentions us", which is a different and much worse
     * claim than "we never asked it".
     */
    const result = await run({ claude: 'Acme leads.' }, ['claude', 'perplexity']);

    expect(result.answered).toBe(1);
    expect(result.unavailable).toHaveLength(1);
    expect(result.unavailable[0]!.assistant).toBe('perplexity');
    expect(result.unavailable[0]!.reason).toMatch(/no enabled perplexity model/i);
  });

  it('does not abort the other models on an unknown name', async () => {
    // `execute` casts modelId to uuid, and in Postgres a cast error aborts the
    // whole transaction — taking the working models' results with it.
    const result = await run({ claude: 'Acme leads.' }, ['claude', 'nonesuch']);
    expect(result.answered).toBe(1);
    expect(result.unavailable.map((u) => u.assistant)).toEqual(['nonesuch']);
  });
});

describe('the tracked set', () => {
  it('refuses before spending anything when no brand is ours', async () => {
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update market_ai_competitors set is_self = false where project_id = ${project}`), { db });

    const result = await run({ claude: 'Acme leads.' }, ['claude']);
    // Forty credits a probe: finding this out afterwards is expensive as well
    // as useless.
    expect(result.problem).toMatch(/nothing to measure/);
    expect(result.answered).toBe(0);

    await withWorkspace(ws, async (tx) => {
      const [n] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from market_ai_prompt_runs where workspace_id = ${ws}`);
      expect(n!.n).toBe(0);
    }, { db });
  });

  it('refuses when several brands are ours', async () => {
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update market_ai_competitors set is_self = true where project_id = ${project}`), { db });
    const result = await run({ claude: 'Acme leads.' }, ['claude']);
    expect(result.problem).toMatch(/exactly one/);
  });
});

describe('scheduling', () => {
  it('pushes the next run out even when every model failed', async () => {
    /*
     * At forty credits a probe, a provider outage retried on every tick would
     * drain a workspace's balance in an afternoon.
     */
    await run({ claude: new Error('down'), chatgpt: new Error('down') });

    await withWorkspace(ws, async (tx) => {
      const [prompt] = await tx.execute<{ next_run_at: string | null }>(sql`
        select next_run_at from market_ai_prompts where project_id = ${project}`);
      expect(prompt!.next_run_at).not.toBeNull();
      expect(new Date(prompt!.next_run_at!).getTime()).toBeGreaterThan(Date.now());
    }, { db });
  });

  it('claims a due prompt once', async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`
        update market_ai_prompts set next_run_at = null where project_id = ${project}`);

      const first = await claimDuePrompts(tx);
      expect(first.length).toBeGreaterThan(0);

      // Pushed out on claim, so an outage does not become a retry loop.
      const second = await claimDuePrompts(tx);
      expect(second.map((p) => p.id)).not.toContain(first[0]!.id);
    }, { db });
  });
});

describe('cited sources', () => {
  it('ranks the URLs the models keep pointing at', async () => {
    await run({
      claude: 'Acme is best, see https://acme.com/guide and https://review.example/widgets',
      chatgpt: 'Widgetly, per https://review.example/widgets',
    });

    const sources = await withWorkspace(
      ws,
      (tx) => citedSources(tx, { projectId: project }),
      { db },
    );

    const shared = sources.find((s) => s.url.includes('review.example'));
    expect(shared, 'a source both models cite is the most actionable row').toBeDefined();
    expect(shared!.citations).toBe(2);
    expect(shared!.models.sort()).toEqual(['chatgpt', 'claude']);
    // Ours versus theirs is the point of the view.
    expect(sources.find((s) => s.url.includes('acme.com'))!.brand).toBe('Acme');
    expect(shared!.brand).toBeNull();
  });
});

describe('lifting URLs out of prose', () => {
  it('strips the punctuation that ends a sentence', () => {
    // A model writes "see acme.com." and the full stop is grammar, not a path.
    expect(urlsIn('Try https://acme.com/pricing.')).toEqual(['https://acme.com/pricing']);
    expect(urlsIn('Sources: https://a.test/x, https://b.test/y).')).toEqual([
      'https://a.test/x', 'https://b.test/y',
    ]);
  });

  it('counts a repeated source once', () => {
    expect(urlsIn('https://a.test/x and again https://a.test/x')).toEqual(['https://a.test/x']);
  });

  it('finds nothing in prose without links', () => {
    expect(urlsIn('No sources here.')).toEqual([]);
  });
});
