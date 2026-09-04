/**
 * Link, against a real database.
 *
 * The assertions that earn their keep here are the ones about *races and
 * recovery*, because that is where a link shortener actually fails: two people
 * claiming the same alias, two recipients claiming the last download, a
 * rotation that rerolls on refresh, an upload that dies at part 41, and a
 * failover that loses the destination it was supposed to protect.
 */
import { randomBytes, randomUUID } from 'node:crypto';
import { mkdtemp, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { resolve, pickVariant } from '@mamal/redirect';
import { BLOCK_CATALOG, qrDef } from '@mamal/link-catalog';
import { localAdapter, writeLocalPart, verifySignature, readLocalObject } from '@mamal/storage';
import {
  addBlock, createBioPage, createLink, createBarcode, createQrCode, generateAlias,
  loadAssignment, loadForResolve, recordClick, rememberAssignment, reportAbuse,
  setRules, shortUrl, validateAlias, verifyPassword, visibleBlocks,
} from '../service.ts';
import {
  cancelTransfer, claimDownload, completeFile, createTransfer, downloadUrl, expireDue,
  finaliseTransfer, planUpload, registerPart, resumeUpload, uploadedParts, PART_SIZE,
} from '../transfers.ts';
import { linkManifest } from '../manifest.ts';
import { linkSubscriptions, linkSweeper } from '../subscriptions.ts';
import { importLinks, planBulk, MAX_BULK_ROWS } from '../bulk.ts';

const db = unsafeUnscopedDb();
const tag = `lnk${Date.now()}`;

/** A real disk-backed store, so the transfer tests move real bytes. */
const STORAGE_SECRET = 'test-storage-secret';
const STORAGE_BASE = 'https://app.test/api/storage';
let storageRoot = '';

let ws = '';
let project = '';

beforeAll(async () => {
  storageRoot = await mkdtemp(join(tmpdir(), 'mamal-link-'));
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Link') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Link', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'link_pro'`);
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await rm(storageRoot, { recursive: true, force: true });
  await closeDb();
});

/* ------------------------------------------------------------------ aliases */

describe('aliases', () => {
  it('draws from an alphabet with no confusable characters', () => {
    // The whole job of a short link is surviving being read off a poster or
    // dictated over a phone. 0/O and 1/l/I are where that goes wrong.
    const sample = Array.from({ length: 400 }, () => generateAlias()).join('');
    expect(sample).not.toMatch(/[0O1lI]/);
  });

  it('refuses paths the app already serves', () => {
    expect(validateAlias('login').ok).toBe(false);
    expect(validateAlias('API').ok).toBe(false);   // case-insensitive
    expect(validateAlias('spring-sale').ok).toBe(true);
  });

  it('composes one public URL for an alias, from one place', () => {
    // Market's `link.shorten`, the copy button and a QR payload must all hand
    // out the same string; composing it in three places is how they stop.
    expect(shortUrl('abc1234', 'https://mml.to')).toBe('https://mml.to/abc1234');
    expect(shortUrl('abc1234', 'https://mml.to/')).toBe('https://mml.to/abc1234');
  });

  it('refuses shapes that would not survive a URL', () => {
    expect(validateAlias('has space').ok).toBe(false);
    expect(validateAlias('-leading').ok).toBe(false);
    expect(validateAlias('slash/es').ok).toBe(false);
  });

  it('holds uniqueness on the platform domain, where custom_domain_id is null', async () => {
    await withWorkspace(ws, async (tx) => {
      await createLink(tx, {
        workspaceId: ws, projectId: project, alias: `${tag}-dup`,
        destinationUrl: 'https://example.com/a',
      });
      await expect(
        createLink(tx, {
          workspaceId: ws, projectId: project, alias: `${tag}-dup`,
          destinationUrl: 'https://example.com/b',
        }),
      ).rejects.toMatchObject({ reason: 'alias_taken' });
    }, { db });
  });
});

/* -------------------------------------------------------------- resolution */

describe('resolution', () => {
  it('round-trips a link through the shared resolver', async () => {
    await withWorkspace(ws, async (tx) => {
      const { alias } = await createLink(tx, {
        workspaceId: ws, projectId: project,
        destinationUrl: 'https://example.com/landing',
        utm: { source: 'poster', medium: 'print' },
      });

      const loaded = await loadForResolve(tx, { alias });
      expect(loaded).not.toBeNull();

      const outcome = resolve({
        link: loaded!.link,
        rules: loaded!.rules,
        visitor: { country: 'DE', os: 'iOS' },
      });
      expect(outcome.kind).toBe('redirect');
      // 302, never 301 — a permanent redirect is cached forever and this link
      // is editable by definition.
      expect(outcome.kind === 'redirect' && outcome.status).toBe(302);
      expect(outcome.kind === 'redirect' && outcome.url).toContain('utm_source=poster');
    }, { db });
  });

  it('a disabled link resolves to blocked, not to its destination', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id, alias } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/x',
      });
      await tx.execute(sql`update links set is_enabled = false where id = ${id}`);
      const loaded = await loadForResolve(tx, { alias });
      expect(resolve({ link: loaded!.link, rules: [], visitor: {} })).toEqual({
        kind: 'blocked', reason: 'disabled',
      });
    }, { db });
  });

  it('counts clicks atomically', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/c',
      });
      // Read-modify-write would lose one of these; `+ 1` in the database does not.
      const counts = await Promise.all([
        recordClick(tx, { linkId: id }),
        recordClick(tx, { linkId: id }),
        recordClick(tx, { linkId: id }),
      ]);
      expect(Math.max(...counts)).toBe(3);
    }, { db });
  });
});

/* ------------------------------------------------------------------- rules */

describe('rules and rotation', () => {
  it('saves an ordered rule list and evaluates the first match', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id, alias } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/global',
      });
      await setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [
          {
            priority: 0, sticky: false, isEnabled: true,
            match: { match: 'all', conditions: [{ field: 'country', op: 'is', value: 'DE' }] },
            action: { type: 'redirect', destinationUrl: 'https://example.de/' },
          },
          {
            priority: 1, sticky: false, isEnabled: true,
            match: { match: 'all', conditions: [{ field: 'os', op: 'is', value: 'iOS' }] },
            action: { type: 'redirect', destinationUrl: 'https://apps.example.com/' },
          },
        ],
      });

      const loaded = await loadForResolve(tx, { alias });
      const german = resolve({ link: loaded!.link, rules: loaded!.rules, visitor: { country: 'DE', os: 'iOS' } });
      expect(german.kind === 'redirect' && german.url).toBe('https://example.de/');

      const iphone = resolve({ link: loaded!.link, rules: loaded!.rules, visitor: { country: 'FR', os: 'iOS' } });
      expect(iphone.kind === 'redirect' && iphone.url).toBe('https://apps.example.com/');
    }, { db });
  });

  it('replaces the rule list wholesale rather than accumulating', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id, alias } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/r',
      });
      const rule = (url: string) => ({
        priority: 0, sticky: false, isEnabled: true, match: {},
        action: { type: 'redirect' as const, destinationUrl: url },
      });
      await setRules(tx, { workspaceId: ws, linkId: id, rules: [rule('https://one.test/')] });
      await setRules(tx, { workspaceId: ws, linkId: id, rules: [rule('https://two.test/')] });

      const loaded = await loadForResolve(tx, { alias });
      expect(loaded!.rules).toHaveLength(1);
    }, { db });
  });

  it('refuses a match shape that would silently target everyone', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/typo',
      });
      // `{ all: [...] }` looks like it says Germany. It has no `conditions`, so
      // it is an empty group, and an empty group matches the whole world.
      await expect(setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: false, isEnabled: true,
          match: { all: [{ field: 'country', op: 'is', value: 'DE' }] } as never,
          action: { type: 'redirect', destinationUrl: 'https://example.de/' },
        }],
      })).rejects.toMatchObject({ reason: 'invalid_match' });

      // A misspelled field is refused for the same reason.
      await expect(setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: false, isEnabled: true,
          match: { match: 'all', conditions: [{ field: 'countrie', op: 'is', value: 'DE' }] },
          action: { type: 'redirect', destinationUrl: 'https://example.de/' },
        }],
      })).rejects.toMatchObject({ reason: 'invalid_match' });

      // An empty rule is still legitimate: it means everyone, and says so.
      await setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: false, isEnabled: true, match: {},
          action: { type: 'redirect', destinationUrl: 'https://example.de/' },
        }],
      });
    }, { db });
  });

  it('refuses a rotation that cannot pick anything', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/rot',
      });
      await expect(setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: true, isEnabled: true, match: {},
          action: { type: 'rotate', variants: [{ url: 'https://a.test/', weight: 0 }] },
        }],
      })).rejects.toMatchObject({ reason: 'empty_rotation' });
    }, { db });
  });

  it('refuses two winners, because the test would have no result', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/rot2',
      });
      await expect(setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: true, isEnabled: true, match: {},
          action: {
            type: 'rotate',
            variants: [
              { url: 'https://a.test/', weight: 1, isWinner: true },
              { url: 'https://b.test/', weight: 1, isWinner: true },
            ],
          },
        }],
      })).rejects.toMatchObject({ reason: 'two_winners' });
    }, { db });
  });

  it('keeps a visitor on the arm they were first shown', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id, alias } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/ab',
      });
      await setRules(tx, {
        workspaceId: ws, linkId: id,
        rules: [{
          priority: 0, sticky: true, isEnabled: true, match: {},
          action: {
            type: 'rotate',
            variants: [
              { url: 'https://a.test/', weight: 1 },
              { url: 'https://b.test/', weight: 1 },
            ],
          },
        }],
      });
      const loaded = await loadForResolve(tx, { alias });
      const ruleId = loaded!.rules[0]!.id;
      const visitorHash = 'v'.repeat(16);

      const first = resolve({
        link: loaded!.link, rules: loaded!.rules,
        visitor: { visitorHash } as never,
      });
      const index = first.kind === 'redirect' ? first.variantIndex! : -1;
      await rememberAssignment(tx, {
        workspaceId: ws, linkId: id, ruleId, visitorHash, variantIndex: index,
      });

      // A second write must not be able to move somebody between arms — the
      // first assignment is the honest one.
      await rememberAssignment(tx, {
        workspaceId: ws, linkId: id, ruleId, visitorHash,
        variantIndex: index === 0 ? 1 : 0,
      });

      const stored = await loadAssignment(tx, { ruleIds: [ruleId], visitorHash });
      expect(stored).toEqual({ ruleId, variantIndex: index });

      const second = resolve({
        link: loaded!.link, rules: loaded!.rules,
        visitor: { visitorHash } as never,
        assignment: stored,
      });
      expect(second.kind === 'redirect' && second.url).toBe(
        first.kind === 'redirect' ? first.url : '',
      );
    }, { db });
  });

  it('picks the same variant for the same visitor on every node', () => {
    const variants = [
      { url: 'https://a.test/', weight: 50 },
      { url: 'https://b.test/', weight: 50 },
    ];
    const once = pickVariant(variants, 'stable-hash');
    for (let i = 0; i < 50; i++) expect(pickVariant(variants, 'stable-hash')).toBe(once);
  });
});

/* --------------------------------------------------------------- bio pages */

describe('bio pages', () => {
  it('creates a page with its own link and rejects an unknown block', async () => {
    await withWorkspace(ws, async (tx) => {
      const page = await createBioPage(tx, {
        workspaceId: ws, projectId: project, title: 'Ada', template: 'plain',
      });
      expect(page.alias).toBeTruthy();

      await expect(
        addBlock(tx, { workspaceId: ws, pageId: page.pageId, type: 'not_a_block' }),
      ).rejects.toMatchObject({ reason: 'unknown_type' });
    }, { db });
  });

  it('refuses settings the renderer could not draw', async () => {
    await withWorkspace(ws, async (tx) => {
      const page = await createBioPage(tx, { workspaceId: ws, projectId: project, title: 'Bad' });
      await expect(
        addBlock(tx, {
          workspaceId: ws, pageId: page.pageId, type: 'link',
          settings: { url: 'not a url' },
        }),
      ).rejects.toMatchObject({ reason: 'invalid_settings' });
    }, { db });
  });

  it('never ships a block outside its schedule window', async () => {
    await withWorkspace(ws, async (tx) => {
      const page = await createBioPage(tx, { workspaceId: ws, projectId: project, title: 'Launch' });
      const now = await addBlock(tx, { workspaceId: ws, pageId: page.pageId, type: 'heading' });
      const later = await addBlock(tx, { workspaceId: ws, pageId: page.pageId, type: 'alert' });
      await tx.execute(sql`
        update bio_blocks set starts_at = now() + interval '1 day' where id = ${later}`);

      const visible = await visibleBlocks(tx, page.pageId);
      // Filtered in the query, not the renderer: a scheduled announcement must
      // not reach anyone reading the page source early.
      expect(visible.map((b) => b.id)).toEqual([now]);
      expect(visible[0]!.family).toBe('text');
    }, { db });
  });

  it('accepts every block in the catalogue at its own defaults', async () => {
    await withWorkspace(ws, async (tx) => {
      const page = await createBioPage(tx, { workspaceId: ws, projectId: project, title: 'All' });
      for (const block of BLOCK_CATALOG) {
        await addBlock(tx, { workspaceId: ws, pageId: page.pageId, type: block.key });
      }
      expect(await visibleBlocks(tx, page.pageId)).toHaveLength(BLOCK_CATALOG.length);
    }, { db });
  });
});

/* ------------------------------------------------------------- QR and bars */

describe('QR codes', () => {
  it('gives a dynamic code a link and a static code none', async () => {
    await withWorkspace(ws, async (tx) => {
      const dynamic = await createQrCode(tx, {
        workspaceId: ws, projectId: project, type: 'dynamic_url', name: 'Poster',
        payload: { url: 'https://example.com/campaign' },
      });
      expect(dynamic.linkId).not.toBeNull();
      // Dynamic codes encode our link, so there is nothing to encode here.
      expect(dynamic.encoded).toBeNull();

      const wifi = await createQrCode(tx, {
        workspaceId: ws, projectId: project, type: 'wifi', name: 'Cafe',
        payload: { ssid: 'Cafe', password: 'letmein', encryption: 'WPA' },
      });
      expect(wifi.linkId).toBeNull();
      expect(wifi.encoded).toContain('WIFI:T:WPA;S:Cafe;');
      expect(qrDef('wifi')!.addressing).toBe('static');
    }, { db });
  });

  it('refuses a payload the type cannot encode', async () => {
    await withWorkspace(ws, async (tx) => {
      await expect(createQrCode(tx, {
        workspaceId: ws, projectId: project, type: 'wifi', name: 'No SSID', payload: {},
      })).rejects.toMatchObject({ reason: 'invalid_payload' });
    }, { db });
  });

  it('refuses a barcode value that would print unscannable', async () => {
    await withWorkspace(ws, async (tx) => {
      // A wrong check digit is the classic failure: ten thousand labels that
      // no scanner in the receiving warehouse will read.
      await expect(createBarcode(tx, {
        workspaceId: ws, projectId: project, symbology: 'ean13', value: '9780306406158',
      })).rejects.toMatchObject({ reason: 'invalid_barcode' });

      const id = await createBarcode(tx, {
        workspaceId: ws, projectId: project, symbology: 'ean13', value: '9780306406157',
      });
      expect(id).toBeTruthy();
    }, { db });
  });
});

/* --------------------------------------------------------------- transfers */

describe('transfers', () => {
  it('carries real bytes from a resumed upload through to a download', async () => {
    /*
     * The whole path, with a real storage backend: presign, upload out of
     * order, lose the connection, resume the gap, assemble, download.
     *
     * Bookkeeping-only assertions passed for weeks while `planUpload` returned
     * no URLs at all and nothing could actually be uploaded. What this asserts
     * is that the bytes that come out are the bytes that went in.
     */
    const storage = localAdapter({ root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE });

    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Drafts',
      });

      // Four parts: three full and a short tail, which is where off-by-one
      // errors in the part count live.
      const content = randomBytes(PART_SIZE * 3 + 10);
      const plan = await planUpload(tx, storage, {
        workspaceId: ws, transferId: transfer.id, name: 'deck.pdf',
        sizeBytes: content.length,
      });
      expect(plan.parts).toBe(4);
      expect(plan.partUrls).toHaveLength(4);

      const chunk = (n: number) => content.subarray((n - 1) * PART_SIZE, n * PART_SIZE);
      const put = async (n: number) => {
        // The URL the browser was given has to be the one that works.
        const params = new URL(plan.partUrls[n - 1]!).searchParams;
        const verified = verifySignature(STORAGE_SECRET, 'PUT', params);
        expect(verified.ok, `part ${n} URL must verify`).toBe(true);
        const { etag } = await writeLocalPart(
          { root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE },
          plan.storageKey, n, chunk(n),
        );
        await registerPart(tx, { fileId: plan.fileId, partNumber: n, etag });
      };

      // Out of order, with a retry, and part 2 never sent — a dropped
      // connection mid-flight.
      await put(3);
      await put(1);
      await put(1);
      await put(4);
      expect(await uploadedParts(tx, plan.fileId)).toEqual([1, 3, 4]);

      const early = await finaliseTransfer(tx, storage, transfer.id);
      expect(early).toEqual({ ready: false, pending: ['deck.pdf'] });

      // Coming back later: fresh URLs for the hole, not for everything.
      const resumed = await resumeUpload(tx, storage, plan.fileId);
      expect(resumed.missing.map((m) => m.partNumber)).toEqual([2]);
      await put(2);

      await completeFile(tx, { fileId: plan.fileId, checksumSha256: 'a'.repeat(64) });
      expect(await finaliseTransfer(tx, storage, transfer.id)).toEqual({ ready: true });

      const stored = await readLocalObject(
        { root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE },
        plan.storageKey,
      );
      expect(stored, 'the assembled object must exist').not.toBeNull();
      expect(stored!.equals(content), 'byte-for-byte what was uploaded').toBe(true);

      const [link] = await downloadUrl(tx, storage, { transferId: transfer.id });
      expect(link!.name).toBe('deck.pdf');
      const download = verifySignature(STORAGE_SECRET, 'GET', new URL(link!.url).searchParams);
      expect(download).toMatchObject({ ok: true, key: plan.storageKey, name: 'deck.pdf' });
    }, { db });
  });

  it('refuses a file bigger than the plan allows, before a byte moves', async () => {
    const storage = localAdapter({ root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE });
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Too big',
      });

      /*
       * Link Pro allows 20 GB per transfer; this asks for 21. The check happens
       * in `planUpload`, before any URL is handed out — deciding afterwards
       * means having already accepted bytes we then have to refuse, and every
       * honest option at that point is bad.
       */
      await expect(
        planUpload(tx, storage, {
          workspaceId: ws, transferId: transfer.id, name: 'huge.iso',
          sizeBytes: 21_000 * 1_000_000,
        }),
      ).rejects.toMatchObject({ reason: 'limit_reached' });

      const [files] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from transfer_files where transfer_id = ${transfer.id}`);
      expect(files!.n, 'a refused file leaves no row behind').toBe(0);
    }, { db });
  });

  it('counts what the transfer already holds, not just the new file', async () => {
    const storage = localAdapter({ root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE });
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Two halves',
      });
      // Two files that each fit and together do not. Checking the file alone
      // would let somebody past the limit by splitting it in two.
      await planUpload(tx, storage, {
        workspaceId: ws, transferId: transfer.id, name: 'a.bin', sizeBytes: 15_000 * 1_000_000,
      });
      await expect(
        planUpload(tx, storage, {
          workspaceId: ws, transferId: transfer.id, name: 'b.bin', sizeBytes: 15_000 * 1_000_000,
        }),
      ).rejects.toMatchObject({ reason: 'limit_reached' });
    }, { db });
  });

  it('never puts the filename in the object key', async () => {
    const storage = localAdapter({ root: storageRoot, secret: STORAGE_SECRET, baseUrl: STORAGE_BASE });
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Confidential',
      });
      const plan = await planUpload(tx, storage, {
        workspaceId: ws, transferId: transfer.id,
        name: 'acme-acquisition-final.pdf', sizeBytes: 10,
      });
      /*
       * Keys reach logs, CDN traces and presigned URLs. A filename there is a
       * disclosure even when the URL itself is signed — the name is restored
       * at download time through Content-Disposition instead.
       */
      expect(plan.storageKey).not.toContain('acme');
      expect(plan.partUrls[0]).not.toContain('acme');
    }, { db });
  });

  it('lets exactly one of two simultaneous claims take the last download', async () => {
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'One shot', downloadLimit: 1,
      });
      await tx.execute(sql`update transfers set status = 'ready' where id = ${transfer.id}`);

      const [a, b] = await Promise.all([
        claimDownload(tx, { transferId: transfer.id }),
        claimDownload(tx, { transferId: transfer.id }),
      ]);
      expect([a.ok, b.ok].filter(Boolean)).toHaveLength(1);
      const refused = a.ok ? b : a;
      expect(refused.ok === false && refused.reason).toBe('limit_reached');
    }, { db });
  });

  it('gates on a password without leaking its length', async () => {
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Locked', password: 'correct horse',
      });
      const wrong = await claimDownload(tx, { transferId: transfer.id, password: 'nope' });
      expect(wrong.ok === false && wrong.reason).toBe('password');
      expect((await claimDownload(tx, { transferId: transfer.id, password: 'correct horse' })).ok).toBe(true);

      const [row] = await tx.execute<{ password_hash: string }>(sql`
        select password_hash from transfers where id = ${transfer.id}`);
      // Never the plaintext, and salted so a leaked table is not a wordlist run.
      expect(row!.password_hash).not.toContain('correct horse');
      expect(verifyPassword(row!.password_hash, 'correct horse')).toBe(true);
      expect(verifyPassword(null, 'anything')).toBe(true);
    }, { db });
  });

  it('tells a recipient why a transfer was pulled back', async () => {
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Oops',
      });
      await cancelTransfer(tx, { workspaceId: ws, transferId: transfer.id, reason: 'Wrong file' });
      const claim = await claimDownload(tx, { transferId: transfer.id });
      expect(claim.ok === false && claim.reason).toBe('cancelled');
    }, { db });
  });

  it('needs a recipient before it can deliver by email', async () => {
    await withWorkspace(ws, async (tx) => {
      await expect(createTransfer(tx, {
        workspaceId: ws, projectId: project, delivery: 'email', recipients: [],
      })).rejects.toMatchObject({ reason: 'no_recipients' });
    }, { db });
  });

  it('marks expiry rather than deleting, so the bytes survive one mistake', async () => {
    await withWorkspace(ws, async (tx) => {
      const transfer = await createTransfer(tx, {
        workspaceId: ws, projectId: project, subject: 'Gone',
      });
      await tx.execute(sql`
        update transfers set status = 'ready', expires_at = now() - interval '1 hour'
         where id = ${transfer.id}`);
      expect(await expireDue(tx, ws)).toBeGreaterThanOrEqual(1);

      const [row] = await tx.execute<{ status: string }>(sql`
        select status from transfers where id = ${transfer.id}`);
      expect(row!.status).toBe('expired');
    }, { db });
  });
});

