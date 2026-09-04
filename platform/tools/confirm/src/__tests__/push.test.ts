/**
 * Push, against a real database.
 *
 * `web-push` itself is mocked — its crypto is not what is under test here, and
 * a real send would need a live push service. What *is* under test is
 * everything around it: who gets chosen, what a plan allows, and whether the
 * subscriber list stays honest after a send.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { audienceFor, enablePush, sendCampaign, ConfirmNotAllowed } from '../index.ts';

/**
 * The transport is injected rather than module-mocked.
 *
 * `vi.mock('web-push')` from this package does not intercept the import inside
 * `@mamal/push` — pnpm resolves it to a different physical path — so the mock
 * silently did nothing and the tests were sending for real. `sendCampaign`
 * takes the sender as a parameter, the same way it takes `decrypt`, so the seam
 * is a real one rather than test plumbing.
 */
type Outcome = { status: 'sent' | 'expired' | 'failed' | 'rate_limited'; id: string; [k: string]: unknown };
let scripted: ((id: string, index: number) => Outcome) | null = null;
let sendCalls = 0;

const fakeSend = (async (subs: { id: string }[]) => {
  return subs.map((s, i) => {
    sendCalls++;
    return scripted ? scripted(s.id, i) : ({ status: 'sent', id: s.id } as Outcome);
  });
}) as never;

const db = unsafeUnscopedDb();
const tag = `push${Date.now()}`;
const identity = (s: string) => s; // encryption is exercised in @mamal/ai

let ws = '';
let project = '';
let siteId = '';
let pushSiteId = '';

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Push') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Push', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    const [s] = await tx.execute<{ id: string }>(sql`
      insert into sites (workspace_id, project_id, host, root_url)
      values (${ws}, ${project}, ${'push.test'}, ${'https://push.test'}) returning id`);
    siteId = s!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'confirm_pro'`);
  }, { db });

  const site = await withWorkspace(
    ws,
    (tx) => enablePush(tx, { workspaceId: ws, projectId: project, siteId, encrypt: identity }),
    { db },
  );
  pushSiteId = site.id;
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where slug = ${tag}`);
    await tx.execute(sql`delete from users where email = ${`${tag}@test.local`}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  scripted = null;
  sendCalls = 0;
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from push_campaigns where push_website_id = ${pushSiteId}`);
    await tx.execute(sql`delete from push_subscribers where push_website_id = ${pushSiteId}`);
    await tx.execute(sql`delete from push_segments where push_website_id = ${pushSiteId}`);
  }, { db });
});

async function addSubscriber(over: Partial<{
  endpoint: string; country: string; browser: string; tags: string[]; status: string; ageDays: number;
}> = {}) {
  const endpoint = over.endpoint ?? `https://fcm.test/${Math.random().toString(36).slice(2)}`;
  return withWorkspace(ws, async (tx) => {
    const [r] = await tx.execute<{ id: string }>(sql`
      insert into push_subscribers
        (workspace_id, push_website_id, endpoint, p256dh, auth, country, browser, tags, status,
         subscribed_at)
      values (${ws}, ${pushSiteId}, ${endpoint}, 'k', 'a',
              ${over.country ?? 'GB'}, ${over.browser ?? 'Chrome'},
              ${sql.raw(`ARRAY[${(over.tags ?? ['customer']).map((t) => `'${t}'`).join(',')}]::text[]`)},
              ${over.status ?? 'active'},
              now() - (${over.ageDays ?? 1} * interval '1 day'))
      returning id`);
    return r!.id;
  }, { db });
}

async function makeCampaign(segmentId?: string) {
  return withWorkspace(ws, async (tx) => {
    const [r] = await tx.execute<{ id: string }>(sql`
      insert into push_campaigns (workspace_id, push_website_id, segment_id, title, body, url)
      values (${ws}, ${pushSiteId}, ${segmentId ?? null}, 'Sale', 'Half price today', '/sale')
      returning id`);
    return r!.id;
  }, { db });
}

const send = (campaignId: string) =>
  withWorkspace(
    ws,
    (tx) => sendCampaign(tx, {
      workspaceId: ws, campaignId, decrypt: identity, subject: 'mailto:a@b.c',
      sendAll: fakeSend,
    }),
    { db },
  );

