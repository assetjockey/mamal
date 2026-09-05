import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * The ad studios and the spend report.
 *
 * What needs a browser: that the report is complete without AI, and that the
 * studio shows what each platform will take *before* somebody pays for a
 * generation. The catalogue and the arithmetic themselves are covered in
 * `tools/market/src/__tests__/ads.test.ts`.
 */
test.describe('ads', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('the report says what it cannot compute rather than printing a zero', async ({ page }) => {
    await page.goto('/market/ads');

    // No accounts connected is the honest state, and it explains that the
    // screen costs nothing to use.
    const empty = page.getByRole('heading', { name: /no ad accounts connected/i });
    if (await empty.isVisible().catch(() => false)) {
      await expect(page.getByText(/works with AI switched off/i)).toBeVisible();
      return;
    }

    // With accounts, the comparison note has to be on the page: a reader
    // comparing periods needs to know the recent days are excluded.
    await expect(page.getByText(/conversions attribute late/i)).toBeVisible();
  });

  test('the studio shows a platform’s limits before anything is generated', async ({ page }) => {
    await page.goto('/market/studio');

    // Google Search is the default: three to fifteen headlines, thirty
    // characters each. Knowing that first is what stops a wasted generation.
    await expect(page.getByText(/3–15, 30 characters each/i)).toBeVisible();

    await page.getByRole('combobox', { name: 'Platform' }).selectOption('tiktok');
    await expect(page.getByText(/100 characters each/i)).toBeVisible();
  });

  test('creative sizes come from the platform, so an impossible shape cannot be picked', async ({
    page,
  }) => {
    await page.goto('/market/studio');
    await page.getByRole('button', { name: /^creative$/i }).click();

    /*
     * By role, not by label: these selects are *wrapped* by their label, so the
     * label's text content includes every option — `getByLabel(/^size$/)` finds
     * nothing. The accessible name is still "Size", which is what
     * `getByRole` uses and what a screen reader announces.
     */
    await page.getByRole('combobox', { name: 'Platform' }).selectOption('tiktok');
    const sizes = page.getByRole('combobox', { name: 'Size' });
    // A 728×90 leaderboard on TikTok would generate, cost money and be
    // unusable — so it is not offered.
    await expect(sizes).toContainText(/1080×1920/);
    await expect(sizes).not.toContainText(/728×90/);
  });

  test('the brand kit explains why it is snapshotted', async ({ page }) => {
    await page.goto('/market/studio');
    await page.getByRole('button', { name: /brand kit/i }).click();
    await expect(page.getByText(/not quietly a rebrand/i)).toBeVisible();
  });
});
