import { sql } from 'drizzle-orm';
import { db } from '@/lib/db';
import {asPlatformAdmin } from '@mamal/db';
import { Card, Divider, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';

export const dynamic = 'force-dynamic';

type PlanRow = {
  key: string; name: string; kind: string; tool: string | null;
  tier_rank: number; entitlements: number; ai_grants: number;
  price_month: number | null; price_once: number | null; credit_grant: number | null;
};

export default async function PlansPage() {
  const database = db();
  const plans = await asPlatformAdmin(
    (tx) =>
      tx.execute<PlanRow>(sql`
        select p.key, p.name, p.kind, p.tool, p.tier_rank,
          (select count(*) from plan_entitlements pe where pe.plan_id = p.id and pe.mode <> 'deny')::int
            as entitlements,
          (select count(*) from plan_entitlements pe
             join features f on f.key = pe.feature_key
            where pe.plan_id = p.id and f.is_ai and pe.mode <> 'deny')::int as ai_grants,
          (select amount_cents from plan_prices pp where pp.plan_id = p.id and pp.interval = 'month')
            as price_month,
          (select amount_cents from plan_prices pp where pp.plan_id = p.id and pp.interval = 'once')
            as price_once,
          (select amount from plan_credit_grants g where g.plan_id = p.id limit 1) as credit_grant
        from plans p
        order by case p.kind when 'free' then 0 when 'tool' then 1 when 'unified' then 2 else 3 end,
                 p.tool nulls first, p.tier_rank`),
    { db: database },
  );

  const groups = [
    { kind: 'free', label: 'Free floor', note: 'Always applied. Nothing here touches a metered vendor.' },
    { kind: 'tool', label: 'Per-tool plans', note: 'Buy one tool. Limits merge with MAX against anything else you hold.' },
    { kind: 'unified', label: 'Unified plans', note: 'Every tool, one price. Quotas merge with SUM.' },
    { kind: 'lifetime', label: 'Lifetime', note: 'Excludes AI at three enforcement points — the trigger rejects any non-deny AI entitlement.' },
  ];

  const money = (c: number | null) => (c == null ? '—' : `$${(c / 100).toFixed(0)}`);

  return (
    <>
      <PageHeader
        title="Plans"
        description="Four selling motions coexist: per-tool subscriptions, a unified subscription, an AI-free lifetime tier, and credits that spend across everything."
      />

      {groups.map((g) => {
        const rows = plans.filter((p) => p.kind === g.kind);
        if (rows.length === 0) return null;
        return (
          <section key={g.kind} className="mb-10">
            <SectionLabel>{g.label}</SectionLabel>
            <p className="mb-4 max-w-2xl text-[14px] text-[var(--text-secondary)]">{g.note}</p>
            <Table>
              <thead>
                <tr>
                  <Th>Plan</Th>
                  <Th>Tool</Th>
                  <Th align="right">Monthly</Th>
                  <Th align="right">Credits</Th>
                  <Th align="right">Grants</Th>
                  <Th>AI</Th>
                </tr>
              </thead>
              <tbody>
                {rows.map((p) => (
                  <Tr key={p.key}>
                    <Td>{p.name}</Td>
                    <Td muted>{p.tool ?? '—'}</Td>
                    <Td align="right">{p.kind === 'lifetime' ? money(p.price_once) : money(p.price_month)}</Td>
                    <Td align="right" muted>{p.credit_grant ? p.credit_grant.toLocaleString() : '—'}</Td>
                    <Td align="right" muted>{p.entitlements}</Td>
                    <Td>
                      {p.kind === 'lifetime' ? (
                        <StatusBadge status="neutral">Excluded</StatusBadge>
                      ) : p.ai_grants > 0 ? (
                        <StatusBadge status="info">{p.ai_grants} features</StatusBadge>
                      ) : (
                        <StatusBadge status="neutral">None</StatusBadge>
                      )}
                    </Td>
                  </Tr>
                ))}
              </tbody>
            </Table>
          </section>
        );
      })}

      <Divider />

      <Card>
        <h2 className="text-[20px]">Why lifetime shows zero AI grants</h2>
        <p className="mt-2 max-w-2xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
          A database trigger on <code className="text-[13px]">plan_entitlements</code> rejects any AI
          feature on a lifetime plan unless the mode is <code className="text-[13px]">deny</code>. It
          cannot be mis-configured from the admin UI. The resolver blocks it a second time, and the
          driver boundary in the AI package will block it a third. Lifetime holders can still buy
          credits — whether those credits may be spent on AI is one instance setting.
        </p>
      </Card>
    </>
  );
}
