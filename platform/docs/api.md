# Mamal API

One REST API and one MCP server over the same operations. Anything you can do in
`/api/v1/audit/*` you can do as an MCP tool, with the same auth, the same
scopes, the same validation and the same errors — they are two encodings of
`apps/app/lib/audit-ops.ts`, not two implementations.

Base URL in development: `http://localhost:3000`.

## Authentication

Every request carries an API key as a bearer token:

```
Authorization: Bearer mk_…
```

Keys are shown **once**, at creation, and stored only as a SHA-256 digest — a
database read cannot be replayed as a credential. A 12-character prefix is kept
in clear so a key found in a log can be identified and revoked without anyone
handing over the secret.

Create one from the CLI:

```bash
pnpm --filter @mamal/auth mint-key <workspace-slug|workspace-id|owner-email> audit:sites:read audit:issues:read
```

Confirm it works — this is the first call any client should make:

```bash
curl -s -H "Authorization: Bearer $KEY" localhost:3000/api/v1/me
```

```json
{
  "workspace": { "id": "01a0…", "name": "Acme", "slug": "acme" },
  "key": { "id": "01a0…", "name": "cli", "scopes": ["audit:sites:read"] }
}
```

`/api/v1/me` needs no scope beyond a valid key.

### Scopes

`<tool>:<resource>:<action>`. `*` is a wildcard **per segment**, so
`audit:*:read` grants read on every Audit resource and `*` alone is full access.
It is not a text prefix: `audit:*:read` does not match `audit_admin:secrets:read`.

| Scope | Grants |
|---|---|
| `audit:sites:read` | List audited websites |
| `audit:audits:read` | Read runs and their progress |
| `audit:audits:write` | Queue a crawl |
| `audit:issues:read` | Read findings |
| `confirm:campaigns:read` | List social-proof campaigns |
| `confirm:widgets:read` | List notifications |
| `confirm:conversions:write` | Record a conversion |
| `link:links:read` | List short links and their clicks |
| `link:links:write` | Create a short link |
| `link:qr:read` | List QR codes and their scans |
| `link:qr:write` | Mint a QR code |

A key with the wrong scope gets `403 insufficient_scope`, naming the scope it
needed. A missing, unknown, revoked or expired key gets `401` with a `code` that
distinguishes them.

## Conventions

**Pagination is keyset, not offset.** Every list returns
`{ "data": [...], "next_cursor": "…" }`. Pass `next_cursor` back as `?cursor=`.
Cursors are uuidv7 primary keys, which sort by creation time, so paging is
stable even while rows are being written — an OFFSET would skip or repeat rows.
`next_cursor` is `null` on the last page.

**`?limit=` clamps, it does not reject.** The maximum is 100; asking for 500
returns 100. An error there would name nothing you could have done differently.

**Filters are validated.** An unknown `severity` is a `400`, never silently
ignored — a dropped filter returns the wrong rows, which is worse than an error.

**Errors have a stable shape:**

```json
{ "error": { "code": "insufficient_scope", "message": "…", "hint": "…" } }
```

Branch on `code`; `message` and `hint` are for humans.

**Tenancy.** A key is bound to one workspace and every query runs under
Postgres RLS. Another workspace's resource is a `404`, not a `403` — existence
is itself information.

## Endpoints

### `GET /api/v1/audit/sites`
Websites Audit is watching, with the latest score, grade and issue counts.
Scope: `audit:sites:read`. Query: `limit`, `cursor`.

### `GET /api/v1/audit/sites/:id/audits`
Run history for one site, newest first. Scope: `audit:audits:read`.

### `POST /api/v1/audit/sites/:id/audits`
Queues a crawl. Scope: `audit:audits:write`.

Returns **202**, not 200 — the crawl runs on a queue in 25-page slices, so
"accepted, poll here" is the honest answer:

```json
{ "id": "01a0…", "status": "queued", "max_pages": 25,
  "start_url": "https://example.com", "poll": "/api/v1/audit/audits/01a0…" }
```

Over quota returns **402** with the resolver's own reason
(`quota_exhausted`, `limit_reached`, `insufficient_credits`, `not_in_plan`), so
a client can tell "out of allowance" from "not on your plan" without parsing
prose.

