import { randomBytes } from 'node:crypto';
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { mint, coreUrn, relate } from '@mamal/resources';
import { widgetDef, themeVars } from '@mamal/widget-catalog';
import { validateTargeting } from '@mamal/targeting';

export class ConfirmNotAllowed extends Error {
  constructor(
    readonly reason: string,
    message: string,
  ) {
    super(message);
    this.name = 'ConfirmNotAllowed';
  }
}

/** The public identifier in the embed snippet. */
const newPixelKey = () => `ck_${randomBytes(10).toString('hex')}`;

/* --------------------------------------------------------------- campaigns */

export async function createCampaign(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; siteId: string; name: string; host: string },
): Promise<string> {
  const ctx = await loadContext(tx, opts.workspaceId, 'confirm.campaigns');
  if (!ctx) throw new Error('confirm.campaigns is not a known feature');
  const [counted] = await tx.execute<{ count: number }>(sql`
    select count(*)::int as count from confirm_campaigns where workspace_id = ${opts.workspaceId}`);
  const decision = resolve({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new ConfirmNotAllowed(decision.reason, decision.message);

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into confirm_campaigns
      (workspace_id, project_id, site_id, name, pixel_key, host_allowlist)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.siteId}, ${opts.name},
            ${newPixelKey()}, array[${opts.host}]::text[])
    returning id`);

  const resource = await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'confirm',
    type: 'campaign',
    externalId: row!.id,
    label: opts.name,
  });

  // The edge that puts this campaign on the site's Connected panel.
  await relate(tx, {
    workspaceId: opts.workspaceId,
    from: resource.urn,
    to: coreUrn.site(opts.siteId),
    relation: 'publishes_to',
    createdBy: 'system',
  });

  return row!.id;
}

/* ----------------------------------------------------------------- widgets */

export async function createWidget(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; campaignId: string; type: string; name: string },
): Promise<string> {
  const def = widgetDef(opts.type);
  if (!def) throw new ConfirmNotAllowed('unknown_type', `No widget type "${opts.type}".`);

  const ctx = await loadContext(tx, opts.workspaceId, 'confirm.widgets');
  if (!ctx) throw new Error('confirm.widgets is not a known feature');
  const [counted] = await tx.execute<{ count: number }>(sql`
    select count(*)::int as count from confirm_widgets where workspace_id = ${opts.workspaceId}`);
  const decision = resolve({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new ConfirmNotAllowed(decision.reason, decision.message);

  // The catalogue's defaults are parsed through its own schema, so a widget is
  // valid the moment it exists rather than only after it is edited.
  const settings = def.settings.parse(def.defaults);

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into confirm_widgets (workspace_id, campaign_id, type, name, settings)
    values (${opts.workspaceId}, ${opts.campaignId}, ${opts.type}, ${opts.name},
            ${JSON.stringify(settings)}::jsonb)
    returning id`);
  return row!.id;
}

/**
 * Validates settings against the type's own schema before saving.
 *
 * The catalogue entry is the single contract: the editor form is generated from
 * it, the runtime reads what it produced, and this rejects anything that would
 * not render. Without it a bad write is only discovered by a visitor.
 */
export async function updateWidgetSettings(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; widgetId: string; settings: unknown; targeting?: unknown },
): Promise<void> {
  const [widget] = await tx.execute<{ type: string }>(sql`
    select type from confirm_widgets
     where id = ${opts.widgetId} and workspace_id = ${opts.workspaceId}`);
  if (!widget) throw new ConfirmNotAllowed('not_found', 'No such notification.');

  const def = widgetDef(widget.type);
  if (!def) throw new ConfirmNotAllowed('unknown_type', `No widget type "${widget.type}".`);

  const parsed = def.settings.safeParse(opts.settings);
  if (!parsed.success) {
    throw new ConfirmNotAllowed(
      'invalid_settings',
      parsed.error.issues.map((i) => `${i.path.join('.') || 'settings'}: ${i.message}`).join('; '),
    );
  }

  // Same check the link rule builder runs: an unrecognised group shape has no
  // conditions to fail closed on, so it quietly targets everybody.
  const problems = validateTargeting(opts.targeting);
  if (problems.length > 0) {
    throw new ConfirmNotAllowed(
      'invalid_targeting',
      problems.map((p) => `${p.path || 'targeting'} — ${p.message}`).join('; '),
    );
  }

  await tx.execute(sql`
    update confirm_widgets
       set settings = ${JSON.stringify(parsed.data)}::jsonb,
           targeting = ${JSON.stringify(opts.targeting ?? {})}::jsonb,
           updated_at = now()
     where id = ${opts.widgetId} and workspace_id = ${opts.workspaceId}`);
}

/* ----------------------------------------------------------------- payload */

export type RuntimeWidget = {
  id: string;
  type: string;
  family: string;
  theme: Record<string, string>;
  position: string;
  settings: Record<string, unknown>;
  targeting: unknown;
  delaySeconds: number;
  durationSeconds: number;
  displayFrequency: string;
  displayLimit: number;
  showBranding: boolean;
};

export type RuntimePayload = {
  campaignId: string;
  widgets: RuntimeWidget[];
  conversions: Record<string, unknown>[];
  counts: Record<string, number>;
  ingest: string;
};

