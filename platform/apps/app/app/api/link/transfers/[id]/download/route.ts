import { asPlatformAdmin } from '@mamal/db';
import { sql } from 'drizzle-orm';
import { resolveAdapter } from '@mamal/storage';
import { claimDownload, downloadUrl } from '@mamal/tool-link';
import { db } from '@/lib/db';

/**
 * Claims a download.
 *
 * The claim and the count are one statement inside `claimDownload`, so two
 * recipients clicking at the same moment on a transfer with one download left
 * cannot both succeed. This route only translates that decision into an HTTP
 * answer.
 *
 * It does not stream bytes. The file lives in object storage and the client is
 * sent a short-lived signed URL, so a 5 GB download never occupies a Node
 * process — the origin decides *whether*, the storage layer does *how*.
 */

export async function POST(request: Request, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const form = await request.formData().catch(() => null);
  const password = form ? String(form.get('password') ?? '') : undefined;
  const fileId = form ? (form.get('fileId') ? String(form.get('fileId')) : null) : null;
  const back = request.headers.get('referer') ?? '/';

  const outcome = await asPlatformAdmin(
    async (tx) => {
      const decision = await claimDownload(tx, { transferId: id, password });
      if (!decision.ok) return decision;

      /*
       * The claim and the URL are minted in one transaction, but they are two
       * separate decisions: `claimDownload` counts the download and enforces
       * the limit, this hands out somewhere to fetch from. Keeping them apart
       * is what stops a saved URL from being a way around the counting — a
       * fresh URL requires a fresh claim.
       */
      const [row] = await tx.execute<{ workspace_id: string }>(sql`
        select workspace_id from transfers where id = ${id}`);
      if (!row) return { ok: false as const, reason: 'not_found' as const };

      const storage = await resolveAdapter(tx, row.workspace_id);
      const files = await downloadUrl(tx, storage, { transferId: id, fileId: fileId ?? undefined });
      return { ok: true as const, files };
    },
    { db: db() },
  );

  if (!outcome.ok) {
    // The reason travels in the fragment so the page can say which one it was
    // without leaking it into a server log or a referrer header.
    return seeOther(`${back}#${outcome.reason}`);
  }

  if (outcome.files.length === 0) return seeOther(`${back}#nothing-uploaded`);

  /*
   * One file: straight to the object store, so the bytes never pass through
   * this process. That is the difference between a transfer product that scales
   * and one where a 5 GB download occupies a Node worker for ten minutes.
   */
  if (outcome.files.length === 1) return seeOther(outcome.files[0]!.url);

  /*
   * Several files: the share page lists them, each with its own signed URL, and
   * the browser downloads them individually.
   *
   * Not a streamed zip — deliberately. Zipping means every byte travels through
   * the origin and out again, which is the one shape of egress the free tier
   * cannot absorb (§0.6). The trade is stated rather than hidden: a "download
   * all" that quietly costs us twice the bandwidth per transfer is a line item
   * nobody notices until the invoice.
   */
  return seeOther(`${back}#ready`);
}

function seeOther(location: string): Response {
  return new Response(null, {
    status: 303,
    headers: { location, 'cache-control': 'no-store' },
  });
}
