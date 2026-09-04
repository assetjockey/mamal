import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getDoc } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Editor } from './client';

export const dynamic = 'force-dynamic';

export default async function Document({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { id } = await params;

  const loaded = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;
    return getDoc(tx, { projectId: project.id, docId: id });
  }, { db: db() });

  if (!loaded) notFound();

  return (
    <>
      <PageHeader title={loaded.doc.title} description="Editing" />
      <Editor doc={loaded.doc} brief={loaded.brief} initialScore={loaded.score} />
    </>
  );
}
