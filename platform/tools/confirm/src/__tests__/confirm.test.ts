/**
 * Confirm, against a real database.
 *
 * The assertions that matter most are about the payload: it is the boundary
 * where our data crosses into a browser on someone else's website, and
 * everything in it is readable by anyone who opens devtools.
 */
import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { mint, coreUrn } from '@mamal/resources';
import { WIDGET_CATALOG, widgetDef } from '@mamal/widget-catalog';
import { matches } from '@mamal/targeting';
import {
  buildPayload, createCampaign, createWidget, recordConversion, updateWidgetSettings,
  ConfirmNotAllowed,
} from '../service.ts';
import { confirmManifest } from '../manifest.ts';
import { confirmSubscriptions } from '../subscriptions.ts';

const db = unsafeUnscopedDb();
const tag = `cfm${Date.now()}`;

let ws = '';
let project = '';
let siteId = '';
let campaignId = '';
let pixelKey = '';

const INGEST = 'https://app.test/c/ingest';

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Confirm') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Confirm', ${u!.id})
      returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
    const [s] = await tx.execute<{ id: string }>(sql`
      insert into sites (workspace_id, project_id, host, root_url)
      values (${ws}, ${project}, ${'shop.test'}, ${'https://shop.test'}) returning id`);
    siteId = s!.id;
    /*
     * confirm_pro: unlimited widgets, 25 campaigns.
     *
     * The catalogue test creates all 44 types at once, which confirm_starter's
     * 20-widget cap correctly refuses — the limit doing its job, not a bug. Pro
     * still has a finite campaign limit, so the entitlement test below is
     * exercising a real ceiling rather than an absent one.
     */
    await tx.execute(sql`
      insert into subscriptions (workspace_id, plan_id, status)
      select ${ws}, id, 'active' from plans where key = 'confirm_pro'`);
  }, { db });

  await withWorkspace(ws, async (tx) => {
    await mint(tx, {
      workspaceId: ws, projectId: project, tool: 'core', type: 'site',
      externalId: siteId, label: 'shop.test',
    });
    campaignId = await createCampaign(tx, {
      workspaceId: ws, projectId: project, siteId, name: 'Shop', host: 'shop.test',
    });
    const [c] = await tx.execute<{ pixel_key: string }>(sql`
      select pixel_key from confirm_campaigns where id = ${campaignId}`);
    pixelKey = c!.pixel_key;
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where slug = ${tag}`);
    await tx.execute(sql`delete from users where email = ${`${tag}@test.local`}`);
  }, { db });
  await closeDb();
});

beforeEach(async () => {
  await withWorkspace(ws, async (tx) => {
    await tx.execute(sql`delete from confirm_widgets where campaign_id = ${campaignId}`);
    await tx.execute(sql`delete from confirm_conversions where campaign_id = ${campaignId}`);
  }, { db });
});

const addConversions = (n: number, over: Record<string, unknown> = {}) =>
  withWorkspace(ws, async (tx) => {
    for (let i = 0; i < n; i++) {
      await recordConversion(tx, {
        workspaceId: ws, campaignId, source: 'manual', type: 'bought',
        data: { name: 'Ana Silva', city: 'Lisbon', email: 'ana@example.com', amount: 99, ...over },
        country: 'PT',
      });
    }
  }, { db });

const payload = () =>
  withWorkspace(ws, (tx) => buildPayload(tx, { pixelKey, ingestUrl: INGEST }), { db });

describe('manifest', () => {
  it('declares a handler for every subscription', () => {
    for (const declared of confirmManifest.subscriptions) {
      const handler = confirmSubscriptions.find((h) => h.key === declared.handlerKey);
      expect(handler, `${declared.handlerKey} declared but not implemented`).toBeDefined();
      expect(handler!.event).toBe(declared.event);
    }
  });

  it('marks impressions as a quota, never a per-occurrence event', () => {
    // 50M impressions a day would flood the bus. The only impression-shaped
    // event here is a rollup threshold.
    const names = confirmManifest.events.map((e) => e.name);
    expect(names).not.toContain('confirm.impression');
    expect(names).toContain('confirm.campaign.threshold');
  });
});

describe('creating widgets', () => {
  it('creates every catalogue type with valid settings', async () => {
    // A type whose defaults do not satisfy its own schema cannot be created —
    // this is the catalogue and the service agreeing.
    await withWorkspace(ws, async (tx) => {
      for (const def of WIDGET_CATALOG) {
        const id = await createWidget(tx, {
          workspaceId: ws, campaignId, type: def.key, name: def.label,
        });
        expect(id, def.key).toBeTruthy();
      }
    }, { db });

    const [n] = await withWorkspace(
      ws,
      (tx) => tx.execute<{ n: number }>(sql`
        select count(*)::int as n from confirm_widgets where campaign_id = ${campaignId}`),
      { db },
    );
    expect(n!.n).toBe(WIDGET_CATALOG.length);
  });

  it('refuses an unknown type rather than storing it', async () => {
    await expect(
      withWorkspace(ws, (tx) => createWidget(tx, {
        workspaceId: ws, campaignId, type: 'telepathy', name: 'x',
      }), { db }),
    ).rejects.toBeInstanceOf(ConfirmNotAllowed);
  });

  it('rejects settings that would not render', async () => {
    const id = await withWorkspace(ws, (tx) => createWidget(tx, {
      workspaceId: ws, campaignId, type: 'cookie_notice', name: 'Cookies',
    }), { db });

    // A cookie notice without a one-click decline is refused by the schema.
    await expect(
      withWorkspace(ws, (tx) => updateWidgetSettings(tx, {
        workspaceId: ws, widgetId: id, settings: { showDecline: false },
      }), { db }),
    ).rejects.toBeInstanceOf(ConfirmNotAllowed);
  });
});

describe('the payload — what crosses into the browser', () => {
  async function widgetOf(type: string, settings?: Record<string, unknown>) {
    return withWorkspace(ws, async (tx) => {
      const id = await createWidget(tx, { workspaceId: ws, campaignId, type, name: type });
      if (settings) {
        const def = widgetDef(type)!;
        // `parse` returns `unknown` off a ZodTypeAny; the catalogue's schemas
        // are all objects, which is what makes the spread valid.
        const base = def.settings.parse(def.defaults) as Record<string, unknown>;
        await updateWidgetSettings(tx, {
          workspaceId: ws, widgetId: id,
          settings: { ...base, ...settings },
        });
      }
      return id;
    }, { db });
  }

  it('projects a conversion down to a proof line and nothing more', async () => {
    await widgetOf('recent_conversion', { minimumCount: 0 });
    await addConversions(1);

    const p = await payload();
    expect(p!.conversions).toHaveLength(1);
    const c = p!.conversions[0]!;

    // What a proof line needs.
    expect(c.name).toBe('Ana');
    expect(c.city).toBe('Lisbon');
    expect(c.country).toBe('PT');

    /*
     * What it must never carry. This payload is readable by anyone who opens
     * devtools on the customer's site — including their competitors — so an
     * email or an order value here is a leak of someone else's customer data.
     * Surname too: "Ana" is proof, "Ana Silva" is identification.
     */
    const serialised = JSON.stringify(p!.conversions);
    expect(serialised).not.toContain('ana@example.com');
    expect(serialised).not.toContain('Silva');
    expect(serialised).not.toContain('99');
  });

  it('withholds the conversion feed from widgets that never declared it', async () => {
    await widgetOf('cookie_notice');
    await addConversions(5);

    const p = await payload();
    // A cookie notice must not be a way to enumerate who bought what.
    expect(p!.conversions).toHaveLength(0);
  });

  it('sends the feed once when any widget needs it', async () => {
    await widgetOf('cookie_notice');
    await widgetOf('recent_conversion', { minimumCount: 0 });
    await addConversions(3);
    expect((await payload())!.conversions.length).toBeGreaterThan(0);
  });

  it('withholds a widget whose minimum is not met — server-side', async () => {
    /*
     * "Show nothing below N recent sales" is a promise not to fabricate proof.
     * Filtering in the runtime would let anyone reading the payload see the
     * widget that was meant to stay hidden, and skip the check.
     */
    await widgetOf('recent_conversion', { minimumCount: 5 });
    await addConversions(2);

    const p = await payload();
    expect(p!.widgets).toHaveLength(0);

    await addConversions(4);
    expect((await payload())!.widgets).toHaveLength(1);
  });

  it('carries the resolved theme, so the browser computes no colours', async () => {
    await widgetOf('informational');
    const w = (await payload())!.widgets[0]!;
    expect(w.theme['--w-bg']).toMatch(/^#[0-9a-f]{6}$/i);
    expect(w.family).toBe('card');
  });

  it('omits disabled, expired and not-yet-started widgets', async () => {
    const id = await widgetOf('informational');
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update confirm_widgets set is_enabled = false where id = ${id}`), { db });
    expect((await payload())!.widgets).toHaveLength(0);

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update confirm_widgets set is_enabled = true, ends_at = now() - interval '1 day'
       where id = ${id}`), { db });
    expect((await payload())!.widgets).toHaveLength(0);

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update confirm_widgets set ends_at = null, starts_at = now() + interval '1 day'
       where id = ${id}`), { db });
    expect((await payload())!.widgets).toHaveLength(0);
  });

  it('skips a row whose type has left the catalogue rather than shipping it', async () => {
    const id = await widgetOf('informational');
    await asPlatformAdmin((tx) => tx.execute(sql`
      update confirm_widgets set type = 'retired_type' where id = ${id}`), { db });
    // The runtime would not know how to draw it; better absent than broken.
    expect((await payload())!.widgets).toHaveLength(0);
  });

  it('returns null for an unknown or disabled campaign', async () => {
    expect(await withWorkspace(ws, (tx) =>
      buildPayload(tx, { pixelKey: 'ck_nope', ingestUrl: INGEST }), { db })).toBeNull();

    await withWorkspace(ws, (tx) => tx.execute(sql`
      update confirm_campaigns set is_enabled = false where id = ${campaignId}`), { db });
    expect(await payload()).toBeNull();
    await withWorkspace(ws, (tx) => tx.execute(sql`
      update confirm_campaigns set is_enabled = true where id = ${campaignId}`), { db });
  });

  it('the targeting it ships is what the engine evaluates', async () => {
    const id = await widgetOf('informational');
    const rule = { match: 'all', conditions: [{ field: 'country', op: 'is', value: 'PT' }] };
    await withWorkspace(ws, (tx) => updateWidgetSettings(tx, {
      workspaceId: ws, widgetId: id,
      settings: widgetDef('informational')!.settings.parse({}),
      targeting: rule,
    }), { db });

    const w = (await payload())!.widgets[0]!;
    // Round-tripped through jsonb and evaluated by the same module the browser
    // runs — the editor preview is only trustworthy because these agree.
    expect(matches(w.targeting, { country: 'PT' })).toBe(true);
    expect(matches(w.targeting, { country: 'ES' })).toBe(false);
  });
});

