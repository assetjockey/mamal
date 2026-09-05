/**
 * Monitor's browser-safe half.
 *
 * The kind catalogue drives the create form and the monitor list; the
 * agreement and uptime maths drive every number on a status page. All of it is
 * pure, so it runs in the browser as well as on the prober — and none of it can
 * pull the database into a client bundle. See `scripts/check-client-imports.mjs`.
 */
export {
  MONITOR_KINDS,
  kindFor,
  isHeavy,
  validateMonitor,
  type MonitorKind,
  type ConfigField,
  type ProbeOutcome,
  type Failure,
  type ConfigProblem,
} from './kinds.ts';
export {
  judge,
  requiredAgreement,
  nextEscalation,
  uptime,
  formatUptime,
  ESCALATION_MINUTES,
  type RegionResult,
  type Verdict,
  type Uptime,
  type UptimeInput,
  type Window,
} from './agreement.ts';
