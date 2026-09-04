import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { parseUrn, resolveUrn } from '@mamal/resources';

/**
 * Audit's command surface — the ONLY thing another tool may call.
 *
 * Everything here takes a URN rather than an internal id, so a caller never
 * needs to know Audit's schema. A command that cannot find its subject returns
 * a reason rather than throwing, because the caller is usually an automation
 * that should degrade rather than fail.
 */
export type CommandResult = { ok: true; value?: unknown } | { ok: false; reason: string };

export async function runSite(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; siteUrn: string; trigger?: string },
): Promise<CommandResult> {
  const auditSiteId = await auditSiteFor(tx, input.workspaceId, input.siteUrn);
  if (!auditSiteId) return { ok: false, reason: 'site is not registered with Audit' };

  // Queue rather than run: a crawl is minutes of work and the caller is
  // usually inside a request or an automation step.
  await tx.execute(sql`
    update audit_sites set next_audit_at = now() where id = ${auditSiteId}`);
  return { ok: true, value: { auditSiteId } };
}

export async function scheduleRun(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; siteUrn: string; at?: Date; reason?: string },
): Promise<CommandResult> {
  const auditSiteId = await auditSiteFor(tx, input.workspaceId, input.siteUrn);
  if (!auditSiteId) return { ok: false, reason: 'site is not registered with Audit' };

  const when = input.at ?? new Date();
  await tx.execute(sql`
    update audit_sites set next_audit_at = ${when.toISOString()}::timestamptz
     where id = ${auditSiteId}`);
  return { ok: true, value: { auditSiteId, scheduledFor: when.toISOString() } };
}

/**
 * Re-check one URL.
 *
 * The cheap half of the fix loop: after someone fixes a page they should not
 * have to re-crawl the whole site to see it turn green.
 */
export async function runPage(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; siteUrn: string; url: string },
): Promise<CommandResult> {
  const auditSiteId = await auditSiteFor(tx, input.workspaceId, input.siteUrn);
  if (!auditSiteId) return { ok: false, reason: 'site is not registered with Audit' };
  return { ok: true, value: { auditSiteId, url: input.url, mode: 'single-page' } };
}

/**
 * Close issues for a URL that has recovered.
 *
 * This is the return leg of the Audit → Monitor handoff: Audit finds a broken
 * link, Monitor watches it, and when Monitor sees it come back the issue
 * closes itself.
 */
export async function resolveIssue(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; siteUrn: string; ruleId: string; targetUrl: string },
): Promise<CommandResult> {
  const auditSiteId = await auditSiteFor(tx, input.workspaceId, input.siteUrn);
  if (!auditSiteId) return { ok: false, reason: 'site is not registered with Audit' };

  const closed = await tx.execute<{ id: string }>(sql`
    update audit_issues
       set status = 'fixed', resolved_at = now(), updated_at = now()
     where workspace_id = ${input.workspaceId}
       and audit_site_id = ${auditSiteId}
       and rule_id = ${input.ruleId}
       and status = 'open'
       and evidence->>'targetUrl' = ${input.targetUrl}
    returning id`);

  return { ok: true, value: { resolved: closed.length } };
}

async function auditSiteFor(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  siteUrn: string,
): Promise<string | null> {
  const { tool, type, id } = parseUrn(siteUrn);

  // Accept either the core site URN or Audit's own.
  if (tool === 'audit' && type === 'audit_site') return id;

  const resource = await resolveUrn(tx, workspaceId, siteUrn);
  if (!resource) return null;

  const [row] = await tx.execute<{ id: string }>(sql`
    select id from audit_sites
     where workspace_id = ${workspaceId} and site_id = ${resource.externalId}`);
  return row?.id ?? null;
}
