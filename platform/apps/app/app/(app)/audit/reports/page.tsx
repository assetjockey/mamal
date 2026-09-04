import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { Card, EmptyState, PageHeader, SectionLabel, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

export default async function ReportsPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const runs = await withWorkspace(
    ws,
    (tx) => tx.execute<{
      id: string; host: string; score: number; finished_at: string;
      pages_crawled: number; issue_count: number;
    }>(sql`
      select a.id, s.host, a.score, a.finished_at, a.pages_crawled,
             (select count(*)::int from audit_issues i where i.audit_id = a.id) as issue_count
        from audits a
        join audit_sites asite on asite.id = a.audit_site_id
        join sites s on s.id = asite.site_id
       where a.workspace_id = ${ws} and a.status = 'completed'
       order by a.finished_at desc limit 50`),
    { db: db() },
  );

  if (runs.length === 0) {
    return (
      <>
        <PageHeader title="Reports" description="Export any completed audit." />
        <EmptyState title="Nothing to export yet" description="Run an audit first." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Reports"
        description="Export any completed audit. Your data is exportable on every plan — what a paid tier buys is branded PDF reporting, not access to your own numbers."
      />

      <SectionLabel>Completed audits</SectionLabel>
      <Table>
        <thead>
          <tr>
            <Th>Website</Th>
            <Th align="right">Score</Th>
            <Th align="right">Pages</Th>
            <Th align="right">Findings</Th>
            <Th>Finished</Th>
            <Th>Export</Th>
          </tr>
        </thead>
        <tbody>
          {runs.map((run) => (
            <Tr key={run.id}>
              <Td>{run.host}</Td>
              <Td align="right">{run.score}</Td>
              <Td align="right" muted>{run.pages_crawled}</Td>
              <Td align="right" muted>{run.issue_count}</Td>
              <Td muted>{new Date(run.finished_at).toLocaleString()}</Td>
              <Td>
                <span className="flex gap-3">
                  <a
                    href={`/api/audit/export/${run.id}?format=csv`}
                    className="text-[13px] text-[var(--accent)] hover:text-[var(--accent-hover)]"
                  >
                    CSV
                  </a>
                  <a
                    href={`/api/audit/export/${run.id}?format=json`}
                    className="text-[13px] text-[var(--accent)] hover:text-[var(--accent-hover)]"
                  >
                    JSON
                  </a>
                </span>
              </Td>
            </Tr>
          ))}
        </tbody>
      </Table>

      <Card className="mt-8">
        <h2 className="text-[20px]">What is in each format</h2>
        <p className="mt-2 max-w-2xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
          <strong className="font-normal text-[var(--text-primary)]">CSV</strong> is one row per
          finding — severity, category, rule, URL and the evidence — ready to sort in a spreadsheet
          or hand to whoever is doing the fixing.{' '}
          <strong className="font-normal text-[var(--text-primary)]">JSON</strong> carries the full
          crawl as well: every page with the facts each rule was judged on.
        </p>
      </Card>
    </>
  );
}
