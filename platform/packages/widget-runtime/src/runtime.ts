/**
 * confirm.js — the widget runtime.
 *
 * This runs on other people's websites, so the constraints are not stylistic:
 *
 *   - **Under 12 KB gzipped**, asserted by a test. No framework, no polyfills.
 *   - **Never throws into the host page.** Every entry point is wrapped; a
 *     failure means no widget, never a broken site.
 *   - **No layout shift.** Everything is `position: fixed` inside a shadow root,
 *     except `inline`, which renders into an element the customer chose.
 *   - **One request.** The whole config — widgets, targeting, and a rolling
 *     window of conversions — arrives in a single edge-cached payload, and
 *     targeting is evaluated locally. A per-impression round trip would put our
 *     latency on their page.
 *   - **Shadow DOM**, so the host's CSS cannot deform us and ours cannot leak.
 */

import { matches, type VisitorContext } from '@mamal/targeting';
import { allowedToShow, fill, timeAgo } from './helpers.ts';

type Widget = {
  id: string;
  type: string;
  family: string;
  theme: Record<string, string>;
  position: string;
  settings: Record<string, unknown>;
  targeting: unknown;
  delaySeconds: number;
  durationSeconds: number;
  displayFrequency: string;
  displayLimit: number;
  showBranding: boolean;
};

type Conversion = Record<string, unknown> & { occurredAt?: string };

type Payload = {
  campaignId: string;
  widgets: Widget[];
  conversions: Conversion[];
  counts: Record<string, number>;
  ingest: string;
};

const W = window;
const D = document;
const STORE_KEY = 'mamal.c';

/* --------------------------------------------------------------- utilities */

/** Everything user-facing goes through here — never `innerHTML`. */
const el = (tag: string, props: Record<string, unknown> = {}, kids: (Node | string)[] = []) => {
  const node = D.createElement(tag);
  for (const k in props) {
    const v = props[k];
    if (v === undefined || v === null) continue;
    if (k === 'style') Object.assign(node.style, v as object);
    else if (k.startsWith('on')) node.addEventListener(k.slice(2), v as EventListener);
    else node.setAttribute(k, String(v));
  }
  for (const kid of kids) node.append(kid);
  return node;
};

/** Never let our failure become their failure. */
const safely = <T>(fn: () => T, fallback: T): T => {
  try {
    return fn();
  } catch {
    return fallback;
  }
};

/** localStorage throws in private mode and when site data is blocked. */
const store = {
  read(): Record<string, { n: number; t: number }> {
    return safely(() => JSON.parse(W.localStorage.getItem(STORE_KEY) || '{}'), {});
  },
  write(v: unknown) {
    safely(() => W.localStorage.setItem(STORE_KEY, JSON.stringify(v)), undefined);
  },
};

/* --------------------------------------------------- the visitor's context */

function buildContext(): VisitorContext {
  const ua = navigator.userAgent;
  const mobile = /Mobi|Android|iPhone/i.test(ua);
  const tablet = /iPad|Tablet/i.test(ua);
  const params = new URLSearchParams(location.search);
  const now = new Date();

  let referrerHost = '';
  safely(() => {
    referrerHost = D.referrer ? new URL(D.referrer).hostname : '';
  }, undefined);

  return {
    path: location.pathname,
    url: location.href,
    referrer: D.referrer,
    referrerHost,
    utm: {
      source: params.get('utm_source') || undefined,
      medium: params.get('utm_medium') || undefined,
      campaign: params.get('utm_campaign') || undefined,
      term: params.get('utm_term') || undefined,
      content: params.get('utm_content') || undefined,
    },
    device: tablet ? 'tablet' : mobile ? 'mobile' : 'desktop',
    os: /Windows/.test(ua) ? 'Windows' : /Mac/.test(ua) ? 'macOS'
      : /Android/.test(ua) ? 'Android' : /iPhone|iPad/.test(ua) ? 'iOS'
      : /Linux/.test(ua) ? 'Linux' : 'Other',
    browser: /Edg\//.test(ua) ? 'Edge' : /Chrome\//.test(ua) ? 'Chrome'
      : /Safari\//.test(ua) ? 'Safari' : /Firefox\//.test(ua) ? 'Firefox' : 'Other',
    language: navigator.language,
    // Geo is stamped by the edge onto the payload — never guessed here, and
    // never derived from an IP the browser would have to send us.
    visitorType: store.read().__seen ? 'returning' : 'new',
    sessionPages: sessionPages(),
    secondsOnPage: 0,
    scrollDepth: 0,
    dayOfWeek: now.getDay(),
    hour: now.getHours(),
  };
}

