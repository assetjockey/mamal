import { z } from 'zod';

/**
 * The event registry. Adding an event FORCES you to declare its payload
 * schema, and the publisher validates against it before anything is written —
 * so a malformed event fails at the producer, not three subscribers later.
 */
export type EventDef = {
  name: string;
  description?: string;
  payload: z.ZodTypeAny;
  /**
   * True for streams that land in ClickHouse. These must NOT emit one event per
   * occurrence — 50M link clicks a day would flood the bus. Rollup jobs emit
   * threshold events instead. The publisher refuses them outright.
   */
  highVolume?: boolean;
};

export class EventRegistry {
  private readonly defs = new Map<string, EventDef>();

  register(...defs: EventDef[]): this {
    for (const def of defs) {
      if (this.defs.has(def.name)) throw new Error(`event "${def.name}" is already registered`);
      this.defs.set(def.name, def);
    }
    return this;
  }

  get(name: string): EventDef | undefined {
    return this.defs.get(name);
  }

  names(): string[] {
    return [...this.defs.keys()].sort();
  }
}

const urn = z.string().startsWith('urn:mamal:');

/**
 * The starter catalogue. Each tool contributes its own via its ToolManifest;
 * these are the platform-level events that exist before any tool is installed.
 */
export const coreEvents: EventDef[] = [
  {
    name: 'core.site.created',
    payload: z.object({ siteId: z.uuid(), host: z.string() }),
  },
  {
    name: 'core.site.verified',
    payload: z.object({ siteId: z.uuid(), method: z.string() }),
  },
  {
    name: 'core.domain.verified',
    payload: z.object({ domainId: z.uuid(), host: z.string() }),
  },
  {
    name: 'core.member.joined',
    payload: z.object({ userId: z.uuid(), role: z.string() }),
  },
  {
    name: 'core.contact.created',
    payload: z.object({ contactId: z.uuid(), sourceUrn: urn.optional() }),
  },
  {
    name: 'billing.plan.changed',
    payload: z.object({ planKey: z.string(), previousPlanKey: z.string().nullable() }),
  },
  {
    name: 'billing.credits.granted',
    payload: z.object({ amount: z.number().int(), source: z.string() }),
  },
  {
    name: 'billing.credits.exhausted',
    payload: z.object({ featureKey: z.string(), required: z.number().int() }),
  },
  {
    name: 'billing.limit.reached',
    payload: z.object({ featureKey: z.string(), used: z.number(), limit: z.number() }),
  },
];
