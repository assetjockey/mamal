/**
 * Sending a notification once.
 *
 * The transports are many and mostly boring; the part that matters — and the
 * part every source product gets wrong — is **not sending the same thing
 * forty times**. One monitor going down produces a check failure every thirty
 * seconds, and a naive alerting path turns a single outage into a Slack channel
 * nobody can read and a phone nobody picks up. After that the tool is off.
 *
 * So the shape here is: resolve the channels, claim a `dedupe_key` per channel,
 * and only then send. The claim is a unique constraint
 * (`notification_deliveries_dedupe_key` on `channel_id, dedupe_key`), so the
 * guarantee survives two schedulers, a retried job and a restart — not just a
 * check in application code.
 *
 * The transports themselves are injected. That keeps this testable without a
 * network, and it means an operator who has only wired up email is not blocked
 * by the fifteen they have not.
 */
import { sql } from 'drizzle-orm';
import { uuidArray, type WorkspaceScopedDb } from '@mamal/db';

/** The seventeen the plan names. Adding one is a row and a transport. */
export const TRANSPORTS = [
  'email', 'webhook', 'slack', 'discord', 'telegram', 'teams', 'google_chat',
  'matrix', 'flock', 'ntfy', 'gotify', 'pushover', 'sms', 'voice', 'whatsapp',
  'pagerduty', 'opsgenie',
] as const;

export type Transport = (typeof TRANSPORTS)[number];

export type Message = {
  /** Which template — `monitor.down`, `audit.score_dropped`. */
  templateKey: string;
  subject: string;
  body: string;
  /** A URL the reader should open. */
  url?: string;
  /** low | normal | urgent — some transports rank by it, most ignore it. */
  urgency?: 'low' | 'normal' | 'urgent';
  data?: Record<string, unknown>;
};

export type Channel = {
  id: string;
  transport: string;
  name: string;
  /** Whatever the transport needs — a webhook URL, an address, a token. */
  config: Record<string, unknown>;
};

export type SendOutcome =
  | { ok: true }
  | { ok: false; retryable: boolean; message: string };

/** Hands a message to one channel. Injected, so nothing here needs a network. */
export type Sender = (channel: Channel, message: Message) => Promise<SendOutcome>;

export type NotifyResult = {
  sent: number;
  /** Suppressed because this exact thing already went to that channel. */
  duplicates: number;
  failed: { channel: string; message: string; retryable: boolean }[];
  /** Channels that exist but have no transport wired up on this instance. */
  unsupported: string[];
};

/**
 * How many consecutive failures before a channel is treated as broken.
 *
 * A webhook whose endpoint has been deleted should stop being retried and start
 * being *reported*, or it silently eats every alert while looking configured.
 */
export const FAILURES_BEFORE_BROKEN = 5;

/**
 * Sends one message to a set of channels, at most once each.
 *
 * `dedupeKey` is the whole design. For an incident it is the incident id plus
 * the escalation level, so:
 *
 *   - the same incident does not alert twice at the same level,
 *   - escalating to level 2 *does* alert again,
 *   - a retried job re-uses the row rather than sending a second message.
 */
export async function notify(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    channelIds: string[];
    message: Message;
    dedupeKey: string;
    eventId?: string;
  },
  send: Sender,
): Promise<NotifyResult> {
  const result: NotifyResult = { sent: 0, duplicates: 0, failed: [], unsupported: [] };
  if (opts.channelIds.length === 0) return result;

  const channels = await tx.execute<{
    id: string; transport: string; name: string; config: string; failure_count: number;
  }>(sql`
    select id, transport, name, config, failure_count
      from notification_channels
     where workspace_id = ${opts.workspaceId}
       and id = any(${uuidArray(opts.channelIds)}::uuid[])
       and is_enabled`);

  for (const row of channels) {
    /*
     * Claim first, send second.
     *
     * `on conflict do nothing returning id` and not a pre-check: two schedulers
     * both reading "no delivery yet" and both sending is exactly the race this
     * exists to prevent, and only the database can settle it. A constraint
     * violation inside a transaction would also abort the whole thing, which is
     * why this is `do nothing` rather than a caught error.
     */
    const [claimed] = await tx.execute<{ id: string }>(sql`
      insert into notification_deliveries
        (workspace_id, channel_id, template_key, dedupe_key, event_id, status, attempts)
      values (${opts.workspaceId}, ${row.id}, ${opts.message.templateKey},
              ${opts.dedupeKey}, ${opts.eventId ?? null}, 'sending', 1)
      on conflict on constraint notification_deliveries_dedupe_key do nothing
      returning id`);

    if (!claimed) {
      // Somebody already sent this. Not an error — the point of the exercise.
      result.duplicates += 1;
      continue;
    }

    const channel: Channel = {
      id: row.id,
      transport: row.transport,
      name: row.name,
      config: parseConfig(row.config),
    };

    let outcome: SendOutcome;
    try {
      outcome = await send(channel, opts.message);
    } catch (err) {
      outcome = {
        ok: false,
        retryable: true,
        message: err instanceof Error ? err.message : String(err),
      };
    }

    if (outcome.ok) {
      await tx.execute(sql`
        update notification_deliveries
           set status = 'sent', sent_at = now(), error = null, updated_at = now()
         where id = ${claimed.id}`);
      await tx.execute(sql`
        update notification_channels
           set failure_count = 0, last_error = null, updated_at = now()
         where id = ${row.id}`);
      result.sent += 1;
      continue;
    }

    if (/no transport/i.test(outcome.message)) result.unsupported.push(row.transport);

    await tx.execute(sql`
      update notification_deliveries
         set status = ${outcome.retryable ? 'failed' : 'rejected'},
             error = ${outcome.message}, updated_at = now()
       where id = ${claimed.id}`);

    /*
     * A failure that will not fix itself counts against the channel. A
     * transient one does not: an endpoint having a bad minute should not
     * accumulate towards being marked broken.
     */
    if (!outcome.retryable) {
      await tx.execute(sql`
        update notification_channels
           set failure_count = failure_count + 1, last_error = ${outcome.message},
               updated_at = now()
         where id = ${row.id}`);
    }

    result.failed.push({
      channel: row.name,
      message: outcome.message,
      retryable: outcome.retryable,
    });
  }

  return result;
}

