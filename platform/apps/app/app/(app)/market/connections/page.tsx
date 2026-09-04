import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { ConnectionList, type ConnectionRow } from './client';

export const dynamic = 'force-dynamic';

/** Which allowance each provider is counted against — mirroring the service. */
const LIMITED: Record<string, string> = {
  google_search_console: 'market.gsc_connections',
  google_analytics: 'market.ga4_connections',
};

export default async function Connections() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [rows, limits] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<ConnectionRow>(sql`
        select id, provider, display_name, status, last_error, last_synced_at, expires_at
          from market_connections
         where workspace_id = ${ws} and status <> 'revoked'
         order by provider, display_name`),

      await (async () => {
        const out: Record<string, { used: number; max: number | null; allowed: boolean; why: string | null }> = {};
        for (const [provider, feature] of Object.entries(LIMITED)) {
          const ctx = await loadContext(tx, ws, feature);
          if (!ctx) continue;
          const [n] = await tx.execute<{ count: number }>(sql`
            select count(*)::int as count from market_connections
             where workspace_id = ${ws} and provider = ${provider} and status <> 'revoked'`);
          const used = n?.count ?? 0;
          const d = resolve({ ...ctx, used }, 1);
          out[provider] = {
            used, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message,
          };
        }
        return out;
      })(),
    ],
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Connections"
        description="Search Console and Analytics are free APIs, and everything the opportunity finders do runs on them. Social and ad accounts come next."
      />
      <ConnectionList rows={rows} limits={limits} />
    </>
  );
}
