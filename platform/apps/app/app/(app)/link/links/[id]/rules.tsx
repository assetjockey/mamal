'use client';

import { useState } from 'react';
import { FIELDS, OPERATORS, validateTargeting, type RuleGroup } from '@mamal/targeting';
import type { Rule } from '@mamal/redirect';
import { Button } from '@mamal/ui';

/**
 * The rule builder.
 *
 * Ordered, first match wins, and the order is the whole semantic — so the
 * editor makes position visible ("Rule 2 of 4") and movable, rather than
 * hiding it behind a priority number nobody can reason about.
 *
 * Every field and operator comes from the targeting package's own closed lists,
 * so the picker cannot offer something the evaluator does not understand. That
 * is the same guarantee `validateTargeting` enforces on the server; here it
 * simply means the mistake is impossible to make rather than caught afterwards.
 */

const control =
  'rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';

const NO_VALUE = new Set(['exists', 'not_exists']);

const readableField: Partial<Record<string, string>> = {
  referrer_host: 'referrer host',
  utm_source: 'UTM source', utm_medium: 'UTM medium', utm_campaign: 'UTM campaign',
  utm_term: 'UTM term', utm_content: 'UTM content',
  visitor_type: 'new or returning', session_pages: 'pages this session',
  seconds_on_page: 'seconds on page', scroll_depth: 'scroll depth',
  day_of_week: 'day of week',
};

const readableOp: Partial<Record<string, string>> = {
  is: 'is', is_not: 'is not', contains: 'contains', not_contains: 'does not contain',
  starts_with: 'starts with', ends_with: 'ends with',
  matches: 'matches regex', not_matches: 'does not match regex',
  in: 'is one of', not_in: 'is none of',
  gt: 'is more than', gte: 'is at least', lt: 'is less than', lte: 'is at most',
  exists: 'is known', not_exists: 'is unknown',
};

type Condition = { field: string; op: string; value?: unknown };

const emptyRule = (priority: number): Rule => ({
  id: `new-${priority}-${Math.random().toString(36).slice(2, 8)}`,
  priority,
  match: { match: 'all', conditions: [{ field: 'country', op: 'is', value: '' }] },
  action: { type: 'redirect', destinationUrl: '' },
  sticky: true,
  isEnabled: true,
});

export function RuleList({
  rules,
  onChange,
  pending,
}: {
  rules: Rule[];
  onChange: (rules: Rule[]) => void;
  pending: boolean;
}) {
  const [draft, setDraft] = useState<Rule[]>(rules);
  const [error, setError] = useState<string | null>(null);

  const dirty = JSON.stringify(draft) !== JSON.stringify(rules);

  const update = (index: number, next: Rule) => {
    setDraft((list) => list.map((r, i) => (i === index ? next : r)));
    setError(null);
  };

  const move = (index: number, by: number) => {
    const to = index + by;
    if (to < 0 || to >= draft.length) return;
    const next = [...draft];
    [next[index], next[to]] = [next[to]!, next[index]!];
    setDraft(next.map((r, i) => ({ ...r, priority: i })));
  };

  const save = () => {
    for (const [i, rule] of draft.entries()) {
      const problems = validateTargeting(rule.match);
      if (problems.length > 0) {
        setError(`Rule ${i + 1}: ${problems[0]!.message}`);
        return;
      }
      if (rule.action.type === 'redirect' && !rule.action.destinationUrl) {
        setError(`Rule ${i + 1} has no destination.`);
        return;
      }
    }
    onChange(draft.map((r, i) => ({ ...r, priority: i })));
  };

  return (
    <div className="mt-3">
      <p className="max-w-[70ch] text-[13px] text-[var(--text-secondary)]">
        Evaluated top to bottom. The first rule that matches wins, and anything below it
        never runs — so put the narrow rules first.
      </p>

      <ol className="mt-4 grid gap-3">
        {draft.map((rule, i) => (
          <li
            key={rule.id}
            className="rounded-[4px] border border-[var(--border-hairline)] p-3"
          >
            <div className="flex flex-wrap items-center justify-between gap-2">
              <span className="text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
                Rule {i + 1} of {draft.length}
              </span>
              <div className="flex gap-1">
                <Button size="sm" variant="quiet" onClick={() => move(i, -1)} disabled={i === 0}>
                  Up
                </Button>
                <Button size="sm" variant="quiet" onClick={() => move(i, 1)}
                        disabled={i === draft.length - 1}>
                  Down
                </Button>
                <Button size="sm" variant="quiet"
                        onClick={() => setDraft((l) => l.filter((_, j) => j !== i))}>
                  Remove
                </Button>
              </div>
            </div>

            <ConditionRows
              group={rule.match as RuleGroup}
              onChange={(match) => update(i, { ...rule, match })}
            />

            <ActionRow rule={rule} onChange={(next) => update(i, next)} />
          </li>
        ))}
      </ol>

      {error ? (
        <p role="alert" className="mt-3 text-[13px] text-[var(--color-status-error)]">{error}</p>
      ) : null}

      <div className="mt-4 flex flex-wrap gap-2 border-t border-[var(--border-hairline)] pt-4">
        <Button variant="ghost" onClick={() => setDraft((l) => [...l, emptyRule(l.length)])}>
          Add rule
        </Button>
        <Button onClick={save} disabled={pending || !dirty}>
          {pending ? 'Saving…' : 'Save rules'}
        </Button>
        {dirty ? (
          <Button variant="quiet" onClick={() => { setDraft(rules); setError(null); }}>
            Discard changes
          </Button>
        ) : null}
      </div>
    </div>
  );
}

