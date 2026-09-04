import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { EmptyState, PageHeader, SectionLabel } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { RankBoard, type TrackerRow, type PositionRow } from './client';

export const dynamic = 'force-dynamic';

export default async function Rank() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [trackers, positions, limit] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<TrackerRow>(sql`
        select c.id, c.domain, c.schedule, c.is_active, c.last_run_at, c.next_check_at,
               (select count(*)::int from rank_keywords k
                 where k.config_id = c.id and k.is_active) as keywords
          from rank_configs c
         where c.workspace_id = ${ws}
         order by c.created_at`),

      await tx.execute<PositionRow>(sql`
        select distinct on (s.keyword_id, s.device)
               s.keyword_id, k.keyword, s.config_id, s.device,
               s.position, s.previous_position, s.url, s.captured_on
          from rank_snapshots s
          join rank_keywords k on k.id = s.keyword_id
         where s.workspace_id = ${ws}
         order by s.keyword_id, s.device, s.captured_on desc`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'market.tracked_keywords');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from rank_keywords
           where workspace_id = ${ws} and is_active`);
        const used = n?.count ?? 0;
        const d = resolve({ ...ctx, used }, 1);
        return { used, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),
    ],
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Rank tracking"
        description="Every check is a SERP call somebody pays for, so this is metered — and the number of keywords you track is the number you are billed for."
      />

      {limit && limit.max !== null && limit.max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {limit.used.toLocaleString()} of {limit.max.toLocaleString()} tracked keywords.
          {!limit.allowed ? (
            <span className="text-[var(--color-status-warn)]"> {limit.why}</span>
          ) : null}
        </p>
      ) : null}

      {trackers.length === 0 ? (
        <EmptyState
          title="No trackers yet"
          description="A tracker is a domain, a market and a schedule. Add one, then paste the keywords you care about."
          action={<RankBoard trackers={[]} positions={[]} canAdd={limit?.allowed ?? true} />}
        />
      ) : (
        <>
          <SectionLabel>Trackers</SectionLabel>
          <RankBoard trackers={trackers} positions={positions} canAdd={limit?.allowed ?? true} />
        </>
      )}
    </>
  );
}
