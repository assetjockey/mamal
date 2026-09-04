import NextLink from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatTile, StatusBadge,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

export default async function MarketOverview() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [connections, stats, topOpportunities] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{ provider: string; display_name: string; status: string; last_synced_at: string | null }>(sql`
        select provider, display_name, status, last_synced_at
          from market_connections
         where workspace_id = ${ws} and status <> 'revoked'
         order by provider`),

      (await tx.execute<{
        keywords: number; tracked: number; docs: number; posts: number; opportunities: number;
      }>(sql`
        select
          (select count(*)::int from seo_keywords where workspace_id = ${ws}) as keywords,
          (select count(*)::int from rank_keywords where workspace_id = ${ws} and is_active) as tracked,
          (select count(*)::int from content_docs where workspace_id = ${ws} and deleted_at is null) as docs,
          (select count(*)::int from social_posts where workspace_id = ${ws} and deleted_at is null) as posts,
          (select count(*)::int from seo_opportunities where workspace_id = ${ws} and status = 'open') as opportunities`))[0],

      await tx.execute<{ id: string; kind: string; query: string | null; page: string | null; score: number }>(sql`
        select id, kind, query, page, score from seo_opportunities
         where workspace_id = ${ws} and status = 'open'
         order by score desc limit 5`),
    ],
    { db: db() },
  );

  const connected = connections.length > 0;

  return (
    <>
      <PageHeader
        title="Market"
        description="Rank on search, get named by AI models, publish, and make ad spend legible."
      />

      {!connected ? (
        <EmptyState
          title="Start with Search Console"
          description="One connection and the opportunity finders have something to work with — queries you rank 11th for, pages that used to earn more, titles nobody clicks. Free APIs, so it costs nothing to run."
          action={
            <NextLink href="/market/connections">
              <Button>Connect an account</Button>
            </NextLink>
          }
        />
      ) : (
        <>
          <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 [&>*]:min-w-0">
            <StatTile label="Open opportunities" value={(stats?.opportunities ?? 0).toLocaleString()} />
            <StatTile label="Keywords" value={(stats?.keywords ?? 0).toLocaleString()} />
            <StatTile label="Tracked" value={(stats?.tracked ?? 0).toLocaleString()} />
            <StatTile label="Documents" value={(stats?.docs ?? 0).toLocaleString()} />
          </div>

          {topOpportunities.length > 0 ? (
            <section className="mb-8">
              <SectionLabel>Worth doing next</SectionLabel>
              <div className="mt-3 grid gap-2">
                {topOpportunities.map((o) => (
                  <NextLink
                    key={o.id}
                    href={`/market/opportunities?kind=${o.kind}`}
                    className="flex items-center justify-between gap-3 rounded-[4px] border border-[var(--border-hairline)] px-3 py-2 hover:bg-[var(--surface-ground)]"
                  >
                    <span className="min-w-0 truncate text-[14px] text-[var(--text-primary)]">
                      {o.query ?? o.page}
                    </span>
                    <span className="shrink-0 text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
                      {o.kind.replace(/_/g, ' ')}
                    </span>
                  </NextLink>
                ))}
              </div>
            </section>
          ) : null}

          <SectionLabel>Connections</SectionLabel>
          <div className="mt-3 grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
            {connections.map((c) => (
              <Card key={`${c.provider}-${c.display_name}`}>
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0">
                    <p className="truncate text-[16px] text-[var(--text-primary)]">{c.display_name}</p>
                    <p className="mt-0.5 text-[13px] text-[var(--text-faint)]">
                      {c.provider.replace(/_/g, ' ')}
                      {c.last_synced_at
                        ? ` · synced ${new Date(c.last_synced_at).toLocaleDateString()}`
                        : ' · never synced'}
                    </p>
                  </div>
                  <StatusBadge status={c.status === 'active' ? 'ok' : 'warn'}>
                    {c.status === 'active' ? 'Connected' : c.status}
                  </StatusBadge>
                </div>
              </Card>
            ))}
          </div>
        </>
      )}
    </>
  );
}
