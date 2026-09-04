'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import { widgetDef, themeVars } from '@mamal/widget-catalog';

const FRAMES = {
  desktop: { w: 1280, h: 800, label: 'Desktop' },
  tablet: { w: 834, h: 1112, label: 'Tablet' },
  mobile: { w: 390, h: 844, label: 'Phone' },
} as const;
export type FrameKey = keyof typeof FRAMES;

/**
 * The live preview.
 *
 * An iframe running the **actual** `confirm.js` against a preview payload —
 * not a React re-implementation of the widget. "The editor preview matches
 * production pixel-for-pixel" is only true if it is literally the same code
 * doing the drawing, and a lookalike would drift on the first CSS change
 * nobody remembered to mirror.
 *
 * The frame is scaled to fit rather than resized, so a phone preview shows a
 * real 390px viewport — a widget's mobile rules key off viewport width, and a
 * squashed desktop frame would report the wrong one.
 */
export function Preview({
  type, settings, targeting, theme, position, showBranding, host, frame,
}: {
  type: string;
  settings: Record<string, unknown>;
  targeting: unknown;
  theme: string;
  position: string;
  showBranding: boolean;
  host: string;
  frame: FrameKey;
}) {
  const ref = useRef<HTMLIFrameElement>(null);
  const [scale, setScale] = useState(1);
  const wrapRef = useRef<HTMLDivElement>(null);
  const size = FRAMES[frame];

  // Fit the frame to whatever width the middle pane has.
  useEffect(() => {
    const el = wrapRef.current;
    if (!el) return;
    const fit = () => {
      const available = el.clientWidth;
      setScale(Math.min(1, available / size.w));
    };
    fit();
    const ro = new ResizeObserver(fit);
    ro.observe(el);
    return () => ro.disconnect();
  }, [size.w]);

  /*
   * Debounced, so a keystroke does not reload a document.
   *
   * `srcdoc`, not `document.write`: the frame is sandboxed *without*
   * `allow-same-origin`, which gives it an opaque origin — a custom-HTML widget
   * cannot reach the app, read a cookie or call an authenticated route. That
   * isolation also blocks writing into it from here, so the content is handed
   * over declaratively instead.
   */
  const [debounced, setDebounced] = useState(0);
  useEffect(() => {
    const t = setTimeout(() => setDebounced((n) => n + 1), 220);
    return () => clearTimeout(t);
  }, [type, settings, targeting, theme, position, showBranding, host]);

  /*
   * Built after mount, never during SSR.
   *
   * The payload carries `occurredAt` timestamps derived from the current time,
   * so a server render and the first client render produce different documents
   * and React reports a hydration mismatch it will not patch. The preview is a
   * browser artefact anyway — rendering it on the server would ship a second
   * copy of the payload in the HTML for no benefit.
   */
  const [mounted, setMounted] = useState(false);
  useEffect(() => setMounted(true), []);

  /*
   * The runtime source, fetched once and inlined into the frame.
   *
   * A sandboxed `srcdoc` document has an opaque origin, and an external
   * `<script src>` fails to load in it — the tag fires `error` with no console
   * message, which is how this cost an hour. Inlining also removes a request,
   * and removes any chance of the preview running a stale cached build while
   * the editor shows new settings.
   *
   * Same-origin here, so it is one cached fetch for the whole session.
   */
  const [runtime, setRuntime] = useState<string | null>(null);
  useEffect(() => {
    let live = true;
    fetch('/confirm.js')
      .then((r) => (r.ok ? r.text() : null))
      .then((t) => { if (live) setRuntime(t); })
      .catch(() => { if (live) setRuntime(null); });
    return () => { live = false; };
  }, []);

  const srcDoc = useMemo(
    () =>
      mounted && runtime
        ? page({ type, settings, theme, position, showBranding, host, runtime })
        : undefined,
    // Deliberately partial deps: `debounced` is the trigger, and the settings
    // are read fresh when it fires. Listing them here would rebuild the frame
    // on every keystroke, which is what the debounce exists to prevent.
    [mounted, runtime, debounced, frame],
  );

  return (
    <div ref={wrapRef} className="min-w-0">
      <div
        className="relative mx-auto overflow-hidden rounded-[4px] border border-[var(--border-hairline)] bg-white"
        style={{
          width: size.w * scale,
          height: size.h * scale,
        }}
      >
        <iframe
          ref={ref}
          title={`Preview on ${size.label}`}
          srcDoc={srcDoc}
          /*
           * No `allow-same-origin`: a Custom HTML widget is markup the customer
           * typed, and it renders here. An opaque origin means it cannot reach
           * the app, read a cookie, or call an authenticated route.
           */
          sandbox="allow-scripts"
          className="absolute left-0 top-0 origin-top-left border-0"
          style={{ width: size.w, height: size.h, transform: `scale(${scale})` }}
        />
      </div>
      <p className="mt-2 text-center text-[11px] tabular-nums text-[var(--text-faint)]">
        {size.label} · {size.w}×{size.h}
        {scale < 1 ? ` · shown at ${Math.round(scale * 100)}%` : ''}
      </p>
    </div>
  );
}

