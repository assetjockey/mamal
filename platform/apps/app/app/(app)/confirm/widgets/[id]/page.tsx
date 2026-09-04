import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { fieldsFor, widgetDef, THEMES } from '@mamal/widget-catalog';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Editor } from './editor';

export const dynamic = 'force-dynamic';

export default async function WidgetEditorPage({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { id } = await params;

  const [row] = await withWorkspace(
    ws,
    (tx) => tx.execute<{
      id: string; campaign_id: string; type: string; name: string;
      settings: Record<string, unknown>; targeting: unknown;
      theme: string; position: string; display_frequency: string; display_limit: number;
      delay_seconds: number; duration_seconds: number; is_enabled: boolean;
      host: string; branding_removed: boolean;
    }>(sql`
      select w.id, w.campaign_id, w.type, w.name, w.settings, w.targeting, w.theme, w.position,
             w.display_frequency, w.display_limit, w.delay_seconds, w.duration_seconds,
             w.is_enabled, s.host, c.branding_removed
        from confirm_widgets w
        join confirm_campaigns c on c.id = w.campaign_id
        join sites s on s.id = c.site_id
       where w.id = ${id} and w.workspace_id = ${ws}`),
    { db: db() },
  );

  if (!row) notFound();

  const def = widgetDef(row.type);
  if (!def) notFound();

  return (
    <Editor
      widget={{
        id: row.id,
        campaignId: row.campaign_id,
        type: row.type,
        name: row.name,
        settings: row.settings,
        targeting: (row.targeting ?? {}) as Record<string, unknown>,
        theme: row.theme,
        position: row.position,
        displayFrequency: row.display_frequency,
        displayLimit: row.display_limit,
        delaySeconds: row.delay_seconds,
        durationSeconds: row.duration_seconds,
        isEnabled: row.is_enabled,
      }}
      meta={{
        label: def.label,
        description: def.description,
        family: def.family,
        needs: def.needs,
        host: row.host,
        showBranding: !row.branding_removed,
      }}
      // Derived from the type's own zod schema — the same schema that validates
      // the save, so the form cannot offer a field the validator rejects.
      fields={fieldsFor(def)}
      themes={THEMES.map((t) => ({ key: t.key, label: t.label }))}
    />
  );
}
