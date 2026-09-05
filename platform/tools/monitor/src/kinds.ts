/**
 * The ten things a monitor can watch.
 *
 * `66uptime` models six of these as six tables, which is why it needs six admin
 * screens, six importers, six alerting paths and six copies of "is it down".
 * Here they are one table with a discriminated `kind`, and this file is the only
 * place that knows the difference between them.
 *
 * Two ideas make that work:
 *
 * **A kind declares its config fields**, so the create form, the validation and
 * the API documentation are generated rather than written three times.
 *
 * **A kind declares what counts as failure**, as a pure function over a probe
 * result. Whether a 301 is a failure depends on the monitor's own settings, not
 * on the transport — so the transport stays a thin fetch and the judgement
 * lives here where it can be tested without a network.
 */

export type ConfigField = {
  key: string;
  label: string;
  type: 'text' | 'number' | 'select' | 'boolean' | 'list';
  required?: boolean;
  options?: string[];
  placeholder?: string;
  help?: string;
};

export type ProbeOutcome = {
  reachable: boolean;
  statusCode?: number;
  responseMs?: number;
  body?: string;
  headers?: Record<string, string>;
  /** Kind-specific: resolved records, cert expiry, packet loss, metrics. */
  data?: Record<string, unknown>;
  error?: string;
};

export type Failure = { kind: string; message: string } | null;

export type MonitorKind = {
  key: string;
  label: string;
  /** What goes in `monitors.target`. */
  targetLabel: string;
  targetPlaceholder: string;
  description: string;
  fields: ConfigField[];
  /** Fastest interval that makes sense for this kind, in seconds. */
  minIntervalSeconds: number;
  /** True when the probe must run in a container rather than at the edge. */
  heavy: boolean;
  /** Null means healthy. */
  evaluate: (outcome: ProbeOutcome, config: Record<string, unknown>) => Failure;
};

const num = (value: unknown, fallback: number): number => {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
};

const list = (value: unknown): string[] =>
  Array.isArray(value) ? value.map(String) : typeof value === 'string' && value
    ? value.split(',').map((v) => v.trim()).filter(Boolean)
    : [];

/** Shared first question: did we reach it at all? */
const unreachable = (outcome: ProbeOutcome): Failure =>
  outcome.reachable
    ? null
    : { kind: 'unreachable', message: outcome.error ?? 'Could not be reached.' };

