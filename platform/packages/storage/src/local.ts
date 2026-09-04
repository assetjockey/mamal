import { createHash, createHmac, timingSafeEqual } from 'node:crypto';
import { mkdir, readdir, readFile, rm, stat, writeFile } from 'node:fs/promises';
import { createWriteStream } from 'node:fs';
import { dirname, join, resolve, sep } from 'node:path';
import { pipeline } from 'node:stream/promises';
import { Readable } from 'node:stream';
import { StorageError, type StorageAdapter } from './types.ts';

/**
 * Disk-backed storage.
 *
 * Not a stub. `storage_providers.handler = 'local'` is a real option an
 * operator can choose — a single box with a big disk is the right answer for a
 * self-hosted instance — and it is what makes the transfers flow runnable in
 * development without cloud credentials.
 *
 * It has no presigning service of its own, so it signs its own URLs with an
 * HMAC and serves them through a route. The signature covers the key *and* the
 * expiry, so a URL cannot be edited into a different object or a longer life.
 */

export type LocalOptions = {
  /** Everything lives under here. Nothing may escape it. */
  root: string;
  /** Secret for URL signatures. */
  secret: string;
  /** Where the serving route lives, e.g. `https://app.example.com/api/storage`. */
  baseUrl: string;
};

export function localAdapter(options: LocalOptions): StorageAdapter {
  /*
   * Every path is resolved and then checked against the root.
   *
   * A storage key reaches this from a request. `join(root, '../../etc/passwd')`
   * resolves outside the root, and the check below is the only thing between
   * that and reading it. Validating the key with a regex instead would be
   * validating the *spelling* of an attack rather than its effect.
   */
  const safe = (key: string): string => {
    const full = resolve(options.root, key);
    const rootWithSep = resolve(options.root) + sep;
    if (full !== resolve(options.root) && !full.startsWith(rootWithSep)) {
      throw new StorageError('bad_key', 'That storage key resolves outside the store.');
    }
    return full;
  };

  const partsDir = (key: string) => safe(`.parts/${createHash('sha256').update(key).digest('hex')}`);

  return {
    handler: 'local',

    async begin(key) {
      await mkdir(partsDir(key), { recursive: true });
      return { uploadId: 'local', storageKey: key };
    },

    async partUrl(handle, partNumber, expiresIn) {
      return sign(options, 'PUT', handle.storageKey, expiresIn, { part: String(partNumber) });
    },

    async complete(handle, parts) {
      const dir = partsDir(handle.storageKey);
      const present = await readdir(dir).catch(() => [] as string[]);
      const missing = parts
        .map((p) => p.partNumber)
        .filter((n) => !present.includes(String(n)));
      if (missing.length > 0) {
        // Assembling anyway would produce a file that opens and is wrong, which
        // is worse than one that never appeared.
        throw new StorageError(
          'incomplete',
          `Cannot assemble: part${missing.length === 1 ? '' : 's'} ${missing.join(', ')} never arrived.`,
        );
      }

      const target = safe(handle.storageKey);
      await mkdir(dirname(target), { recursive: true });

      // Streamed in part order, never all read into memory: the whole point of
      // multipart is that the file is bigger than the process.
      const out = createWriteStream(target);
      for (const part of [...parts].sort((a, b) => a.partNumber - b.partNumber)) {
        const chunk = await readFile(join(dir, String(part.partNumber)));
        await pipeline(Readable.from(chunk), out, { end: false });
      }
      await new Promise<void>((res, rej) => out.end((e: Error | null) => (e ? rej(e) : res())));

      await rm(dir, { recursive: true, force: true });
      return { size: (await stat(target)).size };
    },

    async abort(handle) {
      await rm(partsDir(handle.storageKey), { recursive: true, force: true });
    },

    async readUrl(key, expiresIn, downloadName) {
      return sign(options, 'GET', key, expiresIn, downloadName ? { name: downloadName } : {});
    },

    async delete(key) {
      await rm(safe(key), { force: true });
    },

    async head(key) {
      const s = await stat(safe(key)).catch(() => null);
      return s ? { size: s.size } : null;
    },
  };
}

/* ------------------------------------------------------------------ signing */

function sign(
  options: LocalOptions,
  method: string,
  key: string,
  expiresIn: number,
  extra: Record<string, string>,
): string {
  const expires = String(Math.floor(Date.now() / 1000) + expiresIn);
  const params = new URLSearchParams({ ...extra, key, expires });
  params.set('sig', signature(options.secret, method, params));
  return `${options.baseUrl.replace(/\/+$/, '')}?${params.toString()}`;
}

/**
 * Verifies a signed URL.
 *
 * Returns the key only when the signature covers *these* parameters and the
 * expiry has not passed. The comparison is constant-time: this runs on a public
 * route, and a length-varying compare leaks the prefix under repeated guessing.
 */
export function verifySignature(
  secret: string,
  method: string,
  params: URLSearchParams,
): { ok: true; key: string; part: number | null; name: string | null } | { ok: false; reason: string } {
  const presented = params.get('sig');
  const key = params.get('key');
  const expires = Number(params.get('expires'));
  if (!presented || !key || !Number.isFinite(expires)) {
    return { ok: false, reason: 'malformed' };
  }
  if (expires <= Math.floor(Date.now() / 1000)) return { ok: false, reason: 'expired' };

  const unsigned = new URLSearchParams(params);
  unsigned.delete('sig');
  const expected = signature(secret, method, unsigned);

  const a = Buffer.from(expected);
  const b = Buffer.from(presented);
  if (a.length !== b.length || !timingSafeEqual(a, b)) return { ok: false, reason: 'bad_signature' };

  const part = params.get('part');
  return { ok: true, key, part: part ? Number(part) : null, name: params.get('name') };
}

function signature(secret: string, method: string, params: URLSearchParams): string {
  // Sorted, so a client reordering the query cannot change what was signed.
  const sorted = [...params.entries()].sort(([a], [b]) => a.localeCompare(b));
  const canonical = `${method}\n${sorted.map(([k, v]) => `${k}=${v}`).join('&')}`;
  return createHmac('sha256', secret).update(canonical).digest('base64url');
}

/** Writes one part. Called by the serving route, never by a client directly. */
export async function writeLocalPart(
  options: LocalOptions,
  key: string,
  partNumber: number,
  body: Buffer,
): Promise<{ etag: string }> {
  const full = resolve(options.root, key);
  const rootWithSep = resolve(options.root) + sep;
  if (!full.startsWith(rootWithSep)) {
    throw new StorageError('bad_key', 'That storage key resolves outside the store.');
  }
  const dir = resolve(options.root, `.parts/${createHash('sha256').update(key).digest('hex')}`);
  await mkdir(dir, { recursive: true });
  await writeFile(join(dir, String(partNumber)), body);
  return { etag: createHash('md5').update(body).digest('hex') };
}

/** Reads an assembled object. */
export async function readLocalObject(options: LocalOptions, key: string): Promise<Buffer | null> {
  const full = resolve(options.root, key);
  const rootWithSep = resolve(options.root) + sep;
  if (!full.startsWith(rootWithSep)) return null;
  return readFile(full).catch(() => null);
}
