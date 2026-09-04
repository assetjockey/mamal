import { createCipheriv, createDecipheriv, randomBytes, scryptSync } from 'node:crypto';

/**
 * Envelope encryption for non-AI secrets at rest — currently VAPID private keys.
 *
 * Same construction as `@mamal/ai`'s credential crypto (AES-256-GCM, so
 * tampering is detected rather than silently decrypting to garbage), but with
 * its **own salt**. Reusing one derived key across unrelated secret types is a
 * small smell with no upside: domain separation costs one string and means a
 * weakness in one store cannot be carried into the other.
 */
const ALGO = 'aes-256-gcm';
const SALT = 'mamal-platform-secrets';

function envSecret(): string {
  const secret = process.env.CREDENTIAL_SECRET ?? process.env.AUTH_SECRET;
  if (!secret) {
    throw new Error('CREDENTIAL_SECRET (or AUTH_SECRET) must be set to store secrets at rest');
  }
  return secret;
}

const keyFrom = (secret: string) => scryptSync(secret, SALT, 32);

export function encryptSecret(plaintext: string, secret = envSecret()): string {
  const iv = randomBytes(12);
  const cipher = createCipheriv(ALGO, keyFrom(secret), iv);
  const enc = Buffer.concat([cipher.update(plaintext, 'utf8'), cipher.final()]);
  return [
    iv.toString('base64'),
    cipher.getAuthTag().toString('base64'),
    enc.toString('base64'),
  ].join('.');
}

export function decryptSecret(payload: string, secret = envSecret()): string {
  const [ivB64, tagB64, dataB64] = payload.split('.');
  if (!ivB64 || !tagB64 || !dataB64) throw new Error('malformed secret payload');
  const decipher = createDecipheriv(ALGO, keyFrom(secret), Buffer.from(ivB64, 'base64'));
  decipher.setAuthTag(Buffer.from(tagB64, 'base64'));
  return Buffer.concat([
    decipher.update(Buffer.from(dataB64, 'base64')),
    decipher.final(),
  ]).toString('utf8');
}
