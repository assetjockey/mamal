import { randomBytes } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import type { StorageAdapter } from '@mamal/storage';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { mint } from '@mamal/resources';
import { LinkNotAllowed, createLink, hashPassword, verifyPassword } from './service.ts';

/**
 * File transfers.
 *
 * The shape follows `swipgle`'s two good ideas — a transfer is either a *send*
 * or a *request* ("upload to me"), and delivery is either a link or an email —
 * plus `droppy`'s branded share page. What is deliberately different is where
 * the bytes go: uploads are presigned straight to object storage and this
 * module never touches file content. It accounts for it, gates it, and hands
 * out URLs.
 *
 * The resumability requirement lives in `registerPart`: a 5 GB upload over a
 * hotel wifi connection will drop, and the recovery has to be "carry on from
 * part 41", not "start again".
 */

export type TransferKind = 'send' | 'request';

export type CreateTransferInput = {
  workspaceId: string;
  projectId: string;
  kind?: TransferKind;
  delivery?: 'link' | 'email';
  senderName?: string;
  senderEmail?: string;
  recipients?: string[];
  subject?: string;
  message?: string;
  password?: string;
  encrypt?: boolean;
  expiresInDays?: number;
  downloadLimit?: number;
  branding?: Record<string, unknown>;
};

export async function createTransfer(
  tx: WorkspaceScopedDb,
  input: CreateTransferInput,
): Promise<{ id: string; linkId: string; alias: string }> {
  const ctx = await loadContext(tx, input.workspaceId, 'link.transfers');
  if (!ctx) throw new Error('link.transfers is not a known feature');
  const [counted] = await tx.execute<{ count: number }>(sql`
    select count(*)::int as count from transfers
     where workspace_id = ${input.workspaceId} and status <> 'expired'`);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);

  if (input.delivery === 'email' && (input.recipients ?? []).length === 0) {
    throw new LinkNotAllowed('no_recipients', 'Email delivery needs at least one recipient.');
  }

  const link = await createLink(tx, {
    workspaceId: input.workspaceId,
    projectId: input.projectId,
    kind: 'transfer',
    title: input.subject ?? 'File transfer',
  });

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into transfers
      (workspace_id, project_id, link_id, kind, delivery, sender_name, sender_email,
       recipients, subject, message, password_hash, is_encrypted, expires_at,
       download_limit, branding)
    values (${input.workspaceId}, ${input.projectId}, ${link.id},
            ${input.kind ?? 'send'}, ${input.delivery ?? 'link'},
            ${input.senderName ?? null}, ${input.senderEmail ?? null},
            ${textArray(input.recipients ?? [])},
            ${input.subject ?? null}, ${input.message ?? null},
            ${input.password ? hashPassword(input.password) : null},
            ${input.encrypt ?? false},
            ${input.expiresInDays ? sql`now() + (${input.expiresInDays} * interval '1 day')` : null},
            ${input.downloadLimit ?? 0},
            ${JSON.stringify(input.branding ?? {})}::jsonb)
    returning id`);

  await mint(tx, {
    workspaceId: input.workspaceId,
    projectId: input.projectId,
    tool: 'link',
    type: 'transfer',
    externalId: row!.id,
    label: input.subject ?? 'File transfer',
  });

  return { id: row!.id, linkId: link.id, alias: link.alias };
}

/* ------------------------------------------------------------------ upload */

export type PlannedUpload = {
  fileId: string;
  storageKey: string;
  partSize: number;
  parts: number;
  /**
   * A pre-authorised URL per part, in order.
   *
   * The browser PUTs directly to the object store, so a 5 GB transfer is
   * between the customer and the bucket and never occupies a Node process.
   * They expire; `resumeUpload` mints fresh ones for whatever is still missing.
   */
  partUrls: string[];
};

/** 8 MB parts: large enough that a 5 GB file is 640 parts, small enough to retry cheaply. */
export const PART_SIZE = 8 * 1024 * 1024;

/**
 * An hour per part URL.
 *
 * Long enough that a slow connection finishes a part; short enough that a URL
 * copied out of devtools stops working the same afternoon. `resumeUpload`
 * exists precisely so this can be short.
 */
export const PART_URL_TTL_SECONDS = 3600;

/**
 * Reserves space for a file and returns the plan for uploading it.
 *
 * The size check happens *here*, before a single byte moves, and it counts the
 * *declared* size against what the transfer already holds. Checking afterwards
 * would mean accepting a 5 GB upload from a workspace whose plan tops out at
 * 100 MB and then deciding what to do with it — and every honest option at that
 * point is bad. Declared rather than actual is deliberate too: the storage
 * layer rejects a part stream that exceeds what was declared, so a client
 * cannot under-declare its way past the gate.
 */
export async function planUpload(
  tx: WorkspaceScopedDb,
  storage: StorageAdapter,
  opts: {
    workspaceId: string; transferId: string; name: string;
    sizeBytes: number; mimeType?: string;
  },
): Promise<PlannedUpload> {
  if (opts.sizeBytes <= 0) throw new LinkNotAllowed('empty_file', 'A file needs a size.');

  const ctx = await loadContext(tx, opts.workspaceId, 'link.transfer_size_mb');
  if (ctx) {
    const [t] = await tx.execute<{ total_bytes: number }>(sql`
      select total_bytes from transfers where id = ${opts.transferId}`);
    if (!t) throw new LinkNotAllowed('not_found', 'No such transfer.');

    // The limit is per transfer, in megabytes — "1 transfer up to 100 MB" is
    // what the plan promises, so this is the number the customer was sold.
    const usedMb = Math.ceil(Number(t.total_bytes) / 1_000_000);
    const addMb = Math.ceil(opts.sizeBytes / 1_000_000);
    const decision = resolveEntitlement({ ...ctx, used: usedMb }, addMb);
    if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);
  }

  /*
   * The key carries random bytes, not the filename.
   *
   * Object keys leak into logs, CDN traces and presigned URLs. "invoice-
   * acme-final.pdf" in a URL is a disclosure even when the URL itself is
   * signed; the display name lives in the row and is put back via
   * `Content-Disposition` at download time.
   */
  const storageKey = `transfers/${opts.transferId}/${randomBytes(12).toString('hex')}`;
  const parts = Math.ceil(opts.sizeBytes / PART_SIZE);

  const handle = await storage.begin(storageKey, { contentType: opts.mimeType });
  const partUrls: string[] = [];
  for (let n = 1; n <= parts; n++) {
    partUrls.push(await storage.partUrl(handle, n, PART_URL_TTL_SECONDS));
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into transfer_files
      (workspace_id, transfer_id, name, size_bytes, mime_type, storage_key, upload_id, sort_order)
    values (${opts.workspaceId}, ${opts.transferId}, ${opts.name}, ${opts.sizeBytes},
            ${opts.mimeType ?? null}, ${storageKey}, ${handle.uploadId},
            (select coalesce(max(sort_order), -1) + 1 from transfer_files
              where transfer_id = ${opts.transferId}))
    returning id`);

  await tx.execute(sql`
    update transfers
       set total_files = total_files + 1,
           total_bytes = total_bytes + ${opts.sizeBytes}
     where id = ${opts.transferId}`);

  return { fileId: row!.id, storageKey, partSize: PART_SIZE, parts, partUrls };
}