/**
 * The page the iframe runs.
 *
 * Deliberately plain: it stands in for a customer's site, and anything
 * decorative here would make the preview flatter the widget.
 *
 * The payload is **built here and inlined**, not fetched. The editor already
 * holds every value, so a round trip would add latency to each keystroke and
 * force CORS on a sandboxed opaque origin for no benefit. `fetch` is patched so
 * the runtime takes its ordinary code path against it.
 */
function page(input: {
  type: string; settings: Record<string, unknown>;
  theme: string; position: string; showBranding: boolean; host: string; runtime: string;
}): string {
  const def = widgetDef(input.type);
  if (!def) return '<!doctype html><body></body>';

  // Parsed through the type's own schema, so the preview shows exactly what a
  // save would store — defaults filled in for anything left blank.
  const parsed = def.settings.safeParse(input.settings);
  const settings = (parsed.success ? parsed.data : def.settings.parse(def.defaults)) as Record<
    string,
    unknown
  >;

  const now = Date.now();
  const sample = [
    { name: 'Ana', city: 'Lisbon', country: 'PT', type: 'bought', ago: 3 },
    { name: 'Marek', city: 'Warsaw', country: 'PL', type: 'bought', ago: 24 },
    { name: 'Yuki', city: 'Osaka', country: 'JP', type: 'signed up', ago: 96 },
  ].map((c) => ({
    name: c.name, city: c.city, country: c.country, type: c.type,
    occurredAt: new Date(now - c.ago * 60_000).toISOString(),
  }));

  const id = '00000000-0000-4000-8000-000000000000';
  const payload = {
    campaignId: 'preview',
    widgets: [
      {
        id,
        type: input.type,
        family: def.family,
        theme: themeVars(input.theme, (settings as { accentColor?: string }).accentColor),
        position: input.position,
        settings,
        // The preview always shows the widget; targeting is answered by the
        // "who sees it" panel rather than by hiding the thing being edited.
        targeting: {},
        delaySeconds: 0,
        // Never auto-dismiss: a preview that vanishes mid-edit is useless.
        durationSeconds: 0,
        displayFrequency: 'always',
        displayLimit: 0,
        showBranding: input.showBranding,
      },
    ],
    conversions: def.needs.includes('conversions') ? sample : [],
    counts: { [id]: 38 },
    ingest: '',
  };

  return `<!doctype html><html><head><meta charset="utf-8">
<style>
  body{font:16px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
       margin:0;padding:40px;color:#1f2937;background:#fff}
  h1{font-weight:400;font-size:26px;margin:0 0 8px}
  p{color:#6b7280;max-width:46ch}
  .b{height:180px;border:1px dashed #e5e7eb;border-radius:6px;margin-top:24px}
  #mamal-proof,#mamal-count,#mamal-reviews,#mamal-rating{
    border:1px dashed #e5e7eb;border-radius:6px;padding:12px;margin-top:16px;
    color:#9ca3af;font-size:13px}
</style></head><body>
<h1>${escapeHtml(input.host)}</h1>
<p>A stand-in for your page, so the notification is judged against ordinary content
rather than an empty canvas.</p>
<div class="b"></div>
<div id="mamal-proof">inline target</div>
<div id="mamal-count">inline target</div>
<div id="mamal-reviews">inline target</div>
<div id="mamal-rating">inline target</div>
<script>
(function(){
  var PAYLOAD = ${JSON.stringify(payload)};
  // The runtime's only request, answered locally.
  window.fetch = function(){
    return Promise.resolve({ ok: true, json: function(){ return Promise.resolve(PAYLOAD); } });
  };
  // A preview must never touch a real widget's counters.
  navigator.sendBeacon = function(){ return true; };
  // Frequency capping would hide the widget on the second keystroke.
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
})();
</script>
<script data-key="preview">${input.runtime.replace(/<\/script/gi, '<\\/script')}</script>
</body></html>`;
}

function escapeHtml(s: string): string {
  return s.replace(/[&<>"']/g, (c) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]!);
}

export { FRAMES };
