/**
 * Publishing a document to somebody else's CMS.
 *
 * Four destinations behind one interface. What they have in common is more
 * interesting than what they do not: each takes a title, a body, a status and
 * a slug, and each returns an id and a URL — so the pipeline never branches on
 * destination kind, and adding a fifth is one function.
 *
 * **Every adapter defaults to draft.** The `status` is passed through from the
 * destination's own setting, and that setting defaults to `draft` in the schema
 * because a pipeline that publishes unreviewed generated text to a live site is
 * one bad prompt away from an incident the customer hears about from a reader.
 *
 * **Failures are classified, not just reported.** "Your token expired" and "the
 * site is down" need different responses — one from the customer, one from
 * nobody — and a pipeline that marks itself broken on a 502 is a pipeline
 * people switch off.
 */

export type PublishInput = {
  title: string;
  /** Markdown. Adapters that need HTML convert; the ones that take Markdown do not. */
  body: string;
  html: string;
  slug?: string;
  excerpt?: string;
  status: 'draft' | 'publish';
  tags?: string[];
};

export type PublishResult = {
  ok: true;
  externalId: string;
  url: string | null;
  /** What the remote actually did — it may downgrade a publish to pending. */
  status: string;
};

export type PublishFailure = {
  ok: false;
  reason: 'unauthorised' | 'forbidden' | 'not_found' | 'rejected' | 'rate_limited' | 'server' | 'network';
  message: string;
  retryAfterSeconds?: number;
};

export type Publisher = (input: PublishInput) => Promise<PublishResult | PublishFailure>;

export type Fetcher = typeof fetch;

export class PublishError extends Error {
  constructor(
    readonly reason: PublishFailure['reason'],
    message: string,
    readonly retryAfterSeconds?: number,
  ) {
    super(message);
    this.name = 'PublishError';
  }
}

/* ------------------------------------------------------------- WordPress */

/**
 * WordPress via the REST API and an application password.
 *
 * Application passwords rather than OAuth because the plugin-free WordPress a
 * customer already runs supports them out of the box, and asking somebody to
 * install a plugin to use a feature is where adoption stops.
 *
 * The one WordPress-specific trap: `status: 'publish'` on a site whose user
 * lacks `publish_posts` silently produces a `pending` post rather than an
 * error, so the *returned* status is read back rather than assumed.
 */
export function wordpressPublisher(config: {
  siteUrl: string;
  username: string;
  applicationPassword: string;
  fetchImpl?: Fetcher;
}): Publisher {
  const doFetch = config.fetchImpl ?? fetch;
  const base = config.siteUrl.replace(/\/+$/, '');
  const auth = Buffer.from(`${config.username}:${config.applicationPassword}`).toString('base64');

  return async (input) => {
    try {
      const response = await doFetch(`${base}/wp-json/wp/v2/posts`, {
        method: 'POST',
        headers: {
          authorization: `Basic ${auth}`,
          'content-type': 'application/json',
        },
        body: JSON.stringify({
          title: input.title,
          content: input.html,
          excerpt: input.excerpt ?? '',
          slug: input.slug,
          status: input.status,
        }),
      });

      if (!response.ok) return await failureFrom(response, 'WordPress');

      const post = (await response.json()) as { id: number; link?: string; status?: string };
      return {
        ok: true,
        externalId: String(post.id),
        url: post.link ?? null,
        // Read back, never assumed — see above.
        status: post.status ?? input.status,
      };
    } catch (err) {
      return networkFailure(err, 'WordPress');
    }
  };
}

/* ------------------------------------------------------------------ Ghost */

/**
 * Ghost via the Admin API.
 *
 * Ghost stores posts as Lexical/Mobiledoc, but its Admin API accepts raw HTML
 * when you ask for it with `?source=html` — without that parameter the `html`
 * field is silently ignored and you get an empty post, which is the single most
 * confusing failure in this integration because it returns 201.
 */
export function ghostPublisher(config: {
  adminApiUrl: string;
  /** A signed Admin API JWT. Minted by the caller so this stays testable. */
  token: string;
  fetchImpl?: Fetcher;
}): Publisher {
  const doFetch = config.fetchImpl ?? fetch;
  const base = config.adminApiUrl.replace(/\/+$/, '');

  return async (input) => {
    try {
      const response = await doFetch(`${base}/ghost/api/admin/posts/?source=html`, {
        method: 'POST',
        headers: {
          authorization: `Ghost ${config.token}`,
          'content-type': 'application/json',
          'accept-version': 'v5.0',
        },
        body: JSON.stringify({
          posts: [{
            title: input.title,
            html: input.html,
            slug: input.slug,
            custom_excerpt: input.excerpt,
            status: input.status === 'publish' ? 'published' : 'draft',
            tags: input.tags?.map((name) => ({ name })),
          }],
        }),
      });

      if (!response.ok) return await failureFrom(response, 'Ghost');

      const body = (await response.json()) as {
        posts?: { id: string; url?: string; status?: string }[];
      };
      const post = body.posts?.[0];
      if (!post) {
        return { ok: false, reason: 'rejected', message: 'Ghost accepted the request but returned no post.' };
      }
      return { ok: true, externalId: post.id, url: post.url ?? null, status: post.status ?? 'draft' };
    } catch (err) {
      return networkFailure(err, 'Ghost');
    }
  };
}

/* ---------------------------------------------------------------- Shopify */

