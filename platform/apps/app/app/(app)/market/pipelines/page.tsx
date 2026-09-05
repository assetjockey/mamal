import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { EmptyState, PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { PipelineBoard, type PipelineView, type RunView } from './client';

export const dynamic = 'force-dynamic';

export default async function Pipelines() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const data = await withWorkspace(ws, async (tx) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${ws}
       order by is_default desc, created_at limit 1`);
    if (!project) return null;

    return {
      pipelines: await tx.execute<PipelineView>(sql`
        select p.id, p.name, p.source, p.schedule, p.auto_publish, p.is_active,
               p.next_run_at::text, p.source_config,
               d.name as destination_name
          from content_pipelines p
          left join publish_destinations d on d.id = p.destination_id
         where p.project_id = ${project.id}
         order by p.created_at`),

      runs: await tx.execute<RunView>(sql`
        select r.id, r.pipeline_id, r.status, r.error, r.credits_spent,
               r.trigger, r.doc_id, r.created_at::text, d.title as doc_title
          from content_runs r
          join content_pipelines p on p.id = r.pipeline_id
          left join content_docs d on d.id = r.doc_id
         where p.project_id = ${project.id}
         order by r.created_at desc
         limit 30`),
    };
  }, { db: db() });

  if (!data) {
    return (
      <>
        <PageHeader title="Pipelines" />
        <EmptyState title="No project yet" description="Create one from the overview." />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Pipelines"
        description="A source, a schedule, and somewhere to put the result. Nothing publishes until you turn that on — and with AI off a run still hands you a brief to write from."
      />
      <PipelineBoard pipelines={data.pipelines} runs={data.runs} />
    </>
  );
}
