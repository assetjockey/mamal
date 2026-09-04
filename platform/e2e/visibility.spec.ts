import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * AI visibility, end to end.
 *
 * What this covers is the setup journey and the guard rails around it — the
 * probe itself is exercised in `tools/market`'s integration tests, where a
 * driver can be injected and four models' answers can be varied deliberately.
 * Reaching a real provider from an e2e run would make the suite depend on an
 * API key, a network, and somebody else's rate limit.
 *
 * What is worth asserting here is everything a customer can get wrong before
 * spending anything: that share of voice refuses to be computed without a
 * brand of your own, and that the price is on the button rather than on the
 * invoice.
 */
test.describe('AI visibility', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('the price is on the button before the click', async ({ page }) => {
    await page.goto('/market/visibility');

    // Nothing measured, nothing configured: the empty state has to say what to
    // do first, and "add a prompt" is the wrong first step without a brand.
    await expect(page.getByText(/which brand is yours/i)).toBeVisible();

    await page.getByLabel(/^brand$/i).fill('Acme');
    await page.getByLabel(/domain/i).fill('acme.example');
    await page.getByRole('button', { name: 'Add', exact: true }).first().click();

    // First brand added is automatically the one being measured — a set with no
    // self brand has no numerator, and making somebody do it in two steps just
    // means some people stop after the first.
    await expect(page.getByText('Yours', { exact: true })).toBeVisible({ timeout: 15_000 });

    await page.getByLabel(/^prompt$/i).fill('What is the best widget for a small team?');
    await page.getByRole('button', { name: 'Add', exact: true }).last().click();
    await expect(
      page.getByRole('cell', { name: /best widget for a small team/i }),
    ).toBeVisible({ timeout: 15_000 });

    /*
     * Ten credits an assistant, and the button says so. A probe is the most
     * expensive single click in the platform; discovering the cost from the
     * ledger afterwards is the complaint this exists to prevent.
     */
    const run = page.getByRole('button', { name: /run probes/i });
    await expect(run).toBeVisible();
    await expect(run).toContainText(/credits/i);
  });

  test('a prompt that names nothing is refused before it costs anything', async ({ page }) => {
    await page.goto('/market/visibility');

    await page.getByLabel(/^prompt$/i).fill('widgets');
    await page.getByRole('button', { name: 'Add', exact: true }).last().click();

    // Too short to be a question a buyer would type — and a probe of it would
    // have been billed all the same.
    await expect(page.getByText(/a question somebody would really ask/i)).toBeVisible({
      timeout: 15_000,
    });
  });

  test('the brand being measured cannot be removed out from under the numbers', async ({ page }) => {
    await page.goto('/market/visibility');

    // The self brand offers no Remove at all; only competitors do.
    const selfRow = page.locator('div', { has: page.getByText('Yours', { exact: true }) }).last();
    await expect(selfRow.getByRole('button', { name: /remove/i })).toHaveCount(0);
  });

  test('an assistant with no model configured says so rather than reading as zero', async ({
    page,
  }) => {
    await page.goto('/market/visibility');

    /*
     * No Perplexity provider is seeded, so it is never asked. A blank column
     * would be read as "Perplexity never mentions us" — a much stronger and
     * quite different claim than "we did not ask it".
     */
    await expect(page.getByText(/not asked:/i)).toBeVisible();
    await expect(page.getByText(/perplexity/i).first()).toBeVisible();
  });
});
