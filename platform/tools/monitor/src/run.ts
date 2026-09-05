/**
 * Claiming due monitors and probing them.
 *
 * The sweep, not repeatable jobs. `66uptime` got this right and it is the only
 * design that survives scale: one BullMQ repeatable per monitor at 100,000
 * monitors is Redis-memory suicide, while a `next_check_at` column with
 * `for update skip locked` is one index scan however many there are.
 *
 * **The schedule moves on claim, not on success** — the same rule as every
 * other runner here. A probe that hangs must not be re-claimed by the next tick
 * and pile up; it misses one interval and carries on.
 *
 * **Probes are never retried.** A failed probe *is* the signal. Retrying one
 * turns a real thirty seconds of downtime into no downtime at all, which is
 * the quiet way an uptime product starts lying.
 */
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import type { Sender } from '@mamal/notify';
import { kindFor, type ProbeOutcome } from './kinds.ts';
import { recordRound, type MonitorRow, type RecordResult } from './incidents.ts';
import type { RegionResult } from './agreement.ts';

/** Runs one probe from one region. Injected — nothing here opens a socket. */
export type Prober = (input: {
  kind: string;
  target: string;
  config: Record<string, unknown>;
  timeoutSeconds: number;
  region: string;
}) => Promise<ProbeOutcome>;

export type DueMonitor = MonitorRow & {
  config: Record<string, unknown>;
  regions: string[];
  intervalSeconds: number;
  timeoutSeconds: number;
};

/**
 * Monitors due a check, claimed.
 *
 * `next_check_at` is pushed forward by the interval *from now* rather than from
 * its previous value: a monitor that fell behind — because the worker was down,
 * or the box was busy — should resume its cadence rather than fire a burst of
 * catch-up checks that all record the same instant.
 */
export async function claimDueMonitors(
  tx: WorkspaceScopedDb,
  opts: { limit?: number } = {},
): Promise<DueMonitor[]> {
  const rows = await tx.execute<{
    id: string; workspace_id: string; project_id: string; name: string; kind: string;
    target: string; config: Record<string, unknown>; regions: string[];
    interval_seconds: number; timeout_seconds: number; status: string;
    channel_ids: string[]; current_incident_id: string | null;
  }>(sql`
    with claimed as (
      select id from monitors
       where is_enabled and deleted_at is null
         and (next_check_at is null or next_check_at <= now())
       order by next_check_at nulls first
       limit ${opts.limit ?? 500}
       for update skip locked
    )
    update monitors m
       set next_check_at = now() + (m.interval_seconds * interval '1 second'),
           updated_at = now()
      from claimed
     where m.id = claimed.id
    returning m.id, m.workspace_id, m.project_id, m.name, m.kind, m.target, m.config,
              m.regions, m.interval_seconds, m.timeout_seconds, m.status,
              m.channel_ids, m.current_incident_id`);

  return rows.map((r) => ({
    id: r.id,
    workspaceId: r.workspace_id,
    projectId: r.project_id,
    name: r.name,
    kind: r.kind,
    target: r.target,
    config: r.config ?? {},
    regions: r.regions ?? [],
    intervalSeconds: r.interval_seconds,
    timeoutSeconds: r.timeout_seconds,
    status: r.status,
    channelIds: r.channel_ids ?? [],
    currentIncidentId: r.current_incident_id,
  }));
}

/**
 * Probes one monitor from each of its regions, then records the round.
 *
 * `allSettled`, so a region whose prober throws does not lose the other two —
 * exactly the reasoning as the AI visibility probes, and for the same reason:
 * the value is in the comparison.
 */
export async function checkMonitor(
  tx: WorkspaceScopedDb,
  monitor: DueMonitor,
  probe: Prober,
  send: Sender,
  opts: { now?: Date } = {},
): Promise<RecordResult> {
  const kind = kindFor(monitor.kind);
  const regions = monitor.regions.length > 0 ? monitor.regions : ['default'];

  const settled = await Promise.allSettled(
    regions.map(async (region): Promise<RegionResult> => {
      const started = Date.now();

      if (!kind) {
        return {
          region,
          ok: false,
          failureKind: 'unknown_kind',
          error: `${monitor.kind} is not something this instance can check.`,
        };
      }

      const outcome = await probe({
        kind: monitor.kind,
        target: monitor.target,
        config: monitor.config,
        timeoutSeconds: monitor.timeoutSeconds,
        region,
      });

      /*
       * The transport says what happened; the *kind* says whether that counts
       * as a failure. Whether a 301 or a missing keyword is a problem depends
       * on this monitor's own settings, so the judgement lives in `kinds.ts`
       * where it can be tested without a network.
       */
      const failure = kind.evaluate(outcome, monitor.config);

      return {
        region,
        ok: failure === null,
        responseMs: outcome.responseMs ?? Date.now() - started,
        failureKind: failure?.kind ?? null,
        error: failure?.message ?? null,
      };
    }),
  );

  const results: RegionResult[] = settled.map((outcome, index) =>
    outcome.status === 'fulfilled'
      ? outcome.value
      : {
          region: regions[index]!,
          ok: false,
          failureKind: 'probe_error',
          // Our probe broke, not necessarily their service — and `judge` needs
          // the other regions to agree before this becomes an incident.
          error: reasonOf(outcome.reason),
        },
  );

  return recordRound(tx, monitor, results, send, opts);
}

