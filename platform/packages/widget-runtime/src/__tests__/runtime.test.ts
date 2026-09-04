import { describe, expect, it } from 'vitest';
import { execFileSync } from 'node:child_process';
import { readFileSync, existsSync } from 'node:fs';
import { gzipSync } from 'node:zlib';
import { allowedToShow, fill, timeAgo } from '../helpers.ts';

const BUDGET_BYTES = 12 * 1024;

describe('the size budget', () => {
  it(`bundles to under ${BUDGET_BYTES / 1024} KB gzipped`, () => {
    /*
     * The headline constraint of this whole package. It blocks nothing on the
     * host page, but it is downloaded by every visitor to every customer site,
     * and the free tier's cost model assumes it is cheap to serve.
     *
     * Asserted from a real build rather than a checked-in number, so it cannot
     * quietly drift.
     */
    execFileSync('node', ['scripts/build.mjs'], { cwd: new URL('../../', import.meta.url).pathname });
    const dist = new URL('../../dist/confirm.js', import.meta.url).pathname;
    expect(existsSync(dist)).toBe(true);
    const gz = gzipSync(readFileSync(dist), { level: 9 }).length;
    expect(gz, `${(gz / 1024).toFixed(2)} KB gzipped`).toBeLessThanOrEqual(BUDGET_BYTES);
  }, 60_000);

  it('never uses innerHTML', () => {
    // Conversion data comes from webhooks and forms — other people's input,
    // rendered on other people's sites. Everything goes through createElement
    // and text nodes; a single innerHTML here would be a stored-XSS vector on
    // every customer's page at once.
    const src = readFileSync(new URL('../runtime.ts', import.meta.url).pathname, 'utf8');
    expect(src).not.toMatch(/\.innerHTML/);
    expect(src).not.toMatch(/insertAdjacentHTML/);
    expect(src).not.toMatch(/document\.write/);
  });
});

describe('interpolation', () => {
  it('substitutes tokens from conversion data', () => {
    expect(fill('{{name}} in {{city}}', { name: 'Ana', city: 'Lisbon' })).toBe('Ana in Lisbon');
  });

  it('drops a missing token rather than leaving it visible', () => {
    // "{{city}}" rendered literally on a customer's site is worse than nothing.
    expect(fill('{{name}} in {{city}}', { name: 'Ana' })).toBe('Ana in ');
  });

  it('returns markup as inert text — the caller inserts it as a text node', () => {
    const out = fill('{{name}} joined', { name: '<img src=x onerror=alert(1)>' });
    expect(out).toContain('<img');
    // The guarantee is not escaping here; it is that nothing in the runtime
    // ever parses this as HTML. The innerHTML test above is what enforces it.
  });

  it('handles non-string values without throwing', () => {
    expect(fill('{{count}} people', { count: 42 })).toBe('42 people');
    expect(fill('{{x}}', { x: null })).toBe('');
    expect(fill('{{x}}', {})).toBe('');
  });
});

describe('timeAgo', () => {
  const now = Date.parse('2026-01-01T12:00:00Z');
  const ago = (ms: number) => new Date(now - ms).toISOString();

  it('reads naturally at each scale', () => {
    expect(timeAgo(ago(5_000), now)).toBe('5 seconds ago');
    expect(timeAgo(ago(60_000), now)).toBe('1 minute ago');
    expect(timeAgo(ago(7_200_000), now)).toBe('2 hours ago');
    expect(timeAgo(ago(172_800_000), now)).toBe('2 days ago');
  });

  it('says nothing for a future or unparseable timestamp', () => {
    // A clock-skewed "in -3 seconds" on a customer's site looks broken.
    expect(timeAgo(new Date(now + 60_000).toISOString(), now)).toBe('');
    expect(timeAgo('not a date', now)).toBe('');
    expect(timeAgo(undefined, now)).toBe('');
  });
});

describe('frequency capping', () => {
  const now = Date.parse('2026-01-01T12:00:00Z');

  it('always shows when the frequency is always', () => {
    expect(allowedToShow({ displayFrequency: 'always', displayLimit: 0 }, { n: 99, t: now }, true, now))
      .toBe(true);
  });

  it('once_per_session respects the session flag, not the counter', () => {
    const w = { displayFrequency: 'once_per_session', displayLimit: 0 };
    expect(allowedToShow(w, { n: 5, t: 0 }, false, now)).toBe(true);
    expect(allowedToShow(w, undefined, true, now)).toBe(false);
  });

  it('n_times counts impressions', () => {
    const w = { displayFrequency: 'n_times', displayLimit: 3 };
    expect(allowedToShow(w, { n: 2, t: 0 }, false, now)).toBe(true);
    expect(allowedToShow(w, { n: 3, t: 0 }, false, now)).toBe(false);
  });

  it('once_per_hours waits out the window', () => {
    const w = { displayFrequency: 'once_per_hours', displayLimit: 6 };
    expect(allowedToShow(w, { n: 1, t: now - 5 * 3_600_000 }, false, now)).toBe(false);
    expect(allowedToShow(w, { n: 1, t: now - 7 * 3_600_000 }, false, now)).toBe(true);
  });

  it('shows on an unknown frequency rather than hiding', () => {
    // Frequency is a display preference, not a permission. Silently hiding a
    // widget the customer configured is the worse of the two failures.
    expect(allowedToShow({ displayFrequency: 'sometimes', displayLimit: 0 }, { n: 9, t: 0 }, true, now))
      .toBe(true);
  });

  it('a first-time visitor is never capped', () => {
    for (const f of ['n_times', 'once_per_hours', 'once_per_session']) {
      expect(allowedToShow({ displayFrequency: f, displayLimit: 1 }, undefined, false, now), f).toBe(true);
    }
  });
});
