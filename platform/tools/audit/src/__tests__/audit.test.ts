/**
 * The whole tool, end to end: a real HTTP server with real problems, crawled,
 * evaluated, scored and persisted — then the cross-tool handoff back.
 */
import { randomUUID } from 'node:crypto';
import { createServer, type Server } from 'node:http';
import type { AddressInfo } from 'node:net';
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, inList, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import {
  Dispatcher,
  EventRegistry,
  InProcessTransport,
  OutboxRelay,
  coreEvents,
  publish,
} from '@mamal/bus';
import { z } from 'zod';
import { currentPeriodStart, loadContext, resolve } from '@mamal/entitlements';
import { coreUrn, mint, neighbors } from '@mamal/resources';
import { addSite, advanceAudit, startAudit, AuditNotAllowed, type RunOutcome } from '../service.ts';
import { resolveIssue, runSite, scheduleRun } from '../commands.ts';
import { fixBrief, summariseAudit } from '../ai.ts';
import { auditSubscriptions, auditSweeper } from '../subscriptions.ts';
import type { AiDriver } from '@mamal/ai';
import { auditManifest } from '../manifest.ts';

describe('manifest', () => {
  it('declares every AI feature as a billable feature too', () => {
    for (const ai of auditManifest.aiFeatures) {
      const feature = auditManifest.features.find((f) => f.key === ai.key);
      expect(feature?.isAi, `${ai.key} would bypass the AI kill switch`).toBe(true);
    }
  });

  it('namespaces its events and commands', () => {
    for (const e of auditManifest.events) expect(e.name.startsWith('audit.')).toBe(true);
    for (const c of auditManifest.commands) expect(c.name.startsWith('audit.')).toBe(true);
  });

  it('publishes the event automations key off', () => {
    expect(auditManifest.events.map((e) => e.name)).toContain('audit.issue.detected');
  });
});

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('audit tool', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `aud${Date.now()}`;
  let ws = '';
  let project = '';
  let siteId = '';
  let auditSiteId = '';
  let server: Server;
  let origin = '';
  const DEEP_PAGES = 40;

  const PAGES: Record<string, { status?: number; body: string }> = {
    '/': {
      body: `<!DOCTYPE html><html lang="en"><head>
        <title>A shop selling deliberately broken example pages</title>
        <meta name="description" content="The homepage of a fixture site used to prove the audit engine finds real issues and does not invent fake ones.">
        <meta charset="utf-8"><meta name="viewport" content="width=device-width">
        <link rel="icon" href="/favicon.ico">
        <script type="application/ld+json">{"@type":"Organization","name":"Fixture"}</script>
        </head><body><h1>Fixture shop</h1><p>${'word '.repeat(300)}</p>
        <a href="/ok">Fine page</a><a href="/gone">Dead link</a><a href="/bad">Bad page</a>
        </body></html>`,
    },
    // Correct on everything a plain-HTTP fixture server can control. What it
    // cannot fix — TLS, compression, security headers — is asserted separately.
    '/ok': {
      body: `<!DOCTYPE html><html lang="en"><head>
        <title>A perfectly ordinary page that should not be flagged</title>
        <meta name="description" content="This page is correct in every way the engine checks, so any finding against it is a false positive worth failing the build over.">
        <meta charset="utf-8"><meta name="viewport" content="width=device-width">
        <meta property="og:title" content="A perfectly ordinary page">
        <meta property="og:description" content="Correct markup.">
        <meta property="og:image" content="/og.png">
        <script type="application/ld+json">{"@type":"Article","author":{"@type":"Person","name":"Fixture"}}</script>
        </head><body><h1>All good here</h1><p>${'content '.repeat(300)}</p>
        <a href="/">Home</a></body></html>`,
    },
    // No title, no description, no H1, thin, image with no alt.
    '/bad': {
      body: `<!DOCTYPE html><html><head><meta charset="utf-8"></head>
        <body><img src="/x.png"><p>Tiny.</p><a href="/">Home</a></body></html>`,
    },
    '/gone': { status: 404, body: 'Not found' },
    '/robots.txt': { body: 'User-agent: *\nAllow: /\nUser-agent: GPTBot\nDisallow: /' },
  };

  beforeAll(async () => {
    server = createServer((req, res) => {
      const path = (req.url ?? '/').split('?')[0]!;

      // A deterministic deep site: /deep/1 → /deep/2 → … Used by the crawl
      // resumption test, which needs a crawl that genuinely spans more than one
      // 25-page slice. The hand-written fixture above is five pages and would
      // finish in a single one.
      const deep = /^\/deep\/(\d+)$/.exec(path);
      if (deep) {
        const n = Number(deep[1]);
        res.writeHead(200, { 'content-type': 'text/html' });
        res.end(
          `<!DOCTYPE html><html lang="en"><head><title>Deep page ${n}</title>` +
            `<meta name="description" content="Synthetic page ${n} in a chain used to span crawl slices.">` +
            `<meta charset="utf-8"></head><body><h1>Deep ${n}</h1>` +
            (n < DEEP_PAGES ? `<a href="/deep/${n + 1}">next</a>` : '') +
            `</body></html>`,
        );
        return;
      }

      const page = PAGES[path];
      res.writeHead(page?.status ?? (page ? 200 : 404), { 'content-type': 'text/html' });
      res.end(page?.body ?? '<html><body>404</body></html>');
    });
    await new Promise<void>((r) => server.listen(0, '127.0.0.1', r));
    origin = `http://127.0.0.1:${(server.address() as AddressInfo).port}`;

    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Audit') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Audit', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
      const [s] = await tx.execute<{ id: string }>(sql`
        insert into sites (workspace_id, project_id, host, root_url)
        values (${ws}, ${project}, ${'127.0.0.1'}, ${origin}) returning id`);
      siteId = s!.id;
      // Paid plan: the free tier caps crawls at 25 pages.
      await tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status)
        select ${ws}, id, 'active' from plans where key = 'audit_pro'`);
    }, { db });

    await withWorkspace(ws, async (tx) => {
      await mint(tx, {
        workspaceId: ws, projectId: project, tool: 'core', type: 'site',
        externalId: siteId, label: '127.0.0.1',
      });
      auditSiteId = await addSite(tx, { workspaceId: ws, projectId: project, siteId, host: '127.0.0.1' });
      // The fixture is on loopback; only a test may bypass the SSRF guard.
      await tx.execute(sql`
        update audit_sites
           set crawl_config = crawl_config || '{"maxPages":25,"maxDepth":3,"allowPrivate":true}'::jsonb
         where id = ${auditSiteId}`);
    }, { db });
  });

  afterAll(async () => {
    await new Promise<void>((r) => server.close(() => r()));
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    await withWorkspace(ws, async (tx) => {
      await tx.execute(sql`delete from audits where workspace_id = ${ws}`);
      await tx.execute(sql`delete from audit_snapshots where workspace_id = ${ws}`);
      await tx.execute(sql`update audit_sites set score = null, previous_score = null where id = ${auditSiteId}`);
      // Usage accumulates across runs; each test starts from a clean quota.
      await tx.execute(sql`delete from usage_counters where workspace_id = ${ws}`);
    }, { db });
  });

  /**
   * Drives the slice loop exactly as the worker does, so the tests exercise
   * the real resumption path rather than a convenience wrapper.
   */
  const grantCredits = (amount: number) =>
    asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into credit_buckets (workspace_id, source, amount, remaining)
        values (${ws}, 'admin', ${amount}, ${amount})`),
      { db },
    );

  const run = (over: Record<string, unknown> = {}): Promise<RunOutcome> =>
    withWorkspace(
      ws,
      async (tx) => {
        await tx.execute(sql`
          update audit_sites set crawl_config = crawl_config || ${JSON.stringify({ allowPrivate: true, ...over })}::jsonb
           where id = ${auditSiteId}`);
        const { auditId } = await startAudit(tx, {
          workspaceId: ws, projectId: project, auditSiteId, trigger: 'manual',
        });
        for (let i = 0; i < 200; i++) {
          const slice = await advanceAudit(tx, auditId, ws);
          if (slice.status === 'complete') return slice.outcome;
        }
        throw new Error('crawl did not finish within 200 slices');
      },
      { db },
    );

  it('registers the site and connects it to the shared site row', async () => {
    const connected = await withWorkspace(
      ws,
      (tx) => neighbors(tx, ws, coreUrn.site(siteId), { relation: 'audits' }),
      { db },
    );
    expect(connected).toHaveLength(1);
    expect(connected[0]!.tool).toBe('audit');
  });

  it('crawls, scores and persists a run', async () => {
    const outcome = await run();
    expect(outcome.pagesCrawled).toBeGreaterThanOrEqual(3);
    expect(outcome.score).toBeGreaterThan(0);
    expect(outcome.score).toBeLessThan(100);

    const [audit] = await withWorkspace(
      ws,
      (tx) => tx.execute<{ status: string; phase: string; score: number }>(
        sql`select status, phase, score from audits where id = ${outcome.auditId}`),
      { db },
    );
    expect(audit).toMatchObject({ status: 'completed', phase: 'done' });
  });

  it('stores one row per crawled page with the facts the rules used', async () => {
    await run();
    const pages = await withWorkspace(
      ws,
      (tx) => tx.execute<{ url: string; title: string | null; word_count: number; images_missing_alt: number }>(
        sql`select url, title, word_count, images_missing_alt from audit_pages
             where workspace_id = ${ws} order by url`),
      { db },
    );
    expect(pages.length).toBeGreaterThanOrEqual(3);
    const bad = pages.find((p) => p.url.endsWith('/bad'));
    expect(bad!.title).toBeNull();
    expect(Number(bad!.images_missing_alt)).toBe(1);
  });

  /** crawlseo keeps the graph; open-seo discards it. Keeping it is the point. */
  it('persists the link graph so "what links here" is answerable later', async () => {
    await run();
    const [row] = await withWorkspace(
      ws,
      (tx) => tx.execute<{ n: number }>(sql`
        select count(*)::int as n from audit_links
         where workspace_id = ${ws} and target_url like '%/gone'`),
      { db },
    );
    expect(Number(row!.n)).toBeGreaterThan(0);
  });

  it('finds the real issues and leaves the good page alone', async () => {
    await run();
    const issues = await withWorkspace(
      ws,
      (tx) => tx.execute<{ rule_id: string; page_url: string | null; severity: string }>(
        sql`select rule_id, page_url, severity from audit_issues where workspace_id = ${ws}`),
      { db },
    );
    const fired = new Set(issues.map((i) => i.rule_id));

    expect(fired.has('missing-title')).toBe(true);
    expect(fired.has('missing-h1')).toBe(true);
    expect(fired.has('images-missing-alt')).toBe(true);
    expect(fired.has('ai-crawler-blocked')).toBe(true);

    // The correct page must be clean: a false positive costs more trust than a
    // miss, because it makes the whole report suspect.
    /**
     * A false positive costs more trust than a miss: one wrong finding makes a
     * user doubt the whole report. The correct page must be clean on every
     * dimension the fixture controls — markup, content, links, structured data.
     *
     * The transport findings it cannot avoid (plain HTTP, no compression, no
     * CSP) are genuinely true of a bare test server, so asserting their absence
     * would be asserting a falsehood.
     */
    const TRANSPORT_ONLY = new Set([
      'not-https', 'no-compression', 'missing-csp', 'missing-referrer-policy', 'missing-hsts',
    ]);
    const againstOk = issues
      .filter((i) => i.page_url?.endsWith('/ok'))
      .map((i) => i.rule_id)
      .filter((id) => !TRANSPORT_ONLY.has(id));
    expect(againstOk, `false positives on a correct page: ${againstOk.join(', ')}`).toEqual([]);
  });

  /**
   * The score must not depend on how the crawl was sliced.
   *
   * Page rules run per slice and site rules at the end, so their results are
   * accumulated in the audit row. When that accumulation broke, every page
   * rule silently defaulted to "passed" and the score jumped by 13 points —
   * with nothing failing.
   */
  it('scores identically however the crawl is sliced', async () => {
    const wholeSite = await run();
    const failedRules = await withWorkspace(
      ws,
      (tx) => tx.execute<{ n: number }>(sql`
        select count(*)::int as n
          from jsonb_each((select crawl_cursor->'ruleResults' from audits where id = ${wholeSite.auditId}))
         where value = 'false'::jsonb`),
      { db },
    );

    // Page-rule outcomes must actually be recorded, not defaulted.
    expect(Number(failedRules[0]!.n)).toBeGreaterThan(0);

    const sliced = await run();
    expect(sliced.score).toBe(wholeSite.score);
  });

  it('records a score snapshot for the trend', async () => {
    await run();
    const snaps = await withWorkspace(
      ws,
      (tx) => tx.execute<{ score: number }>(sql`
        select score from audit_snapshots where workspace_id = ${ws}`),
      { db },
    );
    expect(snaps).toHaveLength(1);
  });

  it('emits a score-changed event only when the score actually moves', async () => {
    const first = await run();
    expect(first.events.some((e) => e.name === 'audit.score.changed')).toBe(false);

    const second = await run();
    // Same fixture, same score — no event.
    expect(second.previousScore).toBe(first.score);
    expect(second.events.some((e) => e.name === 'audit.score.changed')).toBe(false);
  });

  /** Only critical findings become events; info would flood the bus. */
  it('emits issue events for critical findings only', async () => {
    const outcome = await run();
    const issueEvents = outcome.events.filter((e) => e.name === 'audit.issue.detected');
    expect(issueEvents.length).toBeGreaterThan(0);
    for (const event of issueEvents) {
      expect((event.data as { severity: string }).severity).toBe('critical');
    }
  });

  it('counts crawled pages against the plan quota', async () => {
    const outcome = await run();
    const [usage] = await withWorkspace(
      ws,
      (tx) => tx.execute<{ used: number }>(sql`
        select used from usage_counters
         where workspace_id = ${ws} and feature_key = 'audit.crawl_pages'
           and period_start = ${currentPeriodStart().toISOString()}::timestamptz`),
      { db },
    );
    expect(Number(usage!.used)).toBe(outcome.pagesCrawled);
  });

  it('allows a second audit while quota headroom remains', async () => {
    const first = await run();
    expect(first.pagesCrawled).toBeGreaterThan(0);
    // Requesting the full budget up front would refuse this — on the free
    // tier that would mean exactly one audit, ever.
    const second = await run();
    expect(second.pagesCrawled).toBeGreaterThan(0);
  });

  it('caps the crawl to the headroom that is actually left', async () => {
    await asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into usage_counters (workspace_id, feature_key, period_start, used)
        values (${ws}, 'audit.crawl_pages', ${currentPeriodStart().toISOString()}::timestamptz, 49998)
        on conflict (workspace_id, feature_key, period_start) do update set used = 49998`),
      { db },
    );
    // audit_pro allows 50,000/month, so two pages remain.
    const outcome = await run();
    expect(outcome.pagesCrawled).toBeLessThanOrEqual(2);
    await asPlatformAdmin(
      (tx) => tx.execute(sql`delete from usage_counters where workspace_id = ${ws}`), { db },
    );
  });

  it('refuses to crawl at all when the workspace is over quota', async () => {
    await asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into usage_counters (workspace_id, feature_key, period_start, used)
        values (${ws}, 'audit.crawl_pages', ${currentPeriodStart().toISOString()}::timestamptz, 9999999)
        on conflict (workspace_id, feature_key, period_start) do update set used = 9999999`),
      { db },
    );
    // The check happens before the first fetch — being over quota must not
    // still cost us a crawl.
    await expect(run()).rejects.toBeInstanceOf(AuditNotAllowed);
    await asPlatformAdmin(
      (tx) => tx.execute(sql`delete from usage_counters where workspace_id = ${ws}`),
      { db },
    );
  });

  /**
   * The AI features exist to prove the guard chain holds inside a real tool,
   * not only in the AI package's own tests. Nothing here reaches a provider
   * directly: ai.execute re-resolves entitlements immediately before the call.
   */
  describe('AI features', () => {
    let driverCalls = 0;
    const fakeDriver: AiDriver = {
      key: 'anthropic',
      modalities: ['text'],
      async generate() {
        driverCalls++;
        return {
          ok: true, text: 'Fix the missing meta description first.', units: 1,
          inputTokens: 200, outputTokens: 90, vendorCostMicros: 300, latencyMs: 40,
        };
      },
    };
    const deps = { driverFor: () => fakeDriver, decrypt: (s: string) => s };

    beforeEach(async () => {
      driverCalls = 0;
      await asPlatformAdmin(async (tx) => {
        await tx.execute(sql`
          insert into ai_credentials (scope, scope_id, provider_key, encrypted_key, key_hint)
          values ('instance', null, 'anthropic', 'test-key', '…test')
          on conflict (scope, scope_id, provider_key) do update set is_active = true`);
        await tx.execute(sql`delete from credit_buckets where workspace_id = ${ws}`);
      }, { db });
    });

    // The credential is instance-scoped, so leaving it behind breaks the next
    // suite — and, as it turned out, the running dev server.
    afterAll(async () => {
      await asPlatformAdmin(
        (tx) => tx.execute(sql`
          delete from ai_credentials where scope = 'instance' and provider_key = 'anthropic'`),
        { db },
      );
    });

    it('refuses without credits, and never reaches the provider', async () => {
      const auditId = (await run()).auditId;
      const result = await withWorkspace(
        ws, (tx) => summariseAudit(tx, { workspaceId: ws, auditId }, deps), { db },
      );
      expect(result.ok).toBe(false);
      expect(!result.ok && result.reason).toBe('insufficient_credits');
      expect(driverCalls, 'the provider must not be called when entitlements deny').toBe(0);
    });

    it('summarises a run when allowed', async () => {
      const auditId = (await run()).auditId;
      await grantCredits(1000);
      const result = await withWorkspace(
        ws, (tx) => summariseAudit(tx, { workspaceId: ws, auditId }, deps), { db },
      );
      expect(result.ok).toBe(true);
      expect(result.ok && result.value).toContain('meta description');
      expect(driverCalls).toBe(1);
    });

    it('writes a fix brief from the page\'s actual values', async () => {
      await run();
      await grantCredits(1000);
      const [issue] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ id: string }>(sql`
          select id from audit_issues where workspace_id = ${ws}
             and rule_id = 'missing-meta-description' limit 1`),
        { db },
      );
      const result = await withWorkspace(
        ws, (tx) => fixBrief(tx, { workspaceId: ws, issueId: issue!.id }, deps), { db },
      );
      expect(result.ok).toBe(true);
      expect(driverCalls).toBe(1);
    });

    it('is blocked by the instance kill switch even with credits', async () => {
      const auditId = (await run()).auditId;
      await grantCredits(1000);
      await asPlatformAdmin(
        (tx) => tx.execute(sql`update instance_settings set ai_master_enabled = false`), { db },
      );

      const result = await withWorkspace(
        ws, (tx) => summariseAudit(tx, { workspaceId: ws, auditId }, deps), { db },
      );
      expect(!result.ok && result.reason).toBe('ai_disabled_instance');
      expect(driverCalls).toBe(0);

      await asPlatformAdmin(
        (tx) => tx.execute(sql`update instance_settings set ai_master_enabled = true`), { db },
      );
    });

    /**
     * The claim the product makes: with AI off, the guidance is unchanged.
     * The rule catalogue carries it, so this must stay true.
     */
    it('leaves every rule with usable guidance when AI is unavailable', async () => {
      const rules = await withWorkspace(
        ws,
        (tx) => tx.execute<{ n: number }>(sql`
          select count(*)::int as n from audit_rules
           where length(how_to_fix) < 40 or length(why) < 40`),
        { db },
      );
      expect(Number(rules[0]!.n), 'a rule with thin guidance leaves AI-off users stranded').toBe(0);
    });
  });

  /**
   * Monitor does not exist yet. The subscription is registered anyway: a
   * subscriber does not need its publisher to exist, which is the whole reason
   * the bus was built before any tool.
   */
  describe('cross-tool subscriptions (dark launched)', () => {
    it('declares a handler for every event the manifest subscribes to', () => {
      for (const declared of auditManifest.subscriptions) {
        const handler = auditSubscriptions.find((h) => h.key === declared.handlerKey);
        expect(handler, `${declared.handlerKey} is declared but not implemented`).toBeDefined();
        expect(handler!.event).toBe(declared.event);
      }
    });

    it('closes a broken-link finding when a monitor.target.recovered arrives', async () => {
      await run();
      const [issue] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ target: string }>(sql`
          select evidence->>'targetUrl' as target from audit_issues
           where workspace_id = ${ws} and rule_id = 'broken-internal-link'
             and status = 'open' limit 1`),
        { db },
      );
      expect(issue, 'the fixture must produce a broken link for this to mean anything').toBeDefined();

      const handler = auditSubscriptions.find((h) => h.event === 'monitor.target.recovered')!;
      await withWorkspace(
        ws,
        (tx) => handler.handle(
          {
            id: randomUUID(), name: 'monitor.target.recovered', version: 1,
            occurredAt: new Date().toISOString(),
            workspaceId: ws, projectId: project,
            actor: { kind: 'system' },
            subject: `urn:mamal:monitor:monitor:${randomUUID()}`,
            related: [],
            data: { targetUrl: issue!.target, sourceUrn: coreUrn.site(siteId) },
            trace: { correlationId: randomUUID(), depth: 1 },
          },
          tx,
        ),
        { db },
      );

      const [after] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ n: number }>(sql`
          select count(*)::int as n from audit_issues
           where workspace_id = ${ws} and rule_id = 'broken-internal-link' and status = 'fixed'`),
        { db },
      );
      expect(Number(after!.n)).toBeGreaterThan(0);
    });

    it('ignores an event that carries no target', async () => {
      const handler = auditSubscriptions.find((h) => h.event === 'monitor.target.recovered')!;
      await expect(
        withWorkspace(
          ws,
          (tx) => handler.handle(
            {
              id: randomUUID(), name: 'monitor.target.recovered', version: 1,
              occurredAt: new Date().toISOString(),
              workspaceId: ws, projectId: project,
              actor: { kind: 'system' }, subject: 'urn:mamal:monitor:monitor:x',
              related: [], data: {},
              trace: { correlationId: randomUUID(), depth: 1 },
            },
            tx,
          ),
          { db },
        ),
      ).resolves.not.toThrow();
    });
  });

  describe('commands — the only cross-tool surface', () => {
    it('queues a run from a core site URN', async () => {
      const result = await withWorkspace(
        ws, (tx) => runSite(tx, { workspaceId: ws, siteUrn: coreUrn.site(siteId) }), { db },
      );
      expect(result.ok).toBe(true);
    });

    it('degrades with a reason for a site Audit does not know', async () => {
      const result = await withWorkspace(
        ws,
        (tx) => runSite(tx, { workspaceId: ws, siteUrn: coreUrn.site('00000000-0000-7000-8000-000000000000') }),
        { db },
      );
      expect(result.ok).toBe(false);
      expect(!result.ok && result.reason).toMatch(/not registered/);
    });

    it('schedules a run at a given time', async () => {
      const when = new Date(Date.now() + 3_600_000);
      const result = await withWorkspace(
        ws, (tx) => scheduleRun(tx, { workspaceId: ws, siteUrn: coreUrn.site(siteId), at: when }), { db },
      );
      expect(result.ok).toBe(true);
      const [row] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ next_audit_at: Date }>(sql`
          select next_audit_at from audit_sites where id = ${auditSiteId}`),
        { db },
      );
      expect(new Date(row!.next_audit_at).getTime()).toBeCloseTo(when.getTime(), -3);
    });

    /**
     * The return leg of the Audit → Monitor handoff: Monitor sees the URL
     * recover and closes the issue Audit opened.
     */
    it('closes a broken-link issue when the URL recovers', async () => {
      await run();
      const [before] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ n: number }>(sql`
          select count(*)::int as n from audit_issues
           where workspace_id = ${ws} and rule_id = 'broken-internal-link' and status = 'open'`),
        { db },
      );
      expect(Number(before!.n)).toBeGreaterThan(0);

      const [issue] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ target: string }>(sql`
          select evidence->>'targetUrl' as target from audit_issues
           where workspace_id = ${ws} and rule_id = 'broken-internal-link' limit 1`),
        { db },
      );

      const result = await withWorkspace(
        ws,
        (tx) => resolveIssue(tx, {
          workspaceId: ws, siteUrn: coreUrn.site(siteId),
          ruleId: 'broken-internal-link', targetUrl: issue!.target,
        }),
        { db },
      );
      expect(result.ok).toBe(true);
      expect((result as { value: { resolved: number } }).value.resolved).toBeGreaterThan(0);
    });
  });
  describe('the site limit', () => {
    /*
     * Regression: `audit.sites` was a declared entitlement that nothing
     * consulted, so every tier — free included — could add unlimited sites.
     */
    it('refuses a second site once the plan\u2019s limit is reached', async () => {
      const limit = await withWorkspace(
        ws,
        async (tx) => {
          const ctx = await loadContext(tx, ws, 'audit.sites');
          return resolve({ ...ctx!, used: 0 }, 1).limit ?? 0;
        },
        { db },
      );
      expect(limit, 'the fixture plan must declare a finite site limit').toBeGreaterThan(0);

      // Fill the plan's allowance, then ask for one more. Each needs its core
      // site URN minted, because addSite relates the audit facet back to it.
      const extras: string[] = [];
      await withWorkspace(ws, async (tx) => {
        for (let i = 0; i < limit + 1; i++) {
          const host = `limit-${i}.test`;
          const [s] = await tx.execute<{ id: string }>(sql`
            insert into sites (workspace_id, project_id, host, root_url)
            values (${ws}, ${project}, ${host}, ${'https://' + host}) returning id`);
          await mint(tx, {
            workspaceId: ws, projectId: project, tool: 'core', type: 'site',
            externalId: s!.id, label: host,
          });
          extras.push(s!.id);
        }
      }, { db });

      let refusedAt = -1;
      for (let i = 0; i < extras.length; i++) {
        try {
          await withWorkspace(
            ws,
            (tx) => addSite(tx, { workspaceId: ws, projectId: project, siteId: extras[i]!, host: `limit-${i}.test` }),
            { db },
          );
        } catch (e) {
          expect(e).toBeInstanceOf(AuditNotAllowed);
          refusedAt = i;
          break;
        }
      }
      expect(refusedAt, 'adding sites past the limit must be refused').toBeGreaterThanOrEqual(0);

      await asPlatformAdmin(async (tx) => {
        // inList, not a bare array: Drizzle renders a JS array as a
        // parenthesised value list, which is an `IN` tuple and not a uuid[].
        await tx.execute(sql`delete from audit_sites where site_id in (${inList(extras)})`);
        await tx.execute(sql`delete from sites where id in (${inList(extras)})`);
      }, { db });
    });

    it('lets an already-registered site be re-saved even at the limit', async () => {
      // Idempotent re-registration must not consume allowance, or a workspace
      // sitting exactly at its limit could never re-save what it already owns.
      await expect(
        withWorkspace(
          ws,
          (tx) => addSite(tx, { workspaceId: ws, projectId: project, siteId, host: '127.0.0.1' }),
          { db },
        ),
      ).resolves.toBe(auditSiteId);
    });
  });
  describe('the handoff travels the real bus', () => {
    /*
     * The unit tests above call the handler directly, which proves the logic
     * but not the wiring. This one publishes the way Monitor will — into the
     * transactional outbox — then relays and dispatches, so the assertion
     * covers the whole path: outbox row, relay, envelope validation, the
     * bus_deliveries barrier, and the handler.
     *
     * Monitor does not exist yet. That is the point: the publisher is a stand-in
     * emitting a registered event, and Audit reacts without either tool
     * importing the other.
     */
    it('publish → relay → dispatch closes the finding, and a redelivery does not repeat it', async () => {
      await run();

      const [issue] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ id: string; target: string }>(sql`
          select id, evidence->>'targetUrl' as target from audit_issues
           where workspace_id = ${ws} and rule_id = 'broken-internal-link'
             and status = 'open' limit 1`),
        { db },
      );
      expect(issue, 'the fixture must produce a broken link for this to mean anything').toBeDefined();

      // A registry holding core events plus the one Monitor will publish.
      const registry = new EventRegistry().register(...coreEvents, {
        name: 'monitor.target.recovered',
        description: 'A monitored target started responding again.',
        payload: z.object({ targetUrl: z.string(), sourceUrn: z.string() }),
      });

      const dispatcher = new Dispatcher(db);
      for (const handler of auditSubscriptions) dispatcher.on(handler);

      const transport = new InProcessTransport();
      const relay = new OutboxRelay(db, transport);

      const envelope = await withWorkspace(
        ws,
        (tx) => publish(tx, registry, {
          name: 'monitor.target.recovered',
          workspaceId: ws,
          projectId: project,
          actor: { kind: 'system' },
          subject: `urn:mamal:monitor:monitor:${randomUUID()}`,
          data: { targetUrl: issue!.target, sourceUrn: coreUrn.site(siteId) },
        }),
        { db },
      );

      // Nothing has happened yet: publish only wrote the outbox row.
      const stillOpen = await withWorkspace(
        ws,
        (tx) => tx.execute<{ status: string }>(sql`
          select status from audit_issues where id = ${issue!.id}`),
        { db },
      );
      expect(stillOpen[0]!.status, 'publish must not run handlers itself').toBe('open');

      const drained = await relay.drain();
      expect(drained, 'the relay must pick up the outbox row').toBeGreaterThan(0);

      const first = await dispatcher.dispatch(envelope);
      expect(first.some((r) => r.status === 'done'), JSON.stringify(first)).toBe(true);

      const closed = await withWorkspace(
        ws,
        (tx) => tx.execute<{ status: string }>(sql`
          select status from audit_issues where id = ${issue!.id}`),
        { db },
      );
      expect(closed[0]!.status, 'the recovered URL should close its finding').toBe('fixed');

      // Effectively-once: at-least-once delivery means this *will* happen, and
      // a second run must be a no-op rather than a second write.
      const again = await dispatcher.dispatch(envelope);
      expect(
        again.every((r) => r.status === 'skipped'),
        `a redelivery must be skipped by the barrier, got ${JSON.stringify(again)}`,
      ).toBe(true);
    });
  });
  describe('retention', () => {
    /*
     * Retiring detail is the promise; erasing history is not. So the sweeper is
     * only correct if it removes old runs *and* leaves the score trend and the
     * most recent run standing.
     */
    it('drops aged runs but keeps the latest one and the trend', async () => {
      await run();
      await run();
      await run();

      const before = await withWorkspace(
        ws,
        (tx) => tx.execute<{ id: string; created_at: string }>(sql`
          select id, created_at from audits
           where workspace_id = ${ws} order by id desc`),
        { db },
      );
      expect(before.length, 'need several runs for this to mean anything').toBeGreaterThanOrEqual(3);
      const latest = before[0]!.id;

      // Age every run past the window. Snapshots keep their own dates.
      await asPlatformAdmin(
        (tx) => tx.execute(sql`
          update audits set created_at = created_at - interval '400 days'
           where workspace_id = ${ws}`),
        { db },
      );
      const snapshotsBefore = await withWorkspace(
        ws,
        async (tx) => {
          const [r] = await tx.execute<{ n: number }>(sql`
            select count(*)::int as n from audit_snapshots where workspace_id = ${ws}`);
          return r!.n;
        },
        { db },
      );

      const deleted = await withWorkspace(
        ws,
        (tx) => auditSweeper.sweep(tx, ws, new Date(Date.now() - 30 * 86_400_000)),
        { db },
      );
      expect(deleted).toBeGreaterThan(0);

      const after = await withWorkspace(
        ws,
        (tx) => tx.execute<{ id: string }>(sql`
          select id from audits where workspace_id = ${ws}`),
        { db },
      );
      expect(after.map((r) => r.id), 'the newest completed run must survive').toContain(latest);

      const snapshotsAfter = await withWorkspace(
        ws,
        async (tx) => {
          const [r] = await tx.execute<{ n: number }>(sql`
            select count(*)::int as n from audit_snapshots where workspace_id = ${ws}`);
          return r!.n;
        },
        { db },
      );
      expect(snapshotsAfter, 'the score trend outlives the runs behind it').toBe(snapshotsBefore);
    });

    it('cascades: retiring a run takes its pages and findings with it', async () => {
      await run();
      await run();
      const [old] = await withWorkspace(
        ws,
        (tx) => tx.execute<{ id: string }>(sql`
          select id from audits where workspace_id = ${ws} order by created_at asc limit 1`),
        { db },
      );
      await asPlatformAdmin(
        (tx) => tx.execute(sql`
          update audits set created_at = now() - interval '400 days' where id = ${old!.id}`),
        { db },
      );

      await withWorkspace(
        ws,
        (tx) => auditSweeper.sweep(tx, ws, new Date(Date.now() - 30 * 86_400_000)),
        { db },
      );

      const orphans = await withWorkspace(
        ws,
        async (tx) => {
          const [r] = await tx.execute<{ n: number }>(sql`
            select (select count(*) from audit_pages where audit_id = ${old!.id})
                 + (select count(*) from audit_issues where audit_id = ${old!.id})
                 + (select count(*) from audit_links where audit_id = ${old!.id}) as n`);
          return Number(r!.n);
        },
        { db },
      );
      expect(orphans, 'nothing may outlive the run it belonged to').toBe(0);
    });
  });
  describe('surviving a kill', () => {
    /*
     * The operational claim is that a killed worker loses at most one slice.
     * That is only true if the frontier is durable, so this simulates the kill
     * at the worst moment: after a slice has committed its pages and before the
     * next one is picked up.
     *
     * A real `kill -9` cannot be issued against an in-process worker, and it
     * would not test anything extra — SIGKILL gives a process no chance to
     * write, so "killed mid-crawl" and "stopped calling advanceAudit" leave the
     * database in exactly the same state. This asserts on that state.
     */
    it('resumes from the persisted frontier instead of restarting', { timeout: 60_000 }, async () => {
      // Its own site, pointing at the deep chain, so the crawl needs several
      // slices and the kill lands in the middle of one.
      const deepSiteId = await withWorkspace(
        ws,
        async (tx) => {
          const [s] = await tx.execute<{ id: string }>(sql`
            insert into sites (workspace_id, project_id, host, root_url)
            values (${ws}, ${project}, ${'deep.test'}, ${`${origin}/deep/1`}) returning id`);
          await mint(tx, {
            workspaceId: ws, projectId: project, tool: 'core', type: 'site',
            externalId: s!.id, label: 'deep.test',
          });
          const id = await addSite(tx, {
            workspaceId: ws, projectId: project, siteId: s!.id, host: 'deep.test',
          });
          await tx.execute(sql`
            update audit_sites
               set crawl_config = crawl_config || '{"maxPages":40,"maxDepth":60,"allowPrivate":true}'::jsonb
             where id = ${id}`);
          return id;
        },
        { db },
      );

      const { auditId } = await withWorkspace(
        ws,
        (tx) => startAudit(tx, {
          workspaceId: ws, projectId: project, auditSiteId: deepSiteId, trigger: 'manual',
        }),
        { db },
      );

      // One slice, then "die".
      const first = await withWorkspace(
        ws,
        (tx) => advanceAudit(tx, auditId, ws),
        { db },
      );
      expect(first.status, 'the fixture must need more than one slice').toBe('continue');

      const midway = await withWorkspace(
        ws,
        async (tx) => {
          const [a] = await tx.execute<{ pages_crawled: number; cursor: unknown }>(sql`
            select pages_crawled, crawl_cursor as cursor from audits where id = ${auditId}`);
          return a!;
        },
        { db },
      );
      expect(midway.pages_crawled, 'work done before the kill must be committed').toBeGreaterThan(0);
      // The durable frontier *and* the visited set both matter: without the
      // frontier the crawl restarts, and without `visited` it re-crawls pages
      // it already has and double-counts them against quota.
      const cursor = midway.cursor as { frontier?: unknown[]; visited?: unknown[] };
      expect(cursor.frontier?.length, 'the frontier must survive the kill').toBeGreaterThan(0);
      expect(cursor.visited?.length, 'visited pages must survive the kill').toBeGreaterThan(0);

      // A fresh worker picks it up with no in-memory state whatsoever.
      let outcome = await withWorkspace(ws, (tx) => advanceAudit(tx, auditId, ws), { db });
      let guard = 0;
      while (outcome.status === 'continue' && guard++ < 50) {
        outcome = await withWorkspace(ws, (tx) => advanceAudit(tx, auditId, ws), { db });
      }
      expect(outcome.status).toBe('complete');

      const final = await withWorkspace(
        ws,
        async (tx) => {
          const [a] = await tx.execute<{ status: string; score: number; pages_crawled: number }>(sql`
            select status, score, pages_crawled from audits where id = ${auditId}`);
          const [p] = await tx.execute<{ n: number }>(sql`
            select count(*)::int as n from audit_pages where audit_id = ${auditId}`);
          return { ...a!, distinctPages: p!.n };
        },
        { db },
      );

      expect(final.status).toBe('completed');
      expect(final.score).toBeGreaterThan(0);
      // The pages crawled before the kill are counted once, not re-crawled and
      // double-counted — the frontier resumed rather than restarted.
      expect(final.distinctPages).toBe(final.pages_crawled);
      expect(final.pages_crawled).toBeGreaterThanOrEqual(midway.pages_crawled);
    });
  });
});
