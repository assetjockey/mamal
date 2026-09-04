import { createHash } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { resolve } from '@mamal/redirect';
import { visitorFrom } from '@mamal/geo';
import {
  loadAssignment, loadForResolve, recordClick, rememberAssignment, verifyPassword,
} from '@mamal/tool-link';
import { db } from '@/lib/db';
import {
  blockedPage, gonePage, interstitialPage, passwordPage, splashPage,
} from '@/lib/redirect-pages';

/**
 * The redirect.
 *
 * This is the busiest route in the platform and the one with the least room:
 * every millisecond here is a millisecond a visitor spends staring at a blank
 * tab. It is deliberately a route handler rather than a page — no React, no
 * RSC payload, no hydration — and it does exactly two queries on the happy
 * path: load the link with its rules, and bump the counter.
 *
 * **On the p99 budget.** The plan asks for p99 < 25 ms at the edge, on
 * Cloudflare Workers with the slug cached in KV. That tier is deliberately
 * deferred (Risk 2: phase the infrastructure, not the interfaces), and this
 * origin cannot meet it — a Node process plus a Postgres round trip is tens of
 * milliseconds before anything else happens. The number is honest about which
 * tier it describes; `resolve()` is a pure function taking plain data precisely
 * so the same logic moves to the worker without a rewrite when it is worth it.
 *
 * Runs as platform admin because there is no session: the alias *is* the
 * lookup, and everything after it is scoped to the row it found.
 */

export const dynamic = 'force-dynamic';

const NO_STORE = {
  /*
   * Never cached, at any layer.
   *
   * A link's destination is editable by definition and its rules are evaluated
   * per visitor: a cached 302 would send one visitor's German variant to
   * everybody behind the same proxy, and would keep sending it after the
   * customer changed the destination.
   */
  'cache-control': 'no-store, no-cache, must-revalidate',
  'referrer-policy': 'no-referrer-when-downgrade',
  'x-robots-tag': 'noindex, nofollow',
} as const;

const html = (body: string, status = 200) =>
  new Response(body, { status, headers: { ...NO_STORE, 'content-type': 'text/html; charset=utf-8' } });

export async function GET(request: Request, ctx: { params: Promise<{ alias: string }> }) {
  return handle(request, ctx, undefined);
}

/** The password form posts back to the same URL. */
export async function POST(request: Request, ctx: { params: Promise<{ alias: string }> }) {
  let password: string | undefined;
  try {
    const form = await request.formData();
    password = String(form.get('password') ?? '');
  } catch {
    password = undefined;
  }
  return handle(request, ctx, password);
}

