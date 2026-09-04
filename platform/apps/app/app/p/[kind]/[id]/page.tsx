import { notFound } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { blockDef } from '@mamal/link-catalog';
import { db } from '@/lib/db';
import { BioPage, type PublicBlock } from './bio';
import { TransferPage } from './transfer';
import { CardPage } from './card';

/**
 * The public renderer.
 *
 * Links that *render* rather than redirect all land here — bio pages, contact
 * cards, calendar events, transfer downloads. The redirect route decides
 * **that** a link renders; this decides what it looks like.
 *
 * Runs as platform admin because a visitor has no session and no workspace: the
 * link id in the URL is the whole lookup, and every query below is constrained
 * to the row it resolves. Nothing here trusts anything else in the request.
 */

export const dynamic = 'force-dynamic';

const KINDS = ['biolink', 'vcard', 'event', 'transfer', 'static'] as const;
type Kind = (typeof KINDS)[number];

export default async function PublicPage({
  params,
}: {
  params: Promise<{ kind: string; id: string }>;
}) {
  const { kind, id } = await params;
  if (!(KINDS as readonly string[]).includes(kind)) notFound();

  const link = await asPlatformAdmin(
    async (tx) => {
      const [row] = await tx.execute<{
        id: string; workspace_id: string; kind: string; alias: string;
        title: string | null; description: string | null; image_url: string | null;
        is_enabled: boolean; moderation_status: string;
      }>(sql`
        select id, workspace_id, kind, alias, title, description, image_url,
               is_enabled, moderation_status
          from links where id = ${id}::uuid and deleted_at is null`);
      return row ?? null;
    },
    { db: db() },
  );

  /*
   * A disabled or blocked link 404s here as well as at the redirect.
   *
   * This route is reachable directly — the redirect hands out its URL — so
   * repeating the checks is not belt-and-braces. Without them, turning a bio
   * page off would stop the short link and leave the rendered page up.
   */
  if (!link || !link.is_enabled || link.moderation_status === 'blocked') notFound();

  switch (kind as Kind) {
    case 'biolink':
      return <Bio linkId={link.id} title={link.title} />;
    case 'transfer':
      return <Transfer linkId={link.id} />;
    case 'vcard':
    case 'event':
    case 'static':
      return <CardPage kind={kind as 'vcard' | 'event' | 'static'} title={link.title} />;
  }
}

async function Bio({ linkId, title }: { linkId: string; title: string | null }) {
  const data = await asPlatformAdmin(
    async (tx) => {
      const [page] = await tx.execute<{
        id: string; template: string; theme: Record<string, string>; is_published: boolean;
      }>(sql`
        select id, template, theme, is_published from bio_pages where link_id = ${linkId}`);
      if (!page || !page.is_published) return null;

      /*
       * Scheduling is applied in the query, not the renderer.
       *
       * A block outside its window must never reach the browser — otherwise
       * "schedule a launch banner" means shipping the announcement to anyone
       * who reads the page source early.
       */
      const blocks = await tx.execute<{
        id: string; type: string; settings: Record<string, unknown>;
      }>(sql`
        select id, type, settings from bio_blocks
         where page_id = ${page.id}
           and is_enabled
           and (starts_at is null or starts_at <= now())
           and (ends_at is null or ends_at >= now())
         order by sort_order, created_at`);

      // A view is a view whether or not a block is clicked afterwards.
      await tx.execute(sql`update bio_pages set views = views + 1 where id = ${page.id}`);

      return { page, blocks };
    },
    { db: db() },
  );

  if (!data) notFound();

  const blocks: PublicBlock[] = data.blocks.flatMap((b) => {
    const def = blockDef(b.type);
    // A block whose type has left the catalogue is skipped rather than drawn as
    // an error: the visitor should see the page, not our migration state.
    return def
      ? [{ id: b.id, type: b.type, family: def.family, label: def.label, settings: b.settings }]
      : [];
  });

  return <BioPage title={title} theme={data.page.theme} blocks={blocks} />;
}

async function Transfer({ linkId }: { linkId: string }) {
  const data = await asPlatformAdmin(
    async (tx) => {
      const [t] = await tx.execute<{
        id: string; subject: string | null; message: string | null; sender_name: string | null;
        status: string; expires_at: string | null; cancelled_at: string | null;
        cancel_reason: string | null; download_limit: number; downloads: number;
        password_hash: string | null; total_files: number; total_bytes: number;
      }>(sql`
        select id, subject, message, sender_name, status, expires_at, cancelled_at,
               cancel_reason, download_limit, downloads, password_hash,
               total_files, total_bytes
          from transfers where link_id = ${linkId}::uuid`);
      if (!t) return null;

      const files = await tx.execute<{ id: string; name: string; size_bytes: number }>(sql`
        select id, name, size_bytes from transfer_files
         where transfer_id = ${t.id} order by sort_order`);
      return { t, files };
    },
    { db: db() },
  );

  if (!data) notFound();

  const { t, files } = data;
  const expired = t.expires_at ? Date.parse(t.expires_at) <= Date.now() : false;

  return (
    <TransferPage
      transfer={{
        id: t.id,
        subject: t.subject,
        message: t.message,
        senderName: t.sender_name,
        // The sender's reason is shown; "this link is dead" is the failure that
        // sends the recipient back to ask what happened.
        cancelReason: t.cancelled_at ? (t.cancel_reason ?? 'The sender cancelled this transfer.') : null,
        expired,
        exhausted: t.download_limit > 0 && t.downloads >= t.download_limit,
        needsPassword: t.password_hash !== null,
        totalFiles: t.total_files,
        totalBytes: Number(t.total_bytes),
      }}
      files={files.map((f) => ({ id: f.id, name: f.name, sizeBytes: Number(f.size_bytes) }))}
    />
  );
}
