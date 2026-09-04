import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { SettingsForm } from './form';

export const dynamic = 'force-dynamic';

export default async function SiteSettings({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const { id } = await params;
  const ws = session.workspace.id;

  const [row] = await withWorkspace(
    ws,
    (tx) => tx.execute<{
      host: string; schedule: string;
      crawl_config: { maxPages: number; maxDepth: number; respectRobots: boolean; excludePatterns?: string[] };
    }>(sql`
      select s.host, a.schedule, a.crawl_config
        from audit_sites a join sites s on s.id = a.site_id
       where a.id = ${id} and a.workspace_id = ${ws}`),
    { db: db() },
  );
  if (!row) notFound();

  const canSchedule = session.workspace.allowed.includes('audit.schedule');

  return (
    <>
      <PageHeader
        title={`${row.host} — Settings`}
        description="How this site is crawled, and how often. Larger crawls cost pages against your monthly quota."
      />
      <SettingsForm
        auditSiteId={id}
        schedule={row.schedule}
        config={row.crawl_config}
        canSchedule={canSchedule}
      />
    </>
  );
}
