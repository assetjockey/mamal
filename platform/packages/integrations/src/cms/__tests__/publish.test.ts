/**
 * The CMS publishers.
 *
 * What is worth testing here is not "it posts" — it is the handful of remote
 * behaviours that are silently wrong if you assume the obvious: Ghost ignoring
 * your HTML unless asked, WordPress downgrading a publish without erroring,
 * Shopify having no concept of a draft, and the difference between a failure
 * the customer must fix and one that will fix itself.
 */
import { describe, expect, it, vi } from 'vitest';
import {
  ghostPublisher, shopifyPublisher, webhookPublisher, wordpressPublisher,
} from '../publish.ts';

const post = {
  title: 'Widget racks',
  body: '# Widget racks\n\nThey hold widgets.',
  html: '<h1>Widget racks</h1><p>They hold widgets.</p>',
  slug: 'widget-racks',
  status: 'draft' as const,
};

const json = (body: unknown, init: ResponseInit = {}) =>
  new Response(JSON.stringify(body), {
    status: init.status ?? 200,
    headers: { 'content-type': 'application/json', ...(init.headers ?? {}) },
  });

describe('WordPress', () => {
  it('reads the status back rather than assuming it', async () => {
    /*
     * A user without `publish_posts` gets a `pending` post and a 201. Assuming
     * the requested status means telling the customer their article is live
     * when it is sitting in a queue nobody watches.
     */
    const fetchImpl = vi.fn(async () =>
      json({ id: 41, link: 'https://site.test/?p=41', status: 'pending' }),
    );
    const publish = wordpressPublisher({
      siteUrl: 'https://site.test/', username: 'ed', applicationPassword: 'pw',
      fetchImpl: fetchImpl as unknown as typeof fetch,
    });

    const result = await publish({ ...post, status: 'publish' });
    expect(result).toMatchObject({ ok: true, externalId: '41', status: 'pending' });
  });

  it('sends HTML, not Markdown', async () => {
    const fetchImpl = vi.fn(async (_url: string, _init?: RequestInit) => json({ id: 1 }));
    const publish = wordpressPublisher({
      siteUrl: 'https://site.test', username: 'ed', applicationPassword: 'pw',
      fetchImpl: fetchImpl as unknown as typeof fetch,
    });
    await publish(post);

    const body = JSON.parse(fetchImpl.mock.calls[0]![1]!.body as string);
    expect(body.content).toContain('<h1>');
    expect(body.content).not.toContain('# Widget');
  });
});

describe('Ghost', () => {
  it('asks for the HTML source, or the post arrives empty', async () => {
    /*
     * Without `?source=html` Ghost accepts the request, returns 201, and stores
     * a post with no body. The most confusing failure in this integration
     * precisely because nothing looks wrong.
     */
    const fetchImpl = vi.fn(async (_url: string, _init?: RequestInit) =>
      json({ posts: [{ id: 'p1', url: 'https://blog.test/widget-racks/', status: 'draft' }] }),
    );
    const publish = ghostPublisher({
      adminApiUrl: 'https://blog.test', token: 'jwt',
      fetchImpl: fetchImpl as unknown as typeof fetch,
    });

    const result = await publish(post);
    expect(fetchImpl.mock.calls[0]![0]).toContain('source=html');
    expect(result).toMatchObject({ ok: true, externalId: 'p1' });
  });

  it('translates publish into Ghost’s own word for it', async () => {
    const fetchImpl = vi.fn(async (_url: string, _init?: RequestInit) =>
      json({ posts: [{ id: 'p1', status: 'published' }] }),
    );
    const publish = ghostPublisher({
      adminApiUrl: 'https://blog.test', token: 'jwt',
      fetchImpl: fetchImpl as unknown as typeof fetch,
    });
    await publish({ ...post, status: 'publish' });

    const body = JSON.parse(fetchImpl.mock.calls[0]![1]!.body as string);
    expect(body.posts[0].status).toBe('published');
  });

  it('does not claim success when Ghost returns nothing usable', async () => {
    const publish = ghostPublisher({
      adminApiUrl: 'https://blog.test', token: 'jwt',
      fetchImpl: (async () => json({ posts: [] })) as unknown as typeof fetch,
    });
    expect(await publish(post)).toMatchObject({ ok: false, reason: 'rejected' });
  });
});

