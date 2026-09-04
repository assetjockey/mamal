import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { widgetDef } from '@mamal/widget-catalog';

/**
 * Confirm's AI features.
 *
 * Every one is additive. With AI off — or on a lifetime plan, which excludes it
 * structurally — the product is unchanged: the catalogue ships sensible default
 * copy for all 44 types, and `{{token}}` interpolation does the personalising.
 * What AI adds is a first draft in the customer's own voice, and translation.
 *
 * Nothing here reaches a provider directly. `ai.execute` re-resolves
 * entitlements immediately before the call, and the eslint boundary forbids
 * importing a provider SDK outside packages/ai.
 */

export type AiResult<T> =
  | { ok: true; value: T; creditsCharged: number }
  | { ok: false; reason: string; message: string };

/**
 * One entry point, so every feature gets the same failure handling.
 *
 * `execute` re-resolves entitlements immediately before the vendor call and
 * releases the credit hold on failure — the guarantee is that nobody is billed
 * for work that never arrived, and the message says so.
 */
async function run(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; featureKey: string; prompt: string; system: string },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  try {
    const result = await execute(
      tx,
      { featureKey: input.featureKey, prompt: input.prompt, system: input.system, modality: 'text' },
      { workspaceId: input.workspaceId },
      deps,
    );
    if (!result.ok || !result.text) {
      return {
        ok: false,
        reason: 'generation_failed',
        message: result.error ?? 'The model returned nothing. You have not been charged.',
      };
    }
    return { ok: true, value: result.text, creditsCharged: 0 };
  } catch (err) {
    if (err instanceof AiUnavailable) return { ok: false, reason: err.reason, message: err.message };
    return {
      ok: false,
      reason: 'error',
      message: err instanceof Error ? err.message : 'The request failed.',
    };
  }
}

/**
 * Writes notification copy from the shape of the conversions a campaign has.
 *
 * Given the *shape*, never the rows: the model is told "these conversions carry
 * a first name and a city" rather than being handed real people's details. It
 * is writing a template, so the data would add nothing but exposure.
 */
export async function generateCopy(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; widgetId: string; tone?: string; brand?: string },
  deps: ExecuteDeps,
): Promise<AiResult<{ title: string; body: string }>> {
  const [widget] = await tx.execute<{ type: string; campaign_id: string; host: string }>(sql`
    select w.type, w.campaign_id, s.host
      from confirm_widgets w
      join confirm_campaigns c on c.id = w.campaign_id
      join sites s on s.id = c.site_id
     where w.id = ${input.widgetId} and w.workspace_id = ${input.workspaceId}`);
  if (!widget) return { ok: false, reason: 'not_found', message: 'No such notification.' };

  const def = widgetDef(widget.type);
  if (!def) return { ok: false, reason: 'unknown_type', message: 'Unknown notification type.' };

  // Which tokens are actually available, so the model cannot invent `{{score}}`.
  const [fields] = await tx.execute<{ keys: string[] }>(sql`
    select coalesce(array_agg(distinct k), array[]::text[]) as keys
      from confirm_conversions c, lateral jsonb_object_keys(c.data) k
     where c.campaign_id = ${widget.campaign_id}
       and c.occurred_at > now() - interval '30 days'`);
  const tokens = (fields?.keys ?? []).filter((k) => /^[a-z_]+$/i.test(k)).slice(0, 12);

  const result = await run(tx, {
    workspaceId: input.workspaceId,
    featureKey: 'confirm.ai_copy',
    system:
      'You write short, factual website notification copy. You never invent urgency, ' +
      'scarcity or numbers. You reply with JSON only.',
    prompt: [
      `Write copy for a "${def.label}" website notification on ${widget.host}.`,
      def.description,
      tokens.length
        ? `Available placeholders: ${tokens.map((t) => `{{${t}}}`).join(', ')}. Use at most two.`
        : 'No placeholders are available; write it without any.',
      input.brand ? `Brand voice: ${input.brand}.` : '',
      `Tone: ${input.tone ?? 'plain and factual'}.`,
      'A title of at most 60 characters and a body of at most 90.',
      'Claim nothing that is not literally true of the data. No urgency invented,',
      'no scarcity invented, no numbers you were not given.',
      'Reply as JSON: {"title": "...", "body": "..."}',
    ].filter(Boolean).join('\n'),
  }, deps);

  if (!result.ok) return result;

  try {
    const parsed = JSON.parse(stripFence(result.value)) as { title?: string; body?: string };
    if (!parsed.title) throw new Error('no title');
    return {
      ok: true,
      value: { title: String(parsed.title).slice(0, 280), body: String(parsed.body ?? '').slice(0, 280) },
      creditsCharged: result.creditsCharged,
    };
  } catch {
    // The credits were spent — the model answered, we could not use it. Say so
    // rather than pretending nothing happened.
    return {
      ok: false,
      reason: 'unparseable',
      message: 'The model did not return usable copy. Try again, or write it yourself.',
    };
  }
}

/** Translates a widget's copy into one locale, preserving `{{tokens}}`. */
export async function translateCopy(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; widgetId: string; locale: string },
  deps: ExecuteDeps,
): Promise<AiResult<Record<string, string>>> {
  const [widget] = await tx.execute<{ settings: Record<string, unknown> }>(sql`
    select settings from confirm_widgets
     where id = ${input.widgetId} and workspace_id = ${input.workspaceId}`);
  if (!widget) return { ok: false, reason: 'not_found', message: 'No such notification.' };

  const text = Object.fromEntries(
    Object.entries(widget.settings).filter(
      ([, v]) => typeof v === 'string' && v.trim().length > 0,
    ),
  );
  if (Object.keys(text).length === 0) {
    return { ok: false, reason: 'nothing_to_translate', message: 'This notification has no text.' };
  }

  const result = await run(tx, {
    workspaceId: input.workspaceId,
    featureKey: 'confirm.ai_translate',
    system: 'You translate UI strings. You reply with JSON only and never alter placeholders.',
    prompt: [
      `Translate these UI strings into ${input.locale}.`,
      'Keep every {{placeholder}} exactly as written, including its braces —',
      'they are substituted at runtime and a translated placeholder breaks the widget.',
      'Keep the same keys. Reply as JSON only.',
      JSON.stringify(text),
    ].join('\n'),
  }, deps);

  if (!result.ok) return result;

  try {
    const parsed = JSON.parse(stripFence(result.value)) as Record<string, string>;
    const out: Record<string, string> = {};
    for (const [key, original] of Object.entries(text)) {
      const translated = parsed[key];
      if (typeof translated !== 'string') continue;
      /*
       * A translation that lost a placeholder is worse than no translation —
       * the widget would render "Someone in just bought" to that locale's
       * visitors and nobody would notice for months. Keep the original.
       */
      const wanted = [...String(original).matchAll(/\{\{(\w+)\}\}/g)].map((m) => m[1]).sort();
      const got = [...translated.matchAll(/\{\{(\w+)\}\}/g)].map((m) => m[1]).sort();
      out[key] = wanted.join() === got.join() ? translated.slice(0, 280) : String(original);
    }
    return { ok: true, value: out, creditsCharged: result.creditsCharged };
  } catch {
    return {
      ok: false,
      reason: 'unparseable',
      message: 'The model did not return usable translations.',
    };
  }
}

/** Models often wrap JSON in a fence despite being asked not to. */
function stripFence(text: string): string {
  const t = text.trim();
  if (!t.startsWith('```')) return t;
  return t.replace(/^```(?:json)?\s*/i, '').replace(/```$/, '').trim();
}
