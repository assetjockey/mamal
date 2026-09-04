import Link from 'next/link';
import { sql } from 'drizzle-orm';
import { db } from '@/lib/db';
import {asPlatformAdmin } from '@mamal/db';
import { OutboxRelay, InProcessTransport } from '@mamal/bus';
import {
  Button, Card, Divider, PageHeader, SectionLabel, SetupChecklist, StatTile, StatusBadge,
  Table, Td, Th, Tr, type ChecklistStep,
} from '@mamal/ui';
import { TOOL_NAV } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { dismissOnboarding } from './welcome/actions';

export const dynamic = 'force-dynamic';

export default async function Home() {
  const session = await getSession();
  if (!session) return null;
  const ws = session.workspace;
  const database = db();

  const [counts] = await asPlatformAdmin(
    (tx) =>
      tx.execute<{ features: number; ai_features: number; plans: number; models: number; tables: number }>(sql`
        select
          (select count(*) from features)::int as features,
          (select count(*) from features where is_ai)::int as ai_features,
          (select count(*) from plans)::int as plans,
          (select count(*) from ai_models)::int as models,
          (select count(*) from information_schema.tables where table_schema='public')::int as tables`),
    { db: database },
  );

  const modules = await asPlatformAdmin(
    (tx) =>
      tx.execute<{ key: string; kind: string; installed: boolean; enabled: boolean }>(
        sql`select key, kind, installed, enabled from instance_modules order by kind, key`,
      ),
    { db: database },
  );

  const relay = await new OutboxRelay(database, new InProcessTransport()).pendingStats();

  const [onboardingState] = await asPlatformAdmin(
    (tx) => tx.execute<{ sites: number; dismissed: boolean }>(sql`
      select
        (select count(*)::int from sites where workspace_id = ${ws.id}) as sites,
        coalesce((select dismissed_at is not null from onboarding
                   where workspace_id = ${ws.id}), false) as dismissed`),
    { db: database },
  );
  const needsOnboarding =
    Number(onboardingState?.sites ?? 0) === 0 && !onboardingState?.dismissed;

  /*
   * Every step is derived from real state, not from a stored "completed" flag.
   * A checklist that can tell you to do something you already did is worse than
   * none, and the flag version drifts the first time a user does the thing by
   * another route.
   *
   * The list covers what Phase 1 can actually deliver. The plan's remaining
   * steps (install the tracking snippet, connect a channel, invite a teammate)
   * arrive with the tools that own them rather than sitting here greyed out.
   */
  const [progress] = await asPlatformAdmin(
    (tx) => tx.execute<{
      sites: number; audits: number; fixed: number; scheduled: number;
    }>(sql`
      select
        (select count(*)::int from sites where workspace_id = ${ws.id}) as sites,
        (select count(*)::int from audits
          where workspace_id = ${ws.id} and status = 'completed') as audits,
        (select count(*)::int from audit_issues
          where workspace_id = ${ws.id} and status = 'fixed') as fixed,
        (select count(*)::int from audit_sites
          where workspace_id = ${ws.id} and schedule <> 'manual') as scheduled`),
    { db: database },
  );

  const steps: ChecklistStep[] = [
    { key: 'site', label: 'Add a website', hint: 'One address; every tool points at it.',
      href: '/welcome', done: Number(progress?.sites ?? 0) > 0 },
    { key: 'audit', label: 'Run your first audit', hint: '72 checks, about 30 seconds.',
      href: '/audit', done: Number(progress?.audits ?? 0) > 0 },
    { key: 'fix', label: 'Resolve a finding', hint: 'Each one carries its evidence and its fix.',
      href: '/audit/issues', done: Number(progress?.fixed ?? 0) > 0 },
    { key: 'schedule', label: 'Schedule re-audits', hint: 'Catch regressions without remembering to look.',
      href: '/audit', done: Number(progress?.scheduled ?? 0) > 0 },
  ];
  const checklistDone = steps.every((s) => s.done);
  const showChecklist = !onboardingState?.dismissed && !checklistDone && !needsOnboarding;

  const tools = modules.filter((m) => m.kind === 'tool');
  const plugins = modules.filter((m) => m.kind === 'plugin');

  return (
    <>
      {needsOnboarding ? (
        <Card className="mb-8 border-[var(--color-lavender-border)] bg-[var(--accent-wash)]">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div>
              <h2 className="text-[20px] text-[var(--text-primary)]">Add your first website</h2>
              <p className="mt-1 text-[14px] text-[var(--text-secondary)]">
                One address, and Audit, Monitor and Track all point at it. Takes about ten seconds.
              </p>
            </div>
            <Link href="/welcome">
              <Button>Get started</Button>
            </Link>
          </div>
        </Card>
      ) : null}

      <PageHeader
        title={`Good to see you, ${ws.name}`}
        description="Six tools, one workspace. Phase 0 is building the shared spine — tenancy, billing, the AI registry and the interop bus are live and running against Postgres."
        action={<Button variant="ghost">View plans</Button>}
      />

      <div className="grid grid-cols-2 gap-6 sm:grid-cols-4 [&>*]:min-w-0">
        <StatTile label="Plan" value={ws.plan} hint={`${ws.allowed.length} entitlements`} />
        <StatTile label="Credits" value={ws.credits.toLocaleString()} hint="1 credit ≈ $0.01 cost" />
        <StatTile label="Features" value={counts?.features ?? 0} hint={`${counts?.ai_features ?? 0} AI-gated`} />
        <StatTile label="Tables" value={counts?.tables ?? 0} hint="all tenant-scoped or exempt" />
      </div>

      <Divider />

      <SectionLabel>Tools</SectionLabel>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 [&>*]:min-w-0">
        {TOOL_NAV.map((tool) => {
          const mod = tools.find((m) => m.key === tool.key);
          const live = Boolean(mod?.installed && mod?.enabled);
          return (
            <Card key={tool.key}>
              <div className="flex items-start justify-between gap-3">
                <Link href={tool.href} className="text-[20px] text-[var(--text-primary)] hover:text-[var(--accent)]">
                  {tool.label}
                </Link>
                <StatusBadge status={live ? 'info' : 'neutral'}>
                  {live ? 'Installed' : 'Not installed'}
                </StatusBadge>
              </div>
              <p className="mt-2 text-[14px] leading-[1.4] text-[var(--text-secondary)]">
                {tool.description}
              </p>
              <p className="mt-4 text-[12px] text-[var(--text-faint)]">
                {tool.items.length} sections · scaffolded, not yet built
              </p>
            </Card>
          );
        })}
      </div>

      <Divider />

      <div className="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] [&>*]:min-w-0">
        <div>
          <SectionLabel>Platform health</SectionLabel>
          <Table>
            <thead>
              <tr>
                <Th>Subsystem</Th>
                <Th>Status</Th>
                <Th align="right">Detail</Th>
              </tr>
            </thead>
            <tbody>
              <Tr>
                <Td>Event bus relay</Td>
                <Td>
                  <StatusBadge status={relay.oldestSeconds > 30 ? 'warn' : 'ok'}>
                    {relay.oldestSeconds > 30 ? 'Lagging' : 'Healthy'}
                  </StatusBadge>
                </Td>
                <Td align="right" muted>
                  {relay.pending} pending · {relay.failed} failed
                </Td>
              </Tr>
              <Tr>
                <Td>Tenant isolation</Td>
                <Td><StatusBadge status="ok">Enforced</StatusBadge></Td>
                <Td align="right" muted>RLS on every tenant table</Td>
              </Tr>
              <Tr>
                <Td>Entitlement resolver</Td>
                <Td><StatusBadge status="ok">Live</StatusBadge></Td>
                <Td align="right" muted>{counts?.plans ?? 0} plans seeded</Td>
              </Tr>
              <Tr>
                <Td>AI registry</Td>
                <Td><StatusBadge status="ok">Live</StatusBadge></Td>
                <Td align="right" muted>{counts?.models ?? 0} models</Td>
              </Tr>
              <Tr>
                <Td>Credit ledger</Td>
                <Td><StatusBadge status="ok">Live</StatusBadge></Td>
                <Td align="right" muted>hold → capture / release</Td>
              </Tr>
            </tbody>
          </Table>
        </div>

        <div>
          <SectionLabel>Plugins</SectionLabel>
          <Card padded={false}>
            <ul>
              {plugins.map((p) => (
                <li
                  key={p.key}
                  className="flex items-center justify-between border-b border-[var(--border-hairline)] px-4 py-2.5 text-[14px] last:border-b-0"
                >
                  <span className="capitalize">{p.key.replace(/-/g, ' ')}</span>
                  <StatusBadge status={p.enabled ? 'ok' : 'neutral'}>
                    {p.enabled ? 'On' : 'Off'}
                  </StatusBadge>
                </li>
              ))}
            </ul>
          </Card>
        </div>
      </div>

      {showChecklist ? (
        <SetupChecklist
          steps={steps}
          onDismiss={async () => {
            'use server';
            await dismissOnboarding();
          }}
        />
      ) : null}
    </>
  );
}
