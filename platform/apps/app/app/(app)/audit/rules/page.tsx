import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { Card, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

type Rule = {
  id: string; category: string; severity: string; weight: number;
  title: string; why: string; applies_to: string;
  thresholds: Record<string, unknown>; is_ai_relevant: boolean;
  is_enabled: boolean; override_enabled: boolean | null;
};

export default async function RulesPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const rules = await withWorkspace(
    ws,
    (tx) => tx.execute<Rule>(sql`
      select r.id, r.category, r.severity, r.weight, r.title, r.why, r.applies_to,
             coalesce(o.thresholds, r.thresholds) as thresholds,
             r.is_ai_relevant, r.is_enabled, o.is_enabled as override_enabled
        from audit_rules r
        left join audit_rule_overrides o
          on o.rule_id = r.id and o.workspace_id = ${ws}
       order by case r.severity when 'critical' then 0 when 'warning' then 1 else 2 end,
                r.category, r.id`),
    { db: db() },
  );

  const byCategory = new Map<string, Rule[]>();
  for (const rule of rules) {
    byCategory.set(rule.category, [...(byCategory.get(rule.category) ?? []), rule]);
  }

  const aiCount = rules.filter((r) => r.is_ai_relevant).length;

  return (
    <>
      <PageHeader
        title="Rules"
        description={`${rules.length} checks. Each carries its own weight, evidence and fix — so the guidance is identical whether or not AI is switched on.`}
      />

      <Card className="mb-8">
        <div className="grid gap-6 sm:grid-cols-3">
          <Stat label="Critical" value={rules.filter((r) => r.severity === 'critical').length} hint="weight 10" />
          <Stat label="Warning" value={rules.filter((r) => r.severity === 'warning').length} hint="weight 5" />
          <Stat label="AI visibility" value={aiCount} hint="can an answer engine read and cite you" />
        </div>
      </Card>

      {[...byCategory.entries()].map(([category, group]) => (
        <section key={category} className="mb-10">
          <SectionLabel>{category.replace('-', ' ')}</SectionLabel>
          <Table>
            <thead>
              <tr>
                <Th>Check</Th>
                <Th>Severity</Th>
                <Th>Scope</Th>
                <Th>Thresholds</Th>
              </tr>
            </thead>
            <tbody>
              {group.map((rule) => (
                <Tr key={rule.id}>
                  <Td>
                    <span className="block text-[14px]">{rule.title}</span>
                    <span className="mt-0.5 block max-w-2xl text-[12px] leading-[1.4] text-[var(--text-muted)]">
                      {rule.why}
                    </span>
                    <span className="mt-1 block font-mono text-[11px] text-[var(--text-faint)]">
                      {rule.id}
                    </span>
                  </Td>
                  <Td>
                    <StatusBadge
                      status={
                        rule.severity === 'critical' ? 'error'
                        : rule.severity === 'warning' ? 'warn' : 'neutral'
                      }
                    >
                      {rule.severity}
                    </StatusBadge>
                    {rule.is_ai_relevant ? (
                      <StatusBadge status="info">AI</StatusBadge>
                    ) : null}
                  </Td>
                  <Td muted>{rule.applies_to}</Td>
                  <Td muted>
                    {Object.keys(rule.thresholds ?? {}).length === 0 ? (
                      '—'
                    ) : (
                      <span className="font-mono text-[12px]">
                        {Object.entries(rule.thresholds)
                          .map(([k, v]) => `${k}=${String(v)}`)
                          .join(' ')}
                      </span>
                    )}
                  </Td>
                </Tr>
              ))}
            </tbody>
          </Table>
        </section>
      ))}
    </>
  );
}

function Stat({ label, value, hint }: { label: string; value: number; hint: string }) {
  return (
    <div>
      <div className="text-[12px] uppercase tracking-[0.5px] text-[var(--text-faint)]">{label}</div>
      <div className="mt-1 text-[26px] leading-[1.12] tabular-nums">{value}</div>
      <div className="mt-0.5 text-[12px] text-[var(--text-muted)]">{hint}</div>
    </div>
  );
}
