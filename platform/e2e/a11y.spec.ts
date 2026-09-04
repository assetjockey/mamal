import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

/**
 * The G1 accessibility gate, actually run.
 *
 * Until this file existed the gate was asserted by hand — colour contrast was
 * measured from rendered elements, keyboard walkthroughs were driven manually —
 * which caught real problems but only the ones somebody thought to look for.
 * axe checks the whole tree, every time.
 *
 * Both themes, because the token set changes wholesale between them and a
 * contrast failure in dark mode is invisible to anyone testing in light.
 * WCAG 2 AA only: the AAA rules are a different product decision, and a gate
 * that fails on rules nobody intends to meet is a gate people learn to ignore.
 */

const ROUTES = [
  '/', '/link', '/link/qr', '/link/bio', '/link/barcodes', '/link/transfers',
  '/link/utm', '/link/folders', '/link/splash', '/link/domains',
  '/audit', '/confirm', '/settings',
  '/market', '/market/opportunities', '/market/visibility', '/market/connections',
];

const TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];

for (const theme of ['light', 'dark'] as const) {
  test.describe(`axe — ${theme}`, () => {
    test.beforeEach(async ({ page }) => {
      // The shell stores the choice per user and reads it on mount, so setting
      // it before the first navigation is what a returning user experiences.
      await page.addInitScript((t) => {
        try {
          localStorage.setItem('mamal.theme', t);
        } catch {
          /* a private window still has to render */
        }
      }, theme);
    });

    for (const route of ROUTES) {
      test(`${route}`, async ({ page }) => {
        await page.goto(route);
        await page.waitForLoadState('domcontentloaded');
        // The theme is applied on mount; measuring before it lands would test
        // the wrong palette.
        await page.waitForTimeout(300);

        const results = await new AxeBuilder({ page }).withTags(TAGS).analyze();

        const detail = results.violations
          .map((v) => `${v.id} (${v.impact}) ×${v.nodes.length}: ${v.help}\n    ${v.nodes[0]?.target.join(' ')}`)
          .join('\n  ');
        expect(results.violations, `${route} [${theme}]\n  ${detail}`).toEqual([]);
      });
    }
  });
}