/**
 * Fresh URLs for the parts that never arrived.
 *
 * This *is* resumability. An upload interrupted overnight comes back to URLs
 * that expired hours ago; without this the only recovery is starting again,
 * which for a 5 GB file over a domestic connection is not a recovery.
 */
export async function resumeUpload(
  tx: WorkspaceScopedDb,
  storage: StorageAdapter,
  fileId: string,
): Promise<{ partSize: number; missing: { partNumber: number; url: string }[] }> {
  const [file] = await tx.execute<{
    storage_key: string; upload_id: string | null; size_bytes: number; parts: number[] | null;
  }>(sql`
    select storage_key, upload_id, size_bytes, parts from transfer_files where id = ${fileId}`);
  if (!file) throw new LinkNotAllowed('not_found', 'No such file.');

  const total = Math.ceil(Number(file.size_bytes) / PART_SIZE);
  const have = new Set(file.parts ?? []);
  const handle = { uploadId: file.upload_id ?? 'local', storageKey: file.storage_key };

  const missing: { partNumber: number; url: string }[] = [];
  for (let n = 1; n <= total; n++) {
    // Holes, not a truncation: an interrupted parallel upload leaves gaps in
    // the middle, so every number is checked rather than resuming from a
    // high-water mark.
    if (have.has(n)) continue;
    missing.push({ partNumber: n, url: await storage.partUrl(handle, n, PART_URL_TTL_SECONDS) });
  }
  return { partSize: PART_SIZE, missing };
}

