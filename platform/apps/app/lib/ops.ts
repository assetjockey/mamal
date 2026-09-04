import { z } from 'zod';
import type { WorkspaceScopedDb } from '@mamal/db';

/**
 * The operation contract shared by REST and MCP.
 *
 * Extracted from `audit-ops.ts` when Confirm needed the same machinery. It is
 * transport-agnostic by design: an operation declares what it needs, what scope
 * authorises it, and how to run it — and each transport only translates.
 */

export const MAX_LIMIT = 100;

/*
 * `limit` clamps rather than rejects, and clamps *before* validation so the
 * emitted JSON Schema can still advertise a real `maximum`. A trailing
 * `.transform()` would clamp correctly but describe itself as unbounded, and a
 * schema that lies to an agent is worse than one that constrains.
 */
export const limit = z.preprocess(
  (v) => {
    if (v === undefined || v === null || v === '') return 25;
    const n = Math.floor(Number(v));
    return Number.isFinite(n) && n > 0 ? Math.min(n, MAX_LIMIT) : 25;
  },
  z.number().int().positive().max(MAX_LIMIT),
);

export const cursor = z.uuid().optional();

export type OpDef<I extends z.ZodTypeAny> = {
  name: string;
  scope: string;
  description: string;
  input: I;
  /** Safe writes are exposed to MCP; destructive ones never are. */
  readOnly: boolean;
  run: (tx: WorkspaceScopedDb, workspaceId: string, input: z.infer<I>) => Promise<unknown>;
};

/**
 * An operation with its input type erased.
 *
 * The transports iterate a heterogeneous list and TypeScript cannot correlate
 * `input` with `run` across a union, so `defineOp` closes the generic at the
 * definition site and hands callers a `call` that validates and runs as one
 * step. Nothing downstream can invoke an op with unparsed arguments, because
 * `run` is not on this surface.
 */
export type Op = {
  name: string;
  scope: string;
  description: string;
  readOnly: boolean;
  input: z.ZodTypeAny;
  call: (
    tx: WorkspaceScopedDb,
    workspaceId: string,
    rawArgs: unknown,
  ) => Promise<{ ok: true; value: unknown } | { ok: false; issues: string }>;
};

export function defineOp<I extends z.ZodTypeAny>(def: OpDef<I>): Op {
  return {
    name: def.name,
    scope: def.scope,
    description: def.description,
    readOnly: def.readOnly,
    input: def.input,
    call: async (tx, workspaceId, rawArgs) => {
      const parsed = def.input.safeParse(rawArgs);
      if (!parsed.success) {
        // A readable sentence, not a JSON dump of the zod tree. This text goes
        // to an API consumer and to a model, and both act on it.
        const issues = parsed.error.issues
          .map((i) => `${i.path.join('.') || 'body'}: ${i.message}`)
          .join('; ');
        return { ok: false, issues };
      }
      return { ok: true, value: await def.run(tx, workspaceId, parsed.data) };
    },
  };
}
