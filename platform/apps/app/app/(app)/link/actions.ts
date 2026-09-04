'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  addBlock, cancelTransfer, createBarcode, createBioPage, createLink, createQrCode,
  createTransfer, finaliseTransfer, importLinks, planUpload, registerPart, resumeUpload,
  setLinkPassword, setRules, shortUrl, LinkNotAllowed,
} from '@mamal/tool-link';
import { resolveAdapter } from '@mamal/storage';
import { checkOneDomain } from '@mamal/domains';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

async function ctx() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  return { ws: session.workspace.id, database: db() };
}

export type ActionResult<T = unknown> =
  | ({ ok: true } & T)
  | { ok: false; error: string };

/**
 * One catch, in one place.
 *
 * `LinkNotAllowed` carries the entitlement resolver's own sentence — "You have
 * used 25 of 25 links" — and that sentence is the whole point of the resolver
 * returning a *reason* rather than a boolean. Swallowing it into "something
 * went wrong" would throw away the only useful part.
 */
async function attempt<T>(run: () => Promise<T>): Promise<ActionResult<{ value: T }>> {
  try {
    return { ok: true, value: await run() };
  } catch (e) {
    if (e instanceof LinkNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

async function defaultProject(tx: Parameters<Parameters<typeof withWorkspace>[1]>[0], ws: string) {
  const [p] = await (tx as { execute: <T>(q: unknown) => Promise<T[]> }).execute<{ id: string }>(sql`
    select id from projects where workspace_id = ${ws} order by is_default desc, created_at limit 1`);
  if (!p) throw new LinkNotAllowed('no_project', 'This workspace has no project yet.');
  return p.id;
}

/* ------------------------------------------------------------------- links */

export async function newLink(input: {
  url: string;
  alias?: string;
  title?: string;
  campaign?: string;
  tags?: string[];
  utm?: Record<string, string>;
}): Promise<ActionResult<{ id: string; alias: string; url: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createLink(tx, {
        workspaceId: ws, projectId,
        destinationUrl: input.url,
        alias: input.alias?.trim() || undefined,
        title: input.title,
        campaign: input.campaign,
        tags: input.tags,
        utm: input.utm,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link');
  return { ok: true, id: result.value.id, alias: result.value.alias, url: shortUrl(result.value.alias) };
}

export async function updateLink(
  id: string,
  patch: {
    destinationUrl?: string;
    title?: string;
    isEnabled?: boolean;
    expiresAt?: string | null;
    expiresUrl?: string | null;
    maxClicks?: number | null;
    campaign?: string | null;
    settings?: Record<string, unknown>;
  },
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      /*
       * `coalesce(param, column)` for every field.
       *
       * The editor saves one section at a time, so an update carries three
       * fields and not fifteen. Writing the whole row from a partial form would
       * blank everything the customer did not have open.
       */
      await tx.execute(sql`
        update links set
          destination_url = coalesce(${patch.destinationUrl ?? null}, destination_url),
          title           = coalesce(${patch.title ?? null}, title),
          is_enabled      = coalesce(${patch.isEnabled ?? null}, is_enabled),
          expires_at      = ${patch.expiresAt === undefined ? sql`expires_at` : patch.expiresAt ? sql`${patch.expiresAt}::timestamptz` : sql`null`},
          expires_url     = ${patch.expiresUrl === undefined ? sql`expires_url` : patch.expiresUrl},
          max_clicks      = ${patch.maxClicks === undefined ? sql`max_clicks` : patch.maxClicks},
          campaign        = ${patch.campaign === undefined ? sql`campaign` : patch.campaign},
          settings        = ${patch.settings ? sql`settings || ${JSON.stringify(patch.settings)}::jsonb` : sql`settings`},
          updated_at      = now()
        where id = ${id} and workspace_id = ${ws}`);
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link');
  revalidatePath(`/link/links/${id}`);
  return { ok: true };
}

/**
 * Soft-deletes, so the toast can undo it.
 *
 * Every destructive action in the platform is undoable for ten seconds rather
 * than confirmed by a dialog. The alias is released by the partial unique index
 * the moment `deleted_at` is set — which means undo can fail if somebody claimed
 * it in between, and `restoreLink` says so rather than pretending.
 */
export async function deleteLink(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update links set deleted_at = now() where id = ${id} and workspace_id = ${ws}`),
    { db: database });
  revalidatePath('/link');
  return { ok: true };
}

export async function restoreLink(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const rows = await withWorkspace(ws, (tx) => tx.execute<{ id: string }>(sql`
    update links set deleted_at = null
     where id = ${id} and workspace_id = ${ws} and deleted_at is not null
       and not exists (
         select 1 from links other
          where other.alias = links.alias
            and other.custom_domain_id is not distinct from links.custom_domain_id
            and other.deleted_at is null)
    returning id`), { db: database });

  revalidatePath('/link');
  if (rows.length === 0) {
    return { ok: false, error: 'That short link was taken by somebody else while it was deleted.' };
  }
  return { ok: true };
}

export async function setPassword(linkId: string, password: string | null): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => setLinkPassword(tx, { workspaceId: ws, linkId, password }), { db: database });
  revalidatePath(`/link/links/${linkId}`);
  return { ok: true };
}

export async function saveRules(
  linkId: string,
  rules: Parameters<typeof setRules>[1]['rules'],
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, (tx) => setRules(tx, { workspaceId: ws, linkId, rules }), { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath(`/link/links/${linkId}`);
  return { ok: true };
}

/* --------------------------------------------------------------- bio pages */

export async function newBioPage(title: string, alias?: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createBioPage(tx, { workspaceId: ws, projectId, title, alias: alias?.trim() || undefined });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/bio');
  return { ok: true, id: result.value.pageId };
}

export async function newBlock(pageId: string, type: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, (tx) => addBlock(tx, { workspaceId: ws, pageId, type }), { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath(`/link/bio/${pageId}`);
  return { ok: true, id: result.value };
}

export async function saveBlock(
  pageId: string,
  blockId: string,
  settings: unknown,
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const [row] = await tx.execute<{ type: string }>(sql`
        select type from bio_blocks where id = ${blockId} and workspace_id = ${ws}`);
      if (!row) throw new LinkNotAllowed('not_found', 'No such block.');
      // Re-validated through the catalogue rather than trusted from the client:
      // the form is generated from the schema, but the request need not be.
      const { blockDef } = await import('@mamal/link-catalog');
      const def = blockDef(row.type);
      if (!def) throw new LinkNotAllowed('unknown_type', `No block type “${row.type}”.`);
      const parsed = def.settings.safeParse(settings);
      if (!parsed.success) {
        throw new LinkNotAllowed(
          'invalid_settings',
          parsed.error.issues.map((i) => `${i.path.join('.') || 'settings'}: ${i.message}`).join('; '),
        );
      }
      await tx.execute(sql`
        update bio_blocks set settings = ${JSON.stringify(parsed.data)}::jsonb, updated_at = now()
         where id = ${blockId} and workspace_id = ${ws}`);
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath(`/link/bio/${pageId}`);
  return { ok: true };
}

export async function reorderBlocks(pageId: string, ids: string[]): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, async (tx) => {
    for (const [i, id] of ids.entries()) {
      await tx.execute(sql`
        update bio_blocks set sort_order = ${i}
         where id = ${id} and page_id = ${pageId} and workspace_id = ${ws}`);
    }
  }, { db: database });
  revalidatePath(`/link/bio/${pageId}`);
  return { ok: true };
}

export async function removeBlock(pageId: string, blockId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    delete from bio_blocks where id = ${blockId} and workspace_id = ${ws}`), { db: database });
  revalidatePath(`/link/bio/${pageId}`);
  return { ok: true };
}

export async function publishBioPage(pageId: string, published: boolean): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update bio_pages set is_published = ${published}, updated_at = now()
     where id = ${pageId} and workspace_id = ${ws}`), { db: database });
  revalidatePath(`/link/bio/${pageId}`);
  return { ok: true };
}

/* -------------------------------------------------------------- QR and bar */

export async function newQr(input: {
  type: string;
  name: string;
  payload: Record<string, unknown>;
  style?: Record<string, unknown>;
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createQrCode(tx, {
        workspaceId: ws, projectId,
        type: input.type, name: input.name,
        payload: input.payload, style: input.style,
        destinationUrl: typeof input.payload.url === 'string' ? input.payload.url : undefined,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/qr');
  return { ok: true, id: result.value.id };
}

export async function saveQrStyle(id: string, style: Record<string, unknown>): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const { qrStyleSchema } = await import('@mamal/link-catalog');
  const parsed = qrStyleSchema.safeParse(style);
  if (!parsed.success) {
    return { ok: false, error: parsed.error.issues.map((i) => i.message).join('; ') };
  }
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update qr_codes set style = ${JSON.stringify(parsed.data)}::jsonb, updated_at = now()
     where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/qr');
  return { ok: true };
}

export async function newBarcode(symbology: string, value: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createBarcode(tx, { workspaceId: ws, projectId, symbology, value });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/barcodes');
  return { ok: true, id: result.value };
}

/* --------------------------------------------------------------- transfers */

export async function newTransfer(input: {
  subject: string;
  message?: string;
  recipients?: string[];
  delivery?: 'link' | 'email';
  password?: string;
  expiresInDays?: number;
}): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return createTransfer(tx, { workspaceId: ws, projectId, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/transfers');
  return { ok: true, id: result.value.id };
}

export async function pullBackTransfer(id: string, reason: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => cancelTransfer(tx, { workspaceId: ws, transferId: id, reason }), { db: database });
  revalidatePath('/link/transfers');
  return { ok: true };
}

/* ------------------------------------------------------------- suggestions */

export async function actOnSuggestion(
  id: string,
  action: 'accept' | 'dismiss',
): Promise<ActionResult<{ url?: string }>> {
  const { ws, database } = await ctx();
  if (action === 'dismiss') {
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update link_suggestions set status = 'dismissed', updated_at = now()
       where id = ${id} and workspace_id = ${ws}`), { db: database });
    revalidatePath('/link');
    return { ok: true };
  }

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const [s] = await tx.execute<{ target_url: string; source_urn: string | null }>(sql`
        select target_url, source_urn from link_suggestions
         where id = ${id} and workspace_id = ${ws} and status = 'open'`);
      if (!s) throw new LinkNotAllowed('not_found', 'That suggestion is no longer open.');

      const projectId = await defaultProject(tx, ws);
      const link = await createLink(tx, {
        workspaceId: ws, projectId,
        destinationUrl: s.target_url,
        title: s.target_url,
        sourceUrn: s.source_urn ?? undefined,
      });
      await tx.execute(sql`
        update link_suggestions set status = 'accepted', created_link_id = ${link.id}, updated_at = now()
         where id = ${id} and workspace_id = ${ws}`);
      return shortUrl(link.alias);
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link');
  return { ok: true, url: result.value };
}

