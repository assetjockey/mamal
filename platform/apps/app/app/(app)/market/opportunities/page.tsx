import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { OpportunityList, type OpportunityRow } from './client';

export const dynamic = 'force-dynamic';

export default async function Opportunities({
  searchParams,
}: {
  searchParams: Promise<{ kind?: string; status?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { kind, status } = await searchParams;

  const [rows, counts, hasData] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<OpportunityRow>(sql`
        select id, kind, query, page, score, evidence, status, detected_on
          from seo_opportunities
         where workspace_id = ${ws}
           and status = ${status ?? 'open'}
           ${kind ? sql`and kind = ${kind}` : sql``}
         order by score desc
         limit 200`),

      await tx.execute<{ kind: string; n: number }>(sql`
        select kind, count(*)::int as n from seo_opportunities
         where workspace_id = ${ws} and status = 'open'
         group by kind order by kind`),

      await (async () => {
        const [row] = await tx.execute<{ n: number }>(sql`
          select count(*)::int as n from market_search_performance where workspace_id = ${ws}`);
        return (row?.n ?? 0) > 0;
      })(),
    ],
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Opportunities"
        description="Arithmetic over your Search Console data — no vendor call, no credits. Where you already rank, and what it would take to do better."
      />
      {!hasData ? (
        <EmptyState
          title="Connect Search Console"
          description="Every finder here runs on data Google gives away: queries you rank 11th for, pages that used to earn more, titles nobody clicks. It costs nothing, which is why it works on the free plan."
        />
      ) : (
        <OpportunityList
          rows={rows}
          counts={counts}
          activeKind={kind ?? null}
          activeStatus={status ?? 'open'}
        />
      )}
    </>
  );
}
