import { describe, expect, it, vi } from 'vitest';
import {
  daysToSync, searchConsoleClient, SearchConsoleError, LAG_DAYS, RESTATEMENT_DAYS,
} from '../search-console.ts';
import { GoogleAuthError, freshAccessToken, refreshAccessToken } from '../oauth.ts';

const config = (fetchImpl: typeof globalThis.fetch) => ({
  clientId: 'id', clientSecret: 'secret', fetch: fetchImpl,
});

const ok = (body: unknown, init: ResponseInit = {}) =>
  new Response(JSON.stringify(body), { status: 200, ...init });

const fail = (status: number, body: unknown, headers: Record<string, string> = {}) =>
  new Response(JSON.stringify(body), { status, headers });

/* ------------------------------------------------------------ which days */

describe('deciding which days to fetch', () => {
  const today = new Date('2026-03-20T12:00:00Z');

  it('never asks for a day inside the lag window', () => {
    /*
     * Rows appear two to three days late and arrive partial before they are
     * complete. Asking for yesterday returns a fraction of the truth and then
     * never revisits it — which reads as a traffic collapse.
     */
    const days = daysToSync({ today, latestStored: '2026-03-01' });
    expect(days.at(-1)).toBe('2026-03-17');   // 20th minus three
    expect(days.some((d) => d > '2026-03-17')).toBe(false);
  });

  it('backfills on a first sync', () => {
    const days = daysToSync({ today, latestStored: null, backfillDays: 30 });
    expect(days[0]).toBe('2026-02-18');
    expect(days).toHaveLength(30 - LAG_DAYS + 1);
  });

  it('re-asks for the restatement window, not just what is new', () => {
    /*
     * The last few days keep being revised as late attribution lands. A sync
     * that only moves forward leaves the most recent — and most interesting —
     * data permanently understated.
     */
    const days = daysToSync({ today, latestStored: '2026-03-16' });
    expect(days[0], 'goes back before what is stored').toBe('2026-03-12');
    expect(days).toContain('2026-03-16');
    expect(days.at(-1)).toBe('2026-03-17');
  });

  it('does not re-fetch history that has settled', () => {
    // Stored up to yesterday-minus-lag: only the restatement window is worth
    // asking for, not the ninety days behind it.
    const days = daysToSync({ today, latestStored: '2026-03-17' });
    expect(days).toHaveLength(RESTATEMENT_DAYS + 1);
    expect(days[0]).toBe('2026-03-12');
  });

  it('keeps re-asking for the restatement window, run after run', () => {
    /*
     * There is no "nothing to do" while a property has data — the last few
     * complete days keep being revised, so a daily sync always re-fetches them.
     * Six requests a day is the price of not permanently understating the most
     * recent week, and it is the right trade.
     */
    const caughtUp = daysToSync({ today, latestStored: '2026-03-17' });
    expect(caughtUp).toHaveLength(RESTATEMENT_DAYS + 1);

    // Even stored *ahead* of the last complete day, the window is re-asked
    // rather than skipped.
    const ahead = daysToSync({ today, latestStored: '2026-03-25' });
    expect(ahead).toEqual(caughtUp);
  });

  it('fills a gap rather than only re-asking the recent window', () => {
    // Two months since the last sync: everything between is fetched once, and
    // the restatement window comes along at the end of it.
    const days = daysToSync({ today, latestStored: '2026-01-10' });
    expect(days[0]).toBe('2026-01-11');
    expect(days.at(-1)).toBe('2026-03-17');
    expect(days.length).toBeGreaterThan(60);
  });

  it('crosses a month boundary correctly', () => {
    const days = daysToSync({
      today: new Date('2026-03-03T00:00:00Z'), latestStored: '2026-02-24',
    });
    // 3 March minus three days of lag is 28 February — 2026 is not a leap year,
    // and anything in March is still settling.
    expect(days.at(-1)).toBe('2026-02-28');
    expect(days).toContain('2026-02-25');
    expect(days.some((d) => d.startsWith('2026-03')), 'March is inside the lag window').toBe(false);
  });
});

