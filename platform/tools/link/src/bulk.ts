import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { generateAlias, shortUrl, validateAlias, LinkNotAllowed } from './service.ts';

/**
 * Bulk link creation from a CSV.
 *
 * Ten thousand rows is the stated target, and it is why this does not simply
 * loop over `createLink`: that is ten thousand round trips, ten thousand
 * `mint()` calls and ten thousand entitlement checks, which turns a paste into
 * a coffee break. Here the whole file is validated first, the allowance is
 * checked once for the whole batch, and the rows go in as multi-row inserts.
 *
 * **Nothing is created unless the file is understood.** A partial import is the
 * worst outcome: the customer cannot tell which half landed, and re-running
 * duplicates whatever did. Validation is complete before the first insert, and
 * the insert is one transaction.
 */

export type BulkRow = {
  line: number;
  url: string;
  alias?: string;
  title?: string;
  campaign?: string;
  tags?: string[];
  utm?: Record<string, string>;
};

export type BulkProblem = { line: number; column: string; message: string };

export type BulkPlan = {
  rows: BulkRow[];
  problems: BulkProblem[];
  /** Aliases the file asks for that are already taken. */
  conflicts: string[];
};

export type BulkResult = {
  created: { alias: string; url: string; destination: string }[];
  problems: BulkProblem[];
};

/** Header names we understand, and the aliases people actually type. */
const COLUMNS: Record<string, keyof BulkRow | 'utm_source' | 'utm_medium' | 'utm_campaign' | 'utm_term' | 'utm_content'> = {
  url: 'url', destination: 'url', link: 'url', 'destination url': 'url', target: 'url',
  alias: 'alias', slug: 'alias', short: 'alias', 'short link': 'alias',
  title: 'title', name: 'title', label: 'title',
  campaign: 'campaign',
  tags: 'tags',
  utm_source: 'utm_source', utm_medium: 'utm_medium', utm_campaign: 'utm_campaign',
  utm_term: 'utm_term', utm_content: 'utm_content',
};

export const MAX_BULK_ROWS = 10_000;

/* --------------------------------------------------------------- parsing */

/**
 * A CSV parser, not a `split(',')`.
 *
 * Destination URLs contain commas, titles contain quotes, and a spreadsheet
 * exports both quoted. Splitting on commas mangles roughly one row in twenty of
 * real-world data, and the customer discovers it after the import.
 */
export function parseCsv(text: string): string[][] {
  const rows: string[][] = [];
  let row: string[] = [];
  let field = '';
  let quoted = false;

  // Strip a UTF-8 BOM: Excel writes one, and it otherwise becomes part of the
  // first header name, so `url` is not recognised and every row fails.
  const input = text.replace(/^﻿/, '');

  for (let i = 0; i < input.length; i++) {
    const c = input[i]!;

    if (quoted) {
      if (c === '"') {
        // A doubled quote inside a quoted field is a literal quote.
        if (input[i + 1] === '"') { field += '"'; i++; }
        else quoted = false;
      } else field += c;
      continue;
    }

    if (c === '"' && field === '') { quoted = true; continue; }
    if (c === ',') { row.push(field); field = ''; continue; }
    if (c === '\r') continue;
    if (c === '\n') { row.push(field); rows.push(row); row = []; field = ''; continue; }
    field += c;
  }

  if (field !== '' || row.length > 0) { row.push(field); rows.push(row); }
  return rows.filter((r) => r.some((cell) => cell.trim() !== ''));
}

/**
 * Turns a CSV into rows we would accept, and a list of everything wrong.
 *
 * Every problem is reported, not just the first: somebody fixing a 10,000-row
 * export needs the whole list, and returning one at a time turns an import into
 * an afternoon.
 */
