import { sql } from 'drizzle-orm';
import { z } from 'zod';
import { recordConversion } from '@mamal/tool-confirm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { defineOp, limit, cursor, type Op } from '@/lib/ops';

/**
 * Confirm's operations, shared by REST and MCP.
 *
 * Same contract as Audit's: one definition, two transports, so a filter added
 * to one cannot be missing from the other.
 */

const listCampaignsInput = z.object({ limit, cursor });
export const listCampaigns: Op = defineOp({
  name: 'confirm_list_campaigns',
  scope: 'confirm:campaigns:read',
  description: 'List social-proof campaigns with their notification and conversion counts.',
  readOnly: true,
  input: listCampaignsInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, { limit: n, cursor: c }) =>
    tx.execute(sql`
      select c.id, c.name, s.host, c.is_enabled, c.impressions, c.clicks,
             (select count(*)::int from confirm_widgets w where w.campaign_id = c.id) as widgets
        from confirm_campaigns c join sites s on s.id = c.site_id
       where c.workspace_id = ${workspaceId}
         ${c ? sql`and c.id > ${c}` : sql``}
       order by c.id
       limit ${n + 1}`),
});

const listWidgetsInput = z.object({
  campaign_id: z.uuid().optional(),
  limit,
  cursor,
});
export const listWidgets: Op = defineOp({
  name: 'confirm_list_notifications',
  scope: 'confirm:widgets:read',
  description: 'List notifications, with impressions and clicks.',
  readOnly: true,
  input: listWidgetsInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, f) =>
    tx.execute(sql`
      select id, campaign_id, type, name, is_enabled, position,
             impressions, clicks, submissions
        from confirm_widgets
       where workspace_id = ${workspaceId}
         ${f.campaign_id ? sql`and campaign_id = ${f.campaign_id}` : sql``}
         ${f.cursor ? sql`and id > ${f.cursor}` : sql``}
       order by id
       limit ${f.limit + 1}`),
});

const recordInput = z.object({
  campaign_id: z.uuid().describe('From confirm_list_campaigns.'),
  type: z.string().max(48).default('conversion'),
  name: z.string().max(80).optional().describe('First name only; a surname is never shown.'),
  city: z.string().max(80).optional(),
  country: z.string().length(2).optional(),
});
export const recordConversionOp: Op = defineOp({
  name: 'confirm_record_conversion',
  scope: 'confirm:conversions:write',
  /*
   * A safe write, but the most consequential one here: it decides what a
   * visitor is told really happened. Recorded as `api`, distinct from `manual`
   * and from `bus`, so the conversions screen can always show where a proof
   * line came from.
   */
  description:
    'Record a conversion. It becomes eligible for proof notifications immediately. ' +
    'Only record things that actually happened.',
  readOnly: false,
  input: recordInput,
  run: async (tx: WorkspaceScopedDb, workspaceId, i) => {
    const id = await recordConversion(tx, {
      workspaceId,
      campaignId: i.campaign_id,
      source: 'api',
      type: i.type,
      data: { name: i.name, city: i.city },
      country: i.country,
    });
    return { id, status: 'recorded' };
  },
});

export const CONFIRM_OPS = [listCampaigns, listWidgets, recordConversionOp] as const;
