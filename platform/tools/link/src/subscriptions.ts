import { sql } from 'drizzle-orm';
import type { Envelope, Handler } from '@mamal/bus';
import type { WorkspaceScopedDb } from '@mamal/db';

/**
 * What Link listens for.
 *
 * One of these is the loop none of the source products could do, and it is the
 * clearest argument for the whole platform: **Monitor watches a link's
 * destination, and Link fails the link over while it is down.** Five separate
 * products cannot do that, no matter how good each one is, because neither
 * knows the other exists.
 */

/** The fallback a failover sends people to while the real target is down. */
type Failover = { previous: string | null; since: string };

async function linksPointingAt(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  targetUrl: string,
): Promise<{ id: string; destination_url: string | null }[]> {
  /*
   * Matched on origin, not on the exact string.
   *
   * A monitor watches `https://shop.example.com/` and the link points at
   * `https://shop.example.com/spring-sale?ref=ig`. Comparing the full URLs
   * would fail over nothing, which is the same as not shipping the feature.
   */
  let origin: string;
  try {
    origin = new URL(targetUrl).origin;
  } catch {
    return [];
  }

  return tx.execute<{ id: string; destination_url: string | null }>(sql`
    select id, destination_url from links
     where workspace_id = ${workspaceId}
       and deleted_at is null
       and is_enabled
       and destination_url is not null
       and destination_url like ${origin + '%'}`);
}

export const linkSubscriptions: Handler[] = [
  {
    key: 'link:failover-while-target-down',
    event: 'monitor.incident.opened',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { targetUrl?: string; fallbackUrl?: string };
      if (!data.targetUrl || !data.fallbackUrl) return;

      const db = tx as WorkspaceScopedDb;
      const affected = await linksPointingAt(db, envelope.workspaceId, data.targetUrl);

      for (const link of affected) {
        /*
         * The original destination is stashed in settings, not overwritten.
         *
         * Recovery has to put it back *exactly*, and there is no other copy: a
         * failover that loses the real destination has broken the link
         * permanently in order to keep it working for an hour.
         */
        const failover: Failover = { previous: link.destination_url, since: envelope.occurredAt };
        await db.execute(sql`
          update links
             set destination_url = ${data.fallbackUrl},
                 settings = settings || ${JSON.stringify({ failover })}::jsonb,
                 updated_at = now()
           where id = ${link.id}
             and not (settings ? 'failover')`);
      }
    },
  },

  {
    key: 'link:restore-after-recovery',
    event: 'monitor.target.recovered',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { targetUrl?: string };
      if (!data.targetUrl) return;
      const db = tx as WorkspaceScopedDb;

      /*
       * Restores from the stash, and only where a stash exists.
       *
       * A recovery event that arrives twice — which the bus guarantees can
       * happen — must not overwrite a destination the customer edited in the
       * meantime, so the second delivery finds no `failover` key and does
       * nothing.
       */
      await db.execute(sql`
        update links
           set destination_url = settings->'failover'->>'previous',
               settings = settings - 'failover',
               updated_at = now()
         where workspace_id = ${envelope.workspaceId}
           and settings ? 'failover'
           and settings->'failover'->>'previous' is not null
           and settings->'failover'->>'previous' like ${new URL(data.targetUrl).origin + '%'}`);
    },
  },

  {
    key: 'link:offer-managed-link-for-broken-external',
    event: 'audit.issue.detected',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { ruleId?: string; targetUrl?: string; pageUrl?: string };
      if (data.ruleId !== 'broken-external-link' || !data.targetUrl) return;

      /*
       * Records a suggestion; does not create a link.
       *
       * Creating one would spend the customer's link allowance on a decision
       * they have not made, and a large site can have hundreds of broken
       * external links. The suggestion surfaces in the issue's fix panel with
       * one button, which is where the choice belongs.
       */
      await (tx as WorkspaceScopedDb).execute(sql`
        insert into link_suggestions
          (workspace_id, kind, target_url, context_url, source_urn)
        values (${envelope.workspaceId}, 'replace_broken_external',
                ${data.targetUrl}, ${data.pageUrl ?? null}, ${envelope.subject})
        on conflict (workspace_id, kind, target_url) do nothing`);
    },
  },
];

/**
 * Link's retention sweep.
 *
 * Three things go, and one deliberately does not.
 *
 * **A/B assignments** are keyed on a daily-rotating visitor hash that stops
 * being meaningful the day after it is minted, so an expired row is pure
 * liability. **Abuse reports** carry a stranger's email address. **Expired
 * transfers** carry filenames and recipient addresses.
 *
 * **Links themselves are never swept by age.** A short link on printed material
 * outlives any retention window, and deleting it would turn a customer's
 * business card into a 404 — the one failure a link shortener must not have.
 */
export const linkSweeper: {
  key: string;
  sweep: (tx: WorkspaceScopedDb, workspaceId: string, cutoff: Date) => Promise<number>;
} = {
  key: 'link_ephemera',
  sweep: async (tx, workspaceId, cutoff) => {
    const iso = cutoff.toISOString();
    let removed = 0;

    // Assignments go by their own expiry, which is always sooner.
    const assignments = await tx.execute(sql`
      delete from link_assignments
       where workspace_id = ${workspaceId} and expires_at < now()
      returning id`);
    removed += assignments.length;

    const reports = await tx.execute(sql`
      delete from abuse_reports
       where workspace_id = ${workspaceId}
         and status <> 'open'
         and created_at < ${iso}::timestamptz
      returning id`);
    removed += reports.length;

    const transfers = await tx.execute(sql`
      delete from transfers
       where workspace_id = ${workspaceId}
         and status = 'expired'
         and updated_at < ${iso}::timestamptz
      returning id`);
    removed += transfers.length;

    return removed;
  },
};
