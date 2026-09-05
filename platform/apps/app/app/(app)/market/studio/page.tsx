import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { listBrands, listCreatives } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Studio } from './client';

export const dynamic = 'force-dynamic';

export default async function StudioPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;
    return {
      brands: await listBrands(tx, { projectId: project.id }),
      creatives: await listCreatives(tx, { projectId: project.id, limit: 24 }),
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="Studio" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Studio"
        description="Ad copy and creative, written against your brand and measured against each platform's own limits before you use it."
      />
      <Studio brands={data.brands} creatives={data.creatives} />
    </>
  );
}
