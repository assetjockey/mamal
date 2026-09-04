import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { AddConversion } from './client';

export const dynamic = 'force-dynamic';

/** Which sources are honest proof, and which are the operator's own word. */
const SOURCE_LABEL: Record<string, { label: string; verified: boolean }> = {
  bus: { label: 'Another tool', verified: true },
  webhook: { label: 'Webhook', verified: true },
  api: { label: 'API', verified: true },
  form_capture: { label: 'Form', verified: true },
  automation: { label: 'Automation', verified: true },
  manual: { label: 'Entered by hand', verified: false },
  csv: { label: 'Imported', verified: false },
};

export default async function LeadsPage({
  searchParams,
}: { searchParams: Promise<{ campaign?: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { campaign } = await searchParams;

  const [rows, campaigns, bySource] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; source: string; type: string; data: Record<string, unknown>;
        country: string | null; path: string | null; source_urn: string | null;
        occurred_at: string; campaign_name: string;
      }>(sql`
        select v.id, v.source, v.type, v.data, v.country, v.path, v.source_urn,
               v.occurred_at, c.name as campaign_name
          from confirm_conversions v
          join confirm_campaigns c on c.id = v.campaign_id
         where v.workspace_id = ${ws}
           ${campaign ? sql`and v.campaign_id = ${campaign}` : sql``}
         order by v.occurred_at desc
         limit 100`),

      await tx.execute<{ id: string; name: string }>(sql`
        select id, name from confirm_campaigns where workspace_id = ${ws} order by name`),

      await tx.execute<{ source: string; n: number }>(sql`
        select source, count(*)::int as n from confirm_conversions
         where workspace_id = ${ws} and occurred_at > now() - interval '30 days'
         group by source order by n desc`),
    ],
    { db: db() },
  );

  if (campaigns.length === 0) {
    return (
      <>
        <PageHeader title="Conversions" description="The pool your proof notifications draw from." />
        <EmptyState
          title="No campaigns yet"
          description="Conversions belong to a campaign — one per website."
          action={<Link href="/confirm"><Button>Go to campaigns</Button></Link>}
        />
      </>
    );
  }

  const verified = bySource.filter((s) => SOURCE_LABEL[s.source]?.verified).reduce((n, s) => n + s.n, 0);
  const manual = bySource.filter((s) => !SOURCE_LABEL[s.source]?.verified).reduce((n, s) => n + s.n, 0);

  return (
    <>
      <PageHeader
        title="Conversions"
        description="Every notification that says something happened draws from this list. Nothing here is invented."
        action={<AddConversion campaigns={campaigns} />}
      />

      <div className="grid gap-6 lg:grid-cols-[1fr_18rem] [&>*]:min-w-0">
        <div className="min-w-0">
          <SectionLabel>Recent</SectionLabel>
          {rows.length === 0 ? (
            <EmptyState
              title="Nothing yet"
              description="Connect a source, or add one by hand to see how a notification will read."
            />
          ) : (
            <Table label="Conversions">
              <thead>
                <Tr>
                  <Th>Who</Th>
                  <Th>What</Th>
                  <Th>Where</Th>
                  <Th>Source</Th>
                  <Th>When</Th>
                </Tr>
              </thead>
              <tbody>
                {rows.map((r) => {
                  const name = String(r.data.name ?? '').split(' ')[0] || '—';
                  const city = String(r.data.city ?? '');
                  const src = SOURCE_LABEL[r.source] ?? { label: r.source, verified: false };
                  return (
                    <Tr key={r.id}>
                      <Td>{name}</Td>
                      <Td>{r.type}</Td>
                      <Td>{[city, r.country].filter(Boolean).join(', ') || '—'}</Td>
                      <Td>
                        <StatusBadge status={src.verified ? 'ok' : 'warn'}>{src.label}</StatusBadge>
                      </Td>
                      <Td>{new Date(r.occurred_at).toLocaleString()}</Td>
                    </Tr>
                  );
                })}
              </tbody>
            </Table>
          )}
        </div>

        <div className="min-w-0 space-y-6">
          <div>
            <SectionLabel>Last 30 days</SectionLabel>
            <Card>
              <dl className="space-y-2 text-[13px]">
                <div className="flex items-baseline justify-between gap-3">
                  <dt className="text-[var(--text-muted)]">From a real event</dt>
                  <dd className="tabular-nums text-[var(--text-primary)]">{verified}</dd>
                </div>
                <div className="flex items-baseline justify-between gap-3">
                  <dt className="text-[var(--text-muted)]">Entered by hand</dt>
                  <dd className="tabular-nums text-[var(--text-primary)]">{manual}</dd>
                </div>
              </dl>
              {manual > 0 ? (
                <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
                  Hand-entered conversions are shown to visitors exactly like verified ones. That is
                  your call to make — but the distinction is kept here so it is always visible to
                  you, and it is why the source column exists.
                </p>
              ) : (
                <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
                  Every conversion here came from a real event. That is the difference between
                  social proof and decoration.
                </p>
              )}
            </Card>
          </div>

          <div>
            <SectionLabel>What a visitor sees</SectionLabel>
            <Card>
              <p className="text-[12px] leading-[1.5] text-[var(--text-secondary)]">
                Only a first name, a city and a country ever reach the browser. Emails, surnames and
                order values stay here.
              </p>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
}
