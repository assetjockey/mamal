/**
 * The generation lifecycle, against a real database.
 *
 * Everything here is about money and durability: a video that takes ten
 * minutes must not hold a worker, must survive that worker being killed, and
 * must not cost anything if it never arrives. The sources get all three wrong —
 * `magicads` debits on dispatch, so a failed generation eats the customer's
 * credits.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { balance } from '@mamal/credits';
import {
  MAX_POLLS, claimPollable, pollCreative, submitCreative, type ProviderStatus,
} from '../creatives.ts';
import { generateCopy, parseVariants, saveBrand } from '../ads.ts';
import { AD_PLATFORMS } from '../ad-platforms.ts';
import { MarketNotAllowed } from '../service.ts';

const db = unsafeUnscopedDb();
const tag = `crt${Date.now()}`;

let ws = '';
let project = '';
/**
 * A real row, because `ad_creatives.asset_id` is a foreign key — a store
 * implementation returning an id that does not exist would abort the poll
 * rather than leave a dangling reference.
 */
let ASSET_ID = '';

/** The provider layer; `execute` itself still runs for real. */
function drivers(answer: {
  text?: string;
  url?: string;
  externalTaskId?: string;
  units?: number;
  fail?: Error;
}) {
  return {
    decrypt: (v: string) => v,
    driverFor: () => ({
      name: 'anthropic',
      async generate() {
        if (answer.fail) throw answer.fail;
        return {
          ok: true,
          text: answer.text,
          url: answer.url,
          externalTaskId: answer.externalTaskId,
          units: answer.units ?? 1,
          inputTokens: 50, outputTokens: 200,
          vendorCostMicros: 1000, latencyMs: 8,
        };
      },
    }),
  } as never;
}

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Crt') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Crt', ${u!.id})
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
      values ('workspace', ${ws}, 'anthropic', 'k', '••••test', true),
             ('workspace', ${ws}, 'openai', 'k', '••••test', true),
             ('workspace', ${ws}, 'google', 'k', '••••test', true)`);

    const [asset] = await tx.execute<{ id: string }>(sql`
      insert into assets (workspace_id, project_id, kind, storage_key, filename, mime_type)
      values (${ws}, ${project}, 'creative', ${`${tag}/v.mp4`}, 'v.mp4', 'video/mp4')
      returning id`);
    ASSET_ID = asset!.id;
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
    await tx.execute(sql`delete from ad_creatives where workspace_id = ${ws}`);
    await tx.execute(sql`delete from ad_copies where workspace_id = ${ws}`);
    await tx.execute(sql`delete from market_brands where workspace_id = ${ws}`);
  }, { db });
});

const submit = (
  input: Partial<Parameters<typeof submitCreative>[1]> = {},
  deps = drivers({ url: 'https://cdn.test/i.png' }),
) =>
  withWorkspace(
    ws,
    (tx) =>
      submitCreative(
        tx,
        { workspaceId: ws, projectId: project, type: 'image', prompt: 'a widget rack', ...input },
        { ...(deps as object), store: async () => null as never } as never,
      ),
    { db },
  );

describe('submitting', () => {
  it('completes straight away when the provider answers with a URL', async () => {
    const result = await submit();
    expect(result.status).toBe('completed');
    expect(result.creditsSpent).toBeGreaterThan(0);
  });

  it('stores the job and returns, rather than waiting minutes', async () => {
    const result = await submit(
      { type: 'video', durationSeconds: 8 },
      drivers({ externalTaskId: 'job-77' }),
    );

    expect(result.status).toBe('polling');

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{
      status: string; provider_job_id: string; next_poll_at: string;
    }>(sql`select status, provider_job_id, next_poll_at from ad_creatives where id = ${result.creativeId}`), { db });

    /*
     * The generation lives in a row, not a process. This is what makes it
     * survive a `kill -9`: a worker that dies mid-flight loses only the poll it
     * was about to make.
     */
    expect(row).toMatchObject({ status: 'polling', provider_job_id: 'job-77' });
    expect(row!.next_poll_at).not.toBeNull();
  });

  it('refuses a canvas the platform does not take', async () => {
    // A 728×90 leaderboard on TikTok would generate, cost money, and be
    // unusable.
    await expect(
      submit({ platform: 'tiktok', preset: 'leaderboard' }),
    ).rejects.toThrow(MarketNotAllowed);
  });

  it('snapshots the brand, so regenerating later is not a rebrand', async () => {
    const brandId = await withWorkspace(ws, (tx) => saveBrand(tx, {
      workspaceId: ws, projectId: project, name: 'Acme',
      voice: 'Plain and direct.', donts: ['No exclamation marks'],
    }), { db });

    const result = await submit({ brandId });
    const [row] = await withWorkspace(ws, (tx) => tx.execute<{
      brand_snapshot: { name?: string; voice?: string }; prompt: string;
    }>(sql`select brand_snapshot, prompt from ad_creatives where id = ${result.creativeId}`), { db });

    expect(row!.brand_snapshot.name).toBe('Acme');
    expect(row!.prompt).toContain('Plain and direct.');
  });

  it('costs nothing when the provider throws', async () => {
    const before = await withWorkspace(ws, (tx) => balance(tx, ws), { db });
    const result = await submit({}, drivers({ fail: new Error('provider exploded') }));

    expect(result.status).toBe('failed');
    // `execute` releases its hold on the throw path, so nothing is charged.
    expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBe(before);
  });
});

describe('polling', () => {
  const startVideo = () =>
    submit({ type: 'video', durationSeconds: 8 }, drivers({ externalTaskId: 'job-1' }));

  const poll = (status: ProviderStatus) =>
    withWorkspace(ws, async (tx) => {
      const [claimed] = await claimPollable(tx);
      // Nothing claimable is itself a failure in these tests, so it is asserted
      // here rather than pushed onto every caller as a null check.
      expect(claimed, 'expected a claimable generation').toBeDefined();
      return pollCreative(tx, claimed!, {
        decrypt: (v: string) => v,
        driverFor: () => undefined,
        poll: async () => status,
        store: async () => ASSET_ID,
      } as never);
    }, { db });

  it('claims a due generation once', async () => {
    await startVideo();
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update ad_creatives set next_poll_at = now() - interval '1 minute' where workspace_id = ${ws}`), { db });

    const first = await withWorkspace(ws, (tx) => claimPollable(tx), { db });
    expect(first).toHaveLength(1);
    // Polling one provider job from two workers is a good way to be rate
    // limited by them.
    expect(await withWorkspace(ws, (tx) => claimPollable(tx), { db })).toEqual([]);
  });

  it('finishes when the provider says it is done', async () => {
    await startVideo();
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update ad_creatives set next_poll_at = now() - interval '1 minute' where workspace_id = ${ws}`), { db });

    const outcome = await poll({ state: 'done', url: 'https://cdn.test/v.mp4', units: 8 });
    expect(outcome).toMatchObject({ status: 'completed', assetId: ASSET_ID });
  });

  it('keeps waiting when the provider is briefly unreachable', async () => {
    await startVideo();
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update ad_creatives set next_poll_at = now() - interval '1 minute' where workspace_id = ${ws}`), { db });

    const outcome = await withWorkspace(ws, async (tx) => {
      const [claimed] = await claimPollable(tx);
      return pollCreative(tx, claimed!, {
        decrypt: (v: string) => v,
        driverFor: () => undefined,
        poll: async () => {
          throw new Error('ECONNRESET');
        },
      } as never);
    }, { db });

    // A failed poll is not a failed generation — throwing away a video that is
    // still rendering because we could not reach the API is the wrong call.
    expect(outcome.status).toBe('polling');
    expect(outcome.note).toMatch(/ECONNRESET/);
  });

  it('refunds when the provider fails hours later', async () => {
    const started = await startVideo();
    const afterSubmit = await withWorkspace(ws, (tx) => balance(tx, ws), { db });

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update ad_creatives set next_poll_at = now() - interval '1 minute' where id = ${started.creativeId}`), { db });

    const outcome = await poll({ state: 'failed', message: 'The render failed.' });
    expect(outcome.status).toBe('failed');

    /*
     * `execute` captured at submit, so by the time a video provider says
     * "failed" the hold is long gone. Without a refund the customer has paid
     * for nothing — which is exactly the bug the sources ship.
     */
    const afterFailure = await withWorkspace(ws, (tx) => balance(tx, ws), { db });
    expect(afterFailure).toBeGreaterThan(afterSubmit);
    expect(outcome.note).toMatch(/refunded/i);
  });

  it('refunds once, however many times the poll runs', async () => {
    const started = await startVideo();

    for (let i = 0; i < 3; i += 1) {
      await withWorkspace(ws, (tx) => tx.execute(sql`
        update ad_creatives set status = 'polling', next_poll_at = now() - interval '1 minute'
         where id = ${started.creativeId}`), { db });
      await poll({ state: 'failed', message: 'Still failed.' });
    }

    const [refunds] = await withWorkspace(ws, (tx) => tx.execute<{ n: number }>(sql`
      select count(*)::int as n from credit_entries
       where workspace_id = ${ws} and idempotency_key = ${`${started.creativeId}:generation-refund`}`), { db });

    // A retried worker must not hand out the credits three times.
    expect(refunds!.n).toBe(1);
  });

  it('gives up and refunds rather than holding money forever', async () => {
    const started = await startVideo();
    const afterSubmit = await withWorkspace(ws, (tx) => balance(tx, ws), { db });

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update ad_creatives
         set poll_count = ${MAX_POLLS}, next_poll_at = now() - interval '1 minute'
       where id = ${started.creativeId}`), { db });

    const outcome = await poll({ state: 'running' });
    expect(outcome.status).toBe('abandoned');
    // Credits nobody can spend and nobody can see is the quiet version of
    // taking the customer's money.
    expect(await withWorkspace(ws, (tx) => balance(tx, ws), { db })).toBeGreaterThan(afterSubmit);
  });
});

