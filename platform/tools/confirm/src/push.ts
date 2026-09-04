import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { inList } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import {
  generateVapidKeys, selectSubscribers, sendMany, summarise,
  type Notification, type SendOutcome, type Subscriber, type Vapid,
} from '@mamal/push';
import { ConfirmNotAllowed } from './service.ts';

/**
 * Push campaigns: choosing an audience, sending to it, and keeping the list
 * honest afterwards.
 *
 * The sending protocol lives in `@mamal/push`; this is the part that knows
 * about workspaces, entitlements and what to write down.
 */

export async function enablePush(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; projectId: string; siteId: string;
    encrypt: (plain: string) => string;
  },
): Promise<{ id: string; publicKey: string }> {
  const [existing] = await tx.execute<{ id: string; vapid_public_key: string }>(sql`
    select id, vapid_public_key from push_websites
     where site_id = ${opts.siteId} and workspace_id = ${opts.workspaceId}`);
  if (existing) return { id: existing.id, publicKey: existing.vapid_public_key };

  /*
   * A pair per site, not per platform.
   *
   * The public key is baked into every subscription a browser creates against
   * it, so a shared key could never be rotated — one compromise would mean
   * invalidating every subscriber on every customer site simultaneously.
   */
  const keys = generateVapidKeys();

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into push_websites
      (workspace_id, project_id, site_id, vapid_public_key, vapid_private_key_encrypted)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.siteId},
            ${keys.publicKey}, ${opts.encrypt(keys.privateKey)})
    returning id`);

  return { id: row!.id, publicKey: keys.publicKey };
}

/** Subscribers a campaign would reach, with its segment applied. */
export async function audienceFor(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; pushWebsiteId: string; segmentId?: string | null },
): Promise<Subscriber[]> {
  const filter = opts.segmentId
    ? (
        await tx.execute<{ filter: unknown }>(sql`
          select filter from push_segments
           where id = ${opts.segmentId} and workspace_id = ${opts.workspaceId}`)
      )[0]?.filter ?? {}
    : {};

  const rows = await tx.execute<{
    id: string; endpoint: string; p256dh: string; auth: string;
    country: string | null; browser: string | null; os: string | null;
    device: string | null; language: string | null; tags: string[];
    status: string; subscribed_at: string; last_seen_at: string | null;
  }>(sql`
    select id, endpoint, p256dh, auth, country, browser, os, device, language,
           tags, status, subscribed_at, last_seen_at
      from push_subscribers
     where push_website_id = ${opts.pushWebsiteId}
       and workspace_id = ${opts.workspaceId}
       -- Filtered here as well as in selectSubscribers: a campaign to 50,000
       -- people should not load 200,000 rows to discard three quarters.
       and status = 'active'`);

  return selectSubscribers(
    rows.map((r) => ({
      id: r.id, endpoint: r.endpoint, p256dh: r.p256dh, auth: r.auth,
      country: r.country, browser: r.browser, os: r.os, device: r.device,
      language: r.language, tags: r.tags ?? [], status: r.status,
      subscribedAt: r.subscribed_at, lastSeenAt: r.last_seen_at,
    })),
    filter,
  );
}

export type SendReport = {
  audience: number;
  sent: number;
  expired: number;
  failed: number;
  rateLimited: number;
};

/**
 * Sends a campaign and records what happened.
 *
 * Entitlements are checked against the *audience size*, not the subscriber
 * total: a plan caps how many people you can reach, and a campaign that would
 * exceed it is refused before a single notification goes out rather than
 * halfway through.
 */
export async function sendCampaign(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    campaignId: string;
    decrypt: (cipher: string) => string;
    subject: string;
    concurrency?: number;
    /**
     * The transport, injected like `decrypt` and `subject` above.
     *
     * Defaults to the real one. A worker can substitute a rate-limit-aware
     * sender without this module knowing, and a test can assert on audience
     * selection and list hygiene without a live push service — which is the
     * part worth testing here, since the encryption is `web-push`'s.
     */
    sendAll?: typeof sendMany;
  },
): Promise<SendReport> {
  const [campaign] = await tx.execute<{
    id: string; push_website_id: string; segment_id: string | null;
    title: string; body: string; icon_url: string | null; image_url: string | null;
    url: string | null; actions: { action: string; title: string }[];
    ttl_seconds: number; status: string;
    vapid_public_key: string; vapid_private_key_encrypted: string;
  }>(sql`
    select c.id, c.push_website_id, c.segment_id, c.title, c.body, c.icon_url, c.image_url,
           c.url, c.actions, c.ttl_seconds, c.status,
           w.vapid_public_key, w.vapid_private_key_encrypted
      from push_campaigns c
      join push_websites w on w.id = c.push_website_id
     where c.id = ${opts.campaignId} and c.workspace_id = ${opts.workspaceId}`);

  if (!campaign) throw new ConfirmNotAllowed('not_found', 'No such campaign.');
  if (campaign.status === 'sending' || campaign.status === 'sent') {
    // Claimed or done. Re-entering would double-send, and a person receiving
    // the same notification twice is the fastest route to a blocked origin.
    throw new ConfirmNotAllowed('already_sent', 'This campaign has already been sent.');
  }

  const audience = await audienceFor(tx, {
    workspaceId: opts.workspaceId,
    pushWebsiteId: campaign.push_website_id,
    segmentId: campaign.segment_id,
  });

  const ctx = await loadContext(tx, opts.workspaceId, 'confirm.push_subscribers');
  if (ctx) {
    const decision = resolve({ ...ctx, used: 0 }, audience.length);
    if (!decision.allowed) throw new ConfirmNotAllowed(decision.reason, decision.message);
  }

  // Claim it, so a retried job or a second worker cannot send it again.
  await tx.execute(sql`
    update push_campaigns set status = 'sending', updated_at = now()
     where id = ${campaign.id}`);

  const vapid: Vapid = {
    publicKey: campaign.vapid_public_key,
    privateKey: opts.decrypt(campaign.vapid_private_key_encrypted),
    subject: opts.subject,
  };

  const notification: Notification = {
    title: campaign.title,
    body: campaign.body,
    iconUrl: campaign.icon_url,
    imageUrl: campaign.image_url,
    url: campaign.url,
    actions: campaign.actions ?? [],
    tag: `campaign-${campaign.id}`,
    ttlSeconds: campaign.ttl_seconds,
  };

  const outcomes = await (opts.sendAll ?? sendMany)(audience, notification, vapid, {
    concurrency: opts.concurrency ?? 50,
  });
  const totals = summarise(outcomes);

  await retireExpired(tx, opts.workspaceId, outcomes);

  await tx.execute(sql`
    update push_campaigns
       set status = 'sent', sent_at = now(),
           sent = ${totals.sent}, failed = ${totals.failed + totals.rateLimited},
           updated_at = now()
     where id = ${campaign.id}`);

  return {
    audience: audience.length,
    sent: totals.sent,
    expired: totals.expired,
    failed: totals.failed,
    rateLimited: totals.rateLimited,
  };
}

/**
 * Retires subscriptions the push service says are gone.
 *
 * Not optional housekeeping: without it every campaign re-attempts the same
 * dead endpoints forever, each send gets slower, and the delivery rate shown to
 * a customer is measured against people who no longer exist.
 */
export async function retireExpired(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  outcomes: SendOutcome[],
): Promise<number> {
  const gone = outcomes.filter((o) => o.status === 'expired').map((o) => o.id);
  if (gone.length === 0) return 0;

  await tx.execute(sql`
    update push_subscribers set status = 'expired', updated_at = now()
     where workspace_id = ${workspaceId} and id in (${inList(gone)})`);
  return gone.length;
}
