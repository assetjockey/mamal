import { matches, type VisitorContext } from '@mamal/targeting';

/**
 * Segments: which subscribers a campaign goes to.
 *
 * Deliberately the **same rule engine** the widgets use. A second rule
 * language would mean a second parser, a second builder UI, a second set of
 * operator bugs, and a customer learning "contains" twice — and the questions
 * are the same shape either way: country, device, browser, how long ago.
 *
 * Evaluated in Node rather than the browser, so `matches` runs over a
 * subscriber row projected into the same context shape a visitor has.
 */

export type Subscriber = {
  id: string;
  endpoint: string;
  p256dh: string;
  auth: string;
  country: string | null;
  browser: string | null;
  os: string | null;
  device: string | null;
  language: string | null;
  tags: string[];
  status: string;
  subscribedAt: Date | string;
  lastSeenAt: Date | string | null;
};

/**
 * Fields a segment may target on.
 *
 * The first five are shared with the visitor engine; the last three exist only
 * for subscribers and are passed as declared extensions, so the engine can read
 * them without subscriber concepts leaking into the widget runtime.
 */
export const SUBSCRIBER_FIELDS = [
  'country', 'browser', 'os', 'device', 'language',
  'tags', 'days_subscribed', 'days_since_seen',
] as const;

const days = (from: Date | string | null, now: number): number | undefined => {
  if (!from) return undefined;
  const t = typeof from === 'string' ? Date.parse(from) : from.getTime();
  return Number.isFinite(t) ? Math.floor((now - t) / 86_400_000) : undefined;
};

/**
 * A subscriber, in the shape the rule engine reads.
 *
 * The extra fields beyond a visitor's — `tags`, `days_subscribed` — ride in the
 * same object because the engine reads by field name and an unknown field
 * fails closed. That means an old rule referencing a field this build does not
 * know narrows the audience rather than widening it, which is the right way for
 * a *send* to fail.
 */
export function contextFor(s: Subscriber, now = Date.now()): VisitorContext & Record<string, unknown> {
  return {
    country: s.country ?? undefined,
    browser: s.browser ?? undefined,
    os: s.os ?? undefined,
    device: (s.device as VisitorContext['device']) ?? undefined,
    language: s.language ?? undefined,
    // Comma-joined so `contains` and `in` both read naturally against it.
    tags: s.tags.join(','),
    days_subscribed: days(s.subscribedAt, now),
    days_since_seen: days(s.lastSeenAt, now),
  };
}

/**
 * Filters a subscriber list by a segment rule.
 *
 * Only `active` subscribers are ever returned, regardless of the rule. An
 * expired endpoint is not a targeting decision — it is a subscription that no
 * longer exists, and including it would inflate every campaign's denominator
 * and make the delivery rate a customer sees meaningless.
 */
export function selectSubscribers(
  subscribers: Subscriber[],
  filter: unknown,
  now = Date.now(),
): Subscriber[] {
  return subscribers.filter(
    (s) =>
      s.status === 'active' &&
      // The extras are declared, so `tags` and the day counts are readable.
      // Anything *not* declared still fails closed.
      matches(filter, contextFor(s, now), SUBSCRIBER_FIELDS),
  );
}


