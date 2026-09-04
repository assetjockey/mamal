import { createHash, randomBytes, timingSafeEqual } from 'node:crypto';
import { sql } from 'drizzle-orm';
import { textArray, uuidArray, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { mint, coreUrn, relate } from '@mamal/resources';
import { blockDef, qrDef, validateBarcode, encodePayload } from '@mamal/link-catalog';
import type { Link, Rule } from '@mamal/redirect';
import { validateTargeting } from '@mamal/targeting';

export class LinkNotAllowed extends Error {
  constructor(
    readonly reason: string,
    message: string,
  ) {
    super(message);
    this.name = 'LinkNotAllowed';
  }
}

/**
 * Aliases are drawn from an unambiguous alphabet.
 *
 * No `0/O`, no `1/l/I`. A short link's whole job is to survive being read off a
 * poster, dictated over a phone, or typed from a business card, and the
 * characters people confuse are the ones that turn a working link into a 404
 * the customer blames us for.
 */
const ALPHABET = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';

export function generateAlias(length = 7): string {
  const bytes = randomBytes(length);
  let out = '';
  for (let i = 0; i < length; i++) out += ALPHABET[bytes[i]! % ALPHABET.length];
  return out;
}

/**
 * Aliases we will not hand out.
 *
 * These are paths the public app already serves, plus the handful that would be
 * actively dangerous in a link somebody trusts. A customer who asks for
 * `/login` is not doing anything wrong; they just cannot have it.
 */
const RESERVED = new Set([
  'api', 'app', 'admin', 'login', 'logout', 'signup', 'signin', 'register',
  'account', 'settings', 'billing', 'pricing', 'blog', 'docs', 'help',
  'support', 'status', 'assets', 'static', 'public', 'favicon.ico',
  'robots.txt', 'sitemap.xml', 'well-known', 'report', 'abuse', 'privacy',
  'terms', 'unsubscribe', 'c', 'p', 'q', 't', 'd',
]);

const ALIAS_SHAPE = /^[A-Za-z0-9][A-Za-z0-9_-]{0,254}$/;

export function validateAlias(alias: string): { ok: true } | { ok: false; message: string } {
  if (!ALIAS_SHAPE.test(alias)) {
    return { ok: false, message: 'Use letters, numbers, hyphens and underscores, starting with a letter or number.' };
  }
  if (RESERVED.has(alias.toLowerCase())) {
    return { ok: false, message: `“${alias}” is reserved by the platform. Try another.` };
  }
  return { ok: true };
}

/* ---------------------------------------------------------------- passwords */

/**
 * Password hashing for a link or a transfer gate.
 *
 * Salted SHA-256, with the salt inline so there is no second column to forget
 * to migrate. Deliberately *not* a password hash in the Argon sense: these are
 * share codes, typically four words out of the sender's own message, that live
 * for days and protect one URL. What they need is that a leaked table cannot be
 * reversed with a wordlist in seconds, and that comparison is constant-time —
 * the verifying endpoint is public by design.
 *
 * One implementation, used by both links and transfers, because two would
 * eventually disagree about the format and one of them would stop verifying.
 */
export function hashPassword(password: string): string {
  const salt = randomBytes(16);
  const hash = createHash('sha256').update(salt).update(password, 'utf8').digest();
  return `s1:${salt.toString('base64url')}:${hash.toString('base64url')}`;
}

/** A null hash means no gate, so anything passes. */
export function verifyPassword(stored: string | null, supplied: string): boolean {
  if (!stored) return true;
  const [version, saltB64, hashB64] = stored.split(':');
  if (version !== 's1' || !saltB64 || !hashB64) return false;
  const expected = Buffer.from(hashB64, 'base64url');
  const actual = createHash('sha256')
    .update(Buffer.from(saltB64, 'base64url'))
    .update(supplied, 'utf8')
    .digest();
  // Length-varying compare leaks the prefix under repeated guessing.
  return expected.length === actual.length && timingSafeEqual(expected, actual);
}

export async function setLinkPassword(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; linkId: string; password: string | null },
): Promise<void> {
  await tx.execute(sql`
    update links
       set password_hash = ${opts.password ? hashPassword(opts.password) : null}, updated_at = now()
     where id = ${opts.linkId} and workspace_id = ${opts.workspaceId}`);
}

/**
 * The public URL for an alias.
 *
 * `SHORT_LINK_BASE` is the short domain in production. On this origin the
 * redirect lives at `/r/:alias` — a bare `/:alias` would collide with every
 * dashboard route, and the two only stop sharing a host once the edge worker
 * lands. Composing it in one place means the value a customer copies, the value
 * `link.shorten` returns to Market, and the value in a QR payload are the same
 * string.
 */
