import { z } from 'zod';

/**
 * The event envelope.
 *
 * `id` doubles as the idempotency key. `subject` is the URN the event is
 * about, which is also the partition key — consumers are sharded by
 * hash(subject), so ordering is guaranteed PER SUBJECT and explicitly not
 * across subjects.
 */
export const envelopeSchema = z.object({
  id: z.uuid(),
  name: z.string().regex(/^[a-z_]+\.[a-z_]+\.[a-z_]+$/),
  version: z.literal(1).default(1),
  occurredAt: z.iso.datetime(),
  workspaceId: z.uuid(),
  projectId: z.uuid().nullable().default(null),
  actor: z.object({
    kind: z.enum(['user', 'system', 'automation', 'api_key']),
    id: z.string().optional(),
  }),
  subject: z.string(),
  related: z.array(z.string()).default([]),
  data: z.record(z.string(), z.unknown()).default({}),
  trace: z.object({
    correlationId: z.uuid(),
    causationId: z.uuid().optional(),
    /**
     * Hard cap at MAX_DEPTH. Without it, two automations that trigger each
     * other take the platform down, and it is the kind of loop nobody notices
     * until the bill arrives.
     */
    depth: z.number().int().min(0).default(0),
  }),
  dedupeKey: z.string().optional(),
});

export type Envelope = z.infer<typeof envelopeSchema>;

export const MAX_DEPTH = 8;
export const MAX_AUTOMATION_DEPTH = 3;

export class DepthExceeded extends Error {
  constructor(readonly depth: number) {
    super(`event chain exceeded max depth ${MAX_DEPTH} (got ${depth}) — likely an automation loop`);
    this.name = 'DepthExceeded';
  }
}

/** Stable partition for a subject, so one subject's events stay ordered. */
export function partitionFor(subject: string, partitions: number): number {
  let h = 2166136261;
  for (let i = 0; i < subject.length; i++) {
    h ^= subject.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return Math.abs(h) % partitions;
}
