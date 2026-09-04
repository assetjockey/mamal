import { EventRegistry, coreEvents } from '@mamal/bus';
import { auditManifest } from '@mamal/tool-audit';
import { confirmManifest } from '@mamal/tool-confirm';
import { linkManifest } from '@mamal/tool-link';

/**
 * The app's event registry.
 *
 * `publish` refuses a name it does not know, which is deliberate: an event
 * published without a declared payload has no schema for a subscriber to trust,
 * and a typo in a name is otherwise a message nobody ever receives.
 *
 * Assembled from the manifests rather than a hand-written list — the manifest
 * is a tool's only public surface (§0.2b), and its `events` array is the
 * declaration. A tool absent from the build contributes nothing, and nothing
 * here needs to know that happened.
 *
 * The import list is the one place that names every tool, which is the
 * composition root doing its job. `services/worker-core` has the same shape for
 * the same reason.
 */
const MANIFESTS = [auditManifest, confirmManifest, linkManifest];

let cached: EventRegistry | null = null;

export function eventRegistry(): EventRegistry {
  if (cached) return cached;
  const registry = new EventRegistry().register(...coreEvents);
  for (const manifest of MANIFESTS) {
    registry.register(...manifest.events);
  }
  cached = registry;
  return registry;
}