export function shortUrl(alias: string, base = process.env.SHORT_LINK_BASE): string {
  const root = (base ?? 'http://localhost:3000/r').replace(/\/+$/, '');
  return `${root}/${alias}`;
}

/* -------------------------------------------------------------------- gate */

/** One shape for every "have they got room for another one of these" check. */
async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  countSql: ReturnType<typeof sql>,
  quantity = 1,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const [counted] = await tx.execute<{ count: number }>(countSql);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, quantity);
  if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);
}

/* ------------------------------------------------------------------- links */

export type CreateLinkInput = {
  workspaceId: string;
  projectId: string;
  kind?: string;
  alias?: string;
  destinationUrl?: string | null;
  title?: string;
  customDomainId?: string | null;
  folderId?: string | null;
  campaign?: string;
  tags?: string[];
  utm?: Record<string, string>;
  settings?: Record<string, unknown>;
  sourceUrn?: string;
};

export async function createLink(tx: WorkspaceScopedDb, input: CreateLinkInput): Promise<{
  id: string;
  alias: string;
}> {
  await requireHeadroom(
    tx,
    input.workspaceId,
    'link.links',
    sql`select count(*)::int as count from links
         where workspace_id = ${input.workspaceId} and deleted_at is null`,
  );

  if (input.alias) {
    const shape = validateAlias(input.alias);
    if (!shape.ok) throw new LinkNotAllowed('invalid_alias', shape.message);
  }

  const settings = { ...(input.settings ?? {}) };
  if (input.utm && Object.keys(input.utm).length > 0) settings.utm = input.utm;

  /*
   * `on conflict do nothing returning id`, not a catch, and not a pre-check.
   *
   * A SELECT-then-INSERT is a race: two requests both find `abc1234` free and
   * one of them loses. Catching the violation instead does not work either —
   * in Postgres a constraint error **aborts the whole transaction**, so the
   * retry runs against a dead connection and a collision that should have been
   * invisible becomes a 500. (Verified: after the failed insert, even
   * `select 1` fails.)
   *
   * Conflicting on a partial unique index returns zero rows and leaves the
   * transaction perfectly healthy, so a generated alias simply tries again and
   * only a *supplied* one is reported back to the person who chose it.
   */
  const supplied = Boolean(input.alias);
  for (let attempt = 0; attempt < 6; attempt++) {
    const alias = input.alias ?? generateAlias();

    const [row] = await tx.execute<{ id: string }>(sql`
      insert into links
        (workspace_id, project_id, custom_domain_id, folder_id, kind, alias,
         destination_url, title, campaign, tags, settings)
      values (${input.workspaceId}, ${input.projectId},
              ${input.customDomainId ?? null}, ${input.folderId ?? null},
              ${input.kind ?? 'short'}, ${alias},
              ${input.destinationUrl ?? null}, ${input.title ?? null},
              ${input.campaign ?? null},
              ${textArray(input.tags ?? [])},
              ${JSON.stringify(settings)}::jsonb)
      on conflict do nothing
      returning id`);

    if (!row) {
      if (supplied) {
        throw new LinkNotAllowed('alias_taken', `“${alias}” is already in use on this domain.`);
      }
      continue;
    }

    const resource = await mint(tx, {
      workspaceId: input.workspaceId,
      projectId: input.projectId,
      tool: 'link',
      type: 'link',
      externalId: row.id,
      label: input.title ?? alias,
    });

    if (input.sourceUrn) {
      await relate(tx, {
        workspaceId: input.workspaceId,
        from: input.sourceUrn,
        to: resource.urn,
        relation: 'shortens',
        createdBy: 'system',
      });
    }

    return { id: row.id, alias };
  }

  // Six collisions on a 54^7 space means something is wrong with the generator,
  // not with the customer's luck. Say so rather than looping forever.
  throw new LinkNotAllowed('alias_exhausted', 'Could not find a free alias. Please try again.');
}

/**
 * Loads everything the resolver needs, in one round trip.
 *
 * Shaped as the `@mamal/redirect` types rather than as rows: the same function
 * feeds the edge, the origin, and the "what a visitor from Germany on iOS sees"
 * simulator in the editor, so all three agree by construction.
 */
