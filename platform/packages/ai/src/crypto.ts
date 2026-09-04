import { createCipheriv, createDecipheriv, randomBytes, scryptSync } from 'node:crypto';

/**
 * Envelope encryption for provider keys.
 *
 * Keys are never logged and never returned — only a `keyHint` (last 4) reaches
 * the UI. AES-256-GCM so tampering is detected rather than silently decrypting
 * to garbage.
 */
const ALGO = 'aes-256-gcm';

function keyFrom(secret: string): Buffer {
  return scryptSync(secret, 'mamal-ai-credentials', 32);
}

export function encryptCredential(plaintext: string, secret = envSecret()): string {
  const iv = randomBytes(12);
  const cipher = createCipheriv(ALGO, keyFrom(secret), iv);
  const enc = Buffer.concat([cipher.update(plaintext, 'utf8'), cipher.final()]);
  const tag = cipher.getAuthTag();
  return [iv.toString('base64'), tag.toString('base64'), enc.toString('base64')].join('.');
}

export function decryptCredential(payload: string, secret = envSecret()): string {
  const [ivB64, tagB64, dataB64] = payload.split('.');
  if (!ivB64 || !tagB64 || !dataB64) throw new Error('malformed credential payload');
  const decipher = createDecipheriv(ALGO, keyFrom(secret), Buffer.from(ivB64, 'base64'));
  decipher.setAuthTag(Buffer.from(tagB64, 'base64'));
  return Buffer.concat([
    decipher.update(Buffer.from(dataB64, 'base64')),
    decipher.final(),
  ]).toString('utf8');
}

/** Last four characters, which is all the UI ever gets. */
export function keyHint(plaintext: string): string {
  return `…${plaintext.slice(-4)}`;
}

function envSecret(): string {
  const s = process.env.CREDENTIALS_SECRET ?? process.env.BETTER_AUTH_SECRET;
  if (!s) throw new Error('CREDENTIALS_SECRET is required to encrypt provider keys');
  return s;
}
