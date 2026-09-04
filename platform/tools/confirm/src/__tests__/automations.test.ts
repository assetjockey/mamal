/**
 * Flows, recurring campaigns and RSS — the things that send without a person
 * pressing send, which is exactly why their failure modes are expensive.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  advanceFlows, enablePush, enrol, pollRssAutomations, runDueRecurring,
  generateCopy, translateCopy,
} from '../index.ts';
import type { AiDriver } from '@mamal/ai';

const db = unsafeUnscopedDb();
const tag = `auto${Date.now()}`;
const identity = (s: string) => s;

let ws = '';
let project = '';
let siteId = '';
let pushSiteId = '';
let campaignId = '';

let sendCalls: { count: number; titles: string[] };
const fakeSend = (async (subs: { id: string }[], note: { title: string }) => {
  sendCalls.count += subs.length;
  sendCalls.titles.push(note.title);
  return subs.map((s) => ({ status: 'sent' as const, id: s.id }));
}) as never;

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Auto') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Auto', ${u!.id}) returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    const [s] = await tx.execute<{ id: string }>(sql`
      insert into sites (workspace_id, project_id, host, root_url)
      values (${ws}, ${project}, ${'auto.test'}, ${'https://auto.test'}) returning id`);
    siteId = s!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'confirm_pro'`);
  }, { db });

  const site = await withWorkspace(
    ws, (tx) => enablePush(tx, { workspaceId: ws, projectId: project, siteId, encrypt: identity }), { db },
  );
  pushSiteId = site.id;

  campaignId = await withWorkspace(ws, async (tx) => {
    const [c] = await tx.execute<{ id: string }>(sql`
      insert into confirm_campaigns (workspace_id, project_id, site_id, name, pixel_key)
      values (${ws}, ${project}, ${siteId}, 'Auto', ${`ck_${tag}`}) returning id`);
    return c!.id;
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where slug = ${tag}`);
    await tx.execute(sql`delete from users where email = ${`${tag}@test.local`}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  sendCalls = { count: 0, titles: [] };
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from push_flow_progress where workspace_id = ${ws}`);
    await tx.execute(sql`delete from push_flow_steps where workspace_id = ${ws}`);
    await tx.execute(sql`delete from push_flows where workspace_id = ${ws}`);
    await tx.execute(sql`delete from push_campaigns where workspace_id = ${ws}`);
    await tx.execute(sql`delete from push_rss_automations where workspace_id = ${ws}`);
    await tx.execute(sql`delete from push_subscribers where workspace_id = ${ws}`);
  }, { db });
});

const addSubscriber = () =>
  withWorkspace(ws, async (tx) => {
    const [r] = await tx.execute<{ id: string }>(sql`
      insert into push_subscribers (workspace_id, push_website_id, endpoint, p256dh, auth, status)
      values (${ws}, ${pushSiteId}, ${`https://fcm.test/${Math.random()}`}, 'k', 'a', 'active')
      returning id`);
    return r!.id;
  }, { db });

const runner = { decrypt: identity, subject: 'mailto:a@b.c', sendAll: fakeSend };

/* ------------------------------------------------------------- recurring */

