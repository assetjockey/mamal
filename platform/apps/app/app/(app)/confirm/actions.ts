'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import {
  createCampaign, createWidget, updateWidgetSettings, enablePush, recordConversion,
  ConfirmNotAllowed,
} from '@mamal/tool-confirm';
import { encryptSecret } from '@/lib/crypto';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

async function ctx() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  return { ws: session.workspace.id, database: db() };
}

export type ActionResult = { ok: true; id?: string } | { ok: false; error: string };

export async function addCampaign(siteId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    const id = await withWorkspace(ws, async (tx) => {
      const [site] = await tx.execute<{ host: string; project_id: string }>(sql`
        select host, project_id from sites where id = ${siteId} and workspace_id = ${ws}`);
      if (!site) throw new ConfirmNotAllowed('not_found', 'No such website.');
      return createCampaign(tx, {
        workspaceId: ws, projectId: site.project_id, siteId,
        name: site.host, host: site.host,
      });
    }, { db: database });
    revalidatePath('/confirm');
    return { ok: true, id };
  } catch (e) {
    // The resolver's own sentence, so "you have used 1 of 1" reaches the user
    // rather than a generic failure.
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

export async function addWidget(campaignId: string, type: string, name: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    const id = await withWorkspace(
      ws,
      (tx) => createWidget(tx, { workspaceId: ws, campaignId, type, name }),
      { db: database },
    );
    revalidatePath(`/confirm/campaigns/${campaignId}`);
    return { ok: true, id };
  } catch (e) {
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

export async function saveWidget(
  widgetId: string,
  patch: {
    name?: string; settings: unknown; targeting: unknown;
    theme?: string; position?: string;
    displayFrequency?: string; displayLimit?: number;
    delaySeconds?: number; durationSeconds?: number;
  },
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    await withWorkspace(ws, async (tx) => {
      // Settings go through the type's own schema; everything else is a column.
      await updateWidgetSettings(tx, {
        workspaceId: ws, widgetId, settings: patch.settings, targeting: patch.targeting,
      });
      await tx.execute(sql`
        update confirm_widgets
           set name = coalesce(${patch.name ?? null}, name),
               theme = coalesce(${patch.theme ?? null}, theme),
               position = coalesce(${patch.position ?? null}, position),
               display_frequency = coalesce(${patch.displayFrequency ?? null}, display_frequency),
               display_limit = coalesce(${patch.displayLimit ?? null}, display_limit),
               delay_seconds = coalesce(${patch.delaySeconds ?? null}, delay_seconds),
               duration_seconds = coalesce(${patch.durationSeconds ?? null}, duration_seconds),
               updated_at = now()
         where id = ${widgetId} and workspace_id = ${ws}`);
    }, { db: database });
    revalidatePath(`/confirm/widgets/${widgetId}`);
    return { ok: true };
  } catch (e) {
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

export async function setWidgetEnabled(widgetId: string, enabled: boolean): Promise<void> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    update confirm_widgets set is_enabled = ${enabled}, updated_at = now()
     where id = ${widgetId} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/confirm/widgets');
}

export async function deleteWidget(widgetId: string): Promise<void> {
  const { ws, database } = await ctx();
  await withWorkspace(ws, (tx) => tx.execute(sql`
    delete from confirm_widgets where id = ${widgetId} and workspace_id = ${ws}`), { db: database });
  revalidatePath('/confirm/widgets');
}

/** Re-creates a deleted widget, for the undo toast. */
export async function restoreWidget(
  campaignId: string,
  snapshot: { type: string; name: string; settings: unknown; targeting: unknown; theme: string; position: string },
): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    const id = await withWorkspace(ws, async (tx) => {
      const created = await createWidget(tx, {
        workspaceId: ws, campaignId, type: snapshot.type, name: snapshot.name,
      });
      await updateWidgetSettings(tx, {
        workspaceId: ws, widgetId: created, settings: snapshot.settings, targeting: snapshot.targeting,
      });
      await tx.execute(sql`
        update confirm_widgets set theme = ${snapshot.theme}, position = ${snapshot.position}
         where id = ${created}`);
      return created;
    }, { db: database });
    revalidatePath('/confirm/widgets');
    return { ok: true, id };
  } catch (e) {
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

/**
 * Turns on push for a site: mints its VAPID pair and stores the private half
 * encrypted. The public half then goes into the customer's page, where it is
 * meant to be seen.
 */
export async function enablePushForSite(siteId: string): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    const id = await withWorkspace(ws, async (tx) => {
      const [site] = await tx.execute<{ project_id: string }>(sql`
        select project_id from sites where id = ${siteId} and workspace_id = ${ws}`);
      if (!site) throw new ConfirmNotAllowed('not_found', 'No such website.');
      const out = await enablePush(tx, {
        workspaceId: ws, projectId: site.project_id, siteId, encrypt: encryptSecret,
      });
      return out.id;
    }, { db: database });
    revalidatePath('/confirm/push');
    return { ok: true, id };
  } catch (e) {
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}

/**
 * Records a conversion the customer typed in.
 *
 * Stored as `manual`, never as anything else. The source column is what keeps
 * "someone really bought this" distinguishable from "we typed it in", and the
 * conversions screen shows the split — the source products let an operator
 * fabricate proof with no trace at all.
 */
export async function addConversion(input: {
  campaignId: string; type: string; name: string; city: string;
}): Promise<ActionResult> {
  const { ws, database } = await ctx();
  try {
    const id = await withWorkspace(ws, (tx) => recordConversion(tx, {
      workspaceId: ws,
      campaignId: input.campaignId,
      source: 'manual',
      type: input.type.slice(0, 48) || 'signed up',
      data: { name: input.name.slice(0, 80), city: input.city.slice(0, 80) },
    }), { db: database });
    revalidatePath('/confirm/leads');
    return { ok: true, id };
  } catch (e) {
    if (e instanceof ConfirmNotAllowed) return { ok: false, error: e.message };
    throw e;
  }
}
