import { is, Table } from 'drizzle-orm';
import { getTableConfig, type PgTable } from 'drizzle-orm/pg-core';
import * as schema from './schema/index.ts';

/**
 * Tenant isolation is a build constraint, not a convention.
 *
 * Every table either carries `workspace_id` and gets an RLS policy, or is
 * listed in EXEMPT_TABLES with a stated reason. The isolation test in
 * `src/__tests__/isolation.test.ts` enumerates the schema and fails on
 * anything that is neither — cheap now, impossible to retrofit across 200
 * tables later.
 */

/** `workspaces` is the tenant root: it isolates on `id`, not `workspace_id`. */
const TENANT_ROOT_TABLE = 'workspaces';

/** Global (non-tenant) tables, each with the reason it is exempt. */
export const EXEMPT_TABLES: Record<string, string> = {
  // Identity spans workspaces by definition.
  users: 'identity — a user belongs to many workspaces',
  sessions: 'identity — scoped to a user, carries activeWorkspaceId',
  accounts: 'identity — OAuth provider links',
  verifications: 'identity — pre-authentication tokens',

  // Instance-operator configuration.
  instance_settings: 'instance singleton',
  instance_modules: 'instance module registry',

  // Catalogue tables: the same rows are read by every tenant.
  features: 'global feature registry',
  plans: 'global plan catalogue',
  plan_prices: 'global plan catalogue',
  plan_entitlements: 'global plan catalogue',
  plan_credit_grants: 'global plan catalogue',
  credit_packs: 'global plan catalogue',
  coupons: 'global — redemption is tenant-scoped, the coupon is not',
  tax_rates: 'global tax table',
  ai_providers: 'global AI registry',
  ai_models: 'global AI registry',
  ai_features: 'global AI registry',
  ai_prompts: 'global prompt library',
  automation_templates: 'global recipe catalogue',
  audit_rules: 'global rule catalogue — overrides are workspace-scoped',

  // Scope-discriminated rather than workspace-scoped.
  ai_feature_state: 'scope/scopeId discriminator covers instance and workspace',
  ai_credentials: 'scope/scopeId discriminator covers instance and workspace',

  // Append-only fact tables. Written by the ingest worker under platform scope
  // and read only through packages/events, which always filters by workspace.
  // RLS on a table taking batched hot writes is the wrong trade.
  events_raw: 'append-only fact table — access mediated by packages/events',
  events_daily: 'rollup of events_raw',

  // Operational tables read only by the platform; workspace_id is nullable.
  bus_deliveries: 'operational — keyed by (handlerKey, eventId)',
  bus_dead_letters: 'operational — admin replay surface',
  job_dead_letters: 'operational — admin replay surface',
};

export type TableInfo = {
  name: string;
  hasWorkspaceId: boolean;
  isTenantRoot: boolean;
  exemptReason?: string;
};

/** camelCase -> snake_case, matching the client's `casing: 'snake_case'`. */
export const toSnake = (s: string): string =>
  s.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase();

function isPgTable(v: unknown): v is PgTable {
  return is(v, Table);
}

/** Reflect over the Drizzle schema rather than maintaining a parallel list. */
export function enumerateTables(): TableInfo[] {
  const out: TableInfo[] = [];
  for (const value of Object.values(schema)) {
    if (!isPgTable(value)) continue;
    const cfg = getTableConfig(value);
    // getTableConfig reports the JS key; `casing` is applied by the client.
    const columns = cfg.columns.map((c) => toSnake(c.name));
    out.push({
      name: cfg.name,
      hasWorkspaceId: columns.includes('workspace_id'),
      isTenantRoot: cfg.name === TENANT_ROOT_TABLE,
      exemptReason: EXEMPT_TABLES[cfg.name],
    });
  }
  return out.sort((a, b) => a.name.localeCompare(b.name));
}

/** Tables that must carry an RLS policy. */
export const TENANT_TABLES = (): TableInfo[] =>
  enumerateTables().filter(
    (t) => !t.exemptReason && (t.hasWorkspaceId || t.isTenantRoot),
  );

/** Tables that satisfy neither rule — the isolation test fails on any of these. */
export const UNPROTECTED_TABLES = (): string[] =>
  enumerateTables()
    .filter((t) => !t.exemptReason && !t.hasWorkspaceId && !t.isTenantRoot)
    .map((t) => t.name);

const PLATFORM_ADMIN_ESCAPE =
  `coalesce(current_setting('app.is_platform_admin', true), 'false') = 'true'`;
const CURRENT_WS = `nullif(current_setting('app.current_workspace_id', true), '')::uuid`;

/**
 * Emit the RLS DDL. Re-applied after every migration, so a new tenant table is
 * protected the moment it exists.
 */
export function rlsStatements(): string[] {
  const stmts: string[] = [];
  for (const t of TENANT_TABLES()) {
    const col = t.isTenantRoot ? 'id' : 'workspace_id';
    const policy = `${t.name}_tenant_isolation`;
    stmts.push(`alter table "${t.name}" enable row level security;`);
    stmts.push(`alter table "${t.name}" force row level security;`);
    stmts.push(`drop policy if exists "${policy}" on "${t.name}";`);
    stmts.push(
      `create policy "${policy}" on "${t.name}" ` +
        `using ("${col}" = ${CURRENT_WS} or ${PLATFORM_ADMIN_ESCAPE}) ` +
        `with check ("${col}" = ${CURRENT_WS} or ${PLATFORM_ADMIN_ESCAPE});`,
    );
  }
  return stmts;
}