export function planBulk(text: string): BulkPlan {
  const table = parseCsv(text);
  const problems: BulkProblem[] = [];

  if (table.length === 0) {
    return { rows: [], problems: [{ line: 0, column: 'file', message: 'The file is empty.' }], conflicts: [] };
  }

  const header = table[0]!.map((h) => h.trim().toLowerCase());
  const mapped = header.map((h) => COLUMNS[h]);
  if (!mapped.includes('url')) {
    return {
      rows: [],
      problems: [{
        line: 1,
        column: 'header',
        message: `No destination column. Name one of them ${Object.keys(COLUMNS).filter((k) => COLUMNS[k] === 'url').map((k) => `"${k}"`).join(', ')}.`,
      }],
      conflicts: [],
    };
  }

  const body = table.slice(1);
  if (body.length > MAX_BULK_ROWS) {
    problems.push({
      line: MAX_BULK_ROWS + 2,
      column: 'file',
      message: `${body.length.toLocaleString()} rows is more than the ${MAX_BULK_ROWS.toLocaleString()} we import at once. Split the file.`,
    });
  }

  const rows: BulkRow[] = [];
  const seenAliases = new Map<string, number>();

  body.slice(0, MAX_BULK_ROWS).forEach((cells, index) => {
    const line = index + 2; // 1-based, and the header is line 1
    const row: BulkRow = { line, url: '' };
    const utm: Record<string, string> = {};

    mapped.forEach((column, i) => {
      const value = (cells[i] ?? '').trim();
      if (!column || !value) return;
      if (column === 'url') row.url = value;
      else if (column === 'alias') row.alias = value;
      else if (column === 'title') row.title = value;
      else if (column === 'campaign') row.campaign = value;
      else if (column === 'tags') row.tags = value.split(/[;|]/).map((t) => t.trim()).filter(Boolean);
      else utm[column.replace('utm_', '')] = value;
    });

    if (Object.keys(utm).length > 0) row.utm = utm;

    if (!row.url) {
      problems.push({ line, column: 'url', message: 'No destination.' });
      return;
    }
    try {
      const parsed = new URL(row.url);
      if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
        problems.push({ line, column: 'url', message: `“${parsed.protocol}” is not a web address.` });
        return;
      }
    } catch {
      problems.push({ line, column: 'url', message: `“${row.url}” is not a URL.` });
      return;
    }

    if (row.alias) {
      const shape = validateAlias(row.alias);
      if (!shape.ok) {
        problems.push({ line, column: 'alias', message: shape.message });
        return;
      }
      // A file that asks for the same alias twice is a mistake in the file, and
      // saying so beats importing one of them and silently dropping the other.
      const earlier = seenAliases.get(row.alias.toLowerCase());
      if (earlier) {
        problems.push({ line, column: 'alias', message: `“${row.alias}” is also on line ${earlier}.` });
        return;
      }
      seenAliases.set(row.alias.toLowerCase(), line);
    }

    rows.push(row);
  });

  return { rows, problems, conflicts: [] };
}

/* ------------------------------------------------------------- importing */

