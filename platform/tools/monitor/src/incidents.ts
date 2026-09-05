/**
 * The incident lifecycle, against the database.
 *
 * `judge` decides whether a round of checks means "down"; this turns that into
 * an incident that opens once, escalates while nobody answers, and closes when
 * the thing comes back.
 *
 * Three rules run through it:
 *
 * **One open incident per monitor, enforced by a constraint.** Two schedulers
 * racing, or a slow notification path, would otherwise produce two incidents
 * for one outage — and then every count, every status page and every SLA number
 * doubles. `incidents_open_key` is a partial unique on `(monitor_id,
 * resolved_at)` with nulls not distinct, so the database settles it.
 *
 * **Maintenance suppresses, it does not hide.** Inside a declared window a
 * monitor going down is recorded and shown, but nobody is paged and the period
 * is excluded from uptime. Alerting through planned work is how people learn to
 * stop declaring it.
 *
 * **Recovery is as important as failure.** A resolved incident notifies on the
 * same channels with the same dedupe discipline; an alert that never gets an
 * "it's back" is why people keep dashboards open.
 */
import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { notify, type Sender } from '@mamal/notify';
import { judge, nextEscalation, type RegionResult, type Verdict } from './agreement.ts';

export type MonitorRow = {
  id: string;
  workspaceId: string;
  projectId: string;
  name: string;
  kind: string;
  target: string;
  status: string;
  channelIds: string[];
  currentIncidentId: string | null;
};

export type RecordResult = {
  verdict: Verdict;
  status: string;
  /** Set when this round opened one. */
  openedIncidentId: string | null;
  /** Set when this round closed one. */
  resolvedIncidentId: string | null;
  /** True when a window suppressed the alerting. */
  suppressed: boolean;
  notified: number;
};

/**
 * Records a round of checks and moves the incident state on.
 *
 * The sender is injected — see `@mamal/notify`. Everything here is decided by
 * `judge`, which is pure and tested without a database.
 */
export async function recordRound(
  tx: WorkspaceScopedDb,
  monitor: MonitorRow,
  results: RegionResult[],
  send: Sender,
  opts: { now?: Date } = {},
): Promise<RecordResult> {
  const now = opts.now ?? new Date();
  const verdict = judge(results);

  for (const result of results) {
    await tx.execute(sql`
      insert into monitor_checks
        (workspace_id, monitor_id, region, ok, response_ms, failure_kind, error, checked_at)
      values (${monitor.workspaceId}, ${monitor.id}, ${result.region}, ${result.ok},
              ${result.responseMs ?? null}, ${result.failureKind ?? null},
              ${result.error ?? null}, ${now.toISOString()}::timestamptz)`);
  }

  const suppressed = await inMaintenance(tx, monitor, now);

  /*
   * `degraded` is a real state and deliberately does not open an incident: one
   * region failing usually means somebody's routing is broken rather than the
   * service being down, and it is worth showing without waking anybody.
   */
  const status = suppressed ? 'maintenance' : verdict.status;

  await tx.execute(sql`
    update monitors
       set status = ${status},
           last_check_at = ${now.toISOString()}::timestamptz,
           checks_total = checks_total + ${results.length},
           checks_failed = checks_failed + ${results.filter((r) => !r.ok).length},
           avg_response_ms = ${averageResponse(results)},
           updated_at = now()
     where id = ${monitor.id}`);

  let openedIncidentId: string | null = null;
  let resolvedIncidentId: string | null = null;
  let notified = 0;

  if (verdict.status === 'down' && verdict.confirmed) {
    const opened = await openIncident(tx, monitor, verdict, now);
    openedIncidentId = opened.created ? opened.id : null;

    if (!suppressed) {
      notified += await escalate(tx, monitor, opened.id, send, now);
    }
  } else if (verdict.status === 'up' && monitor.currentIncidentId) {
    resolvedIncidentId = await resolveIncident(tx, monitor, now);
    if (resolvedIncidentId && !suppressed) {
      notified += await announceRecovery(tx, monitor, resolvedIncidentId, send, now);
    }
  }

  return { verdict, status, openedIncidentId, resolvedIncidentId, suppressed, notified };
}

/**
 * Opens an incident, or finds the one that is already open.
 *
 * `on conflict do nothing returning id` rather than a pre-check, for the reason
 * it is used everywhere else in this codebase: two workers both reading "no
 * open incident" and both inserting is the exact race, and a caught constraint
 * violation would abort the surrounding transaction.
 */