/* ----------------------------------------------------------------- folders */

export async function newFolder(name: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      const [row] = await tx.execute<{ id: string }>(sql`
        insert into link_folders (workspace_id, project_id, name, sort_order)
        values (${ws}, ${projectId}, ${name},
                (select coalesce(max(sort_order), -1) + 1 from link_folders where workspace_id = ${ws}))
        returning id`);
      return row!.id;
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/folders');
  return { ok: true, id: result.value };
}

export async function renameFolder(id: string, name: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update link_folders set name = ${name}, updated_at = now()
     where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/folders');
  return { ok: true };
}

/**
 * Deletes a folder and keeps its links.
 *
 * `links.folder_id` carries no foreign key, so nothing cascades — but relying
 * on that would be relying on an absence. Clearing it explicitly says what is
 * meant: a folder is a label, and removing a label never removes what it was on.
 */
export async function removeFolder(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`
      update links set folder_id = null where folder_id = ${id} and workspace_id = ${ws}`);
    await tx.execute(sql`
      delete from link_folders where id = ${id} and workspace_id = ${ws}`);
  }, { db: database });
  revalidatePath('/link/folders');
  revalidatePath('/link');
  return { ok: true };
}

/* ------------------------------------------------------------- UTM presets */

export async function newUtmPreset(
  name: string,
  values: Record<string, string>,
): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      const [row] = await tx.execute<{ id: string }>(sql`
        insert into utm_presets (workspace_id, project_id, name, values)
        values (${ws}, ${projectId}, ${name}, ${JSON.stringify(values)}::jsonb)
        returning id`);
      return row!.id;
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/utm');
  return { ok: true, id: result.value };
}