export async function loadForResolve(
  tx: WorkspaceScopedDb,
  opts: { alias: string; customDomainId?: string | null },
): Promise<{ link: Link; rules: Rule[]; workspaceId: string } | null> {
  const [row] = await tx.execute<{
    id: string; workspace_id: string; kind: string; destination_url: string | null;
    is_enabled: boolean; moderation_status: string; expires_at: string | null;
    expires_url: string | null; max_clicks: number | null; clicks_count: number;
    password_hash: string | null; settings: Record<string, unknown>;
  }>(sql`
    select id, workspace_id, kind, destination_url, is_enabled, moderation_status,
           expires_at, expires_url, max_clicks, clicks_count, password_hash, settings
      from links
     where alias = ${opts.alias}
       and custom_domain_id is not distinct from ${opts.customDomainId ?? null}
       and deleted_at is null`);
  if (!row) return null;

  const rules = await tx.execute<{
    id: string; priority: number; match: unknown; action: unknown;
    sticky: boolean; is_enabled: boolean;
  }>(sql`
    select id, priority, match, action, sticky, is_enabled
      from link_rules where link_id = ${row.id} order by priority`);

  return {
    workspaceId: row.workspace_id,
    link: {
      id: row.id,
      kind: row.kind,
      destinationUrl: row.destination_url,
      isEnabled: row.is_enabled,
      moderationStatus: row.moderation_status,
      expiresAt: row.expires_at,
      expiresUrl: row.expires_url,
      maxClicks: row.max_clicks,
      clicksCount: Number(row.clicks_count),
      passwordHash: row.password_hash,
      settings: row.settings as Link['settings'],
    },
    rules: rules.map((r) => ({
      id: r.id,
      priority: r.priority,
      match: r.match,
      action: r.action as Rule['action'],
      sticky: r.sticky,
      isEnabled: r.is_enabled,
    })),
  };
}

/**
 * Records a click.
 *
 * The counter is denormalised and advanced with `+ 1` in the database rather
 * than read-modify-written in the app, because two concurrent clicks that both
 * read 41 would both write 42. The authoritative count lives in the fact table;
 * this one exists so the click *limit* can be enforced without querying it.
 */
export async function recordClick(
  tx: WorkspaceScopedDb,
  opts: { linkId: string },
): Promise<number> {
  const [row] = await tx.execute<{ clicks_count: number }>(sql`
    update links
       set clicks_count = clicks_count + 1, last_clicked_at = now()
     where id = ${opts.linkId}
    returning clicks_count`);
  return Number(row?.clicks_count ?? 0);
}

/* ------------------------------------------------------------------- rules */

export async function setRules(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; linkId: string; rules: Omit<Rule, 'id'>[] },
): Promise<void> {
  const ctx = await loadContext(tx, opts.workspaceId, 'link.rules');
  if (ctx) {
    const decision = resolveEntitlement({ ...ctx, used: 0 }, 1);
    if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);
  }

  for (const [i, rule] of opts.rules.entries()) {
    /*
     * A malformed match is refused here, not tolerated at the edge.
     *
     * `matches` fails closed on an unknown *condition*, but a group whose shape
     * it does not recognise has no conditions to fail — so it matches everyone.
     * On a widget that is a visible mistake; on a redirect it silently sends
     * the whole world to the German site, and the only symptom is traffic
     * arriving somewhere it should not.
     */
    const problems = validateTargeting(rule.match);
    if (problems.length > 0) {
      throw new LinkNotAllowed(
        'invalid_match',
        `Rule ${i + 1}: ${problems.map((p) => `${p.path || 'match'} — ${p.message}`).join('; ')}`,
      );
    }

    if (rule.action.type === 'rotate') {
      const total = rule.action.variants.reduce((n, v) => n + (v.weight ?? 0), 0);
      if (total <= 0) {
        throw new LinkNotAllowed('empty_rotation', 'A rotation needs at least one variant with weight.');
      }
      const winners = rule.action.variants.filter((v) => v.isWinner).length;
      if (winners > 1) {
        throw new LinkNotAllowed('two_winners', 'Only one variant can be the winner.');
      }
    }
  }

  /*
   * Replace wholesale, in one transaction.
   *
   * The editor sends the whole ordered list because that is what it edits.
   * Diffing would mean the caller has to know rule ids it never chose, and a
   * partial apply would leave a link routing on half of a new configuration.
   */
  await tx.execute(sql`delete from link_rules where link_id = ${opts.linkId}`);
  for (const [i, rule] of opts.rules.entries()) {
    await tx.execute(sql`
      insert into link_rules (workspace_id, link_id, priority, match, action, sticky, is_enabled)
      values (${opts.workspaceId}, ${opts.linkId}, ${rule.priority ?? i},
              ${JSON.stringify(rule.match ?? {})}::jsonb,
              ${JSON.stringify(rule.action)}::jsonb,
              ${rule.sticky ?? true}, ${rule.isEnabled ?? true})`);
  }
}

