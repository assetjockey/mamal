import { sql } from 'drizzle-orm';
import type { Database } from '@mamal/db';
import type { Envelope } from './envelope.ts';
import type { Transport } from './transport.ts';

/**
 * The outbox relay.
 *
 * Reads pending rows, hands them to the transport, marks them published.
 * `FOR UPDATE SKIP LOCKED` means several relay instances can run without
 * coordinating — no leader election needed for correctness, only for
 * efficiency.
 *
 * Relay lag is a first-class SLO: if this stalls, six tools silently stop
 * cooperating and nobody notices for hours. `pendingStats()` backs the alert.
 */
export class OutboxRelay {
  constructor(
    private readonly db: Database,
    private readonly transport: Transport,
    private readonly opts: { batchSize?: number } = {},
  ) {}

  /** One pass. Returns how many events were relayed. */
  async drain(): Promise<number> {
    const batch = this.opts.batchSize ?? 100;
    let relayed = 0;

    // The whole batch runs in one transaction so a crash mid-publish leaves
    // the rows pending — at-least-once, never at-most-once.
    await this.db.transaction(async (tx) => {
      await tx.execute(sql`select set_config('app.is_platform_admin', 'true', true)`);

      const rows = await tx.execute<{ id: string; envelope: Envelope }>(sql`
        select id, envelope from event_outbox
         where status = 'pending'
         order by created_at asc
         limit ${batch}
         for update skip locked`);

      for (const row of rows) {
        try {
          await this.transport.publish(row.envelope);
          await tx.execute(sql`
            update event_outbox set status = 'published', published_at = now()
             where id = ${row.id}`);
          relayed++;
        } catch (err) {
          await tx.execute(sql`
            update event_outbox
               set attempts = attempts + 1,
                   status = case when attempts + 1 >= 8 then 'failed' else 'pending' end
             where id = ${row.id}`);
          // Keep going: one poison event must not block the rest of the batch.
          console.error(`relay failed for ${row.id}:`, err);
        }
      }
    });

    return relayed;
  }

  /**
   * Backs the relay-lag alert: warn above 30s pending, page above 5 minutes.
   *
   * Runs platform-scoped on purpose. event_outbox carries RLS, so without the
   * GUC this counts only rows visible to the current workspace — which for a
   * background relay is none, and the alert would report a permanently healthy
   * queue while the platform silently stopped cooperating.
   */
  async pendingStats(): Promise<{ pending: number; oldestSeconds: number; failed: number }> {
    return this.db.transaction(async (tx) => {
      await tx.execute(sql`select set_config('app.is_platform_admin', 'true', true)`);
      const [row] = await tx.execute<{
        pending: number;
        oldest_seconds: number | null;
        failed: number;
      }>(sql`
        select
          count(*) filter (where status = 'pending')::int as pending,
          extract(epoch from now() - min(created_at) filter (where status = 'pending'))::int
            as oldest_seconds,
          count(*) filter (where status = 'failed')::int as failed
        from event_outbox`);
      return {
        pending: Number(row?.pending ?? 0),
        oldestSeconds: Number(row?.oldest_seconds ?? 0),
        failed: Number(row?.failed ?? 0),
      };
    });
  }
}
