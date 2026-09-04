import NextLink from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { shortUrl } from '@mamal/tool-link';
import { Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { NewBioPage } from './client';

export const dynamic = 'force-dynamic';

export default async function BioPages() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [pages, limit] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; alias: string; title: string | null; template: string;
        is_published: boolean; views: number; blocks: number; clicks: number;
      }>(sql`
        select p.id, l.alias, l.title, p.template, p.is_published, p.views,
               (select count(*)::int from bio_blocks b where b.page_id = p.id) as blocks,
               (select coalesce(sum(b.clicks), 0)::int from bio_blocks b where b.page_id = p.id) as clicks
          from bio_pages p
          join links l on l.id = p.link_id
         where p.workspace_id = ${ws} and l.deleted_at is null
         order by p.created_at desc`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.bio_pages');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from bio_pages where workspace_id = ${ws}`);
        const d = resolve({ ...ctx, used: n?.count ?? 0 }, 1);
        return { used: n?.count ?? 0, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),
    ],
    { db: db() },
  );

  if (pages.length === 0) {
    return (
      <>
        <PageHeader title="Bio pages" description="One page, every link you want people to find." />
        <EmptyState
          title="No bio pages yet"
          description="A bio page is a link like any other — it just renders instead of redirecting. 84 block types, from a plain link to a payment button."
          action={<NewBioPage allowed={limit?.allowed ?? true} />}
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Bio pages"
        description="One page, every link you want people to find."
        action={<NewBioPage allowed={limit?.allowed ?? true} />}
      />

      {limit && limit.max !== null && limit.max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {limit.used} of {limit.max} page{limit.max === 1 ? '' : 's'} used.
          {!limit.allowed ? <span className="text-[var(--color-status-warn)]"> {limit.why}</span> : null}
        </p>
      ) : null}

      <SectionLabel>Pages</SectionLabel>
      <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
        {pages.map((p) => (
          <Card key={p.id}>
            <div className="flex items-start justify-between gap-4">
              <div className="min-w-0">
                <h3 className="truncate text-[20px] text-[var(--text-primary)]">
                  <NextLink href={`/link/bio/${p.id}`} className="hover:text-[var(--accent)]">
                    {p.title ?? `/${p.alias}`}
                  </NextLink>
                </h3>
                <p className="mt-0.5 truncate text-[13px] text-[var(--text-faint)]">
                  {shortUrl(p.alias)}
                </p>
              </div>
              <StatusBadge status={p.is_published ? 'ok' : 'neutral'}>
                {p.is_published ? 'Published' : 'Draft'}
              </StatusBadge>
            </div>
            <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[var(--border-hairline)] pt-4 text-[13px] tabular-nums text-[var(--text-secondary)]">
              <span>
                {p.blocks} block{p.blocks === 1 ? '' : 's'} ·{' '}
                {Number(p.views).toLocaleString()} view{Number(p.views) === 1 ? '' : 's'} ·{' '}
                {p.clicks.toLocaleString()} click{p.clicks === 1 ? '' : 's'}
              </span>
              <NextLink href={`/link/bio/${p.id}`}>
                <Button size="sm" variant="quiet">Edit</Button>
              </NextLink>
            </div>
          </Card>
        ))}
      </div>
    </>
  );
}
