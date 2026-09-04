/**
 * The Search Console sync, against a real database.
 *
 * The client is injected, so nothing here touches the network — but everything
 * that makes this integration correct is on *our* side of the boundary:
 * restatement has to overwrite rather than append, a day that returns several
 * countries has to become one row, and the difference between "back off" and
 * "the customer revoked us" has to end up in the connection's status.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  GoogleAuthError, SearchConsoleError,
  type GoogleCredentials, type SearchAnalyticsRow, type SearchConsoleClient,
} from '@mamal/integrations';
import { claimDueConnections, syncSearchConsole } from '../sync.ts';
import { saveConnection } from '../service.ts';

const db = unsafeUnscopedDb();
const tag = `syn${Date.now()}`;
const TODAY = new Date('2026-03-20T09:00:00Z');

let ws = '';
let project = '';
let connectionId = '';

/** Credentials are stored as JSON here; the real path envelope-encrypts them. */
const deps = (client: SearchConsoleClient, over: Record<string, unknown> = {}) => ({
  oauth: { clientId: 'id', clientSecret: 'secret' },
  decrypt: (s: string) => JSON.parse(s) as GoogleCredentials,
  encrypt: (c: GoogleCredentials) => JSON.stringify(c),
  makeClient: () => client,
  today: TODAY,
  backfillDays: 5,
  ...over,
});

function fakeClient(
  perDay: (date: string) => SearchAnalyticsRow[] | Error,
  refreshed: GoogleCredentials | null = null,
): SearchConsoleClient & { asked: string[] } {
  const asked: string[] = [];
  return {
    asked,
    async queryDay(_site, date) {
      asked.push(date);
      const result = perDay(date);
      if (result instanceof Error) throw result;
      return result;
    },
    async listSites() { return []; },
    refreshedCredentials: () => refreshed,
  };
}

const row = (over: Partial<SearchAnalyticsRow> = {}): SearchAnalyticsRow => ({
  query: 'widgets', page: 'https://example.com/w', device: 'desktop', country: 'usa',
  clicks: 5, impressions: 100, position: 4.5, date: '2026-03-15', ...over,
});

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Sync') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Sync', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'market_pro'`);
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from market_search_performance where workspace_id = ${ws}`);
    connectionId = await saveConnection(tx, {
      workspaceId: ws, projectId: project,
      provider: 'google_search_console', externalId: 'sc-domain:example.com',
      displayName: 'example.com',
      credentialsEncrypted: JSON.stringify({ accessToken: 'token', refreshToken: 'r' }),
    });
  }, { db });
});

