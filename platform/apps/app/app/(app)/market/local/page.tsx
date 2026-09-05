import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { latestGrids, listProfiles, listReviews } from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { LocalBoard } from './client';

export const dynamic = 'force-dynamic';

export default async function Local() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;

    const profiles = await listProfiles(tx, { projectId: project.id });
    const first = profiles[0];

    return {
      profiles,
      grids: first ? await latestGrids(tx, { profileId: first.id }) : [],
      reviews: first ? await listReviews(tx, { profileId: first.id, limit: 20 }) : [],
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="Local" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Local"
        description="Where you show up block by block, what your profile is missing, and which reviews need answering. None of it needs AI."
      />
      <LocalBoard profiles={data.profiles} grids={data.grids} reviews={data.reviews} />
    </>
  );
}
