import Link from 'next/link';
import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

type PageRow = {
  id: string; url: string; status_code: number | null; fetch_class: string;
  title: string | null; meta_description: string | null;
  word_count: number; images_missing_alt: number; images_total: number;
  links_internal: number; depth: number; is_indexable: boolean;
  in_sitemap: boolean; response_ms: number | null; bytes: number | null;
  issue_count: number;
};

const TITLE_MAX = 60;
const DESC_MAX = 158;

export default async function PagesTable({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ filter?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const { id } = await params;
  const { filter } = await searchParams;
  const ws = session.workspace.id;

  const [site, pages] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{ host: string }>(sql`
        select s.host from audit_sites a join sites s on s.id = a.site_id
         where a.id = ${id} and a.workspace_id = ${ws}`))[0],

      await tx.execute<PageRow>(sql`
        with latest as (
          select id from audits where audit_site_id = ${id} and status = 'completed'
           order by created_at desc limit 1
        )
        select p.id, p.url, p.status_code, p.fetch_class, p.title, p.meta_description,
               p.word_count, p.images_missing_alt, p.images_total, p.links_internal,
               p.depth, p.is_indexable, p.in_sitemap, p.response_ms, p.bytes,
               (select count(*)::int from audit_issues i where i.page_id = p.id
                  or (i.page_url = p.url and i.audit_id = p.audit_id)) as issue_count
          from audit_pages p
         where p.audit_id = (select id from latest)
           ${filter === 'problems' ? sql`and (p.status_code <> 200 or p.title is null or p.meta_description is null)` : sql``}
           ${filter === 'noindex' ? sql`and not p.is_indexable` : sql``}
           ${filter === 'orphan' ? sql`and p.in_sitemap and p.depth > 0` : sql``}
         order by p.depth, p.url
         limit 500`),
    ] as const,
    { db: db() },
  );

  if (!site) notFound();

  if (pages.length === 0) {
    return (
      <>
        <PageHeader title={`${site.host} — Pages`} description="Every URL from the most recent crawl." />
        <EmptyState
          title="No crawl data"
          description="Run an audit and every page it visits will appear here with the facts each rule used."
        />
      </>
    );
  }

  const filters = [
    { key: undefined, label: 'All' },
    { key: 'problems', label: 'Problems' },
    { key: 'noindex', label: 'Not indexable' },
  ];

  return (
    <>
      <PageHeader
        title={`${site.host} — Pages`}
        description={`${pages.length} URLs from the most recent crawl, with the facts each rule was judged on. Length bars turn amber past the limit search engines truncate at.`}
      />

      <div className="mb-5 flex flex-wrap gap-2">
        {filters.map((f) => {
          const active = filter === f.key || (!filter && !f.key);
          return (
            <Link
              key={f.label}
              href={f.key ? `/audit/sites/${id}/pages?filter=${f.key}` : `/audit/sites/${id}/pages`}
              className={
                'rounded-[4px] border px-3 py-1.5 text-[13px] transition-colors duration-[120ms] ' +
                (active
                  ? 'border-[var(--accent)] bg-[var(--accent-wash)] text-[var(--accent)]'
                  : 'border-[var(--border-hairline)] text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]')
              }
            >
              {f.label}
            </Link>
          );
        })}
      </div>

      <Table>
        <thead>
          <tr>
            <Th>URL</Th>
            <Th>Status</Th>
            <Th>Title</Th>
            <Th>Description</Th>
            <Th align="right">Words</Th>
            <Th align="right">Depth</Th>
            <Th align="right">Response</Th>
          </tr>
        </thead>
        <tbody>
          {pages.map((p) => (
            <Tr key={p.id}>
              <Td>
                <span className="block max-w-[22rem] truncate font-mono text-[12px]" title={p.url}>
                  {pathOf(p.url)}
                </span>
                <span className="mt-0.5 flex gap-1.5 text-[11px] text-[var(--text-faint)]">
                  {!p.is_indexable ? <span>noindex</span> : null}
                  {p.in_sitemap ? <span>sitemap</span> : null}
                  {p.images_missing_alt > 0 ? (
                    <span>{p.images_missing_alt} missing alt</span>
                  ) : null}
                  {p.links_internal === 0 ? <span>no outbound links</span> : null}
                </span>
              </Td>
              <Td>
                <StatusBadge status={statusTone(p)}>
                  {p.fetch_class === 'blocked' ? 'blocked' : (p.status_code ?? '—')}
                </StatusBadge>
              </Td>
              <Td><LengthBar value={p.title} max={TITLE_MAX} /></Td>
              <Td><LengthBar value={p.meta_description} max={DESC_MAX} /></Td>
              <Td align="right" muted>{p.word_count}</Td>
              <Td align="right" muted>{p.depth}</Td>
              <Td align="right" muted>{p.response_ms ? `${p.response_ms}ms` : '—'}</Td>
            </Tr>
          ))}
        </tbody>
      </Table>

      {pages.length === 500 ? (
        <p className="mt-4 text-[12px] text-[var(--text-faint)]">
          Showing the first 500 pages. Export the full crawl from Reports.
        </p>
      ) : null}
    </>
  );
}

/** Length against the limit, so over-long titles are visible at a glance. */
function LengthBar({ value, max }: { value: string | null; max: number }) {
  if (!value) {
    return <span className="text-[12px] text-[var(--color-status-error)]">missing</span>;
  }
  const pct = Math.min(100, (value.length / max) * 100);
  const over = value.length > max;
  return (
    <span className="block max-w-[16rem]">
      <span className="block truncate text-[13px]" title={value}>{value}</span>
      <span className="mt-1 flex items-center gap-2">
        <span className="h-1 w-20 overflow-hidden rounded-full bg-[var(--surface-band)]">
          <span
            className="block h-full rounded-full"
            style={{
              width: `${pct}%`,
              background: over ? 'var(--color-status-warn)' : 'var(--accent)',
            }}
          />
        </span>
        <span
          className="text-[11px] tabular-nums"
          style={{ color: over ? 'var(--color-status-warn)' : 'var(--text-faint)' }}
        >
          {value.length}
        </span>
      </span>
    </span>
  );
}

function statusTone(p: PageRow) {
  if (p.fetch_class === 'blocked') return 'warn' as const;
  if (!p.status_code) return 'neutral' as const;
  if (p.status_code >= 500) return 'error' as const;
  if (p.status_code >= 400) return 'error' as const;
  if (p.status_code >= 300) return 'warn' as const;
  return 'ok' as const;
}

function pathOf(url: string): string {
  try {
    const u = new URL(url);
    return `${u.pathname}${u.search}` || '/';
  } catch {
    return url;
  }
}
