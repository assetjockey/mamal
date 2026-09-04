import { sql } from 'drizzle-orm';
import { db } from '@/lib/db';
import {withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { PageHeader, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { Controls, type PlanOption } from './controls';

export const dynamic = 'force-dynamic';

/**
 * Runs the real resolver against every seeded feature for this workspace.
 * Nothing here is mocked — this is the same call path a tool makes before it
 * does anything billable.
 */
export default async function EntitlementsPage() {
  const session = await getSession();
  if (!session) return null;
  const ws = session.workspace;
  const database = db();

  const [flags] = await withWorkspace(
    ws.id,
    (tx) => tx.execute<{ ai_master: boolean; ai_tenant: boolean }>(sql`
      select i.ai_master_enabled as ai_master, w.ai_enabled as ai_tenant
        from instance_settings i, workspaces w
       where w.id = ${ws.id} limit 1`),
    { db: database },
  );

  const plans = await withWorkspace(
    ws.id,
    (tx) => tx.execute<PlanOption>(sql`
      select p.key, p.name, p.kind,
             exists (
               select 1 from subscriptions s
                where s.plan_id = p.id and s.workspace_id = ${ws.id} and s.status = 'active'
             ) as active
        from plans p
       where p.kind <> 'free'
       order by case p.kind when 'tool' then 0 when 'unified' then 1 else 2 end,
                p.tool nulls first, p.tier_rank`),
    { db: database },
  );

  const featureKeys = await withWorkspace(
    ws.id,
    (tx) => tx.execute<{ key: string; tool: string; is_ai: boolean }>(
      sql`select key, tool, is_ai from features order by tool, key`,
    ),
    { db: database },
  );

  const rows = await withWorkspace(
    ws.id,
    async (tx) => {
      const out = [];
      for (const f of featureKeys) {
        const ctx = await loadContext(tx, ws.id, f.key);
        const decision = ctx ? resolve(ctx) : null;
        out.push({ ...f, decision });
      }
      return out;
    },
    { db: database },
  );

  const byTool = new Map<string, typeof rows>();
  for (const r of rows) byTool.set(r.tool, [...(byTool.get(r.tool) ?? []), r]);

  const allowedCount = rows.filter((r) => r.decision?.allowed).length;

  return (
    <>
      <Controls
        plans={plans}
        aiMaster={flags?.ai_master ?? true}
        aiTenant={flags?.ai_tenant ?? true}
        credits={ws.credits}
      />

      <PageHeader
        title="Entitlements"
        description={`Live resolver output for this workspace — ${allowedCount} of ${rows.length} features allowed on ${ws.plan}. Denials name their actual cause, so "your admin disabled AI" is never confused with "not on your plan".`}
      />

      {[...byTool.entries()].map(([tool, features]) => (
        <section key={tool} className="mb-8">
          <h2 className="mb-3 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
            {tool}
          </h2>
          <Table>
            <thead>
              <tr>
                <Th>Feature</Th>
                <Th>Verdict</Th>
                <Th align="right">Allowance</Th>
                <Th>Reason / cost</Th>
              </tr>
            </thead>
            <tbody>
              {features.map((f) => {
                const d = f.decision;
                const allowance =
                  d?.allowed && d.limit != null
                    ? d.limit === -1 ? 'Unlimited' : d.limit.toLocaleString()
                    : d?.allowed && d.quota != null
                      ? d.quota === -1 ? 'Unlimited' : `${d.quota.toLocaleString()}/mo`
                      : '—';
                return (
                  <Tr key={f.key}>
                    <Td>
                      <span className="font-mono text-[13px]">{f.key}</span>
                      {f.is_ai ? (
                        <span className="ml-2 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                          AI
                        </span>
                      ) : null}
                    </Td>
                    <Td>
                      {d?.allowed ? (
                        <StatusBadge status="ok">Allowed</StatusBadge>
                      ) : (
                        <StatusBadge status={f.is_ai ? 'warn' : 'neutral'}>Denied</StatusBadge>
                      )}
                    </Td>
                    <Td align="right" muted>{allowance}</Td>
                    <Td muted>
                      {d?.allowed
                        ? d.cost > 0
                          ? `${d.cost} credits`
                          : `included (${d.source})`
                        : (d && !d.allowed ? d.reason : 'unknown')}
                    </Td>
                  </Tr>
                );
              })}
            </tbody>
          </Table>
        </section>
      ))}
    </>
  );
}