function sessionPages(): number {
  return safely(() => {
    const n = Number(W.sessionStorage.getItem('mamal.p') || '0') + 1;
    W.sessionStorage.setItem('mamal.p', String(n));
    return n;
  }, 1);
}

/* ------------------------------------------------------------------ styles */

/**
 * One stylesheet, adopted into every shadow root.
 *
 * Adopted rather than inlined per widget: a single `CSSStyleSheet` is parsed
 * once no matter how many widgets a page has.
 */
const CSS = `
:host{all:initial}
.w{position:fixed;z-index:2147483000;font:400 14px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
color:var(--w-fg);background:var(--w-bg);border:1px solid var(--w-border);border-radius:10px;
box-shadow:0 6px 24px rgba(0,0,0,.12);max-width:340px;overflow:hidden;
opacity:0;transform:translateY(8px);transition:opacity .24s,transform .24s}
.w.in{opacity:1;transform:none}
.w.bar{max-width:none;left:0;right:0;border-radius:0;border-left:0;border-right:0;text-align:center}
.w.modal{left:50%;top:50%;transform:translate(-50%,calc(-50% + 8px));max-width:420px}
.w.modal.in{transform:translate(-50%,-50%)}
.bd{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2147482999;opacity:0;transition:opacity .24s}
.bd.in{opacity:1}
.bl{bottom:20px;left:20px}.bc{bottom:20px;left:50%;transform:translate(-50%,8px)}.bc.in{transform:translateX(-50%)}
.br{bottom:20px;right:20px}.tl{top:20px;left:20px}.tc{top:20px;left:50%;transform:translate(-50%,8px)}
.tc.in{transform:translateX(-50%)}.tr{top:20px;right:20px}
.w.bar.tl,.w.bar.tc,.w.bar.tr{top:0;transform:translateY(-8px)}.w.bar.tl.in{transform:none}
.w.bar.bl,.w.bar.bc,.w.bar.br{bottom:0;left:0}
.r{display:flex;align-items:center;gap:12px;padding:12px 14px}
.av{width:38px;height:38px;border-radius:8px;flex:0 0 auto;background:var(--w-accent);
color:var(--w-on-accent);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:15px}
.tx{min-width:0;flex:1}
.t{font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.b{color:var(--w-muted);font-size:13px;margin-top:1px}
.ago{color:var(--w-muted);font-size:11px;margin-top:3px}
.x{background:none;border:0;color:var(--w-muted);cursor:pointer;font-size:17px;line-height:1;padding:2px 4px;flex:0 0 auto}
.x:hover{color:var(--w-fg)}
.cta{display:inline-block;margin-top:10px;background:var(--w-accent);color:var(--w-on-accent);
text-decoration:none;padding:8px 14px;border-radius:6px;font-size:13px;border:0;cursor:pointer}
.pad{padding:16px}
.f{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.f input,.f textarea{flex:1;min-width:0;font:inherit;padding:8px 10px;border:1px solid var(--w-border);
border-radius:6px;background:var(--w-bg);color:var(--w-fg)}
.br-{display:block;padding:5px 14px;font-size:10px;color:var(--w-muted);text-align:center;
border-top:1px solid var(--w-border);text-decoration:none}
.cd{font-variant-numeric:tabular-nums;font-weight:600;font-size:16px}
.code{font-family:ui-monospace,monospace;background:var(--w-border);padding:3px 8px;border-radius:5px}
.st{display:flex;gap:4px;font-size:18px;cursor:pointer}
@media (prefers-reduced-motion:reduce){.w,.bd{transition:none}}
@media (max-width:420px){.w{max-width:calc(100vw - 24px)}.bl,.br{left:12px;right:12px}}
`;

let sheet: CSSStyleSheet | null = null;
function styles(): CSSStyleSheet | null {
  if (sheet) return sheet;
  return safely(() => {
    const s = new CSSStyleSheet();
    s.replaceSync(CSS);
    sheet = s;
    return s;
  }, null);
}

const POS: Record<string, string> = {
  'bottom-left': 'bl', 'bottom-center': 'bc', 'bottom-right': 'br',
  'top-left': 'tl', 'top-center': 'tc', 'top-right': 'tr', center: 'bc', inline: '',
};

