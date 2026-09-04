import { test, expect } from '@playwright/test';
import { newAccount, signUp } from './support.ts';

/**
 * The primary journey: a new user goes from an empty workspace to a scored
 * audit with actionable findings, without reading anything.
 *
 * This is the one that must never break. It crosses onboarding, the resource
 * registry, the entitlement resolver, the crawl queue, the rule engine and the
 * findings UI — so a failure anywhere in the spine surfaces here first.
 */
test.describe('a new user gets their first audit', () => {
  // A brand new user, not the shared fixture: onboarding is what this journey
  // is about, and the shared account has already been through it.
  test.use({ storageState: { cookies: [], origins: [] } });

  test('onboard → add a site → run an audit → read the findings → resolve one', async ({ page }) => {
    await signUp(page, newAccount('primary'));

    // --- the empty state offers exactly one thing to do -------------------
    await page.goto('/');
    await expect(page.getByRole('heading', { name: /add your first website/i })).toBeVisible();
    await page.getByRole('link', { name: /get started/i }).click();
    await expect(page).toHaveURL(/\/welcome/);

    // --- one question, then one input ------------------------------------
    // The interests answer only orders the sidebar, so any pick is valid; the
    // journey must not depend on which.
    await page.getByRole('button', { name: /^Fix what is hurting/ }).click();

    // Exact name, not a loose regex. `/go/` also matches "Know the moment my
    // site goes down", which silently toggles an interest instead of
    // submitting — a green-looking test that never reached the next screen.
    await page.getByRole('textbox', { name: /website address/i }).fill('https://www.example.com');
    await page.getByRole('button', { name: 'Continue', exact: true }).click();

    // --- the site is now addressable by every tool ------------------------
    // addFirstSite redirects to the dashboard once the site, its URN and the
    // default project exist.
    await page.waitForURL((url) => url.pathname === '/', { timeout: 30_000 });
    await page.goto('/audit');
    await expect(page.getByText('example.com').first()).toBeVisible({ timeout: 20_000 });

    // The limit is stated before it is reached — the G2 rule, asserted.
    await expect(page.getByText(/\d+ of \d+ website/i)).toBeVisible();

    // --- the first audit starts by itself ---------------------------------
    // Onboarding registers the site with Audit and queues the crawl, so there
    // is nothing to click here. The crawl runs in slices on a queue, so waiting
    // for a grade is waiting for the whole pipeline: claim → crawl → evaluate
    // → finalize.
    await expect(page.getByText(/grade [a-f]/i).first()).toBeVisible({ timeout: 60_000 });

    // --- the findings carry evidence and a fix ----------------------------
    await page.goto('/audit/issues');
    const firstGroup = page.locator('main button').first();
    await expect(firstGroup).toBeVisible({ timeout: 20_000 });
    await firstGroup.click();

    await expect(page.getByRole('button', { name: 'Mark fixed' }).first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Ignore' }).first()).toBeVisible();

    // --- resolving is undoable, not confirmed -----------------------------
    await page.getByRole('button', { name: 'Mark fixed' }).first().click();
    const toast = page.getByRole('status');
    await expect(toast).toContainText(/marked fixed/i);
    await expect(toast.getByRole('button', { name: 'Undo' })).toBeVisible();
  });
});
