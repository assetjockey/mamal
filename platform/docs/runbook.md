# Runbook

What to do when something is wrong, written for whoever is on call at 3am and
has not read the codebase. Every command here has been run against a real
system, not written from memory.

---

## Health checks, in the order worth trying

```bash
curl -s localhost:3000/api/mcp | head -c 80          # app is serving
curl -s -H "Authorization: Bearer $KEY" localhost:3000/api/v1/me   # auth + DB
psql "$DATABASE_URL" -tAc 'select 1'                  # database reachable
redis-cli ping                                        # queue backing store
```

If `/api/mcp` answers and `/api/v1/me` does not, the app is up and the database
is not — go to **Database**. If both answer and users report slowness, go to
**The API is slow**.

---

## Database

```bash
psql "$DATABASE_URL" -tAc 'select 1'                      # reachable at all
psql "$DATABASE_URL" -tAc 'select count(*) from pg_stat_activity'   # connections used
psql "$DATABASE_URL" -tAc 'show max_connections'
```

**Connections exhausted** (`FATAL: sorry, too many clients already`): every app
instance holds up to 10. Count instances x 10 and compare with
`max_connections`. Kill idle-in-transaction sessions before restarting anything
— they are usually the real cause:

```sql
select pid, state, now() - state_change as idle_for, left(query, 80)
  from pg_stat_activity
 where state = 'idle in transaction' order by idle_for desc;

select pg_terminate_backend(<pid>);
```

**RLS refuses a query you expect to work.** That is the design: every tenant
table is gated on `app.current_workspace_id`, and the only sanctioned handles
are `withWorkspace()` and `asPlatformAdmin()`. A query run manually in `psql`
sees nothing until you set it:

```sql
set local app.current_workspace_id = '<workspace-uuid>';
```

Never "fix" an RLS refusal in application code by reaching for
`unsafeUnscopedDb` — there is exactly one audited caller of it, and eslint
fails the build on a second.

---

## The API is slow

**Check first: is it queueing or is it broken?**

```bash
pnpm load --key "$KEY" --base https://<host>
```

Errors are a fault. Latency without errors is saturation, and the ceiling is
almost always the Postgres pool.

**The pool is `max: 10` per app instance** (`packages/db/src/client.ts`).
Measured on a dev box against a production build:

| Concurrency | p50 | p95 | Errors |
|---|---|---|---|
| 50 | 60–105ms | 80–175ms | 0 |
| 100 | 180–220ms | 255–490ms | 0 |
| 200 | 380–475ms | 510–740ms | 0 |

It degrades by *waiting*, never by refusing — so the symptom of overload is a
slow site, not a broken one. The lever is more instances (preferred) or a larger
pool (only if Postgres has `max_connections` headroom: check with
`show max_connections`, and remember every instance multiplies).

**Do not** raise the pool to "fix" latency without checking `max_connections`.
Exhausting the server turns a slow API into a down one.

**Find the actual slow query.** What is running *right now* — always available:

```sql
select pid, now() - query_start as runtime, state, left(query, 120) as query
  from pg_stat_activity
 where state = 'active' and query not ilike '%pg_stat_activity%'
 order by runtime desc;
```

Historical averages need the `pg_stat_statements` extension, which is **not**
installed by default — `create extension pg_stat_statements;` plus
`shared_preload_libraries` and a restart. Do that before you need it, not during
an incident:

```sql
select query, calls, mean_exec_time, max_exec_time
  from pg_stat_statements order by mean_exec_time desc limit 10;
```

> Known trap, already fixed: `last_used_at` on `api_keys` used to be written on
> every request. One row, every request, under concurrency — the *fastest*
> routes had the worst tail (p50 134ms, p95 686ms on a single-SELECT endpoint).
> It is now throttled to once a minute per key. If a similar pattern appears,
> look for a hot single row before looking at the handler.

---

## Audits are stuck

Crawls run on `audit.crawl` in 25-page slices; each job re-enqueues the next.

**Is it stuck, or just large?**

```sql
select id, status, phase, pages_crawled, pages_total,
       now() - started_at as age
  from audits where status in ('queued','running') order by started_at;
```

A `pages_crawled` that is *moving* is a big crawl, not a stuck one. A slice
number that has not changed in minutes is stuck.

