import { matches, type VisitorContext } from '@mamal/targeting';

/**
 * Resolving a short link to a destination.
 *
 * Pure and synchronous. Everything it needs — the link, its rules, any sticky
 * assignment — is passed in, so the same function runs on the edge worker, in
 * the origin, and in the rules simulator the editor shows. That last one is the
 * point: a simulator that re-implements this would eventually disagree with it,
 * and "what a visitor from Germany on iOS actually sees" would become a guess.
 *
 * The latency budget lives around this function, not in it: no I/O, no
 * allocation beyond the result, no regex compilation outside the targeting
 * module's bounded cache.
 */

export type Variant = { url: string; weight: number; isWinner?: boolean };

export type RuleAction =
  | { type: 'redirect'; destinationUrl: string }
  | { type: 'rotate'; variants: Variant[] }
  | { type: 'block' };

export type Rule = {
  id: string;
  priority: number;
  /** Same grammar as widget targeting — see `packages/targeting`. */
  match: unknown;
  action: RuleAction;
  sticky: boolean;
  isEnabled: boolean;
};

export type Link = {
  id: string;
  kind: string;
  destinationUrl: string | null;
  isEnabled: boolean;
  moderationStatus: string;
  expiresAt: Date | string | null;
  expiresUrl: string | null;
  maxClicks: number | null;
  clicksCount: number;
  passwordHash: string | null;
  settings: {
    forwardQuery?: boolean;
    utm?: Record<string, string>;
    deepLink?: { ios?: string; android?: string; fallback?: string };
    splashPageId?: string;
    sensitiveContent?: boolean;
    cloak?: unknown;
  };
};

export type Outcome =
  | { kind: 'redirect'; url: string; status: 301 | 302; ruleId?: string; variantIndex?: number }
  | { kind: 'password'; linkId: string }
  | { kind: 'splash'; linkId: string; splashPageId: string; url: string }
  | { kind: 'interstitial'; linkId: string; url: string; reason: 'sensitive' }
  | { kind: 'render'; linkId: string; what: 'biolink' | 'vcard' | 'event' | 'transfer' | 'static' }
  | { kind: 'gone'; reason: 'expired' | 'click_limit'; url: string | null }
  | { kind: 'blocked'; reason: 'rule' | 'moderation' | 'disabled' }
  | { kind: 'not_found' };

export type ResolveInput = {
  link: Link;
  rules: Rule[];
  visitor: VisitorContext;
  /** Query string on the incoming request, for forwarding. */
  query?: string;
  /** A variant this visitor was already assigned, keeping a test honest. */
  assignment?: { ruleId: string; variantIndex: number } | null;
  /** Set once the visitor has entered the correct password. */
  passwordVerified?: boolean;
  now?: Date;
};

/* ---------------------------------------------------------------- helpers */

/**
 * Weighted pick from a deterministic hash, not `Math.random()`.
 *
 * Determinism is what makes an assignment reproducible: given the same visitor
 * hash and the same variants, every node picks the same branch, so a sticky
 * assignment can be *recomputed* rather than looked up when the store is cold.
 */
export function pickVariant(variants: Variant[], visitorHash: string): number {
  const usable = variants.filter((v) => (v.weight ?? 0) > 0);
  if (usable.length === 0) return 0;

  const total = usable.reduce((n, v) => n + v.weight, 0);
  // FNV-1a over the hash: small, fast, and no dependency.
  let h = 0x811c9dc5;
  for (let i = 0; i < visitorHash.length; i++) {
    h ^= visitorHash.charCodeAt(i);
    h = Math.imul(h, 0x01000193) >>> 0;
  }
  let point = (h / 0x100000000) * total;

  for (const v of usable) {
    point -= v.weight;
    if (point < 0) return variants.indexOf(v);
  }
  return variants.indexOf(usable[usable.length - 1]!);
}

/**
 * Applies UTM parameters and forwards the incoming query.
 *
 * Order matters: the link's own UTM wins over whatever arrived on the request,
 * because the campaign it belongs to is what the customer is measuring. A
 * malformed destination is returned unchanged rather than throwing — a redirect
 * that 500s is worse than one that goes somewhere slightly wrong.
 */
export function decorate(
  destination: string,
  utm: Record<string, string> | undefined,
  incomingQuery: string | undefined,
  forwardQuery: boolean,
): string {
  if (!utm && !(forwardQuery && incomingQuery)) return destination;
  try {
    const url = new URL(destination);
    if (forwardQuery && incomingQuery) {
      for (const [k, v] of new URLSearchParams(incomingQuery)) {
        if (!url.searchParams.has(k)) url.searchParams.set(k, v);
      }
    }
    for (const [k, v] of Object.entries(utm ?? {})) {
      if (v) url.searchParams.set(k.startsWith('utm_') ? k : `utm_${k}`, v);
    }
    return url.toString();
  } catch {
    return destination;
  }
}

/** Deep links only apply where the app could actually open. */
function deepLinkFor(link: Link, visitor: VisitorContext): string | null {
  const dl = link.settings.deepLink;
  if (!dl) return null;
  if (visitor.os === 'iOS' && dl.ios) return dl.ios;
  if (visitor.os === 'Android' && dl.android) return dl.android;
  return null;
}