export const MONITOR_KINDS: Record<string, MonitorKind> = {
  http: {
    key: 'http',
    label: 'HTTP',
    targetLabel: 'URL',
    targetPlaceholder: 'https://example.com/health',
    description: 'A URL, with optional expectations about what comes back.',
    minIntervalSeconds: 30,
    heavy: false,
    fields: [
      { key: 'method', label: 'Method', type: 'select', options: ['GET', 'POST', 'HEAD', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] },
      { key: 'expectedStatus', label: 'Expected status codes', type: 'list', placeholder: '200, 204', help: 'Blank means any 2xx or 3xx.' },
      { key: 'keyword', label: 'Body must contain', type: 'text', help: 'A word that proves the page really rendered, not just that it returned 200.' },
      { key: 'keywordAbsent', label: 'Body must not contain', type: 'text', placeholder: 'Exception' },
      { key: 'headers', label: 'Request headers', type: 'text' },
      { key: 'body', label: 'Request body', type: 'text' },
      { key: 'followRedirects', label: 'Follow redirects', type: 'boolean' },
      { key: 'verifySsl', label: 'Verify the certificate', type: 'boolean' },
    ],
    evaluate(outcome, config) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const expected = list(config.expectedStatus).map(Number).filter(Number.isFinite);
      const code = outcome.statusCode ?? 0;

      if (expected.length > 0) {
        if (!expected.includes(code)) {
          return { kind: 'status', message: `Returned ${code}; expected ${expected.join(' or ')}.` };
        }
      } else if (code < 200 || code >= 400) {
        return { kind: 'status', message: `Returned ${code}.` };
      }

      /*
       * The keyword check is what separates "the server answered" from "the
       * site works". A 200 from a maintenance page or a framework error screen
       * is the most common false *negative* in uptime monitoring.
       */
      const keyword = typeof config.keyword === 'string' ? config.keyword.trim() : '';
      if (keyword && !(outcome.body ?? '').includes(keyword)) {
        return { kind: 'keyword_missing', message: `The page did not contain “${keyword}”.` };
      }

      const absent = typeof config.keywordAbsent === 'string' ? config.keywordAbsent.trim() : '';
      if (absent && (outcome.body ?? '').includes(absent)) {
        return { kind: 'keyword_present', message: `The page contained “${absent}”.` };
      }

      return null;
    },
  },

  ping: {
    key: 'ping',
    label: 'Ping',
    targetLabel: 'Host',
    targetPlaceholder: 'example.com',
    description: 'ICMP echo, with a tolerance for packet loss.',
    minIntervalSeconds: 30,
    heavy: false,
    fields: [
      { key: 'packets', label: 'Packets per check', type: 'number', placeholder: '4' },
      { key: 'maxLossPct', label: 'Acceptable loss', type: 'number', placeholder: '20', help: 'Per cent. Some loss is normal; total loss is not.' },
    ],
    evaluate(outcome, config) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const loss = num(outcome.data?.lossPct, 0);
      const allowed = num(config.maxLossPct, 20);
      // Not a binary: a link dropping 5% of packets is degraded, not down, and
      // treating every dropped packet as an outage is how ping monitors get
      // switched off.
      return loss > allowed
        ? { kind: 'packet_loss', message: `${loss}% packet loss, over the ${allowed}% you allow.` }
        : null;
    },
  },

  port: {
    key: 'port',
    label: 'Port',
    targetLabel: 'Host and port',
    targetPlaceholder: 'db.example.com:5432',
    description: 'A TCP connection opens, or it does not.',
    minIntervalSeconds: 30,
    heavy: false,
    fields: [],
    evaluate: (outcome) => unreachable(outcome),
  },

  dns: {
    key: 'dns',
    label: 'DNS',
    targetLabel: 'Hostname',
    targetPlaceholder: 'example.com',
    description: 'A record resolves, and to what you expect.',
    minIntervalSeconds: 60,
    heavy: false,
    fields: [
      { key: 'recordType', label: 'Record', type: 'select', options: ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'SOA', 'TXT', 'CAA'], required: true },
      { key: 'expected', label: 'Expected values', type: 'list', help: 'Blank means "any answer is fine" — the check is then that it resolves at all.' },
      { key: 'resolver', label: 'Resolver', type: 'text', placeholder: '1.1.1.1' },
    ],
    evaluate(outcome, config) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const records = list(outcome.data?.records);
      if (records.length === 0) {
        return { kind: 'no_records', message: 'The name did not resolve.' };
      }

      const expected = list(config.expected);
      if (expected.length === 0) return null;

      /*
       * Order-insensitive, and a superset is fine: round-robin DNS returns its
       * answers in a different order every time, and requiring an exact list
       * would fire on every check.
       */
      const missing = expected.filter((want) => !records.includes(want));
      return missing.length > 0
        ? {
            kind: 'record_changed',
            message: `Expected ${missing.join(', ')} but got ${records.join(', ')}.`,
          }
        : null;
    },
  },

  domain: {
    key: 'domain',
    label: 'Domain expiry',
    targetLabel: 'Domain',
    targetPlaceholder: 'example.com',
    description: 'WHOIS expiry, so a renewal is never a surprise.',
    minIntervalSeconds: 21_600,
    heavy: true,
    fields: [
      { key: 'warnDays', label: 'Warn this many days before', type: 'number', placeholder: '30' },
    ],
    evaluate(outcome, config) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const days = num(outcome.data?.daysRemaining, Number.POSITIVE_INFINITY);
      const warn = num(config.warnDays, 30);
      if (days <= 0) return { kind: 'expired', message: 'The registration has expired.' };
      return days <= warn
        ? { kind: 'expiring', message: `Expires in ${Math.round(days)} days.` }
        : null;
    },
  },

  ssl: {
    key: 'ssl',
    label: 'Certificate',
    targetLabel: 'Host',
    targetPlaceholder: 'example.com',
    description: 'Expiry, chain and hostname — the three ways a certificate fails.',
    minIntervalSeconds: 3600,
    heavy: false,
    fields: [
      { key: 'warnDays', label: 'Warn this many days before', type: 'number', placeholder: '14' },
      { key: 'port', label: 'Port', type: 'number', placeholder: '443' },
    ],
    evaluate(outcome, config) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const data = outcome.data ?? {};
      // Order matters: an expired certificate is also usually reported as an
      // invalid chain, and "expired" is the actionable half.
      if (data.hostnameMismatch === true) {
        return { kind: 'cert_hostname', message: 'The certificate is for a different hostname.' };
      }
      const days = num(data.daysRemaining, Number.POSITIVE_INFINITY);
      if (days <= 0) return { kind: 'cert_expired', message: 'The certificate has expired.' };
      if (data.chainValid === false) {
        return { kind: 'cert_chain', message: 'The certificate chain does not validate.' };
      }

      const warn = num(config.warnDays, 14);
      return days <= warn
        ? { kind: 'cert_expiring', message: `Expires in ${Math.round(days)} days.` }
        : null;
    },
  },

  heartbeat: {
    key: 'heartbeat',
    label: 'Heartbeat',
    targetLabel: 'Job name',
    targetPlaceholder: 'nightly-backup',
    description: 'Something that should call us. Silence is the failure.',
    minIntervalSeconds: 60,
    heavy: false,
    fields: [
      { key: 'expectedEverySeconds', label: 'Expected every', type: 'number', required: true, placeholder: '86400', help: 'Seconds.' },
      { key: 'graceSeconds', label: 'Grace period', type: 'number', placeholder: '900', help: 'A job that usually finishes at 03:00 will sometimes finish at 03:10.' },
    ],
    evaluate(outcome, config) {
      /*
       * Inverted: nobody probes a heartbeat, the job calls us. `reachable` here
       * means "we heard from it recently", computed by the caller from
       * `lastSeenAt` — so the evaluation stays uniform with every other kind.
       */
      const since = num(outcome.data?.secondsSinceLastBeat, 0);
      const expected = num(config.expectedEverySeconds, 86_400);
      const grace = num(config.graceSeconds, 900);

      return since > expected + grace
        ? {
            kind: 'missed_heartbeat',
            message: `Nothing heard for ${Math.round(since / 60)} minutes; expected every ${Math.round(expected / 60)}.`,
          }
        : null;
    },
  },

  server: {
    key: 'server',
    label: 'Server',
    targetLabel: 'Agent',
    targetPlaceholder: 'web-01',
    description: 'CPU, memory and disk, reported by an agent you install.',
    minIntervalSeconds: 60,
    heavy: false,
    fields: [
      { key: 'maxCpuPct', label: 'CPU above', type: 'number', placeholder: '90' },
      { key: 'maxMemoryPct', label: 'Memory above', type: 'number', placeholder: '90' },
      { key: 'maxDiskPct', label: 'Disk above', type: 'number', placeholder: '90' },
      { key: 'silentAfterSeconds', label: 'Agent silent for', type: 'number', placeholder: '300' },
    ],
    evaluate(outcome, config) {
      const data = outcome.data ?? {};

      // An agent that stopped reporting is the most important case: a machine
      // that is off reports nothing at all, and a threshold check would call
      // that healthy.
      const silence = num(data.secondsSinceReport, 0);
      const silentAfter = num(config.silentAfterSeconds, 300);
      if (silence > silentAfter) {
        return {
          kind: 'agent_silent',
          message: `The agent has not reported for ${Math.round(silence / 60)} minutes.`,
        };
      }

      for (const [metric, limitKey, label] of [
        ['cpuPct', 'maxCpuPct', 'CPU'],
        ['memoryPct', 'maxMemoryPct', 'Memory'],
        ['diskPct', 'maxDiskPct', 'Disk'],
      ] as const) {
        const value = num(data[metric], 0);
        const limit = num(config[limitKey], 90);
        if (value > limit) {
          return { kind: 'threshold', message: `${label} at ${Math.round(value)}%, over ${limit}%.` };
        }
      }

      return null;
    },
  },

  game: {
    key: 'game',
    label: 'Game server',
    targetLabel: 'Host and query port',
    targetPlaceholder: 'mc.example.com:25565',
    description: 'Reachable, and how many players are on.',
    minIntervalSeconds: 60,
    heavy: true,
    fields: [
      { key: 'protocol', label: 'Protocol', type: 'select', options: ['minecraft', 'source', 'goldsource'], required: true },
    ],
    evaluate: (outcome) => unreachable(outcome),
  },

  browser: {
    key: 'browser',
    label: 'Browser flow',
    targetLabel: 'Starting URL',
    targetPlaceholder: 'https://example.com/login',
    description: 'A real browser walking a real journey — sign in, search, check out.',
    minIntervalSeconds: 300,
    heavy: true,
    fields: [
      { key: 'steps', label: 'Steps', type: 'list', required: true, help: 'goto, click, type, assert text, assert selector, screenshot.' },
    ],
    evaluate(outcome) {
      const missed = unreachable(outcome);
      if (missed) return missed;

      const failedStep = outcome.data?.failedStep;
      return failedStep
        ? {
            kind: 'step_failed',
            // Naming the step is the whole value: "the checkout flow is broken"
            // is unactionable, "step 4, click #submit, timed out" is a ticket.
            message: `Step ${failedStep} failed: ${outcome.data?.stepError ?? 'no detail'}.`,
          }
        : null;
    },
  },
};

