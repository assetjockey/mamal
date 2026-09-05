import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { accountReports } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { AdReport } from './client';

export const dynamic = 'force-dynamic';

export default async function Ads() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const reports = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;
    return accountReports(tx, { projectId: project.id });
  }, { db: db() });

  if (!reports) {
    return (
      <>
        <PageHeader title="Ads" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Ads"
        description="Where the money went, and what to do about it. All of this is arithmetic over your own ad accounts — no AI, no credits."
      />
      <AdReport reports={reports} />
    </>
  );
}