**A killed worker is safe.** The frontier and visited set live in
`audits.crawl_cursor`, committed at the end of every slice, so a new worker
resumes from the last committed page rather than restarting. At most one slice
of work is lost. There is a test for exactly this
(`tools/audit` → "surviving a kill"). Restart the worker and it picks up.

**To cancel one:**

```sql
update audits set status = 'cancelled' where id = '<id>' and status in ('queued','running');
```

The next slice sees it and finalises what it has — pages already crawled stay
scored. Nothing is lost, and nothing needs cleaning up.

**Guard rail:** a crawl that somehow never advances stops itself at
`MAX_SLICES` (2000) with `error_code = 'slice_limit'` rather than looping.

---

## Retention did not run

```bash
pnpm --filter @mamal/worker-core retention --dry-run   # what WOULD go
pnpm --filter @mamal/worker-core retention             # do it
```

Nightly, idempotent, holds no state. A missed night is caught up by the next
run, so the failure mode is "data lives a day longer" — never re-run it in a
panic, and never run it with a hand-edited cutoff.

Exit code is non-zero if any workspace errored; per-workspace failures are
isolated, so one bad workspace does not stop the rest.

**Safety property worth knowing:** if a plan resolves retention below one day,
the sweep *refuses* rather than treating "now" as the cutoff. A resolver bug
cannot delete the platform's data. It reports
`refusing to sweep: resolved retention of N days is below the 1-day floor`.

What is deliberately kept regardless of age: `audit_snapshots` (the score trend,
which has no page content in it) and each site's most recent completed run — a
workspace returning after a year should see its score, not an empty dashboard
that looks like data loss.

---

## Widgets are not appearing on a customer's site

In order of likelihood:

```bash
curl -s "https://<host>/c/<pixelKey>.json" | head -c 400   # is there a config?
curl -sI "https://<host>/confirm.js" | head -3             # is the script served?
```

- **404 on the config** → the campaign is disabled, or the key is wrong. Both
  answer 404 deliberately, so keys cannot be enumerated; check the campaign in
  the app rather than guessing from the response.
- **Config returns but `widgets` is empty** → every widget was filtered out.
  Most often a **minimum threshold**: "show nothing below 3 recent sales" is
  enforced *server-side* on purpose, so a quiet week means no widget. Check
  `minimumCount` against the conversions in the window.
- **`conversions` is empty but widgets are present** → expected when no widget
  declares `needs: ['conversions']`. A cookie notice is never sent the feed.
- **Everything looks right but nothing renders** → frequency capping. The
  runtime stores per-widget state in `localStorage`; clear site data on the test
  browser, or set the widget to "every page view" while checking.

Widgets serialise **one per position at a time**. Two widgets both at
bottom-left is not a bug — the second waits for the first.

---

## A push campaign sent to fewer people than expected

```sql
select status, count(*) from push_subscribers
 where push_website_id = '<id>' group by status;
```

`expired` rows are browsers that were uninstalled or had permission revoked; a
404 or 410 from the push service retires them on send. They are excluded from
every audience deliberately — counting them would make the delivery rate
meaningless. That is usually the whole explanation.

If the number is still short, the segment is the next place to look. Segments
**fail closed**: a rule naming a field this build does not know matches nobody.
That is the right direction for a send, but it means a typo in a field name
looks like "no audience".

```sql
select name, filter from push_segments where push_website_id = '<id>';
```

Retention never deletes subscribers by age — a subscription is standing consent,
and removing it would silently stop someone's notifications while their browser
still believes it is subscribed.

---

## Uploads or downloads are failing

Storage is a **row**, not a deploy — check which backend a workspace resolves to
before anything else:

```sql
select id, name, handler, workspace_id, is_enabled, is_default, config
  from storage_providers order by (workspace_id is not null) desc, is_default desc;
```

A workspace-scoped provider wins over the instance default. No row at all is
`no_provider`, and the message says where to add one.

- **`unreadable_credentials`** → `STORAGE_KEK` has changed. Provider credentials
  are envelope-encrypted with it, and rotating it without rewrapping makes every
  provider unreadable at once. Restore the previous key, rewrap, then rotate.
- **403 from `/api/storage`** → the signature did not verify. It covers the
  method, the object key *and* the expiry, so a URL edited to point at another
  object or to live longer fails here by design. A genuinely expired URL answers
  **410** and the client should ask for a fresh one rather than retry.
