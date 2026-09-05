/**
 * Composing, scheduling and publishing, against a real database.
 *
 * The behaviours that need a database are the ones about several networks
 * disagreeing: four succeed and one fails, one is rate limited and retries on
 * its own clock, a reviewer says no after the calendar already shows the post.
 * A single stored status cannot express any of those, which is why the targets
 * are rows.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  claimDueTargets, composePost, listAccounts, listPosts, recordFailure, recordPublished,
  saveQueue, setApproval, MAX_ATTEMPTS,
} from '../social.ts';
import { MarketNotAllowed } from '../service.ts';

const db = unsafeUnscopedDb();
const tag = `soc${Date.now()}`;

let ws = '';
let user = '';
let project = '';
let connection = '';
const accounts: Record<string, string> = {};

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Soc') returning id`);
    user = u!.id;
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Soc', ${user})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'market_pro'`);
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from social_posts where workspace_id = ${ws}`);
    await tx.execute(sql`delete from social_accounts where workspace_id = ${ws}`);
    await tx.execute(sql`delete from market_connections where workspace_id = ${ws}`);

    const [c] = await tx.execute<{ id: string }>(sql`
      insert into market_connections
        (workspace_id, project_id, provider, external_id, display_name, status)
      values (${ws}, ${project}, 'x', 'conn-1', 'Social', 'active') returning id`);
    connection = c!.id;

    for (const [provider, name] of [
      ['x', 'Acme on X'],
      ['linkedin', 'Acme on LinkedIn'],
      ['instagram', 'Acme on Instagram'],
    ] as const) {
      const [a] = await tx.execute<{ id: string }>(sql`
        insert into social_accounts
          (workspace_id, project_id, connection_id, provider, external_id, display_name)
        values (${ws}, ${project}, ${connection}, ${provider}, ${`ext-${provider}`}, ${name})
        returning id`);
      accounts[provider] = a!.id;
    }
  }, { db });
});

const compose = (
  input: Partial<Parameters<typeof composePost>[1]> = {},
  deps: Parameters<typeof composePost>[2] = {},
) =>
  withWorkspace(
    ws,
    (tx) =>
      composePost(
        tx,
        {
          workspaceId: ws,
          projectId: project,
          body: 'A new widget rack.',
          accountIds: [accounts.x!, accounts.linkedin!],
          ...input,
        },
        deps,
      ),
    { db },
  );

describe('composing', () => {
  it('creates one post and a target per account', async () => {
    const result = await compose();

    expect(result.problems).toEqual([]);
    expect(result.scheduled.map((s) => s.provider).sort()).toEqual(['linkedin', 'x']);

    const posts = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(posts).toHaveLength(1);
    expect(posts[0]!.targets).toHaveLength(2);
  });

  it('refuses before scheduling, and names every reason at once', async () => {
    await expect(
      compose({ accountIds: [accounts.instagram!, accounts.x!], body: 'w'.repeat(400) }),
    ).rejects.toThrow(MarketNotAllowed);

    try {
      await compose({ accountIds: [accounts.instagram!, accounts.x!], body: 'w'.repeat(400) });
    } catch (err) {
      const message = (err as Error).message;
      // Instagram needs media, X is over its limit — both, in one refusal, at
      // compose time rather than at 14:00 from somebody else's API.
      expect(message).toMatch(/cannot be text only/i);
      expect(message).toMatch(/over X's 280/i);
    }

    const posts = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(posts, 'nothing is written when the post cannot go out').toEqual([]);
  });

  it('passes warnings through without blocking', async () => {
    const result = await compose({
      accountIds: [accounts.linkedin!],
      images: 2,
      altText: ['just the one'],
    });
    expect(result.problems.every((p) => p.level === 'warning')).toBe(true);
    expect(result.problems[0]!.message).toMatch(/alt text/i);
  });
});

describe('the handoff to Link', () => {
  it('replaces the URL in the body with the short one', async () => {
    // A real row: `social_posts.link_id` is a foreign key, so a shorten
    // implementation handing back an id that no longer exists aborts the
    // compose rather than storing a dangling reference.
    const linkId = await withWorkspace(ws, async (tx) => {
      const [row] = await tx.execute<{ id: string }>(sql`
        insert into links (workspace_id, project_id, kind, alias, destination_url)
        values (${ws}, ${project}, 'short', ${`soc-${Date.now().toString(36)}`},
                'https://example.com/racks')
        returning id`);
      return row!.id;
    }, { db });

    const result = await compose(
      { body: 'Read this: https://example.com/racks', link: 'https://example.com/racks' },
      { shorten: async () => ({ linkId, shortUrl: 'https://mml.to/abc' }) },
    );

    expect(result.linkNote).toBeNull();
    const posts = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(posts[0]!.body).toContain('https://mml.to/abc');
    expect(posts[0]!.body).not.toContain('example.com/racks');
  });

  it('still posts when Link is not installed, and says clicks are untracked', async () => {
    const result = await compose(
      { body: 'Read this: https://example.com/racks', link: 'https://example.com/racks' },
      {},
    );

    // Degrade, never throw — the manifest boundary's whole point.
    expect(result.linkNote).toMatch(/not installed/i);
    expect(result.postId).toBeTruthy();
    const posts = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(posts[0]!.body).toContain('example.com/racks');
  });
});

describe('the queue', () => {
  it('places each account in its own grid', async () => {
    await withWorkspace(ws, async (tx) => {
      await saveQueue(tx, {
        workspaceId: ws, accountId: accounts.x!,
        slots: { mon: [9], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [] },
        timezone: 'UTC',
      });
      await saveQueue(tx, {
        workspaceId: ws, accountId: accounts.linkedin!,
        slots: { mon: [], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [17] },
        timezone: 'UTC',
      });
    }, { db });

    const result = await compose({ scheduleType: 'queue' });
    const times = Object.fromEntries(result.scheduled.map((s) => [s.provider, s.at]));

    // Two accounts, two grids. Forcing them to share one is why "queue" feels
    // useless in most tools.
    expect(times.x).not.toBe(times.linkedin);
    expect(new Date(times.x!).getUTCHours()).toBe(9);
    expect(new Date(times.linkedin!).getUTCHours()).toBe(17);
  });

  it('reports an account with no slots rather than posting immediately', async () => {
    await withWorkspace(ws, (tx) => saveQueue(tx, {
      workspaceId: ws, accountId: accounts.x!,
      slots: { mon: [], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [] },
    }), { db });

    const result = await compose({ scheduleType: 'queue', accountIds: [accounts.x!] });
    // Null, not "now": going out immediately when somebody asked to queue it is
    // worse than saying the queue is empty.
    expect(result.scheduled[0]!.at).toBeNull();
  });

  it('shows how many are waiting per account', async () => {
    await compose({ scheduleType: 'scheduled', scheduledAt: new Date(Date.now() + 86_400_000) });
    const list = await withWorkspace(ws, (tx) => listAccounts(tx, { projectId: project }), { db });

    expect(list.find((a) => a.provider === 'x')!.queued).toBe(1);
    expect(list.find((a) => a.provider === 'instagram')!.queued).toBe(0);
  });
});

describe('publishing', () => {
  const due = () =>
    compose({ scheduleType: 'scheduled', scheduledAt: new Date(Date.now() - 1000) });

  it('claims each target once', async () => {
    await due();
    const first = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });
    expect(first).toHaveLength(2);

    const second = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });
    expect(second).toEqual([]);
  });

  it('reports four of five rather than picking one story', async () => {
    const composed = await due();
    const targets = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });

    await withWorkspace(ws, async (tx) => {
      await recordPublished(tx, {
        targetId: targets[0]!.targetId, postId: composed.postId,
        remoteId: 'r1', remoteUrl: 'https://x.test/1',
      });
      await recordFailure(tx, {
        targetId: targets[1]!.targetId, postId: composed.postId,
        message: 'The caption was rejected.', retryable: false,
      });
    }, { db });

    const [post] = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });

    // Published, because it reached an audience — and the target rows carry
    // the one that did not, with its reason.
    expect(post!.status).toBe('published');
    expect(post!.targets.filter((t) => t.status === 'published')).toHaveLength(1);
    const failed = post!.targets.find((t) => t.status === 'failed')!;
    expect(failed.error).toBe('The caption was rejected.');
  });

  it('is failed only when every network refused', async () => {
    const composed = await due();
    const targets = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });

    await withWorkspace(ws, async (tx) => {
      for (const target of targets) {
        await recordFailure(tx, {
          targetId: target.targetId, postId: composed.postId,
          message: 'Nope.', retryable: false,
        });
      }
    }, { db });

    const [post] = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(post!.status).toBe('failed');
  });

  it('retries a transient failure on its own clock', async () => {
    const composed = await due();
    const [target] = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });

    const outcome = await withWorkspace(ws, (tx) => recordFailure(tx, {
      targetId: target!.targetId, postId: composed.postId,
      message: 'Rate limited.', retryable: true, retryAfterSeconds: 900,
    }), { db });

    expect(outcome.willRetry).toBe(true);

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{
      status: string; next_run_at: string;
    }>(sql`select status, next_run_at from social_targets where id = ${target!.targetId}`), { db });

    // Back to pending with its own future time — the other network is not held
    // up waiting for it.
    expect(row!.status).toBe('pending');
    expect(new Date(row!.next_run_at).getTime()).toBeGreaterThan(Date.now());
  });

  it('gives up after a few goes rather than logging the same failure forever', async () => {
    const composed = await due();

    for (let attempt = 1; attempt <= MAX_ATTEMPTS; attempt += 1) {
      const claimed = await withWorkspace(ws, (tx) => claimDueTargets(tx), { db });
      const target = claimed.find((t) => t.provider === 'x');
      if (!target) break;

      await withWorkspace(ws, (tx) => tx.execute(sql`
        update social_targets set next_run_at = now() - interval '1 minute' where id = ${target.targetId}`), { db });
      await withWorkspace(ws, (tx) => recordFailure(tx, {
        targetId: target.targetId, postId: composed.postId,
        message: 'Upstream is down.', retryable: true, retryAfterSeconds: 0,
      }), { db });
    }

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ status: string; attempts: number }>(sql`
      select t.status, t.attempts from social_targets t
        join social_accounts a on a.id = t.account_id
       where t.post_id = ${composed.postId} and a.provider = 'x'`), { db });

    expect(row!.attempts).toBe(MAX_ATTEMPTS);
    expect(row!.status).toBe('failed');
  });
});

describe('review', () => {
  it('keeps an unapproved post off the queue', async () => {
    await compose({
      scheduleType: 'scheduled',
      scheduledAt: new Date(Date.now() - 1000),
      requireApproval: true,
    });

    // On the calendar, visible to the team, and not claimable.
    expect(await withWorkspace(ws, (tx) => claimDueTargets(tx), { db })).toEqual([]);
  });

  it('releases it once somebody says yes', async () => {
    const composed = await compose({
      scheduleType: 'scheduled',
      scheduledAt: new Date(Date.now() - 1000),
      requireApproval: true,
    });

    await withWorkspace(ws, (tx) => setApproval(tx, {
      projectId: project, postId: composed.postId, state: 'approved', userId: user,
    }), { db });

    expect(await withWorkspace(ws, (tx) => claimDueTargets(tx), { db })).toHaveLength(2);
  });

  it('cancels on rejection rather than leaving it scheduled', async () => {
    const composed = await compose({
      scheduleType: 'scheduled',
      scheduledAt: new Date(Date.now() - 1000),
      requireApproval: true,
    });

    await withWorkspace(ws, (tx) => setApproval(tx, {
      projectId: project, postId: composed.postId, state: 'rejected', userId: user,
    }), { db });

    // A reviewer's "no" that the scheduler ignores an hour later is not a no.
    const [post] = await withWorkspace(ws, (tx) => listPosts(tx, { projectId: project }), { db });
    expect(post!.status).toBe('cancelled');
    expect(await withWorkspace(ws, (tx) => claimDueTargets(tx), { db })).toEqual([]);
  });
});