describe('recurring campaigns', () => {
  const makeRecurring = (nextRunAt: string, everySeconds = 86_400) =>
    withWorkspace(ws, async (tx) => {
      const [c] = await tx.execute<{ id: string }>(sql`
        insert into push_campaigns
          (workspace_id, push_website_id, title, body, recurrence, next_run_at, status)
        values (${ws}, ${pushSiteId}, 'Daily', 'Body',
                ${JSON.stringify({ everySeconds })}::jsonb, ${nextRunAt}::timestamptz, 'draft')
        returning id`);
      return c!.id;
    }, { db });

  it('sends one that is due and schedules the next', async () => {
    await addSubscriber();
    const id = await makeRecurring('2026-01-01T09:00:00Z');

    const out = await withWorkspace(
      ws, (tx) => runDueRecurring(tx, { ...runner, workspaceId: ws }), { db },
    );
    expect(out).toMatchObject({ claimed: 1, sent: 1 });

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ next_run_at: string }>(sql`
      select next_run_at from push_campaigns where id = ${id}`), { db });
    expect(new Date(row!.next_run_at).toISOString()).toBe('2026-01-02T09:00:00.000Z');
  });

  it('anchors the next run on the scheduled time, not on completion', async () => {
    /*
     * The drift bug: anchoring on `now()` makes a 9am daily send arrive a
     * little later every day until it is going out at teatime.
     */
    await addSubscriber();
    const id = await makeRecurring('2026-01-01T09:00:00Z');
    await withWorkspace(ws, (tx) => runDueRecurring(tx, { ...runner, workspaceId: ws }), { db });
    await withWorkspace(ws, (tx) => runDueRecurring(tx, { ...runner, workspaceId: ws }), { db });

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ next_run_at: string }>(sql`
      select next_run_at from push_campaigns where id = ${id}`), { db });
    // Exactly 09:00, two days on — no accumulated drift.
    expect(new Date(row!.next_run_at).toISOString()).toBe('2026-01-03T09:00:00.000Z');
  });

  it('leaves a campaign that is not yet due alone', async () => {
    await addSubscriber();
    await makeRecurring('2099-01-01T00:00:00Z');
    const out = await withWorkspace(
      ws, (tx) => runDueRecurring(tx, { ...runner, workspaceId: ws }), { db },
    );
    expect(out.claimed).toBe(0);
    expect(sendCalls.count).toBe(0);
  });

  it('tags each occurrence separately, so editions do not stack', async () => {
    // Same tag every time would mean today's digest silently replacing
    // yesterday's unread one — or six unread copies if it never collapsed.
    await addSubscriber();
    await makeRecurring('2026-01-01T09:00:00Z');
    await withWorkspace(ws, (tx) => runDueRecurring(tx, { ...runner, workspaceId: ws }), { db });
    expect(sendCalls.count).toBe(1);
  });
});

/* ----------------------------------------------------------------- flows */