/** Shopify blog articles through the Admin GraphQL-adjacent REST endpoint. */
export function shopifyPublisher(config: {
  shop: string;
  blogId: string;
  accessToken: string;
  apiVersion?: string;
  fetchImpl?: Fetcher;
}): Publisher {
  const doFetch = config.fetchImpl ?? fetch;
  const version = config.apiVersion ?? '2025-01';
  const host = config.shop.replace(/^https?:\/\//, '').replace(/\/+$/, '');

  return async (input) => {
    try {
      const response = await doFetch(
        `https://${host}/admin/api/${version}/blogs/${config.blogId}/articles.json`,
        {
          method: 'POST',
          headers: {
            'x-shopify-access-token': config.accessToken,
            'content-type': 'application/json',
          },
          body: JSON.stringify({
            article: {
              title: input.title,
              body_html: input.html,
              handle: input.slug,
              summary_html: input.excerpt,
              tags: input.tags?.join(', '),
              // Shopify has no "draft": an article is published iff it has a
              // published_at. Null is the draft.
              published: input.status === 'publish',
            },
          }),
        },
      );

      if (!response.ok) return await failureFrom(response, 'Shopify');

      const body = (await response.json()) as {
        article?: { id: number; handle?: string; published_at?: string | null };
      };
      if (!body.article) {
        return { ok: false, reason: 'rejected', message: 'Shopify returned no article.' };
      }
      return {
        ok: true,
        externalId: String(body.article.id),
        url: body.article.handle ? `https://${host}/blogs/${config.blogId}/${body.article.handle}` : null,
        status: body.article.published_at ? 'published' : 'draft',
      };
    } catch (err) {
      return networkFailure(err, 'Shopify');
    }
  };
}

/* ---------------------------------------------------------------- Webhook */

/**
 * A signed POST to the customer's own endpoint.
 *
 * The escape hatch for every CMS we do not support, and the reason the
 * destination list is not a roadmap. Signed with HMAC-SHA256 over the exact
 * body bytes so the receiver can verify it, with a timestamp in the signed
 * material so a captured request cannot be replayed a week later.
 */
export function webhookPublisher(config: {
  url: string;
  secret?: string;
  fetchImpl?: Fetcher;
  now?: () => Date;
}): Publisher {
  const doFetch = config.fetchImpl ?? fetch;

  return async (input) => {
    const timestamp = Math.floor((config.now?.() ?? new Date()).getTime() / 1000);
    const payload = JSON.stringify({ ...input, timestamp });

    const headers: Record<string, string> = { 'content-type': 'application/json' };
    if (config.secret) {
      const { createHmac } = await import('node:crypto');
      headers['x-mamal-timestamp'] = String(timestamp);
      headers['x-mamal-signature'] = createHmac('sha256', config.secret)
        .update(`${timestamp}.${payload}`)
        .digest('hex');
    }

    try {
      const response = await doFetch(config.url, { method: 'POST', headers, body: payload });
      if (!response.ok) return await failureFrom(response, 'Your endpoint');

      // A webhook receiver may return anything or nothing; neither is an error.
      let externalId = '';
      let url: string | null = null;
      try {
        const body = (await response.json()) as { id?: string; url?: string };
        externalId = body.id ?? '';
        url = body.url ?? null;
      } catch {
        // No body, or not JSON. The 2xx is the answer.
      }
      return { ok: true, externalId, url, status: input.status };
    } catch (err) {
      return networkFailure(err, 'Your endpoint');
    }
  };
}

/* ----------------------------------------------------------------- shared */

/**
 * Which failures are the customer's to fix.
 *
 * A 401 means reconnect; a 502 means wait. Marking a destination broken on a
 * transient upstream error trains people to ignore the badge that is supposed
 * to mean "you must act" — the same rule the Search Console sync follows.
 */
async function failureFrom(response: Response, who: string): Promise<PublishFailure> {
  const detail = await safeText(response);

  if (response.status === 401) {
    return { ok: false, reason: 'unauthorised', message: `${who} rejected the credentials. Reconnect it.` };
  }
  if (response.status === 403) {
    return {
      ok: false,
      reason: 'forbidden',
      message: `${who} refused: the account may not be allowed to create posts. ${detail}`.trim(),
    };
  }
  if (response.status === 404) {
    return { ok: false, reason: 'not_found', message: `${who} could not find that blog or endpoint.` };
  }
  if (response.status === 429) {
    const retry = Number(response.headers.get('retry-after'));
    return {
      ok: false,
      reason: 'rate_limited',
      message: `${who} is rate limiting us. The next run will pick this up.`,
      retryAfterSeconds: Number.isFinite(retry) && retry > 0 ? retry : 60,
    };
  }
  if (response.status >= 500) {
    return { ok: false, reason: 'server', message: `${who} returned ${response.status}. This is usually temporary.` };
  }
  return {
    ok: false,
    reason: 'rejected',
    message: `${who} rejected the post (${response.status}). ${detail}`.trim(),
  };
}

function networkFailure(err: unknown, who: string): PublishFailure {
  return {
    ok: false,
    reason: 'network',
    message: `Could not reach ${who}: ${err instanceof Error ? err.message : String(err)}`,
  };
}

async function safeText(response: Response): Promise<string> {
  try {
    return (await response.text()).slice(0, 300);
  } catch {
    return '';
  }
}