describe('enabling push', () => {
  it('mints one VAPID pair per site and reuses it', async () => {
    // A shared key could never be rotated: the public half is baked into every
    // browser subscription, so one compromise would invalidate everyone.
    const again = await withWorkspace(
      ws,
      (tx) => enablePush(tx, { workspaceId: ws, projectId: project, siteId, encrypt: identity }),
      { db },
    );
    expect(again.id).toBe(pushSiteId);
  });

  it('stores the private key encrypted, never in clear', async () => {
    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ enc: string; pub: string }>(sql`
      select vapid_private_key_encrypted as enc, vapid_public_key as pub
        from push_websites where id = ${pushSiteId}`), { db });
    // The column exists and is populated; the encryption itself is the
    // credential module's job, exercised there. The public key is a real
    // base64url P-256 point, which is what a browser will accept.
    expect(row!.enc).toBeTruthy();
    expect(row!.pub).toMatch(/^[A-Za-z0-9_-]{80,}$/);
  });
});

describe('choosing an audience', () => {
  it('reaches everyone active when there is no segment', async () => {
    await addSubscriber();
    await addSubscriber();
    await addSubscriber({ status: 'expired' });

    const audience = await withWorkspace(
      ws, (tx) => audienceFor(tx, { workspaceId: ws, pushWebsiteId: pushSiteId }), { db },
    );
    expect(audience).toHaveLength(2);
  });

  it('applies a segment, including on tags', async () => {
    await addSubscriber({ tags: ['customer'] });
    await addSubscriber({ tags: ['prospect'] });

    const segmentId = await withWorkspace(ws, async (tx) => {
      const [r] = await tx.execute<{ id: string }>(sql`
        insert into push_segments (workspace_id, push_website_id, name, filter)
        values (${ws}, ${pushSiteId}, 'Customers',
                ${JSON.stringify({ match: 'all', conditions: [{ field: 'tags', op: 'contains', value: 'customer' }] })}::jsonb)
        returning id`);
      return r!.id;
    }, { db });

    const audience = await withWorkspace(
      ws, (tx) => audienceFor(tx, { workspaceId: ws, pushWebsiteId: pushSiteId, segmentId }), { db },
    );
    expect(audience).toHaveLength(1);
  });

  it('a segment referencing an unknown field selects nobody, not everybody', async () => {
    await addSubscriber();
    const segmentId = await withWorkspace(ws, async (tx) => {
      const [r] = await tx.execute<{ id: string }>(sql`
        insert into push_segments (workspace_id, push_website_id, name, filter)
        values (${ws}, ${pushSiteId}, 'Broken',
                ${JSON.stringify({ conditions: [{ field: 'star_sign', op: 'is', value: 'leo' }] })}::jsonb)
        returning id`);
      return r!.id;
    }, { db });

    // Failing closed matters more for a *send* than for a widget: the wrong
    // direction here means notifying people who were meant to be excluded.
    const audience = await withWorkspace(
      ws, (tx) => audienceFor(tx, { workspaceId: ws, pushWebsiteId: pushSiteId, segmentId }), { db },
    );
    expect(audience).toHaveLength(0);
  });
});