/* --------------------------------------------------------------- querying */

describe('querying a day', () => {
  it('reads the flat key array in the order the dimensions were asked for', async () => {
    const fetchImpl = vi.fn(async () =>
      ok({
        rows: [
          { keys: ['widget reviews', 'https://example.com/a', 'MOBILE', 'gbr'],
            clicks: 12, impressions: 340, ctr: 0.035, position: 4.2 },
        ],
      }),
    );
    const client = searchConsoleClient({ accessToken: 'token' }, config(fetchImpl as never));
    const rows = await client.queryDay('sc-domain:example.com', '2026-03-10');

    expect(rows).toEqual([{
      query: 'widget reviews',
      page: 'https://example.com/a',
      device: 'mobile',
      country: 'gbr',
      clicks: 12, impressions: 340, position: 4.2,
      date: '2026-03-10',
    }]);

    const body = JSON.parse((fetchImpl.mock.calls[0]![1] as RequestInit).body as string);
    expect(body.dimensions).toEqual(['query', 'page', 'device', 'country']);
    // `final` excludes still-settling rows; `all` would look like a collapse
    // the moment it is compared against a complete day.
    expect(body.dataState).toBe('final');
    expect(body.startDate).toBe('2026-03-10');
    expect(body.endDate).toBe('2026-03-10');
  });

  it('pages until the long tail is exhausted', async () => {
    /*
     * 25,000 rows a request, and stopping at the first page silently truncates
     * the tail — which is exactly where the opportunities are.
     */
    const full = Array.from({ length: 25_000 }, (_, i) => ({
      keys: [`q${i}`, 'https://example.com/p', 'DESKTOP', 'usa'],
      clicks: 0, impressions: 1, ctr: 0, position: 50,
    }));
    const fetchImpl = vi.fn()
      .mockResolvedValueOnce(ok({ rows: full }))
      .mockResolvedValueOnce(ok({ rows: full.slice(0, 10) }));

    const client = searchConsoleClient({ accessToken: 'token' }, config(fetchImpl as never));
    const rows = await client.queryDay('sc-domain:example.com', '2026-03-10');

    expect(rows).toHaveLength(25_010);
    expect(fetchImpl).toHaveBeenCalledTimes(2);
    expect(JSON.parse((fetchImpl.mock.calls[1]![1] as RequestInit).body as string).startRow).toBe(25_000);
  });

  it('stops after one request when the first page is short', async () => {
    const fetchImpl = vi.fn(async () => ok({ rows: [] }));
    const client = searchConsoleClient({ accessToken: 'token' }, config(fetchImpl as never));
    expect(await client.queryDay('sc-domain:example.com', '2026-03-10')).toEqual([]);
    expect(fetchImpl).toHaveBeenCalledTimes(1);
  });

  it('distinguishes the failures that need different actions', async () => {
    const cases: [number, string, Record<string, string>][] = [
      [401, 'unauthorised', {}],
      [403, 'forbidden', {}],
      [429, 'rate_limited', { 'retry-after': '120' }],
      [404, 'not_found', {}],
      [500, 'server', {}],
    ];
    for (const [status, reason, headers] of cases) {
      const client = searchConsoleClient(
        { accessToken: 'token' },
        config(vi.fn(async () => fail(status, { error: { message: 'nope' } }, headers)) as never),
      );
      await expect(client.queryDay('s', '2026-03-10'), `${status}`)
        .rejects.toMatchObject({ reason });
    }
  });

  it('carries the provider’s Retry-After rather than guessing', async () => {
    const client = searchConsoleClient(
      { accessToken: 'token' },
      config(vi.fn(async () => fail(429, {}, { 'retry-after': '300' })) as never),
    );
    const error = await client.queryDay('s', '2026-03-10').catch((e: unknown) => e as SearchConsoleError);
    expect(error).toMatchObject({ reason: 'rate_limited', retryAfterSeconds: 300 });
  });
});

/* ------------------------------------------------------------------ oauth */

