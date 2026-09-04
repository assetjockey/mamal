'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { addSite, startAudit, AuditNotAllowed } from '@mamal/tool-audit';
import { enqueue } from '@mamal/jobs';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/**
 * Queues an audit.
 *
 * The entitlement check runs inline so the user learns immediately if they are
 * over quota, rather than by polling a job that was always going to fail. The
 * crawl itself goes to the `audit.crawl` queue in bounded slices, so a large
 * site never blocks a request and a killed worker resumes.
 */
export async function queueAudit(auditSiteId: string) {
  const session = await getSession();
  if (!session) redirect('/sign-in');

  try {
    const started = await withWorkspace(
      session.workspace.id,
      async (tx) => {
        const [project] = await tx.execute<{ id: string }>(sql`
          select id from projects where workspace_id = ${session.workspace.id}
           order by is_default desc limit 1`);
        return startAudit(tx, {
          workspaceId: session.workspace.id,
          projectId: project!.id,
          auditSiteId,
          trigger: 'manual',
        });
      },
      { db: db() },
    );

    await enqueue('audit.crawl', 'slice', {
      auditId: started.auditId,
      workspaceId: session.workspace.id,
      slice: 0,
    });

    revalidatePath('/audit');
    return { ok: true as const, auditId: started.auditId, maxPages: started.maxPages };
  } catch (err) {
    if (err instanceof AuditNotAllowed) {
      return { ok: false as const, error: err.message, reason: err.reason };
    }
    return {
      ok: false as const,
      error: err instanceof Error ? err.message : 'Could not start the audit.',
      reason: 'start_failed',
    };
  }
}

export type AuditProgress = {
  status: string;
  phase: string;
  pagesCrawled: number;
  pagesTotal: number;
  score: number | null;
  errorDetail: string | null;
};

/** Polled by the UI while a crawl runs. */
export async function auditProgress(auditId: string): Promise<AuditProgress | null> {
  const session = await getSession();
  if (!session) return null;

  const [row] = await withWorkspace(
    session.workspace.id,
    (tx) => tx.execute<{
      status: string; phase: string; pages_crawled: number;
      pages_total: number; score: number | null; error_detail: string | null;
    }>(sql`
      select status, phase, pages_crawled, pages_total, score, error_detail
        from audits where id = ${auditId} and workspace_id = ${session.workspace.id}`),
    { db: db() },
  );
  if (!row) return null;

  return {
    status: row.status,
    phase: row.phase,
    pagesCrawled: Number(row.pages_crawled),
    pagesTotal: Number(row.pages_total),
    score: row.score === null ? null : Number(row.score),
    errorDetail: row.error_detail,
  };
}

/** Cancel: the next slice sees the status and finalizes what it has. */
export async function cancelAudit(auditId: string) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  await withWorkspace(
    session.workspace.id,
    (tx) => tx.execute(sql`
      update audits set status = 'cancelled'
       where id = ${auditId} and workspace_id = ${session.workspace.id}
         and status in ('queued', 'running')`),
    { db: db() },
  );
  revalidatePath('/audit');
}

/** Register an existing site with Audit. */
export async function enableAudit(siteId: string, host: string) {
  const session = await getSession();
  if (!session) redirect('/sign-in');

  await withWorkspace(
    session.workspace.id,
    async (tx) => {
      const [project] = await tx.execute<{ id: string }>(sql`
        select id from projects where workspace_id = ${session.workspace.id}
         order by is_default desc limit 1`);
      return addSite(tx, {
        workspaceId: session.workspace.id,
        projectId: project!.id,
        siteId,
        host,
      });
    },
    { db: db() },
  );
  revalidatePath('/audit');
}

export async function setIssueStatus(issueId: string, status: 'open' | 'fixed' | 'ignored') {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  await withWorkspace(
    session.workspace.id,
    (tx) => tx.execute(sql`
      update audit_issues
         set status = ${status},
             resolved_at = ${status === 'fixed' ? sql`now()` : sql`null`},
             updated_at = now()
       where id = ${issueId} and workspace_id = ${session.workspace.id}`),
    { db: db() },
  );
  revalidatePath('/audit/issues');
}
