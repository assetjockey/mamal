import { test, expect } from '@playwright/test';
import { grantPlan } from './support.ts';

/**
 * A transfer, end to end, with real bytes.
 *
 * The journey that matters is not "a row appeared" — it is that a file picked
 * in this browser reaches storage through pre-authorised URLs, survives being
 * interrupted, and comes back to a recipient with no account byte-for-byte.
 *
 * The recipient is a separate request context throughout: a share link has to
 * work for somebody who has never signed in, and driving it from the
 * authenticated page would hide a dependency on a session.
 */
test.describe('file transfers', () => {
  /*
   * The free plan allows exactly one active transfer, and enforces it — so
   * without this the second test finds an upgrade gate where it expects a
   * button, which is the entitlement working rather than a bug. The gate
   * itself is asserted in `link.spec.ts`; these tests are about the bytes.
   */
  test.beforeAll(async () => {
    await grantPlan('link_starter');
  });

  test('upload, share, download — and the gates in between', async ({ page, playwright, baseURL }) => {
    test.setTimeout(120_000);

    await page.goto('/link/transfers');
    await page.getByRole('button', { name: /new transfer/i }).click();
    await page.getByLabel(/what is it/i).fill('Final artwork');
    await page.getByLabel(/message/i).fill('Latest cut, ignore the earlier one.');
    await page.getByLabel(/password/i).fill('open sesame');
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    // The uploader appears under a transfer that is still being filled.
    const picker = page.getByLabel(/add files/i);
    await expect(picker).toBeVisible({ timeout: 15_000 });

    /*
     * Two files, one of them over the 8 MB part size so the multipart path is
     * actually exercised. A single small file uploads in one part and would
     * pass while every ordering and assembly bug survived.
     */
    const big = Buffer.alloc(9 * 1024 * 1024);
    for (let i = 0; i < big.length; i++) big[i] = (i * 31 + 7) & 0xff;

    await picker.setInputFiles([
      { name: 'notes.txt', mimeType: 'text/plain', buffer: Buffer.from('the quick brown fox') },
      { name: 'render.bin', mimeType: 'application/octet-stream', buffer: big },
    ]);

    await expect(page.getByText(/ready to share/i)).toBeVisible({ timeout: 90_000 });
    await expect(page.getByText('Ready', { exact: true })).toBeVisible({ timeout: 15_000 });

    // The share URL is on the row; a recipient gets there through the link.
    const shareText = await page.locator('td', { hasText: '/r/' }).first().innerText();
    const alias = /\/r\/([A-Za-z0-9_-]+)/.exec(shareText)?.[1];
    expect(alias, 'the transfer must carry a share link').toBeTruthy();

    const recipient = await playwright.request.newContext({ baseURL });
    const resolved = await recipient.get(`/r/${alias}`, { maxRedirects: 0 });
    expect(resolved.status()).toBe(302);
    const sharePage = resolved.headers()['location']!;
    expect(sharePage).toContain('/p/transfer/');

    const html = await (await recipient.get(sharePage)).text();
    expect(html).toContain('Final artwork');
    expect(html).toContain('notes.txt');
    expect(html).toContain('render.bin');
    // The sender's message reaches them; the password does not.
    expect(html).toContain('Latest cut');
    expect(html).not.toContain('open sesame');

    /*
     * The transfer id comes from the page's own form, not from the URL.
     *
     * `/p/transfer/<id>` carries the **link** id — the redirect resolves a link
     * and hands off to the renderer, which finds the transfer behind it. Using
     * the URL segment as a transfer id looks right and answers `not_found`.
     */
    const transferId = /action="\/api\/link\/transfers\/([0-9a-f-]{36})\/download"/.exec(html)?.[1];
    expect(transferId, 'the share page must post to its own transfer').toBeTruthy();

    // The gate is real: no password, no bytes.
    const refused = await recipient.post(`/api/link/transfers/${transferId}/download`, {
      form: { password: 'wrong' },
      maxRedirects: 0,
    });
    expect(refused.headers()['location']).toContain('#password');

    // With the password, each file is claimed separately and answers with a
    // short-lived signed URL — the bytes come from storage, not from the app.
    const fileIds = [...html.matchAll(/name="fileId" value="([0-9a-f-]{36})"/g)].map((m) => m[1]!);
    expect(fileIds.length, 'each file gets its own download control').toBe(2);

    const downloaded: Record<string, Buffer> = {};
    for (const fileId of fileIds) {
      const claim = await recipient.post(`/api/link/transfers/${transferId}/download`, {
        form: { password: 'open sesame', fileId },
        maxRedirects: 0,
      });
      const url = claim.headers()['location']!;
      expect(url, 'a claim must hand back somewhere to fetch from').toContain('/api/storage');

      const file = await recipient.get(url);
      expect(file.status()).toBe(200);
      const disposition = file.headers()['content-disposition'] ?? '';
      const name = /filename="([^"]+)"/.exec(disposition)?.[1] ?? '';
      // The stored key is random; the sender's name is restored on the way out.
      expect(name).toMatch(/^(notes\.txt|render\.bin)$/);
      downloaded[name] = Buffer.from(await file.body());
    }

    expect(downloaded['notes.txt']?.toString()).toBe('the quick brown fox');
    expect(downloaded['render.bin']?.length, 'the multipart file must be whole').toBe(big.length);
    expect(downloaded['render.bin']?.equals(big), 'byte-for-byte what was uploaded').toBe(true);
  });

  test('a pulled-back transfer says why', async ({ page, playwright, baseURL }) => {
    test.setTimeout(120_000);

    await page.goto('/link/transfers');
    await page.getByRole('button', { name: /new transfer/i }).click();
    await page.getByLabel(/what is it/i).fill('Wrong version');
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    // Scoped to this transfer's own row throughout: the table holds every
    // transfer the suite has made, and `.first()` picks whichever sorted
    // highest — a test that passes or fails on ordering.
    const row = page.locator('tr', { hasText: 'Wrong version' });
    await expect(row).toBeVisible({ timeout: 15_000 });

    await row.getByLabel(/add files/i).setInputFiles([
      { name: 'draft.txt', mimeType: 'text/plain', buffer: Buffer.from('oops') },
    ]);
    await expect(page.getByText(/ready to share/i)).toBeVisible({ timeout: 60_000 });

    await row.getByRole('button', { name: /pull back/i }).click();
    await row.getByLabel(/why are you pulling back/i).fill('Sent the wrong cut');
    await row.getByRole('button', { name: /confirm/i }).click();
    await expect(page.getByText(/recipients see your reason/i)).toBeVisible({ timeout: 15_000 });

    // What the recipient gets: the reason, not a dead link.
    const alias = /\/r\/([A-Za-z0-9_-]+)/.exec(await row.innerText())?.[1];
    expect(alias).toBeTruthy();
    const recipient = await playwright.request.newContext({ baseURL });
    const resolved = await recipient.get(`/r/${alias}`, { maxRedirects: 0 });
    const html = await (await recipient.get(resolved.headers()['location']!)).text();
    expect(html).toContain('pulled back');
    expect(html).toContain('Sent the wrong cut');
  });
});
