import Link from 'next/link';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  Button, Card, EmptyState, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { EnablePush, PushInstall } from './client';

export const dynamic = 'force-dynamic';

export default async function PushPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [sites, subscribers, campaigns, breakdown] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; host: string; vapid_public_key: string; is_enabled: boolean;
        active: number; expired: number; site_id: string;
      }>(sql`
        select pw.id, s.host, pw.vapid_public_key, pw.is_enabled, pw.site_id,
               (select count(*)::int from push_subscribers p
                 where p.push_website_id = pw.id and p.status = 'active') as active,
               (select count(*)::int from push_subscribers p
                 where p.push_website_id = pw.id and p.status <> 'active') as expired
          from push_websites pw join sites s on s.id = pw.site_id
         where pw.workspace_id = ${ws} order by s.host`),

      await tx.execute<{
        id: string; endpoint: string; country: string | null; browser: string | null;
        device: string | null; tags: string[]; status: string; subscribed_at: string;
      }>(sql`
        select id, endpoint, country, browser, device, tags, status, subscribed_at
          from push_subscribers where workspace_id = ${ws}
         order by subscribed_at desc limit 50`),

      await tx.execute<{
        id: string; title: string; status: string; sent: number; failed: number;
        scheduled_at: string | null; sent_at: string | null;
      }>(sql`
        select id, title, status, sent, failed, scheduled_at, sent_at
          from push_campaigns where workspace_id = ${ws}
         order by created_at desc limit 20`),

      await tx.execute<{ browser: string | null; n: number }>(sql`
        select browser, count(*)::int as n from push_subscribers
         where workspace_id = ${ws} and status = 'active'
         group by browser order by n desc limit 5`),
    ],
    { db: db() },
  );

  // Sites that could have push but do not yet.
  const candidates = await withWorkspace(
    ws,
    (tx) => tx.execute<{ id: string; host: string; project_id: string }>(sql`
      select s.id, s.host, s.project_id from sites s
       where s.workspace_id = ${ws} and s.deleted_at is null
         and not exists (select 1 from push_websites pw where pw.site_id = s.id)
       order by s.host`),
    { db: db() },
  );

  if (sites.length === 0) {
    return (
      <>
        <PageHeader
          title="Push"
          description="Reach people after they have left your site — but only the ones who asked to hear from you."
        />
        <EmptyState
          title="Push is not set up"
          description={
            candidates.length > 0
              ? 'Turning it on mints a signing key for the site and gives you a service worker to host.'
              : 'Add a website first.'
          }
          action={
            candidates.length > 0
              ? <EnablePush sites={candidates} />
              : <Link href="/welcome"><Button>Add a website</Button></Link>
          }
        />
      </>
    );
  }

  const totalActive = sites.reduce((n, s) => n + s.active, 0);

  return (
    <>
      <PageHeader
        title="Push"
        description={`${totalActive.toLocaleString()} people are subscribed across ${sites.length} site(s).`}
        action={candidates.length > 0 ? <EnablePush sites={candidates} /> : undefined}
      />

      <div className="grid gap-6 lg:grid-cols-[1fr_20rem] [&>*]:min-w-0">
        <div className="min-w-0 space-y-6">
          <div>
            <SectionLabel>Recent subscribers</SectionLabel>
            {subscribers.length === 0 ? (
              <EmptyState
                title="Nobody has subscribed yet"
                description="Subscribers appear once the service worker is hosted and someone grants permission."
              />
            ) : (
              <Table label="Subscribers">
                <thead>
                  <Tr>
                    <Th>Browser</Th>
                    <Th>Country</Th>
                    <Th>Device</Th>
                    <Th>Tags</Th>
                    <Th>Subscribed</Th>
                    <Th>Status</Th>
                  </Tr>
                </thead>
                <tbody>
                  {subscribers.map((s) => (
                    <Tr key={s.id}>
                      <Td>{s.browser ?? '—'}</Td>
                      <Td>{s.country ?? '—'}</Td>
                      <Td>{s.device ?? '—'}</Td>
                      <Td>{s.tags?.length ? s.tags.join(', ') : '—'}</Td>
                      <Td>{new Date(s.subscribed_at).toLocaleDateString()}</Td>
                      <Td>
                        <StatusBadge status={s.status === 'active' ? 'ok' : 'neutral'}>
                          {s.status}
                        </StatusBadge>
                      </Td>
                    </Tr>
                  ))}
                </tbody>
              </Table>
            )}
          </div>

          {campaigns.length > 0 ? (
            <div>
              <SectionLabel>Campaigns</SectionLabel>
              <Table label="Push campaigns">
                <thead>
                  <Tr>
                    <Th>Title</Th>
                    <Th align="right">Sent</Th>
                    <Th align="right">Failed</Th>
                    <Th>When</Th>
                    <Th>Status</Th>
                  </Tr>
                </thead>
                <tbody>
                  {campaigns.map((c) => (
                    <Tr key={c.id}>
                      <Td>{c.title}</Td>
                      <Td align="right">{c.sent.toLocaleString()}</Td>
                      <Td align="right">{c.failed.toLocaleString()}</Td>
                      <Td>
                        {c.sent_at
                          ? new Date(c.sent_at).toLocaleString()
                          : c.scheduled_at
                            ? `Scheduled ${new Date(c.scheduled_at).toLocaleString()}`
                            : '—'}
                      </Td>
                      <Td>
                        <StatusBadge
                          status={
                            c.status === 'sent' ? 'ok' : c.status === 'failed' ? 'error' : 'neutral'
                          }
                        >
                          {c.status}
                        </StatusBadge>
                      </Td>
                    </Tr>
                  ))}
                </tbody>
              </Table>
            </div>
          ) : null}
        </div>

        <div className="min-w-0 space-y-6">
          {sites.map((s) => (
            <div key={s.id}>
              <SectionLabel>{s.host}</SectionLabel>
              <PushInstall publicKey={s.vapid_public_key} host={s.host} />
              <Card className="mt-3">
                <dl className="space-y-2 text-[13px]">
                  <div className="flex items-baseline justify-between gap-3">
                    <dt className="text-[var(--text-muted)]">Subscribed</dt>
                    <dd className="tabular-nums text-[var(--text-primary)]">
                      {s.active.toLocaleString()}
                    </dd>
                  </div>
                  <div className="flex items-baseline justify-between gap-3">
                    <dt className="text-[var(--text-muted)]">Retired</dt>
                    <dd className="tabular-nums text-[var(--text-muted)]">
                      {s.expired.toLocaleString()}
                    </dd>
                  </div>
                </dl>
                {s.expired > 0 ? (
                  <p className="mt-3 text-[11px] leading-[1.5] text-[var(--text-faint)]">
                    Retired endpoints are browsers that were uninstalled or had permission revoked.
                    They are excluded from every campaign, so your delivery rate stays truthful.
                  </p>
                ) : null}
              </Card>
            </div>
          ))}

          {breakdown.length > 0 ? (
            <div>
              <SectionLabel>By browser</SectionLabel>
              <Card>
                <dl className="space-y-2 text-[13px]">
                  {breakdown.map((b) => (
                    <div key={b.browser ?? 'unknown'} className="flex items-baseline justify-between gap-3">
                      <dt className="text-[var(--text-muted)]">{b.browser ?? 'Unknown'}</dt>
                      <dd className="tabular-nums text-[var(--text-primary)]">{b.n}</dd>
                    </div>
                  ))}
                </dl>
              </Card>
            </div>
          ) : null}
        </div>
      </div>
    </>
  );
}
