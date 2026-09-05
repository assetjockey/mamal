import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { listAccounts, listPosts } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Calendar } from './client';

export const dynamic = 'force-dynamic';

export default async function CalendarPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;
    return {
      accounts: await listAccounts(tx, { projectId: project.id }),
      posts: await listPosts(tx, { projectId: project.id, limit: 200 }),
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="Calendar" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Calendar"
        description="What is going out, and when. Each account posts in its own slots, in its own timezone."
      />
      <Calendar accounts={data.accounts} posts={data.posts} />
    </>
  );
}