- **`local` handler and nothing on disk** → check `STORAGE_LOCAL_ROOT` is the
  same path the provider row's `config.root` names, and that the process can
  write to it. The two are separate on purpose (env for the process, config for
  the row) and they drift.

Bytes never pass through the app on any backend: `partUrl` and `readUrl` hand
out pre-authorised URLs and the browser talks to storage directly. If you see a
5 GB request in the app's access log, something is calling the wrong path.

---

## A custom domain will not verify

```bash
pnpm --filter @mamal/worker-core domains     # run the sweep now
```

```sql
select host, dns_status, verified_at, ssl_status, dns_checked_at, last_check
  from custom_domains where verified_at is null;
```

`last_check` holds what the resolver actually saw, which is the whole point of
storing it — "pending" alone is unactionable:

- **`owned: false, routed: true`** → they pointed the domain at us but the TXT
  token is missing. This must never verify: a CNAME is something anyone can add,
  and the token is the only proof of control.
- **`owned: true, routed: false`** → proved, not pointed. Usually propagation;
  `found.cname` shows where it currently points.
- **both false** → nothing has landed yet. Normal for the first ninety seconds,
  and some providers take an hour.

**A verified domain is never unverified by the sweep** — it is not even claimed.
A resolver hiccup taking a customer's links down is far worse than serving a
hostname somebody stopped pointing at us, which is a 404 from us rather than an
outage. Removing one is a manual action.

`ssl_status` goes to `provisioning`, not `active`, the moment DNS verifies: the
certificate is issued afterwards by the edge provider. A green "Live" badge next
to a domain still serving a TLS warning is the failure customers read as "this
product is broken", so the UI shows *Issuing certificate* in between.

---

## A bulk import did nothing

By design, if anything about it did not fit:

- **`limit_reached`** → the allowance is checked **once for the whole batch**,
  not per row. Importing the first N and refusing the rest leaves a partial
  import the customer cannot reconcile, so it is all or nothing. The message
  names the shortfall — "this would add 10,000 to the 26 you have… room for
  9,974 more" — rather than the count alone.
- **Problems listed but nothing created** → every row had a problem. The
  reported line numbers are 1-based *including the header*, so line 2 is the
  first data row.
- **"No destination column"** → the header is not recognised. `url`,
  `destination`, `link`, `destination url` and `target` all work; anything else
  needs renaming. A UTF-8 BOM is stripped, so an Excel export is fine.

Nothing is written on a `dryRun`, and the check the UI runs is the same code
path that does the writing — so a clean check cannot be followed by a surprise.

---

## A short link is going to the wrong place

Work down the resolver's own order — it is the same order `resolve()` evaluates,
so the first thing that matches is the answer:

```sql
select id, alias, kind, is_enabled, moderation_status, destination_url,
       expires_at, max_clicks, clicks_count, password_hash is not null as gated,
       settings
  from links where alias = '<alias>' and deleted_at is null;

select id, priority, is_enabled, match, action, sticky
  from link_rules where link_id = '<id>' order by priority;
```

- **`settings ? 'failover'`** → Monitor found the destination down and Link
  swapped in the fallback. `settings->'failover'->>'previous'` holds the real
  destination; a `monitor.target.recovered` event puts it back. This is working
  as designed, and it is the one cause that looks most like a hack.
- **A rule matched that should not have.** Rules are first-match-wins in
  `priority` order, so a broad rule above a narrow one makes the narrow one
  dead. The link editor's simulator runs the *same* `resolve()` — reproduce it
  there rather than reasoning about the JSON.
- **A rotation.** `action->>'type' = 'rotate'` means different visitors get
  different destinations on purpose. `link_assignments` records who got what:

```sql
select variant_index, count(*) from link_assignments
 where rule_id = '<rule>' group by 1;
```

Roughly even counts mean it is working. Wildly uneven usually means the weights
are uneven, not that the hash is broken — `pickVariant` is deterministic and has
a test asserting the same visitor always lands on the same arm.

**Redirects are never cached**, at any layer (`cache-control: no-store`). If a
customer reports a stale destination, it is not our cache — check whether
something in front of us was configured to cache `/r/*`.

---

## A link 404s that should not

Aliases are unique per domain, enforced by two **partial** unique indexes:

```sql
\d+ links
-- links_alias_domain_key   (custom_domain_id, alias) where custom_domain_id is not null and deleted_at is null
-- links_alias_platform_key (alias)                   where custom_domain_id is null and deleted_at is null
```

Partial, because a plain `unique(custom_domain_id, alias)` does **not** hold on
the platform domain: Postgres treats NULLs as distinct, so two rows of
`(NULL, 'promo')` both insert and the redirect resolves to whichever the planner
returns. That was a real bug, and it handed one workspace another's traffic.

`deleted_at is null` is in both on purpose: deleting a link releases its alias.
So "undo" on a deleted link can genuinely fail if somebody claimed it in
between, and the toast says so rather than silently doing nothing.

---

## Bots are inflating click counts

They should not be — `parseClient` flags crawlers and unfurlers, and a flagged
request is not counted and does not consume a click limit. If counts still look
wrong:

```sql
select alias, clicks_count, last_clicked_at from links where id = '<id>';
```

`clicks_count` is a denormalised counter advanced with `+ 1` in the database
(never read-modify-written), and it exists so the *click limit* can be enforced
without querying the fact table. The authoritative count is in `events`. If the
two disagree, trust the fact table and treat the counter as a limit budget.

---

## A transfer will not download

```sql
select status, cancelled_at, cancel_reason, expires_at,
       download_limit, downloads, password_hash is not null as gated,
       total_files, total_bytes
  from transfers where id = '<id>';

select name, size_bytes, array_length(parts, 1) as parts_in,
       ceil(size_bytes / 8388608.0) as parts_expected, uploaded_at
  from transfer_files where transfer_id = '<id>' order by sort_order;
```

- **`parts_in < parts_expected`** → the upload never finished. `finaliseTransfer`
  refuses in this state deliberately: a half-uploaded share is worse than one
  that does not exist yet, because the recipient gets a truncated archive and
  blames the sender. The client resumes by asking which parts arrived and
  sending the difference — holes are normal, a parallel upload does not truncate
  cleanly.
- **`uploaded_at is null` but every part arrived** → the parts are there and the
  object was never assembled. Re-run `readyTransfer` (the "Add files" flow calls
  it automatically); it is idempotent and skips files already assembled.
  `uploaded_at` is written by `finaliseTransfer` **and nothing else** — it means
  "durable in the object store", and giving it a second writer once made
  finalise skip assembly and publish a link to an object that did not exist.
- **`downloads >= download_limit`** → done, by design. The claim and the count
  are one statement, so two recipients clicking simultaneously on a
  one-download transfer cannot both succeed.
- **`cancelled_at` set** → the sender pulled it back, and `cancel_reason` is
  shown to the recipient. That is the feature, not a fault.
- **`status = 'expired'`** → expiry *marks*, it does not delete. The storage
  sweep runs separately and reads this status, so an operator who catches a
  mistake inside the retention window can still recover the bytes.

---

## The bus stopped delivering

Symptom: tools stop reacting to each other; nothing errors.

```sql
select count(*) from event_outbox where published_at is null;   -- relay lag
select handler_key, status, attempts, count(*)
  from bus_deliveries group by 1,2,3 order by 4 desc limit 20;
select * from bus_dead_letters order by created_at desc limit 20;
```

- **Outbox growing** → the relay is not running. It is leader-elected; check
  that exactly one instance holds the advisory lock.
- **`bus_deliveries` stuck `running`** → a handler died mid-flight. It is
  retried; 8 attempts then dead-letters. A poison event never blocks the stream.
- **Dead letters** → replay from admin once the cause is fixed.

Delivery is *effectively once per handler*: redelivery is normal and the barrier
skips it. If you see a handler run twice, that is a bug — the barrier is a
unique constraint on `(handler_key, event_id)`.

---

## Someone is over quota / cannot do something

The resolver returns *one* reason, and it is the accurate one. Read it rather
than guessing:

```sql
select p.key, p.kind, e.feature_key, e.mode, e.limit_value, e.quota_value, e.overage
  from subscriptions s
  join plans p on p.id = s.plan_id
  join plan_entitlements e on e.plan_id = p.id
 where s.workspace_id = '<ws>' and s.status = 'active'
   and e.feature_key = '<feature>';

select * from usage_counters where workspace_id = '<ws>' and feature_key = '<feature>';
```