/* ---------------------------------------------------------------- resolve */

export function resolve(input: ResolveInput): Outcome {
  const { link, visitor } = input;
  const now = input.now ?? new Date();

  // ---- states that end the request before any rule runs -------------------

  if (link.moderationStatus === 'blocked') return { kind: 'blocked', reason: 'moderation' };
  if (!link.isEnabled) return { kind: 'blocked', reason: 'disabled' };

  if (link.expiresAt) {
    const at = typeof link.expiresAt === 'string' ? Date.parse(link.expiresAt) : link.expiresAt.getTime();
    if (Number.isFinite(at) && at <= now.getTime()) {
      return { kind: 'gone', reason: 'expired', url: link.expiresUrl };
    }
  }

  if (link.maxClicks && link.maxClicks > 0 && link.clicksCount >= link.maxClicks) {
    return { kind: 'gone', reason: 'click_limit', url: link.expiresUrl };
  }

  /*
   * The password gate comes before rules on purpose.
   *
   * A rule can reveal where a link points — a rotation exposes every variant to
   * anyone who reloads enough — so evaluating rules for an unauthenticated
   * visitor would leak exactly what the password exists to protect.
   */
  if (link.passwordHash && !input.passwordVerified) {
    return { kind: 'password', linkId: link.id };
  }

  // ---- rules: first match wins --------------------------------------------

  let destination = link.destinationUrl;
  let ruleId: string | undefined;
  let variantIndex: number | undefined;

  const ordered = input.rules
    .filter((r) => r.isEnabled)
    .sort((a, b) => a.priority - b.priority);

  for (const rule of ordered) {
    if (!matches(rule.match, visitor)) continue;

    if (rule.action.type === 'block') return { kind: 'blocked', reason: 'rule' };

    if (rule.action.type === 'redirect') {
      destination = rule.action.destinationUrl;
      ruleId = rule.id;
      break;
    }

    if (rule.action.type === 'rotate') {
      const variants = rule.action.variants ?? [];
      if (variants.length === 0) continue;

      /*
       * A declared winner ends the test.
       *
       * Without this, concluding an experiment would mean deleting the rule and
       * losing its history — so the winner flag stays and everyone goes to it.
       */
      const winner = variants.findIndex((v) => v.isWinner);
      if (winner >= 0) {
        destination = variants[winner]!.url;
        ruleId = rule.id;
        variantIndex = winner;
        break;
      }

      // A prior assignment for *this* rule wins, so a refresh does not reroll.
      const prior =
        rule.sticky && input.assignment?.ruleId === rule.id
          ? input.assignment.variantIndex
          : null;
      const index =
        prior !== null && prior >= 0 && prior < variants.length
          ? prior
          : pickVariant(variants, visitorHashOf(visitor));

      destination = variants[index]!.url;
      ruleId = rule.id;
      variantIndex = index;
      break;
    }
  }

  // ---- links that render rather than redirect -----------------------------

  if (link.kind === 'biolink') return { kind: 'render', linkId: link.id, what: 'biolink' };
  if (link.kind === 'vcard') return { kind: 'render', linkId: link.id, what: 'vcard' };
  if (link.kind === 'event') return { kind: 'render', linkId: link.id, what: 'event' };
  if (link.kind === 'transfer' || link.kind === 'file') {
    return { kind: 'render', linkId: link.id, what: 'transfer' };
  }
  if (link.kind === 'static') return { kind: 'render', linkId: link.id, what: 'static' };

  if (!destination) return { kind: 'not_found' };

  // ---- decoration ---------------------------------------------------------

  const deep = deepLinkFor(link, visitor);
  const finalUrl = decorate(
    deep ?? destination,
    link.settings.utm,
    input.query,
    link.settings.forwardQuery !== false,
  );

  if (link.settings.sensitiveContent) {
    return { kind: 'interstitial', linkId: link.id, url: finalUrl, reason: 'sensitive' };
  }
  if (link.settings.splashPageId) {
    return {
      kind: 'splash',
      linkId: link.id,
      splashPageId: link.settings.splashPageId,
      url: finalUrl,
    };
  }

  /*
   * 302, never 301.
   *
   * A permanent redirect is cached by the browser forever: change the
   * destination afterwards and everyone who ever followed the old one keeps
   * going to the wrong place, with no way to reach them. Every link here is
   * editable by definition, so none of them are permanent.
   */
  return { kind: 'redirect', url: finalUrl, status: 302, ruleId, variantIndex };
}

/**
 * The bucketing key for a rotation.
 *
 * Read off the context the edge already computed — a daily-rotating salted hash
 * that identifies a session without identifying a person. Falls back to a
 * constant so a visitor with nothing known about them still gets a stable
 * variant within the request rather than a random one.
 */
function visitorHashOf(visitor: VisitorContext & { visitorHash?: string }): string {
  return visitor.visitorHash ?? `${visitor.country ?? ''}|${visitor.device ?? ''}|${visitor.browser ?? ''}`;
}
