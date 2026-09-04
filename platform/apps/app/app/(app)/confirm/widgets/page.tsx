import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { widgetDef } from '@mamal/widget-catalog';
import { Button, EmptyState, PageHeader, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

export const dynamic = 'force-dynamic';

export default async function WidgetsPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const rows = await withWorkspace(
    ws,
    (tx) => tx.execute<{
      id: string; type: string; name: string; is_enabled: boolean; position: string;
      impressions: number; clicks: number; submissions: number;
      campaign: string; campaign_id: string;
    }>(sql`
      select w.id, w.type, w.name, w.is_enabled, w.position,
             w.impressions, w.clicks, w.submissions,
             c.name as campaign, c.id as campaign_id
        from confirm_widgets w
        join confirm_campaigns c on c.id = w.campaign_id
       where w.workspace_id = ${ws}
       order by c.name, w.sort_order, w.created_at`),
    { db: db() },
  );

  if (rows.length === 0) {
    return (
      <>
        <PageHeader title="Notifications" description="Every notification across your campaigns." />
        <EmptyState
          title="Nothing here yet"
          description="Notifications live inside a campaign — one campaign per website."
          action={<Link href="/confirm"><Button>Go to campaigns</Button></Link>}
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Notifications"
        description={`${rows.length} across ${new Set(rows.map((r) => r.campaign_id)).size} campaign(s).`}
      />

      <Table label="Notifications">
        <thead>
          <Tr>
            <Th>Name</Th>
            <Th>Type</Th>
            <Th>Campaign</Th>
            <Th align="right">Shown</Th>
            <Th align="right">CTR</Th>
            <Th>Status</Th>
          </Tr>
        </thead>
        <tbody>
          {rows.map((r) => (
            <Tr key={r.id}>
              <Td>
                <Link
                  href={`/confirm/widgets/${r.id}`}
                  className="text-[var(--text-primary)] hover:text-[var(--accent)]"
                >
                  {r.name}
                </Link>
              </Td>
              <Td>{widgetDef(r.type)?.label ?? r.type}</Td>
              <Td>
                <Link
                  href={`/confirm/campaigns/${r.campaign_id}`}
                  className="hover:text-[var(--accent)]"
                >
                  {r.campaign}
                </Link>
              </Td>
              <Td align="right">{r.impressions.toLocaleString()}</Td>
              <Td align="right">
                {r.impressions > 0 ? `${((r.clicks / r.impressions) * 100).toFixed(1)}%` : '—'}
              </Td>
              <Td>
                <StatusBadge status={r.is_enabled ? 'ok' : 'neutral'}>
                  {r.is_enabled ? 'Live' : 'Off'}
                </StatusBadge>
              </Td>
            </Tr>
          ))}
        </tbody>
      </Table>
    </>
  );
}