/**
 * Lets a suppressed notification through again.
 *
 * Used when an incident escalates or reopens: the dedupe key changes, so this
 * is rarely needed — but a customer who fixed a broken webhook and wants the
 * alert re-sent has no other way, and telling them "it was deduped, nothing we
 * can do" is not an answer.
 */
export async function clearDedupe(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; dedupeKey: string },
): Promise<number> {
  const rows = await tx.execute<{ id: string }>(sql`
    delete from notification_deliveries
     where workspace_id = ${opts.workspaceId} and dedupe_key = ${opts.dedupeKey}
       and status <> 'sent'
    returning id`);
  return rows.length;
}

/** Channels that have failed enough times to need somebody's attention. */
export async function brokenChannels(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<{ id: string; name: string; transport: string; failures: number; lastError: string | null }[]> {
  const rows = await tx.execute<{
    id: string; name: string; transport: string; failure_count: number; last_error: string | null;
  }>(sql`
    select id, name, transport, failure_count, last_error
      from notification_channels
     where project_id = ${opts.projectId} and failure_count >= ${FAILURES_BEFORE_BROKEN}
     order by failure_count desc`);

  return rows.map((r) => ({
    id: r.id,
    name: r.name,
    transport: r.transport,
    failures: r.failure_count,
    lastError: r.last_error,
  }));
}

export async function listChannels(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<{ id: string; name: string; transport: string; isEnabled: boolean; failures: number }[]> {
  const rows = await tx.execute<{
    id: string; name: string; transport: string; is_enabled: boolean; failure_count: number;
  }>(sql`
    select id, name, transport, is_enabled, failure_count
      from notification_channels where project_id = ${opts.projectId}
     order by name`);

  return rows.map((r) => ({
    id: r.id,
    name: r.name,
    transport: r.transport,
    isEnabled: r.is_enabled,
    failures: r.failure_count,
  }));
}

export async function saveChannel(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    id?: string;
    transport: string;
    name: string;
    config: Record<string, unknown>;
  },
): Promise<string> {
  if (!TRANSPORTS.includes(opts.transport as Transport)) {
    throw new Error(`${opts.transport} is not a transport we know about`);
  }

  if (opts.id) {
    await tx.execute(sql`
      update notification_channels
         set name = ${opts.name}, config = ${JSON.stringify(opts.config)},
             -- Editing a broken channel is somebody saying "I fixed it", so the
             -- failure count starts again rather than staying broken forever.
             failure_count = 0, last_error = null, updated_at = now()
       where id = ${opts.id} and project_id = ${opts.projectId}`);
    return opts.id;
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into notification_channels (workspace_id, project_id, transport, name, config)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.transport}, ${opts.name},
            ${JSON.stringify(opts.config)})
    returning id`);
  return row!.id;
}

function parseConfig(raw: string): Record<string, unknown> {
  try {
    const parsed: unknown = JSON.parse(raw);
    return parsed && typeof parsed === 'object' ? (parsed as Record<string, unknown>) : {};
  } catch {
    // A channel whose config will not parse is a configuration error, not a
    // reason to abandon the other channels on the same incident.
    return {};
  }
}

