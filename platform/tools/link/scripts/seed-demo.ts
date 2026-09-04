/**
 * Fills a workspace with Link data worth looking at.
 *
 *     pnpm --filter @mamal/tool-link seed-demo <workspaceId>
 *
 * Distinct from `demo.ts`, which mints one link per *redirect outcome* for
 * smoke-testing. This one is for a person opening the app: a plausible spread
 * of links, a published bio page, QR codes and a transfer, so every screen has
 * something on it rather than an empty state.
 */
import { sql } from 'drizzle-orm';
import { closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { localAdapter } from '@mamal/storage';
import {
  addBlock, createBioPage, createLink, createQrCode, createTransfer,
  finaliseTransfer, importLinks, planUpload, registerPart, setRules,
} from '@mamal/tool-link';
import { writeLocalPart } from '@mamal/storage';

const workspaceId = process.argv[2];
if (!workspaceId) throw new Error('usage: seed-demo <workspaceId>');

const db = unsafeUnscopedDb();
const root = process.env.STORAGE_LOCAL_ROOT ?? './.storage';
const storageOptions = {
  root,
  secret: process.env.STORAGE_URL_SECRET ?? 'dev-storage-secret',
  baseUrl: process.env.STORAGE_LOCAL_URL ?? 'http://localhost:3000/api/storage',
};
const storage = localAdapter(storageOptions);

await withWorkspace(workspaceId, async (tx) => {
  const [p] = await tx.execute<{ id: string }>(sql`
    select id from projects where workspace_id = ${workspaceId} order by is_default desc limit 1`);
  const projectId = p!.id;

  /* ------------------------------------------------------------- links */

  const campaign = await createLink(tx, {
    workspaceId, projectId, alias: 'spring',
    destinationUrl: 'https://example.com/spring-collection',
    title: 'Spring collection — Instagram',
    campaign: 'spring-2026',
    tags: ['social', 'instagram'],
    utm: { source: 'instagram', medium: 'social', campaign: 'spring-2026' },
  });

  // A rule, so the editor's simulator has something to show.
  await setRules(tx, {
    workspaceId, linkId: campaign.id,
    rules: [
      {
        id: '', priority: 0, sticky: false, isEnabled: true,
        match: { match: 'all', conditions: [{ field: 'os', op: 'is', value: 'iOS' }] },
        action: { type: 'redirect', destinationUrl: 'https://apps.apple.com/app/example' },
      },
      {
        id: '', priority: 1, sticky: true, isEnabled: true,
        match: { match: 'all', conditions: [{ field: 'country', op: 'in', value: ['DE', 'AT', 'CH'] }] },
        action: {
          type: 'rotate',
          variants: [
            { url: 'https://example.de/fruehling', weight: 50 },
            { url: 'https://example.de/fruehling-b', weight: 50 },
          ],
        },
      },
    ],
  });

  await importLinks(tx, {
    workspaceId, projectId,
    csv:
      'url,alias,title,campaign,utm_source,tags\n' +
      'https://example.com/newsletter,news,Newsletter signup,spring-2026,email,email;lifecycle\n' +
      'https://example.com/pricing,pricing,Pricing page,,,\n' +
      'https://example.com/careers,jobs,Careers,,,\n' +
      'https://example.com/docs/getting-started,,Docs — getting started,,,\n' +
      'https://example.com/webinar,,March webinar,spring-2026,linkedin,events\n',
  });

  // Some plausible traffic, so the table is not all zeroes.
  await tx.execute(sql`
    update links set clicks_count = (random() * 900 + 40)::bigint,
                     last_clicked_at = now() - (random() * interval '3 days')
     where workspace_id = ${workspaceId} and kind = 'short'`);

  /* ---------------------------------------------------------- bio page */

  const bio = await createBioPage(tx, {
    workspaceId, projectId, alias: 'ada', title: 'Ada Lovelace',
  });
  for (const [type, settings] of [
    ['avatar', { imageUrl: 'https://example.com/ada.jpg', name: 'Ada Lovelace', tagline: 'Analytical engines, mostly' }],
    ['paragraph', { text: 'Notes on computing, and occasionally on poetry.' }],
    ['heading', { text: 'Elsewhere', level: 'h2' }],
    ['link', { label: 'The newsletter', url: 'https://example.com/newsletter', description: 'Every other Thursday' }],
    ['link', { label: 'Talks and slides', url: 'https://example.com/talks' }],
    ['divider', { style: 'line' }],
    ['email_collector', { title: 'Get the notes', buttonLabel: 'Join', requireConsent: true, consentText: 'Email me when there is something new.' }],
    ['socials', { links: [
      { network: 'x', url: 'https://x.com/example' },
      { network: 'github', url: 'https://github.com/example' },
    ] }],
  ] as [string, Record<string, unknown>][]) {
    await addBlock(tx, { workspaceId, pageId: bio.pageId, type, settings });
  }
  await tx.execute(sql`
    update bio_pages
       set is_published = true, views = 1284,
           theme = '{"background":"#0a0f1a","foreground":"#f2f6fc","card":"#111827","accent":"#7d6bff"}'::jsonb
     where id = ${bio.pageId}`);

  /* --------------------------------------------------------------- QR */

  await createQrCode(tx, {
    workspaceId, projectId, type: 'dynamic_url', name: 'Spring poster',
    payload: { url: 'https://example.com/spring-collection' },
    style: { body: 'rounded', outerEye: 'rounded', innerEye: 'dot', errorCorrection: 'Q' },
  });
  await createQrCode(tx, {
    workspaceId, projectId, type: 'wifi', name: 'Studio wifi',
    payload: { ssid: 'Studio Guest', password: 'welcome-2026', encryption: 'WPA' },
  });
  await createQrCode(tx, {
    workspaceId, projectId, type: 'vcard', name: 'Ada — contact card',
    payload: { firstName: 'Ada', lastName: 'Lovelace', email: 'ada@example.com', organisation: 'Analytical Engines' },
  });
  await tx.execute(sql`
    update qr_codes set scans = (random() * 400 + 10)::bigint,
                        last_scanned_at = now() - (random() * interval '2 days')
     where workspace_id = ${workspaceId}`);

  /* --------------------------------------------------------- transfer */

  const transfer = await createTransfer(tx, {
    workspaceId, projectId,
    subject: 'Spring campaign assets',
    message: 'Final crops and the two alternates we discussed.',
    expiresInDays: 7,
  });
  const content = Buffer.from('demo asset\n'.repeat(2000));
  const plan = await planUpload(tx, storage, {
    workspaceId, transferId: transfer.id, name: 'hero-crop.txt', sizeBytes: content.length,
  });
  const { etag } = await writeLocalPart(storageOptions, plan.storageKey, 1, content);
  await registerPart(tx, { fileId: plan.fileId, partNumber: 1, etag });
  await finaliseTransfer(tx, storage, transfer.id);

  /* ----------------------------------------------------- odds and ends */

  await tx.execute(sql`
    insert into link_folders (workspace_id, project_id, name, sort_order)
    values (${workspaceId}, ${projectId}, 'Spring 2026', 0),
           (${workspaceId}, ${projectId}, 'Evergreen', 1)
    on conflict do nothing`);

  await tx.execute(sql`
    insert into utm_presets (workspace_id, project_id, name, values, auto_apply)
    values (${workspaceId}, ${projectId}, 'Instagram — spring',
            '{"source":"instagram","medium":"social","campaign":"spring-2026"}'::jsonb, true)
    on conflict do nothing`);

  await tx.execute(sql`
    insert into splash_pages (workspace_id, project_id, name, delay_seconds, settings)
    values (${workspaceId}, ${projectId}, 'Sponsor slot', 5,
            '{"title":"One moment","body":"Brought to you by our sponsor."}'::jsonb)
    on conflict do nothing`);

  // Something for the "From Audit" panel on the links list.
  await tx.execute(sql`
    insert into link_suggestions (workspace_id, kind, target_url, context_url)
    values (${workspaceId}, 'replace_broken_external',
            'https://old-partner.example.org/announcement',
            'https://example.com/blog/partnership')
    on conflict do nothing`);

  console.log(JSON.stringify({
    workspaceId,
    links: 'http://localhost:3000/link',
    bio: `http://localhost:3000/r/ada`,
    campaignLink: `http://localhost:3000/r/spring`,
  }));
}, { db });

await closeDb();
