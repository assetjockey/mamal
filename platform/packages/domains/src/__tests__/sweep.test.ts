/**
 * The domain sweep, against a real database.
 *
 * The claim-and-check pattern is the part that needs a real one: `for update
 * skip locked` cannot be demonstrated in a mock, and it is the whole reason two
 * schedulers running the same minute do not both hammer the same resolver.
 */
import { sql } from 'drizzle-orm';
import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { asPlatformAdmin, closeDb, unsafeUnscopedDb, withWorkspace } from '@mamal/db';
import { checkOneDomain, sweepPendingDomains } from '../sweep.ts';
import type { Resolver } from '../verify.ts';

const db = unsafeUnscopedDb();
const tag = `dom${Date.now()}`;
const TARGET = 'cname.mamal.app';

let ws = '';
let project = '';

const resolver = (byHost: Record<string, { txt?: string[]; cname?: string[] }>): Resolver => ({
  resolveTxt: async (host) => {
    const value = byHost[host]?.txt;
    if (!value) throw new Error('ENOTFOUND');
    return value.map((v) => [v]);
  },
  resolveCname: async (host) => {
    const value = byHost[host]?.cname;
    if (!value) throw new Error('ENOTFOUND');
    return value;
  },
  resolve4: async () => {
    throw new Error('ENOTFOUND');
  },
});

async function addDomain(host: string): Promise<{ id: string; token: string }> {
  return withWorkspace(ws, async (tx) => {
    const token = `mamal-verify-${host.replace(/\W/g, '')}`;
    const [row] = await tx.execute<{ id: string }>(sql`
      insert into custom_domains (workspace_id, project_id, host, kind, verification_token)
      values (${ws}, ${project}, ${host}, 'link', ${token})
      returning id`);
    return { id: row!.id, token };
  }, { db });
}

beforeAll(async () => {
  await asPlatformAdmin(async (tx) => {
    const [u] = await tx.execute<{ id: string }>(sql`
      insert into users (email, name) values (${`${tag}@test.local`}, 'Dom') returning id`);
    const [w] = await tx.execute<{ id: string }>(sql`
      insert into workspaces (slug, name, owner_user_id) values (${tag}, 'Dom', ${u!.id}) returning id`);
    ws = w!.id;
    const [p] = await tx.execute<{ id: string }>(sql`
      insert into projects (workspace_id, name, slug, is_default)
      values (${ws}, 'Default', 'default', true) returning id`);
    project = p!.id;
  }, { db });
});

