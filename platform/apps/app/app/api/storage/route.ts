import { readLocalObject, verifySignature, writeLocalPart, type LocalOptions } from '@mamal/storage';

/**
 * The serving route for disk-backed storage.
 *
 * Only the `local` provider needs this: S3, R2, Wasabi and Backblaze presign
 * against their own endpoints, and the bytes never come near us. A single
 * self-hosted box has no such endpoint, so this stands in for one — same
 * contract, same signed URLs, same expiry.
 *
 * Unauthenticated by design. **The signature is the credential**: it covers the
 * method, the object key and the expiry, so a URL cannot be edited into a
 * different object or a longer life, and it stops working on its own. Requiring
 * a session instead would break the thing this exists for — a recipient with a
 * share link and no account.
 */

export const dynamic = 'force-dynamic';
/** Bytes stream; buffering a 5 GB part would be the whole point missed. */
export const maxDuration = 300;

function options(): LocalOptions {
  return {
    root: process.env.STORAGE_LOCAL_ROOT ?? './.storage',
    secret: process.env.STORAGE_URL_SECRET ?? 'dev-storage-secret',
    baseUrl: process.env.STORAGE_LOCAL_URL ?? 'http://localhost:3000/api/storage',
  };
}

export async function PUT(request: Request): Promise<Response> {
  const url = new URL(request.url);
  const verified = verifySignature(options().secret, 'PUT', url.searchParams);
  if (!verified.ok) return refuse(verified.reason);
  if (verified.part === null) return refuse('malformed');

  const body = Buffer.from(await request.arrayBuffer());
  if (body.length === 0) return refuse('malformed');

  try {
    const { etag } = await writeLocalPart(options(), verified.key, verified.part, body);
    // The ETag is what the caller records and hands back at assembly time,
    // exactly as an S3-compatible provider would return it.
    return new Response(null, { status: 200, headers: { etag: `"${etag}"`, 'cache-control': 'no-store' } });
  } catch {
    return refuse('bad_key');
  }
}

export async function GET(request: Request): Promise<Response> {
  const url = new URL(request.url);
  const verified = verifySignature(options().secret, 'GET', url.searchParams);
  if (!verified.ok) return refuse(verified.reason);

  const body = await readLocalObject(options(), verified.key);
  if (!body) return new Response('Not found', { status: 404, headers: { 'cache-control': 'no-store' } });

  return new Response(new Uint8Array(body), {
    headers: {
      'content-type': 'application/octet-stream',
      'content-length': String(body.length),
      // The stored key is random, so without this every download lands as a
      // hex string. The name is quoted and stripped of quotes to keep the
      // header parseable whatever the sender called the file.
      'content-disposition': `attachment; filename="${(verified.name ?? 'download').replace(/["\r\n]/g, '')}"`,
      // Signed and short-lived: caching it anywhere shared would outlive the
      // permission it was granted under.
      'cache-control': 'private, no-store',
    },
  });
}

/**
 * One shape for every refusal.
 *
 * `expired` is worth distinguishing — a client can ask for a fresh URL — but a
 * bad signature and a key that escapes the root answer the same, so neither
 * tells a prober which one they hit.
 */
function refuse(reason: string): Response {
  const expired = reason === 'expired';
  return Response.json(
    {
      error: {
        code: expired ? 'url_expired' : 'not_authorised',
        message: expired
          ? 'This link has expired. Ask for a new one.'
          : 'This link is not valid.',
      },
    },
    { status: expired ? 410 : 403, headers: { 'cache-control': 'no-store' } },
  );
}
