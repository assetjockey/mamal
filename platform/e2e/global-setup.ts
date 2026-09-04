import { chromium, type FullConfig } from '@playwright/test';
import { writeFileSync, mkdirSync } from 'node:fs';
import { dirname } from 'node:path';
import { ACCOUNT, STORAGE, newAccount, onboard, signUp } from './support.ts';

/**
 * Provisions the shared account every spec starts from: signed up *and*
 * onboarded, so a spec that needs a site does not depend on another spec having
 * run first. Order-dependent suites pass locally and fail the moment someone
 * runs one file.
 *
 * The primary journey signs up its own user instead, because exercising
 * onboarding is the point of it.
 */
export default async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0]?.use?.baseURL ?? 'http://localhost:3000';
  const account = newAccount('shared');

  const browser = await chromium.launch();
  const page = await browser.newPage({ baseURL });
  await signUp(page, account);
  await onboard(page);

  mkdirSync(dirname(STORAGE), { recursive: true });
  await page.context().storageState({ path: STORAGE });
  writeFileSync(ACCOUNT, JSON.stringify(account, null, 2));
  await browser.close();
}
