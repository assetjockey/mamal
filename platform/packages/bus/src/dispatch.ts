import { sql } from 'drizzle-orm';
import type { Database } from '@mamal/db';
import type { Envelope } from './envelope.ts';

export type Handler = {
  /** Stable across deploys — it is half the idempotency key. */
  key: string;
  event: string;
  handle: (envelope: Envelope, tx: HandlerTx) => Promise<void>;
};

export type HandlerTx = Parameters<Parameters<Database['transaction']>[0]>[0];

export type DispatchResult =
  | { status: 'done'; handler: string }
  | { status: 'skipped'; handler: string; reason: 'already_delivered' }
  | { status: 'failed'; handler: string; error: string; attempts: number; deadLettered: boolean };

const MAX_ATTEMPTS = 8;

/**
 * The delivery side of the bus.
 *
 * At-least-once delivery plus a `bus_deliveries` row per (handler, event)
 * yields EFFECTIVELY-ONCE per handler — the guarantee subscribers actually
 * need. The barrier is claimed with INSERT ... ON CONFLICT DO NOTHING, so two
 * workers racing the same event cannot both run the handler.
 */
export class Dispatcher {
  private readonly handlers = new Map<string, Handler[]>();

  constructor(private readonly db: Database) {}

  on(handler: Handler): this {
    const list = this.handlers.get(handler.event) ?? [];
    if (list.some((h) => h.key === handler.key)) {
      throw new Error(`handler "${handler.key}" is already registered for ${handler.event}`);
    }
    list.push(handler);
    this.handlers.set(handler.event, list);
    return this;
  }

  handlersFor(event: string): Handler[] {
    return this.handlers.get(event) ?? [];
  }

  async dispatch(envelope: Envelope): Promise<DispatchResult[]> {
    const handlers = this.handlersFor(envelope.name);
    const results: DispatchResult[] = [];
    for (const handler of handlers) {
      results.push(await this.runOne(handler, envelope));
    }
    return results;
  }

  private async runOne(handler: Handler, envelope: Envelope): Promise<DispatchResult> {
    // Claim the barrier. Zero rows back means someone else has it or it is done.
    const claimed = await this.db.execute<{ status: string; attempts: number }>(sql`
      insert into bus_deliveries (handler_key, event_id, status, attempts)
      values (${handler.key}, ${envelope.id}, 'running', 1)
      on conflict (handler_key, event_id) do update
        set attempts = bus_deliveries.attempts + 1,
            status = 'running'
        where bus_deliveries.status in ('pending', 'failed')
      returning status, attempts`);

    if (claimed.length === 0) {
      return { status: 'skipped', handler: handler.key, reason: 'already_delivered' };
    }
    const attempts = Number(claimed[0]!.attempts);

    try {
      await this.db.transaction(async (tx) => {
        await tx.execute(
          sql`select set_config('app.current_workspace_id', ${envelope.workspaceId}, true)`,
        );
        await handler.handle(envelope, tx);
      });
      await this.db.execute(sql`
        update bus_deliveries set status = 'done', completed_at = now(), error = null
         where handler_key = ${handler.key} and event_id = ${envelope.id}`);
      return { status: 'done', handler: handler.key };
    } catch (err) {
      const message = err instanceof Error ? err.message : String(err);
      const deadLettered = attempts >= MAX_ATTEMPTS;

      await this.db.execute(sql`
        update bus_deliveries
           set status = ${deadLettered ? 'dead' : 'failed'}, error = ${message}
         where handler_key = ${handler.key} and event_id = ${envelope.id}`);

      if (deadLettered) {
        // A poison event goes to the dead-letter table with its full payload so
        // it can be replayed from admin — it must never block the stream.
        await this.db.execute(sql`
          insert into bus_dead_letters
            (workspace_id, handler_key, event_id, envelope, error, attempts)
          values (${envelope.workspaceId}, ${handler.key}, ${envelope.id},
                  ${JSON.stringify(envelope)}::jsonb, ${message}, ${attempts})`);
      }
      return { status: 'failed', handler: handler.key, error: message, attempts, deadLettered };
    }
  }

  /** Admin replay: clear the barrier so the next dispatch re-runs the handler. */
  async replay(handlerKey: string, eventId: string): Promise<void> {
    await this.db.execute(sql`
      update bus_deliveries set status = 'pending', attempts = 0, error = null
       where handler_key = ${handlerKey} and event_id = ${eventId}`);
    await this.db.execute(sql`
      update bus_dead_letters set replayed_at = now()
       where handler_key = ${handlerKey} and event_id = ${eventId}`);
  }
}
