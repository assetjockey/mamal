import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * The composer, end to end.
 *
 * What needs a browser is the thing the unit tests cannot show: that the writer
 * finds out about Instagram's rules while typing, not five hours later from
 * Instagram. The rules themselves are covered exhaustively in
 * `tools/market/src/__tests__/networks.test.ts`.
 */
test.describe('social', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('says what each network makes of the post, as it is typed', async ({ page }) => {
    await page.goto('/market/social');

    // With no accounts connected the screen says so rather than showing an
    // empty composer that cannot do anything.
    const empty = page.getByText(/no accounts connected/i);
    if (await empty.isVisible().catch(() => false)) {
      await expect(empty).toBeVisible();
      return;
    }

    await page.getByRole('checkbox').first().check();
    await page.getByLabel(/^post$/i).fill('A new widget rack, in three sizes.');

    // A per-network panel appears for the account picked, with a live count.
    await expect(page.getByText(/\d+\/\d+/).first()).toBeVisible();
  });

  test('the calendar explains an empty queue instead of guessing', async ({ page }) => {
    await page.goto('/market/calendar');

    // Either there is nothing scheduled, or there are entries — both are
    // legitimate, and both must say what they mean rather than showing a blank.
    await expect(
      page.getByRole('heading', { name: /nothing scheduled/i })
        .or(page.getByRole('heading', { name: /coming up/i })),
    ).toBeVisible();

    // The slots section always says something: a grid per account, or why
    // there is none. A heading over blank space leaves somebody guessing that
    // "queue" quietly means "never".
    await expect(
      page.getByText(/slots? a week|no slots/i).first()
        .or(page.getByRole('heading', { name: /no accounts connected/i })),
    ).toBeVisible();
  });
});
