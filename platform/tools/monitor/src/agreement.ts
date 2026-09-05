/**
 * Deciding whether something is actually down.
 *
 * The single most valuable behaviour in an uptime product, and the one every
 * source gets wrong: **one probe failing is not an outage**. Networks are
 * lossy, a region has a bad minute, a CDN edge misbehaves — and a tool that
 * pages somebody at 3 a.m. for that gets muted within a fortnight, after which
 * it does not work at all.
 *
 * So an incident opens on *agreement*: M of the N regions that reported must
 * have failed. `phpuptime` does a "double check" from one location, which
 * catches a transient blip but not a regional one; checking from several places
 * and requiring consensus catches both.
 *
 * Everything here is pure. The probes themselves are injected — see
 * `tools/monitor/src/run.ts`.
 */

export type RegionResult = {
  region: string;
  ok: boolean;
  responseMs?: number | null;
  failureKind?: string | null;
  error?: string | null;
};

export type Verdict = {
  /** What the monitor's status should become. */
  status: 'up' | 'down' | 'degraded';
  /** True when this is enough to open or keep an incident. */
  confirmed: boolean;
  /** How many of the reporting regions failed, and how many reported. */
  failed: number;
  reporting: number;
  /** How many had to agree. */
  required: number;
  /** One sentence, naming the regions — "down" without "where" is not useful. */
  reason: string;
  /** The failure that best explains it, for the incident's `cause`. */
  failureKind: string | null;
  error: string | null;
};

/**
 * How many regions must agree before an outage is called.
 *
 * A majority of those *reporting*, never of those configured: if one of three
 * probe regions is itself down, waiting for it means no incident is ever opened
 * — which is the failure mode that quietly turns monitoring off.
 *
 * With one region there is no agreement to reach, and one failure is the
 * answer. That is not a weaker guarantee; it is an honest one, and the UI says
 * a single-region monitor is more prone to false positives.
 */
export function requiredAgreement(reporting: number): number {
  if (reporting <= 1) return 1;
  if (reporting === 2) return 2;
  return Math.floor(reporting / 2) + 1;
}

export function judge(results: RegionResult[]): Verdict {
  const reporting = results.length;

  if (reporting === 0) {
    return {
      status: 'degraded',
      // Nothing reported. Not "down" — we do not know, and saying "down"
      // because our own probes failed is how a monitoring outage becomes a
      // customer's incident.
      confirmed: false,
      failed: 0,
      reporting: 0,
      required: 0,
      reason: 'No region reported. This is our problem, not yours.',
      failureKind: null,
      error: null,
    };
  }

  const failures = results.filter((r) => !r.ok);
  const required = requiredAgreement(reporting);
  const confirmed = failures.length >= required;

  if (failures.length === 0) {
    return {
      status: 'up',
      confirmed: true,
      failed: 0,
      reporting,
      required,
      reason: `Reachable from ${listRegions(results)}.`,
      failureKind: null,
      error: null,
    };
  }

  /*
   * The most common failure wins, not the first. Three regions timing out and
   * one returning a 500 is a timeout, and labelling the incident by whichever
   * probe happened to finish first would mislabel most of them.
   */
  const kind = mostCommon(failures.map((f) => f.failureKind ?? 'unknown'));
  const example = failures.find((f) => (f.failureKind ?? 'unknown') === kind);

  if (!confirmed) {
    return {
      status: 'degraded',
      confirmed: false,
      failed: failures.length,
      reporting,
      required,
      // Degraded is a real state and worth showing: one region failing usually
      // means somebody's routing is broken, even when the service is fine.
      reason:
        `Failing from ${listRegions(failures)} but reachable from ` +
        `${listRegions(results.filter((r) => r.ok))}. ` +
        `${required} of ${reporting} regions have to agree before this is an incident.`,
      failureKind: kind === 'unknown' ? null : kind,
      error: example?.error ?? null,
    };
  }

  return {
    status: 'down',
    confirmed: true,
    failed: failures.length,
    reporting,
    required,
    reason:
      failures.length === reporting
        ? `Unreachable from every region (${listRegions(failures)}).`
        : `Unreachable from ${failures.length} of ${reporting} regions (${listRegions(failures)}).`,
    failureKind: kind === 'unknown' ? null : kind,
    error: example?.error ?? null,
  };
}

function listRegions(results: RegionResult[]): string {
  const names = results.map((r) => r.region).sort();
  if (names.length <= 2) return names.join(' and ');
  return `${names.slice(0, -1).join(', ')} and ${names[names.length - 1]}`;
}

