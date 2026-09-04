import { defineConfig, devices } from '@playwright/test';

/**
 * End-to-end tests run against a *real* stack — the dev server, Postgres, RLS,
 * the entitlement resolver and the crawl queue. Nothing is mocked, because the
 * failures worth catching here are the ones that only appear when those pieces
 * are wired together.
 *
 * The suite assumes a server is already listening. `webServer` starts one if
 * not, but reuses an existing dev server so a watch loop is not restarted on
 * every run.
 */
export default defineConfig({
  testDir: './e2e',
  globalSetup: './e2e/global-setup.ts',
  fullyParallel: false, // the specs share one workspace's data
  workers: 1,
  retries: process.env.CI ? 1 : 0,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: process.env.CI ? [['github'], ['list']] : [['list']],
  use: {
    baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3000',
    storageState: 'e2e/.auth/state.json',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  webServer: {
    command: 'pnpm --filter @mamal/app dev',
    url: process.env.E2E_BASE_URL ?? 'http://localhost:3000',
    reuseExistingServer: true,
    timeout: 120_000,
  },
});
