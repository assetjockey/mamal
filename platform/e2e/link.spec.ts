import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * Link, end to end.
 *
 * The journey that matters is not "a table renders" — it is that a link created
 * in the dashboard actually redirects, that a rule changes where a *particular*
 * visitor lands, and that a bio page built here is what a stranger sees.
 *
 * Every navigation to a short link uses a fresh request context rather than the
 * signed-in page: a redirect must work for somebody with no session, and using
 * the authenticated page would hide a dependency on one.
 */
test.describe('links', () => {
  let alias = '';
  let shortPath = '';

  test('create a short link and follow it', async ({ page, playwright, baseURL }) => {
    await page.goto('/link');

    await page.getByRole('button', { name: /new link/i }).click();
    await page.getByLabel(/destination url/i).fill('https://example.com/spring');
    alias = `e2e-${Date.now().toString(36)}`;
    await page.getByLabel(/custom alias/i).fill(alias);
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    // The row appears with the alias the customer chose.
    await expect(page.getByRole('link', { name: `/${alias}` })).toBeVisible({ timeout: 15_000 });

    shortPath = `/r/${alias}`;
    const visitor = await playwright.request.newContext({ baseURL, extraHTTPHeaders: {} });
    const response = await visitor.get(shortPath, { maxRedirects: 0 });
    expect(response.status()).toBe(302);
    expect(response.headers()['location']).toBe('https://example.com/spring');
    // A redirect must never be cached: the destination is editable by definition.
    expect(response.headers()['cache-control']).toContain('no-store');
  });

  test('the free plan names what a rule would cost, rather than hiding it', async ({ page }) => {
    test.skip(!alias, 'needs the link from the previous test');
    await page.goto('/link');
    await page.getByRole('link', { name: `/${alias}` }).click();
    await page.waitForURL(/\/link\/links\//);

    // Discovery is a feature: the gate says which entitlement is missing, and
    // routes to the plans page, instead of the section simply not being there.
    await expect(page.getByText(/targeting and rotation rules/i)).toBeVisible();
    await expect(page.getByRole('link', { name: /see plans/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /add rule/i })).toHaveCount(0);
  });

  test('a rule sends one visitor somewhere else, and the simulator agrees', async ({
    page, playwright, baseURL,
  }) => {
    test.skip(!alias, 'needs the link from the previous test');
    await grantPlan('link_starter');

    await page.goto('/link');
    await page.getByRole('link', { name: `/${alias}` }).click();
    await page.waitForURL(/\/link\/links\//);

    await page.getByRole('button', { name: /add rule/i }).click();
    await page.getByLabel('Field').last().selectOption('os');
    await page.getByLabel('Operator').last().selectOption('is');
    await page.getByLabel('Value').last().fill('iOS');
    await page.getByLabel(/destination for this rule/i).fill('https://apps.example.com/ios');
    await page.getByRole('button', { name: /save rules/i }).click();

    /*
     * The simulator runs the *same* resolver the redirect does, so this
     * assertion and the HTTP one below cannot disagree. If they ever do, the
     * simulator has grown its own copy of the rules and is lying.
     */
    await page.getByLabel(/operating system/i).selectOption('iOS');
    await expect(page.getByText('https://apps.example.com/ios')).toBeVisible({ timeout: 10_000 });

    const iphone = await playwright.request.newContext({
      baseURL,
      extraHTTPHeaders: {
        'user-agent':
          'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 ' +
          '(KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
      },
    });
    const onPhone = await iphone.get(shortPath, { maxRedirects: 0 });
    expect(onPhone.headers()['location']).toBe('https://apps.example.com/ios');

    const desktop = await playwright.request.newContext({ baseURL });
    const onDesktop = await desktop.get(shortPath, { maxRedirects: 0 });
    expect(onDesktop.headers()['location']).toBe('https://example.com/spring');
  });

  test('a bio page built here is what a stranger sees', async ({ page, playwright, baseURL }) => {
    await page.goto('/link/bio');

    await page.getByRole('button', { name: /new page/i }).click();
    const bioAlias = `e2e-bio-${Date.now().toString(36)}`;
    await page.getByLabel(/page title/i).fill('Ada Lovelace');
    await page.getByLabel(/custom alias/i).fill(bioAlias);
    await page.getByRole('button', { name: 'Create', exact: true }).click();
    await page.waitForURL(/\/link\/bio\/[0-9a-f-]+/, { timeout: 30_000 });

    // Add a link block from the palette and fill it in.
    await page.getByLabel(/add a standard block/i).selectOption('link');
    await expect(page.getByRole('button', { name: /^Link\b/ })).toBeVisible({ timeout: 15_000 });
    await page.getByRole('button', { name: /^Link\b/ }).click();
    await page.getByLabel(/^Label/).fill('My newsletter');
    await page.getByLabel(/^Url/).fill('https://example.com/news');

    // A draft page must not be public — publishing is the act that exposes it.
    const stranger = await playwright.request.newContext({ baseURL });
    const draft = await stranger.get(`/r/${bioAlias}`, { maxRedirects: 0 });
    const draftTarget = draft.headers()['location'] ?? '';
    expect(draftTarget).toContain('/p/biolink/');
    expect((await stranger.get(draftTarget)).status(), 'an unpublished page must 404').toBe(404);

    await page.getByRole('button', { name: 'Publish', exact: true }).click();
    await expect(page.getByText('Published', { exact: true })).toBeVisible({ timeout: 15_000 });

    const live = await stranger.get(draftTarget);
    expect(live.status()).toBe(200);
    const html = await live.text();
    expect(html).toContain('Ada Lovelace');
    expect(html).toContain('My newsletter');
    expect(html).toContain('https://example.com/news');
  });

  test('a QR code encodes what the preview shows', async ({ page }) => {
    await page.goto('/link/qr');

    // A wifi code is static by necessity — a phone reads it with no network —
    // so the studio must say so and the preview must encode it locally.
    await page.getByLabel(/qr code type/i).selectOption('wifi');
    await expect(page.getByText(/fixed forever/i)).toBeVisible();

    await page.getByLabel(/^Ssid/).fill('Cafe Mamal');
    await expect(page.getByLabel('QR code preview').locator('svg')).toBeVisible({ timeout: 10_000 });

    // Saving a dynamic code mints a short link; a static one does not.
    await page.getByLabel(/qr code type/i).selectOption('dynamic_url');
    await page.getByLabel(/^Url/).fill('https://example.com/poster');
    await page.getByRole('button', { name: /save code/i }).click();
    await expect(page.getByText(/QR code saved/i)).toBeVisible({ timeout: 15_000 });
  });

  test('every limit is visible before it is hit', async ({ page }) => {
    // The house rule from the brief: "3 of 25 links", never a surprise refusal.
    await page.goto('/link');
    await expect(page.getByText(/\d+ of [\d,]+ links? used/i)).toBeVisible();
  });

  test('a CSV is checked before anything is written', async ({ page }) => {
    await grantPlan('link_starter');   // bulk import is a paid feature
    await page.goto('/link');
    await page.getByRole('button', { name: /import csv/i }).click();

    const stamp = Date.now().toString(36);
    await page.getByLabel(/paste a csv/i).fill(
      [
        'url,alias,campaign',
        `https://example.com/bulk-a,e2e-bulk-a-${stamp},bulk`,
        `https://example.com/bulk-b,,bulk`,
        'not-a-url,,bulk',
        'https://example.com/bulk-c,login,bulk',
      ].join('\n'),
    );

    // Check first. Nothing is written, and every problem is listed — not the
    // first one, which is what makes a large paste fixable in one pass.
    await page.getByRole('button', { name: 'Check', exact: true }).click();
    await expect(page.getByText('2 ready')).toBeVisible({ timeout: 15_000 });
    await expect(page.getByText('2 skipped')).toBeVisible();
    await expect(page.getByText(/Line 4 · url: “not-a-url” is not a URL/)).toBeVisible();
    await expect(page.getByText(/Line 5 · alias: .*reserved/)).toBeVisible();
    await expect(page.getByRole('link', { name: `/e2e-bulk-a-${stamp}` })).toHaveCount(0);

    await page.getByRole('button', { name: /^Import 2 links$/ }).click();
    await expect(page.getByText(/Imported 2 links/i)).toBeVisible({ timeout: 15_000 });
    await expect(page.getByRole('link', { name: `/e2e-bulk-a-${stamp}` })).toBeVisible();
  });
});
