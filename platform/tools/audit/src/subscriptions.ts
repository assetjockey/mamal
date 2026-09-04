import { sql } from 'drizzle-orm';
import type { Envelope, Handler } from '@mamal/bus';
import type { WorkspaceScopedDb } from '@mamal/db';
import { resolveIssue } from './commands.ts';

/**
 * What Audit listens for.
 *
 * The return leg of the platform's headline flow: Audit finds a broken link,
 * an automation asks Monitor to watch it, and when Monitor sees the URL come
 * back the issue closes itself. Nobody has to remember to re-run the audit.
 *
 * Monitor does not exist until Phase 5. This handler is registered now and is
 * simply never invoked until something publishes `monitor.target.recovered` —
 * which is the
 * point of the bus: the subscriber does not need the publisher to exist.
 */
export const auditSubscriptions: Handler[] = [
  {
    key: 'audit:monitor-up-resolves-broken-link',
    event: 'monitor.target.recovered',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { targetUrl?: string; sourceUrn?: string };
      if (!data.targetUrl || !data.sourceUrn) return;

      const result = await resolveIssue(tx as WorkspaceScopedDb, {
        workspaceId: envelope.workspaceId,
        siteUrn: data.sourceUrn,
        ruleId: 'broken-internal-link',
        targetUrl: data.targetUrl,
      });

      if (result.ok) {
        const resolved = (result.value as { resolved: number }).resolved;
        if (resolved > 0) {
          console.info(
            `[audit] ${data.targetUrl} recovered — closed ${resolved} broken-link finding(s)`,
          );
        }
      }
    },
  },

  {
    key: 'audit:site-deleted-cleans-up',
    event: 'core.site.deleted',
    handle: async (envelope: Envelope, tx) => {
      const data = envelope.data as { siteId?: string };
      if (!data.siteId) return;
      // Audits cascade from audit_sites, which cascades from sites — but the
      // resource row is ours to remove.
      await (tx as WorkspaceScopedDb).execute(sql`
        delete from resources
         where workspace_id = ${envelope.workspaceId}
           and tool = 'audit'
           and external_id in (
             select id from audit_sites where site_id = ${data.siteId}
           )`);
    },
  },
];

/**
 * Audit's retention sweep.
 *
 * Deleting an `audits` row cascades to its pages, links, findings and
 * Lighthouse reports, so one statement retires a whole run. `audit_snapshots`
 * is deliberately left alone: it is the per-day score aggregate with no page
 * content in it, and it is what keeps a 90-day trend readable after the runs
 * behind it have expired. Retiring the detail is the promise; erasing the
 * history is not.
 *
 * A site's most recent completed run is also kept regardless of age. A
 * workspace that audits once and then leaves for a year should still see its
 * score on return, rather than an empty dashboard that looks like data loss.
 */
/*
 * Typed structurally rather than importing `@mamal/retention`'s `Sweeper`.
 *
 * Retention's runner has to know about every tool, so if tools also imported
 * retention the graph would be a cycle — and it was, until this line. The shape
 * is the contract; a tool does not need the package that consumes it.
 */
export const auditSweeper: {
  key: string;
  sweep: (tx: WorkspaceScopedDb, workspaceId: string, cutoff: Date) => Promise<number>;
} = {
  key: 'audits',
  sweep: async (tx, workspaceId, cutoff) => {
    const rows = await tx.execute(sql`
      delete from audits a
       where a.workspace_id = ${workspaceId}
         and a.created_at < ${cutoff.toISOString()}::timestamptz
         and a.id <> coalesce((
           select id from audits latest
            where latest.audit_site_id = a.audit_site_id
              and latest.status = 'completed'
            -- By id, not created_at: uuidv7 is time-ordered *and* unique, so
            -- two runs written in the same millisecond still have a definite
            -- order. Ordering by a timestamp lets a tie pick arbitrarily, and
            -- "which run did we keep?" is not a question worth having.
            order by latest.id desc
            limit 1
         ), '00000000-0000-0000-0000-000000000000'::uuid)
      returning a.id`);
    return Array.isArray(rows) ? rows.length : 0;
  },
};
