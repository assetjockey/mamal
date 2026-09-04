# Mamal Platform

Six tools — **audit, confirm, link, market, monitor, track** — under one workspace, one
billing relationship, and one interop bus. Replaces the 18 products in `../tools`.

Full design: `~/.claude/plans/refine-plan-and-break-zany-pretzel.md`.

## Status

Phase 0 (platform core) is under way. What is built and verified:

| Package | What it does | Tests |
|---|---|---|
| `@mamal/db` | 64-table Drizzle schema, RLS on every tenant table, uuidv7, lifetime/AI trigger | 18 |
| `@mamal/entitlements` | Plan resolution across per-tool + unified + lifetime plans | 48 |
| `@mamal/credits` | FIFO expiring buckets with reserve → capture/release | 13 |
| `@mamal/resources` | URN registry + `neighbors()` behind the Connected panel | 13 |
| `@mamal/bus` | Transactional outbox, relay, effectively-once dispatch, dead letters | 21 |
| `@mamal/automations` | Trigger → condition → action engine, 10 seeded cross-tool recipes | 19 |
| `@mamal/auth` | Better Auth, workspace provisioning, permission grants, API keys | 16 |
| `@mamal/ai` | Provider registry, drivers, `execute()` guard, credential crypto | 18 |
| `@mamal/events` | Event fact table, attribution join, Postgres adapter | 10 |
| `@mamal/jobs` | Queue taxonomy, claim-and-enqueue, leader lock | 14 |
| `@mamal/tool-kit` | `ToolManifest` — the contract that makes tools independent | 11 |
| `@mamal/seo-checks` | 72-rule registry + weighted scoring | 31 |
| `@mamal/crawl` | BFS crawler, SSRF guard, HTML parser, robots/sitemap discovery | 21 |
| `@mamal/retention` | Per-workspace data expiry, resolved from the entitlement | 6 |
| `@mamal/ui` | Tokens, dual-tier shell, primitives, ⌘K palette, toasts with undo, setup checklist | 9 |
| `tools/audit` | **Phase 1** — the first tool: manifest, service, commands | 34 |
| `@mamal/push` | Web Push delivery, failure classification, segments | 21 |
| `tools/confirm` | **Phase 2** — social proof widgets and web push | 48 |
| `apps/app` | Next.js 16 dashboard, auth, onboarding, audit screens | — |

**Phase 0 is complete.** A workspace signs up, is provisioned, answers one onboarding question,
adds a site that every tool can already address, subscribes to plans, spends credits, and a tool
round-trips an event through the bus into an automation that calls a second tool — with neither
tool importing the other.

**Phase 1 (`/audit`) is running.** 72 rules across crawlability, on-page, links, performance,
security and AI visibility. A crawl of a real site produces a weighted score, a grade, the link
graph, and findings that each carry their own evidence and fix guidance.

Crawls run on the **`audit.crawl` queue in bounded slices**. One job crawls at most 25 pages,
persists its findings and the frontier, then re-enqueues itself. A killed worker loses one slice,
not a 10,000-page crawl, and the page counter moves throughout — so the UI shows real progress
rather than a spinner, and a stuck crawl is visible as a stalled slice.

Page rules run **per slice**, while the facts are complete: scripts, forms, inline styles and DOM
size are not worth persisting per page, so a page rule evaluated later from stored columns would
silently under-report. Site-wide rules — duplicates, orphans, sitemap coverage — need the whole
crawl and run once at the end.

Deferred by design — the ClickHouse adapter (the Postgres one implements the same interface), the
marketing/public/admin apps, and Lighthouse.

```bash
pnpm --filter @mamal/worker-audit start   # consumes audit.crawl, sweeps for due audits
```

### Screens

