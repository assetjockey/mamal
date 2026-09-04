import { test, expect } from '@playwright/test';

/**
 * Confirm, end to end.
 *
 * The journey that matters is not "an admin screen renders" — it is that a
 * widget configured here reaches a real page, and that what crosses into that
 * page is only what should.
 */
test.describe('social proof', () => {
  let pixelKey = '';

  test('create a campaign, add a notification, and see it on a page', async ({ page }) => {
    await page.goto('/confirm');

    // The shared fixture account has one site and no campaign yet.
    const existing = page.locator('a[href^="/confirm/campaigns/"]').first();
    if (!(await existing.isVisible().catch(() => false))) {
      await page.getByRole('button', { name: /new campaign/i }).click();
      await page.waitForURL(/\/confirm\/campaigns\//, { timeout: 30_000 });
    } else {
      await existing.click();
      await page.waitForURL(/\/confirm\/campaigns\//);
    }

    // The install snippet carries a real key — this is what a customer pastes.
    const snippet = page.locator('pre').first();
    await expect(snippet).toContainText('confirm.js');
    const text = (await snippet.textContent()) ?? '';
    pixelKey = /data-key="([^"]+)"/.exec(text)?.[1] ?? '';
    expect(pixelKey, 'the snippet must carry a pixel key').toMatch(/^ck_/);

    // Add a notification from the 44-type picker.
    await page.getByRole('button', { name: /add notification/i }).click();
    const picker = page.getByRole('dialog', { name: /choose a notification type/i });
    await expect(picker).toBeVisible();
    /*
     * A proof widget, not a coupon.
     *
     * The first version of this test added a Coupon and then expected a
     * conversion feed in the payload — which came back empty, correctly:
     * `coupon` does not declare `needs: ['conversions']`, so it is sent none.
     * That is the withholding rule doing its job, and the journey needs a
     * widget that actually consumes the feed.
     */
    await picker.getByRole('textbox').fill('recent');
    // The accessible name is the label *and* the description — the picker shows
    // both, which is the point of it. Anchor on the start rather than the whole.
    await picker.getByRole('button', { name: /^Recent conversion\b/ }).first().click();

    // Lands in the editor, with the live preview.
    await page.waitForURL(/\/confirm\/widgets\//, { timeout: 30_000 });
    await expect(page.getByRole('heading', { name: /preview/i }).or(page.getByText('PREVIEW'))).toBeVisible();
    await expect(page.locator('iframe[title^="Preview"]')).toBeVisible();
  });

  test('the payload a browser fetches carries only what a proof line needs', async ({ playwright, baseURL }) => {
    test.skip(!pixelKey, 'needs the campaign from the previous test');
    const api = await playwright.request.newContext({ baseURL });

    /*
     * Enough to clear the type's default minimum of 3.
     *
     * "Show nothing below N recent sales" is a promise not to fabricate proof,
     * and it is enforced server-side — so a journey that posts one conversion
     * would see the widget correctly withheld.
     */
    for (let i = 0; i < 4; i++) {
      const posted = await api.post('/api/c/conversion', {
        data: {
          key: pixelKey,
          type: 'bought',
          data: { name: 'Ana Silva', city: 'Lisbon', email: 'ana@example.com', amount: 149 },
        },
      });
      expect(posted.status()).toBe(201);
    }

    const payload = await (await api.get(`/c/${pixelKey}.json`)).json();
    const serialised = JSON.stringify(payload.conversions);

    // What a proof line needs.
    expect(serialised).toContain('Ana');
    expect(serialised).toContain('Lisbon');

    /*
     * What must never cross. This response is readable by anyone with devtools
     * on the customer's site — including their competitors — so an email, a
     * surname or an order value here is a leak of someone else's customer data.
     *
     * Checked by *shape*, not by substring. `not.toContain('149')` looks
     * equivalent and is not: the payload carries ISO timestamps, and one of
     * them will eventually read `…T05:41:49.149Z` — a failure that appears once
     * in a few hundred runs, blames the privacy projection, and is impossible
     * to reproduce. The projection either emits a field or it does not, so
     * assert that.
     */
    expect(serialised).not.toContain('ana@example.com');
    expect(serialised).not.toContain('Silva');

    const keys = new Set(payload.conversions.flatMap((c: object) => Object.keys(c)));
    expect([...keys].sort()).toEqual(['city', 'country', 'name', 'occurredAt', 'type']);
    for (const c of payload.conversions as { name: string }[]) {
      // First name only — the stored value is "Ana Silva".
      expect(c.name).toBe('Ana');
    }
  });

  test('the runtime renders on a third-party page without moving it', async ({ page, baseURL }) => {
    test.skip(!pixelKey, 'needs the campaign from the first test');

    // A page we do not control, loading the script the way a customer would.
    await page.setContent(`
      <h1 id="title">Someone else's website</h1>
      <p id="copy">Ordinary content.</p>
      <script src="${baseURL}/confirm.js" data-key="${pixelKey}"></script>
    `, { waitUntil: 'load' });

    const before = await page.locator('#title').boundingBox();
    await page.waitForFunction(
      () => [...document.body.children].some((e) => e.shadowRoot),
      undefined,
      { timeout: 20_000 },
    );

    const rendered = await page.evaluate(() =>
      [...document.body.children]
        .filter((e) => e.shadowRoot)
        .map((e) => e.shadowRoot!.querySelector('.w')?.textContent?.trim() ?? ''),
    );
    expect(rendered.join(' ')).toMatch(/\S/);

    // The host page must not have moved. This is the no-layout-shift promise,
    // and it is the difference between a widget a customer keeps and one they
    // rip out.
    const after = await page.locator('#title').boundingBox();
    expect(after).toEqual(before);

    // Only one widget occupies a position at a time.
    const visible = await page.evaluate(() =>
      [...document.body.children].filter(
        (e) => e.shadowRoot && e.shadowRoot.querySelector('.w'),
      ).length,
    );
    expect(visible).toBeLessThanOrEqual(1);
  });

  test('push refuses what it should', async ({ playwright, baseURL }) => {
    const api = await playwright.request.newContext({ baseURL });

    // The service worker is served, and scoped to the root.
    const sw = await api.get('/api/push/sw');
    expect(sw.ok()).toBeTruthy();
    expect(sw.headers()['service-worker-allowed']).toBe('/');

    // A subscription with no endpoint is a bad request, not a stored row.
    expect((await api.post('/api/push/subscribe', { data: { key: 'x' } })).status()).toBe(400);

    // A non-https endpoint is refused: without this the table becomes a place
    // to store arbitrary strings.
    const bad = await api.post('/api/push/subscribe', {
      data: {
        key: 'x',
        subscription: { endpoint: 'file:///etc/passwd', keys: { p256dh: 'k', auth: 'a' } },
      },
    });
    expect(bad.status()).toBe(400);

    // An unknown key is a 404 — the same answer as a disabled site, so keys
    // cannot be enumerated.
    const unknown = await api.post('/api/push/subscribe', {
      data: {
        key: 'definitely-not-a-key',
        subscription: {
          endpoint: 'https://fcm.googleapis.com/fcm/send/x',
          keys: { p256dh: 'k', auth: 'a' },
        },
      },
    });
    expect(unknown.status()).toBe(404);
  });
});