function ConditionRows({
  group,
  onChange,
}: {
  group: RuleGroup;
  onChange: (group: RuleGroup) => void;
}) {
  const conditions = (group?.conditions ?? []) as Condition[];
  const mode = group?.match ?? 'all';

  const set = (next: Condition[]) => onChange({ ...group, match: mode, conditions: next });

  return (
    <div className="mt-3 grid gap-2">
      {conditions.length > 1 ? (
        <label className="text-[12px] text-[var(--text-secondary)]">
          Match{' '}
          <select
            value={mode}
            onChange={(e) => onChange({ ...group, match: e.target.value as 'all' | 'any' })}
            className={control}
          >
            <option value="all">all of these</option>
            <option value="any">any of these</option>
          </select>
        </label>
      ) : null}

      {conditions.map((c, i) => (
        <div key={i} className="flex flex-wrap items-center gap-2">
          <label className="sr-only" htmlFor={`f-${i}`}>Field</label>
          <select
            id={`f-${i}`} value={c.field} className={control}
            onChange={(e) => set(conditions.map((x, j) => (j === i ? { ...x, field: e.target.value } : x)))}
          >
            {FIELDS.map((f) => (
              <option key={f} value={f}>{readableField[f] ?? f.replace(/_/g, ' ')}</option>
            ))}
          </select>

          <label className="sr-only" htmlFor={`o-${i}`}>Operator</label>
          <select
            id={`o-${i}`} value={c.op} className={control}
            onChange={(e) => set(conditions.map((x, j) => (j === i ? { ...x, op: e.target.value } : x)))}
          >
            {OPERATORS.map((o) => <option key={o} value={o}>{readableOp[o] ?? o}</option>)}
          </select>

          {NO_VALUE.has(c.op) ? null : (
            <>
              <label className="sr-only" htmlFor={`v-${i}`}>Value</label>
              <input
                id={`v-${i}`} value={String(c.value ?? '')} className={`${control} min-w-0 grow`}
                placeholder={c.op === 'in' || c.op === 'not_in' ? 'DE, AT, CH' : 'DE'}
                onChange={(e) => {
                  const raw = e.target.value;
                  const value = c.op === 'in' || c.op === 'not_in'
                    ? raw.split(',').map((v) => v.trim()).filter(Boolean)
                    : raw;
                  set(conditions.map((x, j) => (j === i ? { ...x, value } : x)));
                }}
              />
            </>
          )}

          <Button size="sm" variant="quiet"
                  onClick={() => set(conditions.filter((_, j) => j !== i))}
                  disabled={conditions.length === 1}>
            −
          </Button>
        </div>
      ))}

      <div>
        <Button size="sm" variant="quiet"
                onClick={() => set([...conditions, { field: 'device', op: 'is', value: '' }])}>
          Add condition
        </Button>
      </div>
    </div>
  );
}

