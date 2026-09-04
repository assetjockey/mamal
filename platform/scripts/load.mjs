#!/usr/bin/env node
/**
 * Load harness.
 *
 * Not k6: this has to run in CI and on a laptop with no extra binary, and what
 * it measures — latency percentiles under concurrency against the real stack —
 * needs no more than fetch and a clock. When the edge tiers land in later
 * phases (10k redirects/s is not something Node can generate), this gets
 * replaced for those paths rather than stretched to cover them.
 *
 * Budgets are asserted, not printed. A performance test nobody fails is a
 * performance test nobody reads.
 *
 *   node scripts/load.mjs --key mk_… [--base http://localhost:3000]
 */

const args = Object.fromEntries(
  process.argv.slice(2).flatMap((a, i, all) =>
    a.startsWith('--') ? [[a.slice(2), all[i + 1]?.startsWith('--') ? true : all[i + 1]]] : [],
  ),
);

const BASE = args.base ?? process.env.LOAD_BASE_URL ?? 'http://localhost:3000';
const KEY = args.key ?? process.env.LOAD_API_KEY;
if (!KEY) {
  console.error('need --key mk_… (mint one: pnpm --filter @mamal/auth mint-key <email> "*")');
  process.exit(2);
}

/**
 * Budgets, in milliseconds, measured against a PRODUCTION build.
 *
 * Set from observed behaviour plus roughly 1.6x headroom, not from aspiration —
 * a budget nothing has ever met is indistinguishable from no budget.
 *
 * Run this against `next start`, never `next dev`. Dev mode adds a ~120ms floor
 * to every route for module resolution, which swamps the thing being measured:
 * the same endpoints read p50 126ms on dev and 56ms on a production build.
 *
 * The ceiling is the Postgres pool (`max: 10` per instance), not CPU. Measured
 * on this box: concurrency 50 sits inside budget, 100 degrades, 200 degrades
 * further — all with **zero errors**, because a saturated pool queues rather
 * than refusing. That is the correct failure mode, and it means the scaling
 * lever is instances or pool size, not code.
 */
const BUDGETS = {
  // 260, not 180. The first budget here came from one favourable run (p95 98ms)
  // and the endpoint's real spread on this box is 83-201ms — four statements
  // including the RLS `set local`, none of them slow. A budget sitting inside
  // the noise band flaps, and a flapping budget is one people re-run until it
  // passes. Set it above what has actually been observed.
  'GET /api/v1/me': { p95: 260, p99: 380 },
  'GET /api/v1/audit/sites': { p95: 280, p99: 400 },
  'GET /api/v1/audit/issues?limit=50': { p95: 260, p99: 400 },
  'POST /api/mcp tools/list': { p95: 180, p99: 300 },
  /*
   * The widget payload is the hottest path in the platform: every visitor to
   * every customer site fetches it. It is edge-cached for a minute, so in
   * production the origin sees one request per campaign per minute regardless
   * of traffic — but the origin still has to be fast enough that a cache miss
   * or a purge does not stall the world.
   */
  'GET /c/{key}.json (widget payload)': { p95: 280, p99: 400 },

  /*
   * The redirect. Hotter than the widget payload and, unlike it, uncacheable:
   * the destination is editable by definition and rules are evaluated per
   * visitor, so every click is an origin request.
   *
   * **This budget is not the plan's p99 < 25 ms.** That figure describes the
   * Cloudflare Workers tier with the slug in KV — deliberately deferred under
   * Risk 2 (phase the infrastructure, not the interfaces). A Node process plus
   * two Postgres round trips cannot approach it, and quoting 25 ms here would
   * be a budget nothing has ever met, which is the same as no budget. What is
   * measured is the origin's own number, honestly, so the gap is visible rather
   * than assumed closed. `resolve()` stays a pure function over plain data so
   * the logic moves to the worker unchanged when that tier lands.
   */
  'GET /r/{alias} (redirect)': { p95: 240, p99: 360 },
  'GET /r/{alias} with rules': { p95: 260, p99: 400 },
};

/** 50 is inside the pool's capacity; above it the numbers measure queueing. */
const CONCURRENCY = Number(args.concurrency ?? 50);
const REQUESTS = Number(args.requests ?? 400);

/**
 * Rounds, asserted on the MEDIAN.
 *
 * A single round on a shared machine swings badly — the same endpoint measured
 * p95 98ms and 197ms minutes apart, with nothing changed but what else the box
 * was doing. Asserting on one sample makes the gate flap, and a flapping perf
 * gate is one everybody learns to re-run until it passes. The median of three
 * absorbs a noisy neighbour while still moving if the code genuinely regresses.
 */
const ROUNDS = Number(args.rounds ?? 3);

const headers = { authorization: `Bearer ${KEY}`, 'content-type': 'application/json' };

