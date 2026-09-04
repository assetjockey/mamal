import NextLink from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { shortUrl } from '@mamal/tool-link';
import { Button, EmptyState, PageHeader, SectionLabel, Table, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { NewLink, LinkRow, Suggestions } from './client';
import { BulkImport } from './import';

export const dynamic = 'force-dynamic';

type Row = {
  id: string; alias: string; kind: string; title: string | null;
  destination_url: string | null; campaign: string | null; tags: string[];
  is_enabled: boolean; clicks_count: number; max_clicks: number | null;
  expires_at: string | null; has_password: boolean; rules: number;
  created_at: string;
};

type Suggestion = { id: string; target_url: string; context_url: string | null };

export default async function LinksPage({
  searchParams,
}: {
  searchParams: Promise<{ q?: string; kind?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { q, kind } = await searchParams;

  const [links, limit, suggestions, bulkAllowed] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<Row>(sql`
        select l.id, l.alias, l.kind, l.title, l.destination_url, l.campaign, l.tags,
               l.is_enabled, l.clicks_count, l.max_clicks, l.expires_at,
               l.password_hash is not null as has_password,
               (select count(*)::int from link_rules r where r.link_id = l.id) as rules,
               l.created_at
          from links l
         where l.workspace_id = ${ws}
           and l.deleted_at is null
           and l.kind <> 'biolink'
           ${kind ? sql`and l.kind = ${kind}` : sql``}
           ${q ? sql`and (l.alias ilike ${'%' + q + '%'} or l.title ilike ${'%' + q + '%'}
                          or l.destination_url ilike ${'%' + q + '%'})` : sql``}
         order by l.created_at desc
         limit 200`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.links');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from links
           where workspace_id = ${ws} and deleted_at is null`);
        const used = n?.count ?? 0;
        const d = resolve({ ...ctx, used }, 1);
        return {
          used, max: d.limit ?? d.quota ?? null,
          allowed: d.allowed, why: d.allowed ? null : d.message,
        };
      })(),

      await tx.execute<Suggestion>(sql`
        select id, target_url, context_url from link_suggestions
         where workspace_id = ${ws} and status = 'open'
         order by created_at desc limit 5`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.bulk');
        return ctx ? resolve({ ...ctx, used: 0 }, 1).allowed : false;
      })(),
    ],
    { db: db() },
  );

  const rows = links.map((l) => ({ ...l, shortUrl: shortUrl(l.alias) }));

  if (rows.length === 0 && !q && !kind) {
    return (
      <>
        <PageHeader
          title="Link"
          description="One address for everything you publish — short links, bio pages, QR codes and transfers, all re-pointable after they are printed."
        />
        <EmptyState
          title="No links yet"
          description="Paste a URL and you will have a short link you can change the destination of later, without reprinting anything."
          action={<NewLink allowed={limit?.allowed ?? true} />}
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Link"
        description="Short links, bio pages, QR codes and transfers."
        action={
          <div className="flex flex-wrap items-start gap-2">
            <NewLink allowed={limit?.allowed ?? true} />
            <BulkImport allowed={bulkAllowed} />
          </div>
        }
      />

      {limit && limit.max !== null && limit.max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {limit.used.toLocaleString()} of {limit.max.toLocaleString()} link
          {limit.max === 1 ? '' : 's'} used.
          {!limit.allowed ? (
            <span className="text-[var(--color-status-warn)]"> {limit.why}</span>
          ) : null}
        </p>
      ) : null}

      {suggestions.length > 0 ? <Suggestions items={suggestions} /> : null}

      <SectionLabel>Links</SectionLabel>
      {rows.length === 0 ? (
        <EmptyState
          title="Nothing matches that"
          description="Try a different search, or clear the filter."
          action={<NextLink href="/link"><Button variant="ghost">Clear</Button></NextLink>}
        />
      ) : (
        <Table label="Links">
          <thead>
            <Tr>
              <Th>Short link</Th>
              <Th>Destination</Th>
              <Th>Status</Th>
              <Th align="right">Clicks</Th>
              <Th align="right"> </Th>
            </Tr>
          </thead>
          <tbody>
            {rows.map((l) => (
              <LinkRow key={l.id} link={l} />
            ))}
          </tbody>
        </Table>
      )}
    </>
  );
}
