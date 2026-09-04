import type { Page } from '@playwright/test';

export const STORAGE = 'e2e/.auth/state.json';
export const ACCOUNT = 'e2e/.auth/account.json';

export type Account = { name: string; email: string; password: string };

export function newAccount(tag: string): Account {
  const stamp = `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
  return { name: `E2E ${tag}`, email: `e2e+${tag}-${stamp}@mamal.test`, password: `e2e-${stamp}-Aa1!` };
}

/**
 * Signs up through the real form.
 *
 * Deliberately not by inserting rows: provisioning a workspace, seeding its
 * default project and attaching the free plan all happen on user creation, and
 * a fixture that bypasses that would set up a state no real user is ever in.
 */
export async function signUp(page: Page, account: Account): Promise<void> {
  await page.goto('/sign-up');
  await page.fill('#name', account.name);
  await page.fill('#email', account.email);
  await page.fill('#password', account.password);
  await page.getByRole('button', { name: /create|sign up/i }).click();
  await page.waitForURL((url) => !/\/sign-(in|up)/.test(url.pathname), { timeout: 30_000 });
}

/**
 * Completes onboarding: one interest, one URL.
 *
 * Adding the site also registers it with Audit and queues the first crawl, so
 * after this the workspace has data for any spec that needs some.
 */
export async function onboard(page: Page, url = 'https://www.example.com'): Promise<void> {
  await page.goto('/welcome');
  await page.getByRole('button', { name: /^Fix what is hurting/ }).click();
  // Exact name, not a loose regex: `/go/` also matches "Know the moment my
  // site goes down", which toggles an interest instead of submitting.
  await page.getByRole('textbox', { name: /website address/i }).fill(url);
  await page.getByRole('button', { name: 'Continue', exact: true }).click();
  await page.waitForURL((u) => u.pathname === '/', { timeout: 30_000 });
}

/**
 * Puts the signed-in workspace on a plan.
 *
 * Some journeys only exist above the free tier — targeting rules, custom
 * domains, push. Testing them by *asserting the upgrade gate* proves the gate
 * and nothing else, so the harness needs a way to be a paying customer.
 *
 * A direct row rather than a checkout: Stripe is not in the loop for these
 * tests, and the entitlement resolver reads `subscriptions`, which is exactly
 * what the journey depends on. The free plan stays attached — it is a floor,
 * not a contributor, and leaving it proves the merge does the right thing.
 */
export async function grantPlan(planKey: string): Promise<void> {
  const { asPlatformAdmin, closeDb, unsafeUnscopedDb } = await import('@mamal/db');
  const { sql } = await import('drizzle-orm');
  const { readFileSync } = await import('node:fs');

  const account = JSON.parse(readFileSync(ACCOUNT, 'utf8')) as Account;
  const db = unsafeUnscopedDb();
  await asPlatformAdmin(async (tx) => {
    const [row] = await tx.execute<{ id: string }>(sql`
      select w.id from workspaces w
        join users u on u.id = w.owner_user_id
       where u.email = ${account.email}
       limit 1`);
    if (!row) throw new Error(`no workspace for ${account.email}`);
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${row.id}, id, 'active' from plans where key = ${planKey}
      on conflict do nothing`);
  }, { db });
  await closeDb();
}