### `GET /api/v1/audit/audits/:id`
One run, including live progress while it is still crawling.
Scope: `audit:audits:read`.

### `GET /api/v1/audit/issues`
Findings, each with its evidence. Scope: `audit:issues:read`.
Filters: `status` (`open` default, `fixed`, `ignored`), `severity`
(`critical`, `warning`, `info`), `rule_id`, `audit_id`, plus `limit`/`cursor`.

### `GET /api/v1/confirm/campaigns`
Social-proof campaigns with notification and conversion counts.
Scope: `confirm:campaigns:read`.

### `GET /api/v1/confirm/notifications`
Notifications with impressions and clicks. Optional `?campaign_id=`.
Scope: `confirm:widgets:read`.

### `POST /api/v1/confirm/conversions`
Records a conversion, which becomes eligible for proof notifications
immediately. Scope: `confirm:conversions:write`.

```json
{ "campaign_id": "01a0…", "type": "bought", "name": "Ana", "city": "Lisbon", "country": "PT" }
```

Only a **first name**, city and country ever reach a visitor's browser — send
more if it is useful in your own dashboard, but it stays server-side. Recorded
with `source: "api"`, distinct from `manual` and `bus`, so a proof line can
always be traced back to how it arrived.

There is also a **public** conversion webhook at `POST /api/c/conversion`,
authenticated by the campaign's pixel key rather than an API key. It is for a
customer's own backend: the key is public by design, and the worst a leaked one
allows is somebody inflating their own proof feed. Anything that reads or
changes configuration needs a real API key.

### `GET /api/v1/link/links`
Short links with their current destination and click count. Filters: `kind`
(`short`, `biolink`, `qr`, `transfer`, …), `campaign`, and `q` — which matches
alias, title or destination. Scope: `link:links:read`.

Every row carries `short_url`, composed in one place, so the string this returns
is byte-for-byte the one the dashboard copies and a QR payload encodes.

### `POST /api/v1/link/links`
Creates a short link and returns **201**. Scope: `link:links:write`.

```json
{ "url": "https://example.com/spring", "alias": "spring", "campaign": "q2",
  "utm": { "source": "poster", "medium": "print" } }
```

`alias` is optional; omit it and one is generated from an alphabet with no
confusable characters — no `0/O`, no `1/l/I` — because the job of a short link
is surviving being read off a poster or dictated over the phone.

The destination stays editable afterwards. That is the point: mint the link,
print it, decide where it goes later.

Refusals name themselves rather than collapsing into a 500:

| Status | When |
|---|---|
| `400 invalid_alias` | Reserved (`login`, `api`, …) or a shape a URL cannot carry |
| `409 alias_taken` | Somebody already has it on this domain. Retrying is futile |
| `402 limit_reached` | Out of plan allowance, with the resolver's own sentence |

### `GET /api/v1/link/qr`
QR codes with scan counts. Scope: `link:qr:read`.

### `POST /api/v1/link/qr`
Mints a QR code and returns **201**. Scope: `link:qr:write`.

```json
{ "name": "Spring poster", "type": "dynamic_url", "url": "https://example.com/spring" }
```

The commercial distinction is decided here, once:

- A **dynamic** type resolves through a short link. `link_id` is set, `encoded`
  is `null`, the destination can be changed after ten thousand posters are
  printed, and every scan is counted.
- A **static** type encodes its payload directly. `link_id` is `null` and
  `encoded` carries the exact string the code contains. Right for wifi
  credentials, which a phone reads with no network; fatal for a campaign URL.

```json
{ "name": "Cafe wifi", "type": "wifi",
  "payload": { "ssid": "Cafe", "password": "hunter2", "encryption": "WPA" } }
→ { "link_id": null, "encoded": "WIFI:T:WPA;S:Cafe;P:hunter2;;" }
```

A payload the type cannot encode is a `400` naming the field, not a code that
scans to nothing.

### Transfers

Uploads and downloads both go **direct to storage** through pre-authorised
URLs — the API hands out URLs and records what landed, and never carries a byte.
That is what makes a 5 GB transfer possible on a small origin.

The flow, and why it has four steps rather than one:

