import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { SplashList } from './client';

export const dynamic = 'force-dynamic';

export default async function SplashPages() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const pages = await withWorkspace(
    ws,
    (tx) => tx.execute<{
      id: string; name: string; delay_seconds: number; is_skippable: boolean;
      auto_redirect: boolean; settings: { title?: string; body?: string }; used: number;
    }>(sql`
      select s.id, s.name, s.delay_seconds, s.is_skippable, s.auto_redirect, s.settings,
             (select count(*)::int from links l
               where l.workspace_id = ${ws} and l.deleted_at is null
                 and l.settings->>'splashPageId' = s.id::text) as used
        from splash_pages s
       where s.workspace_id = ${ws} order by s.created_at`),
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Splash pages"
        description="A page a link passes through before its destination — for a disclaimer, a sponsor, or a warning."
      />
      <SplashList pages={pages} />
    </>
  );
}
