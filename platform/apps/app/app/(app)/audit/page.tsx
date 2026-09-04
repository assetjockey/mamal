import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import {
  Button,
  Card,
  EmptyState,
  PageHeader,
  SectionLabel,
  StatusBadge,
  UpgradeGate,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { AuditActions, ScoreDial } from './client';

export const dynamic = 'force-dynamic';

type Row = {
  audit_site_id: string | null;
  site_id: string;
  host: string;
  root_url: string;
  score: number | null;
  previous_score: number | null;
  grade: string | null;
  critical_count: number;
  warning_count: number;
  info_count: number;
  last_audit_at: string | null;
  pages_crawled: number | null;
  running_audit_id: string | null;
};

export default async function AuditPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  // The brief's rule: every limit is shown before it is hit, not after. Ask for
  // one page of headroom rather than a whole crawl, so this reports the true
  // remaining balance instead of denying on the size of a hypothetical run.
  const crawl = await withWorkspace(
    ws,
    async (tx) => {
      const ctx = await loadContext(tx, ws, 'audit.crawl_pages');
      // A missing feature row is a seed gap, not a user state. startAudit is
      // the real gate, so a registry hole must not blank this page.
      return ctx ? resolve(ctx, 1) : null;
    },
    { db: db() },
  );
  const cap = crawl ? (crawl.quota ?? crawl.limit ?? null) : null;
  const used = crawl?.used ?? 0;

  // The site limit, resolved against the real count so it can be shown before
  // it is reached rather than discovered by an error on the add form.
  const siteLimit = await withWorkspace(
    ws,
    async (tx) => {
      const ctx = await loadContext(tx, ws, 'audit.sites');
      if (!ctx) return null;
      const [counted] = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from audit_sites where workspace_id = ${ws}`);
      const n = counted?.count ?? 0;
      const d = resolve({ ...ctx, used: n }, 1);
      const max = d.limit ?? d.quota ?? null;
      return { used: n, max, allowed: d.allowed };
    },
    { db: db() },
  );

  const sites = await withWorkspace(
    ws,
    (tx) => tx.execute<Row>(sql`
      select a.id as audit_site_id, s.id as site_id, s.host, s.root_url,
             a.score, a.previous_score, a.grade,
             a.critical_count, a.warning_count, a.info_count, a.last_audit_at,
             (select pages_crawled from audits
               where audit_site_id = a.id and status = 'completed'
               order by created_at desc limit 1) as pages_crawled,
             -- Reloading the page must not lose sight of a crawl in flight.
             (select id from audits
               where audit_site_id = a.id and status in ('queued', 'running')
               order by created_at desc limit 1) as running_audit_id
        from sites s
        left join audit_sites a on a.site_id = s.id
       where s.workspace_id = ${ws} and s.deleted_at is null
       order by s.host`),
    { db: db() },
  );

  if (sites.length === 0) {
    return (
      <>
        <PageHeader
          title="Audit"
          description="Find website issues impacting your search and AI visibility — and fix them fast with step-by-step guidance."
        />
        <EmptyState
          title="No websites yet"
          description="Add one address and Audit, Monitor and Track all point at it. The first audit runs on the free tier."
          action={
            <Link href="/welcome">
              <Button>Add a website</Button>
            </Link>
          }
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Audit"
        description="72 checks across crawlability, on-page, links, performance, security and AI visibility. Every finding carries the evidence and the fix."
      />

      {crawl && !crawl.allowed ? (
        <div className="mb-8">
          <UpgradeGate
            feature="Crawl pages"
            // The resolver already decides which of the six refusals applies and
            // writes the matching sentence; restating it here would let the two
            // drift. This only adds what the resolver cannot know: what happens
            // to work already done.
            reason={
              crawl.reason === 'quota_exhausted' || crawl.reason === 'limit_reached'
                ? `${crawl.message} Existing audits stay readable; new crawls resume next period or on a larger plan.`
                : crawl.reason === 'insufficient_credits'
                  ? `${crawl.message} Existing audits stay readable.`
                  : crawl.message
            }
            used={used}
            limit={cap ?? undefined}
            price={
              crawl.reason === 'insufficient_credits'
                ? undefined
                : 'Starter includes 500 pages a month at $12.'
            }
            action={
              <Link
                href={crawl.reason === 'insufficient_credits' ? '/settings/plans' : '/settings/plans'}
              >
                <Button>
                  {crawl.reason === 'insufficient_credits' ? 'Buy credits' : 'Compare plans'}
                </Button>
              </Link>
            }
          />
        </div>
      ) : cap !== null && cap > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {siteLimit && siteLimit.max !== null && siteLimit.max > 0 ? (
            <>
              {siteLimit.used} of {siteLimit.max} website{siteLimit.max === 1 ? '' : 's'} ·{' '}
            </>
          ) : null}
          {used.toLocaleString()} of {cap.toLocaleString()} crawl pages used this period.
          {used > cap ? (
            // Silence here would be the worst option: past the included pages
            // the meter is running, and the user is entitled to know that
            // without opening the billing screen.
            <span className="text-[var(--color-status-warn)]">
              {' '}
              Past your included pages &mdash; further pages cost{' '}
              {crawl!.cost || 1} credit{(crawl!.cost || 1) === 1 ? '' : 's'} each.
            </span>
          ) : null}
        </p>
      ) : null}

      <SectionLabel>Websites</SectionLabel>
      <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
        {sites.map((site) => {
          const delta =
            site.score !== null && site.previous_score !== null
              ? site.score - site.previous_score
              : null;
          return (
            <Card key={site.site_id}>
              <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                  <h3 className="truncate text-[20px] text-[var(--text-primary)]">
                    {site.audit_site_id ? (
                      <Link
                        href={`/audit/sites/${site.audit_site_id}`}
                        className="hover:text-[var(--accent)]"
                      >
                        {site.host}
                      </Link>
                    ) : (
                      site.host
                    )}
                  </h3>
                  <p className="mt-0.5 truncate text-[13px] text-[var(--text-faint)]">
                    {site.root_url}
                  </p>
                </div>
                <ScoreDial score={site.score} grade={site.grade} delta={delta} />
              </div>

              {site.score !== null ? (
                <div className="mt-5 flex flex-wrap items-center gap-2">
                  <StatusBadge status={site.critical_count > 0 ? 'error' : 'ok'}>
                    {site.critical_count} critical
                  </StatusBadge>
                  <StatusBadge status={site.warning_count > 0 ? 'warn' : 'neutral'}>
                    {site.warning_count} warnings
                  </StatusBadge>
                  <StatusBadge status="neutral">{site.info_count} info</StatusBadge>
                  {site.pages_crawled ? (
                    <span className="text-[12px] text-[var(--text-faint)]">
                      {site.pages_crawled} pages
                    </span>
                  ) : null}
                </div>
              ) : (
                <p className="mt-5 text-[14px] text-[var(--text-secondary)]">
                  Not audited yet.
                </p>
              )}

              <div className="mt-5 flex items-center justify-between gap-3 border-t border-[var(--border-hairline)] pt-4">
                <span className="text-[12px] text-[var(--text-faint)]">
                  {site.last_audit_at
                    ? `Last run ${new Date(site.last_audit_at).toLocaleString()}`
                    : 'Never run'}
                </span>
                <AuditActions
                  auditSiteId={site.audit_site_id}
                  siteId={site.site_id}
                  host={site.host}
                  runningAuditId={site.running_audit_id}
                />
              </div>
            </Card>
          );
        })}
      </div>
    </>
  );
}
