import { test, expect, request as pwRequest } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

/**
 * Secondary journey: the machine-facing surface.
 *
 * REST and MCP are two encodings of one set of operations, so the assertions
 * that matter are the ones that would catch them drifting — same auth, same
 * scopes, same validation, same tenancy.
 *
 * These run without a browser session on purpose: an API key is the *only*
 * credential, and a route that quietly accepts a cookie instead would pass a
 * browser-driven test while being unusable by an actual client.
 */

function mintKey(target: string, scopes: string[]): string {
  const out = execFileSync(
    'pnpm',
    ['--filter', '@mamal/auth', 'mint-key', target, ...scopes],
    { encoding: 'utf8', cwd: process.cwd() },
  );
  const line = out.trim().split('\n').at(-1)!;
  return JSON.parse(line).secret as string;
}

test.describe('public API and MCP', () => {
  let full: string;
  let readOnly: string;
  let workspaceId: string;
  let siteId: string;

  // Minting shells out to pnpm twice; that is slower than a normal hook.
  test.beforeAll(async () => {
    test.setTimeout(120_000);

    // The signed-up account's own email identifies its workspace, so the
    // fixture needs no database access and cannot pick up a stale one.
    const account = JSON.parse(readFileSync('e2e/.auth/account.json', 'utf8')) as { email: string };
    full = mintKey(account.email, [
      'audit:sites:read', 'audit:audits:read', 'audit:audits:write', 'audit:issues:read',
      'link:links:read', 'link:links:write', 'link:qr:read', 'link:qr:write',
    ]);
    readOnly = mintKey(account.email, ['audit:sites:read']);

    // No browser here, deliberately: this spec exists to prove the API stands
    // on its own, so its own fixture must not need a session either.
    const probe = await pwRequest.newContext({
      baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3000',
      extraHTTPHeaders: { authorization: `Bearer ${readOnly}` },
    });

    // /api/v1/me is the first call a client should make: it confirms the key
    // and names the workspace it is bound to.
    const me = await probe.get('/api/v1/me');
    expect(me.ok(), 'a scoped key must still be able to identify itself').toBeTruthy();
    workspaceId = (await me.json()).workspace.id;
    expect(workspaceId).toBeTruthy();

    const sites = await (await probe.get('/api/v1/audit/sites')).json();
    expect(sites.data.length, 'the onboarding audit should have registered a site').toBeGreaterThan(0);
    siteId = sites.data[0].id;
  });

  test('REST: rejects, scopes, paginates and queues', async () => {
    const anon = await pwRequest.newContext({ baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3000' });
    const api = await pwRequest.newContext({
      baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3000',
      extraHTTPHeaders: { authorization: `Bearer ${full}` },
    });
    const ro = await pwRequest.newContext({
      baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3000',
      extraHTTPHeaders: { authorization: `Bearer ${readOnly}` },
    });

    // No key at all.
    const noKey = await anon.get('/api/v1/audit/sites');
    expect(noKey.status()).toBe(401);
    expect((await noKey.json()).error.hint).toContain('Bearer');

    // A key that is not ours.
    const badKey = await anon.get('/api/v1/audit/sites', {
      headers: { authorization: 'Bearer mk_not-a-real-key' },
    });
    expect(badKey.status()).toBe(401);

    // Reads work.
    const sites = await api.get('/api/v1/audit/sites');
    expect(sites.ok()).toBeTruthy();
    expect((await sites.json()).data.length).toBeGreaterThan(0);

    // A read-only key cannot write, even naming the route exactly.
    const denied = await ro.post(`/api/v1/audit/sites/${siteId}/audits`);
    expect(denied.status()).toBe(403);
    expect((await denied.json()).error.code).toBe('insufficient_scope');

    // Filters are validated, not silently ignored — an ignored filter returns
    // the wrong rows, which is worse than an error.
    const bogus = await api.get('/api/v1/audit/issues?severity=catastrophic');
    expect(bogus.status()).toBe(400);
    expect((await bogus.json()).error.message).toContain('severity');

    // limit clamps rather than rejecting.
    const clamped = await api.get('/api/v1/audit/issues?limit=500');
    expect(clamped.ok()).toBeTruthy();
    expect((await clamped.json()).data.length).toBeLessThanOrEqual(100);

    // Keyset pagination does not repeat a row across pages.
    const p1 = await (await api.get('/api/v1/audit/issues?limit=2')).json();
    if (p1.next_cursor) {
      const p2 = await (await api.get(`/api/v1/audit/issues?limit=2&cursor=${p1.next_cursor}`)).json();
      const ids = new Set(p1.data.map((r: { id: string }) => r.id));
      for (const row of p2.data) expect(ids.has(row.id)).toBe(false);
    }

    // A write returns 202 and a URL to poll, never a fabricated result.
    const queued = await api.post(`/api/v1/audit/sites/${siteId}/audits`);
    expect(queued.status()).toBe(202);
    const body = await queued.json();
    expect(body.poll).toBe(`/api/v1/audit/audits/${body.id}`);
    expect((await api.get(body.poll)).ok()).toBeTruthy();
  });

  test('MCP: handshake, scope-filtered discovery, and tool calls', async () => {
    const base = process.env.E2E_BASE_URL ?? 'http://localhost:3000';
    const anon = await pwRequest.newContext({ baseURL: base });
    const mcp = await pwRequest.newContext({
      baseURL: base,
      extraHTTPHeaders: { authorization: `Bearer ${full}` },
    });
    const ro = await pwRequest.newContext({
      baseURL: base,
      extraHTTPHeaders: { authorization: `Bearer ${readOnly}` },
    });
    const rpc = (ctx: typeof mcp, method: string, params?: unknown) =>
      ctx.post('/api/mcp', { data: { jsonrpc: '2.0', id: 1, method, params } });

    // Handshake needs no key: a client must be able to discover the server
    // before it has been given one.
    const init = await rpc(anon, 'initialize', { protocolVersion: '2025-06-18' });
    expect((await init.json()).result.serverInfo.name).toBe('mamal');

    // Anything past the handshake does.
    const unauth = await rpc(anon, 'tools/list');
    expect(unauth.status()).toBe(401);

    // Discovery lists only what this key may call — advertising a tool the
    // caller will be refused wastes an agent's turn finding that out.
    const roTools = (await (await rpc(ro, 'tools/list')).json()).result.tools.map(
      (t: { name: string }) => t.name,
    );
    expect(roTools).toContain('audit_list_sites');
    expect(roTools).not.toContain('audit_run_site');

    // The schema must not overstate what it accepts.
    const tools = (await (await rpc(mcp, 'tools/list')).json()).result.tools;
    const listSites = tools.find((t: { name: string }) => t.name === 'audit_list_sites');
    expect(listSites.inputSchema.properties.limit.maximum).toBe(100);

    // A call returns real data.
    const called = await (
      await rpc(mcp, 'tools/call', { name: 'audit_list_sites', arguments: { limit: 5 } })
    ).json();
    expect(called.result.isError).toBeFalsy();
    expect(called.result.structuredContent.result.length).toBeGreaterThan(0);

    // Bad arguments come back as a tool error the model can act on, not a
    // protocol error that ends the exchange.
    const invalid = await (
      await rpc(mcp, 'tools/call', { name: 'audit_list_issues', arguments: { severity: 'nope' } })
    ).json();
    expect(invalid.result.isError).toBe(true);
    expect(invalid.result.content[0].text).toContain('severity');

    // Scope is enforced on the call, not only in the listing.
    const refused = await (
      await rpc(ro, 'tools/call', { name: 'audit_run_site', arguments: { site_id: siteId } })
    ).json();
    expect(refused.error.message).toContain('audit:audits:write');
  });

  /* -------------------------------------------------------------------- link */

  test('Link: create, refuse, and reach the model with a reason', async ({ playwright, baseURL }) => {
    const api = await playwright.request.newContext({
      baseURL,
      extraHTTPHeaders: { authorization: `Bearer ${full}`, 'content-type': 'application/json' },
    });

    const alias = `api-${Date.now().toString(36)}`;
    const created = await api.post('/api/v1/link/links', {
      data: { url: 'https://example.com/from-api', alias, campaign: 'api-test' },
    });
    // 201: something now exists at a URL the caller can use immediately.
    expect(created.status()).toBe(201);
    const link = await created.json();
    expect(link.short_url).toContain(`/${alias}`);

    // It is a real link, not just a row — followed without any credential.
    const visitor = await playwright.request.newContext({ baseURL });
    const followed = await visitor.get(`/r/${alias}`, { maxRedirects: 0 });
    expect(followed.status()).toBe(302);
    expect(followed.headers()['location']).toBe('https://example.com/from-api');

    /*
     * Refusals carry their own sentence and their own status.
     *
     * These three used to be one 500 saying "the request could not be
     * completed" — which is the resolver returning a reason and the transport
     * throwing it away.
     */
    const reserved = await api.post('/api/v1/link/links', {
      data: { url: 'https://example.com/x', alias: 'login' },
    });
    expect(reserved.status()).toBe(400);
    expect((await reserved.json()).error.message).toMatch(/reserved/i);

    const taken = await api.post('/api/v1/link/links', {
      data: { url: 'https://example.com/x', alias },
    });
    expect(taken.status(), 'a taken alias is a conflict, not a server error').toBe(409);

    const badQr = await api.post('/api/v1/link/qr', {
      data: { name: 'No SSID', type: 'wifi', payload: {} },
    });
    expect(badQr.status()).toBe(400);

    // A dynamic QR gets a link; a static one encodes its payload directly.
    const dynamic = await (await api.post('/api/v1/link/qr', {
      data: { name: 'Poster', type: 'dynamic_url', url: 'https://example.com/poster' },
    })).json();
    expect(dynamic.link_id).toBeTruthy();
    expect(dynamic.encoded).toBeNull();

    const wifi = await (await api.post('/api/v1/link/qr', {
      data: { name: 'Cafe', type: 'wifi', payload: { ssid: 'Cafe', password: 'letmein', encryption: 'WPA' } },
    })).json();
    expect(wifi.link_id).toBeNull();
    expect(wifi.encoded).toBe('WIFI:T:WPA;S:Cafe;P:letmein;;');
  });

  test('MCP: a refusal comes back as a tool error the model can act on', async ({ playwright, baseURL }) => {
    const mcp = await playwright.request.newContext({
      baseURL,
      extraHTTPHeaders: { authorization: `Bearer ${full}`, 'content-type': 'application/json' },
    });

    const listed = await (await mcp.post('/api/mcp', {
      data: { jsonrpc: '2.0', id: 1, method: 'tools/list' },
    })).json();
    const names = listed.result.tools.map((t: { name: string }) => t.name);
    expect(names).toContain('link_shorten');
    expect(names).toContain('link_create_qr');

    const refused = await (await mcp.post('/api/mcp', {
      data: {
        jsonrpc: '2.0', id: 2, method: 'tools/call',
        params: { name: 'link_shorten', arguments: { url: 'https://example.com/y', alias: 'admin' } },
      },
    })).json();

    /*
     * A *tool* error, not a protocol error — the difference decides whether the
     * model can recover. A protocol error ends the exchange; this goes back to
     * the model, which can read the reason and pick a different alias.
     */
    expect(refused.error, 'a refusal is not a protocol failure').toBeUndefined();
    expect(refused.result.isError).toBe(true);
    expect(refused.result.content[0].text).toMatch(/reserved by the platform/i);
  });

  test('a key without Link scopes cannot see or call Link tools', async ({ playwright, baseURL }) => {
    const limited = await playwright.request.newContext({
      baseURL,
      extraHTTPHeaders: { authorization: `Bearer ${readOnly}`, 'content-type': 'application/json' },
    });

    // `tools/list` is filtered by scope, so the model is never offered
    // something it will then be refused.
    const listed = await (await limited.post('/api/mcp', {
      data: { jsonrpc: '2.0', id: 1, method: 'tools/list' },
    })).json();
    const names = listed.result.tools.map((t: { name: string }) => t.name);
    expect(names).not.toContain('link_shorten');

    expect((await limited.get('/api/v1/link/links')).status()).toBe(403);
    expect(
      (await limited.post('/api/v1/link/links', { data: { url: 'https://example.com/x' } })).status(),
    ).toBe(403);
  });
});
