import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { PresetList } from './client';

export const dynamic = 'force-dynamic';

export default async function UtmPresets() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const presets = await withWorkspace(
    ws,
    (tx) => tx.execute<{ id: string; name: string; values: Record<string, string>; auto_apply: boolean }>(sql`
      select id, name, values, auto_apply from utm_presets
       where workspace_id = ${ws} order by created_at`),
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="UTM presets"
        description="Campaign parameters applied when a link is created. The link’s own UTM always wins over whatever arrives on the request, because that is what you are measuring."
      />
      <PresetList presets={presets} />
    </>
  );
}