describe('Shopify', () => {
  it('expresses draft as unpublished, because Shopify has no draft', async () => {
    const fetchImpl = vi.fn(async (_url: string, _init?: RequestInit) =>
      json({ article: { id: 9, handle: 'widget-racks', published_at: null } }),
    );
    const publish = shopifyPublisher({
      shop: 'shop.myshopify.com', blogId: '12', accessToken: 'tok',
      fetchImpl: fetchImpl as unknown as typeof fetch,
    });

    const result = await publish(post);
    const body = JSON.parse(fetchImpl.mock.calls[0]![1]!.body as string);
    expect(body.article.published).toBe(false);
    // An article is published iff it has a published_at; null is the draft.
    expect(result).toMatchObject({ ok: true, status: 'draft' });
  });
});

describe('the webhook escape hatch', () => {
  it('signs the exact bytes it sends, with the timestamp inside the signature', async () => {
    const fetchImpl = vi.fn(async (_url: string, _init?: RequestInit) => json({ id: 'x1' }));
    const publish = webhookPublisher({
      url: 'https://hook.test/in', secret: 'shh',
      fetchImpl: fetchImpl as unknown as typeof fetch,
      now: () => new Date('2026-03-20T09:00:00Z'),
    });

    await publish(post);
    const [, init] = fetchImpl.mock.calls[0]!;
    const headers = init!.headers as Record<string, string>;
    const timestamp = headers['x-mamal-timestamp']!;

    const { createHmac } = await import('node:crypto');
    const expected = createHmac('sha256', 'shh')
      .update(`${timestamp}.${init!.body as string}`)
      .digest('hex');

    // Signing the body alone would let a captured request be replayed forever.
    expect(headers['x-mamal-signature']).toBe(expected);
    expect(timestamp).toBe('1773997200');
  });

  it('accepts a 2xx with no body at all', async () => {
    const publish = webhookPublisher({
      url: 'https://hook.test/in',
      fetchImpl: (async () => new Response(null, { status: 204 })) as unknown as typeof fetch,
    });
    // A receiver that answers 204 has done its job; demanding JSON would fail
    // the most ordinary implementation somebody could write.
    expect(await publish(post)).toMatchObject({ ok: true, externalId: '' });
  });
});

describe('whose problem a failure is', () => {
  const failing = (status: number, headers: Record<string, string> = {}) =>
    webhookPublisher({
      url: 'https://hook.test/in',
      fetchImpl: (async () => new Response('nope', { status, headers })) as unknown as typeof fetch,
    })(post);

  it('separates credentials from permissions from outages', async () => {
    expect(await failing(401)).toMatchObject({ reason: 'unauthorised' });
    expect(await failing(403)).toMatchObject({ reason: 'forbidden' });
    expect(await failing(404)).toMatchObject({ reason: 'not_found' });
    expect(await failing(422)).toMatchObject({ reason: 'rejected' });
    // Nobody's problem: it resolves itself, and marking the destination broken
    // would train people to ignore the badge that means "act".
    expect(await failing(503)).toMatchObject({ reason: 'server' });
  });

  it('honours a retry-after rather than guessing', async () => {
    expect(await failing(429, { 'retry-after': '120' })).toMatchObject({
      reason: 'rate_limited', retryAfterSeconds: 120,
    });
    // Absent or nonsense header falls back to a sane wait rather than zero.
    expect(await failing(429)).toMatchObject({ reason: 'rate_limited', retryAfterSeconds: 60 });
  });

  it('reports an unreachable host as a network problem, not a rejection', async () => {
    const publish = webhookPublisher({
      url: 'https://hook.test/in',
      fetchImpl: (async () => {
        throw new Error('ECONNREFUSED');
      }) as unknown as typeof fetch,
    });
    expect(await publish(post)).toMatchObject({ reason: 'network' });
  });
});
