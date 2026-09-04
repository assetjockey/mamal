import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { NewCampaign } from './client';

export const dynamic = 'force-dynamic';

type Row = {
  id: string; name: string; host: string; pixel_key: string;
  is_enabled: boolean; widgets: number; live: number; conversions: number;
  impressions: number; clicks: number;
};

export default async function ConfirmPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [campaigns, limit, sites] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<Row>(sql`
        select c.id, c.name, s.host, c.pixel_key, c.is_enabled,
               c.impressions, c.clicks,
               (select count(*)::int from confirm_widgets w where w.campaign_id = c.id) as widgets,
               (select count(*)::int from confirm_widgets w
                 where w.campaign_id = c.id and w.is_enabled) as live,
               (select count(*)::int from confirm_conversions v
                 where v.campaign_id = c.id
                   and v.occurred_at > now() - interval '30 days') as conversions
          from confirm_campaigns c
          join sites s on s.id = c.site_id
         where c.workspace_id = ${ws}
         order by c.created_at`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'confirm.campaigns');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from confirm_campaigns where workspace_id = ${ws}`);
        const used = n?.count ?? 0;
        const d = resolve({ ...ctx, used }, 1);
        return { used, max: d.limit ?? d.quota ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),

      await tx.execute<{ id: string; host: string }>(sql`
        select s.id, s.host from sites s
         where s.workspace_id = ${ws} and s.deleted_at is null
           and not exists (select 1 from confirm_campaigns c where c.site_id = s.id)
         order by s.host`),
    ],
    { db: db() },
  );

  if (campaigns.length === 0) {
    return (
      <>
        <PageHeader
          title="Confirm"
          description="Show what is really happening on your site — recent sales, live visitors, reviews — without inventing any of it."
        />
        <EmptyState
          title="No campaigns yet"
          description={
            sites.length > 0
              ? 'A campaign is one site’s worth of notifications. Pick a site and add one script tag.'
              : 'Add a website first — every tool points at the same one.'
          }
          action={
            sites.length > 0 ? (
              <NewCampaign sites={sites} />
            ) : (
              <Link href="/welcome"><Button>Add a website</Button></Link>
            )
          }
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Confirm"
        description="Social proof drawn from real activity, and web push for the people who opt in."
        action={sites.length > 0 && limit?.allowed ? <NewCampaign sites={sites} /> : undefined}
      />

      {limit && limit.max !== null && limit.max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {limit.used} of {limit.max} campaign{limit.max === 1 ? '' : 's'} used.
          {!limit.allowed ? (
            <span className="text-[var(--color-status-warn)]"> {limit.why}</span>
          ) : null}
        </p>
      ) : null}

      <SectionLabel>Campaigns</SectionLabel>
      <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
        {campaigns.map((c) => (
          <Card key={c.id}>
            <div className="flex items-start justify-between gap-4">
              <div className="min-w-0">
                <h3 className="truncate text-[20px] text-[var(--text-primary)]">
                  <Link href={`/confirm/campaigns/${c.id}`} className="hover:text-[var(--accent)]">
                    {c.name}
                  </Link>
                </h3>
                <p className="mt-0.5 truncate text-[13px] text-[var(--text-faint)]">{c.host}</p>
              </div>
              <StatusBadge status={c.is_enabled ? 'ok' : 'neutral'}>
                {c.is_enabled ? 'Live' : 'Paused'}
              </StatusBadge>
            </div>

            <div className="mt-5 flex flex-wrap items-center gap-4 text-[13px] tabular-nums text-[var(--text-secondary)]">
              <span>{c.live} of {c.widgets} notifications live</span>
              <span>{c.conversions.toLocaleString()} conversions · 30d</span>
            </div>

            <div className="mt-5 flex items-center justify-between gap-3 border-t border-[var(--border-hairline)] pt-4">
              <span className="text-[12px] tabular-nums text-[var(--text-faint)]">
                {c.impressions.toLocaleString()} impressions · {c.clicks.toLocaleString()} clicks
              </span>
              <Link href={`/confirm/campaigns/${c.id}`}>
                <Button size="sm" variant="quiet">Open</Button>
              </Link>
            </div>
          </Card>
        ))}
      </div>
    </>
  );
}
