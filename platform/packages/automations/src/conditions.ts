import { sql } from 'drizzle-orm';
import type { Envelope } from '@mamal/bus';
import type { WorkspaceScopedDb } from '@mamal/db';
import { neighbors } from '@mamal/resources';
import { loadContext, resolve } from '@mamal/entitlements';
import { interpolate, parseDuration, readPath, type Condition } from './dsl.ts';

export type EvalContext = {
  envelope: Envelope;
  tx: WorkspaceScopedDb;
  scope: Record<string, unknown>;
  automationId: string;
};

export type ConditionResult = { pass: boolean; reason?: string };

/** All conditions must pass. The first failure short-circuits and is reported. */
export async function evaluateAll(
  conditions: Condition[],
  ctx: EvalContext,
): Promise<ConditionResult> {
  for (const condition of conditions) {
    const result = await evaluate(condition, ctx);
    if (!result.pass) return result;
  }
  return { pass: true };
}

export async function evaluate(condition: Condition, ctx: EvalContext): Promise<ConditionResult> {
  const raw = await run(condition, ctx);
  const pass = condition.negate ? !raw.pass : raw.pass;
  return {
    pass,
    reason: pass ? undefined : (raw.reason ?? `${condition.op} on ${condition.field ?? '?'}`),
  };
}

async function run(c: Condition, ctx: EvalContext): Promise<ConditionResult> {
  const left = c.field ? readPath(ctx.scope, c.field) : undefined;
  const right = interpolate(c.value, ctx.scope);

  switch (c.op) {
    case 'exists':
      return { pass: left !== undefined && left !== null };
    case 'equals':
      return { pass: left === right, reason: `${String(left)} !== ${String(right)}` };
    case 'not_equals':
      return { pass: left !== right };
    case 'in':
      return { pass: Array.isArray(right) && right.includes(left as never) };
    case 'not_in':
      return { pass: !(Array.isArray(right) && right.includes(left as never)) };
    case 'contains':
      return {
        pass: Array.isArray(left)
          ? left.includes(right as never)
          : typeof left === 'string' && left.includes(String(right)),
      };
    case 'gt':
      return { pass: Number(left) > Number(right) };
    case 'gte':
      return { pass: Number(left) >= Number(right) };
    case 'lt':
      return { pass: Number(left) < Number(right) };
    case 'lte':
      return { pass: Number(left) <= Number(right) };
    case 'regex':
      return { pass: new RegExp(String(right)).test(String(left ?? '')) };

    /**
     * The condition that makes "don't create a monitor we already created"
     * expressible. Without it every audit run would pile up duplicates.
     */
    case 'resource.has_relation': {
      const urn = String(interpolate(c.subject ?? '{{subject}}', ctx.scope));
      const found = await neighbors(ctx.tx, ctx.envelope.workspaceId, urn, {
        relation: c.relation as never,
      });
      return { pass: found.length > 0, reason: `no ${c.relation} relation on ${urn}` };
    }

    /**
     * Automations must respect the plan. An automation that creates monitors
     * cannot mint them past the workspace's limit.
     */
    case 'entitlement.allows': {
      if (!c.feature) return { pass: false, reason: 'entitlement.allows needs a feature' };
      const entCtx = await loadContext(ctx.tx, ctx.envelope.workspaceId, c.feature);
      if (!entCtx) return { pass: false, reason: `unknown feature ${c.feature}` };
      const decision = resolve(entCtx, c.quantity);
      return {
        pass: decision.allowed,
        reason: decision.allowed ? undefined : `${c.feature}: ${decision.reason}`,
      };
    }

    case 'schedule.within': {
      const now = new Date();
      if (c.days && !c.days.includes(now.getUTCDay())) {
        return { pass: false, reason: 'outside allowed days' };
      }
      if (c.from && c.to) {
        const mins = now.getUTCHours() * 60 + now.getUTCMinutes();
        const [fh, fm] = c.from.split(':').map(Number);
        const [th, tm] = c.to.split(':').map(Number);
        const from = (fh ?? 0) * 60 + (fm ?? 0);
        const to = (th ?? 0) * 60 + (tm ?? 0);
        const inside = from <= to ? mins >= from && mins <= to : mins >= from || mins <= to;
        if (!inside) return { pass: false, reason: 'outside allowed hours' };
      }
      return { pass: true };
    }

    /**
     * Rate limiting expressed as a condition rather than buried in the runner,
     * so a rule can say "at most one alert per site per hour" in its own terms.
     */
    case 'throttle.once_per': {
      const windowMs = parseDuration(c.window ?? '1h');
      const key = String(interpolate(c.key ?? '{{subject}}', ctx.scope));
      const [row] = await ctx.tx.execute<{ last: string | null }>(sql`
        select max(started_at)::text as last from automation_runs
         where automation_id = ${ctx.automationId}
           and status = 'success'
           and steps::text like ${'%' + key + '%'}`);
      if (!row?.last) return { pass: true };
      const elapsed = Date.now() - new Date(row.last).getTime();
      return {
        pass: elapsed >= windowMs,
        reason: `throttled — last run ${Math.round(elapsed / 1000)}s ago`,
      };
    }

    case 'ai.classify':
      // Gated behind the AI kill switch and metered; wired with packages/ai.
      return { pass: false, reason: 'ai.classify is not available yet' };

    default:
      return { pass: false, reason: `unknown op ${String(c.op)}` };
  }
}