| Route | What it does |
|---|---|
| `/audit` | Websites, each with score, delta and a live crawl indicator |
| `/audit/sites/[id]` | Score trend, per-category pass rate, what to fix first, crawl stats |
| `/audit/sites/[id]/pages` | Every crawled URL with the facts each rule was judged on |
| `/audit/sites/[id]/settings` | Schedule, page and depth caps, robots, exclude patterns |
| `/audit/issues` | Findings grouped by rule, with evidence and fix guidance |
| `/audit/runs` | Every crawl: status, trigger, score, duration |
| `/audit/rules` | The 72-check catalogue with weights and thresholds |
| `/audit/sites/[id]/compare` | What was fixed and what was introduced between two runs |
| `/audit/tools` | 18 instant tools — the acquisition surface |
| `/audit/reports` | CSV and JSON export of any completed audit |

Scheduling is entitlement-gated **on the server**, not just in the UI: re-enabling the disabled
radio in the DOM and submitting still leaves the schedule on `manual`.

### AI features are additive, never load-bearing

Three AI features — audit summary, per-page fix brief, alt text — sit *beside* the guidance, never
instead of it. Every one of the 72 rules ships its own `why` and `howToFix`, and a test fails the
build if any rule's prose is thin. So with AI switched off, or on a lifetime plan that excludes it
structurally, the report is unchanged.

When AI is unavailable the panel says which of several distinct things is true — "AI is switched
off for this workspace", "not enough credits", "no provider key is configured" — because the
resolver returns the *first* failing reason rather than a generic denial. Underneath, the tool never
touches a provider: `ai.execute` re-resolves entitlements immediately before every call, and the
tests assert the driver is not reached when they deny.

### The free tools

18 tools, no sign-up. The 13 that compute locally are unlimited because they cost nothing to run;
the 5 that fetch a live page are limited to 30 an hour per IP and go through the same SSRF guard as
the crawler — `127.0.0.1` is refused with "private address", because a tool that takes a URL from an
anonymous visitor and fetches it from inside our network is the textbook SSRF setup.

The robots.txt tester implements longest-match-wins, so a specific `Allow` correctly beats a broader
`Disallow` the way real crawlers resolve it.

Export is available on **every** plan. Locking your own data behind a tier is the kind of thing that
makes people distrust a tool; what a paid tier buys is branded PDF reporting.

## G1 (design) — passed for `/audit`

Measured, not asserted: every check below ran in a real browser against all **46 app routes**,
enumerated from the filesystem rather than assumed. (An earlier sweep used a hand-written route
list; seven of its fifteen entries were 404s, so it was passing on empty pages.)

| Criterion | Result |
|---|---|
| Renders at 375 / 768 / 1280 / 1920 | no horizontal overflow on any route at any width |
| Light **and** dark | 0 text below its WCAG contrast floor, both themes |
| axe-core (wcag2a/aa, wcag21a/aa) | 0 violations, both themes |
| Tokens only, no ad-hoc colour | 0 raw hex outside `tokens.css` |
| `prefers-reduced-motion` | one global rule flattens every transition and animation |
| Keyboard-only | skip link first on every route, 33 stops in DOM order, no positive `tabindex`, no suppressed outline |
| Empty / loading / error / 404 / over-limit | all five exist |

Six real defects came out of it, each fixed at the layer that caused it rather than at the call site:

- **`--text-faint` was the palette's hairline colour.** `#839bc8` measures 2.68:1 on the canvas — a
  border value doing duty as 11px label text. It is now its own value, dark enough to clear 4.5:1 on
  *every* surface it lands on, including the `--accent-wash` of a selected card (4.59:1), which is
  where it was found failing. `--color-smoke` still exists, and still draws borders.
- **`--accent` was doing two incompatible jobs.** It is a text colour, so dark mode lifts it to
  `#7389ff` for legibility on a dark canvas — which made white-on-accent buttons 3.11:1. Worse, the
  primary button's hover *lightened* the fill, so it failed in light mode too, at every size. Split
  into `--accent-solid` / `--accent-solid-hover` / `--on-accent`; hover still lightens, just not past
  the floor.
- **Status green and amber failed as text.** `#0f9d58` at 3.51:1 and `#b26a00` at 4.24:1 mark exactly
  the findings a user must not miss. Darkened, with separate dark-mode values.
- **Scrollable tables were keyboard-unreachable.** `overflow-x-auto` with no `tabindex` hides every
  column past the fold from anyone without a mouse. Fixed once, in the `Table` primitive.