describe('generating copy', () => {
  const google = AD_PLATFORMS.google_search!;

  it('measures what the model produced and marks what will not fit', async () => {
    const result = await withWorkspace(ws, (tx) => generateCopy(tx, {
      workspaceId: ws, projectId: project, platform: 'google_search',
      brief: 'Widget racks for small teams',
    }, drivers({
      text: JSON.stringify({
        variants: [
          {
            headline: ['Widget racks', 'Ships in two days', 'Fits any shelf'],
            description: ['Racks that hold every widget.', 'Free returns.'],
          },
          {
            headline: ['A headline that is much too long for Google Search', 'Two', 'Three'],
            description: ['One.', 'Two.'],
          },
        ],
      }),
    }) as never), { db });

    expect(result.variants).toHaveLength(2);
    expect(result.variants[0]!.usable).toBe(true);
    // Marked, not dropped: a headline two characters over is worth editing.
    expect(result.variants[1]!.usable).toBe(false);
    expect(result.variants[1]!.problems[0]!.message).toMatch(/over 30/);
  });

  it('refuses honestly when AI is off, and does not pretend to have a fallback', async () => {
    await asPlatformAdmin((tx) => tx.execute(sql`
      update instance_settings set ai_master_enabled = false`), { db });

    try {
      await expect(
        withWorkspace(ws, (tx) => generateCopy(tx, {
          workspaceId: ws, projectId: project, platform: 'google_search', brief: 'x',
        }, drivers({ text: '{}' }) as never), { db }),
      ).rejects.toThrow(/switched off/i);
    } finally {
      await asPlatformAdmin((tx) => tx.execute(sql`
        update instance_settings set ai_master_enabled = true`), { db });
    }
  });

  describe('reading the model’s JSON', () => {
    it('copes with a fenced block', () => {
      const parsed = parseVariants(
        '```json\n{"variants":[{"headline":["One"]}]}\n```',
        google,
      );
      expect(parsed).toEqual([{ headline: ['One'] }]);
    });

    it('copes with a sentence before the JSON', () => {
      const parsed = parseVariants(
        'Here are your ads:\n{"variants":[{"headline":["One"]}]}',
        google,
      );
      expect(parsed).toEqual([{ headline: ['One'] }]);
    });

    it('copes with a bare array and with a single string where a list belongs', () => {
      // Both are things models do, and neither is worth failing a paid
      // generation over.
      expect(parseVariants('[{"headline":"Just one"}]', google)).toEqual([
        { headline: ['Just one'] },
      ]);
    });

    it('returns nothing rather than throwing on unparseable output', () => {
      expect(parseVariants('I am afraid I cannot do that.', google)).toEqual([]);
    });
  });
});
