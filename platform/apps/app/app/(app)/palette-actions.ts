'use server';

import { withWorkspace } from '@mamal/db';
import { pick } from '@mamal/resources';
import { auditManifest } from '@mamal/tool-audit';
import { ToolRegistry } from '@mamal/tool-kit';
import type { PaletteItem } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/**
 * Cross-tool resource search for ⌘K.
 *
 * This is the payoff of the URN registry: one query reaches every tool's
 * resources, because each tool mints into the same table rather than owning a
 * private `websites` table the way all eighteen source products did.
 *
 * Where a hit *opens* comes from the tool's own manifest, not from a table
 * here. `externalId` is whichever primary key the owning tool keys on — a site
 * has both a `core:site` row (the hostname you own) and an `audit:audit_site`
 * row (Audit's facet of it), and only Audit knows its screens take the latter.
 * Registering a tool is therefore the whole cost of making it searchable.
 *
 * RLS plus `withWorkspace` means a query can only ever see the caller's own
 * workspace — the palette never has to filter for tenancy itself.
 */

const registry = new ToolRegistry().register(auditManifest);

/** `urn type -> { href template, label }`, built from the registered manifests. */
const ROUTES = new Map(
  registry.list().flatMap((tool) =>
    tool.resources
      .filter((r) => r.searchable && r.href)
      .map((r) => [`${tool.key}:${r.type}`, { href: r.href!, label: r.label }] as const),
  ),
);

export async function searchResources(query: string): Promise<PaletteItem[]> {
  const session = await getSession();
  if (!session) return [];

  const q = query.trim();
  if (q.length < 2) return [];

  const rows = await withWorkspace(
    session.workspace.id,
    (tx) => pick(tx, session.workspace.id, { query: q, limit: 12 }),
    { db: db() },
  );

  return rows.flatMap((r) => {
    const route = ROUTES.get(`${r.tool}:${r.type}`);
    // No declared route means no page to open. Skipping beats offering a row
    // that 404s — and it is why a `core:site` row does not appear alongside the
    // `audit:audit_site` row for the same hostname.
    if (!route) return [];
    return [
      {
        key: r.urn,
        label: r.label ?? r.externalId,
        hint: route.label,
        section: 'Results',
        href: route.href.replace(':id', encodeURIComponent(r.externalId)),
      } satisfies PaletteItem,
    ];
  });
}