- **No skip link.** Both nav tiers precede the content, so reaching the page meant tabbing through
  every tool and section — on every route.
- **An exceeded quota went silent.** With `overage: 'credits'`, `resolve()` returned `allowed: true`
  carrying only a cost, dropping `quota`/`used`. The UI therefore said nothing at precisely the moment
  the user began paying per page. The overage paths now carry the allowance through, and `/audit`
  shows usage before the limit, the per-unit price after it, and an `UpgradeGate` naming the
  entitlement when it is refused.

Two of those were only visible because the sweep switched themes and re-measured. Two more were
invisible to a screenshot: contrast and focus order do not show up in a picture.

## G2 (workflow) — passed for `/audit`

| Criterion | Result |
|---|---|
| ⌘K reaches everything | 39 commands + live cross-tool resource search; `/` opens it, `g`+letter jumps, `?` lists every binding |
| A new user finishes the primary job with no docs | `/welcome` → first audit → findings; a docked checklist tracks the four steps Audit can actually deliver |
| Every limit visible before it is hit | `1 of 25 websites · 4 of 50,000 crawl pages used this period`, plus the per-unit price once past the included pages |
| Every long job cancellable and resumable | crawls run in 25-page slices; cancel is honoured at the next slice and keeps what was scored |
| Every destructive action undoable | issue triage toasts with a 10-second Undo instead of a confirm dialog |

