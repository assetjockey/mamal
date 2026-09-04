import { promises as dns } from 'node:dns';

/**
 * Verifying a custom domain.
 *
 * Two questions, and they are genuinely different:
 *
 * **Do they control it?** A TXT record at `_mamal.<host>` carrying the token we
 * generated. Nobody can add that without access to the zone, which is the whole
 * proof — and it is why the token is per-domain and random rather than derived
 * from the workspace id, which an attacker could compute.
 *
 * **Does it point at us?** A CNAME to our ingress. Separate because a domain
 * can be proved and not yet routed (DNS is still propagating) or routed and not
 * proved (somebody CNAME'd at us hoping to claim a hostname). Reporting them as
 * one state is how "it says pending and I did everything" happens.
 *
 * Resolution is done with the system resolver and no cache of our own: DNS has
 * a TTL, and re-implementing it here would mean telling a customer their record
 * is missing minutes after they added it.
 */

export type DomainCheck = {
  /** Ownership proved by the TXT token. */
  owned: boolean;
  /** Traffic actually arrives here. */
  routed: boolean;
  /** What to tell the customer to do next, or null when it is done. */
  nextStep: string | null;
  /** Everything found, for the runbook and the admin view. */
  found: { txt: string[]; cname: string[]; a: string[] };
};

export type Resolver = {
  resolveTxt(host: string): Promise<string[][]>;
  resolveCname(host: string): Promise<string[]>;
  resolve4(host: string): Promise<string[]>;
};

/** The real one. Swapped in tests, because DNS is not a unit-testable dependency. */
export const systemResolver: Resolver = {
  resolveTxt: (host) => dns.resolveTxt(host),
  resolveCname: (host) => dns.resolveCname(host),
  resolve4: (host) => dns.resolve4(host),
};

export type VerifyOptions = {
  host: string;
  token: string;
  /** What a correctly-pointed domain should CNAME to. */
  target: string;
  /** Addresses that also count as pointing at us, for apex domains. */
  addresses?: string[];
  resolver?: Resolver;
};

export async function verifyDomain(options: VerifyOptions): Promise<DomainCheck> {
  const resolver = options.resolver ?? systemResolver;

  // Both lookups at once: they are independent, and doing them in sequence
  // doubles the time a customer waits on the slowest resolver in the chain.
  const [txt, cname, a] = await Promise.all([
    // `NXDOMAIN` is an answer, not an error — the record is simply not there
    // yet, which is the normal state for the first ninety seconds.
    resolver.resolveTxt(`_mamal.${options.host}`).catch(() => [] as string[][]),
    resolver.resolveCname(options.host).catch(() => [] as string[]),
    resolver.resolve4(options.host).catch(() => [] as string[]),
  ]);

  // A TXT record arrives as chunks of at most 255 bytes; providers split long
  // values, so the parts are joined before comparing.
  const txtValues = txt.map((chunks) => chunks.join(''));
  const owned = txtValues.some((value) => value.trim() === options.token);

  const target = normalise(options.target);
  const routed =
    cname.some((c) => normalise(c) === target) ||
    (options.addresses ?? []).some((ip) => a.includes(ip));

  return {
    owned,
    routed,
    nextStep: nextStep(owned, routed, options),
    found: { txt: txtValues, cname, a },
  };
}

/**
 * One sentence naming the single next action.
 *
 * Not a list of everything outstanding: somebody watching a spinner needs to
 * know what to do *now*, and "add a TXT record and a CNAME and wait" is how a
 * setup gets half-done.
 */
function nextStep(owned: boolean, routed: boolean, options: VerifyOptions): string | null {
  if (owned && routed) return null;
  if (!owned && !routed) {
    return `Add a TXT record at _mamal.${options.host} with the value ${options.token}, and a CNAME from ${options.host} to ${options.target}.`;
  }
  if (!owned) {
    return `${options.host} points at us, but we cannot prove you own it yet. Add a TXT record at _mamal.${options.host} with the value ${options.token}.`;
  }
  return `Ownership is proved. Now point ${options.host} at ${options.target} with a CNAME.`;
}

/** Trailing dots and case are not differences a DNS answer should be judged on. */
function normalise(host: string): string {
  return host.trim().toLowerCase().replace(/\.$/, '');
}