/* ------------------------------------------------------------- cross-tool */

describe('cross-tool handoffs', () => {
  const envelope = (name: string, data: Record<string, unknown>) => ({
    id: randomUUID(), name, version: 1 as const,
    occurredAt: new Date().toISOString(),
    workspaceId: ws, projectId: project,
    actor: { kind: 'system' as const },
    subject: `urn:mamal:monitor:monitor:${randomUUID()}`,
    related: [], data,
    trace: { correlationId: randomUUID(), depth: 0 },
  });

  it('fails a link over while its destination is down, and restores it exactly', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project,
        destinationUrl: 'https://shop.example.com/spring-sale?ref=ig',
      });

      const down = linkSubscriptions.find((h) => h.key === 'link:failover-while-target-down')!;
      await down.handle(
        envelope('monitor.incident.opened', {
          targetUrl: 'https://shop.example.com/',
          fallbackUrl: 'https://status.example.com/',
        }) as never,
        tx as never,
      );

      const [failed] = await tx.execute<{ destination_url: string; settings: Record<string, unknown> }>(sql`
        select destination_url, settings from links where id = ${id}`);
      expect(failed!.destination_url).toBe('https://status.example.com/');
      // The real destination is stashed, not overwritten — it is the only copy.
      expect((failed!.settings as { failover: { previous: string } }).failover.previous)
        .toBe('https://shop.example.com/spring-sale?ref=ig');

      const up = linkSubscriptions.find((h) => h.key === 'link:restore-after-recovery')!;
      const recovery = envelope('monitor.target.recovered', { targetUrl: 'https://shop.example.com/' });
      await up.handle(recovery as never, tx as never);

      const [restored] = await tx.execute<{ destination_url: string; settings: Record<string, unknown> }>(sql`
        select destination_url, settings from links where id = ${id}`);
      expect(restored!.destination_url).toBe('https://shop.example.com/spring-sale?ref=ig');
      expect(restored!.settings).not.toHaveProperty('failover');

      // Redelivery is normal on this bus. The second one must be a no-op, not
      // an overwrite of whatever the customer has edited since.
      await tx.execute(sql`
        update links set destination_url = 'https://shop.example.com/new' where id = ${id}`);
      await up.handle(recovery as never, tx as never);
      const [after] = await tx.execute<{ destination_url: string }>(sql`
        select destination_url from links where id = ${id}`);
      expect(after!.destination_url).toBe('https://shop.example.com/new');
    }, { db });
  });

  it('files a broken external link as a suggestion, not as a link', async () => {
    await withWorkspace(ws, async (tx) => {
      const handler = linkSubscriptions
        .find((h) => h.key === 'link:offer-managed-link-for-broken-external')!;
      const event = envelope('audit.issue.detected', {
        ruleId: 'broken-external-link',
        targetUrl: 'https://dead.example.org/gone',
        pageUrl: 'https://mine.example.com/post',
      });

      await handler.handle(event as never, tx as never);
      // A nightly crawl re-reports the same broken link; the queue must not
      // grow a row a night.
      await handler.handle(event as never, tx as never);

      const rows = await tx.execute<{ id: string; status: string }>(sql`
        select id, status from link_suggestions
         where workspace_id = ${ws} and target_url = 'https://dead.example.org/gone'`);
      expect(rows).toHaveLength(1);
      expect(rows[0]!.status).toBe('open');

      const [links] = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from links
         where workspace_id = ${ws} and destination_url = 'https://dead.example.org/gone'`);
      expect(links!.count).toBe(0);
    }, { db });
  });

  it('ignores an audit issue that is not a broken external link', async () => {
    await withWorkspace(ws, async (tx) => {
      const handler = linkSubscriptions
        .find((h) => h.key === 'link:offer-managed-link-for-broken-external')!;
      await handler.handle(
        envelope('audit.issue.detected', {
          ruleId: 'missing-h1', targetUrl: 'https://mine.example.com/p',
        }) as never,
        tx as never,
      );
      const [n] = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from link_suggestions
         where workspace_id = ${ws} and kind = 'replace_broken_external'
           and target_url = 'https://mine.example.com/p'`);
      expect(n!.count).toBe(0);
    }, { db });
  });
});