afterAll(async () => {
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from workspaces where id = ${ws}`);
  }, { db });
  await closeDb();
});

describe('the domain sweep', () => {
  it('verifies a domain once both records are in place', async () => {
    const host = `${tag}-ready.example.com`;
    const { id, token } = await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      const result = await sweepPendingDomains(tx, {
        target: TARGET,
        minIntervalSeconds: 0,
        resolver: resolver({ [`_mamal.${host}`]: { txt: [token] }, [host]: { cname: [TARGET] } }),
      });
      expect(result.verified).toContain(host);

      const [row] = await tx.execute<{ verified_at: string | null; ssl_status: string }>(sql`
        select verified_at, ssl_status from custom_domains where id = ${id}`);
      expect(row!.verified_at).not.toBeNull();
      /*
       * Not `active`. The certificate is issued after the hostname is routed,
       * and a green tick next to a domain still serving a TLS warning is the
       * one failure a customer reads as "this product is broken".
       */
      expect(row!.ssl_status).toBe('provisioning');
    }, { db });
  });

  it('records which half is missing, not just “pending”', async () => {
    const host = `${tag}-half.example.com`;
    const { id, token } = await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      // Proved, not routed.
      await sweepPendingDomains(tx, {
        target: TARGET,
        minIntervalSeconds: 0,
        resolver: resolver({ [`_mamal.${host}`]: { txt: [token] } }),
      });
      const [row] = await tx.execute<{
        dns_status: string; verified_at: string | null;
        last_check: { owned: boolean; routed: boolean; nextStep: string };
      }>(sql`select dns_status, verified_at, last_check from custom_domains where id = ${id}`);

      expect(row!.verified_at).toBeNull();
      expect(row!.dns_status).toBe('partial');
      expect(row!.last_check).toMatchObject({ owned: true, routed: false });
      // Support should not have to ask the customer to run `dig`.
      expect(row!.last_check.nextStep).toMatch(/point .* at cname\.mamal\.app/i);
    }, { db });
  });

  it('does not verify a domain that merely points at us', async () => {
    const host = `${tag}-squat.example.com`;
    const { id } = await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      // A CNAME anyone can add. Without the token this must never verify.
      await sweepPendingDomains(tx, {
        target: TARGET,
        minIntervalSeconds: 0,
        resolver: resolver({ [host]: { cname: [TARGET] } }),
      });
      const [row] = await tx.execute<{ verified_at: string | null }>(sql`
        select verified_at from custom_domains where id = ${id}`);
      expect(row!.verified_at).toBeNull();
    }, { db });
  });

  it('throttles, so a stuck domain is not looked up every second', async () => {
    const host = `${tag}-slow.example.com`;
    await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      const nothing = resolver({});
      const first = await sweepPendingDomains(tx, { target: TARGET, minIntervalSeconds: 60, resolver: nothing });
      expect(first.checked).toBeGreaterThan(0);

      // Immediately again: everything was just checked, so nothing is due.
      const second = await sweepPendingDomains(tx, { target: TARGET, minIntervalSeconds: 60, resolver: nothing });
      expect(second.checked).toBe(0);
    }, { db });
  });

  it('never unverifies a working domain', async () => {
    const host = `${tag}-live.example.com`;
    const { id, token } = await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      await sweepPendingDomains(tx, {
        target: TARGET, minIntervalSeconds: 0,
        resolver: resolver({ [`_mamal.${host}`]: { txt: [token] }, [host]: { cname: [TARGET] } }),
      });

      /*
       * Now DNS answers nothing — a resolver hiccup. The sweep must not touch
       * it: taking a customer's links down because one lookup failed is far
       * worse than serving a hostname somebody stopped pointing at us, which is
       * a 404 from us rather than an outage.
       */
      const after = await sweepPendingDomains(tx, {
        target: TARGET, minIntervalSeconds: 0, resolver: resolver({}),
      });
      expect(after.checked, 'a verified domain is not even claimed').toBe(0);

      const [row] = await tx.execute<{ verified_at: string | null }>(sql`
        select verified_at from custom_domains where id = ${id}`);
      expect(row!.verified_at).not.toBeNull();
    }, { db });
  });

  it('checks one domain on demand and says what is still missing', async () => {
    const host = `${tag}-now.example.com`;
    const { id, token } = await addDomain(host);

    await withWorkspace(ws, async (tx) => {
      const waiting = await checkOneDomain(tx, {
        workspaceId: ws, domainId: id, target: TARGET,
        resolver: resolver({ [host]: { cname: [TARGET] } }),
      });
      expect(waiting).toMatchObject({ ok: true, owned: false, routed: true });

      const done = await checkOneDomain(tx, {
        workspaceId: ws, domainId: id, target: TARGET,
        resolver: resolver({ [`_mamal.${host}`]: { txt: [token] }, [host]: { cname: [TARGET] } }),
      });
      expect(done).toMatchObject({ ok: true, owned: true, routed: true, nextStep: null });
    }, { db });
  });

  it('answers not_found for another workspace’s domain', async () => {
    const { id } = await addDomain(`${tag}-other.example.com`);
    await withWorkspace(ws, async (tx) => {
      const result = await checkOneDomain(tx, {
        workspaceId: '00000000-0000-7000-8000-000000000000',
        domainId: id, target: TARGET, resolver: resolver({}),
      });
      expect(result).toEqual({ ok: false, reason: 'not_found' });
    }, { db });
  });
});
