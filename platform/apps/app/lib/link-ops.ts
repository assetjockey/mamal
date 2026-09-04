import { sql } from 'drizzle-orm';
import { z } from 'zod';
import type { WorkspaceScopedDb } from '@mamal/db';
import { createLink, createQrCode, shortUrl, LinkNotAllowed } from '@mamal/tool-link';
import { QR_CATALOG } from '@mamal/link-catalog';
import { defineOp, limit, cursor, type Op } from '@/lib/ops';

/**
 * Link's operations, shared by REST and MCP.
 *
 * Same contract as Audit's and Confirm's: one definition, two transports, so a
 * filter added to one cannot be missing from the other — and the MCP tool's
 * JSON Schema is generated from the same zod object the REST handler validates
 * against.
 */

const listLinksInput = z.object({
  kind: z.string().max(16).optional().describe('short | biolink | qr | transfer | …'),
  campaign: z.string().max(160).optional(),
  q: z.string().max(200).optional().describe('Matches alias, title or destination.'),
  limit,
  cursor,
});

export const listLinks: Op = defineOp({
  name: 'link_list_links',
  scope: 'link:links:read',
  description: 'List short links with their click counts and current destination.',
  readOnly: true,
  input: listLinksInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, f) => {
    const rows = await tx.execute<{ alias: string }>(sql`
      select id, alias, kind, title, destination_url, campaign, tags,
             is_enabled, clicks_count, max_clicks, expires_at, created_at
        from links
       where workspace_id = ${workspaceId}
         and deleted_at is null
         ${f.kind ? sql`and kind = ${f.kind}` : sql``}
         ${f.campaign ? sql`and campaign = ${f.campaign}` : sql``}
         ${f.q ? sql`and (alias ilike ${'%' + f.q + '%'} or title ilike ${'%' + f.q + '%'}
                          or destination_url ilike ${'%' + f.q + '%'})` : sql``}
         ${f.cursor ? sql`and id > ${f.cursor}` : sql``}
       order by id
       limit ${f.limit + 1}`);
    // The short URL is composed once, in the tool, so what the API returns is
    // the same string the dashboard copies and a QR payload encodes.
    return rows.map((r) => ({ ...r, short_url: shortUrl(r.alias) }));
  },
});

const shortenInput = z.object({
  url: z.string().url().describe('Where the link should send people.'),
  alias: z.string().max(255).optional()
    .describe('Leave empty for a generated one. Letters, digits, hyphens and underscores.'),
  title: z.string().max(200).optional().describe('For your own reference; not shown to visitors.'),
  campaign: z.string().max(160).optional(),
  tags: z.array(z.string().max(48)).max(20).optional(),
  utm: z.record(z.string(), z.string()).optional()
    .describe('Applied to the destination. The link’s own UTM wins over the incoming request’s.'),
});

export const shorten: Op = defineOp({
  name: 'link_shorten',
  scope: 'link:links:write',
  /*
   * A safe write, and the one an agent is most likely to want: it creates
   * something new rather than changing anything that exists, and the result is
   * immediately usable. Re-pointing an existing link is deliberately *not*
   * exposed here — that changes where traffic already in the world goes.
   */
  description:
    'Create a short link. The destination stays editable afterwards, so the same URL can be ' +
    'printed or shared before the final destination is decided.',
  readOnly: false,
  input: shortenInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, i) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${workspaceId}
       order by is_default desc, created_at limit 1`);
    if (!project) throw new LinkNotAllowed('no_project', 'This workspace has no project yet.');

    const link = await createLink(tx, {
      workspaceId,
      projectId: project.id,
      destinationUrl: i.url,
      alias: i.alias,
      title: i.title,
      campaign: i.campaign,
      tags: i.tags,
      utm: i.utm,
    });
    return { id: link.id, alias: link.alias, short_url: shortUrl(link.alias) };
  },
});

const createQrInput = z.object({
  name: z.string().max(160),
  type: z.enum(QR_CATALOG.map((q) => q.key) as [string, ...string[]])
    .default('dynamic_url')
    .describe('Dynamic types resolve through a short link and stay editable after printing.'),
  url: z.string().url().optional().describe('For the URL-shaped types.'),
  payload: z.record(z.string(), z.unknown()).default({})
    .describe('Type-specific fields — ssid and password for wifi, and so on.'),
});

export const createQr: Op = defineOp({
  name: 'link_create_qr',
  scope: 'link:qr:write',
  description:
    'Mint a QR code. Dynamic codes point at a short link, so the destination can be changed ' +
    'after the code is printed and every scan is counted.',
  readOnly: false,
  input: createQrInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, i) => {
    const [project] = await tx.execute<{ id: string }>(sql`
      select id from projects where workspace_id = ${workspaceId}
       order by is_default desc, created_at limit 1`);
    if (!project) throw new LinkNotAllowed('no_project', 'This workspace has no project yet.');

    const code = await createQrCode(tx, {
      workspaceId,
      projectId: project.id,
      type: i.type,
      name: i.name,
      payload: i.url ? { ...i.payload, url: i.url } : i.payload,
      destinationUrl: i.url,
    });
    return {
      id: code.id,
      link_id: code.linkId,
      // Null for a dynamic code: it encodes the short link, which the caller
      // already has from `link_id`.
      encoded: code.encoded,
    };
  },
});

const listQrInput = z.object({ limit, cursor });

export const listQr: Op = defineOp({
  name: 'link_list_qr',
  scope: 'link:qr:read',
  description: 'List QR codes with their scan counts.',
  readOnly: true,
  input: listQrInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, f) =>
    tx.execute(sql`
      select q.id, q.type, q.name, q.scans, q.last_scanned_at, l.alias
        from qr_codes q
        left join links l on l.id = q.link_id
       where q.workspace_id = ${workspaceId}
         and q.deleted_at is null
         ${f.cursor ? sql`and q.id > ${f.cursor}` : sql``}
       order by q.id
       limit ${f.limit + 1}`),
});

export const LINK_OPS = [listLinks, shorten, createQr, listQr] as const;
