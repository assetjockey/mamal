import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { shortUrl } from '@mamal/tool-link';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { LinkEditor } from './editor';

export const dynamic = 'force-dynamic';

export default async function LinkDetail({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { id } = await params;

  const [link, rules, rulesAllowed] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{
        id: string; alias: string; kind: string; title: string | null;
        destination_url: string | null; is_enabled: boolean; campaign: string | null;
        expires_at: string | null; expires_url: string | null; max_clicks: number | null;
        clicks_count: number; has_password: boolean; settings: Record<string, unknown>;
      }>(sql`
        select id, alias, kind, title, destination_url, is_enabled, campaign,
               expires_at, expires_url, max_clicks, clicks_count,
               password_hash is not null as has_password, settings
          from links where id = ${id} and workspace_id = ${ws} and deleted_at is null`))[0],

      await tx.execute<{
        id: string; priority: number; match: unknown; action: unknown;
        sticky: boolean; is_enabled: boolean;
      }>(sql`
        select id, priority, match, action, sticky, is_enabled
          from link_rules where link_id = ${id} order by priority`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.rules');
        return ctx ? resolve({ ...ctx, used: 0 }, 1) : null;
      })(),
    ],
    { db: db() },
  );

  // A cross-tenant read answers 404, not 403. Existence is information.
  if (!link) notFound();

  return (
    <>
      <PageHeader
        title={`/${link.alias}`}
        description={shortUrl(link.alias)}
      />
      <LinkEditor
        link={{
          id: link.id,
          alias: link.alias,
          kind: link.kind,
          title: link.title,
          destinationUrl: link.destination_url,
          isEnabled: link.is_enabled,
          campaign: link.campaign,
          expiresAt: link.expires_at,
          expiresUrl: link.expires_url,
          maxClicks: link.max_clicks,
          clicksCount: Number(link.clicks_count),
          hasPassword: link.has_password,
          settings: link.settings,
          shortUrl: shortUrl(link.alias),
        }}
        rules={rules.map((r) => ({
          id: r.id,
          priority: r.priority,
          match: r.match,
          action: r.action as never,
          sticky: r.sticky,
          isEnabled: r.is_enabled,
        }))}
        rulesAllowed={rulesAllowed?.allowed ?? false}
        rulesWhy={rulesAllowed?.allowed ? null : (rulesAllowed?.message ?? null)}
      />
    </>
  );
}
