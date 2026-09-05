import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * The content editor, end to end.
 *
 * The journey worth proving is the one somebody actually does: create a
 * document, watch the score respond as they type, act on a check, and see it
 * turn green. The scoring itself is covered exhaustively by unit tests — what
 * needs a browser is that the live number and the stored number agree, because
 * they are computed in two different places.
 */
test.describe('content', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('the score responds while you type and survives a save', async ({ page }) => {
    await page.goto('/market/content');

    const title = `Choosing the best widget rack ${Date.now().toString(36)}`;
    await page.getByLabel(/new document/i).fill(title);
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    await expect(page).toHaveURL(/\/market\/content\/[0-9a-f-]{36}/, { timeout: 15_000 });

    const score = page.getByTestId('seo-score');
    const scoreBefore = Number(await score.innerText());

    await page.getByLabel(/target keyword/i).fill('widget rack');
    await page.getByLabel(/meta description/i).fill(
      'A practical guide to choosing a widget rack for a small team, covering price, ' +
      'fit, setup time and the two mistakes buyers make most often when comparing them.',
    );
    await page.getByLabel(/body/i).fill(
      [
        `# ${title}`,
        '',
        'A widget rack holds widgets. Here is how to choose one for a small team.',
        '',
        '## Price',
        'Most start around £19. Compare against our [pricing guide](/pricing).',
        '',
        '## Fit',
        'Measure the shelf first. See the [setup checklist](/setup).',
      ].join('\n'),
    );

    // No round trip: the same pure function runs in the browser, so the number
    // moves as the draft improves.
    await expect(page.getByTestId('word-count')).not.toHaveText('0 words');
    await expect(async () => {
      expect(Number(await score.innerText())).toBeGreaterThan(scoreBefore);
    }).toPass({ timeout: 5_000 });

    const live = Number(await score.innerText());

    await page.getByRole('button', { name: 'Save', exact: true }).click();
    await expect(page.getByText(/saved at/i)).toBeVisible({ timeout: 15_000 });

    await page.goto('/market/content');
    const row = page.getByRole('row', { name: new RegExp(title.slice(0, 30), 'i') });
    // The stored score is the server's, recomputed from the saved body — and it
    // must agree with what the writer was shown, or the number means nothing.
    await expect(row).toContainText(String(live));
  });

  test('every failing check says what to do about it', async ({ page }) => {
    await page.goto('/market/content');
    await page.getByLabel(/new document/i).fill(`Bare draft ${Date.now().toString(36)}`);
    await page.getByRole('button', { name: 'Create', exact: true }).click();
    await expect(page).toHaveURL(/\/market\/content\/[0-9a-f-]{36}/, { timeout: 15_000 });

    // An empty draft fails nearly everything; each failure must be actionable
    // rather than a red dot.
    const fixes = page.locator('aside li').filter({ hasText: 'fix' });
    await expect(fixes.first()).toBeVisible();

    const count = await fixes.count();
    for (let i = 0; i < count; i += 1) {
      await expect(fixes.nth(i)).not.toHaveText(/^\s*$/);
    }
  });

  test('readability is withheld rather than guessed for a short draft', async ({ page }) => {
    await page.goto('/market/content');
    await page.getByLabel(/new document/i).fill(`Short one ${Date.now().toString(36)}`);
    await page.getByRole('button', { name: 'Create', exact: true }).click();
    await expect(page).toHaveURL(/\/market\/content\/[0-9a-f-]{36}/, { timeout: 15_000 });

    // A Flesch number over twelve words is noise; saying so beats printing it.
    await expect(page.getByText(/too short to score reliably/i)).toBeVisible();
  });
});

test.describe('pipelines', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('a new pipeline starts paused and does not publish', async ({ page }) => {
    await page.goto('/market/pipelines');

    const name = `Weekly ${Date.now().toString(36)}`;
    await page.getByLabel(/^name$/i).fill(name);
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    // Both defaults matter: a pipeline that arrives live and publishing is a
    // decision nobody made.
    await expect(page.getByText(name, { exact: true })).toBeVisible({ timeout: 15_000 });
    await expect(page.getByText('paused').first()).toBeVisible();
    await expect(page.getByText(/leaves drafts for review/i).first()).toBeVisible();
  });
});

test.describe('trends', () => {
  test.beforeAll(async () => {
    await grantPlan('market_pro');
  });

  test('says what the first check will do, so silence is not read as failure', async ({ page }) => {
    await page.goto('/market/trends');

    await page.getByLabel(/^name$/i).fill(`Racks ${Date.now().toString(36)}`);
    await page.getByLabel(/terms/i).fill('widget racks, widget mounts');
    await page.getByRole('button', { name: /^watch$/i }).click();

    // "Nothing happened" is the correct first result and looks like a bug
    // unless the product says so first.
    await expect(page.getByText(/records a baseline and alerts on nothing/i)).toBeVisible({
      timeout: 15_000,
    });
    await expect(page.getByText(/first run records the baseline/i)).toBeVisible();
  });
});
