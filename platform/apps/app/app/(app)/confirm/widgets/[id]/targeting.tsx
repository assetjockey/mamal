'use client';

import { useMemo, useState } from 'react';
import { explain, FIELDS, OPERATORS, type VisitorContext } from '@mamal/targeting';
import { Button, SectionLabel } from '@mamal/ui';

type Condition = { field: string; op: string; value?: unknown };

/** Operators that take no value — showing an input for them is a lie. */
const NO_VALUE = new Set(['exists', 'not_exists']);

/**
 * A sample visitor the rule is explained against.
 *
 * Editable, because "who would see this?" is only useful if you can ask about
 * someone other than yourself — the whole point of a geo rule is that it
 * targets people you are not.
 */
const DEFAULT_VISITOR: VisitorContext = {
  path: '/pricing',
  country: 'GB',
  city: 'London',
  device: 'desktop',
  browser: 'Chrome',
  os: 'macOS',
  language: 'en-GB',
  referrerHost: 'www.google.com',
  utm: { source: 'google', medium: 'cpc' },
  visitorType: 'new',
  sessionPages: 2,
  secondsOnPage: 15,
  scrollDepth: 40,
  dayOfWeek: 3,
  hour: 14,
};

export function TargetingPanel({
  targeting, onChange,
}: {
  targeting: Record<string, unknown>;
  onChange: (t: Record<string, unknown>) => void;
}) {
  const [visitor, setVisitor] = useState<VisitorContext>(DEFAULT_VISITOR);

  const conditions = (Array.isArray(targeting.conditions) ? targeting.conditions : []) as Condition[];
  const match = targeting.match === 'any' ? 'any' : 'all';

  const update = (next: Partial<{ match: string; conditions: Condition[] }>) =>
    onChange({ ...targeting, match, conditions, ...next });

  /*
   * Explained by the same module the browser runs.
   *
   * Not a re-implementation: if this panel and the runtime could disagree, the
   * panel would be worse than nothing — it would give false confidence about
   * who is being shown what.
   */
  const result = useMemo(
    () => explain({ match, conditions }, visitor),
    [match, conditions, visitor],
  );

  return (
    <div>
      <SectionLabel>Who sees it</SectionLabel>

      {conditions.length === 0 ? (
        <p className="mb-3 text-[13px] leading-[1.5] text-[var(--text-muted)]">
          Everyone. Add a rule to narrow it.
        </p>
      ) : (
        <label className="mb-3 flex items-center gap-2 text-[12px] text-[var(--text-muted)]">
          Match
          <select
            value={match}
            onChange={(e) => update({ match: e.target.value })}
            className="h-7 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-1.5 text-[12px] text-[var(--text-primary)]"
          >
            <option value="all">all rules</option>
            <option value="any">any rule</option>
          </select>
        </label>
      )}

      <div className="space-y-2">
        {conditions.map((c, i) => (
          <div key={i} className="rounded-[4px] border border-[var(--border-hairline)] p-2">
            <div className="flex flex-wrap gap-1.5">
              <select
                aria-label="Field"
                value={c.field}
                onChange={(e) => {
                  const next = [...conditions];
                  next[i] = { ...c, field: e.target.value };
                  update({ conditions: next });
                }}
                className="h-7 min-w-0 flex-1 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-1.5 text-[12px] text-[var(--text-primary)]"
              >
                {FIELDS.map((f) => <option key={f} value={f}>{f.replace(/_/g, ' ')}</option>)}
              </select>

              <select
                aria-label="Operator"
                value={c.op}
                onChange={(e) => {
                  const next = [...conditions];
                  next[i] = { ...c, op: e.target.value };
                  update({ conditions: next });
                }}
                className="h-7 min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-1.5 text-[12px] text-[var(--text-primary)]"
              >
                {OPERATORS.map((o) => <option key={o} value={o}>{o.replace(/_/g, ' ')}</option>)}
              </select>

              <button
                aria-label="Remove rule"
                onClick={() => update({ conditions: conditions.filter((_, j) => j !== i) })}
                className="h-7 shrink-0 rounded-[4px] px-1.5 text-[13px] text-[var(--text-faint)] hover:text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
              >
                ✕
              </button>
            </div>

            {!NO_VALUE.has(c.op) ? (
              <input
                aria-label="Value"
                value={String(c.value ?? '')}
                placeholder="Value"
                onChange={(e) => {
                  const next = [...conditions];
                  next[i] = { ...c, value: e.target.value };
                  update({ conditions: next });
                }}
                className="mt-1.5 h-7 w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[12px] text-[var(--text-primary)] focus:border-[var(--accent)] focus:outline-none"
              />
            ) : null}

            {/* What this one condition did, against the sample visitor. */}
            {result.reasons[i] ? (
              <p className="mt-1.5 text-[11px] text-[var(--text-faint)]">
                {result.reasons[i]!.matched ? '✓ matches' : '✕ does not match'}
                {' · visitor has '}
                <span className="text-[var(--text-muted)]">
                  {String(result.reasons[i]!.actual ?? '—')}
                </span>
              </p>
            ) : null}
          </div>
        ))}
      </div>

      <Button
        size="sm"
        variant="quiet"
        onClick={() =>
          update({ conditions: [...conditions, { field: 'country', op: 'is', value: '' }] })
        }
      >
        Add rule
      </Button>

      {/* ------------------------------------------- the sample visitor */}
      <div className="mt-4 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-3">
        <div className="flex items-center justify-between gap-2">
          <span className="text-[12px] text-[var(--text-muted)]">A visitor from</span>
          <span
            className={`text-[12px] ${
              result.matched ? 'text-[var(--color-status-ok)]' : 'text-[var(--text-faint)]'
            }`}
          >
            {result.matched ? 'sees it' : 'does not see it'}
          </span>
        </div>

        <div className="mt-2 grid grid-cols-2 gap-1.5">
          {([
            ['country', 'GB'], ['city', 'London'], ['device', 'desktop'], ['path', '/pricing'],
          ] as const).map(([key, placeholder]) => (
            <input
              key={key}
              aria-label={`Sample visitor ${key}`}
              placeholder={placeholder}
              value={String((visitor as Record<string, unknown>)[key] ?? '')}
              onChange={(e) => setVisitor((v) => ({ ...v, [key]: e.target.value }))}
              className="h-7 min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[12px] text-[var(--text-primary)] focus:border-[var(--accent)] focus:outline-none"
            />
          ))}
        </div>

        <p className="mt-2 text-[11px] leading-[1.4] text-[var(--text-faint)]">
          Evaluated by the same code that runs in your visitors’ browsers.
        </p>
      </div>
    </div>
  );
}
