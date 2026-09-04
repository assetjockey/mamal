/**
 * The pages a redirect can land on instead of redirecting.
 *
 * Rendered as strings, not React. Four reasons, all of them the same reason:
 * this is the hot path.
 *
 * - No hydration, no client bundle, no framework boot. A password prompt is
 *   one form; shipping React to draw it would cost more than the redirect.
 * - Inline styles, so there is no stylesheet request and no flash.
 * - They must render identically at the edge, where there is no React runtime
 *   at all — the same function will move to the worker unchanged.
 * - They are the *only* thing a visitor sees when something is wrong, so they
 *   have to work with CSS disabled, on a slow connection, in dark mode.
 */

const esc = (s: string) =>
  String(s ?? '').replace(/[&<>"']/g, (c) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]!);

/**
 * One shell for every gate page.
 *
 * The tokens are inlined rather than imported because this file has no
 * stylesheet — but they are the same values as `packages/ui/tokens.css`, and
 * `prefers-color-scheme` is honoured so a visitor's phone does not flash white
 * at midnight.
 */
function shell(title: string, body: string, extraHead = ''): string {
  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>${esc(title)}</title>
${extraHead}
<style>
  :root {
    color-scheme: light dark;
    --ink: #061b31; --muted: #50617a; --surface: #ffffff; --ground: #f8fafd;
    --hairline: #e5edf5; --accent: #533afd; --on-accent: #ffffff;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --ink: #f2f6fc; --muted: #9fb0c9; --surface: #111827; --ground: #0a0f1a;
      --hairline: #1f2a3d; --accent: #7d6bff;
    }
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100dvh; display: grid; place-items: center; padding: 24px;
    background: var(--ground); color: var(--ink);
    font: 300 16px/1.5 'Inter Tight', Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    -webkit-font-smoothing: antialiased;
  }
  main {
    width: 100%; max-width: 420px; background: var(--surface);
    border: 1px solid var(--hairline); border-radius: 4px; padding: 32px;
  }
  h1 { margin: 0 0 8px; font-size: 22px; font-weight: 400; letter-spacing: -0.01em; }
  p { margin: 0 0 20px; color: var(--muted); font-size: 14px; }
  label { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em;
          color: var(--muted); margin-bottom: 6px; }
  input {
    width: 100%; padding: 10px 12px; font: inherit; font-size: 16px; color: var(--ink);
    background: var(--ground); border: 1px solid var(--hairline); border-radius: 4px;
  }
  input:focus-visible, button:focus-visible, a:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }
  button, .btn {
    display: inline-block; width: 100%; margin-top: 16px; padding: 10px 16px;
    font: inherit; font-size: 14px; text-align: center; text-decoration: none;
    color: var(--on-accent); background: var(--accent);
    border: 1px solid var(--accent); border-radius: 4px; cursor: pointer;
  }
  .ghost { color: var(--ink); background: transparent; border-color: var(--hairline); }
  .error { color: #c5221f; font-size: 13px; margin: 0 0 16px; }
  @media (prefers-color-scheme: dark) { .error { color: #f87171; } }
  .foot { margin: 20px 0 0; font-size: 12px; color: var(--muted); text-align: center; }
  .foot a { color: inherit; }
  @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
</style>
</head>
<body><main>${body}</main></body>
</html>`;
}

export function passwordPage(opts: { alias: string; wrong?: boolean }): string {
  return shell(
    'Password required',
    `
    <h1>This link is protected</h1>
    <p>Enter the password you were given to continue.</p>
    ${opts.wrong ? '<p class="error">That password is not right. Try again.</p>' : ''}
    <form method="post" action="">
      <label for="p">Password</label>
      <input id="p" name="password" type="password" autocomplete="off" autofocus required>
      <button type="submit">Continue</button>
    </form>`,
  );
}

/**
 * The interstitial for content the owner marked sensitive.
 *
 * No auto-advance and no countdown: the entire point is that the visitor makes
 * a choice. "Go back" is a real link to the referrer rather than
 * `history.back()`, because a visitor who arrived directly has no history and
 * would be left on a dead button.
 */
export function interstitialPage(opts: { url: string; backUrl?: string }): string {
  return shell(
    'Content warning',
    `
    <h1>This link may contain sensitive content</h1>
    <p>The person who created this link has marked its destination as sensitive.
       You are about to go to <strong>${esc(hostOf(opts.url))}</strong>.</p>
    <a class="btn" href="${esc(opts.url)}" rel="noopener noreferrer">Continue anyway</a>
    <a class="btn ghost" href="${esc(opts.backUrl ?? '/')}">Go back</a>`,
  );
}

/**
 * The splash page a link can be sent through.
 *
 * Skippable by default, and the skip button is real markup rather than
 * something JavaScript adds — a visitor with scripts blocked must still be able
 * to leave. The auto-advance is a `<meta refresh>` for the same reason: it
 * works without JavaScript, and the browser's own back button behaves
 * correctly with it, which `location.replace` in a timer does not.
 */
export function splashPage(opts: {
  url: string;
  delaySeconds: number;
  skippable: boolean;
  autoRedirect: boolean;
  title?: string;
  body?: string;
}): string {
  const delay = Math.max(0, Math.min(60, opts.delaySeconds));
  return shell(
    opts.title ?? 'One moment',
    `
    <h1>${esc(opts.title ?? 'One moment')}</h1>
    <p>${esc(opts.body ?? `Taking you to ${hostOf(opts.url)}.`)}</p>
    ${opts.skippable || !opts.autoRedirect
      ? `<a class="btn" href="${esc(opts.url)}" rel="noopener">Continue now</a>`
      : `<p class="foot">You will be forwarded in ${delay} second${delay === 1 ? '' : 's'}.</p>`}`,
    opts.autoRedirect
      ? `<meta http-equiv="refresh" content="${delay};url=${esc(opts.url)}">`
      : '',
  );
}

/**
 * Expired, over its click limit, or simply not a link we have.
 *
 * All three answer 404 with the same page unless the owner set a fallback URL.
 * Distinguishing "expired" from "never existed" tells anyone who asks which
 * aliases have been used, which is how a competitor maps a campaign.
 */
export function gonePage(_opts: { reason: 'expired' | 'click_limit' | 'not_found' }): string {
  // The reason is taken and deliberately unused: callers pass it so the intent
  // is legible at the call site, and this renders the same page for all three.
  return shell(
    'Link unavailable',
    `
    <h1>This link is not available</h1>
    <p>It may have expired, been removed, or never existed.
       If somebody sent it to you, ask them for a new one.</p>`,
  );
}

export function blockedPage(opts: { reason: 'moderation' | 'disabled' | 'rule' }): string {
  const moderated = opts.reason === 'moderation';
  return shell(
    'Link blocked',
    `
    <h1>${moderated ? 'This link has been blocked' : 'This link is not active'}</h1>
    <p>${moderated
      ? 'It was reported and removed for violating our acceptable use policy.'
      : 'The person who created it has turned it off.'}</p>`,
  );
}

function hostOf(url: string): string {
  try {
    return new URL(url).host;
  } catch {
    return url;
  }
}
