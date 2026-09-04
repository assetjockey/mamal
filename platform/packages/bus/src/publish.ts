import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { DepthExceeded, envelopeSchema, MAX_DEPTH, type Envelope } from './envelope.ts';
import type { EventRegistry } from './registry.ts';

export type PublishInput = {
  name: string;
  workspaceId: string;
  projectId?: string | null;
  subject: string;
  related?: string[];
  data?: Record<string, unknown>;
  actor?: Envelope['actor'];
  dedupeKey?: string;
  /** Set when this event is caused by another, to carry the trace forward. */
  causedBy?: Envelope['trace'];
};

/**
 * Publish into the transactional outbox.
 *
 * MUST be called inside the same transaction as the state change it describes.
 * That is what gives exactly-once PRODUCTION: either the domain row and the
 * event both exist, or neither does. The relay then gives at-least-once
 * delivery, and `bus_deliveries` makes each handler effectively-once.
 */
export async function publish(
  tx: WorkspaceScopedDb,
  registry: EventRegistry,
  input: PublishInput,
): Promise<Envelope> {
  const def = registry.get(input.name);
  if (!def) {
    throw new Error(
      `unknown event "${input.name}". Register it in the tool's manifest before publishing.`,
    );
  }
  if (def.highVolume) {
    throw new Error(
      `"${input.name}" is a high-volume stream and must not emit per-occurrence events. ` +
        `Write it to ClickHouse and emit a threshold event from the rollup job instead.`,
    );
  }

  // Validate the payload at the producer, not at three subscribers.
  const data = def.payload.parse(input.data ?? {}) as Record<string, unknown>;

  const depth = (input.causedBy?.depth ?? -1) + 1;
  if (depth > MAX_DEPTH) throw new DepthExceeded(depth);

  const envelope = envelopeSchema.parse({
    id: randomUUID(),
    name: input.name,
    version: 1,
    occurredAt: new Date().toISOString(),
    workspaceId: input.workspaceId,
    projectId: input.projectId ?? null,
    actor: input.actor ?? { kind: 'system' },
    subject: input.subject,
    related: input.related ?? [],
    data,
    trace: {
      correlationId: input.causedBy?.correlationId ?? randomUUID(),
      causationId: input.causedBy?.causationId,
      depth,
    },
    dedupeKey: input.dedupeKey,
  } satisfies Record<string, unknown>);

  await tx.execute(sql`
    insert into event_outbox (id, workspace_id, name, envelope, status)
    values (${envelope.id}, ${envelope.workspaceId}, ${envelope.name},
            ${JSON.stringify(envelope)}::jsonb, 'pending')`);

  return envelope;
}
