import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { inList } from '@mamal/db';
import { sendMany, summarise, type Notification, type Vapid } from '@mamal/push';
import { audienceFor, retireExpired } from './push.ts';

/**
 * The three things that send push without a person pressing send: recurring
 * campaigns, flows, and RSS automations.
 *
 * All three follow the same rule the audit scheduler established — **claim,
 * then act**. A row is moved forward in the same statement that selects it, so
 * two workers racing produce one send rather than two. Nothing here uses a
 * per-entity repeatable job; at 100,000 flows that is Redis-memory suicide.
 */

export type Clock = () => Date;
export type Sender = typeof sendMany;

type Runner = {
  decrypt: (cipher: string) => string;
  subject: string;
  now?: Clock;
  sendAll?: Sender;
};

/* ------------------------------------------------------ recurring campaigns */

export type RecurringResult = { claimed: number; sent: number; expired: number };

/**
 * Sends every recurring campaign whose `next_run_at` has passed.
 *
 * The next occurrence is computed from the *scheduled* time, not from now:
 * anchoring on completion makes a daily campaign drift later every day until a
 * "9am" send arrives at teatime.
 */
export async function runDueRecurring(
  tx: WorkspaceScopedDb,
  opts: Runner & { workspaceId: string; limit?: number },
): Promise<RecurringResult> {
  const now = (opts.now ?? (() => new Date()))();

  const due = await tx.execute<{
    id: string; push_website_id: string; segment_id: string | null;
    title: string; body: string; icon_url: string | null; image_url: string | null;
    url: string | null; ttl_seconds: number; next_run_at: string;
    recurrence: { everySeconds?: number } | null;
    vapid_public_key: string; vapid_private_key_encrypted: string;
  }>(sql`
    select c.id, c.push_website_id, c.segment_id, c.title, c.body, c.icon_url, c.image_url,
           c.url, c.ttl_seconds, c.next_run_at, c.recurrence,
           w.vapid_public_key, w.vapid_private_key_encrypted
      from push_campaigns c
      join push_websites w on w.id = c.push_website_id
     where c.workspace_id = ${opts.workspaceId}
       and c.recurrence is not null
       and c.next_run_at is not null
       and c.next_run_at <= ${now.toISOString()}::timestamptz
       and w.is_enabled
     order by c.next_run_at
     limit ${opts.limit ?? 50}
     for update of c skip locked`);

  if (due.length === 0) return { claimed: 0, sent: 0, expired: 0 };

  // Claim first: the row is moved forward before a single send happens, so a
  // crash mid-send loses that occurrence rather than repeating it forever.
  for (const c of due) {
    const every = Math.max(60, Number(c.recurrence?.everySeconds ?? 86_400));
    await tx.execute(sql`
      update push_campaigns
         set next_run_at = ${c.next_run_at}::timestamptz + (${every} * interval '1 second'),
             updated_at = now()
       where id = ${c.id}`);
  }

  let sent = 0;
  let expired = 0;

  for (const c of due) {
    const audience = await audienceFor(tx, {
      workspaceId: opts.workspaceId,
      pushWebsiteId: c.push_website_id,
      segmentId: c.segment_id,
    });
    if (audience.length === 0) continue;

    const vapid: Vapid = {
      publicKey: c.vapid_public_key,
      privateKey: opts.decrypt(c.vapid_private_key_encrypted),
      subject: opts.subject,
    };
    const notification: Notification = {
      title: c.title, body: c.body, iconUrl: c.icon_url, imageUrl: c.image_url,
      url: c.url, ttlSeconds: c.ttl_seconds,
      // Occurrence-scoped, so today's edition replaces yesterday's unread one
      // rather than stacking six unread copies of the same weekly digest.
      tag: `recurring-${c.id}-${new Date(c.next_run_at).toISOString().slice(0, 10)}`,
    };

    const outcomes = await (opts.sendAll ?? sendMany)(audience, notification, vapid, {});
    const totals = summarise(outcomes);
    expired += await retireExpired(tx, opts.workspaceId, outcomes);
    sent += totals.sent;

    await tx.execute(sql`
      update push_campaigns
         set sent = sent + ${totals.sent},
             failed = failed + ${totals.failed + totals.rateLimited},
             sent_at = now(), updated_at = now()
       where id = ${c.id}`);
  }

  return { claimed: due.length, sent, expired };
}

