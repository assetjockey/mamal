/**
 * Composing and scheduling social posts.
 *
 * One post, many targets. The body lives on the post and per-network changes
 * live on the target, which is what makes "the same announcement, shorter on X"
 * one thing to edit rather than five — and why fixing the text for X does not
 * silently un-schedule the four networks that were already fine.
 *
 * Four rules run through this module:
 *
 * **Refuse at compose time, not at publish time.** A post queued at 09:00 and
 * rejected by Instagram at 14:00 for having no image is the defining complaint
 * about tools in this category. `validatePost` runs here, before anything is
 * scheduled, and its errors block.
 *
 * **Link shortening is a favour, not a dependency.** Market asks Link to
 * shorten outbound URLs so every post is measurable, but Link may not be
 * installed. The handoff is injected and its absence is recorded on the post
 * rather than thrown — the whole point of the manifest boundary.
 *
 * **A post's status is derived from its targets.** Publishing to five networks
 * succeeds four times and fails once, routinely. A single stored status would
 * either lie or lose the four that worked.
 *
 * **Approval gates the queue, not the composer.** A post awaiting review is
 * scheduled and visible on the calendar; it simply is not claimable until
 * somebody says yes.
 */
import { sql } from 'drizzle-orm';
import { uuidArray, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { mint } from '@mamal/resources';
import { MarketNotAllowed } from './service.ts';
import { canSchedule, validatePost, type Problem } from './networks.ts';
import { cleanSlots, defaultSlots, nextSlot, type Slots } from './queue.ts';

/** Turns an outbound URL into a tracked short link. Absent when Link is not installed. */
export type ShortenLink = (input: {
  url: string;
  campaign?: string;
}) => Promise<{ linkId: string; shortUrl: string } | null>;

export type SocialDeps = { shorten?: ShortenLink };

export type ComposeInput = {
  workspaceId: string;
  projectId: string;
  body: string;
  accountIds: string[];
  title?: string;
  link?: string | null;
  mediaAssetIds?: string[];
  images?: number;
  videos?: number;
  altText?: string[];
  firstComment?: string;
  campaign?: string;
  scheduleType?: 'now' | 'scheduled' | 'queue';
  scheduledAt?: Date;
  /** When the workspace requires review, the post lands as `pending`. */
  requireApproval?: boolean;
  batchId?: string;
};

export type ComposeResult = {
  postId: string;
  problems: Problem[];
  /** When each network will go out. Empty for `now`. */
  scheduled: { accountId: string; provider: string; at: string | null }[];
  /** Set when a URL could not be shortened, with the reason. */
  linkNote: string | null;
};

export async function composePost(
  tx: WorkspaceScopedDb,
  input: ComposeInput,
  deps: SocialDeps = {},
): Promise<ComposeResult> {
  if (input.accountIds.length === 0) {
    throw new MarketNotAllowed('invalid', 'Pick at least one account to post to.');
  }

  const accounts = await tx.execute<{
    id: string; provider: string; display_name: string;
  }>(sql`
    select id, provider, display_name from social_accounts
     where project_id = ${input.projectId}
       and id = any(${uuidArray(input.accountIds)}::uuid[])`);

  if (accounts.length !== input.accountIds.length) {
    throw new MarketNotAllowed(
      'not_found',
      'One of those accounts is no longer connected. Reconnect it or remove it from the post.',
    );
  }

  /* -- 1. would every network take this? -------------------------------- */

  const problems = validatePost(
    {
      body: input.body,
      title: input.title,
      images: input.images ?? 0,
      videos: input.videos ?? 0,
      link: input.link,
      altText: input.altText,
      firstComment: input.firstComment,
    },
    accounts.map((a) => a.provider),
  );

  if (!canSchedule(problems)) {
    const blocking = problems.filter((p) => p.level === 'error');
    throw new MarketNotAllowed(
      'invalid',
      // Every problem, not the first: fixing five in five round trips is how
      // people stop using a scheduler.
      blocking.map((p) => p.message).join(' '),
    );
  }

  /* -- 2. the allowance, counted once for the whole post ---------------- */

  await requireHeadroom(
    tx,
    input.workspaceId,
    'market.scheduled_posts',
    sql`select count(*)::int as count from social_posts
         where project_id = ${input.projectId}
           and deleted_at is null
           and created_at >= date_trunc('month', now())`,
  );

  /* -- 3. shorten the outbound link, if Link is here -------------------- */

  let body = input.body;
  let linkId: string | null = null;
  let linkNote: string | null = null;

  if (input.link) {
    if (deps.shorten) {
      const shortened = await deps.shorten({ url: input.link, campaign: input.campaign });
      if (shortened) {
        linkId = shortened.linkId;
        body = body.split(input.link).join(shortened.shortUrl);
      } else {
        linkNote = 'The link could not be shortened, so the post carries the original URL.';
      }
    } else {
      /*
       * Link is not installed. Degrade, never throw — the post still goes out,
       * it simply is not measurable through Link's analytics. Recorded so the
       * customer is not left wondering why clicks are missing.
       */
      linkNote = 'Link is not installed, so clicks on this post will not be tracked.';
    }
  }

  /* -- 4. the post, then one target per account ------------------------- */

  const scheduleType = input.scheduleType ?? 'now';
  const approvalState = input.requireApproval ? 'pending' : 'none';

  const [post] = await tx.execute<{ id: string }>(sql`
    insert into social_posts
      (workspace_id, project_id, body, media_asset_ids, link_id, status,
       schedule_type, scheduled_at, approval_state, campaign, first_comment, batch_id)
    values (${input.workspaceId}, ${input.projectId}, ${body},
            ${uuidArray(input.mediaAssetIds ?? [])}::uuid[], ${linkId},
            ${scheduleType === 'now' && !input.requireApproval ? 'scheduled' : 'draft'},
            ${scheduleType}, ${input.scheduledAt?.toISOString() ?? null}::timestamptz,
            ${approvalState},
            ${input.campaign ?? null}, ${input.firstComment ?? null},
            ${input.batchId ?? null})
    returning id`);

  const postId = post!.id;
  const scheduled: ComposeResult['scheduled'] = [];

  for (const account of accounts) {
    const at = await runAtFor(tx, {
      accountId: account.id,
      scheduleType,
      scheduledAt: input.scheduledAt,
    });

    await tx.execute(sql`
      insert into social_targets (workspace_id, post_id, account_id, status, next_run_at)
      values (${input.workspaceId}, ${postId}, ${account.id}, 'pending',
              ${at?.toISOString() ?? null}::timestamptz)`);

    scheduled.push({
      accountId: account.id,
      provider: account.provider,
      at: at ? at.toISOString() : null,
    });
  }

  // `scheduled` on the post is derived from the targets, so a queue that could
  // not place one network does not make the whole post look scheduled.
  await refreshPostStatus(tx, postId);

  await mint(tx, {
    workspaceId: input.workspaceId,
    projectId: input.projectId,
    tool: 'market',
    type: 'social_post',
    externalId: postId,
    label: body.slice(0, 80) || 'Post',
  });

  return { postId, problems, scheduled, linkNote };
}

/**
 * When this account should post.
 *
 * `now` is immediate, `scheduled` is the time the writer picked, and `queue`
 * asks the account's own grid — which is why it is resolved per account rather
 * than once for the post: two accounts can have different grids and different
 * timezones, and forcing them to share one is the reason "queue" feels useless
 * in most tools.
 */
async function runAtFor(
  tx: WorkspaceScopedDb,
  opts: { accountId: string; scheduleType: string; scheduledAt?: Date },
): Promise<Date | null> {
  if (opts.scheduleType === 'now') return new Date();
  if (opts.scheduleType === 'scheduled') return opts.scheduledAt ?? new Date();

  const [queue] = await tx.execute<{ slots: Slots; timezone: string }>(sql`
    select slots, timezone from social_queues where account_id = ${opts.accountId}`);

  const slots = queue?.slots ?? defaultSlots();
  const timezone = queue?.timezone ?? 'UTC';

  const taken = await tx.execute<{ next_run_at: string }>(sql`
    select next_run_at from social_targets
     where account_id = ${opts.accountId} and status = 'pending' and next_run_at is not null`);

  return nextSlot({
    slots,
    timezone,
    from: new Date(),
    taken: taken.map((t) => new Date(t.next_run_at)),
  });
}

/* ------------------------------------------------------------- approval */

export async function setApproval(
  tx: WorkspaceScopedDb,
  opts: {
    projectId: string;
    postId: string;
    state: 'approved' | 'rejected';
    userId: string;
  },
): Promise<void> {
  await tx.execute(sql`
    update social_posts
       set approval_state = ${opts.state},
           approved_by = ${opts.userId},
           -- Rejecting cancels: leaving it scheduled means the reviewer's "no"
           -- is a note that the scheduler ignores an hour later.
           status = ${opts.state === 'rejected' ? 'cancelled' : sql`status`},
           updated_at = now()
     where id = ${opts.postId} and project_id = ${opts.projectId}`);

  if (opts.state === 'rejected') {
    await tx.execute(sql`
      update social_targets set status = 'skipped', next_run_at = null
       where post_id = ${opts.postId} and status = 'pending'`);
  }

  await refreshPostStatus(tx, opts.postId);
}

/* ------------------------------------------------------------ publishing */

export type DueTarget = {
  targetId: string;
  postId: string;
  workspaceId: string;
  accountId: string;
  provider: string;
  externalId: string;
  body: string;
  firstComment: string | null;
  mediaAssetIds: string[];
  overrides: Record<string, unknown>;
  connectionId: string;
};

/**
 * Targets due to publish, claimed.
 *
 * Claimed per *target*, not per post, so a rate-limited network retries on its
 * own clock without holding up the four that are ready. A post still awaiting
 * approval is invisible here — the review is what makes it claimable.
 */
export async function claimDueTargets(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<DueTarget[]> {
  const rows = await tx.execute<{
    id: string; post_id: string; workspace_id: string; account_id: string;
    provider: string; external_id: string; connection_id: string;
    body: string; first_comment: string | null; media_asset_ids: string[];
    overrides: Record<string, unknown>;
  }>(sql`
    with claimed as (
      select t.id
        from social_targets t
        join social_posts p on p.id = t.post_id
       where t.status = 'pending'
         and t.next_run_at is not null and t.next_run_at <= now()
         and p.deleted_at is null
         and p.status <> 'cancelled'
         -- 'none' means review was never required; 'approved' means it passed.
         and p.approval_state in ('none', 'approved')
       order by t.next_run_at
       limit ${opts.limit ?? 50}
       for update skip locked
    )
    update social_targets t
       set status = 'publishing', attempts = t.attempts + 1, updated_at = now()
      from claimed
     where t.id = claimed.id
    returning t.id, t.post_id, t.workspace_id, t.account_id, t.overrides,
              (select provider from social_accounts a where a.id = t.account_id) as provider,
              (select external_id from social_accounts a where a.id = t.account_id) as external_id,
              (select connection_id from social_accounts a where a.id = t.account_id) as connection_id,
              (select body from social_posts p where p.id = t.post_id) as body,
              (select first_comment from social_posts p where p.id = t.post_id) as first_comment,
              (select media_asset_ids from social_posts p where p.id = t.post_id) as media_asset_ids`);

  return rows.map((r) => ({
    targetId: r.id,
    postId: r.post_id,
    workspaceId: r.workspace_id,
    accountId: r.account_id,
    provider: r.provider,
    externalId: r.external_id,
    connectionId: r.connection_id,
    body: r.body,
    firstComment: r.first_comment,
    mediaAssetIds: r.media_asset_ids ?? [],
    overrides: r.overrides ?? {},
  }));
}

/** How many attempts before a target is left alone. */
export const MAX_ATTEMPTS = 3;

export async function recordPublished(
  tx: WorkspaceScopedDb,
  opts: { targetId: string; postId: string; remoteId: string; remoteUrl: string | null },
): Promise<void> {
  await tx.execute(sql`
    update social_targets
       set status = 'published', remote_id = ${opts.remoteId}, remote_url = ${opts.remoteUrl},
           error = null, published_at = now(), next_run_at = null, updated_at = now()
     where id = ${opts.targetId}`);
  await refreshPostStatus(tx, opts.postId);
}

/**
 * A target that failed, and whether it will be tried again.
 *
 * `retryable` comes from the caller because only the transport knows: a 503 is
 * worth another go, a rejected caption is not, and retrying the second one
 * three times just posts the same failure to the customer's log three times.
 */
export async function recordFailure(
  tx: WorkspaceScopedDb,
  opts: {
    targetId: string;
    postId: string;
    message: string;
    retryable: boolean;
    retryAfterSeconds?: number;
  },
): Promise<{ willRetry: boolean }> {
  const [row] = await tx.execute<{ attempts: number }>(sql`
    select attempts from social_targets where id = ${opts.targetId}`);
  const attempts = row?.attempts ?? MAX_ATTEMPTS;
  const willRetry = opts.retryable && attempts < MAX_ATTEMPTS;

  await tx.execute(sql`
    update social_targets
       set status = ${willRetry ? 'pending' : 'failed'},
           error = ${opts.message},
           next_run_at = ${
             willRetry
               ? sql`now() + (${opts.retryAfterSeconds ?? 300} * interval '1 second')`
               : null
           },
           updated_at = now()
     where id = ${opts.targetId}`);

  await refreshPostStatus(tx, opts.postId);
  return { willRetry };
}

/**
 * The post's status, computed from its targets.
 *
 * Never set directly. Five networks routinely produce four successes and one
 * failure, and a stored post-level status would have to pick one of those to
 * report — which means lying about the other four.
 */
export async function refreshPostStatus(
  tx: WorkspaceScopedDb,
  postId: string,
): Promise<string> {
  const [counts] = await tx.execute<{
    total: number; published: number; failed: number; pending: number; publishing: number;
  }>(sql`
    select count(*)::int as total,
           count(*) filter (where status = 'published')::int as published,
           count(*) filter (where status = 'failed')::int as failed,
           count(*) filter (where status = 'pending')::int as pending,
           count(*) filter (where status = 'publishing')::int as publishing
      from social_targets where post_id = ${postId}`);

  const [post] = await tx.execute<{ approval_state: string; status: string }>(sql`
    select approval_state, status from social_posts where id = ${postId}`);
  if (!post || post.status === 'cancelled') return post?.status ?? 'cancelled';

  const c = counts ?? { total: 0, published: 0, failed: 0, pending: 0, publishing: 0 };

  let status: string;
  if (c.total === 0) status = 'draft';
  else if (c.publishing > 0) status = 'publishing';
  else if (c.pending > 0) status = post.approval_state === 'pending' ? 'draft' : 'scheduled';
  // Everything settled: published if any network took it. A post that reached
  // four of five audiences is published *and* partly failed, and the target
  // rows carry the detail.
  else if (c.published > 0) status = 'published';
  else status = 'failed';

  await tx.execute(sql`
    update social_posts set status = ${status}, updated_at = now() where id = ${postId}`);
  return status;
}

/* --------------------------------------------------------------- reading */

export async function listPosts(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; status?: string; limit?: number },
): Promise<{
  id: string; body: string; status: string; scheduleType: string; scheduledAt: string | null;
  approvalState: string; campaign: string | null;
  targets: { provider: string; displayName: string; status: string; error: string | null; url: string | null; at: string | null }[];
}[]> {
  const posts = await tx.execute<{
    id: string; body: string; status: string; schedule_type: string;
    scheduled_at: string | null; approval_state: string; campaign: string | null;
  }>(sql`
    select id, body, status, schedule_type, scheduled_at::text, approval_state, campaign
      from social_posts
     where project_id = ${opts.projectId} and deleted_at is null
       ${opts.status ? sql`and status = ${opts.status}` : sql``}
     order by coalesce(scheduled_at, created_at) desc
     limit ${opts.limit ?? 100}`);

  if (posts.length === 0) return [];

  const targets = await tx.execute<{
    post_id: string; provider: string; display_name: string; status: string;
    error: string | null; remote_url: string | null; next_run_at: string | null;
    published_at: string | null;
  }>(sql`
    select t.post_id, a.provider, a.display_name, t.status, t.error, t.remote_url,
           t.next_run_at::text, t.published_at::text
      from social_targets t
      join social_accounts a on a.id = t.account_id
     where t.post_id = any(${uuidArray(posts.map((p) => p.id))}::uuid[])
     order by a.provider`);

  return posts.map((p) => ({
    id: p.id,
    body: p.body,
    status: p.status,
    scheduleType: p.schedule_type,
    scheduledAt: p.scheduled_at,
    approvalState: p.approval_state,
    campaign: p.campaign,
    targets: targets
      .filter((t) => t.post_id === p.id)
      .map((t) => ({
        provider: t.provider,
        displayName: t.display_name,
        status: t.status,
        error: t.error,
        url: t.remote_url,
        at: t.published_at ?? t.next_run_at,
      })),
  }));
}

export async function saveQueue(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    accountId: string;
    slots: Record<string, unknown>;
    timezone?: string;
  },
): Promise<void> {
  await tx.execute(sql`
    insert into social_queues (workspace_id, account_id, slots, timezone)
    values (${opts.workspaceId}, ${opts.accountId},
            ${JSON.stringify(cleanSlots(opts.slots))}::jsonb, ${opts.timezone ?? 'UTC'})
    on conflict on constraint social_queues_account do update
       set slots = excluded.slots, timezone = excluded.timezone, updated_at = now()`);
}

export async function listAccounts(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<{
  id: string; provider: string; handle: string | null; displayName: string;
  followers: number | null; slots: Slots | null; timezone: string; queued: number;
}[]> {
  const rows = await tx.execute<{
    id: string; provider: string; handle: string | null; display_name: string;
    followers: number | null; slots: Slots | null; timezone: string | null; queued: number;
  }>(sql`
    select a.id, a.provider, a.handle, a.display_name, a.followers,
           q.slots, q.timezone,
           (select count(*)::int from social_targets t
             where t.account_id = a.id and t.status = 'pending') as queued
      from social_accounts a
      left join social_queues q on q.account_id = a.id
     where a.project_id = ${opts.projectId}
     order by a.provider, a.display_name`);

  return rows.map((r) => ({
    id: r.id,
    provider: r.provider,
    handle: r.handle,
    displayName: r.display_name,
    followers: r.followers,
    slots: r.slots,
    timezone: r.timezone ?? 'UTC',
    queued: r.queued,
  }));
}

async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  countSql: ReturnType<typeof sql>,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const [counted] = await tx.execute<{ count: number }>(countSql);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}

