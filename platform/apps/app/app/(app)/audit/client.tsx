'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useCallback, useEffect, useRef, useState, useTransition } from 'react';
import { Button, StatusBadge, useToast } from '@mamal/ui';
import { auditProgress, cancelAudit, enableAudit, queueAudit, type AuditProgress } from './actions';

/** The score, as a quiet monument rather than a gauge. */
export function ScoreDial({
  score,
  grade,
  delta,
}: {
  score: number | null;
  grade: string | null;
  delta: number | null;
}) {
  if (score === null) {
    return <span className="text-[32px] leading-none text-[var(--text-faint)]">—</span>;
  }
  const tone =
    score >= 90 ? 'var(--color-status-ok)' : score >= 70 ? 'var(--color-status-warn)' : 'var(--color-status-error)';
  return (
    <div className="shrink-0 text-right">
      <div className="text-[32px] leading-[1.1] tracking-[-0.64px] tabular-nums" style={{ color: tone }}>
        {score}
      </div>
      <div className="mt-0.5 flex items-center justify-end gap-1.5 text-[12px] text-[var(--text-faint)]">
        <span>Grade {grade}</span>
        {delta !== null && delta !== 0 ? (
          <span style={{ color: delta > 0 ? 'var(--color-status-ok)' : 'var(--color-status-error)' }}>
            {delta > 0 ? '+' : ''}
            {delta}
          </span>
        ) : null}
      </div>
    </div>
  );
}

const PHASE_LABEL: Record<string, string> = {
  queued: 'Queued',
  discovering: 'Reading robots.txt',
  crawling: 'Crawling',
  analyzing: 'Analysing',
  lighthouse: 'Measuring speed',
  scoring: 'Scoring',
  done: 'Done',
  failed: 'Failed',
};

export function AuditActions({
  auditSiteId,
  siteId,
  host,
  runningAuditId,
}: {
  auditSiteId: string | null;
  siteId: string;
  host: string;
  runningAuditId?: string | null;
}) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const [message, setMessage] = useState<{ tone: 'ok' | 'error'; text: string } | null>(null);
  const [activeId, setActiveId] = useState<string | null>(runningAuditId ?? null);
  const [progress, setProgress] = useState<AuditProgress | null>(null);
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

  /**
   * Poll while a crawl is in flight.
   *
   * The counter is real — it comes from the audit row the worker updates after
   * every slice — so this shows progress rather than an indeterminate spinner.
   */
  const poll = useCallback(
    async (auditId: string) => {
      const next = await auditProgress(auditId);
      if (!next) return;
      setProgress(next);

      if (next.status === 'completed' || next.status === 'cancelled' || next.status === 'failed') {
        setActiveId(null);
        setMessage(
          next.status === 'completed'
            ? { tone: 'ok', text: `Score ${next.score} · ${next.pagesCrawled} pages` }
            : { tone: 'error', text: next.errorDetail?.slice(0, 60) ?? next.status },
        );
        router.refresh();
        return;
      }
      timer.current = setTimeout(() => void poll(auditId), 1200);
    },
    [router],
  );

  useEffect(() => {
    if (activeId) void poll(activeId);
    return () => {
      if (timer.current) clearTimeout(timer.current);
    };
  }, [activeId, poll]);

  if (!auditSiteId) {
    return (
      <Button
        size="sm"
        variant="ghost"
        disabled={pending}
        onClick={() => start(async () => {
          await enableAudit(siteId, host);
          router.refresh();
        })}
      >
        {pending ? 'Enabling…' : 'Enable Audit'}
      </Button>
    );
  }

  if (activeId) {
    const phase = PHASE_LABEL[progress?.phase ?? 'queued'] ?? 'Working';
    return (
      <div className="flex flex-wrap items-center justify-end gap-3">
        <span className="text-[12px] tabular-nums text-[var(--text-secondary)]">
          {phase}
          {progress && progress.pagesCrawled > 0 ? (
            <>
              {' · '}
              {progress.pagesCrawled}
              {progress.pagesTotal > progress.pagesCrawled ? ` of ~${progress.pagesTotal}` : ''} pages
            </>
          ) : null}
        </span>
        <Button
          size="sm"
          variant="quiet"
          onClick={() => start(async () => {
            await cancelAudit(activeId);
            /*
             * Deliberately no Undo. Cancelling is not data loss — the slices
             * already crawled are scored and kept — and "undo" would have to
             * mean a fresh crawl, which spends quota again. Offering a button
             * that quietly bills the user is worse than offering none, so the
             * toast says what was kept instead.
             */
            toast({ message: 'Audit cancelled. Pages crawled so far were scored.' });
          })}
        >
          Cancel
        </Button>
      </div>
    );
  }

  return (
    <div className="flex flex-wrap items-center justify-end gap-3">
      {message ? (
        <StatusBadge status={message.tone === 'ok' ? 'ok' : 'error'}>{message.text}</StatusBadge>
      ) : null}
      <Link href={`/audit/issues?site=${auditSiteId}`}>
        <Button size="sm" variant="quiet">Issues</Button>
      </Link>
      <Button
        size="sm"
        disabled={pending}
        onClick={() => start(async () => {
          setMessage(null);
          const result = await queueAudit(auditSiteId);
          if (result.ok) {
            setActiveId(result.auditId);
            setProgress(null);
          } else {
            setMessage({ tone: 'error', text: result.error.slice(0, 70) });
          }
        })}
      >
        {pending ? 'Starting…' : 'Run audit'}
      </Button>
    </div>
  );
}