/**
 * At most one preset applies automatically.
 *
 * Two would mean the later one silently overwriting the earlier one's
 * parameters on every link, with nothing on screen to say which won — so
 * turning one on turns the others off, visibly.
 */
export async function toggleAutoApply(id: string, on: boolean): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, async (tx) => {
    if (on) {
      await tx.execute(sql`
        update utm_presets set auto_apply = false where workspace_id = ${ws} and auto_apply`);
    }
    await tx.execute(sql`
      update utm_presets set auto_apply = ${on}, updated_at = now()
       where id = ${id} and workspace_id = ${ws}`);
  }, { db: database });
  revalidatePath('/link/utm');
  return { ok: true };
}

export async function removeUtmPreset(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    delete from utm_presets where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/utm');
  return { ok: true };
}

/* --------------------------------------------------------------- splash */

export async function newSplashPage(name: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      const [row] = await tx.execute<{ id: string }>(sql`
        insert into splash_pages (workspace_id, project_id, name)
        values (${ws}, ${projectId}, ${name}) returning id`);
      return row!.id;
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/splash');
  return { ok: true, id: result.value };
}

export async function saveSplashPage(
  id: string,
  patch: {
    name?: string; delaySeconds?: number; isSkippable?: boolean;
    autoRedirect?: boolean; title?: string; body?: string;
  },
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update splash_pages set
      name          = coalesce(${patch.name ?? null}, name),
      delay_seconds = coalesce(${patch.delaySeconds ?? null}, delay_seconds),
      is_skippable  = coalesce(${patch.isSkippable ?? null}, is_skippable),
      auto_redirect = coalesce(${patch.autoRedirect ?? null}, auto_redirect),
      settings      = settings || ${JSON.stringify({
        ...(patch.title !== undefined ? { title: patch.title } : {}),
        ...(patch.body !== undefined ? { body: patch.body } : {}),
      })}::jsonb,
      updated_at    = now()
    where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/splash');
  return { ok: true };
}

