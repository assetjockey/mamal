import { z } from 'zod';
import { verifyApiKey, hasScope, touchApiKey } from '@mamal/auth';
import { withWorkspace } from '@mamal/db';
import { db } from '@/lib/db';
import { AUDIT_OPS } from '@/lib/audit-ops';
import { asRefusal } from '@/lib/api';
import { CONFIRM_OPS } from '@/lib/confirm-ops';
import { LINK_OPS } from '@/lib/link-ops';
import type { Op } from '@/lib/ops';

/**
 * Every tool's operations, in one list.
 *
 * A tool contributes its ops here and gets REST *and* MCP for free. This is the
 * only file that knows about more than one tool — the composition root, the
 * same role `services/worker-core` plays for retention.
 */
const OPS: readonly Op[] = [...AUDIT_OPS, ...CONFIRM_OPS, ...LINK_OPS];

/**
 * The MCP server.
 *
 * Stateless JSON-RPC 2.0 over HTTP — every request carries its own bearer token
 * and nothing is kept between calls, so this scales like any other route and a
 * dropped connection loses no session. The protocol surface an agent needs is
 * three methods (`initialize`, `tools/list`, `tools/call`), so it is written
 * directly rather than pulled in behind an SDK and a transport abstraction.
 *
 * The tools come from `AUDIT_OPS` — the same definitions the REST API uses — so
 * the two cannot drift, and a tool's declared scope is enforced identically
 * whichever door the caller comes through.
 */

const PROTOCOL_VERSION = '2025-06-18';

type RpcId = string | number | null;

function result(id: RpcId, value: unknown) {
  return Response.json({ jsonrpc: '2.0', id, result: value }, {
    headers: { 'cache-control': 'no-store' },
  });
}

function rpcError(id: RpcId, code: number, message: string, data?: unknown) {
  return Response.json({ jsonrpc: '2.0', id, error: { code, message, ...(data ? { data } : {}) } }, {
    // JSON-RPC carries its own error channel; a 200 with an error body is
    // correct, except for auth, where an HTTP 401 is what makes a client
    // prompt for credentials.
    status: code === -32001 ? 401 : 200,
    headers: { 'cache-control': 'no-store' },
  });
}

/** MCP wants JSON Schema; zod 4 emits it, so the schema has one source. */
function jsonSchema(schema: z.ZodTypeAny): unknown {
  return z.toJSONSchema(schema, { io: 'input' });
}

export async function POST(request: Request) {
  let body: { jsonrpc?: string; id?: RpcId; method?: string; params?: Record<string, unknown> };
  try {
    body = await request.json();
  } catch {
    return rpcError(null, -32700, 'Parse error');
  }

  const id = body.id ?? null;
  const method = body.method;
  if (!method) return rpcError(id, -32600, 'Invalid request: no method');

  // `initialize` and `notifications/*` are handshake traffic. Keeping them
  // unauthenticated lets a client discover the server and negotiate a version
  // before it has a key — and they expose nothing.
  if (method === 'initialize') {
    return result(id, {
      protocolVersion: PROTOCOL_VERSION,
      capabilities: { tools: { listChanged: false } },
      serverInfo: { name: 'mamal', version: '0.1.0' },
      instructions:
        'Mamal exposes its tools over one workspace. Authenticate with `Authorization: Bearer mk_…`. ' +
        'Start from audit_list_sites; ids from it feed the other tools.',
    });
  }
  if (method.startsWith('notifications/')) return new Response(null, { status: 202 });

  const database = db();
  const auth = await verifyApiKey(database, request.headers.get('authorization'));
  if (!auth.ok) {
    return rpcError(id, -32001, 'Unauthorized', {
      reason: auth.reason,
      hint: 'Send `Authorization: Bearer mk_…`. Create a key in Settings → API.',
    });
  }
  const key = auth.key;

  if (method === 'tools/list') {
    // Only the tools this key can actually call. A list that advertises a tool
    // the caller will be refused wastes an agent's turn discovering that.
    const tools = OPS.filter((op) => hasScope(key.scopes, op.scope)).map((op) => ({
      name: op.name,
      description: op.description,
      inputSchema: jsonSchema(op.input),
      annotations: { readOnlyHint: op.readOnly, destructiveHint: false },
    }));
    return result(id, { tools });
  }

  if (method === 'tools/call') {
    const name = body.params?.name as string | undefined;
    const args = (body.params?.arguments ?? {}) as Record<string, unknown>;
    const op = OPS.find((o) => o.name === name);
    if (!op) return rpcError(id, -32602, `Unknown tool: ${name}`);

    if (!hasScope(key.scopes, op.scope)) {
      return rpcError(id, -32001, `This key cannot ${op.scope}.`);
    }

    // Argument errors come back as a *tool* error, not a protocol error: the
    // model can read the message and retry with corrected arguments, which a
    // -32602 would not let it do.
    void touchApiKey(database, key.id).catch(() => {});

    try {
      const outcome = await withWorkspace(
        key.workspaceId,
        (tx) => op.call(tx, key.workspaceId, args),
        { db: database },
      );
      if (!outcome.ok) {
        return result(id, {
          isError: true,
          content: [{ type: 'text', text: `Invalid arguments: ${outcome.issues}` }],
        });
      }
      return result(id, {
        content: [{ type: 'text', text: JSON.stringify(outcome.value, null, 2) }],
        structuredContent: { result: outcome.value },
      });
    } catch (e) {
      /*
       * A refusal is a *tool error*, not a protocol error, and it carries the
       * tool's own sentence.
       *
       * That distinction is the whole point: a protocol error ends the
       * conversation, while a tool error goes back to the model — which can
       * then read "“admin” is reserved by the platform" and try another alias.
       * "The operation failed" cannot be acted on by anybody.
       *
       * Detected structurally rather than by importing each tool's error
       * class: `apps/app` has to compile with any single tool absent, and the
       * per-tool build matrix enforces that.
       */
      const refusal = asRefusal(e);
      if (refusal) {
        return result(id, {
          isError: true,
          content: [{ type: 'text', text: `${refusal.reason}: ${refusal.message}` }],
        });
      }
      console.error('mcp error', e);
      return result(id, {
        isError: true,
        content: [{ type: 'text', text: 'The operation failed.' }],
      });
    }
  }

  return rpcError(id, -32601, `Method not found: ${method}`);
}

/** Discovery for clients that probe before posting. */
export async function GET() {
  return Response.json({
    name: 'mamal',
    protocolVersion: PROTOCOL_VERSION,
    transport: 'streamable-http',
    authentication: { type: 'bearer', header: 'Authorization', prefix: 'mk_' },
    tools: OPS.map((o) => ({ name: o.name, scope: o.scope, readOnly: o.readOnly })),
  });
}