**The palette is built from the nav data and the entitlement list, not from a hand-written array.**
A tool the workspace cannot use is absent from the palette for the same reason it is absent from
the sidebar, and a new tool brings its own entries. Where a *resource* hit opens comes from that
tool's manifest (`resources[].href`), not a lookup table in the palette — `externalId` is whichever
primary key the owning tool keys on, and only it knows. That distinction was not academic: the first
version keyed on the URN type alone and sent every website result to a 404, because a site has both
a `core:site` row (the hostname you own) and an `audit:audit_site` row (Audit's facet), and Audit's
screens take the latter. Registering a tool is now the whole cost of making it searchable.

Two other things came out of the gate:

- **`audit.sites` was a declared entitlement that nothing consulted.** Every tier — free included —
  could add unlimited websites. Now enforced in `addSite`, where re-registering a site you already
  own stays free so a workspace at its limit can still re-save what it has, with a regression test
  for both halves.
- **Cancelling an audit deliberately has no Undo.** It is not data loss — the slices already crawled
  are scored and kept — and "undo" would have to mean a fresh crawl, which spends quota again. A
  button that quietly bills the user is worse than no button, so the toast says what was kept.

## G3 (function) — passed for `/audit`

| Criterion | Result |
|---|---|
| Unit + integration tests green | 16 packages, `pnpm ci:check` clean |
| Playwright covers the primary and two secondary journeys | 5 specs, ~11s, against the real stack |
| Public API documented and exercised | `docs/api.md`; every claim in it is asserted by a test |
| MCP tools documented and exercised | 5 tools, scope-filtered discovery, live tool calls |
| Cross-tool handoffs demonstrated live | publish → outbox → relay → dispatch → handler, plus the idempotency barrier |

**One definition, two transports.** REST and MCP are encodings of
`apps/app/lib/audit-ops.ts` — the same SQL, the same zod schemas, the same
declared scope. The first version had them owning separate queries, which is how
one gains a filter and the other silently does not; the shared module means an
`?severity=catastrophic` is rejected identically by both, and MCP's advertised
JSON Schema is generated from the schema REST validates against.

Three defects the gate produced, none of which a unit test would have found:

- **The cross-tool handoff could never have fired.** Audit subscribed to
  `monitor.up`, but the envelope schema enforces `<tool>.<noun>.<past-tense>` —
  two segments, so `publish()` would have rejected it and the handler would have
  sat there looking correct until someone debugged it in Phase 5. The unit tests
  passed because they called `handler.handle` directly and never went near the
  bus. Renamed to `monitor.target.recovered`, and `subscriptionDefSchema` now
  holds subscriptions to the same rule as `eventDefSchema` — the publisher's
  contract and the subscriber's have to be one contract.
- **Onboarding stopped one click short of its own promise.** The screen says
  "two questions, then we go and look at your site", then left the user on a
  dashboard with an **Enable Audit** button to find. `addFirstSite` now registers
  the site with Audit and queues the crawl, which is what the plan's §0.10 "wow
  moment" describes and what the primary journey asserts.
- **Escape did nothing for a moment after ⌘K opened.** The palette handled it on
  its input, but focus lands there on a timeout — so pressing Escape the instant
  it appears, which is what a fast typist does, was a no-op. Escape now closes
  overlays from the global handler regardless of focus, and the spec keeps the
  race rather than waiting it away.

Two smaller ones worth the note: `limit=500` used to be a `400` rather than
clamping to 100, and the MCP schema advertised an unbounded maximum because the
clamp was a trailing `.transform()` — a schema that lies to an agent is worse
than one that constrains, so the clamp moved to a `preprocess` and the emitted
schema now says `maximum: 100`.

**API keys.** SHA-256 of 256 CSPRNG bits, shown once, with a clear 12-character
prefix so a leaked key in a log can be identified and revoked without anyone
handing over the secret. Not bcrypt: there is no user-chosen secret for a work
factor to defend, and a deliberately slow hash on every request is a
self-inflicted rate limit. Scope wildcards match per segment — `audit:*:read`
must not reach `audit_admin:secrets:read`, and a prefix match would.

## G4 (operation) — passed for `/audit`

| Criterion | Result |
|---|---|
| Load-tested at the tier's limit | `pnpm load` — 1,200 requests/scenario at concurrency 50, zero errors |
| p95 budgets met | asserted, against a **production build**; median of 3 rounds |
| Jobs resume after a kill | crawl resumes from the persisted frontier, no page re-crawled |
| Entitlements + credits enforced on free / lifetime / over-limit | 31 resolver cases, 13 ledger cases |
| Retention actually deletes | `@mamal/retention`, 6 cases + 2 in `tools/audit`, run end-to-end |
| Runbook written | `docs/runbook.md` — every query in it executed against a real database |

**Retention did not exist.** `core.data_retention_days` was on every plan and
nothing read it, so "7-day retention" on the free tier was a line on a pricing
page and nowhere else — a storage bill that grows forever and a promise not
kept. It now resolves per workspace through the same resolver everything else
uses, so an add-on that buys 24 months applies without the sweeper knowing the
add-on exists. Tools register a sweeper rather than having core reach into their
tables; `audit_snapshots` and each site's latest completed run are kept
regardless of age, because retiring detail is the promise and erasing history is
not. A window resolving below one day makes the sweep **refuse**: a resolver bug
must not be able to set the cutoff to "now" and delete everything.

**The load test found a real bottleneck, not a theoretical one.**
`last_used_at` on `api_keys` was written on every request — one row, every
request, under concurrency. The *fastest* routes had the worst tail (p50 134ms
against p95 686ms on a single-SELECT endpoint) because the write became the
dominant cost and the queue behind that one row grew. Not awaiting the promise
hid it from the response path but not from the row lock. Throttled to once a
minute per key; p95 dropped 686→361 immediately, and to ~100ms on a production
build.

Three things about measuring that turned out to matter more than the numbers:

- **Dev mode is not measurable.** `next dev` adds a ~120ms floor per route for
  module resolution, which swamps what is being measured — the same endpoints
  read p50 126ms on dev and 56ms on `next start`. Budgets are asserted against a
  production build only.
- **One round is noise.** The same endpoint measured p95 98ms and 197ms minutes
  apart with nothing changed but what else the box was doing. The harness now
  asserts on the median of three; a budget inside the noise band is one people
  re-run until it passes.
- **A budget set from a favourable run is not a budget.** `/api/v1/me` was
  pinned at 180ms from a single 98ms sample and its real spread is 83–201ms. Set
  above what has actually been observed, or it flaps.

The ceiling is the Postgres pool (`max: 10`/instance), and it degrades by
**waiting, never refusing** — zero errors at concurrency 200. That is the right
failure mode, and it means the lever is instances, not code.

**`ci:check` now runs `next build`.** It is the only thing that type-checks
route handler signatures: `tsc --noEmit` does not see Next's generated route
validators, and four unbuildable API routes passed CI before this was added.
That gap is also what let a dependency cycle through — `tools/audit` imported
`@mamal/retention` for a type while retention's runner imported every tool. The
tool now describes its sweeper structurally and the runner lives in
`services/worker-core`, which is the one place allowed to know every tool at
once.

## Phase 2 — `/confirm`, complete (G1–G4)

The spine is built and verified end to end: a widget configured in the database
renders on a third-party page through the real runtime, on the real payload.

| Package | What it does | Tests |
|---|---|---|
| `@mamal/widget-catalog` | 44 widget types across 8 render families, 30 themes | 14 |
| `@mamal/targeting` | 23 fields, 16 operators, evaluated in the browser | 20 |
| `@mamal/widget-runtime` | `confirm.js` — **5.22 KB gzipped**, CLS 0 | 14 |
| `tools/confirm` | Manifest, service, payload builder, cross-tool handlers | 18 |

**44 types, not 41.** The brief says 41 in prose and then enumerates 44 across
its five category lists. The lists win — a customer migrating from the source
product cares whether *their* widget exists, not what the total is called.

**The catalogue is data; the renderers are families.** 44 types share 8
layouts, so a new type is a catalogue entry plus a settings schema — not a
component, not a migration, not a column. One `zod` schema per type drives the
editor form, validates every write, and is what the runtime reads, so those
three cannot disagree.

**The payload is the boundary that matters.** It is fetched by a script tag on
someone else's website and readable by anyone who opens devtools, so
`buildPayload` decides what is safe to send:

- A conversion is projected to **first name, city, country** before it leaves
  the database. Never an email, never a surname, never an order value — "Ana in
  Lisbon" is proof, "Ana Silva" is identification, and the amount is nobody's
  business. Asserted by searching the serialised payload for each.
- A widget that never declared `needs: ['conversions']` is sent **none**. A
  cookie notice must not be a way to enumerate who bought what.
- Minimum thresholds are applied **server-side**. "Show nothing below 3 recent
  sales" is a promise not to fabricate proof; filtering in the runtime would let
  anyone read the payload for the widget that was meant to stay hidden.

**Targeting fails closed.** An unknown field or operator — an older script
meeting a newer rule — never matches. A regex that will not compile fails
`matches` *and* `not_matches`, because a broken rule must never be the reason a
widget appears. Nothing in the module throws: this runs on other people's sites,
and a malformed rule disables its widget rather than taking down the page.

Two defects the browser found that no unit test would have:

- **Widgets piled on top of each other.** Every one defaults to bottom-left, so
  a proof bubble, a bar and a signup form drew over one another. There is now
  one widget per position at a time; a widget whose slot is busy waits and
  re-checks rather than being dropped, because a customer who configured two
  bottom-left widgets expects to see both — just not at once. Verified by
  measuring every rendered box for overlap, and by watching the slot cycle.
- **The runtime fetched a URL that did not exist.** It requests
  `/c/{key}.json` — the brief's public contract, and effectively permanent once
  it ships inside a script tag — while the handler lives at `/api/c/`. Rewritten
  rather than changing the contract.

Measured in a real page, not asserted: **CLS 0**, zero layout-shift events, no
host element moved through a full widget cycle.

### The editor

Three panes: what it says, what it looks like, who sees it.

**The settings form is generated from the type's own zod schema** — the same
schema that validates the save. A new widget type therefore arrives with a
working editor and no UI written for it, and the form cannot offer a field the
validator will reject. Derived via `z.toJSONSchema`, the same public call the
MCP server uses to describe its tools: one mechanism, two consumers.

**The preview runs the actual `confirm.js`**, inlined into a sandboxed iframe —
not a React lookalike. "Matches production pixel-for-pixel" is only true if it
is literally the same code drawing, and a lookalike drifts on the first CSS
change nobody mirrors. The payload is built client-side and inlined too, so
there is no round trip per keystroke.

The frame is sandboxed **without** `allow-same-origin`, giving it an opaque
origin: a Custom HTML widget is markup the customer typed, and this is where it
renders. Three things fell out of that, each a real fix rather than a
workaround:

- `document.write` into the frame is blocked by the same isolation, so content
  is handed over declaratively via `srcdoc`.
- An external `<script src>` **fails to load** in a sandboxed srcdoc document —
  the tag fires `error` with nothing in the console. The runtime source is now
  fetched once by the parent and inlined, which also removes a request and any
  chance of previewing a stale cached build.
- Inlining exposed a genuine runtime bug: `new URL(script?.src ?? location.href)`
  uses `??`, but an inline script's `src` is `''` — not null — so `new URL('')`
  threw, and the boot wrapper swallowed it into "no widgets, no error". Now `||`.

The targeting panel explains each rule against an **editable** sample visitor,
using `explain()` from the same module the browser runs. A panel that could
disagree with the runtime would be worse than none — it would give false
confidence about who is being shown what.

Also fixed: the preview was computing `Date.now()`-derived timestamps during
render, so SSR and the first client render disagreed and React reported a
hydration mismatch. It is client-only now, which is also one fewer copy of the
payload in the HTML.

### Push

| Package | What | Tests |
|---|---|---|
| `@mamal/push` | Delivery, failure classification, segment selection | 21 |

**The encryption is not hand-rolled.** RFC 8291 (aes128gcm, ECDH P-256, HKDF)
and RFC 8292 (VAPID JWTs) are exactly the kind of cryptography that is easy to
implement so it looks right and is quietly broken — `web-push` is the reference
implementation. The same reasoning that wraps `linkinator` rather than
reimplementing a crawler. What this codebase owns is everything around it: who
gets chosen, what a failure *means*, and keeping the list honest.

**A 404 or 410 retires the subscription.** The browser was uninstalled or
permission revoked — retrying is guaranteed to fail. Without retiring, every
campaign re-attempts the same dead endpoints forever, each send gets slower, and
the delivery rate shown to a customer is measured against people who no longer
exist. A 500 is *not* treated the same way: a transient failure is not a reason
to unsubscribe somebody.

**One VAPID pair per site, not per platform.** The public half is baked into
every subscription a browser creates against it, so a shared key could never be
rotated — one compromise would invalidate every subscriber on every customer
site at once. The private half is encrypted at rest with its own salt rather
than reusing the AI credential key: domain separation costs one string.

**Segments reuse the widget rule engine.** A second rule language would mean a
second parser, a second builder, a second set of operator bugs, and a customer
learning "contains" twice. That required a real change: the engine's field list
is deliberately closed and unknown fields fail closed, so `tags` and
`days_subscribed` matched *nobody* — a segment that silently selected zero
people and looked like "no audience" rather than a bug. Callers now declare
their extra fields; anything undeclared still fails closed, which for a *send*
is the direction that matters.

Three things the tests caught:

- **`vi.mock('web-push')` silently did nothing.** pnpm resolves the package to a
  different physical path from `tools/confirm` than from `packages/push`, so the
  mock never applied and the suite was attempting real sends. The transport is
  now injected the same way `decrypt` and `subject` already were — a real seam a
  worker can also use for rate-limit-aware sending, not test plumbing.
- **A limit test that inserted 25,001 rows** timed out, and its writes kept
  landing *after* the timeout and corrupted the next test. The refusal is now
  checked against a purpose-built 2-subscriber plan.
- **Re-subscribing must update, not duplicate.** The endpoint is the identity;
  without the unique constraint someone who clears site data three times gets
  the same campaign three times, which is the fastest route to a blocked origin.
  Verified live: two subscribes to the same endpoint left one row with updated
  keys.

The install flow is deliberately honest that push is harder than the widget: a
service worker only controls the scope it is *served from*, so it cannot be
loaded cross-origin and the customer has to host one file. The UI says so and
advises asking for permission after interest rather than on page load — browsers
remember a refusal permanently and there is no second chance at it.

### Automations, and the gates

Recurring campaigns, drip flows and RSS automations all follow the rule the
audit scheduler established — **claim, then act**. A row is moved forward in the
same statement that selects it, so two workers racing produce one send.

Four failure modes those tests pin down, each one a real product complaint:

- **Recurrence anchors on the scheduled time, not on completion.** Anchoring on
  `now()` makes a 9am daily send arrive later every day until it goes out at
  teatime.
- **Flow position is per subscriber.** Tracking it per flow would send step four
  to somebody who never received steps one to three.
- **RSS guards on guid, not date.** Feeds re-order and back-date constantly; a
  date comparison notifies everyone about a three-year-old post the moment
  someone fixes a typo in it. The first poll records the position without
  notifying, and only the newest unseen item is sent — forty items appearing at
  once must not become forty notifications.
- **A subscriber who unsubscribes mid-sequence stops receiving it**, and a feed
  that is down does not fail the batch.

**AI is additive, and refuses to make things worse.** A translation that lost a
`{{placeholder}}` is discarded in favour of the original: the alternative is a
widget rendering "Someone in just bought" to one locale's visitors, which nobody
who reads that language is likely to report for months. Copy generation is given
the *shape* of the conversion data — which tokens exist — never the rows, since
it is writing a template and the data would add nothing but exposure.

| Gate | Result |
|---|---|
| G1 design | 8 routes, 375px, both themes, 0 axe violations |
| G2 workflow | limits shown before they are hit; deletes undoable; editor preview is the real runtime |
| G3 function | 4 e2e journeys; REST + MCP extended to Confirm; cross-tool handlers dark-launched |
| G4 operation | widget payload p95 **46ms**; retention sweeps conversions; runbook covers both halves |

**The API and MCP surfaces now serve two tools from one list.** `AUDIT_OPS` and
`CONFIRM_OPS` are concatenated at the composition root; a tool contributes its
operations and gets REST *and* MCP for free. `tools/list` stays scope-filtered,
so a key holding only `confirm:*` sees three tools and a full key sees eight.

Sending a push campaign is deliberately **not** an MCP tool. Recording a
conversion is a safe write; notifying a customer's entire audience is not
something an agent should be able to do.

Two things the e2e caught that the unit tests could not:

- The journey added a **Coupon** and then expected a conversion feed — which
  came back empty, correctly, because `coupon` does not declare
  `needs: ['conversions']`. The withholding rule working, and the test wrong.
- Posting one conversion left the widget hidden, because the type's default
  minimum is three and that threshold is enforced server-side. Both are the
  product being honest; both looked like bugs until read properly.

Retention now sweeps `confirm_conversions` — personal data held only to power a
rolling feed — but deliberately **never** sweeps push subscribers by age. A
subscription is standing consent; deleting it because it is old would silently
stop someone's notifications while their browser still believes it is
subscribed. Expired endpoints are retired on send instead, which is the moment
we actually learn they are gone.

## The audit rule engine

Rules are **rows, not a 1,200-line trait**. phprank puts all 36 checks in one `model()` method;
66audit in one includes array. Here each rule is a small evaluator with a weight, its own
`why`/`howToFix` prose, and workspace-overridable thresholds — so adding one is a registry entry
plus a seed row, and retuning one needs no deploy.

Two things the engine does that the sources do not:

- **The link graph is persisted.** crawlseo keeps it, open-seo discards it at the end of the crawl. Keeping it is what makes "what links to this broken page" answerable afterwards.
- **A WAF block is reported as a block**, not as a healthy page or a broken one, with the exact user agent to allowlist. Pretending it did not happen is the most common complaint about cloud crawlers.

The `ai-visibility` category is what "search **and AI visibility**" means concretely: whether an
answer engine can read the page at all (`content-not-extractable` catches a client-rendered shell
that Google ranks fine), attribute it, and quote it. Blocked AI crawlers are reported as
*informational with the trade-off stated* — it is a legitimate choice, not a mistake.

## Try it

```bash
pnpm --filter @mamal/app dev     # http://localhost:3000
```

Sign up at `/sign-up` — a workspace, a Default project and an owner seat are provisioned by a
database hook, so the first render already has an RLS scope.

`/settings/entitlements` carries a **resolver sandbox**: stack a per-tool plan on a unified one and
watch limits merge with MAX while quotas merge with SUM, grant credits, flip the instance or
workspace AI kill switch, or switch to Lifetime and watch every AI feature refuse while 5,000
credits sit unspent in the bank. The buttons write the same rows the billing webhook will, so it is
a real exercise of the resolver rather than a mock.

## Local bring-up

Requires Node 22+, pnpm 11+, and PostgreSQL 16+ (Homebrew is fine; Docker is not required).

```bash
pnpm install
createdb mamal_dev
export DATABASE_URL="postgres://$(whoami)@localhost:5432/mamal_dev"

pnpm db:migrate      # schema + RLS policies + the lifetime/AI trigger
pnpm db:seed         # 73 features, 16 plans, AI registry — idempotent
pnpm db:test:setup   # creates the non-superuser role the RLS tests need
pnpm test
```

`pnpm db:seed` asserts its own invariants and fails loudly if the catalogue drifts:
lifetime plans grant no AI, no entitlement references an unknown feature, and the free
plan grants nothing metered.

## Why the tests need a second database role

Superusers bypass row level security entirely, so a suite run as the schema owner would
pass vacuously. `pnpm db:test:setup` creates `mamal_app` (non-superuser), and
`rls.live.test.ts` asserts it is not a superuser before asserting anything else.

## Architectural boundaries, enforced by CI

`eslint.config.mjs` makes three rules unbreakable rather than conventional:

- **Tools cannot import each other.** Cross-tool work goes through `commands.dispatch('<tool>.<verb>')`, so a workspace without that tool degrades instead of failing to build.
- **Only `packages/ai` may import a provider SDK.** This is the third enforcement point for lifetime's AI exclusion — there is no code path around `ai.execute()`'s entitlement re-check.
- **`unsafeUnscopedDb` is banned outside one audited accessor.** Everything else goes through `withWorkspace()` or `asPlatformAdmin()`, both of which set the RLS GUC.

## Three invariants worth knowing

**Tenant isolation is a build constraint.** `packages/db/src/rls.ts` reflects over the
schema; the isolation test fails on any table that neither carries `workspace_id` nor
appears in `EXEMPT_TABLES` with a stated reason. Adding a table without thinking about
isolation breaks CI.

**Lifetime plans exclude AI at three independent points** — a database trigger on
`plan_entitlements`, the entitlement resolver, and `ai.execute()`, which re-resolves entitlements
immediately before every vendor call. The eslint boundary forbids importing a provider SDK outside
`packages/ai`, so there is no path around it. The tests prove the driver is never reached: a
lifetime holder with 5,000 credits *and* their own API key still gets `ai_excluded_lifetime`.

**Credits are held, not debited.** `reserve()` draws from buckets, `capture()` trues up to
the actual cost, `release()` restores to the *same* buckets so expiry is preserved. A
failed generation costs the user nothing, and a retried job cannot double-charge.

## Deliberate deferrals

**ClickHouse runs on a Postgres adapter.** `packages/events` defines the store contract; the
Postgres adapter implements it today so the platform needs one database. Phasing the
*infrastructure* is safe. Phasing the *interface* would not be — which is why the bus was built
first and in full.

**Three array bugs, one helper.** Drizzle renders a JS array as a parenthesised value list, which
is right for `IN` and wrong for `text[]`. It bit an events insert, a claim-and-enqueue update and an
onboarding upsert before `packages/db/src/sql-helpers.ts` fixed it once.

## Layout

```
apps/       app · web · public · admin        (Next.js)
workers/    redirect · ingest · widget · bridge (Cloudflare, Hono)
services/   worker-* · scheduler · trends     (long-running Node)
packages/   db · entitlements · credits · resources · tool-kit · …
tools/      one package per tool
plugins/    affiliate · teams · newsletters · …
```

## Why `pnpm test` runs serially

Several suites mutate genuinely global rows — `instance_settings.ai_master_enabled` is a singleton,
and the outbox relay drains platform-wide by design. Run in parallel against one database they see
each other's state, and a different package fails each time. `--concurrency=1` is the honest fix
until each suite gets its own database; `lint` and `typecheck` still run in parallel.