export async function removeSplashPage(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    delete from splash_pages where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/splash');
  return { ok: true };
}

/* ----------------------------------------------------------------- domains */

/** A hostname we will serve from: no scheme, no path, no port, at least two labels. */
const HOST = /^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/;

export async function addDomain(host: string): Promise<ActionResult<{ id: string }>> {
  const { ws, database } = await ctx();
  const normalised = host.trim().toLowerCase().replace(/^https?:\/\//, '').replace(/\/.*$/, '');

  if (!HOST.test(normalised)) {
    return { ok: false, error: 'Enter a hostname like links.example.com — no https://, no path.' };
  }

  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);

      /*
       * The entitlement is checked here rather than only in the UI.
       *
       * A custom hostname costs us a certificate per hostname — a real
       * third-party invoice line — so this is exactly the kind of gate §0.6
       * says must be enforced by the resolver and not by hiding a button.
       */
      const { loadContext, resolve } = await import('@mamal/entitlements');
      const entitlement = await loadContext(tx, ws, 'core.custom_domains');
      if (entitlement) {
        const [n] = await tx.execute<{ count: number }>(sql`
          select count(*)::int as count from custom_domains where workspace_id = ${ws}`);
        const decision = resolve({ ...entitlement, used: n?.count ?? 0 }, 1);
        if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);
      }

      const token = `mamal-verify-${Math.random().toString(36).slice(2, 14)}`;
      const [row] = await tx.execute<{ id: string }>(sql`
        insert into custom_domains (workspace_id, project_id, host, kind, verification_token)
        values (${ws}, ${projectId}, ${normalised}, 'link', ${token})
        on conflict do nothing
        returning id`);

      // The host uniqueness is global — one hostname resolves to one workspace,
      // so a conflict means somebody else already claimed it.
      if (!row) {
        throw new LinkNotAllowed('host_taken', `${normalised} is already connected to an account.`);
      }
      return row.id;
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/domains');
  return { ok: true, id: result.value };
}

/**
 * Looks the domain up now, rather than waiting for the sweep.
 *
 * It runs the *same* `checkOneDomain` the cron runs, so the button and the
 * background job can never disagree about what DNS says — which was the point
 * of pulling the check into `@mamal/domains` rather than leaving a "mark it
 * pending and hope" stub here.
 *
 * The resolver is allowed to be slow, so this is the one action that can take a
 * couple of seconds. It is a button somebody pressed and is watching.
 */
export async function recheckDomain(
  id: string,
): Promise<ActionResult<{ owned: boolean; routed: boolean; nextStep: string | null }>> {
  const { ws, database } = await ctx();
  const outcome = await withWorkspace(
    ws,
    (tx) =>
      checkOneDomain(tx, {
        workspaceId: ws,
        domainId: id,
        target: process.env.CUSTOM_DOMAIN_TARGET ?? 'cname.mamal.app',
        addresses: (process.env.CUSTOM_DOMAIN_ADDRESSES ?? '')
          .split(',').map((a) => a.trim()).filter(Boolean),
      }),
    { db: database },
  );
  revalidatePath('/link/domains');

  if (!outcome.ok) return { ok: false, error: 'That domain is no longer here.' };
  return { ok: true, owned: outcome.owned, routed: outcome.routed, nextStep: outcome.nextStep };
}