/* ---------------------------------------------------------------- renderers */

type Ctx = { payload: Payload; track: (w: Widget, kind: string) => void };

/** Eight families cover all 44 types — see `@mamal/widget-catalog`. */
function render(w: Widget, ctx: Ctx): { node: HTMLElement; backdrop?: HTMLElement } | null {
  const s = w.settings as Record<string, string & number>;
  const conv = (ctx.payload.conversions[0] ?? {}) as Record<string, unknown>;
  // Conversion fields, the widget's own count, then the nested `data` blob —
  // last wins, so a source that sent `{data:{name}}` beats a stray top-level
  // `name`. Typed loosely on purpose: the shape is whatever the source sent.
  const data: Record<string, unknown> = {
    ...conv,
    count: ctx.payload.counts[w.id] ?? 0,
    ...((conv.data as Record<string, unknown>) ?? {}),
  };
  const title = fill(String(s.title ?? ''), data);
  const body = fill(String(s.body ?? ''), data);

  const close = el('button', {
    class: 'x', 'aria-label': 'Close',
    onclick: () => { dismiss(w, ctx); },
  }, ['×']);

  const cta = (label: string, href: string) =>
    el('a', { class: 'cta', href, target: '_blank', rel: 'noopener noreferrer',
      onclick: () => ctx.track(w, 'click') }, [label]);

  let inner: HTMLElement;

  switch (w.family) {
    case 'bubble': {
      const initial = String((data.name as string) ?? '·').trim().charAt(0).toUpperCase() || '·';
      inner = el('div', { class: 'r' }, [
        ...(s.showAvatar !== false ? [el('div', { class: 'av' }, [initial])] : []),
        el('div', { class: 'tx' }, [
          el('div', { class: 't' }, [title]),
          ...(body ? [el('div', { class: 'b' }, [body])] : []),
          ...(s.showTimeAgo && conv.occurredAt
            ? [el('div', { class: 'ago' }, [timeAgo(conv.occurredAt as string)])]
            : []),
        ]),
        close,
      ]);
      break;
    }

    case 'bar':
      inner = el('div', { class: 'r', style: { justifyContent: 'center' } }, [
        el('div', { class: 'tx', style: { flex: 'unset' } }, [
          el('span', { class: 't', style: { whiteSpace: 'normal' } }, [title]),
          ...(s.code ? [el('span', { class: 'code', style: { marginLeft: '8px' } }, [String(s.code)])] : []),
        ]),
        ...(s.linkUrl ? [cta(String(s.linkLabel ?? 'Open'), String(s.linkUrl))] : []),
        close,
      ]);
      break;

    case 'card':
    case 'modal':
      inner = el('div', { class: 'pad' }, [
        el('div', { style: { display: 'flex', gap: '8px' } }, [
          el('div', { class: 't', style: { flex: '1', whiteSpace: 'normal' } }, [title]),
          close,
        ]),
        ...(body ? [el('div', { class: 'b', style: { marginTop: '6px' } }, [body])] : []),
        ...(s.code ? [el('div', { style: { marginTop: '10px' } }, [el('span', { class: 'code' }, [String(s.code)])])] : []),
        ...(s.linkUrl ? [cta(String(s.linkLabel ?? 'Learn more'), String(s.linkUrl))] : []),
      ]);
      break;

    case 'form': {
      const input = el('input', {
        type: w.type === 'phone_collector' ? 'tel' : 'email',
        placeholder: w.type === 'phone_collector' ? 'Phone number' : 'Email address',
        'aria-label': title || 'Your details', required: 'required',
      }) as HTMLInputElement;
      const ok = el('div', { class: 'b', style: { marginTop: '8px', display: 'none' } }, [
        String(s.successMessage ?? 'Thank you.'),
      ]);
      const form = el('form', {
        class: 'f',
        onsubmit: (e: Event) => {
          e.preventDefault();
          if (!input.value) return;
          ctx.track(w, 'submit');
          send(ctx.payload.ingest, {
            t: 'submit', w: w.id, v: input.value, p: location.pathname,
          });
          form.style.display = 'none';
          ok.style.display = 'block';
        },
      }, [input, el('button', { class: 'cta', type: 'submit', style: { marginTop: '0' } }, [
        String(s.submitLabel ?? 'Subscribe'),
      ])]);
      inner = el('div', { class: 'pad' }, [
        el('div', { style: { display: 'flex', gap: '8px' } }, [
          el('div', { class: 't', style: { flex: '1', whiteSpace: 'normal' } }, [title]),
          close,
        ]),
        ...(body ? [el('div', { class: 'b', style: { marginTop: '6px' } }, [body])] : []),
        form, ok,
      ]);
      break;
    }

    case 'rating': {
      const stars = el('div', { class: 'st', role: 'radiogroup', 'aria-label': title });
      for (let i = 1; i <= 5; i++) {
        stars.append(el('button', {
          class: 'x', role: 'radio', 'aria-checked': 'false', 'aria-label': `${i} of 5`,
          style: { fontSize: '18px' },
          onclick: () => {
            ctx.track(w, 'submit');
            send(ctx.payload.ingest, { t: 'rate', w: w.id, v: i, p: location.pathname });
            stars.replaceWith(el('div', { class: 'b' }, ['Thank you.']));
          },
        }, ['★']));
      }
      inner = el('div', { class: 'pad' }, [
        el('div', { style: { display: 'flex', gap: '8px' } }, [
          el('div', { class: 't', style: { flex: '1', whiteSpace: 'normal' } }, [title]),
          close,
        ]),
        stars,
      ]);
      break;
    }

    case 'chat':
      inner = el('div', { class: 'r' }, [
        el('div', { class: 'tx' }, [el('div', { class: 't' }, [title])]),
        cta('Open', chatHref(w)),
        close,
      ]);
      break;

    case 'inline': {
      const host = D.querySelector(String(s.selector ?? ''));
      if (!host) return null; // the customer's element is absent; show nothing
      const box = el('div', {}, [el('div', { class: 't' }, [title])]);
      host.append(box);
      return { node: box as HTMLElement };
    }

    default:
      return null;
  }

  const wrap = el('div', {
    class: `w ${w.family === 'bar' ? 'bar ' : ''}${w.family === 'modal' ? 'modal ' : ''}${POS[w.position] ?? 'bl'}`,
    role: w.family === 'modal' ? 'dialog' : 'status',
    'aria-live': w.family === 'modal' ? null : 'polite',
    'aria-modal': w.family === 'modal' ? 'true' : null,
  }, [inner]);

  if (w.showBranding) {
    wrap.append(el('a', {
      class: 'br-', href: 'https://mamal.app', target: '_blank', rel: 'noopener noreferrer',
    }, ['Powered by Mamal']));
  }

  const backdrop = w.family === 'modal'
    ? (el('div', { class: 'bd', onclick: () => dismiss(w, ctx) }) as HTMLElement)
    : undefined;

  return { node: wrap as HTMLElement, backdrop };
}