export async function importLinks(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    customDomainId?: string | null;
    folderId?: string | null;
    csv: string;
    /** Report what would happen and change nothing. */
    dryRun?: boolean;
  },
): Promise<BulkResult> {
  const plan = planBulk(opts.csv);
  if (plan.rows.length === 0) return { created: [], problems: plan.problems };

  /*
   * Aliases already taken are found in one query, not one per row.
   *
   * Ten thousand `select` round trips is the difference between an import that
   * takes two seconds and one that takes four minutes — and the customer is
   * watching a spinner for both.
   */
  const wanted = plan.rows.map((r) => r.alias).filter((a): a is string => Boolean(a));
  const taken = wanted.length
    ? await tx.execute<{ alias: string }>(sql`
        select alias from links
         where alias = any(${textArray(wanted)})
           and custom_domain_id is not distinct from ${opts.customDomainId ?? null}
           and deleted_at is null`)
    : [];
  const takenSet = new Set(taken.map((t) => t.alias));

  const problems = [...plan.problems];
  const usable = plan.rows.filter((row) => {
    if (row.alias && takenSet.has(row.alias)) {
      problems.push({ line: row.line, column: 'alias', message: `“${row.alias}” is already in use.` });
      return false;
    }
    return true;
  });

  /*
   * The allowance is checked once, for the whole batch.
   *
   * Per row it would pass for the first N and fail for the rest, leaving a
   * partial import — the outcome this whole function exists to avoid. Asking
   * for the full quantity up front means the answer is "all of it or none",
   * and the resolver's message names the shortfall.
   */
  const ctx = await loadContext(tx, opts.workspaceId, 'link.links');
  if (ctx) {
    const [counted] = await tx.execute<{ count: number }>(sql`
      select count(*)::int as count from links
       where workspace_id = ${opts.workspaceId} and deleted_at is null`);
    const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, usable.length);
    if (!decision.allowed) throw new LinkNotAllowed(decision.reason, decision.message);
  }

  if (opts.dryRun || usable.length === 0) {
    return {
      created: usable.map((row) => ({
        alias: row.alias ?? '(generated)',
        url: row.alias ? shortUrl(row.alias) : '(generated)',
        destination: row.url,
      })),
      problems,
    };
  }

  /*
   * Generated aliases are drawn up front and checked as a set.
   *
   * `on conflict do nothing` would silently drop a colliding row from a
   * multi-row insert, and the customer would get 9,997 links from a 10,000-row
   * file with nothing saying which three vanished. Collisions on a 54^7 space
   * are vanishingly rare, so the loop below almost never runs twice — but
   * "almost never" is not "never" at ten thousand rows.
   */
  const aliases = new Map<number, string>();
  const claimed = new Set(wanted);
  for (const row of usable) {
    if (row.alias) { aliases.set(row.line, row.alias); continue; }
    let candidate = generateAlias();
    for (let attempt = 0; claimed.has(candidate) && attempt < 8; attempt++) candidate = generateAlias();
    claimed.add(candidate);
    aliases.set(row.line, candidate);
  }

  const generated = [...aliases.values()].filter((a) => !wanted.includes(a));
  const collided = generated.length
    ? await tx.execute<{ alias: string }>(sql`
        select alias from links
         where alias = any(${textArray(generated)})
           and custom_domain_id is not distinct from ${opts.customDomainId ?? null}
           and deleted_at is null`)
    : [];
  for (const c of collided) {
    for (const [line, alias] of aliases) {
      if (alias === c.alias) aliases.set(line, generateAlias());
    }
  }

  const created: BulkResult['created'] = [];

  // Chunked, because a single insert of ten thousand rows exceeds the driver's
  // parameter limit long before it exceeds Postgres's patience.
  const CHUNK = 500;
  for (let i = 0; i < usable.length; i += CHUNK) {
    const chunk = usable.slice(i, i + CHUNK);
    const values = chunk.map((row) => {
      const settings: Record<string, unknown> = {};
      if (row.utm && Object.keys(row.utm).length > 0) settings.utm = row.utm;
      return sql`(${opts.workspaceId}, ${opts.projectId}, ${opts.customDomainId ?? null},
                  ${opts.folderId ?? null}, 'short', ${aliases.get(row.line)!},
                  ${row.url}, ${row.title ?? null}, ${row.campaign ?? null},
                  ${textArray(row.tags ?? [])}, ${JSON.stringify(settings)}::jsonb)`;
    });

    const rows = await tx.execute<{ alias: string; destination_url: string }>(sql`
      insert into links
        (workspace_id, project_id, custom_domain_id, folder_id, kind, alias,
         destination_url, title, campaign, tags, settings)
      values ${sql.join(values, sql`, `)}
      returning alias, destination_url`);

    for (const row of rows) {
      created.push({ alias: row.alias, url: shortUrl(row.alias), destination: row.destination_url });
    }
  }

  return { created, problems };
}