/* --------------------------------------------------------------------- flows */

export type FlowResult = { advanced: number; sent: number };

/**
 * Advances drip sequences.
 *
 * A flow is a list of steps with delays. Progress is stored per subscriber in
 * `push_flow_progress`, so a subscriber who joins today starts at step one
 * regardless of when the flow was created — the alternative, sending step four
 * to someone who never got steps one to three, is the classic drip bug.
 */
export async function advanceFlows(
  tx: WorkspaceScopedDb,
  opts: Runner & { workspaceId: string; limit?: number },
): Promise<FlowResult> {
  const now = (opts.now ?? (() => new Date()))();

  const due = await tx.execute<{
    progress_id: string; subscriber_id: string; step_id: string;
    endpoint: string; p256dh: string; auth: string;
    title: string; body: string; url: string | null;
    flow_id: string; step_order: number;
    vapid_public_key: string; vapid_private_key_encrypted: string;
  }>(sql`
    select p.id as progress_id, p.subscriber_id, s.id as step_id,
           sub.endpoint, sub.p256dh, sub.auth,
           s.title, s.body, s.url, s.flow_id, s.step_order,
           w.vapid_public_key, w.vapid_private_key_encrypted
      from push_flow_progress p
      join push_flow_steps s on s.flow_id = p.flow_id and s.step_order = p.next_step
      join push_subscribers sub on sub.id = p.subscriber_id
      join push_flows f on f.id = p.flow_id
      join push_websites w on w.id = f.push_website_id
     where p.workspace_id = ${opts.workspaceId}
       and p.due_at <= ${now.toISOString()}::timestamptz
       and p.completed_at is null
       and f.is_enabled
       -- A subscriber who unsubscribed mid-sequence must not keep receiving it.
       and sub.status = 'active'
     order by p.due_at
     limit ${opts.limit ?? 200}
     for update of p skip locked`);

  if (due.length === 0) return { advanced: 0, sent: 0 };

  let sent = 0;
  for (const row of due) {
    const vapid: Vapid = {
      publicKey: row.vapid_public_key,
      privateKey: opts.decrypt(row.vapid_private_key_encrypted),
      subject: opts.subject,
    };

    const outcomes = await (opts.sendAll ?? sendMany)(
      [{ id: row.subscriber_id, endpoint: row.endpoint, p256dh: row.p256dh, auth: row.auth }],
      { title: row.title, body: row.body, url: row.url, tag: `flow-${row.flow_id}` },
      vapid,
      {},
    );
    sent += summarise(outcomes).sent;
    await retireExpired(tx, opts.workspaceId, outcomes);

    // Schedule the next step, or finish. `completed_at` rather than deleting,
    // so re-enrolling someone is a deliberate act and not an accident of a
    // missing row.
    const [next] = await tx.execute<{ step_order: number; delay_seconds: number }>(sql`
      select step_order, delay_seconds from push_flow_steps
       where flow_id = ${row.flow_id} and step_order > ${row.step_order}
       order by step_order limit 1`);

    if (next) {
      await tx.execute(sql`
        update push_flow_progress
           set next_step = ${next.step_order},
               due_at = now() + (${next.delay_seconds} * interval '1 second'),
               updated_at = now()
         where id = ${row.progress_id}`);
    } else {
      await tx.execute(sql`
        update push_flow_progress set completed_at = now(), updated_at = now()
         where id = ${row.progress_id}`);
    }
  }

  return { advanced: due.length, sent };
}

/** Enrols a subscriber at step one of a flow. Idempotent. */
export async function enrol(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; flowId: string; subscriberId: string },
): Promise<boolean> {
  const [first] = await tx.execute<{ step_order: number; delay_seconds: number }>(sql`
    select step_order, delay_seconds from push_flow_steps
     where flow_id = ${opts.flowId} order by step_order limit 1`);
  if (!first) return false;

  const rows = await tx.execute<{ id: string }>(sql`
    insert into push_flow_progress
      (workspace_id, flow_id, subscriber_id, next_step, due_at)
    values (${opts.workspaceId}, ${opts.flowId}, ${opts.subscriberId}, ${first.step_order},
            now() + (${first.delay_seconds} * interval '1 second'))
    on conflict (flow_id, subscriber_id) do nothing
    returning id`);
  return rows.length > 0;
}