/**
 * The parts already stored for a file.
 *
 * This is the whole resumability contract: a client that lost its connection
 * asks what arrived and uploads the difference. Returned sorted so a caller can
 * find the first gap rather than assuming a contiguous prefix — an interrupted
 * parallel upload leaves holes, not a clean truncation.
 */
export async function uploadedParts(
  tx: WorkspaceScopedDb,
  fileId: string,
): Promise<number[]> {
  const [row] = await tx.execute<{ parts: number[] | null }>(sql`
    select parts from transfer_files where id = ${fileId}`);
  return [...(row?.parts ?? [])].sort((a, b) => a - b);
}

export async function registerPart(
  tx: WorkspaceScopedDb,
  opts: { fileId: string; partNumber: number; etag?: string },
): Promise<number[]> {
  /*
   * `array_agg(distinct …)` over the union, not `array_append`.
   *
   * A retried part is normal — the client cannot tell a lost response from a
   * lost request — so registering part 12 twice must leave one 12, not two.
   */
  const [row] = await tx.execute<{ parts: number[] }>(sql`
    update transfer_files
       set parts = (
         select coalesce(array_agg(distinct p order by p), array[]::int[])
           from unnest(coalesce(parts, array[]::int[]) || array[${opts.partNumber}]::int[]) as p
       ),
           -- The ETag the provider issued. It will refuse to assemble without
           -- the exact set, so a retried part legitimately overwrites its own.
           part_etags = part_etags || ${JSON.stringify(
             opts.etag ? { [String(opts.partNumber)]: opts.etag } : {},
           )}::jsonb
     where id = ${opts.fileId}
    returning parts`);
  return row?.parts ?? [];
}

/**
 * Records the client's checksum for a file whose parts have all arrived.
 *
 * Deliberately does **not** set `uploaded_at`. That column means "assembled and
 * durable in the object store", and only `finaliseTransfer` can know that —
 * having two writers with two meanings made `finaliseTransfer` treat an
 * unassembled file as done and publish a share link to an object that did not
 * exist.
 */
export async function completeFile(
  tx: WorkspaceScopedDb,
  opts: { fileId: string; checksumSha256?: string },
): Promise<void> {
  await tx.execute(sql`
    update transfer_files
       set checksum_sha256 = ${opts.checksumSha256 ?? null}
     where id = ${opts.fileId}`);
}

/**
 * Marks a transfer ready to share.
 *
 * Refuses when a file is still missing parts. A share link that half-works is
 * worse than one that does not exist yet: the recipient downloads a truncated
 * archive and blames the sender.
 */
export async function finaliseTransfer(
  tx: WorkspaceScopedDb,
  storage: StorageAdapter,
  transferId: string,
): Promise<{ ready: true } | { ready: false; pending: string[] }> {
  const files = await tx.execute<{
    id: string; name: string; size_bytes: number; parts: number[] | null;
    storage_key: string; upload_id: string | null; part_etags: Record<string, string>;
    uploaded_at: string | null;
  }>(sql`
    select id, name, size_bytes, parts, storage_key, upload_id, part_etags, uploaded_at
      from transfer_files where transfer_id = ${transferId}`);

  const pending = files
    .filter((f) => (f.parts?.length ?? 0) < Math.ceil(Number(f.size_bytes) / PART_SIZE))
    .map((f) => f.name);
  if (pending.length > 0) return { ready: false, pending };

  /*
   * Assemble, then mark ready — in that order.
   *
   * Marking first would publish a share link to an object the provider has not
   * built yet, and the recipient would get a 404 from the bucket with nothing
   * on our side to explain it. An assembly that fails leaves the transfer
   * `pending`, which is recoverable.
   */
  for (const file of files) {
    // `uploaded_at` is set here and nowhere else, so it is a reliable "already
    // assembled" and a retried finalise is a genuine no-op.
    if (file.uploaded_at) continue;
    const parts = (file.parts ?? []).map((partNumber) => ({
      partNumber,
      etag: file.part_etags?.[String(partNumber)] ?? '',
    }));
    const { size } = await storage.complete(
      { uploadId: file.upload_id ?? 'local', storageKey: file.storage_key },
      parts,
    );
    await tx.execute(sql`
      update transfer_files set uploaded_at = now(), size_bytes = ${size} where id = ${file.id}`);
  }

  await tx.execute(sql`
    update transfers
       set status = 'ready',
           total_bytes = (select coalesce(sum(size_bytes), 0) from transfer_files
                           where transfer_id = ${transferId})
     where id = ${transferId}`);
  return { ready: true };
}

