import { createHash, createHmac } from 'node:crypto';

/**
 * AWS Signature Version 4, for presigned URLs.
 *
 * Hand-written rather than pulled from the AWS SDK, for one reason worth
 * stating: presigning is a pure function of a key, a clock and a string, and
 * the SDK that performs it weighs several megabytes and drags in a credential
 * chain, a region resolver and a retry layer we do not use. What we need is
 * about eighty lines, and it is verified against AWS's own published test
 * vector — so "did we get the signing right" is answered by the canonical
 * example rather than by trusting a dependency.
 *
 * R2, Wasabi and Backblaze all speak this. That is the whole reason
 * `storage_providers` can be a row rather than a deploy.
 */

export type SignOptions = {
  method: 'GET' | 'PUT' | 'HEAD' | 'DELETE' | 'POST';
  /** Host only — `bucket.account.r2.cloudflarestorage.com`. */
  host: string;
  /** Object key, without a leading slash. */
  key: string;
  region: string;
  accessKeyId: string;
  secretAccessKey: string;
  /** Seconds the URL stays valid. */
  expiresIn: number;
  /** Extra query parameters — `partNumber` and `uploadId` for multipart. */
  query?: Record<string, string>;
  service?: string;
  /** Overridable so a test can pin the clock. */
  now?: Date;
  protocol?: 'https' | 'http';
};

const sha256 = (data: string | Buffer) => createHash('sha256').update(data).digest('hex');
const hmac = (key: Buffer | string, data: string) => createHmac('sha256', key).update(data).digest();

/**
 * S3 wants each path segment encoded, but not the slashes between them, and
 * `encodeURIComponent` leaves `!'()*` alone where AWS requires them escaped.
 */
function encodeKey(key: string): string {
  return key
    .split('/')
    .map((segment) => encodeRfc3986(segment))
    .join('/');
}

function encodeRfc3986(value: string): string {
  return encodeURIComponent(value).replace(
    /[!'()*]/g,
    (c) => `%${c.charCodeAt(0).toString(16).toUpperCase()}`,
  );
}

/** The canonical query string: sorted by key, every part encoded. */
function canonicalQuery(params: Record<string, string>): string {
  return Object.keys(params)
    .sort()
    .map((k) => `${encodeRfc3986(k)}=${encodeRfc3986(params[k]!)}`)
    .join('&');
}

export function presign(options: SignOptions): string {
  const service = options.service ?? 's3';
  const now = options.now ?? new Date();
  const amzDate = now.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
  const dateStamp = amzDate.slice(0, 8);
  const scope = `${dateStamp}/${options.region}/${service}/aws4_request`;

  const params: Record<string, string> = {
    ...options.query,
    'X-Amz-Algorithm': 'AWS4-HMAC-SHA256',
    'X-Amz-Credential': `${options.accessKeyId}/${scope}`,
    'X-Amz-Date': amzDate,
    'X-Amz-Expires': String(options.expiresIn),
    'X-Amz-SignedHeaders': 'host',
  };

  const canonicalPath = `/${encodeKey(options.key)}`;
  const canonicalRequest = [
    options.method,
    canonicalPath,
    canonicalQuery(params),
    `host:${options.host}\n`,
    'host',
    /*
     * `UNSIGNED-PAYLOAD`, because the body is the point.
     *
     * A presigned URL is handed to a browser that then streams gigabytes
     * through it; signing the payload would mean hashing the whole file on our
     * side first, which is exactly the work presigning exists to avoid.
     */
    'UNSIGNED-PAYLOAD',
  ].join('\n');

  const stringToSign = [
    'AWS4-HMAC-SHA256',
    amzDate,
    scope,
    sha256(canonicalRequest),
  ].join('\n');

  const signature = hmac(signingKey(options.secretAccessKey, dateStamp, options.region, service), stringToSign)
    .toString('hex');

  const protocol = options.protocol ?? 'https';
  return `${protocol}://${options.host}${canonicalPath}?${canonicalQuery(params)}&X-Amz-Signature=${signature}`;
}

/** The four-step derivation. Each step keys the next; none is reusable alone. */
function signingKey(secret: string, dateStamp: string, region: string, service: string): Buffer {
  const kDate = hmac(`AWS4${secret}`, dateStamp);
  const kRegion = hmac(kDate, region);
  const kService = hmac(kRegion, service);
  return hmac(kService, 'aws4_request');
}

/** Exposed for the test that pins AWS's published vector. */
export const __internals = { canonicalQuery, encodeKey, signingKey, sha256 };