/* ------------------------------------------------------------------- RSS */

export type RssItem = { guid: string; title: string; summary: string; url: string };
export type FetchFeed = (url: string) => Promise<RssItem[]>;
export type RssResult = { checked: number; sent: number };

const fill = (tpl: string, item: RssItem) =>
  tpl.replace(/\{\{(\w+)\}\}/g, (_, k: string) => String((item as Record<string, string>)[k] ?? ''));

/**
 * Polls RSS feeds and notifies on genuinely new items.
 *
 * `last_guid` is the guard, not a timestamp: feeds re-order, re-publish and
 * back-date constantly, and a date comparison notifies subscribers about a
 * three-year-old post the moment someone fixes a typo in it.
 *
 * Only the newest unseen item is sent per poll. A feed that publishes forty
 * items at once should not produce forty notifications — that is how an origin
 * gets blocked.
 */
export async function pollRssAutomations(
  tx: WorkspaceScopedDb,
  opts: Runner & { workspaceId: string; fetchFeed: FetchFeed; limit?: number },
): Promise<RssResult> {
  const now = (opts.now ?? (() => new Date()))();

  const due = await tx.execute<{
    id: string; push_website_id: string; feed_url: string; last_guid: string | null;
    title_template: string; body_template: string; check_interval_minutes: number;
    vapid_public_key: string; vapid_private_key_encrypted: string;
  }>(sql`
    select r.id, r.push_website_id, r.feed_url, r.last_guid,
           r.title_template, r.body_template, r.check_interval_minutes,
           w.vapid_public_key, w.vapid_private_key_encrypted
      from push_rss_automations r
      join push_websites w on w.id = r.push_website_id
     where r.workspace_id = ${opts.workspaceId}
       and r.is_enabled and w.is_enabled
       and r.next_check_at <= ${now.toISOString()}::timestamptz
     order by r.next_check_at
     limit ${opts.limit ?? 20}
     for update of r skip locked`);

  if (due.length === 0) return { checked: 0, sent: 0 };

  // Claim before fetching: a slow feed must not be polled twice concurrently.
  await tx.execute(sql`
    update push_rss_automations
       set next_check_at = now() + (check_interval_minutes * interval '1 minute'),
           updated_at = now()
     where id in (${inList(due.map((d) => d.id))})`);

  let sent = 0;
  for (const feed of due) {
    let items: RssItem[] = [];
    try {
      items = await opts.fetchFeed(feed.feed_url);
    } catch {
      // A feed that is down is not an error worth failing the batch for; the
      // next poll picks it up.
      continue;
    }
    if (items.length === 0) continue;

    const newest = items[0]!;
    if (feed.last_guid === newest.guid) continue;

    /*
     * First poll records the position without notifying.
     *
     * Otherwise switching a feed on blasts subscribers with whatever happened
     * to be at the top of an archive they never asked about.
     */
    if (!feed.last_guid) {
      await tx.execute(sql`
        update push_rss_automations set last_guid = ${newest.guid}, updated_at = now()
         where id = ${feed.id}`);
      continue;
    }

    const audience = await audienceFor(tx, {
      workspaceId: opts.workspaceId,
      pushWebsiteId: feed.push_website_id,
    });

    if (audience.length > 0) {
      const vapid: Vapid = {
        publicKey: feed.vapid_public_key,
        privateKey: opts.decrypt(feed.vapid_private_key_encrypted),
        subject: opts.subject,
      };
      const outcomes = await (opts.sendAll ?? sendMany)(
        audience,
        {
          title: fill(feed.title_template, newest),
          body: fill(feed.body_template, newest),
          url: newest.url,
          tag: `rss-${feed.id}`,
        },
        vapid,
        {},
      );
      sent += summarise(outcomes).sent;
      await retireExpired(tx, opts.workspaceId, outcomes);
    }

    await tx.execute(sql`
      update push_rss_automations set last_guid = ${newest.guid}, updated_at = now()
       where id = ${feed.id}`);
  }

  return { checked: due.length, sent };
}
