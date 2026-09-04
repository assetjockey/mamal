import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { BLOCK_CATALOG, blockDef } from '@mamal/link-catalog';
import { validateAlias } from './service.ts';

/**
 * Link's AI features.
 *
 * All additive, all individually toggleable, and none of them load-bearing.
 * With AI off — which is what a lifetime-plan holder sees — the product is
 * unchanged: aliases are generated or typed, preview copy is the destination's
 * own OG tags, bio pages start from one of the templates, and artistic QR
 * simply is not offered. Nothing here is a degraded path around a missing
 * feature; each is a first draft the customer can take or ignore.
 */

export type AiResult<T> =
  | { ok: true; value: T; creditsCharged: number }
  | { ok: false; reason: string; message: string };

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
 * Suggests aliases for a destination.
 *
 * Every candidate is put through the same `validateAlias` the create path uses
 * and checked against what already exists, so the list the customer sees is
 * only aliases they can actually have. Offering `/login` and then refusing it
 * is worse than offering nothing.
 */
export async function suggestSlugs(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; url: string; title?: string; customDomainId?: string | null },
  deps: ExecuteDeps,
): Promise<AiResult<string[]>> {
  const result = await run(
    tx,
    {
      workspaceId: input.workspaceId,
      featureKey: 'link.ai_slug',
      system:
        'You suggest short link aliases. Reply with 6 candidates, one per line, no numbering, ' +
        'no explanation. Each is lowercase, 3 to 24 characters, letters digits and hyphens only, ' +
        'and reads as words a person could dictate over the phone.',
      prompt: `Destination: ${input.url}\n${input.title ? `Title: ${input.title}` : ''}`,
    },
    deps,
  );
  if (!result.ok) return result;

  const candidates = result.value
    .split('\n')
    .map((line) => line.trim().replace(/^[-*\d.\s]+/, '').toLowerCase())
    .filter((line) => validateAlias(line).ok)
    .slice(0, 12);

  if (candidates.length === 0) {
    return { ok: false, reason: 'no_usable_output', message: 'No usable suggestions came back.' };
  }

  const taken = await tx.execute<{ alias: string }>(sql`
    select alias from links
     where alias = any(${textArray(candidates)})
       and custom_domain_id is not distinct from ${input.customDomainId ?? null}
       and deleted_at is null`);
  const used = new Set(taken.map((r) => r.alias));

  const free = candidates.filter((c) => !used.has(c)).slice(0, 6);
  if (free.length === 0) {
    return { ok: false, reason: 'all_taken', message: 'Every suggestion is already in use.' };
  }
  return { ok: true, value: free, creditsCharged: result.creditsCharged };
}

/**
 * Writes the title and description a shared link previews with.
 *
 * Bounded to what the platforms actually render — roughly 60 and 155 characters
 * — because a model asked for "a title" writes a sentence, and a sentence is
 * truncated mid-word in every feed it appears in.
 */
export async function suggestPreviewCopy(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; url: string; pageTitle?: string; pageText?: string },
  deps: ExecuteDeps,
): Promise<AiResult<{ title: string; description: string }>> {
  const result = await run(
    tx,
    {
      workspaceId: input.workspaceId,
      featureKey: 'link.ai_og_copy',
      system:
        'You write link preview copy. Reply with exactly two lines: the first is a title of at ' +
        'most 60 characters, the second a description of at most 155 characters. No labels, no ' +
        'quotes, no markdown.',
      prompt: [
        `URL: ${input.url}`,
        input.pageTitle ? `Current title: ${input.pageTitle}` : '',
        input.pageText ? `Page text: ${input.pageText.slice(0, 1500)}` : '',
      ].filter(Boolean).join('\n'),
    },
    deps,
  );
  if (!result.ok) return result;

  const [title = '', description = ''] = result.value.split('\n').map((l) => l.trim()).filter(Boolean);
  if (!title) {
    return { ok: false, reason: 'no_usable_output', message: 'No usable copy came back.' };
  }
  return {
    ok: true,
    value: { title: title.slice(0, 60), description: description.slice(0, 155) },
    creditsCharged: result.creditsCharged,
  };
}

/**
 * Proposes a bio page layout from a description.
 *
 * The model is given the catalogue's block keys and told to choose from them.
 * Anything it invents is dropped rather than guessed at — a layout referring to
 * a block type that does not exist would fail at render, and silently
 * substituting a similar block would produce a page the customer did not ask
 * for and cannot explain.
 */
export async function suggestBioLayout(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; description: string; existingUrl?: string },
  deps: ExecuteDeps,
): Promise<AiResult<{ type: string; label: string }[]>> {
  // The full 84 would be most of a prompt; the families that make sense at the
  // top of a page are what a layout is actually built from.
  const offerable = BLOCK_CATALOG
    .filter((b) => b.category !== 'embed' || ['youtube', 'spotify', 'calendly'].includes(b.key))
    .map((b) => b.key);

  const result = await run(
    tx,
    {
      workspaceId: input.workspaceId,
      featureKey: 'link.ai_bio_layout',
      system:
        'You lay out link-in-bio pages. Reply with one block key per line, in the order they ' +
        'should appear, at most 12 lines. Use only keys from the provided list. No explanation.\n' +
        `Available keys: ${offerable.join(', ')}`,
      prompt: [
        input.description,
        input.existingUrl ? `Their existing site: ${input.existingUrl}` : '',
      ].filter(Boolean).join('\n'),
    },
    deps,
  );
  if (!result.ok) return result;

  const blocks = result.value
    .split('\n')
    .map((l) => l.trim().replace(/^[-*\d.\s]+/, ''))
    .flatMap((key) => {
      const def = blockDef(key);
      return def ? [{ type: def.key, label: def.label }] : [];
    })
    .slice(0, 12);

  if (blocks.length === 0) {
    return { ok: false, reason: 'no_usable_output', message: 'No usable layout came back.' };
  }
  return { ok: true, value: blocks, creditsCharged: result.creditsCharged };
}

/**
 * Writes alt text for an image.
 *
 * One credit each, because it is cheap and because it is the feature most worth
 * running in bulk: a bio page with twenty images and no alt text is both an
 * accessibility failure and an Audit issue, and fixing it by hand is exactly
 * the tedium people do not do.
 */
export async function suggestAltText(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; imageUrl: string; context?: string },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  const result = await run(
    tx,
    {
      workspaceId: input.workspaceId,
      featureKey: 'link.ai_alt_text',
      system:
        'You write alt text. Reply with one sentence under 125 characters describing what the ' +
        'image shows, for someone who cannot see it. Do not start with "image of" or "picture of".',
      prompt: [`Image: ${input.imageUrl}`, input.context ? `Context: ${input.context}` : '']
        .filter(Boolean).join('\n'),
    },
    deps,
  );
  if (!result.ok) return result;
  return {
    ok: true,
    value: result.value.split('\n')[0]!.trim().slice(0, 125),
    creditsCharged: result.creditsCharged,
  };
}