async function handle(
  request: Request,
  ctx: { params: Promise<{ alias: string }> },
  password: string | undefined,
): Promise<Response> {
  const { alias } = await ctx.params;
  const url = new URL(request.url);
  const visitor = visitorFrom(request);

  const loaded = await asPlatformAdmin(
    (tx) => loadForResolve(tx, { alias, customDomainId: customDomainOf(request) }),
    { db: db() },
  );
  if (!loaded) return html(gonePage({ reason: 'not_found' }), 404);

  const { link, rules, workspaceId } = loaded;

  /*
   * The visitor hash: salted, daily-rotating, and never stored alongside an IP.
   *
   * It exists to keep one person on one arm of an A/B test for a day. That is
   * the entire purpose, and it is why the salt rotates — a hash that persisted
   * would become an identifier, which is not what anyone consented to.
   */
  const visitorHash = hashVisitor(request, link.id);

  const ruleIds = rules.filter((r) => r.action.type === 'rotate' && r.sticky).map((r) => r.id);
  const assignment = ruleIds.length
    ? await asPlatformAdmin((tx) => loadAssignment(tx, { ruleIds, visitorHash }), { db: db() })
    : null;

  const passwordVerified =
    password !== undefined && link.passwordHash !== null
      ? verifyPassword(link.passwordHash, password)
      : false;

  const outcome = resolve({
    link,
    rules,
    visitor: { ...visitor, visitorHash } as never,
    query: url.search.replace(/^\?/, ''),
    assignment,
    passwordVerified,
  });

  switch (outcome.kind) {
    case 'password':
      // A wrong password is only reported when one was actually submitted —
      // a GET must not render an error for something nobody typed.
      return html(passwordPage({ alias, wrong: password !== undefined }), password !== undefined ? 401 : 200);

    case 'blocked':
      return html(blockedPage({ reason: outcome.reason }), outcome.reason === 'moderation' ? 451 : 410);

    case 'gone':
      // A fallback URL is the owner's own answer to "what should happen after
      // this expires", so it wins over our page.
      if (outcome.url) return redirectTo(outcome.url);
      return html(gonePage({ reason: outcome.reason }), 410);

    case 'not_found':
      return html(gonePage({ reason: 'not_found' }), 404);

    case 'render':
      // Bio pages, vCards, events and transfers are rendered by the public app,
      // which owns their templates. This route only decides *that* they render.
      return redirectTo(`${url.origin}/p/${outcome.what}/${link.id}${url.search}`);

    case 'interstitial':
      await count(link.id, visitor.isBot);
      return html(interstitialPage({
        url: outcome.url,
        backUrl: request.headers.get('referer') ?? undefined,
      }));

    case 'splash': {
      await count(link.id, visitor.isBot);
      const splash = await asPlatformAdmin(
        (tx) => tx.execute<{
          delay_seconds: number; is_skippable: boolean; auto_redirect: boolean;
          settings: { title?: string; body?: string };
        }>(sql`
          select delay_seconds, is_skippable, auto_redirect, settings
            from splash_pages
           where id = ${outcome.splashPageId}::uuid and workspace_id = ${workspaceId}`),
        { db: db() },
      );
      const page = splash[0];
      // A splash page that has been deleted must not strand the visitor on a
      // blank interstitial — the link still has somewhere to go.
      if (!page) return redirectTo(outcome.url);
      return html(splashPage({
        url: outcome.url,
        delaySeconds: page.delay_seconds,
        skippable: page.is_skippable,
        autoRedirect: page.auto_redirect,
        title: page.settings?.title,
        body: page.settings?.body,
      }));
    }

    case 'redirect': {
      await count(link.id, visitor.isBot);
      if (outcome.ruleId && outcome.variantIndex !== undefined) {
        const rule = rules.find((r) => r.id === outcome.ruleId);
        if (rule?.sticky && !assignment) {
          await asPlatformAdmin(
            (tx) => rememberAssignment(tx, {
              workspaceId, linkId: link.id, ruleId: rule.id,
              visitorHash, variantIndex: outcome.variantIndex!,
            }),
            { db: db() },
          );
        }
      }
      return redirectTo(outcome.url);
    }
  }
}

function redirectTo(location: string): Response {
  // 302 rather than 307: the password form POSTs here, and a 307 would replay
  // that POST at the customer's destination.
  return new Response(null, { status: 302, headers: { ...NO_STORE, location } });
}

/**
 * Counts a click, and does not count a bot.
 *
 * A crawler or a Slack unfurler following a link must not consume a click
 * limit — a link limited to 100 opens would be exhausted before a person saw
 * it, and an A/B test would report on traffic that is entirely automated.
 */
async function count(linkId: string, isBot: boolean): Promise<void> {
  if (isBot) return;
  await asPlatformAdmin((tx) => recordClick(tx, { linkId }), { db: db() });
}

/**
 * Which custom domain this request arrived on, if any.
 *
 * Null means the platform domain, which is what the partial unique index on
 * `links` treats as its own namespace. Host routing proper lands with custom
 * domains; until then every request is on the platform domain.
 */
function customDomainOf(_request: Request): string | null {
  return null;
}

const SALT_PERIOD_MS = 24 * 60 * 60 * 1000;

function hashVisitor(request: Request, linkId: string): string {
  const ip =
    request.headers.get('cf-connecting-ip') ??
    request.headers.get('x-forwarded-for')?.split(',')[0]?.trim() ??
    '';
  const day = Math.floor(Date.now() / SALT_PERIOD_MS);
  return createHash('sha256')
    .update(`${process.env.VISITOR_SALT ?? 'dev-salt'}|${day}|${ip}|${request.headers.get('user-agent') ?? ''}|${linkId}`)
    .digest('hex')
    .slice(0, 16);
}