Rules that surprise people:
- **Limits take the MAX** across plans, **quotas SUM**. Two plans stack headroom.
- **Free is a floor, not a contributor** — once a paid plan has an opinion, the
  free row drops out. (Without this, every paying customer silently got the free
  allowance on top of what they bought.)
- **Lifetime plans exclude AI structurally**, at three independent points: a DB
  trigger, the resolver, and `ai.execute()`. A BYO key does not unlock it.
- `usage_counters` keys on `currentPeriodStart()` in **UTC**. Do not compute a
  period with `date_trunc(... now())` — that resolves in the database's
  timezone, and a mismatch silently stops every quota applying.

---

## AI needs to be turned off, now

```sql
update instance_settings
   set ai_master_enabled = false, ai_config_version = ai_config_version + 1;
```

It is a **kill**, not a hide: in-flight generations are cancelled and their
credit holds released, so nobody is billed for work that will not finish.
`ai_config_version` makes it take effect in under five seconds rather than after
a cache TTL.

Every AI-gated screen has a non-AI path, so the product stays usable — this is
the same experience a lifetime-plan holder has.

---

## Credits look wrong

Charging is a **hold**, never a debit: `reserve → capture | release`. A failed
job releases back to the *same* buckets, preserving expiry, so a refund does not
become immortal credit.

```sql
select id, source, amount, remaining, expires_at
  from credit_buckets where workspace_id = '<ws>' and remaining > 0
 order by expires_at asc nulls last, granted_at asc;   -- the spend order

select * from credit_entries where workspace_id = '<ws>' order by created_at desc limit 20;
```

Spend order is deliberate: plan grants expire first, so they burn first and
purchased credits are never wasted. Every capture carries an
`idempotency_key` with a unique constraint — a retried worker cannot
double-charge.

---

## Deploys

```bash
pnpm ci:check      # lint, typecheck, unit + integration tests, production build, tool isolation
pnpm e2e           # every journey, against a running server
pnpm load --key …  # p95 budgets, against `next start` — never `next dev`
```

**`pnpm e2e` needs a worker.** Crawls are queued, not run inline, so
`audit-primary.spec.ts` waits forever without one:

```bash
pnpm --filter @mamal/worker-audit start
```

Everything else in the suite runs against the app alone.

`ci:check` includes `next build` deliberately: it is the *only* thing that type
checks route handler signatures. `tsc --noEmit` does not see Next's generated
route validators, and four unbuildable API routes passed CI before this was
added.

Load numbers must come from a production build. Dev mode adds a ~120ms floor per
route, which swamps what is being measured — the same endpoints read p50 126ms
on dev and 56ms on `next start`.

---

## Things that look broken but are not

- **A cross-tenant read returns 404, not 403.** Deliberate. Existence is
  information.
- **`POST /api/v1/audit/sites/:id/audits` returns 202, never 200.** The crawl is
  queued; the response carries a `poll` URL.
- **A read-only key sees fewer MCP tools than the docs list.** `tools/list` is
  filtered by scope on purpose.
- **Cancelling an audit offers no Undo.** It is not data loss — crawled slices
  are scored and kept — and "undo" would mean a fresh crawl that spends quota
  again.
- **The bus delivers the same event twice.** Expected. The barrier makes the
  second a no-op.
- **A short link 302s, never 301.** A permanent redirect is cached by the
  browser forever; change the destination afterwards and everyone who followed
  the old one keeps going to the wrong place, unreachably. Every link here is
  editable by definition, so none of them are permanent.
- **A QR code's `encoded` is `null`.** That is a *dynamic* code: it encodes the
  short link, which is what makes it re-pointable after printing. Only static
  types carry an encoded payload.
- **Deleting a link frees its alias immediately.** Deliberate — a customer who
  deletes `/promo` can create it again. It also means the ten-second undo can
  fail if somebody claimed it in between, and the toast says so.
- **A splash page shows a `<meta refresh>`, not a JavaScript timer.** It works
  with scripts blocked, and the browser's back button behaves correctly with
  it, which `location.replace` in a timer does not.
- **The redirect's p95 is ~40ms, not the plan's 25ms.** That figure describes
  the Cloudflare Workers tier with the slug in KV, deliberately deferred. What
  is measured is this origin's own number. `resolve()` is a pure function over
  plain data precisely so the logic moves to the worker unchanged.