describe('entitlements', () => {
  it('refuses a campaign past the plan limit', async () => {
    const [row] = await withWorkspace(ws, (tx) => tx.execute<{ n: string }>(sql`
      select limit_value as n from plan_entitlements e
        join plans p on p.id = e.plan_id
       where p.key = 'confirm_pro' and e.feature_key = 'confirm.campaigns'`), { db });
    // Postgres returns the numeric as text through this driver.
    const limit = Number(row!.n);
    expect(limit).toBeGreaterThan(0);

    const extra: string[] = [];
    await withWorkspace(ws, async (tx) => {
      for (let i = 0; i < limit + 2; i++) {
        const [s] = await tx.execute<{ id: string }>(sql`
          insert into sites (workspace_id, project_id, host, root_url)
          values (${ws}, ${project}, ${`extra-${i}.test`}, ${`https://extra-${i}.test`})
          returning id`);
        await mint(tx, {
          workspaceId: ws, projectId: project, tool: 'core', type: 'site',
          externalId: s!.id, label: `extra-${i}.test`,
        });
        extra.push(s!.id);
      }
    }, { db });

    let refused = false;
    for (const [i, id] of extra.entries()) {
      try {
        await withWorkspace(ws, (tx) => createCampaign(tx, {
          workspaceId: ws, projectId: project, siteId: id,
          name: `Extra ${i}`, host: `extra-${i}.test`,
        }), { db });
      } catch (e) {
        expect(e).toBeInstanceOf(ConfirmNotAllowed);
        refused = true;
        break;
      }
    }
    expect(refused, 'campaigns past the plan limit must be refused').toBe(true);

    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from confirm_campaigns where id <> ${campaignId} and workspace_id = ${ws}`);
      await tx.execute(sql`delete from sites where workspace_id = ${ws} and host like 'extra-%'`);
    }, { db });
  });
});

describe('cross-tool subscriptions (dark launched)', () => {
  it('turns a real goal conversion into a proof line, with its origin recorded', async () => {
    const handler = confirmSubscriptions.find((h) => h.event === 'track.goal.converted')!;
    const subject = `urn:mamal:track:goal:${randomUUID()}`;

    await withWorkspace(ws, (tx) => handler.handle(
      {
        id: randomUUID(), name: 'track.goal.converted', version: 1,
        occurredAt: new Date().toISOString(),
        workspaceId: ws, projectId: project,
        actor: { kind: 'system' },
        subject,
        related: [],
        data: { siteUrn: coreUrn.site(siteId), goalKey: 'checkout', city: 'Porto', country: 'PT' },
        trace: { correlationId: randomUUID(), depth: 0 },
      },
      tx,
    ), { db });

    const [row] = await withWorkspace(ws, (tx) => tx.execute<{
      source: string; type: string; source_urn: string; city: string;
    }>(sql`
      select source, type, source_urn, data->>'city' as city
        from confirm_conversions where campaign_id = ${campaignId}`), { db });

    expect(row).toBeDefined();
    expect(row!.source).toBe('bus');
    expect(row!.type).toBe('checkout');
    expect(row!.city).toBe('Porto');
    // Traceable back to the event that caused it — the difference between proof
    // and the source product's hand-typed "conversions".
    expect(row!.source_urn).toBe(subject);
  });

  it('ignores a goal for a site with no campaign', async () => {
    const handler = confirmSubscriptions.find((h) => h.event === 'track.goal.converted')!;
    await withWorkspace(ws, (tx) => handler.handle(
      {
        id: randomUUID(), name: 'track.goal.converted', version: 1,
        occurredAt: new Date().toISOString(),
        workspaceId: ws, projectId: project, actor: { kind: 'system' },
        subject: 'urn:mamal:track:goal:x', related: [],
        data: { siteUrn: coreUrn.site(randomUUID()), goalKey: 'checkout' },
        trace: { correlationId: randomUUID(), depth: 0 },
      },
      tx,
    ), { db });

    const rows = await withWorkspace(ws, (tx) => tx.execute(sql`
      select id from confirm_conversions where campaign_id = ${campaignId}`), { db });
    expect(rows).toHaveLength(0);
  });

  it('only badges a site that actually scored well', async () => {
    const handler = confirmSubscriptions.find((h) => h.event === 'audit.run.completed')!;
    const id = await withWorkspace(ws, (tx) => createWidget(tx, {
      workspaceId: ws, campaignId, type: 'trust_badge', name: 'Badge',
    }), { db });

    const fire = (score: number) =>
      withWorkspace(ws, (tx) => handler.handle(
        {
          id: randomUUID(), name: 'audit.run.completed', version: 1,
          occurredAt: new Date().toISOString(),
          workspaceId: ws, projectId: project, actor: { kind: 'system' },
          subject: 'urn:mamal:audit:run:x', related: [],
          data: { score, siteId },
          trace: { correlationId: randomUUID(), depth: 0 },
        },
        tx,
      ), { db });

    const verified = async () => {
      const [r] = await withWorkspace(ws, (tx) => tx.execute<{ v: string | null }>(sql`
        select settings->>'verifiedScore' as v from confirm_widgets where id = ${id}`), { db });
      return r!.v;
    };

    await fire(72);
    expect(await verified(), 'a badge at any score is not a signal').toBeNull();

    await fire(94);
    expect(await verified()).toBe('94');
  });
});
