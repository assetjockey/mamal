import { sql } from 'drizzle-orm';
import type { Envelope, Handler } from '@mamal/bus';
import type { WorkspaceScopedDb } from '@mamal/db';
import { recordConversion } from './service.ts';

/**
 * What Confirm listens for.
 *
 * The brief calls out a dependency inversion here and it is worth restating:
 * Confirm's best feature — proof from *real* conversions — needs Track, which
 * is Phase 6. These handlers are registered now and simply never fire until
 * something publishes. That is the point of the bus, and G3 showed why they
 * must be tested through it rather than by direct call: a subscription to an
 * event name the envelope schema rejects looks correct and can never fire.
 */

/** Finds the campaign attached to a site, if Confirm is set up for it. */
async function campaignForSite(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  siteUrn: string,
): Promise<string | null> {
  const externalId = siteUrn.split(':').pop();
  if (!externalId) return null;
  const [row] = await tx.execute<{ id: string }>(sql`
    select c.id from confirm_campaigns c
     where c.workspace_id = ${workspaceId} and c.site_id = ${externalId}::uuid
       and c.is_enabled
     limit 1`);
  return row?.id ?? null;
}

export const confirmSubscriptions: Handler[] = [
  {
    key: 'confirm:goal-becomes-proof',
    event: 'track.goal.converted',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as {
        siteUrn?: string; goalKey?: string; city?: string; country?: string;
      };
      if (!data.siteUrn) return;

      const campaignId = await campaignForSite(tx as WorkspaceScopedDb, envelope.workspaceId, data.siteUrn);
      if (!campaignId) return;

      /*
       * This is the honest version of what the source product did.
       *
       * `66socialproof` let an operator type conversions in by hand and show
       * them as if they had just happened. Here a proof line exists only
       * because Track saw a real goal completion, and it carries `sourceUrn` so
       * it can be traced back to the event that caused it.
       */
      await recordConversion(tx as WorkspaceScopedDb, {
        workspaceId: envelope.workspaceId,
        campaignId,
        source: 'bus',
        type: data.goalKey ?? 'converted',
        data: { city: data.city },
        country: data.country,
        sourceUrn: envelope.subject,
      });
    },
  },

  {
    key: 'confirm:incident-shows-status-bar',
    event: 'monitor.incident.opened',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { siteUrn?: string; title?: string };
      if (!data.siteUrn) return;
      const campaignId = await campaignForSite(tx as WorkspaceScopedDb, envelope.workspaceId, data.siteUrn);
      if (!campaignId) return;

      // Raised disabled-by-default is wrong here: the point is that it appears
      // while the site is degraded. It is retired by the resolved handler.
      await (tx as WorkspaceScopedDb).execute(sql`
        insert into confirm_widgets
          (workspace_id, campaign_id, type, name, settings, position, display_frequency, duration_seconds)
        values (${envelope.workspaceId}, ${campaignId}, 'informational_bar',
                ${'Incident: ' + (data.title ?? 'service disruption')},
                ${JSON.stringify({
                  title: data.title ?? 'We are aware of an issue and working on it.',
                  sticky: true,
                })}::jsonb,
                'top-center', 'always', 0)
        on conflict do nothing`);
    },
  },

  {
    key: 'confirm:good-score-enables-trust-badge',
    event: 'audit.run.completed',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { score?: number; siteId?: string };
      // A badge that appears at any score is not a signal. 90 is the threshold
      // the brief names, and below it nothing is claimed.
      if (typeof data.score !== 'number' || data.score < 90 || !data.siteId) return;

      await (tx as WorkspaceScopedDb).execute(sql`
        update confirm_widgets w
           set settings = w.settings || ${JSON.stringify({ verifiedScore: data.score })}::jsonb,
               updated_at = now()
          from confirm_campaigns c
         where w.campaign_id = c.id
           and c.site_id = ${data.siteId}::uuid
           and w.workspace_id = ${envelope.workspaceId}
           and w.type = 'trust_badge'`);
    },
  },
];

/**
 * Confirm's retention sweep.
 *
 * Conversions are personal data — a name, a city, a country, and whatever else
 * a source sent — held to power a rolling proof feed. Once a row is older than
 * any widget's window it serves no purpose and is only a liability, so it goes
 * with everything else past the workspace's retention window.
 *
 * Subscriber rows are deliberately *not* swept by age: a push subscription is a
 * standing consent, and deleting it because it is old would silently stop
 * someone's notifications while their browser still believes it is subscribed.
 * Expired endpoints are retired on send instead, which is the moment we
 * actually learn they are gone.
 */
export const confirmSweeper: {
  key: string;
  sweep: (tx: WorkspaceScopedDb, workspaceId: string, cutoff: Date) => Promise<number>;
} = {
  key: 'confirm_conversions',
  sweep: async (tx, workspaceId, cutoff) => {
    const rows = await tx.execute(sql`
      delete from confirm_conversions
       where workspace_id = ${workspaceId}
         and occurred_at < ${cutoff.toISOString()}::timestamptz
      returning id`);
    return Array.isArray(rows) ? rows.length : 0;
  },
};