async function openIncident(
  tx: WorkspaceScopedDb,
  monitor: MonitorRow,
  verdict: Verdict,
  now: Date,
): Promise<{ id: string; created: boolean }> {
  const [inserted] = await tx.execute<{ id: string }>(sql`
    insert into incidents
      (workspace_id, monitor_id, cause, failure_kind, severity, started_at, failed_checks)
    values (${monitor.workspaceId}, ${monitor.id}, ${verdict.reason},
            ${verdict.failureKind}, ${severityFor(verdict)},
            ${now.toISOString()}::timestamptz, ${verdict.failed})
    on conflict on constraint incidents_open_key do nothing
    returning id`);

  if (inserted) {
    await tx.execute(sql`
      update monitors set current_incident_id = ${inserted.id}, updated_at = now()
       where id = ${monitor.id}`);
    return { id: inserted.id, created: true };
  }

  const [existing] = await tx.execute<{ id: string }>(sql`
    select id from incidents where monitor_id = ${monitor.id} and resolved_at is null`);

  // Still counting the failures, so the incident's record of how bad it was is
  // right even though it was opened by an earlier round.
  await tx.execute(sql`
    update incidents set failed_checks = failed_checks + ${verdict.failed}, updated_at = now()
     where id = ${existing!.id}`);

  return { id: existing!.id, created: false };
}

async function resolveIncident(
  tx: WorkspaceScopedDb,
  monitor: MonitorRow,
  now: Date,
): Promise<string | null> {
  const [resolved] = await tx.execute<{ id: string }>(sql`
    update incidents
       set resolved_at = ${now.toISOString()}::timestamptz,
           duration_seconds = extract(epoch from (${now.toISOString()}::timestamptz - started_at))::int,
           updated_at = now()
     where monitor_id = ${monitor.id} and resolved_at is null
    returning id`);

  if (!resolved) return null;

  await tx.execute(sql`
    update monitors set current_incident_id = null, updated_at = now() where id = ${monitor.id}`);
  return resolved.id;
}

/* ------------------------------------------------------------ notifying */

async function escalate(
  tx: WorkspaceScopedDb,
  monitor: MonitorRow,
  incidentId: string,
  send: Sender,
  now: Date,
): Promise<number> {
  const [incident] = await tx.execute<{
    started_at: string; acknowledged_at: string | null;
    escalation_level: number; last_notified_at: string | null; cause: string | null;
  }>(sql`
    select started_at, acknowledged_at, escalation_level, last_notified_at, cause
      from incidents where id = ${incidentId}`);
  if (!incident) return 0;

  const step = nextEscalation({
    startedAt: new Date(incident.started_at),
    acknowledgedAt: incident.acknowledged_at ? new Date(incident.acknowledged_at) : null,
    escalationLevel: incident.escalation_level,
    lastNotifiedAt: incident.last_notified_at ? new Date(incident.last_notified_at) : null,
    now,
  });

  if (!step.escalate) return 0;

  const channelIds = await channelsFor(tx, monitor);
  const result = await notify(
    tx,
    {
      workspaceId: monitor.workspaceId,
      channelIds,
      message: {
        templateKey: 'monitor.down',
        subject: `${monitor.name} is down`,
        body: `${incident.cause ?? 'It stopped responding.'} ${step.reason}`,
        // Urgency climbs with the ladder, so a transport that ranks by it
        // behaves the way somebody would expect at 3 a.m.
        urgency: step.level >= 3 ? 'urgent' : 'normal',
        data: { monitorId: monitor.id, incidentId, target: monitor.target },
      },
      // Level is in the key, so escalating alerts again and re-running the same
      // level does not.
      dedupeKey: `incident:${incidentId}:${step.level}`,
    },
    send,
  );

  await tx.execute(sql`
    update incidents
       set escalation_level = ${step.level},
           last_notified_at = ${now.toISOString()}::timestamptz,
           updated_at = now()
     where id = ${incidentId}`);

  return result.sent;
}

async function announceRecovery(
  tx: WorkspaceScopedDb,
  monitor: MonitorRow,
  incidentId: string,
  send: Sender,
  now: Date,
): Promise<number> {
  const [incident] = await tx.execute<{ duration_seconds: number | null }>(sql`
    select duration_seconds from incidents where id = ${incidentId}`);

  const channelIds = await channelsFor(tx, monitor);
  const result = await notify(
    tx,
    {
      workspaceId: monitor.workspaceId,
      channelIds,
      message: {
        templateKey: 'monitor.up',
        subject: `${monitor.name} is back`,
        body: `Down for ${humanDuration(incident?.duration_seconds ?? 0)}.`,
        urgency: 'normal',
        data: { monitorId: monitor.id, incidentId },
      },
      // An alert that never gets an "it's back" is why people keep dashboards
      // open all day.
      dedupeKey: `incident:${incidentId}:resolved`,
    },
    send,
  );

  void now;
  return result.sent;
}

/** A monitor's own channels, falling back to the project's defaults. */
async function channelsFor(tx: WorkspaceScopedDb, monitor: MonitorRow): Promise<string[]> {
  if (monitor.channelIds.length > 0) return monitor.channelIds;

  const rows = await tx.execute<{ id: string }>(sql`
    select id from notification_channels
     where project_id = ${monitor.projectId} and is_enabled`);
  return rows.map((r) => r.id);
}