/**
 * A URL the recipient's browser can GET, once the claim has been allowed.
 *
 * Short-lived and per-file. `claimDownload` decides *whether*; this decides
 * *where*, and the two are separate so the counting cannot be bypassed by
 * holding on to a URL — a fresh claim is needed for a fresh URL.
 */
export async function downloadUrl(
  tx: WorkspaceScopedDb,
  storage: StorageAdapter,
  opts: { transferId: string; fileId?: string; expiresIn?: number },
): Promise<{ name: string; url: string }[]> {
  const files = await tx.execute<{ id: string; name: string; storage_key: string }>(sql`
    select id, name, storage_key from transfer_files
     where transfer_id = ${opts.transferId}
       and uploaded_at is not null
       ${opts.fileId ? sql`and id = ${opts.fileId}` : sql``}
     order by sort_order`);

  return Promise.all(
    files.map(async (f) => ({
      name: f.name,
      // The display name is restored here via Content-Disposition: the object
      // key is deliberately random, so without this every download would land
      // as a hex string.
      url: await storage.readUrl(f.storage_key, opts.expiresIn ?? DOWNLOAD_TTL_SECONDS, f.name),
    })),
  );
}

/** Five minutes: long enough to start a download, short enough not to be shareable. */
export const DOWNLOAD_TTL_SECONDS = 300;

/* ---------------------------------------------------------------- download */

export type DownloadDecision =
  | { ok: true; transferId: string }
  | { ok: false; reason: 'not_found' | 'expired' | 'cancelled' | 'limit_reached' | 'password'; message: string };

/**
 * Decides whether a download may proceed, and counts it if so.
 *
 * The count and the check are one statement on purpose. Two recipients clicking
 * at the same moment on a transfer with one download left would both pass a
 * separate check; the `where` clause here lets exactly one of them through.
 */
export async function claimDownload(
  tx: WorkspaceScopedDb,
  opts: { transferId: string; password?: string },
): Promise<DownloadDecision> {
  const [t] = await tx.execute<{
    id: string; status: string; password_hash: string | null;
    expires_at: string | null; download_limit: number; downloads: number;
    cancelled_at: string | null;
  }>(sql`
    select id, status, password_hash, expires_at, download_limit, downloads, cancelled_at
      from transfers where id = ${opts.transferId}`);

  if (!t) return { ok: false, reason: 'not_found', message: 'This transfer no longer exists.' };
  if (t.cancelled_at) {
    return { ok: false, reason: 'cancelled', message: 'The sender cancelled this transfer.' };
  }
  if (t.expires_at && Date.parse(t.expires_at) <= Date.now()) {
    return { ok: false, reason: 'expired', message: 'This transfer has expired.' };
  }
  if (t.password_hash && !verifyPassword(t.password_hash, opts.password ?? '')) {
    return { ok: false, reason: 'password', message: 'That password is not right.' };
  }

  const [claimed] = await tx.execute<{ downloads: number }>(sql`
    update transfers
       set downloads = downloads + 1
     where id = ${opts.transferId}
       and (download_limit = 0 or downloads < download_limit)
    returning downloads`);

  if (!claimed) {
    return { ok: false, reason: 'limit_reached', message: 'This transfer has reached its download limit.' };
  }
  return { ok: true, transferId: t.id };
}

/**
 * Sender-initiated cancellation, with a reason the recipient sees.
 *
 * `swipgle`'s idea and a good one: the common case is "wrong file", and a
 * recipient told that is far better served than one who finds a dead link.
 */
export async function cancelTransfer(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; transferId: string; reason?: string },
): Promise<void> {
  await tx.execute(sql`
    update transfers
       set status = 'cancelled', cancelled_at = now(), cancel_reason = ${opts.reason ?? null}
     where id = ${opts.transferId} and workspace_id = ${opts.workspaceId}`);
}

/**
 * Expires transfers whose time is up.
 *
 * Marks rather than deletes. The storage sweep runs separately and reads this
 * status, so a bug in one cannot both hide a transfer and destroy its bytes in
 * the same pass — and an operator who catches the mistake within the retention
 * window can still recover it.
 */
export async function expireDue(tx: WorkspaceScopedDb, workspaceId: string): Promise<number> {
  const rows = await tx.execute<{ id: string }>(sql`
    update transfers
       set status = 'expired'
     where workspace_id = ${workspaceId}
       and status = 'ready'
       and expires_at is not null
       and expires_at <= now()
    returning id`);
  return rows.length;
}
