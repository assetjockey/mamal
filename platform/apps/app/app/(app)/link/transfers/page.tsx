import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { shortUrl } from '@mamal/tool-link';
import {
  EmptyState, PageHeader, SectionLabel, StatusBadge, Table, Td, Th, Tr,
} from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { NewTransfer, PullBack } from './client';
import { Uploader } from './uploader';

export const dynamic = 'force-dynamic';

const mb = (bytes: number) => `${(bytes / 1_000_000).toFixed(bytes < 10_000_000 ? 1 : 0)} MB`;

export default async function Transfers() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [rows, limit, sizeLimit] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; subject: string | null; status: string; delivery: string;
        recipients: string[]; total_files: number; total_bytes: number;
        downloads: number; download_limit: number; expires_at: string | null;
        cancelled_at: string | null; cancel_reason: string | null; alias: string | null;
      }>(sql`
        select t.id, t.subject, t.status, t.delivery, t.recipients, t.total_files,
               t.total_bytes, t.downloads, t.download_limit, t.expires_at,
               t.cancelled_at, t.cancel_reason, l.alias
          from transfers t
          left join links l on l.id = t.link_id
         where t.workspace_id = ${ws}
         order by t.created_at desc limit 100`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.transfers');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from transfers
           where workspace_id = ${ws} and status <> 'expired'`);
        const d = resolve({ ...ctx, used: n?.count ?? 0 }, 1);
        return { used: n?.count ?? 0, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.transfer_size_mb');
        return ctx ? (resolve({ ...ctx, used: 0 }, 1).limit ?? null) : null;
      })(),
    ],
    { db: db() },
  );

  if (rows.length === 0) {
    return (
      <>
        <PageHeader title="Transfers" description="Send files without attaching them to anything." />
        <EmptyState
          title="No transfers yet"
          description={
            sizeLimit
              ? `Send up to ${sizeLimit >= 1000 ? `${(sizeLimit / 1000).toFixed(0)} GB` : `${sizeLimit} MB`} per transfer on your plan. Uploads resume where they stopped.`
              : 'Uploads resume where they stopped, and you can pull a transfer back with a reason.'
          }
          action={<NewTransfer allowed={limit?.allowed ?? true} />}
        />
      </>
    );
  }

  return (
    <>
      <PageHeader
        title="Transfers"
        description="Send files without attaching them to anything. Uploads resume where they stopped."
        action={<NewTransfer allowed={limit?.allowed ?? true} />}
      />

      {limit && limit.max !== null && limit.max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {limit.used} of {limit.max} active transfer{limit.max === 1 ? '' : 's'}.
          {sizeLimit ? ` Up to ${sizeLimit.toLocaleString()} MB each.` : ''}
          {!limit.allowed ? <span className="text-[var(--color-status-warn)]"> {limit.why}</span> : null}
        </p>
      ) : null}

      <SectionLabel>Transfers</SectionLabel>
      <Table label="Transfers">
        <thead>
          <Tr>
            <Th>Transfer</Th>
            <Th>Files</Th>
            <Th>Status</Th>
            <Th align="right">Downloads</Th>
            <Th align="right"> </Th>
          </Tr>
        </thead>
        <tbody>
          {rows.map((t) => {
            const expired = t.expires_at ? Date.parse(t.expires_at) <= Date.now() : false;
            const tone = t.cancelled_at ? 'error' : expired || t.status === 'expired' ? 'warn'
              : t.status === 'ready' ? 'ok' : 'neutral';
            const label = t.cancelled_at ? 'Pulled back'
              : expired || t.status === 'expired' ? 'Expired'
              : t.status === 'ready' ? 'Ready' : 'Uploading';

            return (
              <Tr key={t.id}>
                <Td>
                  <p className="text-[var(--text-primary)]">{t.subject ?? 'Untitled transfer'}</p>
                  <p className="mt-0.5 truncate text-[12px] text-[var(--text-faint)]">
                    {t.alias ? shortUrl(t.alias) : '—'}
                    {t.delivery === 'email' && t.recipients.length > 0
                      ? ` · to ${t.recipients.length} recipient${t.recipients.length === 1 ? '' : 's'}`
                      : ''}
                  </p>
                  {t.cancel_reason ? (
                    <p className="mt-0.5 text-[12px] text-[var(--text-muted)]">“{t.cancel_reason}”</p>
                  ) : null}
                  {t.status === 'pending' && !t.cancelled_at ? (
                    <div className="mt-3 max-w-[420px]">
                      <Uploader transferId={t.id} />
                    </div>
                  ) : null}
                </Td>
                <Td>
                  <span className="tabular-nums text-[13px] text-[var(--text-secondary)]">
                    {t.total_files} · {mb(Number(t.total_bytes))}
                  </span>
                </Td>
                <Td><StatusBadge status={tone}>{label}</StatusBadge></Td>
                <Td align="right">
                  <span className="tabular-nums">{t.downloads.toLocaleString()}</span>
                  {t.download_limit > 0 ? (
                    <span className="tabular-nums text-[var(--text-faint)]">
                      {' / '}{t.download_limit.toLocaleString()}
                    </span>
                  ) : null}
                </Td>
                <Td align="right">
                  {!t.cancelled_at && t.status === 'ready' ? (
                    <PullBack id={t.id} subject={t.subject ?? 'this transfer'} />
                  ) : null}
                </Td>
              </Tr>
            );
          })}
        </tbody>
      </Table>
    </>
  );
}
