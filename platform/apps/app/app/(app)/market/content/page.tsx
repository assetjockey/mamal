import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { listDocs } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { DocList } from './client';

export const dynamic = 'force-dynamic';

export default async function Content({
  searchParams,
}: {
  searchParams: Promise<{ status?: string }>;
}) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { status } = await searchParams;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;

    return {
      docs: await listDocs(tx, { projectId: project.id, status }),
      counts: await tx.execute<{ status: string; n: number }>(sql`
        select status, count(*)::int as n from content_docs
         where project_id = ${project.id} and deleted_at is null
         group by status order by status`),
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="Content" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Content"
        description="Write against a brief and see the score as you go. The scoring is arithmetic over your own draft — it costs nothing and works with AI switched off."
      />
      <DocList docs={data.docs} counts={data.counts} activeStatus={status ?? null} />
    </>
  );
}
