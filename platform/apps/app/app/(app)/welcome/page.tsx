import { sql } from 'drizzle-orm';
import { redirect } from 'next/navigation';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { WelcomeFlow } from './flow';

export const dynamic = 'force-dynamic';

export default async function WelcomePage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');

  const [state] = await withWorkspace(
    session.workspace.id,
    (tx) => tx.execute<{ interests: string[]; first_resource_url: string | null }>(sql`
      select interests, first_resource_url from onboarding
       where workspace_id = ${session.workspace.id}`),
    { db: db() },
  );

  return (
    <div className="mx-auto max-w-2xl">
      <PageHeader
        title={`Welcome, ${session.user.name ?? 'there'}`}
        description="Two questions, then we go and look at your site. Nothing here costs anything — the first audit runs on the free tier."
      />
      <WelcomeFlow
        interests={state?.interests ?? []}
        siteUrl={state?.first_resource_url ?? null}
      />
    </div>
  );
}
