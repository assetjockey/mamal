import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/**
 * GET /api/confirm/widget/:id — a snapshot for the undo toast.
 *
 * Session-authenticated, not an API key: this serves the app's own UI. It
 * exists so a delete can be undone exactly, rather than recreated from
 * defaults and quietly losing whatever the user had configured.
 */
export async function GET(_request: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await getSession();
  if (!session) return new Response('Unauthorized', { status: 401 });
  const { id } = await params;

  const [row] = await withWorkspace(
    session.workspace.id,
    (tx) => tx.execute<Record<string, unknown>>(sql`
      select type, name, settings, targeting, theme, position
        from confirm_widgets where id = ${id} and workspace_id = ${session.workspace.id}`),
    { db: db() },
  );

  if (!row) return new Response('Not found', { status: 404 });
  return Response.json(row, { headers: { 'cache-control': 'no-store' } });
}
