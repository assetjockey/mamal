import { notFound, redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { BLOCK_CATALOG, BLOCK_CATEGORIES, blockDef, fieldsFor } from '@mamal/link-catalog';
import { shortUrl } from '@mamal/tool-link';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { Builder } from './builder';

export const dynamic = 'force-dynamic';

export default async function BioBuilder({ params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  const { id } = await params;

  const [page, blocks] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{
        id: string; alias: string; title: string | null; template: string;
        is_published: boolean; theme: Record<string, unknown>;
      }>(sql`
        select p.id, l.alias, l.title, p.template, p.is_published, p.theme
          from bio_pages p join links l on l.id = p.link_id
         where p.id = ${id} and p.workspace_id = ${ws} and l.deleted_at is null`))[0],

      await tx.execute<{
        id: string; type: string; settings: Record<string, unknown>;
        sort_order: number; is_enabled: boolean; clicks: number;
      }>(sql`
        select id, type, settings, sort_order, is_enabled, clicks
          from bio_blocks where page_id = ${id} order by sort_order, created_at`),
    ],
    { db: db() },
  );

  // A cross-tenant read answers 404, not 403. Existence is information.
  if (!page) notFound();

  /*
   * The whole catalogue goes down as plain data.
   *
   * 84 types with their zod schemas would ship the validator to the browser to
   * draw a form. `fieldsFor` runs here and the builder renders inputs from the
   * result — the same mechanism the widget editor and the QR studio use, and
   * the reason a new block type gets a working editor with no UI written for it.
   */
  const palette = BLOCK_CATEGORIES.map((category) => ({
    category,
    items: BLOCK_CATALOG.filter((b) => b.category === category).map((b) => ({
      key: b.key, label: b.label, family: b.family,
    })),
  }));

  return (
    <>
      <PageHeader title={page.title ?? `/${page.alias}`} description={shortUrl(page.alias)} />
      <Builder
        page={{
          id: page.id,
          alias: page.alias,
          title: page.title,
          isPublished: page.is_published,
          url: shortUrl(page.alias),
        }}
        palette={palette}
        blocks={blocks.map((b) => {
          const def = blockDef(b.type);
          return {
            id: b.id,
            type: b.type,
            label: def?.label ?? b.type,
            family: def?.family ?? 'custom',
            settings: b.settings,
            // A block whose type has left the catalogue keeps its row and shows
            // no form, rather than crashing the builder.
            fields: def ? fieldsFor(def.settings, def.defaults) : [],
            isEnabled: b.is_enabled,
            clicks: Number(b.clicks),
          };
        })}
      />
    </>
  );
}
