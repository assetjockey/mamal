import { sql } from 'drizzle-orm';
import { db } from '@/lib/db';
import {withWorkspace } from '@mamal/db';
import { Card, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';

export const dynamic = 'force-dynamic';

type Template = {
  key: string;
  name: string;
  description: string;
  category: string;
  required_tools: string[];
  definition: {
    trigger: { event: string };
    conditions?: unknown[];
    actions?: { type: string; name?: string }[];
  };
};

export default async function AutomationsPage() {
  const session = await getSession();
  if (!session) return null;
  const ws = session.workspace;
  const database = db();

  const [templates, installed, runs] = await withWorkspace(
    ws.id,
    async (tx) => [
      await tx.execute<Template>(sql`
        select key, name, description, category, required_tools, definition
          from automation_templates order by category, sort_order, key`),
      await tx.execute<{ key: string }>(sql`
        select key from instance_modules where kind = 'tool' and installed and enabled`),
      await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from automations where workspace_id = ${ws.id}`),
    ] as const,
    { db: database },
  );

  const have = new Set(installed.map((m) => m.key));
  const active = Number(runs[0]?.n ?? 0);
  const canRun = (t: Template) => t.required_tools.every((k) => have.has(k));

  const byCategory = new Map<string, Template[]>();
  for (const t of templates) byCategory.set(t.category, [...(byCategory.get(t.category) ?? []), t]);

  return (
    <>
      <PageHeader
        title="Automations"
        description="WHEN something happens, IF the conditions hold, THEN do something in another tool. This is the layer that makes six tools one platform — every recipe below does something none of the source products could, because none of them could see another's data."
      />

      {active === 0 ? (
        <Card className="mb-8">
          <div className="text-[14px] text-[var(--text-secondary)]">
            You have no automations yet. The recipes below are seeded and ready — each one is a real
            rule, validated against the DSL, waiting on the tools it needs.
          </div>
        </Card>
      ) : null}

      {[...byCategory.entries()].map(([category, items]) => (
        <section key={category} className="mb-10">
          <SectionLabel>{category}</SectionLabel>
          <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
            {items.map((t) => {
              const runnable = canRun(t);
              const missing = t.required_tools.filter((k) => !have.has(k));
              const actions = t.definition.actions ?? [];
              return (
                <Card key={t.key}>
                  <div className="flex items-start justify-between gap-3">
                    <h3 className="text-[20px] leading-[1.4] text-[var(--text-primary)]">{t.name}</h3>
                    <StatusBadge status={runnable ? 'ok' : 'neutral'}>
                      {runnable ? 'Ready' : `Needs ${missing.join(', ')}`}
                    </StatusBadge>
                  </div>
                  <p className="mt-2 text-[14px] leading-[1.4] text-[var(--text-secondary)]">
                    {t.description}
                  </p>
                  <dl className="mt-4 space-y-1 text-[13px]">
                    <div className="flex gap-2">
                      <dt className="w-16 shrink-0 text-[var(--text-faint)]">When</dt>
                      <dd className="min-w-0 break-all font-mono text-[12px]">
                        {t.definition.trigger.event}
                      </dd>
                    </div>
                    {(t.definition.conditions ?? []).length > 0 ? (
                      <div className="flex gap-2">
                        <dt className="w-16 shrink-0 text-[var(--text-faint)]">If</dt>
                        <dd className="text-[var(--text-muted)]">
                          {(t.definition.conditions ?? []).length} condition
                          {(t.definition.conditions ?? []).length === 1 ? '' : 's'}
                        </dd>
                      </div>
                    ) : null}
                    <div className="flex gap-2">
                      <dt className="w-16 shrink-0 text-[var(--text-faint)]">Then</dt>
                      <dd className="min-w-0 break-all font-mono text-[12px]">
                        {actions.map((a) => a.name ?? a.type).join(' → ')}
                      </dd>
                    </div>
                  </dl>
                </Card>
              );
            })}
          </div>
        </section>
      ))}

      <SectionLabel>How a rule runs</SectionLabel>
      <Table>
        <thead>
          <tr>
            <Th>Stage</Th>
            <Th>Guarantee</Th>
          </tr>
        </thead>
        <tbody>
          <Tr>
            <Td>Publish</Td>
            <Td muted>Written to the outbox in the same transaction as the state change — exactly-once production</Td>
          </Tr>
          <Tr>
            <Td>Relay</Td>
            <Td muted>Outbox → stream, at-least-once, with lag as a first-class SLO</Td>
          </Tr>
          <Tr>
            <Td>Dispatch</Td>
            <Td muted>One barrier row per handler per event — effectively-once, retried 8 times then dead-lettered</Td>
          </Tr>
          <Tr>
            <Td>Evaluate</Td>
            <Td muted>Filter, then conditions — including the plan, so a rule cannot mint past its entitlement</Td>
          </Tr>
          <Tr>
            <Td>Act</Td>
            <Td muted>Commands resolve through the registry; an uninstalled tool skips with a reason instead of throwing</Td>
          </Tr>
        </tbody>
      </Table>
    </>
  );
}
