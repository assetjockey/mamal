import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge, UpgradeGate,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { WebhookSource } from './client';

export const dynamic = 'force-dynamic';

/**
 * Where conversions come from.
 *
 * Ordered by how much they can be trusted, not alphabetically: a webhook from a
 * payment provider is a fact, a CSV is a claim. The page says which is which
 * because that difference is the whole product.
 */
const KINDS = [
  { kind: 'bus', label: 'Another Mamal tool', detail: 'Track goals, Link leads, Monitor incidents.', automatic: true },
  { kind: 'webhook', label: 'Webhook', detail: 'Your backend posts a signed payload when something happens.', automatic: false },
  { kind: 'stripe', label: 'Stripe', detail: 'Payments become proof, with nothing to build.', automatic: false },
  { kind: 'shopify', label: 'Shopify', detail: 'Orders become proof.', automatic: false },
  { kind: 'woocommerce', label: 'WooCommerce', detail: 'Orders become proof.', automatic: false },
];

export default async function SourcesPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [campaigns, sources, gate] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{ id: string; name: string; pixel_key: string }>(sql`
        select id, name, pixel_key from confirm_campaigns where workspace_id = ${ws} order by name`),
      await tx.execute<{
        id: string; kind: string; name: string; is_enabled: boolean;
        received_count: number; last_received_at: string | null; campaign_name: string;
      }>(sql`
        select s.id, s.kind, s.name, s.is_enabled, s.received_count, s.last_received_at,
               c.name as campaign_name
          from confirm_sources s
          join confirm_campaigns c on c.id = s.campaign_id
         where s.workspace_id = ${ws} order by s.created_at`),
      await (async () => {
        const ctx = await loadContext(tx, ws, 'confirm.live_sources');
        return ctx ? resolve(ctx, 1) : null;
      })(),
    ],
    { db: db() },
  );

  if (campaigns.length === 0) {
    return (
      <>
        <PageHeader title="Sources" description="Where conversions come from." />
        <EmptyState
          title="No campaigns yet"
          description="Sources feed a campaign — one per website."
          action={<Link href="/confirm"><Button>Go to campaigns</Button></Link>}
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Sources"
        description="A notification is only worth showing if something really happened. This is where that comes from."
      />

      {gate && !gate.allowed ? (
        <div className="mb-8">
          <UpgradeGate
            feature="Live conversion sources"
            reason={`${gate.message} You can still add conversions by hand and import a CSV.`}
            price="Starter includes live sources at $19 a month."
            action={<Link href="/settings/plans"><Button>Compare plans</Button></Link>}
          />
        </div>
      ) : null}

      {sources.length > 0 ? (
        <div className="mb-8">
          <SectionLabel>Connected</SectionLabel>
          <div className="space-y-2">
            {sources.map((s) => (
              <Card key={s.id}>
                <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                  <div className="min-w-0 flex-1">
                    <span className="block truncate text-[14px] text-[var(--text-primary)]">
                      {s.name}
                    </span>
                    <span className="mt-0.5 block truncate text-[12px] text-[var(--text-faint)]">
                      {s.kind} · {s.campaign_name}
                    </span>
                  </div>
                  <span className="shrink-0 text-[12px] tabular-nums text-[var(--text-muted)]">
                    {s.received_count.toLocaleString()} received
                    {s.last_received_at
                      ? ` · last ${new Date(s.last_received_at).toLocaleDateString()}`
                      : ''}
                  </span>
                  <StatusBadge status={s.is_enabled ? 'ok' : 'neutral'}>
                    {s.is_enabled ? 'Live' : 'Off'}
                  </StatusBadge>
                </div>
              </Card>
            ))}
          </div>
        </div>
      ) : null}

      <SectionLabel>Available</SectionLabel>
      <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
        {KINDS.map((k) => (
          <Card key={k.kind}>
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <h3 className="text-[16px] text-[var(--text-primary)]">{k.label}</h3>
                <p className="mt-1 text-[13px] leading-[1.5] text-[var(--text-secondary)]">
                  {k.detail}
                </p>
              </div>
              {k.automatic ? <StatusBadge status="ok">Automatic</StatusBadge> : null}
            </div>

            {k.kind === 'bus' ? (
              <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
                Already wired. A Track goal completion becomes a proof line the moment Track ships,
                carrying the id of the event that caused it so it can be traced back.
              </p>
            ) : k.kind === 'webhook' ? (
              <WebhookSource campaigns={campaigns} enabled={Boolean(gate?.allowed)} />
            ) : (
              <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
                Arrives with the {k.label} integration. Until then, the webhook below accepts the
                same data from anything that can post JSON.
              </p>
            )}
          </Card>
        ))}
      </div>
    </>
  );
}
