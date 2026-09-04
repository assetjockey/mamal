import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

type Run = {
  id: string; audit_site_id: string; host: string; status: string; phase: string;
  trigger: string; score: number | null; pages_crawled: number; pages_blocked: number;
  critical_count: number; started_at: string | null; finished_at: string | null;
  error_detail: string | null;
};

export default async function RunsPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const runs = await withWorkspace(
    ws,
    (tx) => tx.execute<Run>(sql`
      select a.id, a.audit_site_id, s.host, a.status, a.phase, a.trigger, a.score,
             a.pages_crawled, a.pages_blocked, a.critical_count,
             a.started_at, a.finished_at, a.error_detail
        from audits a
        join audit_sites asite on asite.id = a.audit_site_id
        join sites s on s.id = asite.site_id
       where a.workspace_id = ${ws}
       order by a.created_at desc limit 100`),
    { db: db() },
  );

  if (runs.length === 0) {
    return (
      <>
        <PageHeader title="Audits" description="Every crawl, with what it found and how long it took." />
        <EmptyState title="No audits yet" description="Run one from the Websites screen." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Audits"
        description="Every crawl this workspace has run. Crawls happen in bounded slices on a queue, so a run in progress shows real page counts rather than a spinner."
      />
      <Table>
        <thead>
          <tr>
            <Th>Website</Th>
            <Th>Status</Th>
            <Th>Trigger</Th>
            <Th align="right">Score</Th>
            <Th align="right">Pages</Th>
            <Th align="right">Duration</Th>
            <Th>When</Th>
          </tr>
        </thead>
        <tbody>
          {runs.map((run) => (
            <Tr key={run.id}>
              <Td>
                <Link href={`/audit/sites/${run.audit_site_id}`} className="hover:text-[var(--accent)]">
                  {run.host}
                </Link>
              </Td>
              <Td>
                <StatusBadge status={tone(run.status)}>
                  {run.status === 'running' ? run.phase : run.status}
                </StatusBadge>
                {run.error_detail ? (
                  <span className="ml-2 text-[12px] text-[var(--color-status-error)]">
                    {run.error_detail.slice(0, 40)}
                  </span>
                ) : null}
              </Td>
              <Td muted>{run.trigger}</Td>
              <Td align="right">{run.score ?? '—'}</Td>
              <Td align="right" muted>
                {run.pages_crawled}
                {run.pages_blocked > 0 ? (
                  <span className="ml-1 text-[var(--color-status-warn)]" title="blocked by a bot wall">
                    +{run.pages_blocked}
                  </span>
                ) : null}
              </Td>
              <Td align="right" muted>{duration(run)}</Td>
              <Td muted>{run.started_at ? new Date(run.started_at).toLocaleString() : '—'}</Td>
            </Tr>
          ))}
        </tbody>
      </Table>
    </>
  );
}

function tone(status: string) {
  if (status === 'completed') return 'ok' as const;
  if (status === 'failed') return 'error' as const;
  if (status === 'running' || status === 'queued') return 'info' as const;
  return 'neutral' as const;
}

function duration(run: Run): string {
  if (!run.started_at || !run.finished_at) return '—';
  const s = Math.round(
    (new Date(run.finished_at).getTime() - new Date(run.started_at).getTime()) / 1000,
  );
  return s < 60 ? `${s}s` : `${Math.floor(s / 60)}m ${s % 60}s`;
}
