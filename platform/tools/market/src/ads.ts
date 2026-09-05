/**
 * Ad copy, brand kits and spend reporting.
 *
 * The split that matters: **reporting is free and generation costs**. Every
 * number on the ads screen — spend, CPA, ROAS, what is wasting money — is
 * arithmetic in `ad-performance.ts` over data the customer's own ad accounts
 * gave us. AI writes copy and narrates findings; it never produces them. That
 * is what makes the screen work identically on a lifetime plan.
 *
 * Generated copy is **measured before it is stored**. A model told "under 30
 * characters" writes 32 often enough to matter, and the rejection would
 * otherwise arrive from Google at upload time — after the customer has paid for
 * the generation. Variants that do not fit are marked rather than dropped: a
 * headline two characters over is usually worth editing, not regenerating.
 */
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { MarketNotAllowed } from './service.ts';
import {
  FRAMEWORKS, platformFor, validateCopy, type AdPlatform, type CopyProblem,
} from './ad-platforms.ts';
import {
  comparisonWindows, findAll, totalsOf, type Finding, type MetricRow, type Totals,
} from './ad-performance.ts';
import { toPromptContext, type BrandSnapshot } from './creatives.ts';

/* ---------------------------------------------------------------- brands */

export async function saveBrand(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    id?: string;
    name: string;
    voice?: string;
    audience?: string;
    palette?: string[];
    dos?: string[];
    donts?: string[];
    isDefault?: boolean;
  },
): Promise<string> {
  const name = opts.name.trim();
  if (!name) throw new MarketNotAllowed('invalid', 'A brand needs a name.');

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into market_brands
      (workspace_id, project_id, name, voice, audience, palette, dos, donts, is_default)
    values (${opts.workspaceId}, ${opts.projectId}, ${name}, ${opts.voice ?? null},
            ${opts.audience ?? null}, ${textArray(opts.palette ?? [])}::text[],
            ${textArray(opts.dos ?? [])}::text[], ${textArray(opts.donts ?? [])}::text[],
            ${opts.isDefault ?? false})
    on conflict on constraint market_brands_key do update
       set voice = excluded.voice, audience = excluded.audience,
           palette = excluded.palette, dos = excluded.dos, donts = excluded.donts,
           is_default = excluded.is_default, updated_at = now()
    returning id`);

  if (opts.isDefault) {
    // One default, and switching unmarks the old one in the same statement —
    // between two statements there would be none, and a generation in that
    // window would silently go out unbranded.
    await tx.execute(sql`
      update market_brands set is_default = (id = ${row!.id}), updated_at = now()
       where project_id = ${opts.projectId} and is_default <> (id = ${row!.id})`);
  }

  return row!.id;
}

export async function listBrands(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<{
  id: string; name: string; voice: string | null; audience: string | null;
  palette: string[]; dos: string[]; donts: string[]; isDefault: boolean;
}[]> {
  const rows = await tx.execute<{
    id: string; name: string; voice: string | null; audience: string | null;
    palette: string[]; dos: string[]; donts: string[]; is_default: boolean;
  }>(sql`
    select id, name, voice, audience, palette, dos, donts, is_default
      from market_brands where project_id = ${opts.projectId}
     order by is_default desc, name`);

  return rows.map((r) => ({
    id: r.id, name: r.name, voice: r.voice, audience: r.audience,
    palette: r.palette, dos: r.dos, donts: r.donts, isDefault: r.is_default,
  }));
}

/* ------------------------------------------------------------------ copy */

export type CopyVariant = {
  values: Record<string, string[]>;
  problems: CopyProblem[];
  /** False when something in it would be rejected at upload. */
  usable: boolean;
};

export type GenerateCopyResult = {
  copyId: string;
  variants: CopyVariant[];
  creditsSpent: number;
};

export async function generateCopy(
  tx: WorkspaceScopedDb,
  input: {
    workspaceId: string;
    projectId: string;
    platform: string;
    brief: string;
    brandId?: string | null;
    framework?: string;
    tone?: string;
    objective?: string;
    language?: string;
    variants?: number;
  },
  deps: ExecuteDeps,
): Promise<GenerateCopyResult> {
  const platform = platformFor(input.platform);
  if (!platform) {
    throw new MarketNotAllowed('invalid', `${input.platform} is not a platform we write for.`);
  }

  const brand = input.brandId ? await loadBrandSnapshot(tx, input.brandId) : null;
  const wanted = Math.min(Math.max(input.variants ?? 3, 1), 5);

  let text: string;
  try {
    const result = await execute(
      tx,
      {
        featureKey: 'market.ai_copy',
        system: copySystem(platform, wanted),
        prompt: copyPrompt(platform, input, brand),
        modality: 'text',
      },
      { workspaceId: input.workspaceId },
      deps,
    );
    if (!result.ok || !result.text) {
      throw new MarketNotAllowed(
        'generation_failed',
        result.error ?? 'The model returned nothing.',
      );
    }
    text = result.text;
  } catch (err) {
    if (err instanceof AiUnavailable) {
      /*
       * The honest refusal. Nothing here has a non-AI fallback worth offering:
       * a template with `{{placeholder}}` in it is not ad copy, and pretending
       * otherwise wastes the customer's time. The rest of the ads screen —
       * accounts, spend, what is working — is unaffected.
       */
      throw new MarketNotAllowed('ai_unavailable', err.message);
    }
    throw err;
  }

  const variants = parseVariants(text, platform).map((values) => {
    const problems = validateCopy(platform, values);
    return {
      values,
      problems,
      // Marked, not dropped: a headline two characters over is worth editing.
      usable: !problems.some((p) => p.level === 'error'),
    };
  });

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into ad_copies
      (workspace_id, project_id, brand_id, platform, objective, framework, tone,
       language, brief, variants, word_count)
    values (${input.workspaceId}, ${input.projectId}, ${input.brandId ?? null},
            ${platform.key}, ${input.objective ?? null}, ${input.framework ?? null},
            ${input.tone ?? null}, ${input.language ?? 'en'},
            ${JSON.stringify({ brief: input.brief })}::jsonb,
            ${JSON.stringify(variants)}::jsonb,
            ${wordsIn(variants)})
    returning id`);

  const [spend] = await tx.execute<{ credits: number }>(sql`
    select coalesce(-sum(delta), 0)::int as credits from credit_entries
     where feature_key = 'market.ai_copy' and delta < 0
       and created_at > now() - interval '1 minute'`);

  return { copyId: row!.id, variants, creditsSpent: spend?.credits ?? 0 };
}