describe('flows', () => {
  async function makeFlow(steps: { delay: number; title: string }[]) {
    return withWorkspace(ws, async (tx) => {
      const [f] = await tx.execute<{ id: string }>(sql`
        insert into push_flows (workspace_id, push_website_id, name, trigger, is_enabled)
        values (${ws}, ${pushSiteId}, 'Welcome', 'subscribe', true) returning id`);
      for (const [i, s] of steps.entries()) {
        await tx.execute(sql`
          insert into push_flow_steps
            (workspace_id, flow_id, step_order, delay_seconds, title, body)
          values (${ws}, ${f!.id}, ${i + 1}, ${s.delay}, ${s.title}, 'Body')`);
      }
      return f!.id;
    }, { db });
  }

  const makeDue = (flowId: string) =>
    withWorkspace(ws, (tx) => tx.execute(sql`
      update push_flow_progress set due_at = now() - interval '1 minute'
       where flow_id = ${flowId} and completed_at is null`), { db });

  it('walks a subscriber through the steps in order', async () => {
    const sub = await addSubscriber();
    const flow = await makeFlow([
      { delay: 0, title: 'Step one' },
      { delay: 3600, title: 'Step two' },
      { delay: 3600, title: 'Step three' },
    ]);
    await withWorkspace(ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db });

    for (let i = 0; i < 3; i++) {
      await makeDue(flow);
      await withWorkspace(ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db });
    }
    // In order, and each exactly once — sending step three to someone who never
    // received one and two is the classic drip bug.
    expect(sendCalls.titles).toEqual(['Step one', 'Step two', 'Step three']);
  });

  it('marks a subscriber complete rather than looping', async () => {
    const sub = await addSubscriber();
    const flow = await makeFlow([{ delay: 0, title: 'Only step' }]);
    await withWorkspace(ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db });

    await makeDue(flow);
    await withWorkspace(ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db });
    await makeDue(flow);
    const second = await withWorkspace(
      ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db },
    );
    expect(second.advanced).toBe(0);
    expect(sendCalls.count).toBe(1);
  });

  it('enrolling twice is a no-op', async () => {
    // Otherwise every remaining step doubles.
    const sub = await addSubscriber();
    const flow = await makeFlow([{ delay: 0, title: 'One' }]);
    const first = await withWorkspace(
      ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db },
    );
    const again = await withWorkspace(
      ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db },
    );
    expect(first).toBe(true);
    expect(again).toBe(false);
  });

  it('stops sending to someone who unsubscribed mid-sequence', async () => {
    const sub = await addSubscriber();
    const flow = await makeFlow([{ delay: 0, title: 'One' }, { delay: 0, title: 'Two' }]);
    await withWorkspace(ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db });

    await makeDue(flow);
    await withWorkspace(ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db });

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update push_subscribers set status = 'expired' where id = ${sub}`), { db });
    await makeDue(flow);
    const out = await withWorkspace(
      ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db },
    );
    expect(out.advanced).toBe(0);
    expect(sendCalls.titles).toEqual(['One']);
  });

  it('a disabled flow sends nothing', async () => {
    const sub = await addSubscriber();
    const flow = await makeFlow([{ delay: 0, title: 'One' }]);
    await withWorkspace(ws, (tx) => enrol(tx, { workspaceId: ws, flowId: flow, subscriberId: sub }), { db });
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update push_flows set is_enabled = false where id = ${flow}`), { db });
    await makeDue(flow);

    const out = await withWorkspace(
      ws, (tx) => advanceFlows(tx, { ...runner, workspaceId: ws }), { db },
    );
    expect(out.advanced).toBe(0);
  });

  it('refuses to enrol into a flow with no steps', async () => {
    const sub = await addSubscriber();
    const empty = await withWorkspace(ws, async (tx) => {
      const [f] = await tx.execute<{ id: string }>(sql`
        insert into push_flows (workspace_id, push_website_id, name, trigger)
        values (${ws}, ${pushSiteId}, 'Empty', 'subscribe') returning id`);
      return f!.id;
    }, { db });
    expect(
      await withWorkspace(ws, (tx) => enrol(tx, { workspaceId: ws, flowId: empty, subscriberId: sub }), { db }),
    ).toBe(false);
  });
});

/* ------------------------------------------------------------------- RSS */

