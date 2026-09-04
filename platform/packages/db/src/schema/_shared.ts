import { sql } from 'drizzle-orm';
import { timestamp, uuid, jsonb } from 'drizzle-orm/pg-core';

/**
 * Pure helpers only. This module must never import another schema file:
 * table modules import `workspaces`/`projects` directly from ./tenancy.ts,
 * which keeps the ESM graph acyclic.
 */

/** uuidv7 — time-ordered, non-enumerable, mintable at the edge before the DB sees it. */
export const primaryId = () =>
  uuid()
    .primaryKey()
    .default(sql`uuidv7()`);

export const timestamps = {
  createdAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
  updatedAt: timestamp({ withTimezone: true }).notNull().defaultNow(),
};

export const softDelete = {
  deletedAt: timestamp({ withTimezone: true }),
};

export type Json = Record<string, unknown>;

/** Typed jsonb column. */
export const json = <T = Json>() => jsonb().$type<T>();

/**
 * jsonb defaults. `.default([])` does not typecheck against a `$type`d column,
 * so array/object defaults are expressed as SQL literals.
 */
export const emptyJsonArray = sql`'[]'::jsonb`;
export const emptyJsonObject = sql`'{}'::jsonb`;
