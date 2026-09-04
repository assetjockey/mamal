import { presign } from './sigv4.ts';
import { StorageError, type StorageAdapter } from './types.ts';

/**
 * S3-compatible storage: R2, S3, Wasabi, Backblaze.
 *
 * One adapter for all four because they speak the same protocol — which is the
 * whole reason `storage_providers` can be a row an operator fills in rather
 * than a deploy. The only differences are the host and the region string
 * (`auto` on R2), and both are configuration.
 *
 * `begin` and `complete` are the two calls the origin actually makes; they
 * carry no payload and are quick. Everything with bytes in it goes through a
 * presigned URL, so a 5 GB upload never touches a Node process.
 */

export type S3Options = {
  handler: string;
  host: string;
  region: string;
  accessKeyId: string;
  secretAccessKey: string;
  /** R2 needs no bucket in the path when the host is bucket-scoped. */
  bucketInPath?: string;
};

export function s3Adapter(options: S3Options): StorageAdapter {
  const path = (key: string) => (options.bucketInPath ? `${options.bucketInPath}/${key}` : key);

  const sign = (
    method: 'GET' | 'PUT' | 'POST' | 'DELETE' | 'HEAD',
    key: string,
    expiresIn: number,
    query?: Record<string, string>,
  ) =>
    presign({
      method,
      host: options.host,
      key: path(key),
      region: options.region,
      accessKeyId: options.accessKeyId,
      secretAccessKey: options.secretAccessKey,
      expiresIn,
      query,
    });

  const call = async (method: string, url: string, body?: string) => {
    const response = await fetch(url, {
      method,
      ...(body ? { body, headers: { 'content-type': 'application/xml' } } : {}),
    });
    const text = await response.text();
    if (!response.ok) {
      // The provider's own message, not ours: "bucket does not exist" and
      // "signature mismatch" need different fixes and only it knows which.
      throw new StorageError(
        'provider_error',
        `${options.handler} answered ${response.status}: ${text.slice(0, 300)}`,
      );
    }
    return text;
  };

  return {
    handler: options.handler,

    async begin(key, opts) {
      const xml = await call('POST', sign('POST', key, 300, { uploads: '' }));
      const uploadId = /<UploadId>([^<]+)<\/UploadId>/.exec(xml)?.[1];
      if (!uploadId) {
        throw new StorageError('no_upload_id', 'The provider did not return an upload id.');
      }
      void opts;
      return { uploadId, storageKey: key };
    },

    async partUrl(handle, partNumber, expiresIn) {
      return sign('PUT', handle.storageKey, expiresIn, {
        partNumber: String(partNumber),
        uploadId: handle.uploadId,
      });
    },

    async complete(handle, parts) {
      /*
       * Parts must be listed in order, and the ETags must be the ones the
       * provider returned. Sending them out of order is accepted by some
       * providers and produces a scrambled object on others — so they are
       * sorted here rather than trusted from the caller.
       */
      const body =
        '<CompleteMultipartUpload>' +
        [...parts]
          .sort((a, b) => a.partNumber - b.partNumber)
          .map((p) => `<Part><PartNumber>${p.partNumber}</PartNumber><ETag>${p.etag}</ETag></Part>`)
          .join('') +
        '</CompleteMultipartUpload>';

      await call('POST', sign('POST', handle.storageKey, 300, { uploadId: handle.uploadId }), body);
      const head = await this.head(handle.storageKey);
      return { size: head?.size ?? 0 };
    },

    async abort(handle) {
      await call('DELETE', sign('DELETE', handle.storageKey, 300, { uploadId: handle.uploadId }));
    },

    async readUrl(key, expiresIn, downloadName) {
      return sign('GET', key, expiresIn, downloadName
        ? { 'response-content-disposition': `attachment; filename="${downloadName.replace(/"/g, '')}"` }
        : undefined);
    },

    async delete(key) {
      await call('DELETE', sign('DELETE', key, 300));
    },

    async head(key) {
      const response = await fetch(sign('HEAD', key, 300), { method: 'HEAD' });
      if (!response.ok) return null;
      return { size: Number(response.headers.get('content-length') ?? 0) };
    },
  };
}