describe('syncing', () => {
  it('stores what it fetched and stamps the connection', async () => {
    await withWorkspace(ws, async (tx) => {
      const client = fakeClient((date) => [row({ date, query: `q-${date}` })]);
      const result = await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(client));

      expect(result.failed).toBeNull();
      // Five days of backfill, minus the three-day lag, inclusive.
      expect(result.days).toBe(3);
      expect(result.rows).toBe(3);
      // Never inside the lag window: those rows arrive partial and would read
      // as a traffic collapse.
      expect(client.asked.at(-1)).toBe('2026-03-17');

      const [stored] = await tx.execute<{ n: number; newest: string }>(sql`
        select count(*)::int as n, max(captured_on)::text as newest
          from market_search_performance where connection_id = ${connectionId}`);
      expect(stored).toMatchObject({ n: 3, newest: '2026-03-17' });

      const [connection] = await tx.execute<{ status: string; last_synced_at: string | null }>(sql`
        select status, last_synced_at from market_connections where id = ${connectionId}`);
      expect(connection!.status).toBe('active');
      expect(connection!.last_synced_at).not.toBeNull();
    }, { db });
  });

  it('overwrites a restated day rather than doubling it', async () => {
    await withWorkspace(ws, async (tx) => {
      const first = fakeClient(() => [row({ clicks: 5, impressions: 100, position: 8 })]);
      await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(first));

      /*
       * The same days come back with bigger numbers as late attribution lands.
       * Appending would double-count; de-duplicating on read would slow every
       * dashboard query to work around a write-side mistake.
       */
      const second = fakeClient(() => [row({ clicks: 9, impressions: 140, position: 6 })]);
      const result = await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(second));
      expect(result.failed).toBeNull();

      const rows = await tx.execute<{ clicks: number; impressions: number; position: number }>(sql`
        select clicks, impressions, position from market_search_performance
         where connection_id = ${connectionId} and captured_on = '2026-03-17'`);
      expect(rows).toHaveLength(1);
      expect(rows[0]).toMatchObject({ clicks: 9, impressions: 140 });
    }, { db });
  });

  it('re-asks the restatement window on the next run, not just what is new', async () => {
    await withWorkspace(ws, async (tx) => {
      await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(fakeClient(() => [row()])));

      const again = fakeClient(() => [row()]);
      await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(again));

      // Already caught up, and it still goes back — those days keep moving.
      expect(again.asked).toContain('2026-03-17');
      expect(again.asked.length).toBeGreaterThan(1);
    }, { db });
  });

  it('folds several countries into one row for a query and page', async () => {
    await withWorkspace(ws, async (tx) => {
      /*
       * The stored key has no country in it — an opportunity is about a page
       * and a query, not a market. Without folding, a multi-row insert with a
       * repeated conflict target fails the *whole statement* and loses the day.
       */
      const client = fakeClient((date) => [
        row({ date, country: 'usa', clicks: 6, impressions: 100, position: 4 }),
        row({ date, country: 'gbr', clicks: 2, impressions: 300, position: 12 }),
      ]);
      const result = await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(client));
      expect(result.failed).toBeNull();

      const rows = await tx.execute<{ clicks: number; impressions: number; position: number }>(sql`
        select clicks, impressions, position from market_search_performance
         where connection_id = ${connectionId} and captured_on = '2026-03-17'`);
      expect(rows).toHaveLength(1);
      expect(rows[0]!.clicks).toBe(8);
      expect(rows[0]!.impressions).toBe(400);
      // Impression-weighted, so the country with three times the impressions
      // dominates: (4*100 + 12*300) / 400 = 10.
      expect(rows[0]!.position).toBeCloseTo(10, 1);
    }, { db });
  });

  it('handles a day with no data at all', async () => {
    await withWorkspace(ws, async (tx) => {
      const result = await syncSearchConsole(
        tx, { workspaceId: ws, connectionId }, deps(fakeClient(() => [])),
      );
      expect(result).toMatchObject({ failed: null, rows: 0 });
      // Still marked synced: a quiet property is not a broken connection.
      const [connection] = await tx.execute<{ status: string }>(sql`
        select status from market_connections where id = ${connectionId}`);
      expect(connection!.status).toBe('active');
    }, { db });
  });
});

