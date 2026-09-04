import type { ToolManifest } from './manifest.ts';

/**
 * A tool absent from the instance must degrade, never throw. Automations that
 * reference it show a broken-step badge; nothing else notices.
 */
export type CommandResult<T = unknown> =
  | { ok: true; value: T }
  | { ok: false; reason: 'tool_not_installed' | 'unknown_command'; message: string };

export class ToolRegistry {
  private readonly tools = new Map<string, ToolManifest>();

  register(manifest: ToolManifest): this {
    this.tools.set(manifest.key, manifest);
    return this;
  }

  get(key: string): ToolManifest | undefined {
    return this.tools.get(key);
  }

  has(key: string): boolean {
    return this.tools.has(key);
  }

  list(kind?: 'tool' | 'plugin'): ToolManifest[] {
    const all = [...this.tools.values()];
    return kind ? all.filter((t) => t.kind === kind) : all;
  }

  /** Navigation for the second tier, already filtered by entitlement. */
  navFor(key: string, allowed: (featureKey: string) => boolean) {
    const tool = this.tools.get(key);
    if (!tool) return [];
    const keep = (items: ToolManifest['nav']): ToolManifest['nav'] =>
      items
        .filter((i) => !i.requires || allowed(i.requires))
        .map((i) => (i.children ? { ...i, children: keep(i.children) } : i));
    return keep(tool.nav);
  }

  /** Resolve a command without importing the owning tool. */
  lookupCommand(name: string): CommandResult<{ tool: string; sync: boolean }> {
    const toolKey = name.split('.')[0];
    if (!toolKey) {
      return { ok: false, reason: 'unknown_command', message: `malformed command "${name}"` };
    }
    const tool = this.tools.get(toolKey);
    if (!tool) {
      return {
        ok: false,
        reason: 'tool_not_installed',
        message: `the ${toolKey} tool is not installed on this instance`,
      };
    }
    const command = tool.commands.find((c) => c.name === name);
    if (!command) {
      return { ok: false, reason: 'unknown_command', message: `${toolKey} exposes no "${name}"` };
    }
    return { ok: true, value: { tool: toolKey, sync: command.sync } };
  }

  /** Every entitlement key contributed by installed tools. */
  allFeatures() {
    return this.list().flatMap((t) => t.features);
  }

  allAiFeatures() {
    return this.list().flatMap((t) => t.aiFeatures);
  }
}