/**
 * Builds the single payload the runtime fetches.
 *
 * Two things this function is careful about, because it is the boundary where
 * our data crosses into a browser we do not control:
 *
 * **Only what each widget declared it needs.** The conversion feed is other
 * customers' activity. A widget that does not declare `conversions` is not sent
 * any, so a cookie notice on a page cannot be read to enumerate who bought what.
 *
 * **Only what a proof line requires.** A conversion is projected down to first
 * name, city and country before it leaves — never an email, never a full name,
 * never an order value. The widget says "Ana in Lisbon"; it does not need, and
 * therefore must not receive, anything more.
 */
export async function buildPayload(
  tx: WorkspaceScopedDb,
  opts: { pixelKey: string; ingestUrl: string; brandingRemoved?: boolean },
): Promise<RuntimePayload | null> {
  const [campaign] = await tx.execute<{
    id: string; workspace_id: string; branding_removed: boolean; is_enabled: boolean;
  }>(sql`
    select id, workspace_id, branding_removed, is_enabled
      from confirm_campaigns where pixel_key = ${opts.pixelKey}`);
  if (!campaign || !campaign.is_enabled) return null;

  const rows = await tx.execute<{
    id: string; type: string; settings: Record<string, unknown>; targeting: unknown;
    theme: string; position: string; delay_seconds: number; duration_seconds: number;
    display_frequency: string; display_limit: number;
  }>(sql`
    select id, type, settings, targeting, theme, position,
           delay_seconds, duration_seconds, display_frequency, display_limit
      from confirm_widgets
     where campaign_id = ${campaign.id}
       and is_enabled
       and (starts_at is null or starts_at <= now())
       and (ends_at is null or ends_at >= now())
     order by sort_order, created_at`);

  const widgets: RuntimeWidget[] = [];
  let anyNeedsConversions = false;

  for (const r of rows) {
    const def = widgetDef(r.type);
    // A row whose type is no longer in the catalogue is skipped rather than
    // shipped: the runtime would not know how to draw it.
    if (!def) continue;
    if (def.needs.includes('conversions')) anyNeedsConversions = true;

    widgets.push({
      id: r.id,
      type: r.type,
      family: def.family,
      theme: themeVars(r.theme, (r.settings as { accentColor?: string }).accentColor),
      position: r.position,
      settings: r.settings,
      targeting: r.targeting,
      delaySeconds: r.delay_seconds,
      durationSeconds: r.duration_seconds,
      displayFrequency: r.display_frequency,
      displayLimit: r.display_limit,
      showBranding: !campaign.branding_removed,
    });
  }

  const conversions = anyNeedsConversions
    ? (
        await tx.execute<{ name: string; city: string; country: string; type: string; occurred_at: string }>(sql`
          select
            -- First name only, and only when a name was supplied. The full
            -- value never leaves the database.
            nullif(split_part(coalesce(data->>'name', ''), ' ', 1), '') as name,
            data->>'city' as city,
            country,
            type,
            occurred_at
          from confirm_conversions
         where campaign_id = ${campaign.id}
           and occurred_at > now() - interval '30 days'
         order by occurred_at desc
         limit 30`)
      ).map((c) => ({
        name: c.name,
        city: c.city,
        country: c.country,
        type: c.type,
        occurredAt: c.occurred_at,
      }))
    : [];

  const counts: Record<string, number> = {};
  for (const w of widgets) {
    const def = widgetDef(w.type)!;
    if (!def.needs.includes('count')) continue;
    const hours = Number((w.settings as { windowHours?: number }).windowHours ?? 168);
    const [n] = await tx.execute<{ n: number }>(sql`
      select count(*)::int as n from confirm_conversions
       where campaign_id = ${campaign.id}
         and occurred_at > now() - (${hours} * interval '1 hour')`);
    counts[w.id] = n?.n ?? 0;
  }

  return {
    campaignId: campaign.id,
    widgets: applyMinimums(widgets, conversions.length, counts),
    conversions,
    counts,
    ingest: opts.ingestUrl,
  };
}

/**
 * Drops widgets whose minimum threshold is not met.
 *
 * Filtered on the server, not in the runtime: "show nothing below 3 recent
 * sales" is a promise not to fabricate proof, and a client-side check could be
 * skipped by anyone reading the payload. It also means the browser is never
 * sent a feed it was not going to display.
 */
function applyMinimums(
  widgets: RuntimeWidget[],
  conversionCount: number,
  counts: Record<string, number>,
): RuntimeWidget[] {
  return widgets.filter((w) => {
    const min = Number((w.settings as { minimumCount?: number }).minimumCount ?? 0);
    if (min <= 0) return true;
    const def = widgetDef(w.type)!;
    const available = def.needs.includes('count') ? (counts[w.id] ?? 0) : conversionCount;
    return available >= min;
  });
}

/* ------------------------------------------------------------- conversions */

export async function recordConversion(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    campaignId: string;
    source: string;
    type?: string;
    data?: Record<string, unknown>;
    path?: string;
    country?: string;
    sourceUrn?: string;
  },
): Promise<string> {
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into confirm_conversions
      (workspace_id, campaign_id, source, type, data, path, country, source_urn)
    values (${opts.workspaceId}, ${opts.campaignId}, ${opts.source},
            ${opts.type ?? 'conversion'}, ${JSON.stringify(opts.data ?? {})}::jsonb,
            ${opts.path ?? null}, ${opts.country ?? null}, ${opts.sourceUrn ?? null})
    returning id`);
  return row!.id;
}
