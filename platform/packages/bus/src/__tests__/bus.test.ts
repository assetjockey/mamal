import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { z } from 'zod';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { EventRegistry, coreEvents } from '../registry.ts';
import { publish } from '../publish.ts';
import { OutboxRelay } from '../relay.ts';
import { Dispatcher, type Handler } from '../dispatch.ts';
import { InProcessTransport } from '../transport.ts';
import { DepthExceeded, MAX_DEPTH, partitionFor } from '../envelope.ts';

describe('partitioning', () => {
  it('is stable, so one subject stays ordered', () => {
    const s = 'urn:mamal:core:site:abc';
    expect(partitionFor(s, 16)).toBe(partitionFor(s, 16));
  });

  it('spreads subjects across partitions', () => {
    const seen = new Set(
      Array.from({ length: 200 }, (_, i) => partitionFor(`urn:mamal:link:link:${i}`, 16)),
    );
    expect(seen.size).toBeGreaterThan(8);
  });
});

describe('registry', () => {
  it('refuses duplicate registration', () => {
    const r = new EventRegistry().register(...coreEvents);
    expect(() => r.register(coreEvents[0]!)).toThrow(/already registered/);
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('bus', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `bus${Date.now()}`;
  let ws = '';
  let project = '';

  const registry = new EventRegistry().register(...coreEvents, {
    name: 'link.click.recorded',
    payload: z.object({ linkId: z.string() }),
    highVolume: true,
  });

  const site = () => `urn:mamal:core:site:${randomUUID()}`;

  const emit = (over: Partial<Parameters<typeof publish>[2]> = {}) =>
    withWorkspace(
      ws,
      (tx) =>
        publish(tx, registry, {
          name: 'core.site.created',
          workspaceId: ws,
          projectId: project,
          subject: site(),
          data: { siteId: randomUUID(), host: 'example.com' },
          ...over,
        }),
      { db },
    );

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Bus') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Bus', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
    }, { db });
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from bus_dead_letters where workspace_id = ${ws}`);
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from event_outbox where workspace_id = ${ws}`);
      await tx.execute(sql`delete from bus_dead_letters where workspace_id = ${ws}`);
    }, { db });
  });

  describe('publish — validation happens at the producer', () => {
    it('writes to the outbox in the caller transaction', async () => {
      const env = await emit();
      const [row] = await asPlatformAdmin(
        (tx) => tx.execute<{ status: string }>(sql`select status from event_outbox where id = ${env.id}`),
        { db },
      );
      expect(row!.status).toBe('pending');
    });

    it('rolls the event back with the state change it describes', async () => {
      const before = await countOutbox();
      await expect(
        withWorkspace(
          ws,
          async (tx) => {
            await publish(tx, registry, {
              name: 'core.site.created',
              workspaceId: ws,
              projectId: project,
              subject: site(),
              data: { siteId: randomUUID(), host: 'rollback.com' },
            });
            throw new Error('the domain write failed');
          },
          { db },
        ),
      ).rejects.toThrow('the domain write failed');
      expect(await countOutbox()).toBe(before);
    });

    it('rejects an unregistered event', async () => {
      await expect(emit({ name: 'core.nope.happened' })).rejects.toThrow(/unknown event/);
    });

    it('rejects a payload that does not match the schema', async () => {
      await expect(emit({ data: { siteId: 'not-a-uuid', host: 'x.com' } })).rejects.toThrow();
    });

    it('refuses a high-volume stream outright', async () => {
      await expect(
        emit({ name: 'link.click.recorded', data: { linkId: 'l1' } }),
      ).rejects.toThrow(/must not emit per-occurrence events/);
    });

    it('caps chain depth so two automations cannot trigger each other forever', async () => {
      const deep = { correlationId: randomUUID(), depth: MAX_DEPTH };
      await expect(emit({ causedBy: deep })).rejects.toBeInstanceOf(DepthExceeded);
    });

    it('carries the correlation id down a causal chain', async () => {
      const first = await emit();
      const second = await emit({ causedBy: first.trace });
      expect(second.trace.correlationId).toBe(first.trace.correlationId);
      expect(second.trace.depth).toBe(first.trace.depth + 1);
    });
  });

  describe('relay', () => {
    /*
     * The relay drains the whole outbox — that is the design, not a leak: one
     * leader-elected relay serves every workspace. So these assertions are only
     * meaningful from a known starting state, and any rows another suite left
     * behind would otherwise be counted here.
     *
     * This surfaced when the audit suite started publishing: its runs leave
     * pending rows, and a count of 2 became a count of 5.
     */
    beforeEach(async () => {
      await asPlatformAdmin((tx) => tx.execute(sql`delete from event_outbox`), { db });
    });

    it('publishes pending events and marks them published', async () => {
      await emit();
      await emit();
      const transport = new InProcessTransport();
      const relay = new OutboxRelay(db, transport);

      expect(await relay.drain()).toBe(2);
      expect(transport.published).toHaveLength(2);
      expect((await relay.pendingStats()).pending).toBe(0);
    });

    it('is a no-op on a second pass — events are not re-published', async () => {
      await emit();
      const transport = new InProcessTransport();
      const relay = new OutboxRelay(db, transport);
      await relay.drain();
      expect(await relay.drain()).toBe(0);
      expect(transport.published).toHaveLength(1);
    });

    it('leaves an event pending when the transport throws', async () => {
      await emit();
      const flaky = {
        publish: async () => {
          throw new Error('redis is down');
        },
        subscribe: () => () => {},
        close: async () => {},
      };
      const relay = new OutboxRelay(db, flaky);
      expect(await relay.drain()).toBe(0);

      const stats = await relay.pendingStats();
      expect(stats.pending).toBe(1);

      // and a healthy relay picks it up afterwards
      const good = new InProcessTransport();
      expect(await new OutboxRelay(db, good).drain()).toBe(1);
    });

    it('reports lag, which is the SLO the whole platform hangs on', async () => {
      await emit();
      const stats = await new OutboxRelay(db, new InProcessTransport()).pendingStats();
      expect(stats.pending).toBe(1);
      expect(stats.oldestSeconds).toBeGreaterThanOrEqual(0);
    });
  });

  describe('dispatch — effectively-once per handler', () => {
    const seen: string[] = [];
    const ok = (key: string): Handler => ({
      key,
      event: 'core.site.created',
      handle: async (env) => {
        seen.push(`${key}:${env.id}`);
      },
    });

    beforeEach(() => {
      seen.length = 0;
    });

    it('runs every handler registered for the event', async () => {
      const env = await emit();
      const dispatcher = new Dispatcher(db).on(ok('a')).on(ok('b'));
      const results = await dispatcher.dispatch(env);
      expect(results.map((r) => r.status)).toEqual(['done', 'done']);
      expect(seen).toHaveLength(2);
    });

    it('does NOT re-run a handler on redelivery', async () => {
      const env = await emit();
      const dispatcher = new Dispatcher(db).on(ok('idem'));
      await dispatcher.dispatch(env);
      const second = await dispatcher.dispatch(env);
      expect(second[0]!.status).toBe('skipped');
      expect(seen).toHaveLength(1);
    });

    it('isolates handlers — one failing does not stop the others', async () => {
      const env = await emit();
      const dispatcher = new Dispatcher(db)
        .on({ key: 'boom', event: 'core.site.created', handle: async () => { throw new Error('nope'); } })
        .on(ok('survivor'));
      const results = await dispatcher.dispatch(env);
      expect(results[0]!.status).toBe('failed');
      expect(results[1]!.status).toBe('done');
      expect(seen).toEqual([`survivor:${env.id}`]);
    });

    it('retries a failed handler, then dead-letters it instead of blocking', async () => {
      const env = await emit();
      const dispatcher = new Dispatcher(db).on({
        key: 'always-fails',
        event: 'core.site.created',
        handle: async () => { throw new Error('poison'); },
      });

      let last = await dispatcher.dispatch(env);
      for (let i = 1; i < 8; i++) last = await dispatcher.dispatch(env);

      expect(last[0]!.status).toBe('failed');
      expect(last[0]!.status === 'failed' && last[0]!.deadLettered).toBe(true);

      const [dl] = await asPlatformAdmin(
        (tx) => tx.execute<{ n: number }>(sql`
          select count(*)::int as n from bus_dead_letters
           where event_id = ${env.id} and handler_key = 'always-fails'`),
        { db },
      );
      expect(Number(dl!.n)).toBe(1);
    });

    it('admin replay clears the barrier so the handler runs again', async () => {
      const env = await emit();
      const dispatcher = new Dispatcher(db).on(ok('replayable'));
      await dispatcher.dispatch(env);
      expect(seen).toHaveLength(1);

      await dispatcher.replay('replayable', env.id);
      await dispatcher.dispatch(env);
      expect(seen).toHaveLength(2);
    });

    it('gives the handler a workspace-scoped transaction', async () => {
      const env = await emit();
      let scoped = '';
      await new Dispatcher(db)
        .on({
          key: 'scope-check',
          event: 'core.site.created',
          handle: async (_e, tx) => {
            const [r] = await tx.execute<{ v: string }>(
              sql`select current_setting('app.current_workspace_id', true) as v`,
            );
            scoped = r!.v;
          },
        })
        .dispatch(env);
      expect(scoped).toBe(ws);
    });

    it('refuses two handlers with the same key on one event', () => {
      const dispatcher = new Dispatcher(db).on(ok('dupe'));
      expect(() => dispatcher.on(ok('dupe'))).toThrow(/already registered/);
    });
  });

  async function countOutbox(): Promise<number> {
    const [r] = await asPlatformAdmin(
      (tx) => tx.execute<{ n: number }>(sql`
        select count(*)::int as n from event_outbox where workspace_id = ${ws}`),
      { db },
    );
    return Number(r!.n);
  }
});
