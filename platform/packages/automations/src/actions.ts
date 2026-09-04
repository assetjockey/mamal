import { sql } from 'drizzle-orm';
import { relate } from '@mamal/resources';
import type { ToolRegistry } from '@mamal/tool-kit';
import { evaluateAll, type EvalContext } from './conditions.ts';
import { interpolate, parseDuration, type Action } from './dsl.ts';

export type StepResult = {
  type: string;
  name?: string;
  status: 'success' | 'skipped' | 'failed';
  detail?: string;
  /** Long-running work records its child job so the run stays non-blocking. */
  jobId?: string;
};

/**
 * Executes a command against another tool WITHOUT importing it. The registry
 * resolves the owner; an uninstalled tool degrades to a skipped step with a
 * readable reason rather than throwing.
 */
export type CommandExecutor = (
  name: string,
  input: Record<string, unknown>,
  ctx: EvalContext,
) => Promise<{ ok: boolean; detail?: string; jobId?: string }>;

export type ActionDeps = {
  registry: ToolRegistry;
  execute: CommandExecutor;
  /** Wired to packages/notify; a no-op returns skipped. */
  notify?: (input: Record<string, unknown>, ctx: EvalContext) => Promise<void>;
  /** Outbound HTTP, so tests can stub it. */
  fetch?: typeof globalThis.fetch;
};

export async function runActions(
  actions: Action[],
  ctx: EvalContext,
  deps: ActionDeps,
): Promise<StepResult[]> {
  const steps: StepResult[] = [];
  for (const action of actions) {
    const result = await runAction(action, ctx, deps);
    steps.push(...result);
    // Only a `critical` failure stops the run; everything else keeps going so
    // one dead webhook cannot swallow the rest of the rule.
    if (action.critical && result.some((s) => s.status === 'failed')) break;
  }
  return steps;
}

async function runAction(
  action: Action,
  ctx: EvalContext,
  deps: ActionDeps,
): Promise<StepResult[]> {
  const input = interpolate(action.input, ctx.scope) as Record<string, unknown>;

  try {
    switch (action.type) {
      case 'command': {
        if (!action.name) return [{ type: 'command', status: 'failed', detail: 'missing name' }];
        const lookup = deps.registry.lookupCommand(action.name);
        if (!lookup.ok) {
          return [{ type: 'command', name: action.name, status: 'skipped', detail: lookup.message }];
        }
        const out = await deps.execute(action.name, input, ctx);
        return [{
          type: 'command',
          name: action.name,
          status: out.ok ? 'success' : 'failed',
          detail: out.detail,
          jobId: out.jobId,
        }];
      }

      case 'notify': {
        if (!deps.notify) {
          return [{ type: 'notify', status: 'skipped', detail: 'notify transport not configured' }];
        }
        await deps.notify(input, ctx);
        return [{ type: 'notify', status: 'success' }];
      }

      case 'resource.relate': {
        await relate(ctx.tx, {
          workspaceId: ctx.envelope.workspaceId,
          from: String(input.from),
          to: String(input.to),
          relation: String(input.relation) as never,
          createdBy: `automation:${ctx.automationId}`,
        });
        return [{ type: 'resource.relate', status: 'success', detail: `${input.from} → ${input.to}` }];
      }

      case 'tag': {
        const name = String(input.name);
        const [tag] = await ctx.tx.execute<{ id: string }>(sql`
          insert into tags (workspace_id, name) values (${ctx.envelope.workspaceId}, ${name})
          on conflict (workspace_id, name) do update set name = excluded.name
          returning id`);
        await ctx.tx.execute(sql`
          insert into taggables (workspace_id, tag_id, resource_id)
          select ${ctx.envelope.workspaceId}, ${tag!.id}, id from resources
           where workspace_id = ${ctx.envelope.workspaceId} and urn = ${String(input.urn ?? ctx.envelope.subject)}
          on conflict (tag_id, resource_id) do nothing`);
        return [{ type: 'tag', status: 'success', detail: name }];
      }

      case 'webhook':
      case 'http_request': {
        const url = String(input.url ?? '');
        if (!/^https:\/\//i.test(url)) {
          // Outbound calls from a rule are an SSRF surface; https only, and the
          // job runner adds the private-IP guard before this ships publicly.
          return [{ type: action.type, status: 'failed', detail: 'url must be https' }];
        }
        const doFetch = deps.fetch ?? globalThis.fetch;
        const res = await doFetch(url, {
          method: String(input.method ?? 'POST'),
          headers: { 'content-type': 'application/json', ...(input.headers as object) },
          body: JSON.stringify(input.body ?? ctx.envelope),
          signal: AbortSignal.timeout(10_000),
        });
        return [{
          type: action.type,
          status: res.ok ? 'success' : 'failed',
          detail: `HTTP ${res.status}`,
        }];
      }

      case 'ai.generate':
        return [{ type: 'ai.generate', status: 'skipped', detail: 'AI runner not wired yet' }];

      case 'delay': {
        // Recorded, not slept. Holding a worker for 15 minutes is how you lose
        // a queue; the scheduler re-enters the run at the deadline.
        const ms = parseDuration(action.duration ?? '0s');
        return [{ type: 'delay', status: 'success', detail: `${ms}ms deferred` }];
      }

      case 'branch': {
        const verdict = await evaluateAll(action.conditions ?? [], ctx);
        const chosen = verdict.pass ? (action.then ?? []) : (action.otherwise ?? []);
        const nested = await runActions(chosen, ctx, deps);
        return [
          { type: 'branch', status: 'success', detail: verdict.pass ? 'then' : 'otherwise' },
          ...nested,
        ];
      }

      default:
        return [{ type: String(action.type), status: 'failed', detail: 'unknown action type' }];
    }
  } catch (err) {
    return [{
      type: action.type,
      name: action.name,
      status: 'failed',
      detail: err instanceof Error ? err.message : String(err),
    }];
  }
}
