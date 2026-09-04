import { sql, type SQL } from 'drizzle-orm';

/**
 * Drizzle's `sql` template renders a JS array as a parenthesised value list
 * (`($1, $2)`), which is what an `IN` clause wants and what a `text[]` column
 * emphatically does not. These build the right thing, parameterised.
 *
 * Learned the hard way three separate times: an events insert, a claim-and-
 * enqueue update, and an onboarding upsert.
 */

/** A real `text[]`, safe when empty. */
export function textArray(values: readonly string[]): SQL {
  if (values.length === 0) return sql`array[]::text[]`;
  return sql`array[${sql.join(
    values.map((v) => sql`${v}`),
    sql`, `,
  )}]::text[]`;
}

/** The same, for a `uuid[]` — `= any(...)` will not compare uuid against text. */
export function uuidArray(values: readonly string[]): SQL {
  if (values.length === 0) return sql`array[]::uuid[]`;
  return sql`array[${sql.join(
    values.map((v) => sql`${v}`),
    sql`, `,
  )}]::uuid[]`;
}

/** A parameterised `IN (…)` list. Returns a never-matching predicate when empty. */
export function inList(values: readonly (string | number)[]): SQL {
  if (values.length === 0) return sql`null`;
  return sql.join(
    values.map((v) => sql`${v}`),
    sql`, `,
  );
}

/** postgres.js cannot serialise a Date as a bare parameter in raw SQL. */
export function ts(date: Date): SQL {
  return sql`${date.toISOString()}::timestamptz`;
}