describe('token handling', () => {
  it('leaves a token alone while it has headroom', async () => {
    const fetchImpl = vi.fn();
    const result = await freshAccessToken(
      { accessToken: 'good', expiresAt: Date.now() + 600_000 },
      config(fetchImpl as never),
    );
    expect(result).toEqual({ accessToken: 'good', refreshed: null });
    expect(fetchImpl).not.toHaveBeenCalled();
  });

  it('refreshes early rather than mid-flight', async () => {
    /*
     * A sync starting with fifty seconds left makes its first request and fails
     * its second. A refresh that happens slightly early is far easier to reason
     * about than a 401 halfway through a page loop.
     */
    const fetchImpl = vi.fn(async () => ok({ access_token: 'new', expires_in: 3600 }));
    const result = await freshAccessToken(
      { accessToken: 'stale', refreshToken: 'r', expiresAt: Date.now() + 50_000 },
      config(fetchImpl as never),
    );
    expect(result.accessToken).toBe('new');
    expect(fetchImpl).toHaveBeenCalledOnce();
  });

  it('carries the refresh token forward, since Google does not reissue it', async () => {
    const refreshed = await refreshAccessToken(
      'the-refresh-token',
      config(vi.fn(async () => ok({ access_token: 'new', expires_in: 3600 })) as never),
    );
    // Dropping it here turns every hour into a reconnect.
    expect(refreshed.refreshToken).toBe('the-refresh-token');
    expect(refreshed.expiresAt).toBeGreaterThan(Date.now());
  });

  it('separates a revoked grant from a bad network', async () => {
    const revoked = await refreshAccessToken(
      'r',
      config(vi.fn(async () => fail(400, { error: 'invalid_grant', error_description: 'Token has been expired or revoked.' })) as never),
    ).catch((e: unknown) => e as GoogleAuthError);
    expect(revoked).toMatchObject({ reason: 'revoked' });

    // A 500 from Google is not the customer revoking us, and treating it as one
    // would disconnect everybody during an outage.
    const transient = await refreshAccessToken(
      'r',
      config(vi.fn(async () => fail(503, { error: 'backendError' })) as never),
    ).catch((e: unknown) => e as GoogleAuthError);
    expect(transient).toMatchObject({ reason: 'network' });

    const offline = await refreshAccessToken(
      'r',
      config(vi.fn(async () => { throw new Error('ECONNREFUSED'); }) as never),
    ).catch((e: unknown) => e as GoogleAuthError);
    expect(offline).toMatchObject({ reason: 'network' });
  });

  it('says so when the instance has no OAuth credentials', async () => {
    const error = await refreshAccessToken('r', {
      clientId: '', clientSecret: '', fetch: vi.fn() as never,
    }).catch((e: unknown) => e as GoogleAuthError);
    expect(error).toMatchObject({ reason: 'misconfigured' });
    expect((error as GoogleAuthError).message).toMatch(/GOOGLE_CLIENT_ID/);
  });

  it('hands the refreshed credentials back so the caller can store them', async () => {
    const fetchImpl = vi.fn()
      .mockResolvedValueOnce(ok({ access_token: 'fresh', expires_in: 3600 }))
      .mockResolvedValueOnce(ok({ rows: [] }));
    const client = searchConsoleClient(
      { accessToken: 'stale', refreshToken: 'r', expiresAt: Date.now() - 1 },
      config(fetchImpl as never),
    );
    await client.queryDay('s', '2026-03-10');
    // Without this the next sync starts from the stale token and refreshes
    // again — a wasted round trip every single run.
    expect(client.refreshedCredentials()?.accessToken).toBe('fresh');
  });

  it('refuses once rather than looping when there is nothing to refresh with', async () => {
    const client = searchConsoleClient(
      { accessToken: 'stale', expiresAt: Date.now() - 1 },
      config(vi.fn() as never),
    );
    await expect(client.queryDay('s', '2026-03-10')).rejects.toMatchObject({ reason: 'revoked' });
  });
});
