import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { QR_CATEGORIES, fieldsFor, qrTypesIn, type Field } from '@mamal/link-catalog';
import { shortUrl } from '@mamal/tool-link';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Studio } from './studio';

export const dynamic = 'force-dynamic';

export default async function QrPage() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const [codes, limit, serverRender] = await withWorkspace(
    ws,
    async (tx) => [
      await tx.execute<{
        id: string; type: string; name: string; payload: Record<string, unknown>;
        style: Record<string, unknown>; scans: number; alias: string | null;
      }>(sql`
        select q.id, q.type, q.name, q.payload, q.style, q.scans, l.alias
          from qr_codes q
          left join links l on l.id = q.link_id
         where q.workspace_id = ${ws} and q.deleted_at is null
         order by q.created_at desc limit 100`),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.qr_codes');
        if (!ctx) return null;
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from qr_codes
           where workspace_id = ${ws} and deleted_at is null`);
        const used = n?.count ?? 0;
        const d = resolve({ ...ctx, used }, 1);
        return { used, max: d.limit ?? null, allowed: d.allowed, why: d.allowed ? null : d.message };
      })(),

      await (async () => {
        const ctx = await loadContext(tx, ws, 'link.qr_server_render');
        return ctx ? resolve({ ...ctx, used: 0 }, 1).allowed : false;
      })(),
    ],
    { db: db() },
  );

  /*
   * The catalogue is passed down as plain data, not imported by the client.
   *
   * 35 types with their Zod schemas would ship the whole of Zod to the browser
   * to render a form. The field list is derived here, on the server, and the
   * editor renders inputs from it — the same trick the widget editor uses.
   */
  const types = QR_CATEGORIES.map((category) => ({
    category,
    items: qrTypesIn(category).map((q) => ({
      key: q.key,
      label: q.label,
      description: q.description,
      addressing: q.addressing,
      fields: fieldsFor(q.payload),
    })),
  }));

  return (
    <>
      <PageHeader
        title="QR studio"
        description="Dynamic codes resolve through a short link, so the destination stays editable after ten thousand posters are printed. Static codes encode their payload directly and cannot be changed."
      />
      <Studio
        types={types}
        codes={codes.map((c) => ({
          id: c.id,
          type: c.type,
          name: c.name,
          payload: c.payload,
          style: c.style,
          scans: Number(c.scans),
          url: c.alias ? shortUrl(c.alias) : null,
        }))}
        limit={limit}
        serverRenderAllowed={serverRender}
      />
    </>
  );
}

export type QrField = Field;