/* -------------------------------------------------------------- retention */

describe('retention', () => {
  it('sweeps ephemera and leaves the links themselves alone', async () => {
    await withWorkspace(ws, async (tx) => {
      const { id } = await createLink(tx, {
        workspaceId: ws, projectId: project, destinationUrl: 'https://example.com/keep',
      });
      const reportId = await reportAbuse(tx, {
        workspaceId: ws, linkId: id, reason: 'spam', reporterEmail: 'someone@example.org',
      });
      await tx.execute(sql`
        update abuse_reports
           set status = 'dismissed', created_at = now() - interval '400 days'
         where id = ${reportId}`);

      const before = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from links where id = ${id}`);
      await linkSweeper.sweep(tx, ws, new Date(Date.now() - 90 * 86_400_000));

      const [gone] = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from abuse_reports where id = ${reportId}`);
      expect(gone!.count).toBe(0);

      // A short link on printed material outlives every retention window, and
      // deleting it would turn a business card into a 404.
      const [kept] = await tx.execute<{ count: number }>(sql`
        select count(*)::int as count from links where id = ${id}`);
      expect(kept!.count).toBe(before[0]!.count);
    }, { db });
  });
});

/* --------------------------------------------------------------- manifest */

describe('the manifest', () => {
  it('declares every event with a three-segment name', () => {
    // The envelope schema enforces `<tool>.<noun>.<past-tense>`. A subscription
    // to a name it rejects looks correct and can never fire — which is exactly
    // how `monitor.up` shipped dead.
    for (const e of linkManifest.events) {
      expect(e.name, e.name).toMatch(/^[a-z]+\.[a-z_]+\.[a-z_]+$/);
      expect(e.name.startsWith('link.'), e.name).toBe(true);
    }
    for (const s of linkManifest.subscriptions) {
      expect(s.event, s.event).toMatch(/^[a-z]+\.[a-z_]+\.[a-z_]+$/);
    }
  });

  it('has a handler for every subscription it declares', () => {
    const keys = new Set(linkSubscriptions.map((h) => h.key));
    for (const s of linkManifest.subscriptions) expect(keys.has(s.handlerKey), s.handlerKey).toBe(true);
  });

  it('gives every resource type a route the palette can open', () => {
    for (const r of linkManifest.resources) {
      expect(r.href, r.type).toMatch(/^\/link\//);
      expect(r.href, r.type).toContain(':id');
    }
  });
});

/* -------------------------------------------------------------------- bulk */

describe('bulk import', () => {
  it('parses the CSV a spreadsheet actually exports', () => {
    // Commas inside quoted fields, doubled quotes, a BOM, and CRLF endings.
    const csv =
      '﻿url,title\r\n' +
      '"https://example.com/a?x=1,2","Spring, summer"\r\n' +
      '"https://example.com/b","He said ""hello"""\r\n';
    const plan = planBulk(csv);
    expect(plan.problems).toEqual([]);
    expect(plan.rows).toHaveLength(2);
    expect(plan.rows[0]!.url).toBe('https://example.com/a?x=1,2');
    expect(plan.rows[0]!.title).toBe('Spring, summer');
    expect(plan.rows[1]!.title).toBe('He said "hello"');
  });

  it('accepts the column names people actually type', () => {
    for (const header of ['url', 'destination', 'link', 'Destination URL', 'target']) {
      const plan = planBulk(`${header}\nhttps://example.com/x\n`);
      expect(plan.problems, header).toEqual([]);
      expect(plan.rows, header).toHaveLength(1);
    }
    // And says what it wants when none of them are there.
    const bad = planBulk('href\nhttps://example.com/x\n');
    expect(bad.problems[0]!.message).toMatch(/No destination column/);
  });

  it('reports every problem, not just the first', () => {
    const plan = planBulk(
      'url,alias\n' +
      'not-a-url,ok-one\n' +
      'ftp://example.com/x,ok-two\n' +
      'https://example.com/a,login\n' +
      'https://example.com/b,dup\n' +
      'https://example.com/c,dup\n',
    );
    // Somebody fixing a 10,000-row export needs the whole list.
    expect(plan.problems.map((p) => p.line)).toEqual([2, 3, 4, 6]);
    expect(plan.problems[0]!.message).toMatch(/not a URL/);
    expect(plan.problems[1]!.message).toMatch(/not a web address/);
    expect(plan.problems[2]!.message).toMatch(/reserved/);
    expect(plan.problems[3]!.message).toMatch(/also on line 5/);
    // Only line 5 survives: 2 and 3 are bad URLs, 4 is reserved, and 6
    // repeats 5's alias.
    expect(plan.rows).toHaveLength(1);
  });

  it('reads UTM columns and semicolon-separated tags', () => {
    const plan = planBulk(
      'url,utm_source,utm_medium,tags\n' +
      'https://example.com/a,poster,print,spring;print;q2\n',
    );
    expect(plan.rows[0]!.utm).toEqual({ source: 'poster', medium: 'print' });
    expect(plan.rows[0]!.tags).toEqual(['spring', 'print', 'q2']);
  });

  it('refuses a file bigger than one import', () => {
    const rows = Array.from({ length: MAX_BULK_ROWS + 5 }, (_, i) => `https://example.com/${i}`);
    const plan = planBulk(`url\n${rows.join('\n')}\n`);
    expect(plan.problems[0]!.message).toMatch(/Split the file/);
    expect(plan.rows).toHaveLength(MAX_BULK_ROWS);
  });

  it('imports a file, generating the aliases it was not given', async () => {
    await withWorkspace(ws, async (tx) => {
      const csv =
        'url,alias,title,campaign\n' +
        `https://example.com/bulk-1,${tag}-b1,First,spring\n` +
        'https://example.com/bulk-2,,Second,spring\n' +
        'https://example.com/bulk-3,,Third,spring\n';

      const result = await importLinks(tx, {
        workspaceId: ws, projectId: project, csv,
      });
      expect(result.problems).toEqual([]);
      expect(result.created).toHaveLength(3);
      expect(result.created[0]!.alias).toBe(`${tag}-b1`);
      // The rest are generated, distinct, and from the unambiguous alphabet.
      const generated = result.created.slice(1).map((c) => c.alias);
      expect(new Set(generated).size).toBe(2);
      for (const alias of generated) expect(alias).not.toMatch(/[0O1lI]/);

      const [count] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links
         where workspace_id = ${ws} and campaign = 'spring' and deleted_at is null`);
      expect(count!.n).toBe(3);
    }, { db });
  });

  it('creates nothing when the file asks for an alias that is taken', async () => {
    await withWorkspace(ws, async (tx) => {
      const [before] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);

      const result = await importLinks(tx, {
        workspaceId: ws, projectId: project,
        csv: `url,alias\nhttps://example.com/x,${tag}-b1\n`,
      });

      // The row is reported and skipped; nothing else in the file is punished
      // for it, and here there is nothing else.
      expect(result.created).toHaveLength(0);
      expect(result.problems[0]!.message).toMatch(/already in use/);

      const [after] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);
      expect(after!.n).toBe(before!.n);
    }, { db });
  });

  it('changes nothing on a dry run', async () => {
    await withWorkspace(ws, async (tx) => {
      const [before] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);

      const result = await importLinks(tx, {
        workspaceId: ws, projectId: project, dryRun: true,
        csv: 'url\nhttps://example.com/dry-1\nhttps://example.com/dry-2\n',
      });
      expect(result.created).toHaveLength(2);

      const [after] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);
      expect(after!.n, 'a dry run must write nothing').toBe(before!.n);
    }, { db });
  });

  it('refuses the whole batch rather than importing part of it', async () => {
    await withWorkspace(ws, async (tx) => {
      const [before] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);

      // Link Pro allows 10,000 and this workspace already holds some, so a
      // full-size file does not fit. The point is that it imports *none* of it.
      const rows = Array.from({ length: MAX_BULK_ROWS }, (_, i) => `https://example.com/over/${i}`);
      await expect(
        importLinks(tx, { workspaceId: ws, projectId: project, csv: `url\n${rows.join('\n')}\n` }),
      ).rejects.toMatchObject({ reason: 'limit_reached' });

      const [after] = await tx.execute<{ n: number }>(sql`
        select count(*)::int as n from links where workspace_id = ${ws} and deleted_at is null`);
      expect(after!.n, 'a refused import writes nothing').toBe(before!.n);
    }, { db });
  });

  it('names the shortfall, rather than the count alone', async () => {
    await withWorkspace(ws, async (tx) => {
      const rows = Array.from({ length: MAX_BULK_ROWS }, (_, i) => `https://example.com/msg/${i}`);
      const error = await importLinks(tx, {
        workspaceId: ws, projectId: project, csv: `url\n${rows.join('\n')}\n`,
      }).then(
        () => null,
        (e: unknown) => e as Error,
      );
      expect(error, 'the import must be refused').not.toBeNull();

      /*
       * "You have used 26 of 10,000" is true and useless here — it reads as
       * though there is room. What the person has to act on is how many of
       * their rows will fit.
       */
      expect(error!.message).toMatch(/would add 10,000/);
      expect(error!.message).toMatch(/room for/);
    }, { db });
  });

  it('imports 10,000 rows in one go', async () => {
    /*
     * A workspace of its own, because the throughput this is measuring only
     * happens when the whole file fits — and every other test in this file has
     * been putting links in the shared one.
     */
    const bulkWs = await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${`${tag}-bulk@test.local`}, 'Bulk') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id)
        values (${`${tag}-bulk`}, 'Bulk', ${u!.id}) returning id`);
      await tx.execute(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${w!.id}, 'Default', 'default', true)`);
      await tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status)
        select ${w!.id}, id, 'active' from plans where key = 'link_pro'`);
      return w!.id;
    }, { db });

    try {
      await withWorkspace(bulkWs, async (tx) => {
        const [p] = await tx.execute<{ id: string }>(sql`
          select id from projects where workspace_id = ${bulkWs} limit 1`);
        const rows = Array.from({ length: MAX_BULK_ROWS }, (_, i) => `https://example.com/mass/${i}`);

        const started = Date.now();
        const result = await importLinks(tx, {
          workspaceId: bulkWs, projectId: p!.id, csv: `url\n${rows.join('\n')}\n`,
        });
        const elapsed = Date.now() - started;

        expect(result.problems).toEqual([]);
        expect(result.created).toHaveLength(MAX_BULK_ROWS);
        expect(new Set(result.created.map((c) => c.alias)).size).toBe(MAX_BULK_ROWS);
        // Not a benchmark — a guard against somebody reintroducing a per-row
        // round trip, which takes this from seconds to minutes.
        expect(elapsed, `took ${elapsed}ms`).toBeLessThan(30_000);
      }, { db });
    } finally {
      await asPlatformAdmin(
        (tx) => tx.execute(sql`delete from workspaces where id = ${bulkWs}`),
        { db },
      );
    }
  }, 90_000);
});