1. `planFileUpload` reserves the file and returns `partUrls`, one per 8 MB part.
   The plan's size limit is checked **here**, before anything moves: deciding
   afterwards means having already accepted bytes we then have to refuse.
2. The client `PUT`s each part to its URL and reports the `ETag` the store
   returned. S3-compatible providers will not assemble without the exact set.
3. `resumeFileUpload` mints fresh URLs for whatever is still missing. This is
   the resumability: a 5 GB upload *will* be interrupted, and the recovery has
   to be "carry on from part 41" rather than "start again". Holes are normal —
   an interrupted parallel upload leaves gaps, not a clean truncation.
4. `readyTransfer` assembles the objects and marks the transfer shareable. It
   refuses while a part is missing, because a share link to a truncated archive
   is worse than one that does not exist yet.

Downloading is two decisions kept deliberately apart: the claim counts the
download and enforces the limit, and only then is a short-lived signed URL
issued. A saved URL is therefore not a way around the counting — a fresh URL
needs a fresh claim.

Object keys carry random bytes, never the filename: keys reach logs, CDN traces
and presigned URLs, and `invoice-acme-final.pdf` in a URL is a disclosure even
when the URL is signed. The sender's name is restored on the way out through
`Content-Disposition`.

## Widget delivery

`GET /c/{pixelKey}.json` — the single payload the widget runtime fetches.
Unauthenticated, because it is loaded by a script tag on a customer's site.
Edge-cached 60s with a 24h stale-while-revalidate window, so one origin request
per campaign per minute serves any amount of traffic.

An unknown key and a disabled campaign return the same 404, so keys cannot be
enumerated.

`GET /confirm.js` — the runtime itself, 5.2 KB gzipped.

## MCP

`POST /api/mcp` — stateless JSON-RPC 2.0 over HTTP. Nothing is kept between
calls, so a dropped connection loses no session.

```json
{ "mcpServers": { "mamal": {
  "url": "http://localhost:3000/api/mcp",
  "headers": { "Authorization": "Bearer mk_…" }
} } }
```

`initialize` and `notifications/*` need no key, so a client can discover the
server and negotiate a version before it has been given one. Everything else
does.

`tools/list` returns **only the tools the presented key can call** — advertising
one the caller will be refused wastes an agent's turn discovering that. Input
schemas are generated from the same zod schemas the REST API validates against,
so they cannot overstate what is accepted.

| Tool | Scope | Read-only |
|---|---|---|
| `audit_list_sites` | `audit:sites:read` | yes |
| `audit_list_audits` | `audit:audits:read` | yes |
| `audit_get_audit` | `audit:audits:read` | yes |
| `audit_list_issues` | `audit:issues:read` | yes |
| `audit_run_site` | `audit:audits:write` | no — a *safe* write |
| `confirm_list_campaigns` | `confirm:campaigns:read` | yes |
| `confirm_list_notifications` | `confirm:widgets:read` | yes |
| `confirm_record_conversion` | `confirm:conversions:write` | no — a *safe* write |

`audit_run_site` starts work the user already pays for and can cancel, and
destroys nothing. Deleting a site or marking a finding fixed are deliberately
**not** exposed: an agent should not be able to close a customer's problems.

`confirm_record_conversion` is the most consequential safe write on the server,
because it decides what a visitor is told really happened. Its description says
so. Sending a push campaign is deliberately **not** exposed at all — an agent
should not be able to notify a customer's entire audience.

Invalid arguments come back as a tool error (`isError: true`) rather than a
JSON-RPC error, so a model can read the message and retry. Scope failures are
protocol errors, because retrying will not help.

A tool's own refusal is also a tool error, and it carries the tool's sentence:

```json
{ "result": { "isError": true,
  "content": [{ "type": "text",
    "text": "invalid_alias: “admin” is reserved by the platform. Try another." }] } }
```

That distinction decides whether the model can recover. `link_shorten` refusing
an alias is a fact the model can act on by choosing another; the same thing as
"The operation failed" is a dead end.

```bash
curl -s -X POST localhost:3000/api/mcp \
  -H "Authorization: Bearer $KEY" -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/call",
       "params":{"name":"audit_list_sites","arguments":{"limit":5}}}'
```

`GET /api/mcp` returns discovery metadata for clients that probe before posting.
