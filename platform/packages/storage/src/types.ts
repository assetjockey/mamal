/**
 * What a storage backend has to do.
 *
 * Small on purpose. The transfers flow needs five things — start a multipart
 * upload, hand out a URL for each part, finish it, hand out a URL to read the
 * result, and delete — and every one of R2, S3, Wasabi, Backblaze and a plain
 * disk can do them. Anything richer would be an S3 interface with other
 * backends bolted on, and the point of `storage_providers` being a *row* is
 * that an operator can add Wasabi by filling in a form.
 *
 * **Bytes never pass through the origin.** `partUrl` and `readUrl` return
 * pre-authorised URLs the browser uses directly, so a 5 GB transfer is between
 * the customer and the bucket. A Node process streaming that is the single
 * easiest way to make the platform fall over.
 */

export type UploadHandle = {
  /** Provider-side identifier for the multipart upload, if it has one. */
  uploadId: string;
  storageKey: string;
};

export type PartRef = { partNumber: number; etag: string };

export type StorageAdapter = {
  readonly handler: string;

  /** Begins a multipart upload and returns the handle later calls need. */
  begin(key: string, opts?: { contentType?: string }): Promise<UploadHandle>;

  /** A URL the browser may PUT one part to, valid for `expiresIn` seconds. */
  partUrl(handle: UploadHandle, partNumber: number, expiresIn: number): Promise<string>;

  /** Assembles the parts. Fails if any are missing — a partial object is worse than none. */
  complete(handle: UploadHandle, parts: PartRef[]): Promise<{ size: number }>;

  /** Abandons an upload and frees whatever the provider is holding. */
  abort(handle: UploadHandle): Promise<void>;

  /**
   * A URL the browser may GET, valid for `expiresIn` seconds.
   *
   * `downloadName` sets `Content-Disposition`, so the file lands with the name
   * the sender gave it rather than the opaque storage key.
   */
  readUrl(key: string, expiresIn: number, downloadName?: string): Promise<string>;

  delete(key: string): Promise<void>;
  head(key: string): Promise<{ size: number } | null>;
};

export class StorageError extends Error {
  constructor(
    readonly reason: string,
    message: string,
  ) {
    super(message);
    this.name = 'StorageNotAllowed';
  }
}