function chatHref(w: Widget): string {
  const s = w.settings as Record<string, string>;
  const msg = encodeURIComponent(s.prefill ?? '');
  if (w.type === 'whatsapp_chat') return `https://wa.me/${(s.phone ?? '').replace(/\D/g, '')}?text=${msg}`;
  if (w.type === 'telegram_chat') return `https://t.me/${s.username ?? ''}`;
  return `https://m.me/${s.pageId ?? ''}`;
}

/* ------------------------------------------------------------------ events */

let queue: unknown[] = [];
let flushing = 0;

/**
 * Batched, and flushed with `sendBeacon` so a click that navigates away still
 * reports. A per-event fetch would both slow the page and lose the last event.
 */
function send(url: string, event: unknown) {
  queue.push(event);
  if (flushing) return;
  flushing = W.setTimeout(() => flush(url), 1000);
}

function flush(url: string) {
  flushing = 0;
  if (!queue.length) return;
  const body = JSON.stringify({ e: queue });
  queue = [];
  safely(() => {
    if (navigator.sendBeacon) navigator.sendBeacon(url, body);
    else fetch(url, { method: 'POST', body, keepalive: true, mode: 'no-cors' });
  }, undefined);
}

/* ------------------------------------------------------------- frequency */

function allowed(w: Widget): boolean {
  const seen = store.read()[w.id];
  const inSession = safely(() => !!W.sessionStorage.getItem(`mamal.s.${w.id}`), false);
  return allowedToShow(w, seen, inSession);
}

function markSeen(w: Widget) {
  const all = store.read();
  const prev = all[w.id] ?? { n: 0, t: 0 };
  all[w.id] = { n: prev.n + 1, t: Date.now() };
  all.__seen = { n: 1, t: Date.now() };
  store.write(all);
  safely(() => W.sessionStorage.setItem(`mamal.s.${w.id}`, '1'), undefined);
}

