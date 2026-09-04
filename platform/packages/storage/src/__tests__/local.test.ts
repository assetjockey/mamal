import { mkdtemp, readFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { localAdapter, verifySignature, writeLocalPart, readLocalObject } from '../local.ts';
import { newDataKey, open, seal, unwrapKey, wrapKey } from '../crypto.ts';
import { StorageError } from '../types.ts';

let root = '';
const SECRET = 'test-secret';
const BASE = 'https://app.test/api/storage';

beforeAll(async () => {
  root = await mkdtemp(join(tmpdir(), 'mamal-storage-'));
});
afterAll(async () => {
  await rm(root, { recursive: true, force: true });
});

const adapter = () => localAdapter({ root, secret: SECRET, baseUrl: BASE });

describe('the local adapter', () => {
  it('assembles parts in order, whatever order they arrived in', async () => {
    const a = adapter();
    const key = 'transfers/t1/deck.pdf';
    const handle = await a.begin(key);

    // Out of order and with a repeat, which is what a parallel client does.
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 3, Buffer.from('CCC'));
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 1, Buffer.from('A'));
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 1, Buffer.from('A'));
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 2, Buffer.from('BB'));

    const { size } = await a.complete(handle, [
      { partNumber: 2, etag: 'x' },
      { partNumber: 1, etag: 'x' },
      { partNumber: 3, etag: 'x' },
    ]);
    expect(size).toBe(6);
    expect((await readFile(join(root, key))).toString()).toBe('ABBCCC');
  });

  it('refuses to assemble while a part is missing', async () => {
    const a = adapter();
    const key = 'transfers/t2/half.bin';
    const handle = await a.begin(key);
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 1, Buffer.from('A'));

    // A file that opens and is wrong is worse than one that never appeared.
    await expect(
      a.complete(handle, [
        { partNumber: 1, etag: 'x' },
        { partNumber: 2, etag: 'x' },
      ]),
    ).rejects.toMatchObject({ reason: 'incomplete' });
    expect(await readLocalObject({ root, secret: SECRET, baseUrl: BASE }, key)).toBeNull();
  });

  it('refuses a key that escapes the root', async () => {
    const a = adapter();
    // Validating the spelling would be validating the spelling of an attack;
    // this checks where the path actually resolves.
    for (const key of ['../outside', 'a/../../outside', '/etc/passwd']) {
      await expect(a.head(key), key).rejects.toBeInstanceOf(StorageError);
    }
  });

  it('drops the part directory once the object exists', async () => {
    const a = adapter();
    const key = 'transfers/t3/one.txt';
    const handle = await a.begin(key);
    await writeLocalPart({ root, secret: SECRET, baseUrl: BASE }, key, 1, Buffer.from('hello'));
    await a.complete(handle, [{ partNumber: 1, etag: 'x' }]);
    expect((await a.head(key))?.size).toBe(5);

    // A second complete has nothing left to work from, which is the correct
    // answer to a retried finalise.
    await expect(a.complete(handle, [{ partNumber: 1, etag: 'x' }])).rejects.toMatchObject({
      reason: 'incomplete',
    });
  });
});

