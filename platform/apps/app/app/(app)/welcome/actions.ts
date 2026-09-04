'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { enqueue } from '@mamal/jobs';
import { addSite, startAudit } from '@mamal/tool-audit';
import { sql } from 'drizzle-orm';
import { textArray, withWorkspace } from '@mamal/db';
import { coreUrn, mint } from '@mamal/resources';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/** Normalize to the form `sites.host` is unique on: no scheme, no www, no path. */
function normalizeHost(input: string): { host: string; rootUrl: string } | null {
  const trimmed = input.trim();
  if (!trimmed) return null;
  let url: URL;
  try {
    url = new URL(/^https?:\/\//i.test(trimmed) ? trimmed : `https://${trimmed}`);
  } catch {
    return null;
  }
  const host = url.hostname.toLowerCase().replace(/^www\./, '');
  if (!host.includes('.')) return null;
  return { host, rootUrl: `${url.protocol}//${url.hostname}` };
}

export async function saveInterests(interests: string[]) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  await withWorkspace(
    ws,
    (tx) => tx.execute(sql`
      insert into onboarding (workspace_id, interests)
      values (${ws}, ${textArray(interests)})
      on conflict (workspace_id) do update set interests = excluded.interests, updated_at = now()`),
    { db: db() },
  );
  revalidatePath('/welcome');
}

/**
 * The first resource.
 *
 * One input, one URL. Creating the site also mints its URN, so every tool can
 * address it from the moment it exists — that is what makes the follow-on work
 * (audit it, watch it, track it) a single click rather than three setups.
 */
export async function addFirstSite(formData: FormData) {
  const session = await getSession();
  if (!session) redirect('/sign-in');

  const parsed = normalizeHost(String(formData.get('url') ?? ''));
  if (!parsed) return { error: 'That does not look like a website address.' };

  const ws = session.workspace.id;
  const database = db();
  let queued: { auditId: string } | null = null as { auditId: string } | null;

  await withWorkspace(
    ws,
    async (tx) => {
      const [project] = await tx.execute<{ id: string }>(sql`
        select id from projects where workspace_id = ${ws} order by is_default desc limit 1`);

      const [site] = await tx.execute<{ id: string }>(sql`
        insert into sites (workspace_id, project_id, host, root_url)
        values (${ws}, ${project!.id}, ${parsed.host}, ${parsed.rootUrl})
        on conflict (workspace_id, host) do update set root_url = excluded.root_url
        returning id`);

      await mint(tx, {
        workspaceId: ws,
        projectId: project!.id,
        tool: 'core',
        type: 'site',
        externalId: site!.id,
        label: parsed.host,
      });

      await tx.execute(sql`
        insert into onboarding (workspace_id, first_resource_url, completed_steps)
        values (${ws}, ${parsed.rootUrl}, array['add_site'])
        on conflict (workspace_id) do update
          set first_resource_url = excluded.first_resource_url,
              completed_steps = array(
                select distinct unnest(onboarding.completed_steps || array['add_site'])),
              updated_at = now()`);

      /*
       * Register the site with Audit and start crawling, here, without asking.
       *
       * The promise on this screen is "two questions, then we go and look at
       * your site" — leaving the user on a dashboard with an "Enable Audit"
       * button to find breaks that promise, and it is the difference between
       * seeing results in thirty seconds and configuring a tool. The crawl is
       * free-tier sized and queued, so this costs one slice of shared worker.
       *
       * Failures here must not fail onboarding: the site exists and is
       * addressable either way, and the dashboard still offers a manual run.
       */
      try {
        const auditSiteId = await addSite(tx, {
          workspaceId: ws,
          projectId: project!.id,
          siteId: site!.id,
          host: parsed.host,
        });
        const started = await startAudit(tx, {
          workspaceId: ws,
          projectId: project!.id,
          auditSiteId,
          trigger: 'onboarding',
        });
        queued = { auditId: started.auditId };
      } catch (e) {
        console.info('onboarding audit not started', e);
      }

      return coreUrn.site(site!.id);
    },
    { db: database },
  );

  // Enqueued outside the transaction: a job that starts before its rows commit
  // finds nothing to crawl.
  if (queued) {
    await enqueue('audit.crawl', 'slice', { auditId: queued.auditId, workspaceId: ws, slice: 0 });
  }

  revalidatePath('/');
  redirect('/');
}

export async function dismissOnboarding() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;
  await withWorkspace(
    ws,
    (tx) => tx.execute(sql`
      insert into onboarding (workspace_id, dismissed_at) values (${ws}, now())
      on conflict (workspace_id) do update set dismissed_at = now()`),
    { db: db() },
  );
  revalidatePath('/');
}
