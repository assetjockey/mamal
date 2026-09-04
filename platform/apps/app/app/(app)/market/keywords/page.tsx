import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader, SectionLabel, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { AddKeywords } from './client';

export const dynamic = 'force-dynamic';

type Row = {
  id: string; keyword: string; volume: number | null; difficulty: number | null;
  intent: string | null; cpc_micros: number | null; source: string;
  fetched_at: string | null; location_code: number;
};

export default async function Keywords({
  searchParams,
}: {
  searchParams: Promise<{ q?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { q } = await searchParams;

  const rows = await withWorkspace(
    ws,
    (tx) => tx.execute<Row>(sql`
      select id, keyword, volume, difficulty, intent, cpc_micros, source, fetched_at, location_code
        from seo_keywords
       where workspace_id = ${ws}
         ${q ? sql`and keyword ilike ${'%' + q + '%'}` : sql``}
       order by volume desc nulls last, keyword
       limit 500`),
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Keywords"
        description="A list you can build for nothing, and enrich when you choose to spend. Volume and difficulty come from a paid vendor — nothing here fetches them until you ask."
        action={<AddKeywords />}
      />

      {rows.length === 0 ? (
        <EmptyState
          title={q ? 'Nothing matches that' : 'No keywords yet'}
          description={
            q
              ? 'Try a different search.'
              : 'Paste a list — one per line, or comma-separated. They are stored without metrics, so adding them costs nothing.'
          }
        />
      ) : (
        <>
          <SectionLabel>{rows.length} keyword{rows.length === 1 ? '' : 's'}</SectionLabel>
          <div className="mt-3">
            <Table label="Keywords">
              <thead>
                <Tr>
                  <Th>Keyword</Th>
                  <Th align="right">Volume</Th>
                  <Th align="right">Difficulty</Th>
                  <Th>Intent</Th>
                  <Th align="right">CPC</Th>
                </Tr>
              </thead>
              <tbody>
                {rows.map((row) => (
                  <Tr key={row.id}>
                    <Td>
                      <span className="text-[var(--text-primary)]">{row.keyword}</span>
                      {/*
                        Says where the figure came from and when. A month-old
                        volume is still useful; pretending it is current is not.
                      */}
                      {row.fetched_at ? null : (
                        <span className="ml-2 text-[12px] text-[var(--text-faint)]">not researched</span>
                      )}
                    </Td>
                    <Td align="right">
                      <span className="tabular-nums">
                        {row.volume === null ? '—' : row.volume.toLocaleString()}
                      </span>
                    </Td>
                    <Td align="right">
                      <span className="tabular-nums">{row.difficulty ?? '—'}</span>
                    </Td>
                    <Td>
                      <span className="text-[13px] text-[var(--text-secondary)]">
                        {row.intent ?? '—'}
                      </span>
                    </Td>
                    <Td align="right">
                      <span className="tabular-nums">
                        {row.cpc_micros === null
                          ? '—'
                          : `$${(row.cpc_micros / 1_000_000).toFixed(2)}`}
                      </span>
                    </Td>
                  </Tr>
                ))}
              </tbody>
            </Table>
          </div>
        </>
      )}
    </>
  );
}
