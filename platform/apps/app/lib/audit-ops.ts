import { sql } from 'drizzle-orm';
import { z } from 'zod';
import { enqueue } from '@mamal/jobs';
import { startAudit, AuditNotAllowed } from '@mamal/tool-audit';
import { defineOp, limit, cursor, MAX_LIMIT, type Op } from '@/lib/ops';

/**
 * Audit's operations, defined once.
 *
 * REST (`/api/v1/audit/*`) and MCP (`/api/mcp`) are two encodings of the same
 * capability, and the moment they own separate SQL they drift — one gains a
 * filter, the other silently does not. So the query lives here and each
 * transport only translates.
 *
 * Every operation declares the scope it needs, so the two transports cannot
 * disagree about authorisation either.
 */

const listSitesInput = z.object({ limit, cursor });
export const listSites: Op = defineOp({
  name: 'audit_list_sites',
  scope: 'audit:sites:read',
  description:
    'List the websites Audit is watching, with the latest score, grade and issue counts.',
  readOnly: true,
  input: listSitesInput,
  run: async (tx, workspaceId, { limit: n, cursor: c }) =>
    tx.execute(sql`
      select a.id, s.host, s.root_url, a.score, a.grade,
             a.critical_count, a.warning_count, a.info_count,
             a.schedule, a.last_audit_at
        from audit_sites a
        join sites s on s.id = a.site_id
       where a.workspace_id = ${workspaceId} and s.deleted_at is null
         ${c ? sql`and a.id > ${c}` : sql``}
       order by a.id
       limit ${n + 1}`),
});

const listAuditsInput = z.object({
  site_id: z.uuid().describe('An audit site id, from audit_list_sites.'),
  limit,
  cursor,
});
export const listAudits: Op = defineOp({
  name: 'audit_list_audits',
  scope: 'audit:audits:read',
  description: 'List audit runs for one site, newest first.',
  readOnly: true,
  input: listAuditsInput,
  run: async (tx, workspaceId, { site_id, limit: n, cursor: c }) =>
    tx.execute(sql`
      select id, status, phase, score, pages_crawled, started_at, finished_at
        from audits
       where workspace_id = ${workspaceId} and audit_site_id = ${site_id}
         ${c ? sql`and id > ${c}` : sql``}
       order by id desc
       limit ${n + 1}`),
});

const getAuditInput = z.object({ audit_id: z.uuid() });
export const getAudit: Op = defineOp({
  name: 'audit_get_audit',
  scope: 'audit:audits:read',
  description: 'One audit run, including live progress while it is still crawling.',
  readOnly: true,
  input: getAuditInput,
  run: async (tx, workspaceId, { audit_id }) => {
    const [row] = await tx.execute(sql`
      select a.id, a.status, a.phase, a.score, a.pages_crawled, a.pages_total,
             a.pages_blocked, a.critical_count, a.warning_count, a.info_count,
             a.error_code, a.started_at, a.finished_at, s.host
        from audits a
        join audit_sites asite on asite.id = a.audit_site_id
        join sites s on s.id = asite.site_id
       where a.id = ${audit_id} and a.workspace_id = ${workspaceId}`);
    return row ?? null;
  },
});

const listIssuesInput = z.object({
  status: z.enum(['open', 'fixed', 'ignored']).default('open'),
  severity: z.enum(['critical', 'warning', 'info']).optional(),
  rule_id: z.string().max(64).optional(),
  audit_id: z.uuid().optional(),
  limit,
  cursor,
});
export const listIssues: Op = defineOp({
  name: 'audit_list_issues',
  scope: 'audit:issues:read',
  description:
    'List findings. Filter by status, severity, rule or audit. Each carries its evidence.',
  readOnly: true,
  input: listIssuesInput,
  run: async (tx, workspaceId, f) =>
    // The enums are validated by zod above, so these are constrained values —
    // but they are still passed as parameters, never interpolated.
    tx.execute(sql`
      select i.id, i.rule_id, i.severity, i.status, p.url as page_url, i.evidence, i.audit_id
        from audit_issues i
        left join audit_pages p on p.id = i.page_id
       where i.workspace_id = ${workspaceId}
         and i.status = ${f.status}
         ${f.severity ? sql`and i.severity = ${f.severity}` : sql``}
         ${f.rule_id ? sql`and i.rule_id = ${f.rule_id}` : sql``}
         ${f.audit_id ? sql`and i.audit_id = ${f.audit_id}` : sql``}
         ${f.cursor ? sql`and i.id > ${f.cursor}` : sql``}
       order by i.id
       limit ${f.limit + 1}`),
});

const runSiteInput = z.object({
  site_id: z.uuid().describe('An audit site id, from audit_list_sites.'),
});
export const runSite: Op = defineOp({
  name: 'audit_run_site',
  scope: 'audit:audits:write',
  /*
   * A safe write: it starts work the user already pays for and can cancel, and
   * it destroys nothing. Deleting a site or resolving a finding is *not* here —
   * an agent should not be able to mark a customer's problems fixed.
   */
  description:
    'Queue a crawl for a site. Returns immediately with an audit id to poll; it does not wait for the crawl.',
  readOnly: false,
  input: runSiteInput,
  run: async (tx, workspaceId, { site_id }) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${workspaceId}
       order by is_default desc limit 1`);
    const started = await startAudit(tx, {
      workspaceId,
      projectId: project!.id,
      auditSiteId: site_id,
      trigger: 'api',
    });
    await enqueue('audit.crawl', 'slice', {
      auditId: started.auditId,
      workspaceId,
      slice: 0,
    });
    return {
      id: started.auditId,
      status: 'queued',
      max_pages: started.maxPages,
      start_url: started.startUrl,
    };
  },
});

export const AUDIT_OPS = [listSites, listAudits, getAudit, listIssues, runSite] as const;

export { AuditNotAllowed, MAX_LIMIT };