/**
 * Remembers which variant a visitor was shown.
 *
 * Without this a rotation is not a test: somebody who lands on B and refreshes
 * onto A has been counted twice and converted once. `on conflict do nothing`
 * rather than an upsert — the *first* assignment is the honest one, and a
 * concurrent second request must not be able to move somebody between arms.
 */
export async function rememberAssignment(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; linkId: string; ruleId: string;
    visitorHash: string; variantIndex: number; days?: number;
  },
): Promise<void> {
  await tx.execute(sql`
    insert into link_assignments
      (workspace_id, link_id, rule_id, visitor_hash, variant_index, expires_at)
    values (${opts.workspaceId}, ${opts.linkId}, ${opts.ruleId}, ${opts.visitorHash},
            ${opts.variantIndex}, now() + (${opts.days ?? 30} * interval '1 day'))
    on conflict on constraint link_assignments_visitor do nothing`);
}

export async function loadAssignment(
  tx: WorkspaceScopedDb,
  opts: { ruleIds: string[]; visitorHash: string },
): Promise<{ ruleId: string; variantIndex: number } | null> {
  if (opts.ruleIds.length === 0) return null;
  const [row] = await tx.execute<{ rule_id: string; variant_index: number }>(sql`
    select rule_id, variant_index from link_assignments
     where visitor_hash = ${opts.visitorHash}
       and rule_id = any(${uuidArray(opts.ruleIds)})
       and expires_at > now()
     limit 1`);
  return row ? { ruleId: row.rule_id, variantIndex: row.variant_index } : null;
}

/* --------------------------------------------------------------- bio pages */

export async function createBioPage(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; alias?: string; title: string; template?: string },
): Promise<{ pageId: string; linkId: string; alias: string }> {
  await requireHeadroom(
    tx,
    opts.workspaceId,
    'link.bio_pages',
    sql`select count(*)::int as count from bio_pages where workspace_id = ${opts.workspaceId}`,
  );

  const link = await createLink(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    kind: 'biolink',
    alias: opts.alias,
    title: opts.title,
  });

  const [page] = await tx.execute<{ id: string }>(sql`
    insert into bio_pages (workspace_id, link_id, template)
    values (${opts.workspaceId}, ${link.id}, ${opts.template ?? 'plain'})
    returning id`);

  await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'link',
    type: 'bio_page',
    externalId: page!.id,
    label: opts.title,
  });

  return { pageId: page!.id, linkId: link.id, alias: link.alias };
}

/**
 * Adds a block, validated against its catalogue entry.
 *
 * Same contract as Confirm's widgets: the editor form is generated from the
 * schema, the renderer reads what it produced, and a write that would not
 * render is refused here rather than discovered by a visitor.
 */
