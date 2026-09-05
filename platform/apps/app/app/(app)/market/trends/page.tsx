import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { listWatches } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { TrendBoard } from './client';

export const dynamic = 'force-dynamic';

export default async function Trends() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const watches = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;
    return listWatches(tx, { projectId: project.id });
  }, { db: db() });

  if (!watches) {
    return (
      <>
        <PageHeader title="Trends" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Trends"
        description="Watch a term and hear about it when interest actually moves — measured against a stored baseline per region, not against nothing."
      />
      <TrendBoard watches={watches} />
    </>
  );
}