function copySystem(platform: AdPlatform, variants: number): string {
  const fields = platform.fields
    .map(
      (f) =>
        `- ${f.key}: ${f.min}–${f.max} of them, each at most ${f.maxLength} characters` +
        (f.guidance ? ` (${f.guidance})` : ''),
    )
    .join('\n');

  return [
    `You write ${platform.label} ads. Return exactly ${variants} variants as JSON:`,
    '{"variants":[{"<field>":["value","value"]}]}',
    'Fields, with their limits:',
    fields,
    // Stated because a model over-runs a limit often enough that it is worth
    // saying twice — and because it is checked afterwards regardless.
    'Stay inside every character limit. Count characters, not words.',
    'No markdown, no commentary, JSON only.',
  ].join('\n');
}

function copyPrompt(
  platform: AdPlatform,
  input: { brief: string; framework?: string; tone?: string; objective?: string; language?: string },
  brand: BrandSnapshot | null,
): string {
  const parts = [brand ? toPromptContext(brand) : '', `Brief: ${input.brief}`];
  if (input.objective) parts.push(`Objective: ${input.objective}.`);
  if (input.tone) parts.push(`Tone: ${input.tone}.`);
  const framework = input.framework ? FRAMEWORKS[input.framework] : undefined;
  if (framework) parts.push(`Structure: ${framework.label} — ${framework.shape}`);
  if (input.language && input.language !== 'en') parts.push(`Write in ${input.language}.`);
  void platform;
  return parts.filter(Boolean).join('\n');
}

