import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import {
  citedSources, listCompetitors, listPrompts, visibilityOverview,
} from '@mamal/tool-market';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { VisibilityBoard } from './client';

export const dynamic = 'force-dynamic';

/**
 * What the models say about you when nobody asks them to.
 *
 * The screen is arranged around the one number that answers the question the
 * product exists for — "if somebody asks this, will they hear about us" — and
 * everything else is evidence for it: the answers themselves, who was named
 * instead, and which pages got cited.
 */
export default async function Visibility() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;

    const projectId = project.id;
    return {
      overview: await visibilityOverview(tx, { projectId }),
      prompts: await listPrompts(tx, { projectId }),
      brands: await listCompetitors(tx, { projectId }),
      sources: await citedSources(tx, { projectId, limit: 20 }),
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="AI visibility" />
        <EmptyState
          title="No project yet"
          description="Every Market feature hangs off a project. Create one from the overview and this screen will have something to measure."
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="AI visibility"
        description="A growing share of buying research never reaches a results page. This asks the assistants what a buyer would ask, and reads the answer."
      />
      <VisibilityBoard
        overview={data.overview}
        prompts={data.prompts}
        brands={data.brands}
        sources={data.sources}
      />
    </>
  );
}
