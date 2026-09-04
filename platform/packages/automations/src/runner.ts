import { sql } from 'drizzle-orm';
import { MAX_AUTOMATION_DEPTH, type Envelope, type Handler } from '@mamal/bus';
import type { WorkspaceScopedDb } from '@mamal/db';
import { runActions, type ActionDeps, type StepResult } from './actions.ts';
import { evaluateAll } from './conditions.ts';
import { parseDefinition, readPath, type Definition } from './dsl.ts';

export type AutomationRow = {
  id: string;
  name: string;
  enabled: boolean;
  trigger_event: string;
  trigger_filter: Record<string, unknown>;
  conditions: unknown;
  actions: unknown;
  run_limit_per_hour: number;
};

export type RunOutcome = {
  automationId: string;
  status: 'success' | 'skipped' | 'failed';
  reason?: string;
  steps: StepResult[];
};

/**
 * The automations engine.
 *
 * One bus handler drives every rule in the workspace. It is registered as a
 * single dispatcher handler per event name, so the `bus_deliveries` barrier
 * gives the whole fan-out effectively-once semantics for free.
 */
export class AutomationRunner {
  constructor(private readonly deps: ActionDeps) {}

  /** The bus handler. Register one per event the workspace has rules for. */
  handlerFor(eventName: string): Handler {
    return {
      key: `automations:${eventName}`,
      event: eventName,
      handle: async (envelope, tx) => {
        await this.run(envelope, tx as WorkspaceScopedDb);
      },
    };
  }

  async run(envelope: Envelope, tx: WorkspaceScopedDb): Promise<RunOutcome[]> {
    // An automation that fires an event that fires an automation is how a
    // platform takes itself down. Three hops is generous.
    if (envelope.trace.depth > MAX_AUTOMATION_DEPTH) {
      return [{
        automationId: '-',
        status: 'skipped',
        reason: `automation depth ${envelope.trace.depth} exceeds ${MAX_AUTOMATION_DEPTH}`,
        steps: [],
      }];
    }

    const rows = await tx.execute<AutomationRow>(sql`
      select id, name, enabled, trigger_event, trigger_filter, conditions, actions,
             run_limit_per_hour
        from automations
       where workspace_id = ${envelope.workspaceId}
         and trigger_event = ${envelope.name}
         and enabled = true`);

    const outcomes: RunOutcome[] = [];
    for (const row of rows) outcomes.push(await this.runOne(row, envelope, tx));
    return outcomes;
  }

  private async runOne(
    row: AutomationRow,
    envelope: Envelope,
    tx: WorkspaceScopedDb,
  ): Promise<RunOutcome> {
    const scope: Record<string, unknown> = {
      ...envelope,
      subject: envelope.subject,
      data: envelope.data,
      workspaceId: envelope.workspaceId,
      projectId: envelope.projectId,
    };

    const ctx = { envelope, tx, scope, automationId: row.id };

    const [run] = await tx.execute<{ id: string }>(sql`
      insert into automation_runs (workspace_id, automation_id, event_id, status)
      values (${envelope.workspaceId}, ${row.id}, ${envelope.id}, 'running')
      returning id`);
    const runId = run!.id;

    const finish = async (
      status: RunOutcome['status'],
      steps: StepResult[],
      reason?: string,
    ): Promise<RunOutcome> => {
      await tx.execute(sql`
        update automation_runs
           set status = ${status}, finished_at = now(),
               steps = ${JSON.stringify(steps)}::jsonb, error = ${reason ?? null}
         where id = ${runId}`);
      if (status === 'success') {
        await tx.execute(sql`update automations set last_run_at = now() where id = ${row.id}`);
      }
      return { automationId: row.id, status, reason, steps };
    };

    try {
      const overLimit = await this.overRunLimit(row, tx);
      if (overLimit) return finish('skipped', [], overLimit);

      // The trigger filter is a cheap pre-check on the payload, so a rule about
      // critical issues does not open a transaction for every info-level one.
      for (const [path, expected] of Object.entries(row.trigger_filter ?? {})) {
        const actual = readPath(scope, path);
        const matches = Array.isArray(expected)
          ? expected.includes(actual as never)
          : actual === expected;
        if (!matches) {
          return finish('skipped', [], `filter ${path} did not match`);
        }
      }

      const definition = this.definitionOf(row);
      const verdict = await evaluateAll(definition.conditions, ctx);
      if (!verdict.pass) return finish('skipped', [], verdict.reason);

      const steps = await runActions(definition.actions, ctx, this.deps);
      const failed = steps.some((s) => s.status === 'failed');
      return finish(failed ? 'failed' : 'success', steps, failed ? 'one or more actions failed' : undefined);
    } catch (err) {
      return finish('failed', [], err instanceof Error ? err.message : String(err));
    }
  }

  private definitionOf(row: AutomationRow): Definition {
    return parseDefinition({
      trigger: { event: row.trigger_event, filter: row.trigger_filter ?? {} },
      conditions: row.conditions ?? [],
      actions: row.actions ?? [],
    });
  }

  private async overRunLimit(row: AutomationRow, tx: WorkspaceScopedDb): Promise<string | null> {
    const [count] = await tx.execute<{ n: number }>(sql`
      select count(*)::int as n from automation_runs
       where automation_id = ${row.id}
         and started_at > now() - interval '1 hour'
         and status <> 'skipped'`);
    const used = Number(count?.n ?? 0);
    return used >= row.run_limit_per_hour
      ? `rate limited — ${used} runs in the last hour (limit ${row.run_limit_per_hour})`
      : null;
  }
}
