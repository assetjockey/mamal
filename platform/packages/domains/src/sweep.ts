import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { verifyDomain, type Resolver } from './verify.ts';

/**
 * The cron that watches pending custom domains.
 *
 * A customer adds a CNAME and then waits — sometimes ninety seconds, sometimes
 * an hour, depending on whose DNS they use. Nothing they do tells us it landed,
 * so something has to look, and it has to keep looking without turning into a
 * resolver flood: `dnsCheckedAt` throttles each domain, and `claimDue` picks up
 * only what is actually waiting.
 *
 * It never *unverifies* a working domain. A resolver hiccup on a live hostname
 * would otherwise take a customer's links down, and the failure mode of leaving
 * a stale verification in place is that we serve a domain somebody stopped
 * pointing at us — which is a 404 from us rather than an outage.
 */

export type SweepResult = {
  checked: number;
  verified: string[];
  stillWaiting: number;
};

export type SweepOptions = {
  target: string;
  addresses?: string[];
  resolver?: Resolver;
  /** How many to look at in one pass. */
  batch?: number;
  /** Don't re-check a domain more often than this. */
  minIntervalSeconds?: number;
  now?: Date;
};

export async function sweepPendingDomains(
  tx: WorkspaceScopedDb,
  options: SweepOptions,
): Promise<SweepResult> {
  const interval = options.minIntervalSeconds ?? 60;

  /*
   * Claim-and-check, with `for update skip locked`.
   *
   * Two schedulers running the same minute must not both resolve the same
   * domain: DNS providers rate-limit, and the second lookup answers nothing the
   * first did not. Claiming here means N schedulers are safe with no leader
   * election, which is the same pattern the monitor sweep uses.
   */
  const due = await tx.execute<{
    id: string; host: string; verification_token: string;
  }>(sql`
    with claimed as (
      select id from custom_domains
       where verified_at is null
         and (dns_checked_at is null
              or dns_checked_at < now() - (${interval} * interval '1 second'))
       order by dns_checked_at nulls first, created_at
       limit ${options.batch ?? 100}
       for update skip locked
    )
    update custom_domains d
       set dns_checked_at = now()
      from claimed
     where d.id = claimed.id
    returning d.id, d.host, d.verification_token`);

  const verified: string[] = [];

  for (const domain of due) {
    const check = await verifyDomain({
      host: domain.host,
      token: domain.verification_token,
      target: options.target,
      addresses: options.addresses,
      resolver: options.resolver,
    });

    if (check.owned && check.routed) {
      /*
       * `ssl_status` goes to `provisioning`, not `active`.
       *
       * The certificate is issued by the edge provider after the hostname is
       * routed, and it takes its own minute or two. Claiming `active` here
       * would put a green tick next to a domain that still serves a TLS
       * warning — the one failure a customer reads as "this product is broken".
       */
      await tx.execute(sql`
        update custom_domains
           set verified_at = now(), dns_status = 'ok', ssl_status = 'provisioning'
         where id = ${domain.id} and verified_at is null`);
      verified.push(domain.host);
    } else {
      await tx.execute(sql`
        update custom_domains
           set dns_status = ${check.owned || check.routed ? 'partial' : 'pending'},
               last_check = ${JSON.stringify({
                 owned: check.owned,
                 routed: check.routed,
                 nextStep: check.nextStep,
                 found: check.found,
                 at: (options.now ?? new Date()).toISOString(),
               })}::jsonb
         where id = ${domain.id}`);
    }
  }

  return { checked: due.length, verified, stillWaiting: due.length - verified.length };
}

/** One domain, now — what the "Check now" button calls. */
export async function checkOneDomain(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; domainId: string } & SweepOptions,
): Promise<{ ok: true; owned: boolean; routed: boolean; nextStep: string | null } | { ok: false; reason: string }> {
  const [domain] = await tx.execute<{ host: string; verification_token: string; verified_at: string | null }>(sql`
    select host, verification_token, verified_at from custom_domains
     where id = ${opts.domainId} and workspace_id = ${opts.workspaceId}`);
  if (!domain) return { ok: false, reason: 'not_found' };
  if (domain.verified_at) return { ok: true, owned: true, routed: true, nextStep: null };

  const check = await verifyDomain({
    host: domain.host,
    token: domain.verification_token,
    target: opts.target,
    addresses: opts.addresses,
    resolver: opts.resolver,
  });

  await tx.execute(sql`
    update custom_domains
       set dns_checked_at = now(),
           dns_status = ${check.owned && check.routed ? 'ok' : check.owned || check.routed ? 'partial' : 'pending'},
           verified_at = ${check.owned && check.routed ? sql`now()` : sql`verified_at`},
           ssl_status = ${check.owned && check.routed ? 'provisioning' : sql`ssl_status`},
           last_check = ${JSON.stringify({
             owned: check.owned, routed: check.routed,
             nextStep: check.nextStep, found: check.found,
             at: (opts.now ?? new Date()).toISOString(),
           })}::jsonb
     where id = ${opts.domainId} and workspace_id = ${opts.workspaceId}`);

  return { ok: true, owned: check.owned, routed: check.routed, nextStep: check.nextStep };
}
