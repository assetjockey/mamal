/**
 * Seeds a workspace with one link per redirect outcome.
 *
 * Run it, then curl the aliases it prints: this is how the redirect path is
 * exercised against a real server rather than only in unit tests, and it is
 * what the runbook points at when somebody needs to reproduce a routing
 * complaint locally.
 *
 *     pnpm --filter @mamal/tool-link demo
 */
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { addBlock, createBioPage, createLink, setRules, setLinkPassword } from '@mamal/tool-link';

const db = unsafeUnscopedDb();
const tag = `smoke${Date.now()}`;

const ws = await asPlatformAdmin(async (tx) => {
  const [u] = await tx.execute<{ id: string }>(sql`
    insert into users (email, name) values (${`${tag}@t.local`}, 'Smoke') returning id`);
  const [w] = await tx.execute<{ id: string }>(sql`
    insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Smoke', ${u!.id}) returning id`);
  await tx.execute(sql`
    insert into projects (workspace_id, name, slug, is_default) values (${w!.id}, 'D', 'default', true)`);
  await tx.execute(sql`
    insert into subscriptions (workspace_id, plan_id, status)
    select ${w!.id}, id, 'active' from plans where key = 'link_pro'`);
  return w!.id;
}, { db });

const out = await withWorkspace(ws, async (tx) => {
  const [p] = await tx.execute<{ id: string }>(sql`
    select id from projects where workspace_id = ${ws} limit 1`);
  const project = p!.id;
  const mk = (o: Parameters<typeof createLink>[1]) => createLink(tx, o);

  const plain = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-plain`,
    destinationUrl: 'https://example.com/landing', utm: { source: 'poster' } });

  const geo = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-geo`,
    destinationUrl: 'https://example.com/global' });
  await setRules(tx, { workspaceId: ws, linkId: geo.id, rules: [{
    priority: 0, sticky: false, isEnabled: true,
    match: { match: 'all', conditions: [{ field: 'os', op: 'is', value: 'iOS' }] },
    action: { type: 'redirect', destinationUrl: 'https://apps.example.com/ios' },
  }] });

  const locked = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-locked`,
    destinationUrl: 'https://example.com/secret' });
  await setLinkPassword(tx, { workspaceId: ws, linkId: locked.id, password: 'open sesame' });

  const expired = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-expired`,
    destinationUrl: 'https://example.com/gone' });
  await tx.execute(sql`
    update links set expires_at = now() - interval '1 day' where id = ${expired.id}`);

  const paused = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-paused`,
    destinationUrl: 'https://example.com/off' });
  await tx.execute(sql`update links set is_enabled = false where id = ${paused.id}`);

  const splash = await mk({ workspaceId: ws, projectId: project, alias: `${tag}-splash`,
    destinationUrl: 'https://example.com/sponsored' });
  const [sp] = await tx.execute<{ id: string }>(sql`
    insert into splash_pages (workspace_id, project_id, name, delay_seconds)
    values (${ws}, ${project}, 'Sponsor', 5) returning id`);
  await tx.execute(sql`
    update links set settings = settings || ${JSON.stringify({ splashPageId: sp!.id })}::jsonb
     where id = ${splash.id}`);

  /*
   * A real bio page, not just a link with `kind: 'biolink'`.
   *
   * The two are not the same thing and the difference is exactly the bug this
   * script caught: the redirect resolves a biolink to the public renderer, and
   * the renderer 404s unless a `bio_pages` row exists and is published.
   */
  const bio = await createBioPage(tx, {
    workspaceId: ws, projectId: project, alias: `${tag}-bio`, title: 'Ada Lovelace',
  });
  for (const [type, settings] of [
    ['avatar', { imageUrl: 'https://example.com/a.png', name: 'Ada Lovelace', tagline: 'Analytical engines' }],
    ['heading', { text: 'Elsewhere', level: 'h2' }],
    ['link', { label: 'My newsletter', url: 'https://example.com/news', description: 'Weekly' }],
    ['divider', { style: 'line' }],
    ['email_collector', { title: 'Get the notes', buttonLabel: 'Join', requireConsent: true }],
  ] as [string, Record<string, unknown>][]) {
    await addBlock(tx, { workspaceId: ws, pageId: bio.pageId, type, settings });
  }
  // A block type the catalogue no longer knows, to prove the renderer skips it
  // rather than breaking the page.
  await tx.execute(sql`
    insert into bio_blocks (workspace_id, page_id, type, settings, sort_order)
    values (${ws}, ${bio.pageId}, 'a_type_from_the_future', '{}'::jsonb, 9)`);
  await tx.execute(sql`
    update bio_pages set is_published = true,
      theme = '{"background":"#0a0f1a","foreground":"#f2f6fc","card":"#111827","accent":"#7d6bff"}'::jsonb
     where id = ${bio.pageId}`);

  return { ws, tag, plain: plain.alias, geo: geo.alias, locked: locked.alias,
           expired: expired.alias, paused: paused.alias, splash: splash.alias, bio: bio.alias };
}, { db });

console.log(JSON.stringify(out));
await closeDb();
