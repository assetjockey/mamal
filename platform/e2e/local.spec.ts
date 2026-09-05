import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * Local.
 *
 * The grid geometry and the NAP comparison are covered exhaustively in
 * `tools/market/src/__tests__/local.test.ts`. What needs a browser is that the
 * screen states the cost before a grid is run, and explains itself when there
 * is nothing connected — a blank local screen is indistinguishable from a
 * broken one.
 */
test.describe('local', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('explains itself with nothing connected', async ({ page }) => {
    await page.goto('/market/local');

    const empty = page.getByRole('heading', { name: /no business profile connected/i });
    if (await empty.isVisible().catch(() => false)) {
      // Says which three things the screen would show, so somebody can decide
      // whether connecting is worth it.
      await expect(page.getByText(/rank grid/i).first()).toBeVisible();
      return;
    }

    // With a profile, the grid's price is on the button before it is pressed —
    // every point is a paid lookup.
    await expect(page.getByRole('button', { name: /run grid · \d+ credits/i })).toBeVisible();
  });
});