export async function removeDomain(id: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  const rows = await withWorkspace(ws, (tx) => tx.execute<{ links: number }>(sql`
    select (select count(*)::int from links l
             where l.custom_domain_id = ${id} and l.deleted_at is null) as links`),
    { db: database });

  /*
   * Refused while links still point at it.
   *
   * `links.custom_domain_id` is `on delete set null`, so removing the domain
   * would silently move every link onto the platform domain — where its alias
   * may already be taken, and where the printed QR codes do not point.
   */
  if ((rows[0]?.links ?? 0) > 0) {
    return {
      ok: false,
      error: `${rows[0]!.links} link${rows[0]!.links === 1 ? '' : 's'} still use this domain. Move or delete them first.`,
    };
  }

  await withWorkspace(ws, (tx) => tx.execute(sql`
    delete from custom_domains where id = ${id} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/link/domains');
  return { ok: true };
}

/* ------------------------------------------------------- transfer uploads */

/**
 * Reserves space for a file and hands back somewhere to put it.
 *
 * The URLs point at the object store, not at us, so the browser uploads
 * directly and a 5 GB file never occupies a server process. The size is checked
 * against the plan *before* any of it moves — deciding afterwards means
 * accepting bytes we have already agreed to store and then refusing them.
 */
export async function planFileUpload(input: {
  transferId: string;
  name: string;
  sizeBytes: number;
  mimeType?: string;
}): Promise<ActionResult<{
  fileId: string; partSize: number; parts: number; partUrls: string[];
}>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const storage = await resolveAdapter(tx, ws);
      return planUpload(tx, storage, { workspaceId: ws, ...input });
    }, { db: database }),
  );
  if (!result.ok) return result;
  const { fileId, partSize, parts, partUrls } = result.value;
  return { ok: true, fileId, partSize, parts, partUrls };
}

/** Records that a part landed, with the ETag the store issued for it. */
export async function notePartUploaded(
  fileId: string,
  partNumber: number,
  etag: string,
): Promise<ActionResult<{ parts: number[] }>> {
  const { ws, database } = await ctx();
  const parts = await withWorkspace(
    ws,
    (tx) => registerPart(tx, { fileId, partNumber, etag: etag.replace(/"/g, '') }),
    { db: database },
  );
  return { ok: true, parts };
}

/** Fresh URLs for whatever never arrived. This is what makes an upload resumable. */
export async function resumeFileUpload(
  fileId: string,
): Promise<ActionResult<{ partSize: number; missing: { partNumber: number; url: string }[] }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const storage = await resolveAdapter(tx, ws);
      return resumeUpload(tx, storage, fileId);
    }, { db: database }),
  );
  if (!result.ok) return result;
  return { ok: true, ...result.value };
}

/**
 * Assembles the objects and marks the transfer shareable.
 *
 * Refuses while a part is missing: a share link to a truncated archive is
 * worse than one that does not exist yet, because the recipient downloads it,
 * finds it broken, and blames the sender.
 */
export async function readyTransfer(
  transferId: string,
): Promise<ActionResult<{ pending: string[] }>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const storage = await resolveAdapter(tx, ws);
      return finaliseTransfer(tx, storage, transferId);
    }, { db: database }),
  );
  if (!result.ok) return result;
  revalidatePath('/link/transfers');
  return result.value.ready
    ? { ok: true, pending: [] }
    : { ok: false, error: `Still uploading: ${result.value.pending.join(', ')}.` };
}

/* ------------------------------------------------------------------- bulk */

/**
 * Imports a CSV of links.
 *
 * Two passes by design: `dryRun` reports exactly what would happen, and only a
 * second call writes. A ten-thousand-row paste is not something to find out
 * about afterwards — and a partial import is unrecoverable in practice, because
 * the customer cannot tell which half landed.
 */
export async function importCsv(
  csv: string,
  opts: { dryRun?: boolean; folderId?: string | null } = {},
): Promise<ActionResult<{
  created: { alias: string; url: string; destination: string }[];
  problems: { line: number; column: string; message: string }[];
}>> {
  const { ws, database } = await ctx();
  const result = await attempt(() =>
    withWorkspace(ws, async (tx) => {
      const projectId = await defaultProject(tx, ws);
      return importLinks(tx, {
        workspaceId: ws,
        projectId,
        folderId: opts.folderId ?? null,
        csv,
        dryRun: opts.dryRun,
      });
    }, { db: database }),
  );
  if (!result.ok) return result;
  if (!opts.dryRun) revalidatePath('/link');
  return { ok: true, created: result.value.created, problems: result.value.problems };
}