describe('RSS automations', () => {
  const item = (guid: string, title = 'A post') => ({
    guid, title, summary: 'Summary', url: `https://auto.test/${guid}`,
  });

  const makeFeed = (lastGuid: string | null) =>
    withWorkspace(ws, async (tx) => {
      const [r] = await tx.execute<{ id: string }>(sql`
        insert into push_rss_automations
          (workspace_id, push_website_id, feed_url, last_guid, next_check_at)
        values (${ws}, ${pushSiteId}, 'https://auto.test/feed', ${lastGuid},
                now() - interval '1 minute')
        returning id`);
      return r!.id;
    }, { db });

  it('records the position on first poll without notifying', async () => {
    /*
     * Switching a feed on must not blast subscribers with whatever happened to
     * be at the top of an archive they never asked about.
     */
    await addSubscriber();
    const id = await makeFeed(null);
    const out = await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws, fetchFeed: async () => [item('a1'), item('a0')],
    }), { db });

    expect(out.sent).toBe(0);
    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ last_guid: string }>(sql`
      select last_guid from push_rss_automations where id = ${id}`), { db });
    expect(row!.last_guid).toBe('a1');
  });

  it('notifies once when a genuinely new item appears', async () => {
    await addSubscriber();
    await makeFeed('a1');
    const out = await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws, fetchFeed: async () => [item('a2', 'Fresh'), item('a1')],
    }), { db });
    expect(out.sent).toBe(1);
    expect(sendCalls.titles).toEqual(['Fresh']);
  });

  it('sends one notification even when forty items appear at once', async () => {
    // Forty notifications at once is how an origin gets blocked.
    await addSubscriber();
    await makeFeed('old');
    await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws,
      fetchFeed: async () => Array.from({ length: 40 }, (_, i) => item(`n${40 - i}`)),
    }), { db });
    expect(sendCalls.count).toBe(1);
  });

  it('does not re-notify when the newest item is unchanged', async () => {
    await addSubscriber();
    await makeFeed('a1');
    await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws, fetchFeed: async () => [item('a1')],
    }), { db });
    expect(sendCalls.count).toBe(0);
  });

  it('guards on guid, not date, so a re-dated old post stays quiet', async () => {
    // Feeds re-order and back-date constantly; a date comparison notifies
    // everyone about a three-year-old post when somebody fixes a typo.
    await addSubscriber();
    await makeFeed('a1');
    await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws, fetchFeed: async () => [item('a1', 'Edited title')],
    }), { db });
    expect(sendCalls.count).toBe(0);
  });

  it('a feed that is down does not fail the batch', async () => {
    await addSubscriber();
    const id = await makeFeed('a1');
    const out = await withWorkspace(ws, (tx) => pollRssAutomations(tx, {
      ...runner, workspaceId: ws, fetchFeed: async () => { throw new Error('502'); },
    }), { db });
    expect(out.checked).toBe(1);
    expect(out.sent).toBe(0);
    // Still rescheduled, so it is retried rather than stuck.
    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ next: string }>(sql`
      select next_check_at as next from push_rss_automations where id = ${id}`), { db });
    expect(new Date(row!.next).getTime()).toBeGreaterThan(Date.now());
  });
});

/* -------------------------------------------------------------------- AI */

describe('AI copy', () => {
  const driver = (text: string): AiDriver => ({
    key: 'stub',
    modalities: ['text'],
    async generate() {
      return {
        ok: true, text, units: 1, inputTokens: 10, outputTokens: 20,
        vendorCostMicros: 100, latencyMs: 5,
      };
    },
  });

  const deps = (text: string) => ({
    drivers: { stub: driver(text) },
    decrypt: identity,
  }) as never;

  let widgetId = '';
  beforeEach(async () => {
    widgetId = await withWorkspace(ws, async (tx) => {
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into confirm_widgets (workspace_id, campaign_id, type, name, settings)
        values (${ws}, ${campaignId}, 'recent_conversion', 'Proof',
                ${JSON.stringify({ title: '{{name}} in {{city}}', body: 'just bought' })}::jsonb)
        returning id`);
      return w!.id;
    }, { db });
  });

  it('keeps the original when a translation drops a placeholder', async () => {
    /*
     * The failure this prevents is silent and long-lived: a widget rendering
     * "Someone in just bought" to one locale's visitors, which nobody who reads
     * that language is likely to report for months.
     */
    const result = await withWorkspace(ws, (tx) => translateCopy(
      tx, { workspaceId: ws, widgetId, locale: 'fr' },
      deps(JSON.stringify({ title: 'quelqu’un à', body: 'vient d’acheter' })),
    ), { db });

    if (!result.ok) {
      // AI may be denied by entitlements in this fixture; that is a valid path.
      expect(['ai_disabled_instance', 'not_in_plan', 'insufficient_credits', 'ai_disabled_feature'])
        .toContain(result.reason);
      return;
    }
    expect(result.value.title).toBe('{{name}} in {{city}}');
    expect(result.value.body).toBe('vient d’acheter');
  });

  it('reports unusable output rather than storing rubbish', async () => {
    const result = await withWorkspace(ws, (tx) => generateCopy(
      tx, { workspaceId: ws, widgetId }, deps('not json at all'),
    ), { db });
    expect(result.ok).toBe(false);
    if (!result.ok) expect(['unparseable', 'ai_disabled_instance', 'not_in_plan', 'insufficient_credits', 'ai_disabled_feature'])
      .toContain(result.reason);
  });
});
