import { describe, expect, it } from 'vitest';
import { presign, __internals } from '../sigv4.ts';

/**
 * Checked against AWS's own published example, not against ourselves.
 *
 * "Example: Signature Calculation for Presigned URL" from the S3 signing
 * documentation — a fixed key, a fixed clock and a fixed expected signature.
 * A signer that agrees with its own helper functions proves nothing; one that
 * reproduces this string byte for byte will be accepted by S3, R2, Wasabi and
 * Backblaze alike.
 */
const VECTOR = {
  accessKeyId: 'AKIAIOSFODNN7EXAMPLE',
  secretAccessKey: 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
  region: 'us-east-1',
  host: 'examplebucket.s3.amazonaws.com',
  key: 'test.txt',
  now: new Date('2013-05-24T00:00:00Z'),
  expiresIn: 86_400,
  signature: 'aeeed9bbccd4d02ee5c0109b86d86835f995330da4c265957d157751f604d404',
};

describe('SigV4 presigning', () => {
  it('reproduces AWS’s published presigned-GET signature', () => {
    const url = presign({ method: 'GET', ...VECTOR });
    expect(url).toContain(`X-Amz-Signature=${VECTOR.signature}`);
  });

  it('builds the URL AWS’s example shows, parameter for parameter', () => {
    const url = new URL(presign({ method: 'GET', ...VECTOR }));
    expect(url.origin).toBe('https://examplebucket.s3.amazonaws.com');
    expect(url.pathname).toBe('/test.txt');
    expect(url.searchParams.get('X-Amz-Algorithm')).toBe('AWS4-HMAC-SHA256');
    expect(url.searchParams.get('X-Amz-Credential')).toBe(
      'AKIAIOSFODNN7EXAMPLE/20130524/us-east-1/s3/aws4_request',
    );
    expect(url.searchParams.get('X-Amz-Date')).toBe('20130524T000000Z');
    expect(url.searchParams.get('X-Amz-Expires')).toBe('86400');
    expect(url.searchParams.get('X-Amz-SignedHeaders')).toBe('host');
  });

  it('derives the signing key through all four steps', () => {
    /*
     * The intermediate from AWS's worked example — `kSigning` after date,
     * region, service and `aws4_request`. Pinning it separates "the derivation
     * is wrong" from "the canonical request is wrong", which otherwise both
     * show up as one bad signature and take an afternoon to tell apart.
     */
    const key = __internals.signingKey(VECTOR.secretAccessKey, '20130524', 'us-east-1', 's3');
    expect(key.toString('hex')).toBe(
      'dbb893acc010964918f1fd433add87c70e8b0db6be30c1fbeafefa5ec6ba8378',
    );
  });

  it('encodes each path segment but leaves the separators alone', () => {
    expect(__internals.encodeKey('transfers/a b/c+d.pdf')).toBe('transfers/a%20b/c%2Bd.pdf');
    // `encodeURIComponent` leaves these; AWS requires them escaped.
    expect(__internals.encodeKey("it's(a)*test!")).toBe('it%27s%28a%29%2Atest%21');
  });

  it('sorts the query canonically, because the order is part of what is signed', () => {
    expect(__internals.canonicalQuery({ b: '2', a: '1', C: '3' })).toBe('C=3&a=1&b=2');
  });

  it('signs a multipart PUT with its part number and upload id', () => {
    const url = new URL(
      presign({
        method: 'PUT',
        host: 'bucket.account.r2.cloudflarestorage.com',
        key: 'transfers/abc/deck.pdf',
        region: 'auto',
        accessKeyId: 'k',
        secretAccessKey: 's',
        expiresIn: 3600,
        query: { partNumber: '3', uploadId: 'up-1' },
        now: VECTOR.now,
      }),
    );
    expect(url.searchParams.get('partNumber')).toBe('3');
    expect(url.searchParams.get('uploadId')).toBe('up-1');
    expect(url.searchParams.get('X-Amz-Signature')).toMatch(/^[0-9a-f]{64}$/);
  });

  it('changes the signature when anything signed changes', () => {
    const base = { method: 'GET' as const, ...VECTOR };
    const original = presign(base);
    for (const change of [
      { key: 'other.txt' },
      { region: 'eu-west-1' },
      { expiresIn: 60 },
      { now: new Date('2013-05-25T00:00:00Z') },
      { host: 'other.s3.amazonaws.com' },
    ]) {
      expect(presign({ ...base, ...change }), JSON.stringify(change)).not.toBe(original);
    }
  });
});
