import type { Envelope } from './envelope.ts';

/**
 * The relay's egress. Redis Streams in production; an in-process fanout for
 * tests and single-process dev, so neither needs Redis running.
 */
export interface Transport {
  publish(envelope: Envelope): Promise<void>;
  /** Returns an unsubscribe function. */
  subscribe(handler: (envelope: Envelope) => Promise<void>): () => void;
  close(): Promise<void>;
}

export class InProcessTransport implements Transport {
  private readonly handlers = new Set<(e: Envelope) => Promise<void>>();
  readonly published: Envelope[] = [];

  async publish(envelope: Envelope): Promise<void> {
    this.published.push(envelope);
    for (const handler of this.handlers) await handler(envelope);
  }

  subscribe(handler: (e: Envelope) => Promise<void>): () => void {
    this.handlers.add(handler);
    return () => this.handlers.delete(handler);
  }

  async close(): Promise<void> {
    this.handlers.clear();
  }
}

export type RedisLike = {
  xadd(key: string, id: string, ...args: string[]): Promise<unknown>;
  quit(): Promise<unknown>;
};

/** Redis Streams transport. Consumer groups live in services/worker-core. */
export class RedisStreamTransport implements Transport {
  constructor(
    private readonly redis: RedisLike,
    private readonly stream = 'bus:events',
  ) {}

  async publish(envelope: Envelope): Promise<void> {
    await this.redis.xadd(
      this.stream,
      '*',
      'id',
      envelope.id,
      'name',
      envelope.name,
      'subject',
      envelope.subject,
      'envelope',
      JSON.stringify(envelope),
    );
  }

  subscribe(): () => void {
    throw new Error(
      'RedisStreamTransport is publish-only; consume with a consumer group in services/worker-core',
    );
  }

  async close(): Promise<void> {
    await this.redis.quit();
  }
}