describe('when the provider says no', () => {
  const failsOn = (date: string, error: Error) =>
    fakeClient((d) => (d === date ? error : [row({ date: d })]));

  it('keeps what landed and resumes from there', async () => {
    await withWorkspace(ws, async (tx) => {
      const client = failsOn('2026-03-17', new SearchConsoleError('server', 'boom'));
      const result = await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(client));

      expect(result.failed).toMatchObject({ reason: 'server' });
      // Days are fetched oldest-first, so an interruption leaves a contiguous
      // prefix and the next run picks up from max(captured_on).
      expect(result.rows).toBe(2);
      const [stored] = await tx.execute<{ newest: string }>(sql`
        select max(captured_on)::text as newest from market_search_performance
         where connection_id = ${connectionId}`);
      expect(stored!.newest).toBe('2026-03-16');
    }, { db });
  });

  it('marks the connection only when the customer has to act', async () => {
    const cases: [Error, string | null][] = [
      [new SearchConsoleError('forbidden', 'lost access'), 'error'],
      [new SearchConsoleError('unauthorised', 'bad token'), 'expired'],
      [new GoogleAuthError('revoked', 'Token has been expired or revoked.'), 'revoked'],
      // Ours to solve, not theirs. Marking the badge for these would train
      // people to ignore it.
      [new SearchConsoleError('rate_limited', 'slow down', 90), null],
      [new SearchConsoleError('server', 'boom'), null],
    ];

    for (const [error, expected] of cases) {
      await withWorkspace(ws, async (tx) => {
        await tx.execute(sql`
          update market_connections set status = 'active', last_error = null
           where id = ${connectionId}`);
        await syncSearchConsole(
          tx, { workspaceId: ws, connectionId },
          deps(fakeClient(() => error)),
        );
        const [connection] = await tx.execute<{ status: string }>(sql`
          select status from market_connections where id = ${connectionId}`);
        expect(connection!.status, error.message).toBe(expected ?? 'active');
      }, { db });
    }
  });

  it('carries the provider’s back-off rather than guessing one', async () => {
    await withWorkspace(ws, async (tx) => {
      const result = await syncSearchConsole(
        tx, { workspaceId: ws, connectionId },
        deps(fakeClient(() => new SearchConsoleError('rate_limited', 'slow down', 900))),
      );
      expect(result.retryAfterSeconds).toBe(900);
    }, { db });
  });

  it('does not blame the connection for the instance being misconfigured', async () => {
    await withWorkspace(ws, async (tx) => {
      const result = await syncSearchConsole(
        tx, { workspaceId: ws, connectionId },
        deps(fakeClient(() => new GoogleAuthError('misconfigured', 'GOOGLE_CLIENT_ID is not set'))),
      );
      expect(result.failed).toMatchObject({ reason: 'misconfigured' });
      // Sending every customer to reconnect over an operator's mistake would
      // be the wrong call in the most visible possible way.
      const [connection] = await tx.execute<{ status: string }>(sql`
        select status from market_connections where id = ${connectionId}`);
      expect(connection!.status).toBe('active');
    }, { db });
  });

  it('reports unreadable credentials without pretending they were revoked', async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`
        update market_connections set credentials_encrypted = 'not-json' where id = ${connectionId}`);
      const result = await syncSearchConsole(
        tx, { workspaceId: ws, connectionId }, deps(fakeClient(() => [])),
      );
      expect(result.failed).toMatchObject({ reason: 'unreadable' });
      const [connection] = await tx.execute<{ status: string; last_error: string }>(sql`
        select status, last_error from market_connections where id = ${connectionId}`);
      expect(connection!.status).toBe('error');
      expect(connection!.last_error).toMatch(/instance key/);
    }, { db });
  });
});

describe('tokens', () => {
  it('persists a refreshed token so the next run does not refresh again', async () => {
    await withWorkspace(ws, async (tx) => {
      const fresh = { accessToken: 'new-token', refreshToken: 'r', expiresAt: Date.now() + 3_600_000 };
      const client = fakeClient(() => [row()], fresh);
      await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(client));

      const [connection] = await tx.execute<{ credentials_encrypted: string; expires_at: string | null }>(sql`
        select credentials_encrypted, expires_at from market_connections where id = ${connectionId}`);
      expect(JSON.parse(connection!.credentials_encrypted).accessToken).toBe('new-token');
      expect(connection!.expires_at, 'so the next run knows without asking').not.toBeNull();
    }, { db });
  });

  it('leaves the stored token alone when nothing was refreshed', async () => {
    await withWorkspace(ws, async (tx) => {
      await syncSearchConsole(tx, { workspaceId: ws, connectionId }, deps(fakeClient(() => [row()])));
      const [connection] = await tx.execute<{ credentials_encrypted: string }>(sql`
        select credentials_encrypted from market_connections where id = ${connectionId}`);
      expect(JSON.parse(connection!.credentials_encrypted).accessToken).toBe('token');
    }, { db });
  });
});

describe('claiming work', () => {
  it('claims a due connection once, and not twice', async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`
        update market_connections set last_synced_at = null where id = ${connectionId}`);

      const first = await claimDueConnections(tx, { minIntervalMinutes: 360 });
      expect(first.map((c) => c.id)).toContain(connectionId);

      // Stamped on claim, so a crashed sync is not retried in a tight loop by
      // the next scheduler tick.
      const second = await claimDueConnections(tx, { minIntervalMinutes: 360 });
      expect(second.map((c) => c.id)).not.toContain(connectionId);
    }, { db });
  });

  it('skips a connection with no credentials or a broken status', async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`
        update market_connections
           set last_synced_at = null, status = 'revoked' where id = ${connectionId}`);
      const claimed = await claimDueConnections(tx, { minIntervalMinutes: 360 });
      // Syncing a revoked connection burns quota to be told no.
      expect(claimed.map((c) => c.id)).not.toContain(connectionId);
    }, { db });
  });
});