export function kindFor(key: string): MonitorKind | null {
  return MONITOR_KINDS[key] ?? null;
}

/** Kinds that need a container rather than an edge worker. */
export function isHeavy(key: string): boolean {
  return MONITOR_KINDS[key]?.heavy ?? false;
}

/* ---------------------------------------------------------- validation */

export type ConfigProblem = { field: string; message: string };

/**
 * Checks a monitor before it is saved.
 *
 * A monitor that can never succeed is worse than no monitor: it pages somebody
 * on its first check and they learn the tool is noisy.
 */
export function validateMonitor(input: {
  kind: string;
  target: string;
  intervalSeconds: number;
  config: Record<string, unknown>;
}): ConfigProblem[] {
  const kind = kindFor(input.kind);
  if (!kind) return [{ field: 'kind', message: `${input.kind} is not something we can watch.` }];

  const problems: ConfigProblem[] = [];

  if (!input.target.trim()) {
    problems.push({ field: 'target', message: `${kind.targetLabel} is required.` });
  }

  if (input.kind === 'http' && !/^https?:\/\//i.test(input.target.trim())) {
    problems.push({ field: 'target', message: 'An HTTP monitor needs a full URL, including https://.' });
  }

  if (input.kind === 'port' && !/:\d+$/.test(input.target.trim())) {
    problems.push({ field: 'target', message: 'A port monitor needs a port — for example db.example.com:5432.' });
  }

  if (input.intervalSeconds < kind.minIntervalSeconds) {
    problems.push({
      field: 'intervalSeconds',
      // Named per kind: a WHOIS lookup every 30 seconds gets us rate limited by
      // the registry and tells the customer nothing new.
      message: `${kind.label} monitors check at most every ${kind.minIntervalSeconds} seconds.`,
    });
  }

  for (const field of kind.fields) {
    if (!field.required) continue;
    const value = input.config[field.key];
    if (value === undefined || value === null || value === '') {
      problems.push({ field: field.key, message: `${field.label} is required.` });
    }
  }

  return problems;
}
