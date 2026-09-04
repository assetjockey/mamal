/**
 * Phase 0's exit criterion: a tool publishes an event, the relay carries it,
 * an automation evaluates it and calls a SECOND tool — with neither tool
 * importing the other.
 */
import { randomUUID } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { z } from 'zod';
import { afterAll, beforeAll, beforeEach, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { Dispatcher, EventRegistry, InProcessTransport, OutboxRelay, publish } from '@mamal/bus';
import { defineTool, ToolRegistry } from '@mamal/tool-kit';
import { coreUrn, mint, neighbors } from '@mamal/resources';
import { AutomationRunner } from '../runner.ts';
import type { CommandExecutor } from '../actions.ts';

const URL = process.env.TEST_DATABASE_URL;
const d = URL ? describe : describe.skip;

d('automations end to end', () => {
  const db = unsafeUnscopedDb(URL);
  const tag = `auto${Date.now()}`;
  let ws = '';
  let project = '';
  let siteId = '';

  /** Two independent tools that know nothing about each other. */
  const auditTool = defineTool({
    key: 'audit',
    name: 'Audit',
    basePath: '/audit',
    events: [{
      name: 'audit.issue.detected',
      payload: z.object({ ruleId: z.string(), severity: z.string(), targetUrl: z.string() }),
    }],
    features: [{ key: 'audit.sites', name: 'Sites', kind: 'limit', freeTierAllowed: true }],
  });

  const monitorTool = defineTool({
    key: 'monitor',
    name: 'Monitor',
    basePath: '/monitor',
    commands: [{ name: 'monitor.createCheck', input: z.object({ target: z.string() }) }],
    features: [{ key: 'monitor.monitors', name: 'Monitors', kind: 'limit', freeTierAllowed: true }],
  });

  const registry = new EventRegistry().register(...auditTool.events);
  const tools = new ToolRegistry().register(auditTool).register(monitorTool);

  /** Records what Monitor was asked to do, and actually mints the resource. */
  const calls: { name: string; input: Record<string, unknown> }[] = [];
  const execute: CommandExecutor = async (name, input, ctx) => {
    calls.push({ name, input });
    if (name === 'monitor.createCheck') {
      const monitorId = randomUUID();
      await mint(ctx.tx, {
        workspaceId: ws,
        projectId: project,
        tool: 'monitor',
        type: 'monitor',
        externalId: monitorId,
        label: `check ${String(input.target)}`,
      });
      await ctx.tx.execute(sql`
        insert into resource_links (workspace_id, from_resource_id, to_resource_id, relation, created_by)
        select ${ws}, m.id, s.id, 'monitors', ${'automation:' + ctx.automationId}
          from resources m, resources s
         where m.workspace_id = ${ws} and m.external_id = ${monitorId}
           and s.workspace_id = ${ws} and s.urn = ${String(input.sourceUrn)}
        on conflict do nothing`);
      return { ok: true, detail: `monitor ${monitorId}` };
    }
    return { ok: true };
  };

  const runner = new AutomationRunner({ registry: tools, execute });

  const emit = (data: Record<string, unknown>, subject: string) =>
    withWorkspace(
      ws,
      (tx) =>
        publish(tx, registry, {
          name: 'audit.issue.detected',
          workspaceId: ws,
          projectId: project,
          subject,
          data,
        }),
      { db },
    );

  /** Publish → relay → dispatch, i.e. the real path, not a direct call. */
  const deliver = async () => {
    const transport = new InProcessTransport();
    const dispatcher = new Dispatcher(db).on(runner.handlerFor('audit.issue.detected'));
    transport.subscribe(async (env) => {
      await dispatcher.dispatch(env);
    });
    await new OutboxRelay(db, transport).drain();
  };

  const createAutomation = (over: Record<string, unknown> = {}) =>
    asPlatformAdmin(
      (tx) =>
        tx.execute<{ id: string }>(sql`
          insert into automations
            (workspace_id, project_id, name, trigger_event, trigger_filter, conditions, actions,
             run_limit_per_hour, enabled)
          values (
            ${ws}, ${project}, 'Watch broken links', 'audit.issue.detected',
            ${JSON.stringify(over.filter ?? { 'data.ruleId': 'broken-internal-link' })}::jsonb,
            ${JSON.stringify(over.conditions ?? [
              { op: 'resource.has_relation', subject: '{{subject}}', relation: 'monitors', negate: true },
              { op: 'entitlement.allows', feature: 'monitor.monitors', quantity: 1 },
            ])}::jsonb,
            ${JSON.stringify(over.actions ?? [
              { type: 'command', name: 'monitor.createCheck',
                input: { target: '{{data.targetUrl}}', kind: 'http', sourceUrn: '{{subject}}' } },
            ])}::jsonb,
            ${(over.runLimit as number) ?? 1000}, true)
          returning id`),
      { db },
    );

  beforeAll(async () => {
    await asPlatformAdmin(async (tx) => {
      const [u] = await tx.execute<{ id: string }>(sql`
        insert into users (email, name) values (${tag + '@test.local'}, 'Auto') returning id`);
      const [w] = await tx.execute<{ id: string }>(sql`
        insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Auto', ${u!.id}) returning id`);
      ws = w!.id;
      const [p] = await tx.execute<{ id: string }>(sql`
        insert into projects (workspace_id, name, slug, is_default)
        values (${ws}, 'Default', 'default', true) returning id`);
      project = p!.id;
      const [s] = await tx.execute<{ id: string }>(sql`
        insert into sites (workspace_id, project_id, host, root_url)
        values (${ws}, ${project}, ${tag + '.example.com'}, ${'https://' + tag + '.example.com'})
        returning id`);
      siteId = s!.id;
    }, { db });

    await withWorkspace(
      ws,
      (tx) => mint(tx, {
        workspaceId: ws, projectId: project, tool: 'core', type: 'site',
        externalId: siteId, label: 'Example',
      }),
      { db },
    );
  });

  afterAll(async () => {
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from workspaces where id = ${ws}`);
      await tx.execute(sql`delete from users where email = ${tag + '@test.local'}`);
    }, { db });
    await closeDb();
  });

  beforeEach(async () => {
    calls.length = 0;
    await asPlatformAdmin(async (tx) => {
      await tx.execute(sql`delete from automation_runs where workspace_id = ${ws}`);
      await tx.execute(sql`delete from automations where workspace_id = ${ws}`);
      await tx.execute(sql`delete from event_outbox where workspace_id = ${ws}`);
      await tx.execute(sql`
        delete from resource_links where workspace_id = ${ws} and relation = 'monitors'`);
      await tx.execute(sql`
        delete from resources where workspace_id = ${ws} and tool = 'monitor'`);
    }, { db });
  });

  it('Audit → bus → automation → Monitor, with no cross-tool import', async () => {
    await createAutomation();
    await emit(
      { ruleId: 'broken-internal-link', severity: 'critical', targetUrl: 'https://example.com/gone' },
      coreUrn.site(siteId),
    );
    await deliver();

    expect(calls).toHaveLength(1);
    expect(calls[0]!.name).toBe('monitor.createCheck');
    expect(calls[0]!.input.target).toBe('https://example.com/gone');

    // and the two tools' objects are now connected in the registry
    const connected = await withWorkspace(
      ws,
      (tx) => neighbors(tx, ws, coreUrn.site(siteId), { relation: 'monitors' }),
      { db },
    );
    expect(connected).toHaveLength(1);
    expect(connected[0]!.tool).toBe('monitor');
    expect(connected[0]!.createdBy).toMatch(/^automation:/);
  });

  it('does not create a second monitor for the same site', async () => {
    await createAutomation();
    const payload = {
      ruleId: 'broken-internal-link', severity: 'critical', targetUrl: 'https://example.com/gone',
    };
    await emit(payload, coreUrn.site(siteId));
    await deliver();
    await emit(payload, coreUrn.site(siteId));
    await deliver();

    // The has_relation guard is what stops every audit run piling up duplicates.
    expect(calls).toHaveLength(1);
    const runs = await lastRuns();
    expect(runs.map((r) => r.status)).toEqual(['success', 'skipped']);
    expect(runs[1]!.error).toMatch(/no monitors relation/);
  });

  it('skips when the trigger filter does not match', async () => {
    await createAutomation();
    await emit(
      { ruleId: 'missing-title', severity: 'critical', targetUrl: 'https://example.com/x' },
      coreUrn.site(siteId),
    );
    await deliver();
    expect(calls).toHaveLength(0);
    expect((await lastRuns())[0]!.error).toMatch(/filter data.ruleId did not match/);
  });

  it('respects the plan — an automation cannot mint past the entitlement', async () => {
    await createAutomation({
      conditions: [{ op: 'entitlement.allows', feature: 'market.ai_image', quantity: 1 }],
    });
    await emit(
      { ruleId: 'broken-internal-link', severity: 'critical', targetUrl: 'https://example.com/a' },
      coreUrn.site(siteId),
    );
    await deliver();

    expect(calls).toHaveLength(0);
    expect((await lastRuns())[0]!.error).toMatch(/market\.ai_image/);
  });

  it('degrades to a skipped step when the target tool is not installed', async () => {
    const withoutMonitor = new AutomationRunner({
      registry: new ToolRegistry().register(auditTool), // Monitor absent
      execute,
    });
    await createAutomation({ conditions: [] });
    await emit(
      { ruleId: 'broken-internal-link', severity: 'critical', targetUrl: 'https://example.com/b' },
      coreUrn.site(siteId),
    );

    const transport = new InProcessTransport();
    const dispatcher = new Dispatcher(db).on(withoutMonitor.handlerFor('audit.issue.detected'));
    transport.subscribe(async (env) => { await dispatcher.dispatch(env); });
    await new OutboxRelay(db, transport).drain();

    expect(calls).toHaveLength(0);
    const [run] = await lastRuns();
    const steps = run!.steps as { status: string; detail?: string }[];
    expect(steps[0]!.status).toBe('skipped');
    expect(steps[0]!.detail).toMatch(/monitor tool is not installed/);
    // Skipped is not failed — the rest of the rule still runs.
    expect(run!.status).toBe('success');
  });

  it('rate limits a runaway rule', async () => {
    await createAutomation({ conditions: [], runLimit: 1 });
    for (const path of ['/a', '/b']) {
      await emit(
        { ruleId: 'broken-internal-link', severity: 'critical', targetUrl: `https://example.com${path}` },
        coreUrn.site(siteId),
      );
      await deliver();
    }
    const runs = await lastRuns();
    expect(runs[1]!.status).toBe('skipped');
    expect(runs[1]!.error).toMatch(/rate limited/);
  });

  it('records every step for the run history', async () => {
    await createAutomation({ conditions: [] });
    await emit(
      { ruleId: 'broken-internal-link', severity: 'critical', targetUrl: 'https://example.com/c' },
      coreUrn.site(siteId),
    );
    await deliver();
    const [run] = await lastRuns();
    expect(run!.status).toBe('success');
    const steps = run!.steps as { type: string; name?: string; status: string }[];
    expect(steps[0]).toMatchObject({ type: 'command', name: 'monitor.createCheck', status: 'success' });
  });

  async function lastRuns() {
    return asPlatformAdmin(
      (tx) =>
        tx.execute<{ status: string; error: string | null; steps: unknown }>(sql`
          select status, error, steps from automation_runs
           where workspace_id = ${ws} order by started_at asc`),
      { db },
    );
  }
});
