import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { publish } from '@mamal/bus';
import { db } from '@/lib/db';
import { eventRegistry } from '@/lib/bus';

/**
 * A form submission from a public bio page.
 *
 * Unauthenticated by necessity — it is a stranger filling in a form on
 * somebody's link-in-bio page. Three things follow from that, and all three are
 * the point of this file:
 *
 * **The block id is the only thing trusted.** Workspace, page and consent
 * requirement are all read from the block's own row; nothing about where the
 * contact goes comes from the request.
 *
 * **Consent is recorded, not assumed.** `contacts` carries a per-contact
 * consent trail, and a submission that did not tick the box is stored without
 * marketing consent rather than being quietly opted in.
 *
 * **The response is a redirect, not JSON.** The page has no JavaScript, so the
 * browser must be sent somewhere it can read.
 */

export async function POST(
  request: Request,
  ctx: { params: Promise<{ blockId: string }> },
) {
  const { blockId } = await ctx.params;
  const form = await request.formData().catch(() => null);
  const value = String(form?.get('value') ?? '').trim().slice(0, 320);
  const consented = form?.get('consent') != null;
  const back = request.headers.get('referer') ?? '/';

  if (!value) return seeOther(`${back}#form-error`);

  const outcome = await asPlatformAdmin(
    async (tx) => {
      const [block] = await tx.execute<{
        id: string; workspace_id: string; page_id: string; type: string;
        settings: { requireConsent?: boolean };
      }>(sql`
        select b.id, b.workspace_id, b.page_id, b.type, b.settings
          from bio_blocks b
          join bio_pages p on p.id = b.page_id
          join links l on l.id = p.link_id
         where b.id = ${blockId}::uuid
           and b.is_enabled and p.is_published
           and l.is_enabled and l.deleted_at is null
           and (b.starts_at is null or b.starts_at <= now())
           and (b.ends_at is null or b.ends_at >= now())`);
      if (!block) return null;

      // Enforced server-side: the checkbox is `required` in the markup, and
      // markup is not a control.
      if (block.settings.requireConsent !== false && !consented) return { ok: false as const };

      const kind = block.type.startsWith('phone') ? 'phone'
        : block.type.startsWith('review') ? 'review'
        : block.type.startsWith('contact') ? 'contact' : 'email';

      /*
       * Into the shared `contacts` table, not a Link-specific one.
       *
       * This is the sleeper unification from §0.2: a lead captured on a bio
       * page, a Confirm collector and a newsletter signup are all *people who
       * touched this workspace*, and GDPR requires one consent record per
       * person rather than one per product.
       */
      const [project] = await tx.execute<{ id: string }>(sql`
        select project_id as id from bio_pages p
          join links l on l.id = p.link_id
         where p.id = ${block.page_id}`);

      const [contact] = await tx.execute<{ id: string }>(sql`
        insert into contacts (workspace_id, project_id, email, phone, source_urn, consent)
        values (
          ${block.workspace_id}, ${project!.id},
          ${kind === 'email' ? value : null},
          ${kind === 'phone' ? value : null},
          ${`urn:mamal:link:bio_page:${block.page_id}`},
          ${JSON.stringify({
            marketing: consented,
            at: new Date().toISOString(),
            method: 'bio_form',
          })}::jsonb)
        on conflict (workspace_id, email) do update
           set last_seen_at = now(), updated_at = now()
        returning id`);

      await tx.execute(sql`
        update bio_blocks set clicks = clicks + 1 where id = ${block.id}`);

      await publish(tx, eventRegistry(), {
        name: 'link.lead.captured',
        workspaceId: block.workspace_id,
        subject: `urn:mamal:link:bio_page:${block.page_id}`,
        data: { pageId: block.page_id, blockId: block.id, value, kind },
        actor: { kind: 'system' },
      });

      return { ok: true as const, contactId: contact?.id };
    },
    { db: db() },
  );

  // A missing or unpublished block answers the same as a rejected one: this
  // endpoint must not tell a stranger which block ids exist.
  if (!outcome?.ok) return seeOther(`${back}#form-error`);
  return seeOther(`${back}#form-thanks`);
}

/** 303, so the browser follows with GET and a refresh does not resubmit. */
function seeOther(location: string): Response {
  return new Response(null, {
    status: 303,
    headers: { location, 'cache-control': 'no-store' },
  });
}