/* ------------------------------------------------------------ heartbeat */

/**
 * Records a heartbeat.
 *
 * Inverted from every other kind: the job calls us. A heartbeat that arrives
 * while an incident is open resolves it on the next sweep, which is why this
 * only touches `last_check_at` rather than deciding anything itself — one place
 * decides, and it is `recordRound`.
 */
export async function beat(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; monitorId: string; now?: Date },
): Promise<boolean> {
  const now = (opts.now ?? new Date()).toISOString();
  const rows = await tx.execute<{ id: string }>(sql`
    update monitors
       set last_check_at = ${now}::timestamptz,
           config = config || jsonb_build_object('lastBeatAt', ${now}),
           -- Checked promptly rather than on the usual interval, so a job that
           -- reports in gets its incident closed in seconds rather than minutes.
           next_check_at = least(next_check_at, now() + interval '30 seconds'),
           updated_at = now()
     where id = ${opts.monitorId} and workspace_id = ${opts.workspaceId} and kind = 'heartbeat'
    returning id`);
  return rows.length > 0;
}

/** Turns a stored heartbeat into the shape `kinds.ts` evaluates. */
export function heartbeatOutcome(
  config: Record<string, unknown>,
  now: Date,
): ProbeOutcome {
  const last = typeof config.lastBeatAt === 'string' ? Date.parse(config.lastBeatAt) : null;
  return {
    reachable: true,
    data: {
      secondsSinceLastBeat:
        // Never heard from at all is the same as "overdue", not "fine" — a job
        // that has never run is exactly what somebody wants to hear about.
        last === null ? Number.POSITIVE_INFINITY : (now.getTime() - last) / 1000,
    },
  };
}

/* -------------------------------------------------------------- writing */

export async function saveMonitor(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    id?: string;
    kind: string;
    name: string;
    target: string;
    config?: Record<string, unknown>;
    regions?: string[];
    intervalSeconds?: number;
    timeoutSeconds?: number;
    channelIds?: string[];
  },
): Promise<string> {
  const config = opts.config ?? {};
  const intervalSeconds = opts.intervalSeconds ?? 300;

  if (opts.id) {
    await tx.execute(sql`
      update monitors
         set name = ${opts.name}, target = ${opts.target},
             config = ${JSON.stringify(config)}::jsonb,
             regions = ${textArray(opts.regions ?? [])}::text[],
             interval_seconds = ${intervalSeconds},
             timeout_seconds = ${opts.timeoutSeconds ?? 15},
             -- Edited settings take effect now rather than at the end of the
             -- old interval, which is what somebody fixing a broken monitor
             -- expects.
             next_check_at = now(),
             updated_at = now()
       where id = ${opts.id} and project_id = ${opts.projectId}`);
    return opts.id;
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into monitors
      (workspace_id, project_id, kind, name, target, config, regions,
       interval_seconds, timeout_seconds, status, next_check_at)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.kind}, ${opts.name},
            ${opts.target}, ${JSON.stringify(config)}::jsonb,
            ${textArray(opts.regions ?? [])}::text[], ${intervalSeconds},
            ${opts.timeoutSeconds ?? 15}, 'pending', now())
    returning id`);

  return row!.id;
}

export async function setEnabled(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; monitorId: string; enabled: boolean },
): Promise<void> {
  await tx.execute(sql`
    update monitors
       set is_enabled = ${opts.enabled},
           status = ${opts.enabled ? 'pending' : 'paused'},
           next_check_at = ${opts.enabled ? sql`now()` : null},
           updated_at = now()
     where id = ${opts.monitorId} and project_id = ${opts.projectId}`);
}

function reasonOf(reason: unknown): string {
  return reason instanceof Error ? reason.message : String(reason);
}
