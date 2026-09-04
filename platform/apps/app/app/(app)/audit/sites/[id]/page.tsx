import Link from 'next/link';
import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { Card, Divider, PageHeader, SectionLabel, StatTile, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { ScoreTrend } from './trend';
import { AuditSummary } from './summary';

export const dynamic = 'force-dynamic';

type Site = {
  id: string; host: string; root_url: string;
  score: number | null; previous_score: number | null; grade: string | null;
  tests_total: number; tests_passed: number;
  critical_count: number; warning_count: number; info_count: number;
  schedule: string; last_audit_at: string | null; next_audit_at: string | null;
};

export default async function SiteOverview({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const { id } = await params;
  const ws = session.workspace.id;

  const [site, snapshots, topIssues, lastRun, categories] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<Site>(sql`
        select a.id, s.host, s.root_url, a.score, a.previous_score, a.grade,
               a.tests_total, a.tests_passed, a.critical_count, a.warning_count, a.info_count,
               a.schedule, a.last_audit_at, a.next_audit_at
          from audit_sites a join sites s on s.id = a.site_id
         where a.id = ${id} and a.workspace_id = ${ws}`))[0],

      await tx.execute<{ captured_at: string; score: number; critical_count: number }>(sql`
        select captured_at, score, critical_count from audit_snapshots
         where audit_site_id = ${id} order by captured_at asc limit 90`),

      await tx.execute<{ rule_id: string; title: string; severity: string; n: number; is_ai_relevant: boolean }>(sql`
        select i.rule_id, r.title, i.severity, count(*)::int as n, r.is_ai_relevant
          from audit_issues i join audit_rules r on r.id = i.rule_id
         where i.audit_site_id = ${id} and i.status = 'open'
           and i.audit_id = (select id from audits where audit_site_id = ${id}
                              and status = 'completed' order by created_at desc limit 1)
         group by 1, 2, 3, 5
         order by case i.severity when 'critical' then 0 when 'warning' then 1 else 2 end,
                  count(*) desc
         limit 10`),

      (await tx.execute<{
        id: string; pages_crawled: number; pages_blocked: number; finished_at: string | null;
        started_at: string | null; trigger: string;
      }>(sql`
        select id, pages_crawled, pages_blocked, finished_at, started_at, trigger
          from audits where audit_site_id = ${id} and status = 'completed'
         order by created_at desc limit 1`))[0],

      await tx.execute<{ category: string; failed: number; total: number }>(sql`
        select r.category,
               count(*) filter (where exists (
                 select 1 from audit_issues i
                  where i.rule_id = r.id and i.audit_site_id = ${id} and i.status = 'open'
                    and i.audit_id = (select id from audits where audit_site_id = ${id}
                                       and status = 'completed' order by created_at desc limit 1)
               ))::int as failed,
               count(*)::int as total
          from audit_rules r where r.is_enabled group by r.category order by r.category`),
    ] as const,
    { db: db() },
  );

  if (!site) notFound();

  const delta =
    site.score !== null && site.previous_score !== null ? site.score - site.previous_score : null;
  const duration =
    lastRun?.started_at && lastRun?.finished_at
      ? Math.round((new Date(lastRun.finished_at).getTime() - new Date(lastRun.started_at).getTime()) / 1000)
      : null;

  return (
    <>
      <PageHeader
        title={site.host}
        description={site.root_url}
        action={
          <div className="flex flex-wrap gap-2">
            <Link href={`/audit/sites/${id}/compare`}>
              <span className="inline-flex h-10 items-center rounded-[4px] border border-[var(--color-lilac-border)] px-4 text-[14px] text-[var(--accent)]">
                Compare
              </span>
            </Link>
            <Link href={`/audit/sites/${id}/pages`}>
              <span className="inline-flex h-10 items-center rounded-[4px] border border-[var(--color-lilac-border)] px-4 text-[14px] text-[var(--accent)]">
                Pages
              </span>
            </Link>
            <Link href={`/audit/sites/${id}/settings`}>
              <span className="inline-flex h-10 items-center rounded-[4px] border border-[var(--color-lavender-border)] px-4 text-[14px] text-[var(--accent)]">
                Settings
              </span>
            </Link>
          </div>
        }
      />

      <div className="grid grid-cols-2 gap-6 sm:grid-cols-4 [&>*]:min-w-0">
        <StatTile
          label="Score"
          value={site.score ?? '—'}
          hint={site.grade ? `Grade ${site.grade}${delta ? ` · ${delta > 0 ? '+' : ''}${delta}` : ''}` : 'Not audited'}
        />
        <StatTile
          label="Checks passed"
          value={site.tests_total > 0 ? `${site.tests_passed}/${site.tests_total}` : '—'}
          hint="weighted by severity"
        />
        <StatTile
          label="Pages crawled"
          value={lastRun?.pages_crawled ?? '—'}
          hint={
            lastRun && lastRun.pages_blocked > 0
              ? `${lastRun.pages_blocked} blocked by a bot wall`
              : duration !== null ? `in ${duration}s` : undefined
          }
        />
        <StatTile
          label="Open issues"
          value={site.critical_count + site.warning_count + site.info_count}
          hint={`${site.critical_count} critical`}
        />
      </div>

      <Divider />

      {lastRun ? <AuditSummary auditId={lastRun.id} /> : null}

      <div className="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] [&>*]:min-w-0">
        <div>
          <SectionLabel>Score over time</SectionLabel>
          {snapshots.length > 1 ? (
            <ScoreTrend points={snapshots.map((s) => ({ at: s.captured_at, score: Number(s.score) }))} />
          ) : (
            <Card>
              <p className="text-[14px] text-[var(--text-secondary)]">
                One audit so far. The trend appears after the second run — and Monitor incidents and
                Market publishes will annotate it, so a dip explains itself.
              </p>
            </Card>
          )}
        </div>

        <div>
          <SectionLabel>By category</SectionLabel>
          <Card padded={false}>
            <ul>
              {categories.map((c) => {
                const passed = c.total - c.failed;
                const pct = c.total === 0 ? 100 : Math.round((passed / c.total) * 100);
                return (
                  <li
                    key={c.category}
                    className="flex items-center justify-between gap-3 border-b border-[var(--border-hairline)] px-4 py-2.5 text-[14px] last:border-b-0"
                  >
                    <span className="capitalize">{c.category.replace('-', ' ')}</span>
                    <span className="flex items-center gap-3">
                      <span className="hidden h-1 w-20 overflow-hidden rounded-full bg-[var(--surface-band)] sm:block">
                        <span
                          className="block h-full rounded-full"
                          style={{
                            width: `${pct}%`,
                            background: pct === 100 ? 'var(--color-status-ok)' : 'var(--accent)',
                          }}
                        />
                      </span>
                      <span className="w-16 text-right text-[13px] tabular-nums text-[var(--text-secondary)]">
                        {passed}/{c.total}
                      </span>
                    </span>
                  </li>
                );
              })}
            </ul>
          </Card>
        </div>
      </div>

      <Divider />

      <SectionLabel>What to fix first</SectionLabel>
      {topIssues.length === 0 ? (
        <Card>
          <p className="text-[14px] text-[var(--text-secondary)]">
            Nothing open. Run an audit to check again.
          </p>
        </Card>
      ) : (
        <Table>
          <thead>
            <tr>
              <Th>Issue</Th>
              <Th>Severity</Th>
              <Th align="right">Pages</Th>
            </tr>
          </thead>
          <tbody>
            {topIssues.map((issue) => (
              <Tr key={issue.rule_id}>
                <Td>
                  <Link href={`/audit/issues?site=${id}`} className="hover:text-[var(--accent)]">
                    {issue.title}
                  </Link>
                  {issue.is_ai_relevant ? (
                    <span className="ml-2 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                      AI
                    </span>
                  ) : null}
                </Td>
                <Td>
                  <StatusBadge
                    status={
                      issue.severity === 'critical' ? 'error' : issue.severity === 'warning' ? 'warn' : 'neutral'
                    }
                  >
                    {issue.severity}
                  </StatusBadge>
                </Td>
                <Td align="right" muted>{issue.n}</Td>
              </Tr>
            ))}
          </tbody>
        </Table>
      )}

      <p className="mt-6 text-[12px] text-[var(--text-faint)]">
        {site.last_audit_at ? `Last audited ${new Date(site.last_audit_at).toLocaleString()}` : 'Never audited'}
        {site.schedule !== 'manual' && site.next_audit_at
          ? ` · next ${new Date(site.next_audit_at).toLocaleString()} (${site.schedule})`
          : ' · no schedule'}
      </p>
    </>
  );
}
