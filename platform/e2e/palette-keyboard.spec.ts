import { test, expect } from '@playwright/test';

/**
 * Secondary journey: reaching anything without a mouse.
 *
 * The G2 rule is that ⌘K reaches everything and the app is navigable by
 * keyboard alone. That is only true if it is exercised as a keyboard user
 * would — pressing keys, not calling click() on elements a keyboard user could
 * never focus.
 */
test.describe('keyboard-only navigation', () => {
  test('skip link, ⌘K, resource search and g-jump all work from the keyboard', async ({ page }) => {
    await page.goto('/audit');

    // --- the first tab stop must escape the two nav tiers -----------------
    await page.keyboard.press('Tab');
    const skip = page.locator(':focus');
    await expect(skip).toHaveText(/skip to content/i);
    await skip.press('Enter');
    await expect(page.locator('main')).toBeFocused();

    // --- ⌘K opens and focuses the input -----------------------------------
    await page.keyboard.press('ControlOrMeta+k');
    const palette = page.getByRole('dialog', { name: 'Command palette' });
    await expect(palette).toBeVisible();
    await expect(palette.getByRole('combobox')).toBeFocused();

    // --- it reaches resources, not just static pages ----------------------
    // This is the cross-tool search: the row comes from the URN registry, and
    // the route it opens comes from Audit's manifest.
    await palette.getByRole('combobox').fill('example');
    const hit = palette.getByRole('option').filter({ hasText: 'example.com' });
    await expect(hit).toBeVisible({ timeout: 10_000 });
    await page.keyboard.press('Enter');
    await expect(page).toHaveURL(/\/audit\/sites\/[0-9a-f-]{36}/);

    // --- Escape closes, even immediately after opening --------------------
    // No wait for focus here on purpose: pressing Escape the instant the
    // palette appears is what a fast typist does, and it must still close.
    await page.keyboard.press('ControlOrMeta+k');
    await expect(palette).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(palette).toBeHidden();

    // --- `g` is a prefix, not a key ---------------------------------------
    await page.keyboard.press('g');
    await page.keyboard.press('t');
    await expect(page).toHaveURL(/\/track$/);

    // --- `?` documents every binding --------------------------------------
    await page.keyboard.press('?');
    const sheet = page.getByRole('dialog', { name: 'Keyboard shortcuts' });
    await expect(sheet).toBeVisible();
    await expect(sheet).toContainText('Open the command palette');
    await page.keyboard.press('Escape');
    await expect(sheet).toBeHidden();
  });

  test('a bare key never fires while typing', async ({ page }) => {
    // The trap this guards: `g`, `/` and `?` are global, so typing "grape" in
    // a form field must not arm a jump or open the palette.
    await page.goto('/audit');
    await page.keyboard.press('ControlOrMeta+k');
    const palette = page.getByRole('dialog', { name: 'Command palette' });
    await palette.getByRole('combobox').type('go / ? issues');
    await expect(palette.getByRole('combobox')).toHaveValue('go / ? issues');
    await expect(page.getByRole('dialog', { name: 'Keyboard shortcuts' })).toBeHidden();
    await expect(page).toHaveURL(/\/audit$/);
  });
});
