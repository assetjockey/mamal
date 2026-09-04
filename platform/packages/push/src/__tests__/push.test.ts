import { beforeEach, describe, expect, it, vi } from 'vitest';
import { generateVapidKeys, payloadFor, sendMany, sendOne, summarise } from '../send.ts';
import { contextFor, selectSubscribers, SUBSCRIBER_FIELDS, type Subscriber } from '../segments.ts';

vi.mock('web-push', () => ({
  default: {
    generateVAPIDKeys: () => ({ publicKey: 'pub', privateKey: 'priv' }),
    sendNotification: vi.fn(),
  },
}));
const webpush = (await import('web-push')).default as unknown as {
  sendNotification: ReturnType<typeof vi.fn>;
};

const VAPID = { publicKey: 'pub', privateKey: 'priv', subject: 'mailto:a@b.c' };
const sub = (id: string) => ({ id, endpoint: `https://push.test/${id}`, p256dh: 'k', auth: 'a' });
const NOTE = { title: 'Hello', body: 'World' };

const fail = (statusCode: number, extra: Record<string, unknown> = {}) =>
  Object.assign(new Error('push failed'), { statusCode, ...extra });

beforeEach(() => webpush.sendNotification.mockReset());

describe('VAPID', () => {
  it('generates a pair', () => {
    expect(generateVapidKeys()).toEqual({ publicKey: 'pub', privateKey: 'priv' });
  });
});

describe('payload', () => {
  it('keeps only what a service worker uses', () => {
    const p = JSON.parse(payloadFor({ ...NOTE, url: '/x', iconUrl: null, tag: 't' }));
    expect(p).toEqual({ title: 'Hello', body: 'World', url: '/x', actions: [], tag: 't' });
    // `icon: null` must not ship — some services count every byte toward a cap.
    expect('icon' in p).toBe(false);
  });

  it('truncates to two actions, because no browser shows more', () => {
    const p = JSON.parse(payloadFor({
      ...NOTE,
      actions: [1, 2, 3, 4].map((n) => ({ action: `a${n}`, title: `A${n}` })),
    }));
    expect(p.actions).toHaveLength(2);
  });
});

describe('what a failure means', () => {
  it('treats 410 and 404 as permanently gone', async () => {
    // The one that matters: retrying these forever inflates the list, slows
    // every campaign, and makes the delivery rate a customer sees a lie.
    for (const code of [404, 410]) {
      webpush.sendNotification.mockRejectedValueOnce(fail(code));
      const out = await sendOne(sub('s1'), NOTE, VAPID);
      expect(out).toEqual({ status: 'expired', id: 's1', code });
    }
  });

  it('reads Retry-After off a 429 rather than guessing', async () => {
    webpush.sendNotification.mockRejectedValueOnce(fail(429, { headers: { 'retry-after': '120' } }));
    expect(await sendOne(sub('s1'), NOTE, VAPID)).toEqual({
      status: 'rate_limited', id: 's1', retryAfterSeconds: 120,
    });
  });

  it('reports rate limiting even with no Retry-After header', async () => {
    webpush.sendNotification.mockRejectedValueOnce(fail(429));
    const out = await sendOne(sub('s1'), NOTE, VAPID);
    expect(out).toMatchObject({ status: 'rate_limited', retryAfterSeconds: null });
  });

  it('keeps the service’s own reason on a generic failure', async () => {
    // "push failed" is not diagnosable; "payload too large" is.
    webpush.sendNotification.mockRejectedValueOnce(fail(413, { body: 'payload too large' }));
    const out = await sendOne(sub('s1'), NOTE, VAPID);
    expect(out).toMatchObject({ status: 'failed', code: 413, error: 'payload too large' });
  });

  it('survives an error with no status code at all', async () => {
    webpush.sendNotification.mockRejectedValueOnce(new Error('socket hang up'));
    expect(await sendOne(sub('s1'), NOTE, VAPID)).toMatchObject({
      status: 'failed', code: null, error: 'socket hang up',
    });
  });
});

describe('sending to many', () => {
  it('one dead endpoint does not abandon the rest', async () => {
    const subs = ['a', 'b', 'c', 'd'].map(sub);
    webpush.sendNotification
      .mockResolvedValueOnce(undefined)
      .mockRejectedValueOnce(fail(410))
      .mockRejectedValueOnce(fail(500, { body: 'boom' }))
      .mockResolvedValueOnce(undefined);

    const outcomes = await sendMany(subs, NOTE, VAPID, { concurrency: 1 });
    expect(outcomes).toHaveLength(4);
    expect(summarise(outcomes)).toEqual({ sent: 2, expired: 1, rateLimited: 0, failed: 1 });
  });

  it('never opens more sockets than the concurrency cap', async () => {
    let inFlight = 0;
    let peak = 0;
    webpush.sendNotification.mockImplementation(async () => {
      peak = Math.max(peak, ++inFlight);
      await new Promise((r) => setTimeout(r, 5));
      inFlight--;
    });
    await sendMany(Array.from({ length: 30 }, (_, i) => sub(`s${i}`)), NOTE, VAPID, {
      concurrency: 4,
    });
    // 50,000 subscribers must not mean 50,000 simultaneous connections.
    expect(peak).toBeLessThanOrEqual(4);
  });

  it('reports each outcome as it happens, for progress', async () => {
    webpush.sendNotification.mockResolvedValue(undefined);
    const seen: string[] = [];
    await sendMany([sub('a'), sub('b')], NOTE, VAPID, {
      concurrency: 1,
      onOutcome: (o) => seen.push(o.status),
    });
    expect(seen).toEqual(['sent', 'sent']);
  });

  it('handles an empty list without hanging', async () => {
    expect(await sendMany([], NOTE, VAPID)).toEqual([]);
  });
});