/**
 * Reads the model's JSON, tolerating the ways it goes wrong.
 *
 * Models wrap JSON in fences, add a sentence before it, or return a bare array.
 * Each is easy to accommodate and expensive to treat as a failure — the
 * customer has already paid for the generation by the time it is parsed.
 */
export function parseVariants(
  text: string,
  platform: AdPlatform,
): Record<string, string[]>[] {
  const fenced = text.match(/```(?:json)?\s*([\s\S]*?)```/i);
  const body = (fenced?.[1] ?? text).trim();

  const start = body.search(/[[{]/);
  if (start === -1) return [];

  let parsed: unknown;
  try {
    parsed = JSON.parse(body.slice(start));
  } catch {
    return [];
  }

  const list = Array.isArray(parsed)
    ? parsed
    : ((parsed as { variants?: unknown }).variants ?? []);
  if (!Array.isArray(list)) return [];

  return list.map((entry) => {
    const values: Record<string, string[]> = {};
    if (!entry || typeof entry !== 'object') return values;

    for (const [key, raw] of Object.entries(entry as Record<string, unknown>)) {
      // A model sometimes returns a string where the field takes several, and
      // sometimes several where it takes one. Both normalise to an array.
      const asArray = Array.isArray(raw) ? raw : [raw];
      const strings = asArray
        .filter((v) => typeof v === 'string' || typeof v === 'number')
        .map((v) => String(v).trim())
        .filter(Boolean);
      if (strings.length > 0) values[key] = strings;
    }
    void platform;
    return values;
  });
}

function wordsIn(variants: CopyVariant[]): number {
  return variants.reduce(
    (total, variant) =>
      total +
      Object.values(variant.values)
        .flat()
        .reduce((n, value) => n + value.split(/\s+/).filter(Boolean).length, 0),
    0,
  );
}

/* ----------------------------------------------------------- performance */

export type AccountReport = {
  accountId: string;
  name: string;
  currency: string;
  totals: Totals;
  previous: Totals;
  findings: Finding[];
};

/**
 * What the ad accounts are doing, and what to do about it.
 *
 * No AI anywhere in this path. `market.ai_insight` can narrate the findings
 * afterwards; the findings themselves are arithmetic, which is why the screen
 * is complete with AI switched off.
 */
export async function accountReports(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; today?: Date; days?: number },
): Promise<AccountReport[]> {
  const windows = comparisonWindows({ today: opts.today ?? new Date(), days: opts.days ?? 14 });

  const accounts = await tx.execute<{
    id: string; name: string; currency: string;
  }>(sql`
    select id, name, currency from ad_accounts where project_id = ${opts.projectId}
     order by name`);

  const reports: AccountReport[] = [];

  for (const account of accounts) {
    const [earlier, later] = await Promise.all([
      metricsBetween(tx, account.id, windows.earlier),
      metricsBetween(tx, account.id, windows.later),
    ]);

    reports.push({
      accountId: account.id,
      name: account.name,
      currency: account.currency,
      totals: totalsOf(later),
      previous: totalsOf(earlier),
      findings: findAll(earlier, later, { currency: account.currency }),
    });
  }

  return reports;
}

async function metricsBetween(
  tx: WorkspaceScopedDb,
  accountId: string,
  window: { from: string; to: string },
): Promise<MetricRow[]> {
  const rows = await tx.execute<{
    entity_id: string; entity_name: string | null; level: string; captured_on: string;
    impressions: number; clicks: number; spend_micros: number;
    conversions: number; conversion_value_micros: number;
  }>(sql`
    select entity_id, entity_name, level, captured_on::text,
           impressions, clicks, spend_micros, conversions, conversion_value_micros
      from ad_metrics
     where account_id = ${accountId}
       -- Campaign grain: account totals would hide which one is wasting money,
       -- and ad grain is too fine to act on in a summary.
       and level = 'campaign'
       and captured_on between ${window.from}::date and ${window.to}::date`);

  return rows.map((r) => ({
    entityId: r.entity_id,
    entityName: r.entity_name,
    level: r.level,
    capturedOn: r.captured_on,
    impressions: Number(r.impressions),
    clicks: Number(r.clicks),
    spendMicros: Number(r.spend_micros),
    conversions: Number(r.conversions),
    conversionValueMicros: Number(r.conversion_value_micros),
  }));
}

/**
 * Stores a day of metrics.
 *
 * Upsert, because ad platforms *restate*: conversions attribute late and the
 * last 28 days are revised. An append-only design would need a dedupe pass on
 * every read, which is the mistake `phpanalytics` made with its stats table.
 */
export async function recordMetrics(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; accountId: string; rows: MetricRow[] },
): Promise<number> {
  for (const row of opts.rows) {
    await tx.execute(sql`
      insert into ad_metrics
        (workspace_id, account_id, level, entity_id, entity_name, captured_on,
         impressions, clicks, spend_micros, conversions, conversion_value_micros)
      values (${opts.workspaceId}, ${opts.accountId}, ${row.level}, ${row.entityId},
              ${row.entityName}, ${row.capturedOn}::date,
              ${row.impressions}, ${row.clicks}, ${row.spendMicros},
              ${row.conversions}, ${row.conversionValueMicros})
      on conflict on constraint ad_metrics_key do update
         set entity_name = excluded.entity_name,
             impressions = excluded.impressions,
             clicks = excluded.clicks,
             spend_micros = excluded.spend_micros,
             conversions = excluded.conversions,
             conversion_value_micros = excluded.conversion_value_micros,
             updated_at = now()`);
  }
  return opts.rows.length;
}

