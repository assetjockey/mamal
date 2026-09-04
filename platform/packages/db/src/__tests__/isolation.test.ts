import { describe, expect, it } from 'vitest';
import {
  enumerateTables,
  EXEMPT_TABLES,
  rlsStatements,
  TENANT_TABLES,
  UNPROTECTED_TABLES,
} from '../rls.ts';

describe('tenant isolation', () => {
  const tables = enumerateTables();

  it('reflects the whole schema', () => {
    expect(tables.length).toBeGreaterThan(50);
  });

  it('every table either carries workspace_id or is explicitly exempt', () => {
    const offenders = UNPROTECTED_TABLES();
    expect(
      offenders,
      'These tables have no workspace_id and no entry in EXEMPT_TABLES.\n' +
        'Add the column, or add the table to EXEMPT_TABLES in src/rls.ts with a reason:\n' +
        offenders.map((n) => `  - ${n}`).join('\n'),
    ).toEqual([]);
  });

  it('every exemption names a real table', () => {
    const known = new Set(tables.map((t) => t.name));
    const stale = Object.keys(EXEMPT_TABLES).filter((n) => !known.has(n));
    expect(stale, `EXEMPT_TABLES lists tables that no longer exist: ${stale.join(', ')}`).toEqual([]);
  });

  it('emits an RLS policy for every tenant table', () => {
    const tenant = TENANT_TABLES();
    expect(tenant.length).toBeGreaterThan(30);
    const ddl = rlsStatements().join('\n');
    for (const t of tenant) {
      expect(ddl).toContain(`alter table "${t.name}" enable row level security;`);
      expect(ddl).toContain(`"${t.name}_tenant_isolation"`);
    }
  });

  it('isolates the workspaces table on id, not workspace_id', () => {
    const ddl = rlsStatements().join('\n');
    expect(ddl).toContain('create policy "workspaces_tenant_isolation" on "workspaces" using ("id" =');
  });

  it('policies read the GUC set by withWorkspace, not a literal', () => {
    const ddl = rlsStatements().join('\n');
    expect(ddl).toContain("current_setting('app.current_workspace_id', true)");
    expect(ddl).toContain("current_setting('app.is_platform_admin', true)");
  });

  it('every policy has both USING and WITH CHECK, so writes cannot cross tenants', () => {
    const policies = rlsStatements().filter((s) => s.startsWith('create policy'));
    expect(policies.length).toBe(TENANT_TABLES().length);
    for (const p of policies) expect(p).toContain('with check');
  });
});
