'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, StatusBadge, useToast } from '@mamal/ui';
import { importCsv } from './actions';

/**
 * Bulk import, in two steps.
 *
 * Check first, then write. A ten-thousand-row paste is not something to find
 * out about afterwards, and a half-finished import is unrecoverable in
 * practice — the customer cannot tell which rows landed, and re-running
 * duplicates whatever did. So the first click reports and the second commits,
 * and the report is the *same* code path that will do the writing.
 */

type Problem = { line: number; column: string; message: string };
type Created = { alias: string; url: string; destination: string };

export function BulkImport({ allowed }: { allowed: boolean }) {
  const [open, setOpen] = useState(false);
  const [csv, setCsv] = useState('');
  const [checked, setChecked] = useState<{ created: Created[]; problems: Problem[] } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  if (!open) {
    return (
      <Button variant="ghost" onClick={() => setOpen(true)} disabled={!allowed}>
        Import CSV
      </Button>
    );
  }

  const run = (dryRun: boolean) => {
    setError(null);
    start(async () => {
      const result = await importCsv(csv, { dryRun });
      if (!result.ok) { setError(result.error); setChecked(null); return; }
      if (dryRun) { setChecked(result); return; }

      toast({
        kind: 'ok',
        message: `Imported ${result.created.length.toLocaleString()} link${result.created.length === 1 ? '' : 's'}.`,
      });
      setOpen(false); setCsv(''); setChecked(null);
      router.refresh();
    });
  };

  return (
    <div className="w-full max-w-[560px] rounded-[4px] border border-[var(--border-hairline)] p-4">
      <label htmlFor="bulk-csv" className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
        Paste a CSV
      </label>
      <textarea
        id="bulk-csv"
        rows={6}
        value={csv}
        onChange={(e) => { setCsv(e.target.value); setChecked(null); }}
        placeholder={'url,alias,title,campaign,utm_source\nhttps://example.com/spring,spring,Spring sale,q2,poster'}
        className="w-full resize-y rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 font-mono text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
      />
      <p className="mt-1 text-[12px] text-[var(--text-faint)]">
        A destination column is required — call it <code>url</code>, <code>destination</code>,
        <code> link</code> or <code>target</code>. Everything else is optional. Leave{' '}
        <code>alias</code> blank and we generate one.
      </p>

      {error ? (
        <p role="alert" className="mt-3 text-[13px] text-[var(--color-status-error)]">{error}</p>
      ) : null}

      {checked ? (
        <div aria-live="polite" className="mt-3 border-t border-[var(--border-hairline)] pt-3">
          <div className="flex flex-wrap items-center gap-2">
            <StatusBadge status={checked.created.length > 0 ? 'ok' : 'warn'}>
              {checked.created.length.toLocaleString()} ready
            </StatusBadge>
            {checked.problems.length > 0 ? (
              <StatusBadge status="warn">
                {checked.problems.length.toLocaleString()} skipped
              </StatusBadge>
            ) : null}
          </div>

          {checked.problems.length > 0 ? (
            <>
              {/* Every problem, not the first — somebody fixing a 10,000-row
                  export needs the whole list in one pass. */}
              <ul className="mt-2 max-h-[180px] overflow-y-auto text-[13px] text-[var(--text-secondary)]">
                {checked.problems.slice(0, 200).map((p, i) => (
                  <li key={`${p.line}-${i}`} className="tabular-nums">
                    Line {p.line} · {p.column}: {p.message}
                  </li>
                ))}
              </ul>
              {checked.problems.length > 200 ? (
                <p className="mt-1 text-[12px] text-[var(--text-faint)]">
                  …and {(checked.problems.length - 200).toLocaleString()} more.
                </p>
              ) : null}
            </>
          ) : null}
        </div>
      ) : null}

      <div className="mt-4 flex flex-wrap gap-2 border-t border-[var(--border-hairline)] pt-3">
        <Button variant="ghost" onClick={() => run(true)} disabled={pending || !csv.trim()}>
          {pending && !checked ? 'Checking…' : 'Check'}
        </Button>
        <Button
          onClick={() => run(false)}
          disabled={pending || !checked || checked.created.length === 0}
        >
          {checked
            ? `Import ${checked.created.length.toLocaleString()} link${checked.created.length === 1 ? '' : 's'}`
            : 'Import'}
        </Button>
        <Button variant="quiet" onClick={() => { setOpen(false); setChecked(null); setError(null); }}>
          Cancel
        </Button>
      </div>
    </div>
  );
}
