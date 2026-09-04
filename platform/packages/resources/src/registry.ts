import { and, eq, inArray, or, sql } from 'drizzle-orm';
import { resourceLinks, resources, type WorkspaceScopedDb } from '@mamal/db';
import type { ResourceRelation } from '@mamal/db';
import { makeUrn, parseUrn, type Urn } from './urn.ts';

export type ResourceRecord = {
  id: string;
  urn: Urn;
  tool: string;
  type: string;
  externalId: string;
  label: string | null;
  status: string;
  attrs: Record<string, unknown>;
  projectId: string;
};

/**
 * Register (or re-register) a resource.
 *
 * Call this in the SAME transaction as the row it describes, so a tool can
 * never end up with an object the rest of the platform cannot address.
 */
export async function mint(
  tx: WorkspaceScopedDb,
  input: {
    workspaceId: string;
    projectId: string;
    tool: string;
    type: string;
    externalId: string;
    label?: string;
    status?: string;
    attrs?: Record<string, unknown>;
  },
): Promise<ResourceRecord> {
  const urn = makeUrn(input.tool, input.type, input.externalId);
  const [row] = await tx
    .insert(resources)
    .values({
      workspaceId: input.workspaceId,
      projectId: input.projectId,
      urn,
      tool: input.tool,
      type: input.type,
      externalId: input.externalId,
      label: input.label ?? null,
      status: input.status ?? 'active',
      attrs: input.attrs ?? {},
    })
    .onConflictDoUpdate({
      target: [resources.workspaceId, resources.urn],
      set: {
        label: input.label ?? null,
        status: input.status ?? 'active',
        attrs: input.attrs ?? {},
        updatedAt: new Date(),
      },
    })
    .returning();
  return toRecord(row!);
}

export async function resolveUrn(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  urn: string,
): Promise<ResourceRecord | null> {
  const [row] = await tx
    .select()
    .from(resources)
    .where(and(eq(resources.workspaceId, workspaceId), eq(resources.urn, urn)))
    .limit(1);
  return row ? toRecord(row) : null;
}

/**
 * Connect two resources.
 *
 * `createdBy` records whether a person or an automation made the edge, which
 * is what lets an automation clean up only its own links when the condition
 * that created them goes away.
 */
export async function relate(
  tx: WorkspaceScopedDb,
  input: {
    workspaceId: string;
    from: string; // URN
    to: string; // URN
    relation: ResourceRelation;
    createdBy?: string;
    metadata?: Record<string, unknown>;
  },
): Promise<void> {
  const [fromRow, toRow] = await Promise.all([
    resolveUrn(tx, input.workspaceId, input.from),
    resolveUrn(tx, input.workspaceId, input.to),
  ]);
  if (!fromRow) throw new Error(`unknown resource: ${input.from}`);
  if (!toRow) throw new Error(`unknown resource: ${input.to}`);

  await tx
    .insert(resourceLinks)
    .values({
      workspaceId: input.workspaceId,
      fromResourceId: fromRow.id,
      toResourceId: toRow.id,
      relation: input.relation,
      createdBy: input.createdBy ?? 'system',
      metadata: input.metadata ?? {},
    })
    .onConflictDoNothing();
}

export async function unrelate(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; from: string; to: string; relation: ResourceRelation },
): Promise<void> {
  const [fromRow, toRow] = await Promise.all([
    resolveUrn(tx, input.workspaceId, input.from),
    resolveUrn(tx, input.workspaceId, input.to),
  ]);
  if (!fromRow || !toRow) return;
  await tx
    .delete(resourceLinks)
    .where(
      and(
        eq(resourceLinks.workspaceId, input.workspaceId),
        eq(resourceLinks.fromResourceId, fromRow.id),
        eq(resourceLinks.toResourceId, toRow.id),
        eq(resourceLinks.relation, input.relation),
      ),
    );
}

export type Neighbor = ResourceRecord & {
  relation: ResourceRelation;
  direction: 'out' | 'in';
  createdBy: string;
};

/**
 * Everything connected to this resource, in either direction.
 *
 * This is the query behind the "Connected" panel that appears on every detail
 * page in every tool — the visible payoff of unification. A site's panel shows
 * the monitors watching it, the audits run against it, the links pointing at
 * it, and the campaigns running on it, without any tool knowing about another.
 */
export async function neighbors(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  urn: string,
  filter?: { relation?: ResourceRelation; tool?: string },
): Promise<Neighbor[]> {
  const self = await resolveUrn(tx, workspaceId, urn);
  if (!self) return [];

  const edges = await tx
    .select()
    .from(resourceLinks)
    .where(
      and(
        eq(resourceLinks.workspaceId, workspaceId),
        or(
          eq(resourceLinks.fromResourceId, self.id),
          eq(resourceLinks.toResourceId, self.id),
        ),
        filter?.relation ? eq(resourceLinks.relation, filter.relation) : undefined,
      ),
    );
  if (edges.length === 0) return [];

  const otherIds = edges.map((e) =>
    e.fromResourceId === self.id ? e.toResourceId : e.fromResourceId,
  );
  const rows = await tx
    .select()
    .from(resources)
    .where(and(eq(resources.workspaceId, workspaceId), inArray(resources.id, otherIds)));

  const byId = new Map(rows.map((r) => [r.id, r]));
  const out: Neighbor[] = [];
  for (const edge of edges) {
    const isOut = edge.fromResourceId === self.id;
    const other = byId.get(isOut ? edge.toResourceId : edge.fromResourceId);
    if (!other) continue;
    if (filter?.tool && other.tool !== filter.tool) continue;
    out.push({
      ...toRecord(other),
      relation: edge.relation,
      direction: isOut ? 'out' : 'in',
      createdBy: edge.createdBy,
    });
  }
  return out;
}

/** Backing query for the automations resource picker. */
export async function pick(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  opts: { type?: string; tool?: string; query?: string; limit?: number },
): Promise<ResourceRecord[]> {
  const rows = await tx
    .select()
    .from(resources)
    .where(
      and(
        eq(resources.workspaceId, workspaceId),
        opts.type ? eq(resources.type, opts.type) : undefined,
        opts.tool ? eq(resources.tool, opts.tool) : undefined,
        opts.query ? sql`${resources.label} ilike ${'%' + opts.query + '%'}` : undefined,
      ),
    )
    .limit(opts.limit ?? 25);
  return rows.map(toRecord);
}

function toRecord(row: typeof resources.$inferSelect): ResourceRecord {
  parseUrn(row.urn); // fail loudly on a malformed URN rather than downstream
  return {
    id: row.id,
    urn: row.urn as Urn,
    tool: row.tool,
    type: row.type,
    externalId: row.externalId,
    label: row.label,
    status: row.status,
    attrs: (row.attrs ?? {}) as Record<string, unknown>,
    projectId: row.projectId,
  };
}