describe('signed URLs', () => {
  const params = (url: string) => new URL(url).searchParams;

  it('round-trips a read URL', async () => {
    const url = await adapter().readUrl('transfers/t1/deck.pdf', 300, 'Slide deck.pdf');
    const verified = verifySignature(SECRET, 'GET', params(url));
    expect(verified).toMatchObject({ ok: true, key: 'transfers/t1/deck.pdf', name: 'Slide deck.pdf' });
  });

  it('refuses a URL edited to point at another object', async () => {
    const url = await adapter().readUrl('transfers/mine/deck.pdf', 300);
    const tampered = params(url);
    tampered.set('key', 'transfers/somebody-elses/deck.pdf');
    expect(verifySignature(SECRET, 'GET', tampered)).toMatchObject({ ok: false, reason: 'bad_signature' });
  });

  it('refuses a URL edited to live longer', async () => {
    const url = await adapter().readUrl('transfers/t1/deck.pdf', 300);
    const tampered = params(url);
    tampered.set('expires', String(Math.floor(Date.now() / 1000) + 86_400));
    expect(verifySignature(SECRET, 'GET', tampered)).toMatchObject({ ok: false, reason: 'bad_signature' });
  });

  it('refuses an expired URL even though the signature is good', async () => {
    // Signed correctly, for a moment that has passed. Both checks are needed.
    const url = await localAdapter({ root, secret: SECRET, baseUrl: BASE })
      .readUrl('transfers/t1/deck.pdf', -1);
    expect(verifySignature(SECRET, 'GET', params(url))).toMatchObject({ ok: false, reason: 'expired' });
  });

  it('will not accept a read URL as an upload URL', async () => {
    // The method is part of what is signed, so a GET link cannot be turned
    // into a PUT that overwrites the object.
    const url = await adapter().readUrl('transfers/t1/deck.pdf', 300);
    expect(verifySignature(SECRET, 'PUT', params(url))).toMatchObject({ ok: false });
  });

  it('will not accept another instance’s signature', async () => {
    const url = await adapter().readUrl('transfers/t1/deck.pdf', 300);
    expect(verifySignature('a-different-secret', 'GET', params(url))).toMatchObject({ ok: false });
  });

  it('is order-independent, so a client may reshuffle the query', async () => {
    const url = await adapter().readUrl('transfers/t1/deck.pdf', 300, 'a.pdf');
    const original = params(url);
    const reordered = new URLSearchParams();
    for (const k of [...original.keys()].reverse()) reordered.set(k, original.get(k)!);
    expect(verifySignature(SECRET, 'GET', reordered)).toMatchObject({ ok: true });
  });
});

describe('envelope encryption', () => {
  const KEK = Buffer.from('0123456789abcdef0123456789abcdef').toString('base64');

  it('wraps and unwraps a data key', () => {
    process.env.STORAGE_KEK = KEK;
    const dek = newDataKey();
    expect(unwrapKey(wrapKey(dek))).toEqual(dek);
  });

  it('seals and opens a payload', () => {
    process.env.STORAGE_KEK = KEK;
    const dek = newDataKey();
    const sealed = seal(dek, Buffer.from('the quick brown fox'));
    expect(sealed.data.toString()).not.toContain('quick');
    expect(open(dek, sealed).toString()).toBe('the quick brown fox');
  });

  it('refuses a modified ciphertext rather than returning rubbish', () => {
    process.env.STORAGE_KEK = KEK;
    const dek = newDataKey();
    const sealed = seal(dek, Buffer.from('the quick brown fox'));
    sealed.data[0] = sealed.data[0]! ^ 0xff;
    // GCM authenticates, which is the entire reason it is used here.
    expect(() => open(dek, sealed)).toThrow();
  });

  it('cannot unwrap under a rotated instance key', () => {
    process.env.STORAGE_KEK = KEK;
    const wrapped = wrapKey(newDataKey());
    process.env.STORAGE_KEK = Buffer.from('fedcba9876543210fedcba9876543210').toString('base64');
    // This is the property that makes rotation meaningful, and the reason the
    // resolver reports "has STORAGE_KEK changed?" rather than a decode error.
    expect(() => unwrapKey(wrapped)).toThrow();
    process.env.STORAGE_KEK = KEK;
  });

  it('says what to do when the instance key is missing or wrong-sized', () => {
    delete process.env.STORAGE_KEK;
    expect(() => newDataKey() && wrapKey(Buffer.alloc(32))).toThrow(/openssl rand/);
    process.env.STORAGE_KEK = Buffer.from('too short').toString('base64');
    expect(() => wrapKey(Buffer.alloc(32))).toThrow(/32 bytes/);
    process.env.STORAGE_KEK = KEK;
  });
});