function dismiss(w: Widget, ctx: Ctx) {
  ctx.track(w, 'close');
  const node = mounted.get(w.id);
  if (!node) return;
  node.host.remove();
  mounted.delete(w.id);
  releaseSlot(w);
}

const mounted = new Map<string, { host: HTMLElement }>();

/*
 * One widget per position at a time.
 *
 * Without this every widget defaults to bottom-left and they draw on top of
 * each other — a proof bubble under a signup form under a cookie bar. The
 * source products serialise per position and so does this: a widget whose slot
 * is busy waits and re-checks rather than being dropped, because a customer who
 * configured two bottom-left widgets expects to see both, just not at once.
 *
 * `inline` is exempt: it renders into an element the customer chose, so two of
 * them cannot collide unless the customer pointed them at the same node.
 */
const occupied = new Set<string>();
const WAIT_MS = 1500;
const MAX_WAITS = 40; // ~1 minute, then give up rather than spin forever

function claimSlot(w: Widget, run: () => void, attempt = 0): void {
  if (w.family === 'inline' || w.position === 'inline') return run();
  if (!occupied.has(w.position)) {
    occupied.add(w.position);
    return run();
  }
  if (attempt >= MAX_WAITS) return;
  W.setTimeout(() => claimSlot(w, run, attempt + 1), WAIT_MS);
}

function releaseSlot(w: Widget) {
  occupied.delete(w.position);
}

/* -------------------------------------------------------------------- boot */

function show(w: Widget, ctx: Ctx) {
  const built = render(w, ctx);
  if (!built) {
    // Nothing drawn — an inline target that is absent, or a type this build
    // does not know. Free the slot rather than blocking every later widget.
    releaseSlot(w);
    return;
  }

  const host = el('div') as HTMLElement;
  const root = host.attachShadow({ mode: 'open' });
  const s = styles();
  if (s) root.adoptedStyleSheets = [s];
  else root.append(el('style', {}, [CSS]));

  Object.entries(w.theme).forEach(([k, v]) => host.style.setProperty(k, v));
  if (built.backdrop) root.append(built.backdrop);
  root.append(built.node);
  D.body.append(host);
  mounted.set(w.id, { host });

  // Next frame, so the transition has a start state to animate from.
  requestAnimationFrame(() => {
    built.node.classList.add('in');
    built.backdrop?.classList.add('in');
  });

  ctx.track(w, 'impression');
  markSeen(w);

  if (w.durationSeconds > 0 && w.family !== 'modal') {
    W.setTimeout(() => {
      const m = mounted.get(w.id);
      if (!m) return;
      built.node.classList.remove('in');
      W.setTimeout(() => {
        m.host.remove();
        mounted.delete(w.id);
        releaseSlot(w);
      }, 260);
    }, w.durationSeconds * 1000);
  }
}

async function boot() {
  const script = D.currentScript as HTMLScriptElement | null;
  const key = script?.dataset.key ?? script?.src.match(/[?&]k=([^&]+)/)?.[1];
  if (!key) return;

  /*
   * `||`, not `??`.
   *
   * An inline `<script data-key="…">` has `src === ''` — an empty string, not
   * null — so `??` keeps it and `new URL('')` throws, which the boot wrapper
   * swallows into "no widgets, no error". The editor preview embeds the runtime
   * exactly that way.
   */
  const base = new URL(script?.src || location.href).origin;
  const res = await fetch(`${base}/c/${key}.json`, { credentials: 'omit' });
  if (!res.ok) return;
  const payload = (await res.json()) as Payload;

  const ctx: VisitorContext = buildContext();
  const track = (w: Widget, kind: string) =>
    send(payload.ingest, { t: kind, w: w.id, c: payload.campaignId, p: location.pathname });

  const runtime: Ctx = { payload, track };

  for (const w of payload.widgets) {
    if (!allowed(w)) continue;
    if (!matches(w.targeting, ctx)) continue;
    W.setTimeout(
      () => claimSlot(w, () => safely(() => show(w, runtime), undefined)),
      (w.delaySeconds ?? 0) * 1000,
    );
  }

  // A click that navigates away must still report its impression.
  addEventListener('pagehide', () => flush(payload.ingest));
}

safely(() => void boot().catch(() => {}), undefined);
