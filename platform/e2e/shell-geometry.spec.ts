import { test, expect } from '@playwright/test';

/**
 * The shell's geometry, at the four widths G1 names.
 *
 * This exists because of a bug that every visual sweep missed: the rail and
 * context nav take their widths from the 8px `--spacing` scale (`w-16`, `w-56`
 * → 128px and 448px) while the offset that clears them was written as an
 * absolute `pl-[18rem]` → 288px. So 288px of every page sat *underneath* the
 * navigation at lg and up. Nothing looked broken — the content was simply
 * unreachable, and only a click that landed on the sidebar instead of a table
 * row exposed it.
 *
 * Screenshots cannot catch that. `elementFromPoint` can, so this asserts the
 * thing that actually matters: at every width, what the page draws is what a
 * pointer reaches.
 */

const WIDTHS = [
  { name: '360 (phone)', width: 360, height: 780 },
  { name: '768 (tablet)', width: 768, height: 1024 },
  { name: '1280 (laptop)', width: 1280, height: 800 },
  { name: '1920 (desktop)', width: 1920, height: 1080 },
];

/** One per tool, plus a table-heavy page and a three-pane editor. */
const ROUTES = [
  '/', '/link', '/link/qr', '/link/bio', '/audit', '/confirm', '/settings',
  '/market/opportunities', '/market/visibility', '/market/content', '/market/trends',
  '/market/social', '/market/calendar', '/market/ads', '/market/studio',
  '/market/local',
];

test.describe('shell geometry', () => {
  for (const route of ROUTES) {
    test(`${route} is reachable at every width`, async ({ page }) => {
      await page.goto(route);
      await page.waitForLoadState('domcontentloaded');

      /*
       * Collapse the setup checklist first.
       *
       * It docks bottom-right by design, and a docked panel covers what is
       * behind it — that is what docking *is*. The question worth asserting is
       * whether anything the user cannot move covers a control, so the one
       * thing they can move is moved before measuring. It is collapsible and
       * dismissible; that it stays out of the way when collapsed is the
       * property this relies on.
       */
      const checklistToggle = page.locator('aside[data-dock="checklist"] button[aria-expanded="true"]');
      if (await checklistToggle.isVisible().catch(() => false)) {
        await checklistToggle.click();
        await page.waitForTimeout(200);
      }

      for (const size of WIDTHS) {
        await page.setViewportSize({ width: size.width, height: size.height });
        // One frame for the media queries, one for layout to settle.
        await page.waitForTimeout(250);

        const geometry = await page.evaluate(() => {
          const rect = (el: Element | null) => {
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { x: Math.round(r.x), width: Math.round(r.width), right: Math.round(r.right) };
          };

          const heading = document.querySelector('main h1, main h2');
          let headingCovered: boolean | null = null;
          if (heading) {
            const r = heading.getBoundingClientRect();
            // Four pixels in from the leading edge: the first place an
            // overlapping fixed panel would win.
            const top = document.elementFromPoint(r.x + 4, r.y + r.height / 2);
            headingCovered = !(top && (heading.contains(top) || top === heading));
          }

          /*
           * Every control must actually receive a pointer.
           *
           * A covered element still renders, still passes a screenshot diff and
           * still reads correctly to a screen reader — it simply cannot be
           * clicked. Two separate overlaps shipped past visual review and were
           * caught only by asking `elementFromPoint` who wins.
           *
           * Each control is scrolled to the middle of the viewport first,
           * because that is what a click does: something sitting under a
           * bottom-docked panel at rest is reachable, and reporting it would be
           * a false alarm. What this catches is the element that stays covered
           * *wherever* the page is scrolled — which is the actual defect.
           */
          const unreachable: string[] = [];
          const controls = document.querySelectorAll('main button, main a[href], main select');
          const scrollBack = window.scrollY;
          for (const el of Array.from(controls)) {
            if (el.getBoundingClientRect().width === 0) continue;
            el.scrollIntoView({ block: 'center', inline: 'nearest' });
            const r = el.getBoundingClientRect();
            if (r.width === 0 || r.height === 0) continue;
            const cx = Math.min(Math.max(r.x + r.width / 2, 1), window.innerWidth - 1);
            const cy = Math.min(Math.max(r.y + r.height / 2, 1), window.innerHeight - 1);
            const top = document.elementFromPoint(cx, cy);
            if (!top || !(el.contains(top) || top === el || top.contains(el))) {
              unreachable.push(`${el.tagName.toLowerCase()} "${(el.textContent ?? '').trim().slice(0, 28)}"`);
            }
          }
          window.scrollTo(0, scrollBack);

          return {
            rail: rect(document.querySelector('nav[aria-label="Tools"]')),
            context: rect(document.querySelector('aside[aria-label$="navigation"]')),
            headingCovered,
            unreachable: unreachable.slice(0, 5),
            scrollsSideways:
              document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
          };
        });

        expect(geometry.headingCovered, `${route} at ${size.name}: heading is under the shell`)
          .not.toBe(true);
        expect(geometry.scrollsSideways, `${route} at ${size.name}: the page scrolls sideways`)
          .toBe(false);
        expect(geometry.unreachable, `${route} at ${size.name}: controls covered by something else`)
          .toEqual([]);

        // Below `md` both tiers collapse into the bottom/top bars; above it the
        // rail is exactly the 64px the design specifies, and the context nav
        // the 216px, sitting flush against it.
        if (size.width >= 1024) {
          expect(geometry.rail?.width, `${route} at ${size.name}: rail width`).toBe(64);
          if (geometry.context && geometry.context.width > 0) {
            expect(geometry.context.x, `${route} at ${size.name}: context nav is not flush`).toBe(64);
            expect(geometry.context.width, `${route} at ${size.name}: context width`).toBe(216);
          }
        }
      }
    });
  }
});
