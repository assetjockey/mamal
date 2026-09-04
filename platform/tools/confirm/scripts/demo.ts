import { sql } from 'drizzle-orm';
import { asPlatformAdmin, withWorkspace, unsafeUnscopedDb, closeDb } from '@mamal/db';
import { createCampaign, createWidget, recordConversion, updateWidgetSettings } from '../src/index.ts';
import { widgetDef } from '@mamal/widget-catalog';

const db = unsafeUnscopedDb();
const WS = '01a067f5-ab4a-7168-9754-ecbd5a6b2628';

const [proj] = await asPlatformAdmin((tx) => tx.execute<{ id: string }>(sql`
  select id from projects where workspace_id = ${WS} order by is_default desc limit 1`), { db });
const [site] = await asPlatformAdmin((tx) => tx.execute<{ id: string; host: string }>(sql`
  select id, host from sites where workspace_id = ${WS} limit 1`), { db });

// Give the workspace a confirm plan so limits allow the demo.
await asPlatformAdmin((tx) => tx.execute(sql`
  insert into subscriptions (workspace_id, plan_id, status)
  select ${WS}, id, 'active' from plans where key = 'confirm_pro'
  on conflict do nothing`), { db });

const out = await withWorkspace(WS, async (tx) => {
  const [existing] = await tx.execute<{ id: string; pixel_key: string }>(sql`
    select id, pixel_key from confirm_campaigns where workspace_id = ${WS} limit 1`);
  if (existing) return existing;

  const id = await createCampaign(tx, {
    workspaceId: WS, projectId: proj!.id, siteId: site!.id,
    name: site!.host, host: site!.host,
  });

  const bubble = await createWidget(tx, { workspaceId: WS, campaignId: id, type: 'recent_conversion', name: 'Recent sales' });
  await updateWidgetSettings(tx, {
    workspaceId: WS, widgetId: bubble,
    settings: { ...widgetDef('recent_conversion')!.settings.parse({}), minimumCount: 1 },
    targeting: { match: 'all', conditions: [{ field: 'device', op: 'is', value: 'desktop' }] },
  });
  await createWidget(tx, { workspaceId: WS, campaignId: id, type: 'informational_bar', name: 'Free delivery' });
  await createWidget(tx, { workspaceId: WS, campaignId: id, type: 'cookie_notice', name: 'Cookies' });
  await createWidget(tx, { workspaceId: WS, campaignId: id, type: 'email_collector', name: 'Newsletter' });

  for (const [name, city] of [['Ana Silva', 'Lisbon'], ['Marek Nowak', 'Warsaw'], ['Yuki Tanaka', 'Osaka']]) {
    await recordConversion(tx, {
      workspaceId: WS, campaignId: id, source: 'manual', type: 'bought',
      data: { name, city, email: `${name.split(' ')[0]!.toLowerCase()}@example.com`, amount: 149 },
      country: 'PT',
    });
  }

  const [c] = await tx.execute<{ id: string; pixel_key: string }>(sql`
    select id, pixel_key from confirm_campaigns where id = ${id}`);
  return c!;
}, { db });

console.log(JSON.stringify(out));
await closeDb();
