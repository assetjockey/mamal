import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { PageHeader, UpgradeGate } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { DomainList } from './client';

export const dynamic = 'force-dynamic';

export default async function Domains() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [domains, limit] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; host: string; kind: string; dns_status: string; ssl_status: string;
        verification_token: string; is_primary: boolean; verified_at: string | null; links: number;
        last_check: { owned?: boolean; routed?: boolean; nextStep?: string | null };
        dns_checked_at: string | null;
      }>(sql`
        select d.id, d.host, d.kind, d.dns_status, d.ssl_status, d.verification_token,
               d.is_primary, d.verified_at, d.last_check, d.dns_checked_at,
               (select count(*)::int from links l
                 where l.custom_domain_id = d.id and l.deleted_at is null) as links
          from custom_domains d
         where d.workspace_id = ${ws}
         order by d.created_at`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'core.custom_domains');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from custom_domains where workspace_id = ${ws}`);
        const d = resolve({ ...ctx, used: n?.count ?? 0 }, 1);
        return { used: n?.count ?? 0, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),
    ],
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Domains"
        description="Serve links from your own domain. One domain covers short links, bio pages, status pages and transfer downloads — it is not a Link-only setting."
      />

      {limit && !limit.allowed && domains.length === 0 ? (
        <UpgradeGate
          feature="Custom domains"
          reason={
            limit.why ??
            'Custom domains are a paid feature. Each one costs us a per-hostname certificate, which is why the free plan cannot include them.'
          }
          used={limit.used}
          limit={limit.max ?? undefined}
        />
      ) : (
        <DomainList
          domains={domains}
          canAdd={limit?.allowed ?? false}
          why={limit?.why ?? null}
          used={limit?.used ?? 0}
          max={limit?.max ?? null}
        />
      )}
    </>
  );
}
