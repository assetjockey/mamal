'use client';

import { useTransition } from 'react';
import { Button, StatusBadge } from '@mamal/ui';
import {
  grantCredits, resetWorkspace, toggleAiMaster, toggleAiTenant, toggleSubscription,
} from './actions';

export type PlanOption = { key: string; name: string; kind: string; active: boolean };

export function Controls({
  plans,
  aiMaster,
  aiTenant,
  credits,
}: {
  plans: PlanOption[];
  aiMaster: boolean;
  aiTenant: boolean;
  credits: number;
}) {
  const [pending, start] = useTransition();
  const run = (fn: () => Promise<void>) => () => start(() => void fn());

  const groups = [
    { kind: 'tool', label: 'Per-tool' },
    { kind: 'unified', label: 'Unified' },
    { kind: 'lifetime', label: 'Lifetime' },
  ];

  return (
    <div className="mb-8 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-5">
      <div className="mb-1 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
        Resolver sandbox
      </div>
      <p className="mb-4 max-w-3xl text-[13px] text-[var(--text-secondary)]">
        These write the same rows the billing webhook will. Stack a per-tool plan on a unified one
        and watch limits merge with MAX while quotas merge with SUM — then switch the lifetime plan
        on and see every AI feature refuse regardless of credits.
      </p>

      {groups.map((g) => {
        const items = plans.filter((p) => p.kind === g.kind);
        if (!items.length) return null;
        return (
          <div key={g.kind} className="mb-3">
            <div className="mb-1.5 text-[12px] text-[var(--text-muted)]">{g.label}</div>
            <div className="flex flex-wrap gap-2">
              {items.map((p) => (
                <button
                  key={p.key}
                  disabled={pending}
                  onClick={run(() => toggleSubscription(p.key))}
                  className={
                    'rounded-[4px] border px-3 py-1.5 text-[13px] transition-colors duration-[120ms] disabled:opacity-50 ' +
                    (p.active
                      ? 'border-[var(--accent)] bg-[var(--accent-wash)] text-[var(--accent)]'
                      : 'border-[var(--border-hairline)] text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]')
                  }
                >
                  {p.name}
                </button>
              ))}
            </div>
          </div>
        );
      })}

      <div className="mt-4 flex flex-wrap items-center gap-3 border-t border-[var(--border-hairline)] pt-4">
        <Button size="sm" variant="ghost" disabled={pending} onClick={run(() => grantCredits(5000))}>
          + 5,000 credits
        </Button>
        <span className="text-[13px] tabular-nums text-[var(--text-secondary)]">
          balance {credits.toLocaleString()}
        </span>

        <span className="ml-2 h-4 w-px bg-[var(--border-hairline)]" />

        <button
          disabled={pending}
          onClick={run(toggleAiMaster)}
          className="flex items-center gap-2 text-[13px] text-[var(--text-secondary)] disabled:opacity-50"
        >
          Instance AI
          <StatusBadge status={aiMaster ? 'ok' : 'error'}>{aiMaster ? 'On' : 'Killed'}</StatusBadge>
        </button>

        <button
          disabled={pending}
          onClick={run(toggleAiTenant)}
          className="flex items-center gap-2 text-[13px] text-[var(--text-secondary)] disabled:opacity-50"
        >
          Workspace AI
          <StatusBadge status={aiTenant ? 'ok' : 'warn'}>{aiTenant ? 'On' : 'Off'}</StatusBadge>
        </button>

        <Button size="sm" variant="quiet" disabled={pending} onClick={run(resetWorkspace)}>
          Reset
        </Button>
      </div>
    </div>
  );
}