describe('sending', () => {
  it('sends to the audience and records the result', async () => {
    await addSubscriber();
    await addSubscriber();
    const id = await makeCampaign();

    const report = await send(id);
    expect(report).toMatchObject({ audience: 2, sent: 2, failed: 0 });
    expect(sendCalls).toBe(2);

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ status: string; sent: number }>(sql`
      select status, sent from push_campaigns where id = ${id}`), { db });
    expect(row).toMatchObject({ status: 'sent', sent: 2 });
  });

  it('refuses to send the same campaign twice', async () => {
    // The fastest route to a blocked origin is sending someone the same
    // notification twice, so a retried job must be refused rather than obeyed.
    await addSubscriber();
    const id = await makeCampaign();
    await send(id);
    await expect(send(id)).rejects.toBeInstanceOf(ConfirmNotAllowed);
    expect(sendCalls).toBe(1);
  });

  it('retires endpoints the push service says are gone', async () => {
    const alive = await addSubscriber();
    const dead = await addSubscriber();
    const id = await makeCampaign();

    // Second endpoint comes back 410 — permanently gone.
    scripted = (subId, i) =>
      i === 1 ? { status: 'expired', id: subId, code: 410 } : { status: 'sent', id: subId };

    const report = await send(id);
    expect(report.expired).toBe(1);

    const rows = await withWorkspace(ws, (tx) => tx.execute<{ id: string; status: string }>(sql`
      select id, status from push_subscribers where push_website_id = ${pushSiteId}`), { db });
    const byId = Object.fromEntries(rows.map((r) => [r.id, r.status]));
    expect(byId[dead]).toBe('expired');
    expect(byId[alive]).toBe('active');
  });

  it('a retired subscriber is not in the next campaign’s audience', async () => {
    // The point of retiring: without it every send re-attempts the same dead
    // endpoints forever and the delivery rate becomes meaningless.
    await addSubscriber();
    await addSubscriber();
    const first = await makeCampaign();
    scripted = (subId, i) =>
      i === 1 ? { status: 'expired', id: subId, code: 410 } : { status: 'sent', id: subId };
    await send(first);

    scripted = null;
    sendCalls = 0;
    const second = await makeCampaign();
    const report = await send(second);
    expect(report.audience).toBe(1);
    expect(sendCalls).toBe(1);
  });

  it('a failure that is not a 410 leaves the subscriber alone', async () => {
    // A transient 500 is not a reason to unsubscribe somebody.
    const id0 = await addSubscriber();
    const id = await makeCampaign();
    scripted = (subId) => ({ status: 'failed', id: subId, code: 500, error: 'internal' });

    const report = await send(id);
    expect(report).toMatchObject({ sent: 0, failed: 1, expired: 0 });

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ status: string }>(sql`
      select status from push_subscribers where id = ${id0}`), { db });
    expect(row!.status).toBe('active');
  });

  it('refuses a campaign whose audience exceeds the plan', async () => {
    /*
     * Against a purpose-built 2-subscriber plan, not the fixture's 25,000.
     *
     * The first version of this test inserted `limit + 1` rows to reach the
     * real ceiling: 25,001 inserts, which timed out — and its writes kept
     * landing after the timeout and corrupted the next test. What is being
     * checked is the refusal, and that needs a small limit, not a large
     * audience.
     */
    const tiny = `${tag}-tiny`;
    const small = await asPlatformAdmin(async (tx) => {
      const [plan] = await tx.execute<{ id: string }>(sql`
        insert into plans (key, name, kind, status)
        values (${tiny}, 'Tiny push', 'subscription', 'active') returning id`);
      await tx.execute(sql`
        insert into plan_entitlements (plan_id, feature_key, mode, limit_value)
        values (${plan!.id}, 'confirm.push_subscribers', 'limit', 2)`);

      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${`${tiny}@test.local`}, 'Tiny') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tiny}, 'Tiny', ${u!.id})
        returning id`);
      const [pr] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${w!.id}, 'Default', 'default', true) returning id`);
      const [st] = await tx.execute<{ id: string }>(sql`
        insert into sites (workspace_id, project_id, host, root_url)
        values (${w!.id}, ${pr!.id}, ${'tiny.test'}, ${'https://tiny.test'}) returning id`);
      await tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status)
        values (${w!.id}, ${plan!.id}, 'active')`);
      return { ws: w!.id, project: pr!.id, site: st!.id };
    }, { db });

    const site = await withWorkspace(
      small.ws,
      (tx) => enablePush(tx, {
        workspaceId: small.ws, projectId: small.project, siteId: small.site, encrypt: identity,
      }),
      { db },
    );

    const campaignId = await withWorkspace(small.ws, async (tx) => {
      for (let i = 0; i < 3; i++) {
        await tx.execute(sql`
          insert into push_subscribers
            (workspace_id, push_website_id, endpoint, p256dh, auth, status)
          values (${small.ws}, ${site.id}, ${`https://fcm.test/tiny-${i}`}, 'k', 'a', 'active')`);
      }
      const [c] = await tx.execute<{ id: string }>(sql`
        insert into push_campaigns (workspace_id, push_website_id, title, body)
        values (${small.ws}, ${site.id}, 'Too many', 'Body') returning id`);
      return c!.id;
    }, { db });

    // Refused before a single notification goes out, not halfway through.
    await expect(
      withWorkspace(small.ws, (tx) => sendCampaign(tx, {
        workspaceId: small.ws, campaignId, decrypt: identity,
        subject: 'mailto:a@b.c', sendAll: fakeSend,
      }), { db }),
    ).rejects.toBeInstanceOf(ConfirmNotAllowed);
    expect(sendCalls).toBe(0);

    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where slug = ${tiny}`);
      await tx.execute(sql`delete from users where email = ${`${tiny}@test.local`}`);
      await tx.execute(sql`delete from plans where key = ${tiny}`);
    }, { db });
  },
  /*
   * Twenty seconds, not the suite's five.
   *
   * This test builds a whole second tenant — plan, entitlement, user,
   * workspace, project, site, three subscribers, a campaign — across ten round
   * trips, where its neighbours do one or two. It passes in well under a second
   * on an idle box and times out when the dev server and a worker are also on
   * the pool, which is the normal state of a developer's machine. A timeout
   * sized to the fixture is honest; one sized to the fastest test in the file
   * is a flake generator.
   */
  20_000);

  it('sends nothing, gracefully, when the audience is empty', async () => {
    const id = await makeCampaign();
    const report = await send(id);
    expect(report).toMatchObject({ audience: 0, sent: 0 });
    expect(sendCalls).toBe(0);
  });
});
