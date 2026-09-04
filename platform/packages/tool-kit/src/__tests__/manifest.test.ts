import { z } from 'zod';
import { describe, expect, it } from 'vitest';
import { defineTool } from '../manifest.ts';
import { ToolRegistry } from '../registry.ts';

const monitor = defineTool({
  key: 'monitor',
  name: 'Monitor',
  basePath: '/monitor',
  nav: [
    { key: 'monitors', label: 'Monitors', href: '/monitor' },
    { key: 'status', label: 'Status pages', href: '/monitor/status', requires: 'monitor.status_pages' },
    { key: 'agents', label: 'Private agents', href: '/monitor/agents', requires: 'monitor.private_agents' },
  ],
  events: [
    { name: 'monitor.incident.opened', payload: z.object({ monitorId: z.string() }) },
    { name: 'monitor.check.recorded', payload: z.object({ ok: z.boolean() }), highVolume: true },
  ],
  commands: [
    { name: 'monitor.createCheck', input: z.object({ target: z.string() }) },
    { name: 'monitor.pause', input: z.object({ id: z.string() }), sync: true },
  ],
  features: [
    { key: 'monitor.monitors', name: 'Monitors', kind: 'limit', freeTierAllowed: true },
    { key: 'monitor.status_pages', name: 'Status pages', kind: 'limit', freeTierAllowed: true },
    { key: 'monitor.private_agents', name: 'Private agents', kind: 'limit' },
    { key: 'monitor.ai_rca', name: 'Root-cause analysis', kind: 'metered', isAi: true, defaultCreditCost: 20 },
  ],
  aiFeatures: [{ key: 'monitor.ai_rca', name: 'Root-cause analysis', modality: 'text' }],
  queues: [{ name: 'monitor.check', concurrency: 200, attempts: 0 }],
});

describe('defineTool', () => {
  it('accepts a well-formed manifest', () => {
    expect(monitor.key).toBe('monitor');
    expect(monitor.kind).toBe('tool');
    expect(monitor.queues[0]!.attempts).toBe(0);
  });

  it('rejects an event not namespaced to its tool', () => {
    expect(() =>
      defineTool({
        key: 'monitor',
        name: 'M',
        basePath: '/m',
        events: [{ name: 'audit.run.completed', payload: z.object({}) }],
      }),
    ).toThrow(/must be namespaced under "monitor\."/);
  });

  it('rejects a command not namespaced to its tool', () => {
    expect(() =>
      defineTool({
        key: 'monitor',
        name: 'M',
        basePath: '/m',
        commands: [{ name: 'link.shorten', input: z.object({}) }],
      }),
    ).toThrow(/must be namespaced under "monitor\."/);
  });

  it('enforces the event naming grammar', () => {
    expect(() =>
      defineTool({
        key: 'monitor',
        name: 'M',
        basePath: '/m',
        events: [{ name: 'monitor.down', payload: z.object({}) }],
      }),
    ).toThrow();
  });

  /**
   * The important one: an AI feature with no billable counterpart would escape
   * both the kill switch and the lifetime exclusion.
   */
  it('refuses an AI feature that has no matching entitlement feature', () => {
    expect(() =>
      defineTool({
        key: 'monitor',
        name: 'M',
        basePath: '/m',
        aiFeatures: [{ key: 'monitor.ai_rca', name: 'RCA', modality: 'text' }],
      }),
    ).toThrow(/bypasses the AI kill switch/);
  });

  it('refuses an AI feature whose entitlement is not marked isAi', () => {
    expect(() =>
      defineTool({
        key: 'monitor',
        name: 'M',
        basePath: '/m',
        features: [{ key: 'monitor.ai_rca', name: 'RCA', kind: 'metered' }],
        aiFeatures: [{ key: 'monitor.ai_rca', name: 'RCA', modality: 'text' }],
      }),
    ).toThrow(/not marked isAi/);
  });
});

describe('ToolRegistry', () => {
  const registry = new ToolRegistry().register(monitor);

  it('filters second-tier nav by entitlement, server-side', () => {
    const nav = registry.navFor('monitor', (k) => k !== 'monitor.private_agents');
    expect(nav.map((n) => n.key)).toEqual(['monitors', 'status']);
  });

  it('resolves a command belonging to an installed tool', () => {
    const r = registry.lookupCommand('monitor.createCheck');
    expect(r.ok).toBe(true);
    expect(r.ok && r.value.sync).toBe(false);
  });

  it('degrades rather than throwing when the tool is absent', () => {
    const r = registry.lookupCommand('market.generateAd');
    expect(r.ok).toBe(false);
    expect(!r.ok && r.reason).toBe('tool_not_installed');
    expect(!r.ok && r.message).toMatch(/market tool is not installed/);
  });

  it('distinguishes an unknown command from an absent tool', () => {
    const r = registry.lookupCommand('monitor.nope');
    expect(!r.ok && r.reason).toBe('unknown_command');
  });

  it('collects every contributed entitlement and AI key', () => {
    expect(registry.allFeatures()).toHaveLength(4);
    expect(registry.allAiFeatures()).toHaveLength(1);
  });
});
