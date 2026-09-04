import type { AiDriver } from '../types.ts';
import { anthropicDriver } from './anthropic.ts';

/**
 * The driver registry.
 *
 * Adding a provider is a driver file plus an `ai_providers` row — the model
 * catalogue itself is data, so a new model on launch day is an admin form.
 */
const DRIVERS: Record<string, AiDriver> = {
  [anthropicDriver.key]: anthropicDriver,
};

export function driverFor(providerKey: string): AiDriver | undefined {
  return DRIVERS[providerKey];
}

export function registerDriver(driver: AiDriver): void {
  DRIVERS[driver.key] = driver;
}

export { anthropicDriver };