const SCENARIOS = [
  { name: 'GET /api/v1/me', run: () => fetch(`${BASE}/api/v1/me`, { headers }) },
  { name: 'GET /api/v1/audit/sites', run: () => fetch(`${BASE}/api/v1/audit/sites`, { headers }) },
  {
    name: 'GET /api/v1/audit/issues?limit=50',
    run: () => fetch(`${BASE}/api/v1/audit/issues?limit=50`, { headers }),
  },
  {
    name: 'GET /c/{key}.json (widget payload)',
    // Unauthenticated by design — it is fetched by a script tag.
    run: () => fetch(`${BASE}/c/${args.pixel ?? 'ck_missing'}.json`),
  },
  {
    name: 'POST /api/mcp tools/list',
    run: () =>
      fetch(`${BASE}/api/mcp`, {
        method: 'POST',
        headers,
        body: JSON.stringify({ jsonrpc: '2.0', id: 1, method: 'tools/list' }),
      }),
  },
  /*
   * `redirect: 'manual'` — following the 302 would measure example.com.
   * Two aliases: one plain, one carrying targeting rules, because the rule
   * path does a second query and evaluates the matcher per request.
   */
  {
    name: 'GET /r/{alias} (redirect)',
    ok: (r) => r.status === 302,
    run: () =>
      fetch(`${BASE}/r/${args.alias ?? 'load-missing'}`, { redirect: 'manual' }),
  },
  {
    name: 'GET /r/{alias} with rules',
    ok: (r) => r.status === 302,
    run: () =>
      fetch(`${BASE}/r/${args.rulesAlias ?? args.alias ?? 'load-missing'}`, {
        redirect: 'manual',
        headers: {
          'user-agent':
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 ' +
            '(KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
        },
      }),
  },
];

function percentile(sorted, p) {
  if (sorted.length === 0) return 0;
  // Nearest-rank. With a few hundred samples the interpolated variants differ
  // by less than the noise floor, and this one cannot report a latency that
  // never happened.
  const rank = Math.ceil((p / 100) * sorted.length);
  return sorted[Math.min(sorted.length - 1, Math.max(0, rank - 1))];
}

/**
 * What counts as a successful response.
 *
 * Not `r.ok`. The redirect's *correct* answer is a 302, and treating every
 * non-2xx as an error reported a perfectly healthy path at 43ms p95 as 1200
 * failures. A scenario says what it expects; the default stays 2xx.
 */
const DEFAULT_OK = (r) => r.ok;

async function measure({ name, run, ok = DEFAULT_OK }) {
  // Warm the route: the first request through a Next dev route compiles it, and
  // reporting that as p99 would be measuring the bundler.
  for (let i = 0; i < 5; i++) await run();

  const timings = [];
  let failures = 0;
  let inFlight = 0;
  let started = 0;

  await new Promise((resolve) => {
    const pump = () => {
      while (inFlight < CONCURRENCY && started < REQUESTS) {
        started++;
        inFlight++;
        const t0 = performance.now();
        run()
          .then((r) => {
            if (!ok(r)) failures++;
            return r.arrayBuffer(); // drain, or the socket stays busy
          })
          .catch(() => failures++)
          .finally(() => {
            timings.push(performance.now() - t0);
            inFlight--;
            if (timings.length === REQUESTS) resolve();
            else pump();
          });
      }
    };
    pump();
  });

  const sorted = timings.slice().sort((a, b) => a - b);
  const wall = timings.reduce((a, b) => a + b, 0);
  return {
    name,
    n: timings.length,
    failures,
    p50: percentile(sorted, 50),
    p95: percentile(sorted, 95),
    p99: percentile(sorted, 99),
    max: sorted[sorted.length - 1] ?? 0,
    rps: (REQUESTS / (wall / CONCURRENCY)) * 1000,
  };
}

const ms = (v) => `${v.toFixed(0)}ms`.padStart(7);

console.log(
  `load: ${ROUNDS} rounds x ${REQUESTS} requests, concurrency ${CONCURRENCY}, against ${BASE}\n` +
    `      p50/p95/p99 are the MEDIAN across rounds; max is the worst seen.\n`,
);
console.log('scenario                             n   fail     p50     p95     p99     max   budget');
console.log('─'.repeat(92));

const median = (xs) => {
  const s = xs.slice().sort((a, b) => a - b);
  return s[Math.floor(s.length / 2)] ?? 0;
};

let failed = 0;
for (const scenario of SCENARIOS) {
  const rounds = [];
  for (let i = 0; i < ROUNDS; i++) rounds.push(await measure(scenario));

  const r = {
    name: scenario.name,
    n: rounds.reduce((a, b) => a + b.n, 0),
    failures: rounds.reduce((a, b) => a + b.failures, 0),
    p50: median(rounds.map((x) => x.p50)),
    p95: median(rounds.map((x) => x.p95)),
    p99: median(rounds.map((x) => x.p99)),
    max: Math.max(...rounds.map((x) => x.max)),
  };
  const budget = BUDGETS[r.name];
  const over = budget && (r.p95 > budget.p95 || r.p99 > budget.p99);
  if (over || r.failures > 0) failed++;

  console.log(
    `${r.name.padEnd(34)}${String(r.n).padStart(5)}${String(r.failures).padStart(6)}` +
      `${ms(r.p50)}${ms(r.p95)}${ms(r.p99)}${ms(r.max)}` +
      `   ${budget ? `p95<${budget.p95}` : '—'}` +
      `${over ? '  ✗ OVER' : r.failures ? '  ✗ ERRORS' : '  ✓'}`,
  );
}

console.log();
console.log(
  'Note: the ceiling here is the Postgres pool (max 10/instance). Past ~50 concurrent\n' +
  '      requests these numbers measure queueing, not the handlers — and no request\n' +
  '      errors, it only waits. Scale with instances or pool size, not with code.',
);
console.log();
if (failed > 0) {
  console.error(`${failed} scenario(s) missed budget or returned errors.`);
  process.exit(1);
}
console.log('all scenarios within budget.');
