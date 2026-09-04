/**
 * Pure helpers, split out so they can be tested directly.
 *
 * Separate module rather than exports on the entry point: esbuild preserves an
 * entry's exports, so testing via the bundle would ship the test surface to
 * every visitor. Imported here, tree-shaking keeps only what the runtime uses.
 */

/**
 * `{{token}}` interpolation from conversion data.
 *
 * The values are supplied by whoever sent the conversion — a webhook, a form,
 * another tool — so they are *never* trusted. This returns a string that the
 * caller inserts as a text node; nothing here builds markup, and the runtime
 * has no `innerHTML` anywhere. A missing token becomes empty rather than
 * leaving `{{name}}` visible on a customer's site.
 */
export function fill(tpl: string, data: Record<string, unknown>): string {
  return String(tpl).replace(/\{\{(\w+)\}\}/g, (_, k: string) => {
    const v = data[k];
    return v === undefined || v === null ? '' : String(v);
  });
}

/** "3 minutes ago". Empty for anything unparseable or in the future. */
export function timeAgo(iso: string | undefined, now = Date.now()): string {
  if (!iso) return '';
  const secs = (now - Date.parse(iso)) / 1000;
  if (!Number.isFinite(secs) || secs < 0) return '';
  const [n, unit]: [number, string] =
    secs < 60 ? [secs, 'second']
    : secs < 3600 ? [secs / 60, 'minute']
    : secs < 86400 ? [secs / 3600, 'hour']
    : [secs / 86400, 'day'];
  const r = Math.floor(n);
  return `${r} ${unit}${r === 1 ? '' : 's'} ago`;
}

export type Frequency = {
  displayFrequency: string;
  displayLimit: number;
};

export type SeenRecord = { n: number; t: number } | undefined;

/**
 * Frequency capping.
 *
 * Pure, so the rule can be tested without a browser: the storage read and the
 * session flag are passed in rather than reached for.
 */
export function allowedToShow(
  w: Frequency,
  seen: SeenRecord,
  seenThisSession: boolean,
  now = Date.now(),
): boolean {
  if (w.displayFrequency === 'always') return true;
  if (w.displayFrequency === 'once_per_session') return !seenThisSession;
  if (!seen) return true;
  if (w.displayFrequency === 'n_times') return seen.n < (w.displayLimit || 1);
  if (w.displayFrequency === 'once_per_hours') {
    return now - seen.t > (w.displayLimit || 24) * 3_600_000;
  }
  // An unknown frequency shows the widget: it is a display preference, not a
  // permission, and silently hiding a paid-for widget is the worse failure.
  return true;
}
