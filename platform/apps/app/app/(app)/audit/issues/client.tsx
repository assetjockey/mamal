'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Card, StatusBadge, useToast } from '@mamal/ui';
import { setIssueStatus } from '../actions';
import { generateFixBrief } from '../ai-actions';
import { AiPanel } from '../ai-panel';

type Issue = {
  id: string;
  rule_id: string;
  severity: 'critical' | 'warning' | 'info';
  page_url: string | null;
  evidence: Record<string, unknown>;
  title: string;
  why: string;
  how_to_fix: string;
  category: string;
  is_ai_relevant: boolean;
};

export function IssueGroup({ issues }: { issues: Issue[] }) {
  const [open, setOpen] = useState(false);
  const [pending, start] = useTransition();
  const router = useRouter();
  const toast = useToast();
  const first = issues[0]!;

  /*
   * No confirmation dialog. Triaging a hundred findings is the job, and a
   * modal on every one of them would make the job unbearable — so the action
   * happens immediately and stays reversible for ten seconds. `setIssueStatus`
   * is idempotent, so the undo is just the same call with 'open'.
   */
  const triage = (id: string, status: 'fixed' | 'ignored', label: string) =>
    start(async () => {
      await setIssueStatus(id, status);
      router.refresh();
      toast({
        message: `Marked ${label}.`,
        kind: 'ok',
        onUndo: async () => {
          await setIssueStatus(id, 'open');
          router.refresh();
        },
      });
    });

  const tone = first.severity === 'critical' ? 'error' : first.severity === 'warning' ? 'warn' : 'neutral';

  return (
    <Card padded={false}>
      <button
        onClick={() => setOpen((v) => !v)}
        aria-expanded={open}
        className="flex w-full items-start justify-between gap-4 p-5 text-left transition-colors duration-[120ms] hover:bg-[var(--surface-hover)]"
      >
        <div className="min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <span className="text-[16px] text-[var(--text-primary)]">{first.title}</span>
            <StatusBadge status={tone}>{first.severity}</StatusBadge>
            {first.is_ai_relevant ? <StatusBadge status="info">AI visibility</StatusBadge> : null}
          </div>
          <p className="mt-1.5 max-w-3xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
            {first.why}
          </p>
        </div>
        <span className="shrink-0 text-[13px] tabular-nums text-[var(--text-faint)]">
          {/* A site-wide finding affects the site, not "1 page". */}
          {issues.every((i) => i.page_url === null)
            ? 'Site-wide'
            : `${issues.length} ${issues.length === 1 ? 'page' : 'pages'}`}
        </span>
      </button>

      {open ? (
        <div className="border-t border-[var(--border-hairline)] p-5">
          <div className="mb-5">
            <div className="mb-2 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
              How to fix
            </div>
            <div className="max-w-3xl space-y-2 text-[14px] leading-[1.5] text-[var(--text-primary)]">
              {first.how_to_fix.split('\n\n').map((para, i) => (
                <p key={i} className="whitespace-pre-wrap">{para}</p>
              ))}
            </div>
          </div>

          <div className="mb-5">
            <AiPanel
              label="Tailored fix for this page"
              hint="Uses the page's actual title, description and evidence — the standard guidance above is written once for every site."
              action={() => generateFixBrief(first.id)}
            />
          </div>

          <div className="mb-2 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
            Affected
          </div>
          <ul className="space-y-1.5">
            {issues.slice(0, 25).map((issue) => (
              <li
                key={issue.id}
                className="flex flex-wrap items-center justify-between gap-3 rounded-[4px] bg-[var(--surface-band)] px-3 py-2"
              >
                <div className="min-w-0 flex-1">
                  <span className="block truncate font-mono text-[12px] text-[var(--text-primary)]">
                    {issue.page_url ?? 'Site-wide'}
                  </span>
                  <Evidence evidence={issue.evidence} />
                </div>
                <div className="flex shrink-0 items-center gap-3">
                  <button
                    disabled={pending}
                    onClick={() => triage(issue.id, 'fixed', 'fixed')}
                    className="text-[12px] text-[var(--accent)] transition-colors hover:text-[var(--accent-hover)] disabled:opacity-50"
                  >
                    Mark fixed
                  </button>
                  <button
                    disabled={pending}
                    onClick={() => triage(issue.id, 'ignored', 'ignored')}
                    className="text-[12px] text-[var(--text-muted)] transition-colors hover:text-[var(--text-primary)] disabled:opacity-50"
                  >
                    Ignore
                  </button>
                </div>
              </li>
            ))}
          </ul>
          {issues.length > 25 ? (
            <p className="mt-3 text-[12px] text-[var(--text-faint)]">
              and {issues.length - 25} more
            </p>
          ) : null}
        </div>
      ) : null}
    </Card>
  );
}

/** The exact value that failed — never a generic message. */
function Evidence({ evidence }: { evidence: Record<string, unknown> }) {
  const entries = Object.entries(evidence).filter(
    ([, v]) => v !== null && v !== undefined && !(Array.isArray(v) && v.length === 0),
  );
  if (entries.length === 0) return null;

  return (
    <span className="mt-0.5 block truncate text-[12px] text-[var(--text-muted)]">
      {entries.slice(0, 3).map(([key, value]) => (
        <span key={key} className="mr-3">
          <span className="text-[var(--text-faint)]">{key}:</span>{' '}
          {Array.isArray(value)
            ? `${value.length} item${value.length === 1 ? '' : 's'}`
            : typeof value === 'object'
              ? JSON.stringify(value).slice(0, 60)
              : String(value).slice(0, 80)}
        </span>
      ))}
    </span>
  );
}