function mostCommon(values: string[]): string {
  const counts = new Map<string, number>();
  for (const value of values) counts.set(value, (counts.get(value) ?? 0) + 1);
  return [...counts.entries()].sort((a, b) => b[1] - a[1])[0]![0];
}

/* ------------------------------------------------------------ escalation */

/**
 * The ladder. Level 0 fires immediately, then it climbs while nobody
 * acknowledges.
 *
 * Acknowledging stops the climb without resolving — the distinction matters:
 * somebody is on it, so stop waking people, but the incident is still open and
 * still on the status page.
 */
export const ESCALATION_MINUTES = [0, 5, 15, 60] as const;

export function nextEscalation(incident: {
  startedAt: Date;
  acknowledgedAt: Date | null;
  escalationLevel: number;
  lastNotifiedAt: Date | null;
  now?: Date;
}): { escalate: boolean; level: number; reason: string } {
  const now = incident.now ?? new Date();

  if (incident.acknowledgedAt) {
    return {
      escalate: false,
      level: incident.escalationLevel,
      reason: 'Acknowledged — somebody is on it.',
    };
  }

  const next = incident.escalationLevel;
  if (next >= ESCALATION_MINUTES.length) {
    return {
      escalate: false,
      level: incident.escalationLevel,
      // The top of the ladder. Continuing to page hourly forever trains people
      // to ignore the channel that matters.
      reason: 'Escalated as far as it goes.',
    };
  }

  const dueAfter = ESCALATION_MINUTES[next]! * 60_000;
  const elapsed = now.getTime() - incident.startedAt.getTime();

  if (elapsed < dueAfter) {
    return { escalate: false, level: incident.escalationLevel, reason: 'Not due yet.' };
  }

  return {
    escalate: true,
    level: next + 1,
    reason:
      next === 0
        ? 'Opened.'
        : `Unacknowledged for ${ESCALATION_MINUTES[next]} minutes.`,
  };
}

/* ---------------------------------------------------------------- uptime */

export type Window = { from: Date; to: Date };

export type UptimeInput = {
  window: Window;
  /** Every check in the window, in any order. */
  checks: { ok: boolean; checkedAt: Date }[];
  /** Periods excluded from the calculation. */
  maintenance?: Window[];
  /** When the monitor started existing — nothing before this counts. */
  createdAt?: Date;
};

export type Uptime = {
  /** 0–1, or null when the window holds nothing to measure. */
  ratio: number | null;
  total: number;
  failed: number;
  excluded: number;
  /** Why the answer is null, when it is. */
  note: string | null;
};

/**
 * Uptime over a window.
 *
 * Three things it refuses to do:
 *
 * **It does not count planned maintenance as downtime.** Otherwise every
 * declared deploy costs the customer their SLA number and they stop declaring
 * them — which is worse for everybody than the number being slightly kind.
 *
 * **It does not report 100% for a monitor that did not exist.** A monitor
 * created yesterday has no opinion about last month, and a status page showing
 * "100% over 90 days" under it is a lie of the most damaging kind.
 *
 * **It does not report 100% for a window with no checks.** No checks is not
 * perfect uptime; it is no information, and the two look identical on a chart
 * unless one of them is null.
 */
export function uptime(input: UptimeInput): Uptime {
  const excludedWindows = input.maintenance ?? [];

  const from =
    input.createdAt && input.createdAt > input.window.from
      ? input.createdAt
      : input.window.from;

  let total = 0;
  let failed = 0;
  let excluded = 0;

  for (const check of input.checks) {
    if (check.checkedAt < from || check.checkedAt > input.window.to) continue;

    if (excludedWindows.some((w) => check.checkedAt >= w.from && check.checkedAt <= w.to)) {
      excluded += 1;
      continue;
    }

    total += 1;
    if (!check.ok) failed += 1;
  }

  if (total === 0) {
    return {
      ratio: null,
      total: 0,
      failed: 0,
      excluded,
      note:
        excluded > 0
          ? 'Every check in this period was during planned maintenance.'
          : input.createdAt && input.createdAt > input.window.from
            ? 'This monitor did not exist for the whole period.'
            : 'No checks in this period.',
    };
  }

  return {
    ratio: (total - failed) / total,
    total,
    failed,
    excluded,
    note:
      input.createdAt && input.createdAt > input.window.from
        // Shown alongside the number rather than instead of it: the figure is
        // real, it just covers less time than the label implies.
        ? 'Measured from when this monitor was created.'
        : null,
  };
}

/** The customary rendering — three decimals, because 99.9 and 99.95 differ. */
export function formatUptime(ratio: number | null): string {
  if (ratio === null) return '—';
  return `${(ratio * 100).toFixed(ratio >= 0.9999 ? 2 : 3)}%`;
}
