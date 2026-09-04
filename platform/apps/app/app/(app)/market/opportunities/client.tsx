'use client';

import { useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { actOnOpportunity, refreshOpportunities } from '../actions';

export type OpportunityRow = {
  id: string;
  kind: string;
  query: string | null;
  page: string | null;
  score: number;
  evidence: Record<string, unknown>;
  status: string;
  detected_on: string;
};

/**
 * Each finder gets its own sentence.
 *
 * A generic "opportunity" card is unusable: the whole value is that somebody
 * can read one line and know whether to act, and the action for a low
 * click-through (rewrite a title) is nothing like the action for decay (update
 * the page) or cannibalisation (merge two).
 */
const KINDS: Record<string, { label: string; unit: string; explain: (e: Record<string, unknown>) => string }> = {
  striking_distance: {
    label: 'Page two',
    unit: 'impressions',
    explain: (e) =>
      `Ranking ${e.position} with ${fmt(e.impressions)} impressions. Six places would put this on page one.`,
  },
  low_ctr: {
    label: 'Ignored',
    unit: 'clicks missed',
    explain: (e) =>
      `Position ${e.position} earns ${pct(e.actualCtr)} where ${pct(e.expectedCtr)} is normal — about ${fmt(e.missedClicks)} clicks a month, for a title rewrite.`,
  },
  content_decay: {
    label: 'Fading',
    unit: 'clicks lost',
    explain: (e) =>
      `${fmt(e.clicksBefore)} clicks then, ${fmt(e.clicksAfter)} now — down ${e.dropPct}%. ` +
      (e.rankingHeld
        ? 'The ranking held, so the search moved on rather than the page slipping.'
        : `It slipped from ${e.positionBefore} to ${e.positionAfter ?? 'out of sight'}.`),
  },
  cannibalisation: {
    label: 'Competing',
    unit: 'impressions split',
    explain: (e) => {
      const pages = (e.pages as { page: string; position: number }[]) ?? [];
      return `${pages.length} of your pages rank for this, the best at ${pages[0]?.position}. Pick one and point the others at it.`;
    },
  },
  rising_query: {
    label: 'Rising',
    unit: 'new impressions',
    explain: (e) =>
      e.isNew
        ? `New demand: ${fmt(e.impressionsAfter)} impressions where there were none, currently at ${e.position}.`
        : `Up from ${fmt(e.impressionsBefore)} to ${fmt(e.impressionsAfter)} impressions, currently at ${e.position}.`,
  },
};

export function OpportunityList({
  rows,
  counts,
  activeKind,
  activeStatus,
}: {
  rows: OpportunityRow[];
  counts: { kind: string; n: number }[];
  activeKind: string | null;
  activeStatus: string;
}) {
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const refresh = () => {
    start(async () => {
      const result = await refreshOpportunities();
      toast(result.ok
        ? { kind: 'ok', message: `${result.found} opportunit${result.found === 1 ? 'y' : 'ies'} found.` }
        : { kind: 'error', message: result.error });
      router.refresh();
    });
  };

  const act = (id: string, status: 'actioned' | 'dismissed' | 'open') => {
    start(async () => {
      await actOnOpportunity(id, status);
      if (status !== 'open') {
        toast({
          kind: 'info',
          message: status === 'dismissed' ? 'Dismissed. It will not come back.' : 'Marked as done.',
          // Undoable, like every other destructive action here.
          onUndo: async () => {
            await actOnOpportunity(id, 'open');
            router.refresh();
          },
        });
      }
      router.refresh();
    });
  };

  return (
    <>
      <div className="mb-6 flex flex-wrap items-center gap-2">
        <Filter href="/market/opportunities" active={!activeKind && activeStatus === 'open'}>
          All open
        </Filter>
        {counts.map((c) => (
          <Filter
            key={c.kind}
            href={`/market/opportunities?kind=${c.kind}`}
            active={activeKind === c.kind}
          >
            {KINDS[c.kind]?.label ?? c.kind} · {c.n}
          </Filter>
        ))}
        <Filter href="/market/opportunities?status=dismissed" active={activeStatus === 'dismissed'}>
          Dismissed
        </Filter>
        <span className="grow" />
        <Button variant="ghost" onClick={refresh} disabled={pending}>
          {pending ? 'Recomputing…' : 'Recompute'}
        </Button>
      </div>

      {rows.length === 0 ? (
        <EmptyState
          title={activeStatus === 'dismissed' ? 'Nothing dismissed' : 'Nothing to do'}
          description={
            activeStatus === 'dismissed'
              ? 'Anything you dismiss lands here, and stays dismissed.'
              : 'Either the data is still syncing, or your pages are doing what they should. Recompute after the next Search Console sync.'
          }
        />
      ) : (
        <>
          <SectionLabel>{rows.length} found</SectionLabel>
          <div className="mt-3 grid gap-3">
            {rows.map((row) => {
              const kind = KINDS[row.kind];
              return (
                <Card key={row.id}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <StatusBadge status={toneFor(row.kind)}>
                          {kind?.label ?? row.kind}
                        </StatusBadge>
                        <span className="text-[13px] tabular-nums text-[var(--text-faint)]">
                          {fmt(row.score)} {kind?.unit ?? ''}
                        </span>
                      </div>
                      <p className="mt-2 text-[16px] text-[var(--text-primary)]">
                        {row.query ?? row.page}
                      </p>
                      {row.query && row.page ? (
                        <p className="mt-0.5 max-w-[70ch] truncate text-[13px] text-[var(--text-faint)]">
                          {row.page}
                        </p>
                      ) : null}
                      {/* The card shows its working — nobody acts on a number
                          they cannot check. */}
                      <p className="mt-2 max-w-[75ch] text-[14px] text-[var(--text-secondary)]">
                        {kind ? kind.explain(row.evidence) : JSON.stringify(row.evidence)}
                      </p>
                      {/*
                        The finders overlap, and the overlap is the priority
                        signal: a page-two query that is *also* rising is worth
                        doing before one that is not. Shown as a note rather
                        than a second card, because it is the same job.
                      */}
                      <AlsoSeen evidence={row.evidence} />
                    </div>

                    {row.status === 'open' ? (
                      <div className="flex shrink-0 gap-1">
                        <Button size="sm" variant="quiet" disabled={pending}
                                onClick={() => act(row.id, 'actioned')}>
                          Done
                        </Button>
                        <Button size="sm" variant="quiet" disabled={pending}
                                onClick={() => act(row.id, 'dismissed')}>
                          Dismiss
                        </Button>
                      </div>
                    ) : (
                      <Button size="sm" variant="quiet" disabled={pending}
                              onClick={() => act(row.id, 'open')}>
                        Reopen
                      </Button>
                    )}
                  </div>
                </Card>
              );
            })}
          </div>
        </>
      )}
    </>
  );
}

function AlsoSeen({ evidence }: { evidence: Record<string, unknown> }) {
  const also = evidence.alsoSeen as { kind: string }[] | undefined;
  if (!also || also.length === 0) return null;
  return (
    <p className="mt-1 text-[13px] text-[var(--text-faint)]">
      {also
        .map((o) => {
          const label = KINDS[o.kind]?.label ?? o.kind;
          return o.kind === 'rising_query'
            ? 'and demand for it is growing'
            : `also flagged as ${label.toLowerCase()}`;
        })
        .join(' · ')}
    </p>
  );
}

function Filter({
  href, active, children,
}: {
  href: string; active: boolean; children: React.ReactNode;
}) {
  return (
    <NextLink
      href={href}
      className={`rounded-[9999px] border px-3 py-1 text-[13px] transition-colors duration-[120ms] ${
        active
          ? 'border-[var(--accent-solid)] bg-[var(--accent-wash)] text-[var(--accent)]'
          : 'border-[var(--border-hairline)] text-[var(--text-secondary)] hover:bg-[var(--surface-ground)]'
      }`}
    >
      {children}
    </NextLink>
  );
}

const toneFor = (kind: string) =>
  kind === 'content_decay' ? 'warn'
  : kind === 'cannibalisation' ? 'error'
  : kind === 'rising_query' ? 'ok'
  : 'info';

const fmt = (value: unknown) =>
  typeof value === 'number' ? Math.round(value).toLocaleString() : String(value ?? '—');

const pct = (value: unknown) =>
  typeof value === 'number' ? `${(value * 100).toFixed(1)}%` : '—';