/* ------------------------------------------------------------- segments */

const person = (over: Partial<Subscriber> = {}): Subscriber => ({
  id: 's1',
  endpoint: 'https://push.test/s1',
  p256dh: 'k',
  auth: 'a',
  country: 'GB',
  browser: 'Chrome',
  os: 'macOS',
  device: 'desktop',
  language: 'en-GB',
  tags: ['customer', 'newsletter'],
  status: 'active',
  subscribedAt: new Date('2026-01-01T00:00:00Z'),
  lastSeenAt: new Date('2026-03-01T00:00:00Z'),
  ...over,
});

const NOW = Date.parse('2026-03-11T00:00:00Z');
const rule = (...conditions: { field: string; op: string; value?: unknown }[]) => ({
  match: 'all' as const,
  conditions,
});

describe('segments', () => {
  it('reuses the widget rule engine — same operators, same grammar', () => {
    const people = [person(), person({ id: 's2', country: 'US' })];
    const gb = selectSubscribers(people, rule({ field: 'country', op: 'is', value: 'GB' }), NOW);
    expect(gb.map((p) => p.id)).toEqual(['s1']);
  });

  it('targets on tags, which a visitor does not have', () => {
    /*
     * Regression: `tags` is not a field the visitor engine knows, so before the
     * extension mechanism this matched nobody — a segment that silently
     * selected zero subscribers and looked like "no audience" rather than a bug.
     */
    const people = [person(), person({ id: 's2', tags: ['prospect'] })];
    const customers = selectSubscribers(
      people, rule({ field: 'tags', op: 'contains', value: 'customer' }), NOW,
    );
    expect(customers.map((p) => p.id)).toEqual(['s1']);
  });

  it('targets on how long ago someone subscribed', () => {
    const people = [
      person({ id: 'old', subscribedAt: new Date('2026-01-01T00:00:00Z') }), // ~69 days
      person({ id: 'new', subscribedAt: new Date('2026-03-10T00:00:00Z') }), // 1 day
    ];
    const settled = selectSubscribers(
      people, rule({ field: 'days_subscribed', op: 'gt', value: 30 }), NOW,
    );
    expect(settled.map((p) => p.id)).toEqual(['old']);
  });

  it('an undeclared field still fails closed', () => {
    // The safety property must survive the extension mechanism.
    const people = [person()];
    expect(
      selectSubscribers(people, rule({ field: 'favourite_colour', op: 'is', value: 'blue' }), NOW),
    ).toHaveLength(0);
  });

  it('never returns an expired subscriber, whatever the rule says', () => {
    // Not a targeting decision: the subscription does not exist any more, and
    // counting it would inflate every campaign's denominator.
    const people = [person({ status: 'expired' }), person({ id: 's2', status: 'revoked' })];
    expect(selectSubscribers(people, {}, NOW)).toHaveLength(0);
    expect(
      selectSubscribers(people, rule({ field: 'country', op: 'is', value: 'GB' }), NOW),
    ).toHaveLength(0);
  });

  it('an empty segment means everyone active', () => {
    const people = [person(), person({ id: 's2', country: 'US' }), person({ id: 's3', status: 'expired' })];
    expect(selectSubscribers(people, {}, NOW).map((p) => p.id)).toEqual(['s1', 's2']);
  });

  it('projects a subscriber into the shape the engine reads', () => {
    const ctx = contextFor(person(), NOW);
    expect(ctx.country).toBe('GB');
    expect(ctx.tags).toBe('customer,newsletter');
    expect(ctx.days_subscribed).toBe(69);
    expect(ctx.days_since_seen).toBe(10);
  });

  it('tolerates a subscriber with nothing known about them', () => {
    const blank = person({
      country: null, browser: null, os: null, device: null, language: null,
      tags: [], lastSeenAt: null,
    });
    expect(() => contextFor(blank, NOW)).not.toThrow();
    expect(selectSubscribers([blank], {}, NOW)).toHaveLength(1);
    // A rule on an attribute we never captured excludes them, rather than
    // sending to someone who may not match.
    expect(
      selectSubscribers([blank], rule({ field: 'country', op: 'is', value: 'GB' }), NOW),
    ).toHaveLength(0);
  });

  it('declares the fields a segment builder should offer', () => {
    expect(SUBSCRIBER_FIELDS).toContain('tags');
    expect(SUBSCRIBER_FIELDS).toContain('days_subscribed');
    expect(SUBSCRIBER_FIELDS.length).toBe(8);
  });
});