export async function listCreatives(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; limit?: number },
): Promise<{
  id: string; type: string; status: string; prompt: string; preset: string | null;
  width: number | null; height: number | null; assetId: string | null;
  creditsSpent: number; error: string | null; createdAt: string;
}[]> {
  const rows = await tx.execute<{
    id: string; type: string; status: string; prompt: string; preset: string | null;
    width: number | null; height: number | null; asset_id: string | null;
    credits_spent: number; error: string | null; created_at: string;
  }>(sql`
    select id, type, status, prompt, preset, width, height, asset_id,
           credits_spent, error, created_at::text
      from ad_creatives where project_id = ${opts.projectId}
     order by created_at desc limit ${opts.limit ?? 50}`);

  return rows.map((r) => ({
    id: r.id, type: r.type, status: r.status, prompt: r.prompt, preset: r.preset,
    width: r.width, height: r.height, assetId: r.asset_id,
    creditsSpent: r.credits_spent, error: r.error, createdAt: r.created_at,
  }));
}

async function loadBrandSnapshot(
  tx: WorkspaceScopedDb,
  brandId: string,
): Promise<BrandSnapshot | null> {
  const [row] = await tx.execute<{
    name: string; voice: string | null; audience: string | null;
    palette: string[]; dos: string[]; donts: string[];
  }>(sql`
    select name, voice, audience, palette, dos, donts from market_brands where id = ${brandId}`);

  return row
    ? {
        name: row.name,
        voice: row.voice ?? undefined,
        audience: row.audience ?? undefined,
        palette: row.palette,
        dos: row.dos,
        donts: row.donts,
      }
    : null;
}

export async function requireAdAccountHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  projectId: string,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, 'market.ad_accounts');
  if (!ctx) throw new Error('market.ad_accounts is not a known feature');
  const [counted] = await tx.execute<{ count: number }>(sql`
    select count(*)::int as count from ad_accounts where project_id = ${projectId}`);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}
