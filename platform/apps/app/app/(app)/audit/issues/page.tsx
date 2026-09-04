import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { Card, EmptyState, PageHeader, SectionLabel, StatusBadge } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { IssueGroup } from './client';

export const dynamic = 'force-dynamic';

type IssueRow = {
  id: string;
  rule_id: string;
  severity: 'critical' | 'warning' | 'info';
  page_url: string | null;
  evidence: Record<string, unknown>;
  status: string;
  title: string;
  why: string;
  how_to_fix: string;
  category: string;
  is_ai_relevant: boolean;
};

export default async function IssuesPage({
  searchParams,
}: {
  searchParams: Promise<{ site?: string; severity?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const params = await searchParams;
  const ws = session.workspace.id;

  const issues = await withWorkspace(
    ws,
    (tx) => tx.execute<IssueRow>(sql`
      select i.id, i.rule_id, i.severity, i.page_url, i.evidence, i.status,
             r.title, r.why, r.how_to_fix, r.category, r.is_ai_relevant
        from audit_issues i
        join audit_rules r on r.id = i.rule_id
       where i.workspace_id = ${ws}
         and i.status = 'open'
         and i.audit_id = (
           select id from audits
            where workspace_id = ${ws} and status = 'completed'
              ${params.site ? sql`and audit_site_id = ${params.site}` : sql``}
            order by created_at desc limit 1)
         ${params.severity ? sql`and i.severity = ${params.severity}` : sql``}
       order by case i.severity when 'critical' then 0 when 'warning' then 1 else 2 end,
                i.rule_id`),
    { db: db() },
  );

  if (issues.length === 0) {
    return (
      <>
        <PageHeader title="Issues" description="Findings from your most recent audit." />
        <EmptyState
          title="Nothing open"
          description="Either the last audit found nothing, or no audit has run yet. Run one from the Websites screen."
        />
      </>
    );
  }

  // Group by rule: 400 findings of one kind is one problem, not 400.
  const groups = new Map<string, IssueRow[]>();
  for (const issue of issues) {
    groups.set(issue.rule_id, [...(groups.get(issue.rule_id) ?? []), issue]);
  }

  const counts = {
    critical: issues.filter((i) => i.severity === 'critical').length,
    warning: issues.filter((i) => i.severity === 'warning').length,
    info: issues.filter((i) => i.severity === 'info').length,
  };
  const aiCount = issues.filter((i) => i.is_ai_relevant).length;

  return (
    <>
      <PageHeader
        title="Issues"
        description={`${groups.size} distinct problems across ${issues.length} findings. Ordered by severity, then by how many pages each affects.`}
      />

      <div className="mb-8 flex flex-wrap gap-2">
        <StatusBadge status={counts.critical > 0 ? 'error' : 'neutral'}>
          {counts.critical} critical
        </StatusBadge>
        <StatusBadge status={counts.warning > 0 ? 'warn' : 'neutral'}>
          {counts.warning} warnings
        </StatusBadge>
        <StatusBadge status="neutral">{counts.info} info</StatusBadge>
        {aiCount > 0 ? (
          <StatusBadge status="info">{aiCount} affect AI visibility</StatusBadge>
        ) : null}
      </div>

      <SectionLabel>What to fix, in order</SectionLabel>
      <div className="space-y-4">
        {[...groups.entries()].map(([ruleId, group]) => (
          <IssueGroup key={ruleId} issues={group} />
        ))}
      </div>

      <Card className="mt-10">
        <h2 className="text-[20px]">Where this guidance comes from</h2>
        <p className="mt-2 max-w-2xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
          Every rule ships its own explanation and fix, written once and stored in the rule
          catalogue. That is deliberate: with AI switched off — or on a lifetime plan, which
          excludes AI entirely — the guidance you see here is unchanged. AI adds a tailored brief
          on top of this, never instead of it.
        </p>
      </Card>
    </>
  );
}