/* ---------------------------------------------------------- maintenance */

/**
 * Whether this monitor is inside a declared window right now.
 *
 * Checked at alert time rather than at schedule time: a window declared *while*
 * an incident is open should stop the paging immediately, which is exactly what
 * somebody does when they realise they forgot to declare it.
 */
export async function inMaintenance(
  tx: WorkspaceScopedDb,
  monitor: { id: string; projectId: string },
  now: Date,
): Promise<boolean> {
  const [row] = await tx.execute<{ n: number }>(sql`
    select count(*)::int as n from maintenance_windows
     where project_id = ${monitor.projectId}
       and ${now.toISOString()}::timestamptz between starts_at and ends_at
       -- An empty monitor list means the whole project, which is what somebody
       -- declaring a deploy window almost always means.
       and (cardinality(monitor_ids) = 0 or ${monitor.id} = any(monitor_ids))`);
  return (row?.n ?? 0) > 0;
}

/* -------------------------------------------------------------- actions */

export async function acknowledge(
  tx: WorkspaceScopedDb,
  opts: { incidentId: string; userId: string },
): Promise<void> {
  await tx.execute(sql`
    update incidents
       set acknowledged_at = coalesce(acknowledged_at, now()),
           acknowledged_by = coalesce(acknowledged_by, ${opts.userId}),
           updated_at = now()
     where id = ${opts.incidentId} and resolved_at is null`);
}

export async function addUpdate(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    incidentId: string;
    status: 'investigating' | 'identified' | 'monitoring' | 'resolved';
    body: string;
    userId?: string;
  },
): Promise<void> {
  await tx.execute(sql`
    insert into incident_updates (workspace_id, incident_id, status, body, author_id)
    values (${opts.workspaceId}, ${opts.incidentId}, ${opts.status}, ${opts.body},
            ${opts.userId ?? null})`);

  // Posting an update is a decision to be public about it. Doing that silently
  // and requiring a second switch is how status pages end up empty during
  // outages.
  await tx.execute(sql`
    update incidents set is_public = true, updated_at = now() where id = ${opts.incidentId}`);
}

export async function listIncidents(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; open?: boolean; limit?: number },
): Promise<{
  id: string; monitorId: string; monitorName: string; cause: string | null;
  severity: string; startedAt: string; acknowledgedAt: string | null;
  resolvedAt: string | null; durationSeconds: number | null; escalationLevel: number;
  isPublic: boolean;
}[]> {
  const rows = await tx.execute<{
    id: string; monitor_id: string; monitor_name: string; cause: string | null;
    severity: string; started_at: string; acknowledged_at: string | null;
    resolved_at: string | null; duration_seconds: number | null;
    escalation_level: number; is_public: boolean;
  }>(sql`
    select i.id, i.monitor_id, m.name as monitor_name, i.cause, i.severity,
           i.started_at::text, i.acknowledged_at::text, i.resolved_at::text,
           i.duration_seconds, i.escalation_level, i.is_public
      from incidents i
      join monitors m on m.id = i.monitor_id
     where m.project_id = ${opts.projectId}
       ${opts.open ? sql`and i.resolved_at is null` : sql``}
     order by i.started_at desc
     limit ${opts.limit ?? 50}`);

  return rows.map((r) => ({
    id: r.id,
    monitorId: r.monitor_id,
    monitorName: r.monitor_name,
    cause: r.cause,
    severity: r.severity,
    startedAt: r.started_at,
    acknowledgedAt: r.acknowledged_at,
    resolvedAt: r.resolved_at,
    durationSeconds: r.duration_seconds,
    escalationLevel: r.escalation_level,
    isPublic: r.is_public,
  }));
}

/* --------------------------------------------------------------- shared */

/**
 * How bad it is.
 *
 * Every region failing is worse than a bare majority: the first is the service,
 * the second is often a partial network problem, and paging with the same
 * urgency for both is how urgency stops meaning anything.
 */
function severityFor(verdict: Verdict): string {
  // Every region failing is the service. A bare majority is often a partial
  // network problem, and paging identically for both is how urgency stops
  // meaning anything.
  return verdict.failed === verdict.reporting && verdict.reporting > 1 ? 'critical' : 'major';
}

function averageResponse(results: RegionResult[]): number | null {
  const times = results.map((r) => r.responseMs).filter((n): n is number => typeof n === 'number');
  return times.length === 0 ? null : Math.round(times.reduce((a, b) => a + b, 0) / times.length);
}

function humanDuration(seconds: number): string {
  if (seconds < 60) return `${Math.max(1, Math.round(seconds))} seconds`;
  if (seconds < 3600) return `${Math.round(seconds / 60)} minutes`;
  const hours = seconds / 3600;
  return `${hours.toFixed(1)} hours`;
}