function ActionRow({ rule, onChange }: { rule: Rule; onChange: (rule: Rule) => void }) {
  const action = rule.action;

  return (
    <div className="mt-3 border-t border-[var(--border-hairline)] pt-3">
      <div className="flex flex-wrap items-center gap-2">
        <label className="sr-only" htmlFor={`a-${rule.id}`}>Then</label>
        <select
          id={`a-${rule.id}`} value={action.type} className={control}
          onChange={(e) => {
            const type = e.target.value as 'redirect' | 'rotate' | 'block';
            onChange({
              ...rule,
              action:
                type === 'redirect' ? { type, destinationUrl: '' }
                : type === 'rotate' ? { type, variants: [{ url: '', weight: 50 }, { url: '', weight: 50 }] }
                : { type },
            });
          }}
        >
          <option value="redirect">send them to</option>
          <option value="rotate">split them between</option>
          <option value="block">block them</option>
        </select>

        {action.type === 'redirect' ? (
          <input
            value={action.destinationUrl} className={`${control} min-w-0 grow`}
            placeholder="https://example.de/"
            aria-label="Destination for this rule"
            onChange={(e) => onChange({ ...rule, action: { type: 'redirect', destinationUrl: e.target.value } })}
          />
        ) : null}
      </div>

      {action.type === 'rotate' ? (
        <div className="mt-2 grid gap-2">
          {action.variants.map((v, i) => (
            <div key={i} className="flex flex-wrap items-center gap-2">
              <input
                value={v.url} className={`${control} min-w-0 grow`}
                placeholder={`Variant ${String.fromCharCode(65 + i)}`}
                aria-label={`Variant ${String.fromCharCode(65 + i)} URL`}
                onChange={(e) => onChange({
                  ...rule,
                  action: {
                    type: 'rotate',
                    variants: action.variants.map((x, j) => (j === i ? { ...x, url: e.target.value } : x)),
                  },
                })}
              />
              <input
                type="number" min={0} max={100} value={v.weight} className={`${control} w-[76px]`}
                aria-label={`Variant ${String.fromCharCode(65 + i)} weight`}
                onChange={(e) => onChange({
                  ...rule,
                  action: {
                    type: 'rotate',
                    variants: action.variants.map((x, j) =>
                      (j === i ? { ...x, weight: Number(e.target.value) } : x)),
                  },
                })}
              />
              <label className="flex items-center gap-1.5 text-[12px] text-[var(--text-secondary)]">
                <input
                  type="radio" name={`winner-${rule.id}`} checked={Boolean(v.isWinner)}
                  onChange={() => onChange({
                    ...rule,
                    action: {
                      type: 'rotate',
                      variants: action.variants.map((x, j) => ({ ...x, isWinner: j === i })),
                    },
                  })}
                />
                Winner
              </label>
            </div>
          ))}
          <div className="flex flex-wrap items-center gap-2">
            <Button size="sm" variant="quiet"
                    onClick={() => onChange({
                      ...rule,
                      action: { type: 'rotate', variants: [...action.variants, { url: '', weight: 0 }] },
                    })}>
              Add variant
            </Button>
            <label className="flex items-center gap-1.5 text-[12px] text-[var(--text-secondary)]">
              <input type="checkbox" checked={rule.sticky}
                     onChange={(e) => onChange({ ...rule, sticky: e.target.checked })} />
              Keep each visitor on the same variant
            </label>
          </div>
          <p className="text-[12px] text-[var(--text-faint)]">
            Marking a winner ends the test: everyone goes to it, and the rule keeps its history.
          </p>
        </div>
      ) : null}
    </div>
  );
}
