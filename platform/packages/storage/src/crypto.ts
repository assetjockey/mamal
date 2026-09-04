import { createCipheriv, createDecipheriv, randomBytes } from 'node:crypto';

/**
 * Envelope encryption for a transfer's contents.
 *
 * Each transfer gets its own data key; the data key is wrapped with the
 * instance key and stored beside the transfer. Two properties follow, and both
 * are why it is done this way rather than encrypting with one key everywhere:
 *
 * - Rotating the instance key rewraps N small data keys, not N terabytes.
 * - Deleting a transfer's wrapped key makes its bytes unrecoverable
 *   immediately, without waiting for a storage sweep — which is what
 *   "encrypted at rest, deletable on request" has to mean under GDPR.
 *
 * AES-256-GCM throughout: it authenticates as well as encrypts, so a modified
 * ciphertext fails to open rather than decrypting to rubbish.
 */

const ALGORITHM = 'aes-256-gcm';

export type WrappedKey = string;

function instanceKey(): Buffer {
  const raw = process.env.STORAGE_KEK;
  if (!raw) {
    throw new Error(
      'STORAGE_KEK is not set. Encrypted transfers cannot be created or read without it — ' +
        'generate one with `openssl rand -base64 32`.',
    );
  }
  const key = Buffer.from(raw, 'base64');
  if (key.length !== 32) {
    throw new Error(`STORAGE_KEK must decode to 32 bytes, got ${key.length}.`);
  }
  return key;
}

/** A fresh 256-bit data key. Never stored unwrapped, never logged. */
export function newDataKey(): Buffer {
  return randomBytes(32);
}

export function wrapKey(dataKey: Buffer): WrappedKey {
  const iv = randomBytes(12);
  const cipher = createCipheriv(ALGORITHM, instanceKey(), iv);
  const wrapped = Buffer.concat([cipher.update(dataKey), cipher.final()]);
  return ['v1', iv.toString('base64url'), wrapped.toString('base64url'),
    cipher.getAuthTag().toString('base64url')].join('.');
}

export function unwrapKey(wrapped: WrappedKey): Buffer {
  const [version, ivB64, dataB64, tagB64] = wrapped.split('.');
  if (version !== 'v1' || !ivB64 || !dataB64 || !tagB64) {
    throw new Error('Wrapped key is malformed.');
  }
  const decipher = createDecipheriv(ALGORITHM, instanceKey(), Buffer.from(ivB64, 'base64url'));
  decipher.setAuthTag(Buffer.from(tagB64, 'base64url'));
  return Buffer.concat([decipher.update(Buffer.from(dataB64, 'base64url')), decipher.final()]);
}

export type Sealed = { iv: string; tag: string; data: Buffer };

export function seal(dataKey: Buffer, plaintext: Buffer): Sealed {
  const iv = randomBytes(12);
  const cipher = createCipheriv(ALGORITHM, dataKey, iv);
  const data = Buffer.concat([cipher.update(plaintext), cipher.final()]);
  return { iv: iv.toString('base64url'), tag: cipher.getAuthTag().toString('base64url'), data };
}

export function open(dataKey: Buffer, sealed: Sealed): Buffer {
  const decipher = createDecipheriv(ALGORITHM, dataKey, Buffer.from(sealed.iv, 'base64url'));
  decipher.setAuthTag(Buffer.from(sealed.tag, 'base64url'));
  return Buffer.concat([decipher.update(sealed.data), decipher.final()]);
}
