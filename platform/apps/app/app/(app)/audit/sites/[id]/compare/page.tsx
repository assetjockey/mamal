import Link from 'next/link';
import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { Card, EmptyState, PageHeader, SectionLabel, StatTile, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

type Run = { id: string; score: number; finished_at: string; pages_crawled: number };
type Issue = { rule_id: string; title: string; severity: string; page_url: string | null };

export default async function ComparePage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ from?: string; to?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const { id } = await params;
  const query = await searchParams;
  const ws = session.workspace.id;

  const [site, runs] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{ host: string }>(sql`
        select s.host from audit_sites a join sites s on s.id = a.site_id
         where a.id = ${id} and a.workspace_id = ${ws}`))[0],
      await tx.execute<Run>(sql`
        select id, score, finished_at, pages_crawled from audits
         where audit_site_id = ${id} and status = 'completed'
         order by finished_at desc limit 30`),
    ] as const,
    { db: db() },
  );

  if (!site) notFound();

  if (runs.length < 2) {
    return (
      <>
        <PageHeader title={`${site.host} — Compare`} description="What changed between two audits." />
        <EmptyState
          title="Need two audits"
          description="Comparison needs at least two completed runs. Run another and the diff appears here."
          action={
            <Link href={`/audit/sites/${id}`}>
              <span className="inline-flex h-10 items-center rounded-[4px] border border-[var(--color-lavender-border)] px-4 text-[14px] text-[var(--accent)]">
                Back to overview
              </span>
            </Link>
          }
        />
      </>
    );
  }

  // Newest vs the one before it by default — the comparison people actually want.
  const to = runs.find((r) => r.id === query.to) ?? runs[0]!;
  const from = runs.find((r) => r.id === query.from) ?? runs[1]!;

  const [fromIssues, toIssues] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<Issue>(sql`
        select i.rule_id, r.title, i.severity, i.page_url
          from audit_issues i join audit_rules r on r.id = i.rule_id
         where i.audit_id = ${from.id}`),
      await tx.execute<Issue>(sql`
        select i.rule_id, r.title, i.severity, i.page_url
          from audit_issues i join audit_rules r on r.id = i.rule_id
         where i.audit_id = ${to.id}`),
    ] as const,
    { db: db() },
  );

  // Keyed on rule + URL so "fixed on one page, still broken on another" reads
  // correctly rather than collapsing to a single rule-level verdict.
  const key = (i: Issue) => `${i.rule_id}::${i.page_url ?? ''}`;
  const before = new Map(fromIssues.map((i) => [key(i), i]));
  const after = new Map(toIssues.map((i) => [key(i), i]));

  const fixed = [...before.entries()].filter(([k]) => !after.has(k)).map(([, i]) => i);
  const introduced = [...after.entries()].filter(([k]) => !before.has(k)).map(([, i]) => i);

  const scoreDelta = to.score - from.score;

  return (
    <>
      <PageHeader
        title={`${site.host} — Compare`}
        description={`${new Date(from.finished_at).toLocaleString()} → ${new Date(to.finished_at).toLocaleString()}`}
      />

      <div className="grid grid-cols-2 gap-6 sm:grid-cols-4 [&>*]:min-w-0">
        <StatTile
          label="Score"
          value={to.score}
          hint={scoreDelta === 0 ? 'unchanged' : `${scoreDelta > 0 ? '+' : ''}${scoreDelta} from ${from.score}`}
        />
        <StatTile label="Fixed" value={fixed.length} hint="findings gone" />
        <StatTile label="Introduced" value={introduced.length} hint="new findings" />
        <StatTile
          label="Pages"
          value={to.pages_crawled}
          hint={
            to.pages_crawled === from.pages_crawled
              ? 'same as before'
              : `was ${from.pages_crawled}`
          }
        />
      </div>

      {/*
        A crawl that covered far fewer pages will "fix" issues it simply never
        looked at. Saying so is more useful than a misleading green number.
      */}
      {to.pages_crawled < from.pages_crawled * 0.8 ? (
        <Card className="mt-6 border-[var(--color-status-warn)]">
          <p className="text-[14px] text-[var(--text-secondary)]">
            The newer run crawled {to.pages_crawled} pages against {from.pages_crawled} before, so
            some findings below may be absent because the page was not visited rather than because
            it was fixed.
          </p>
        </Card>
      ) : null}

      <div className="mt-10 grid gap-8 lg:grid-cols-2">
        <IssueList
          label="Fixed"
          issues={fixed}
          empty="Nothing was fixed between these runs."
          tone="ok"
        />
        <IssueList
          label="Introduced"
          issues={introduced}
          empty="Nothing new appeared. That is the good outcome."
          tone="error"
        />
      </div>

      <div className="mt-10">
        <SectionLabel>Compare other runs</SectionLabel>
        <Table>
          <thead>
            <tr>
              <Th>Finished</Th>
              <Th align="right">Score</Th>
              <Th align="right">Pages</Th>
              <Th>Compare</Th>
            </tr>
          </thead>
          <tbody>
            {runs.map((run) => (
              <Tr key={run.id}>
                <Td muted>{new Date(run.finished_at).toLocaleString()}</Td>
                <Td align="right">{run.score}</Td>
                <Td align="right" muted>{run.pages_crawled}</Td>
                <Td>
                  <span className="flex gap-3 text-[13px]">
                    <Link
                      href={`/audit/sites/${id}/compare?from=${run.id}&to=${to.id}`}
                      className="text-[var(--accent)]"
                    >
                      as before
                    </Link>
                    <Link
                      href={`/audit/sites/${id}/compare?from=${from.id}&to=${run.id}`}
                      className="text-[var(--accent)]"
                    >
                      as after
                    </Link>
                  </span>
                </Td>
              </Tr>
            ))}
          </tbody>
        </Table>
      </div>
    </>
  );
}

function IssueList({
  label,
  issues,
  empty,
  tone,
}: {
  label: string;
  issues: Issue[];
  empty: string;
  tone: 'ok' | 'error';
}) {
  // Group so "12 pages fixed" is one line rather than twelve.
  const groups = new Map<string, { title: string; severity: string; count: number }>();
  for (const issue of issues) {
    const existing = groups.get(issue.rule_id);
    groups.set(issue.rule_id, {
      title: issue.title,
      severity: issue.severity,
      count: (existing?.count ?? 0) + 1,
    });
  }

  return (
    <div>
      <SectionLabel>
        {label} ({issues.length})
      </SectionLabel>
      {groups.size === 0 ? (
        <Card>
          <p className="text-[14px] text-[var(--text-secondary)]">{empty}</p>
        </Card>
      ) : (
        <Card padded={false}>
          <ul>
            {[...groups.entries()]
              .sort((a, b) => b[1].count - a[1].count)
              .map(([ruleId, group]) => (
                <li
                  key={ruleId}
                  className="flex items-center justify-between gap-3 border-b border-[var(--border-hairline)] px-4 py-2.5 last:border-b-0"
                >
                  <span className="min-w-0">
                    <span className="block truncate text-[14px]">{group.title}</span>
                    <span className="font-mono text-[11px] text-[var(--text-faint)]">{ruleId}</span>
                  </span>
                  <span className="flex shrink-0 items-center gap-2">
                    <StatusBadge status={tone}>{group.severity}</StatusBadge>
                    <span className="w-8 text-right text-[13px] tabular-nums text-[var(--text-secondary)]">
                      {group.count}
                    </span>
                  </span>
                </li>
              ))}
          </ul>
        </Card>
      )}
    </div>
  );
}