export async function addBlock(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; pageId: string; type: string;
    settings?: unknown; sortOrder?: number;
  },
): Promise<string> {
  const def = blockDef(opts.type);
  if (!def) throw new LinkNotAllowed('unknown_type', `No block type “${opts.type}”.`);

  const parsed = def.settings.safeParse(opts.settings ?? def.defaults);
  if (!parsed.success) {
    throw new LinkNotAllowed(
      'invalid_settings',
      parsed.error.issues.map((i) => `${i.path.join('.') || 'settings'}: ${i.message}`).join('; '),
    );
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into bio_blocks (workspace_id, page_id, type, settings, sort_order)
    values (${opts.workspaceId}, ${opts.pageId}, ${opts.type},
            ${JSON.stringify(parsed.data)}::jsonb,
            ${opts.sortOrder ?? sql`(select coalesce(max(sort_order), -1) + 1
                                       from bio_blocks where page_id = ${opts.pageId})`})
    returning id`);
  return row!.id;
}

/**
 * The blocks a visitor should see right now.
 *
 * Scheduling is applied in the query, not in the renderer: a block outside its
 * window must not reach the browser at all, or "schedule a launch banner" would
 * mean shipping the announcement to anyone who reads the page source early.
 */
export async function visibleBlocks(
  tx: WorkspaceScopedDb,
  pageId: string,
): Promise<{ id: string; type: string; family: string; settings: Record<string, unknown> }[]> {
  const rows = await tx.execute<{ id: string; type: string; settings: Record<string, unknown> }>(sql`
    select id, type, settings from bio_blocks
     where page_id = ${pageId}
       and is_enabled
       and (starts_at is null or starts_at <= now())
       and (ends_at is null or ends_at >= now())
     order by sort_order, created_at`);

  // A block whose type has left the catalogue is skipped, not shipped: the
  // renderer would not know which family to draw it as.
  return rows.flatMap((r) => {
    const def = blockDef(r.type);
    return def ? [{ id: r.id, type: r.type, family: def.family, settings: r.settings }] : [];
  });
}

/* -------------------------------------------------------------- QR and bar */

export async function createQrCode(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; projectId: string; type: string; name: string;
    payload?: Record<string, unknown>; style?: Record<string, unknown>;
    destinationUrl?: string; batchId?: string; sourceUrn?: string;
  },
): Promise<{ id: string; linkId: string | null; encoded: string | null }> {
  const def = qrDef(opts.type);
  if (!def) throw new LinkNotAllowed('unknown_type', `No QR type “${opts.type}”.`);

  await requireHeadroom(
    tx,
    opts.workspaceId,
    'link.qr_codes',
    sql`select count(*)::int as count from qr_codes
         where workspace_id = ${opts.workspaceId} and deleted_at is null`,
  );

  const payload = opts.payload ?? {};
  const parsed = def.payload.safeParse(payload);
  if (!parsed.success) {
    throw new LinkNotAllowed(
      'invalid_payload',
      parsed.error.issues.map((i) => `${i.path.join('.') || 'payload'}: ${i.message}`).join('; '),
    );
  }

  /*
   * A dynamic code gets a link; a static one does not.
   *
   * This is the whole commercial distinction and it is decided here, once. A
   * code that resolves through a link can be re-pointed after ten thousand
   * posters are printed; a static one is fixed forever, which is right for wifi
   * credentials and fatal for a campaign URL.
   */
  let linkId: string | null = null;
  if (def.addressing !== 'static') {
    const link = await createLink(tx, {
      workspaceId: opts.workspaceId,
      projectId: opts.projectId,
      kind: 'qr',
      destinationUrl: opts.destinationUrl ?? (payload.url as string | undefined) ?? null,
      title: opts.name,
      sourceUrn: opts.sourceUrn,
    });
    linkId = link.id;
  }

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into qr_codes (workspace_id, project_id, link_id, type, name, payload, style, batch_id)
    values (${opts.workspaceId}, ${opts.projectId}, ${linkId}, ${opts.type}, ${opts.name},
            ${JSON.stringify(parsed.data)}::jsonb,
            ${JSON.stringify(opts.style ?? {})}::jsonb,
            ${opts.batchId ?? null})
    returning id`);

  const resource = await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'link',
    type: 'qr_code',
    externalId: row!.id,
    label: opts.name,
  });

  if (linkId) {
    await relate(tx, {
      workspaceId: opts.workspaceId,
      from: resource.urn,
      to: coreUrn.link(linkId),
      relation: 'shortens',
      createdBy: 'system',
    });
  }

  return {
    id: row!.id,
    linkId,
    encoded: encodePayload(opts.type, payload),
  };
}

export async function createBarcode(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string; projectId: string; symbology: string;
    value: string; style?: Record<string, unknown>;
  },
): Promise<string> {
  const check = validateBarcode(opts.symbology, opts.value);
  if (!check.ok) throw new LinkNotAllowed('invalid_barcode', check.reason);

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into barcodes (workspace_id, project_id, symbology, value, style)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.symbology}, ${opts.value},
            ${JSON.stringify(opts.style ?? {})}::jsonb)
    returning id`);
  return row!.id;
}

/* ------------------------------------------------------------------- abuse */

/**
 * Files an abuse report.
 *
 * The reporter is a stranger who followed a link, so there is no user to
 * attribute it to and nothing here is authenticated. Two consequences: the
 * report is rate-limited by the caller, and it never reveals whether the link
 * exists — an unknown alias records nothing and answers the same way.
 */
export async function reportAbuse(
  tx: WorkspaceScopedDb,
  opts: { linkId: string; workspaceId: string; reason: string; detail?: string; reporterEmail?: string },
): Promise<string> {
  const [row] = await tx.execute<{ id: string }>(sql`
    insert into abuse_reports (workspace_id, link_id, reason, detail, reporter_email)
    values (${opts.workspaceId}, ${opts.linkId}, ${opts.reason},
            ${opts.detail ?? null}, ${opts.reporterEmail ?? null})
    returning id`);
  return row!.id;
}
