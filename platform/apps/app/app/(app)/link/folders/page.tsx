import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { FolderList } from './client';

export const dynamic = 'force-dynamic';

export default async function Folders() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const folders = await withWorkspace(
    ws,
    (tx) => tx.execute<{ id: string; name: string; color: string | null; links: number }>(sql`
      select f.id, f.name, f.color,
             (select count(*)::int from links l
               where l.folder_id = f.id and l.deleted_at is null) as links
        from link_folders f
       where f.workspace_id = ${ws}
       order by f.sort_order, f.created_at`),
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Folders"
        description="Group links however you actually work — by campaign, by client, by channel."
      />
      <FolderList folders={folders} />
    </>
  );
}
