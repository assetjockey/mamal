import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';

/**
 * What has to be true the moment someone signs up.
 *
 * Every user gets exactly one personal workspace with a Default project, an
 * owner membership, and the free plan's entitlements by virtue of the free
 * floor. Nothing here is optional: a user without a workspace has no RLS scope
 * and therefore cannot see anything, including their own account.
 */
export type Provisioned = {
  workspaceId: string;
  projectId: string;
  slug: string;
};

export async function provisionWorkspace(
  tx: WorkspaceScopedDb,
  user: { id: string; email: string; name?: string | null },
): Promise<Provisioned> {
  const slug = await uniqueSlug(tx, baseSlugFor(user));
  const displayName = user.name?.trim() || user.email.split('@')[0] || 'Workspace';

  const [ws] = await tx.execute<{ id: string }>(sql`
    insert into workspaces (slug, name, kind, owner_user_id)
    values (${slug}, ${`${displayName}'s workspace`}, 'personal', ${user.id})
    returning id`);

  const [project] = await tx.execute<{ id: string }>(sql`
    insert into projects (workspace_id, name, slug, is_default)
    values (${ws!.id}, 'Default', 'default', true)
    returning id`);

  await tx.execute(sql`
    insert into workspace_members (workspace_id, user_id, role)
    values (${ws!.id}, ${user.id}, 'owner')
    on conflict (workspace_id, user_id) do nothing`);

  return { workspaceId: ws!.id, projectId: project!.id, slug };
}

/** Every workspace the user can act in, most recently used first. */
export async function workspacesFor(
  tx: WorkspaceScopedDb,
  userId: string,
): Promise<{ id: string; slug: string; name: string; role: string }[]> {
  return tx.execute<{ id: string; slug: string; name: string; role: string }>(sql`
    select w.id, w.slug, w.name, m.role
      from workspace_members m
      join workspaces w on w.id = m.workspace_id
     where m.user_id = ${userId} and w.deleted_at is null
     order by w.created_at asc`);
}

/**
 * Authorization check for a request.
 *
 * Returns the member row or null. RLS stops data leaking even if a caller
 * forgets this, but the caller still needs to know whether to render a 404.
 */
export async function memberOf(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  userId: string,
): Promise<{ role: string; toolGrants: Record<string, string | null> } | null> {
  const [row] = await tx.execute<{ role: string; tool_grants: Record<string, string | null> }>(sql`
    select role, tool_grants from workspace_members
     where workspace_id = ${workspaceId} and user_id = ${userId}`);
  return row ? { role: row.role, toolGrants: row.tool_grants ?? {} } : null;
}

const ROLE_RANK: Record<string, number> = { viewer: 0, billing: 1, member: 2, admin: 3, owner: 4 };

/**
 * `<tool>:<resource>:<action>`, with per-tool grants layered over the base
 * role — so a member can hold Link and not Market.
 */
export function can(
  member: { role: string; toolGrants: Record<string, string | null> },
  permission: string,
): boolean {
  const [tool, , action] = permission.split(':');
  if (member.role === 'owner' || member.role === 'admin') return true;

  const grant = tool ? member.toolGrants[tool] : undefined;
  const effective = grant === null ? 'none' : (grant ?? member.role);
  if (effective === 'none') return false;

  const rank = ROLE_RANK[effective] ?? 0;
  const isWrite = action !== undefined && action !== 'read' && action !== 'list';
  return isWrite ? rank >= ROLE_RANK.member! : rank >= ROLE_RANK.viewer!;
}

function baseSlugFor(user: { email: string; name?: string | null }): string {
  const source = user.name?.trim() || user.email.split('@')[0] || 'workspace';
  const slug = source
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 40);
  return slug || 'workspace';
}

async function uniqueSlug(tx: WorkspaceScopedDb, base: string): Promise<string> {
  for (let i = 0; i < 50; i++) {
    const candidate = i === 0 ? base : `${base}-${i + 1}`;
    const [taken] = await tx.execute<{ n: number }>(sql`
      select count(*)::int as n from workspaces where slug = ${candidate}`);
    if (Number(taken?.n ?? 0) === 0) return candidate;
  }
  return `${base}-${Date.now().toString(36)}`;
}
