import Link from 'next/link';
import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { WIDGET_CATEGORIES, widgetsIn, widgetDef } from '@mamal/widget-catalog';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { InstallSnippet, WidgetPicker, WidgetRow } from './client';

export const dynamic = 'force-dynamic';

export default async function CampaignPage({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { id } = await params;

  const [campaign, widgets, conversions] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{
        id: string; name: string; host: string; pixel_key: string; is_enabled: boolean;
        impressions: number; clicks: number;
      }>(sql`
        select c.id, c.name, s.host, c.pixel_key, c.is_enabled, c.impressions, c.clicks
          from confirm_campaigns c join sites s on s.id = c.site_id
         where c.id = ${id} and c.workspace_id = ${ws}`))[0],

      await tx.execute<{
        id: string; type: string; name: string; is_enabled: boolean; position: string;
        impressions: number; clicks: number; submissions: number;
      }>(sql`
        select id, type, name, is_enabled, position, impressions, clicks, submissions
          from confirm_widgets where campaign_id = ${id} order by sort_order, created_at`),

      await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from confirm_conversions
         where campaign_id = ${id} and occurred_at > now() - interval '30 days'`),
    ],
    { db: db() },
  );

  if (!campaign) notFound();

  const categories = WIDGET_CATEGORIES.map((c) => ({
    key: c,
    types: widgetsIn(c).map((w) => ({ key: w.key, label: w.label, description: w.description })),
  }));

  return (
    <>
      <PageHeader
        title={campaign.name}
        description={`Notifications on ${campaign.host}. ${conversions[0]?.n ?? 0} conversions in the last 30 days.`}
        action={<WidgetPicker campaignId={campaign.id} categories={categories} />}
      />

      <div className="grid gap-6 lg:grid-cols-[1fr_20rem] [&>*]:min-w-0">
        <div className="min-w-0">
          <SectionLabel>Notifications</SectionLabel>
          {widgets.length === 0 ? (
            <EmptyState
              title="No notifications yet"
              description="Add one and it appears on your site within a minute — the config is edge-cached, not compiled."
            />
          ) : (
            <div className="space-y-2">
              {widgets.map((w) => (
                <WidgetRow
                  key={w.id}
                  widget={{ ...w, label: widgetDef(w.type)?.label ?? w.type }}
                  campaignId={campaign.id}
                />
              ))}
            </div>
          )}
        </div>

        <div className="min-w-0 space-y-6">
          <div>
            <SectionLabel>Install</SectionLabel>
            <InstallSnippet pixelKey={campaign.pixel_key} host={campaign.host} />
          </div>

          <div>
            <SectionLabel>This month</SectionLabel>
            <Card>
              <dl className="space-y-3 text-[13px]">
                {[
                  ['Impressions', campaign.impressions],
                  ['Clicks', campaign.clicks],
                  ['Conversions · 30d', conversions[0]?.n ?? 0],
                ].map(([label, value]) => (
                  <div key={String(label)} className="flex items-baseline justify-between gap-3">
                    <dt className="text-[var(--text-muted)]">{label}</dt>
                    <dd className="tabular-nums text-[var(--text-primary)]">
                      {Number(value).toLocaleString()}
                    </dd>
                  </div>
                ))}
              </dl>
            </Card>
          </div>

          <div>
            <SectionLabel>Status</SectionLabel>
            <Card>
              <div className="flex items-center justify-between gap-3">
                <StatusBadge status={campaign.is_enabled ? 'ok' : 'neutral'}>
                  {campaign.is_enabled ? 'Live' : 'Paused'}
                </StatusBadge>
                <Link href="/confirm/leads">
                  <Button size="sm" variant="quiet">Leads</Button>
                </Link>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
}
