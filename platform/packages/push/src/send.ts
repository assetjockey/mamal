import webpush from 'web-push';

/**
 * Web Push delivery.
 *
 * The encryption is **not** hand-rolled. RFC 8291 (aes128gcm content encoding,
 * ECDH over P-256, HKDF) and RFC 8292 (VAPID JWTs) are exactly the kind of
 * cryptography that is easy to implement in a way that looks correct and is
 * quietly broken, and getting it wrong here means either undeliverable
 * notifications or a downgraded key exchange. `web-push` is the reference
 * implementation the browser vendors' own examples use; the same reasoning that
 * wraps `linkinator` rather than reimplementing a crawler applies.
 *
 * What this module owns is everything around it: which endpoints get a message,
 * what a failure *means*, and making sure a dead subscription is retired rather
 * than retried forever.
 */

export type Subscription = {
  id: string;
  endpoint: string;
  p256dh: string;
  auth: string;
};

export type Notification = {
  title: string;
  body: string;
  iconUrl?: string | null;
  imageUrl?: string | null;
  url?: string | null;
  /** Up to two buttons; more are ignored by every browser that renders them. */
  actions?: { action: string; title: string }[];
  /** Collapses earlier undelivered messages with the same tag. */
  tag?: string;
  ttlSeconds?: number;
};

export type Vapid = { publicKey: string; privateKey: string; subject: string };

/**
 * What happened to one send, in terms the caller can act on.
 *
 * `expired` is the important one: a 404 or 410 from a push service means the
 * subscription is permanently gone — the browser was uninstalled, the profile
 * cleared, permission revoked. Retrying it is guaranteed to fail, and *not*
 * retiring it means the list inflates with dead endpoints, every campaign gets
 * slower, and the delivery rate a customer sees is a lie.
 */
export type SendOutcome =
  | { status: 'sent'; id: string }
  | { status: 'expired'; id: string; code: number }
  | { status: 'rate_limited'; id: string; retryAfterSeconds: number | null }
  | { status: 'failed'; id: string; code: number | null; error: string };

/**
 * A fresh VAPID pair for a site.
 *
 * One pair per push website, not one per platform: the public key is baked
 * into every browser subscription created against it, so rotating a shared key
 * would invalidate every subscriber on every site at once.
 */
export function generateVapidKeys(): { publicKey: string; privateKey: string } {
  return webpush.generateVAPIDKeys();
}

/** The payload a service worker receives. Kept small — some services cap at 4 KB. */
export function payloadFor(n: Notification): string {
  return JSON.stringify({
    title: n.title,
    body: n.body,
    icon: n.iconUrl ?? undefined,
    image: n.imageUrl ?? undefined,
    url: n.url ?? undefined,
    actions: (n.actions ?? []).slice(0, 2),
    tag: n.tag,
  });
}

export async function sendOne(
  subscription: Subscription,
  notification: Notification,
  vapid: Vapid,
): Promise<SendOutcome> {
  try {
    await webpush.sendNotification(
      {
        endpoint: subscription.endpoint,
        keys: { p256dh: subscription.p256dh, auth: subscription.auth },
      },
      payloadFor(notification),
      {
        TTL: notification.ttlSeconds ?? 86_400,
        vapidDetails: {
          subject: vapid.subject,
          publicKey: vapid.publicKey,
          privateKey: vapid.privateKey,
        },
      },
    );
    return { status: 'sent', id: subscription.id };
  } catch (e) {
    const err = e as { statusCode?: number; headers?: Record<string, string>; body?: string; message?: string };
    const code = err.statusCode ?? null;

    // 404/410: the subscription is gone for good. Retire it.
    if (code === 404 || code === 410) return { status: 'expired', id: subscription.id, code };

    if (code === 429) {
      const retry = Number(err.headers?.['retry-after']);
      return {
        status: 'rate_limited',
        id: subscription.id,
        retryAfterSeconds: Number.isFinite(retry) ? retry : null,
      };
    }

    return {
      status: 'failed',
      id: subscription.id,
      code,
      // The body carries the push service's own reason ("payload too large",
      // "invalid TTL"); the generic message alone is not diagnosable.
      error: (err.body || err.message || 'unknown').slice(0, 300),
    };
  }
}

/**
 * Sends to many, with bounded concurrency.
 *
 * Concurrency is capped because a campaign to 50,000 subscribers would
 * otherwise open 50,000 sockets at once and be throttled — or dropped — by
 * every push service at the same moment. Failures never reject: one dead
 * endpoint must not abandon the other 49,999.
 */
export async function sendMany(
  subscriptions: Subscription[],
  notification: Notification,
  vapid: Vapid,
  opts: { concurrency?: number; onOutcome?: (o: SendOutcome) => void } = {},
): Promise<SendOutcome[]> {
  const limit = Math.max(1, opts.concurrency ?? 50);
  const outcomes: SendOutcome[] = [];
  let cursor = 0;

  const worker = async () => {
    while (cursor < subscriptions.length) {
      const sub = subscriptions[cursor++]!;
      const outcome = await sendOne(sub, notification, vapid);
      outcomes.push(outcome);
      opts.onOutcome?.(outcome);
    }
  };

  await Promise.all(Array.from({ length: Math.min(limit, subscriptions.length) }, worker));
  return outcomes;
}

export function summarise(outcomes: SendOutcome[]) {
  return {
    sent: outcomes.filter((o) => o.status === 'sent').length,
    expired: outcomes.filter((o) => o.status === 'expired').length,
    rateLimited: outcomes.filter((o) => o.status === 'rate_limited').length,
    failed: outcomes.filter((o) => o.status === 'failed').length,
  };
}
