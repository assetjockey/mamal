'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Button, Card, StatusBadge, Table, Td, Th, Tr, useToast, type Status,
} from '@mamal/ui';
import { addTrackedKeywords, newRankConfig } from '../actions';

export type TrackerRow = {
  id: string; domain: string; schedule: string; is_active: boolean;
  last_run_at: string | null; next_check_at: string | null; keywords: number;
};

export type PositionRow = {
  keyword_id: string; keyword: string; config_id: string; device: string;
  position: number | null; previous_position: number | null;
  url: string | null; captured_on: string;
};

export function RankBoard({
  trackers,
  positions,
  canAdd,
}: {
  trackers: TrackerRow[];
  positions: PositionRow[];
  canAdd: boolean;
}) {
  const [domain, setDomain] = useState('');
  const [addingTo, setAddingTo] = useState<string | null>(null);
  const [keywords, setKeywords] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    setError(null);
    start(async () => {
      const result = await newRankConfig(domain.trim());
      if (!result.ok) { setError(result.error); return; }
      setDomain('');
      router.refresh();
    });
  };

  const addKeywords = (configId: string) => {
    setError(null);
    start(async () => {
      const result = await addTrackedKeywords(configId, keywords);
      if (!result.ok) { setError(result.error); return; }
      toast({
        kind: 'ok',
        message: `${result.added} keyword${result.added === 1 ? '' : 's'} added. The first check runs on the next sweep.`,
      });
      setAddingTo(null); setKeywords('');
      router.refresh();
    });
  };

  return (
    <>
      <div className="mb-6 flex flex-wrap items-start gap-2">
        <div>
          <label htmlFor="tracker-domain" className="sr-only">Domain to track</label>
          <input
            id="tracker-domain" value={domain} onChange={(e) => setDomain(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter' && domain.trim()) create(); }}
            placeholder="example.com"
            className="w-[min(280px,60vw)] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
          />
          {error ? (
            <p role="alert" className="mt-1 max-w-[280px] text-[12px] text-[var(--color-status-error)]">{error}</p>
          ) : null}
        </div>
        <Button onClick={create} disabled={pending || !domain.trim()}>New tracker</Button>
      </div>

      <div className="grid gap-4">
        {trackers.map((tracker) => {
          const rows = positions.filter((p) => p.config_id === tracker.id);
          return (
            <Card key={tracker.id}>
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0">
                  <h3 className="text-[20px] text-[var(--text-primary)]">{tracker.domain}</h3>
                  <p className="mt-0.5 text-[13px] text-[var(--text-faint)]">
                    {tracker.keywords} keyword{tracker.keywords === 1 ? '' : 's'} · {tracker.schedule}
                    {tracker.last_run_at
                      ? ` · last checked ${new Date(tracker.last_run_at).toLocaleDateString()}`
                      : ' · not checked yet'}
                  </p>
                </div>
                <div className="flex shrink-0 items-center gap-2">
                  <StatusBadge status={tracker.is_active ? 'ok' : 'neutral'}>
                    {tracker.is_active ? 'Active' : 'Paused'}
                  </StatusBadge>
                  {canAdd ? (
                    <Button size="sm" variant="quiet"
                            onClick={() => { setAddingTo(tracker.id); setError(null); }}>
                      Add keywords
                    </Button>
                  ) : null}
                </div>
              </div>

              {addingTo === tracker.id ? (
                <div className="mt-4 border-t border-[var(--border-hairline)] pt-3">
                  <label htmlFor={`kw-${tracker.id}`} className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
                    One per line
                  </label>
                  <textarea
                    id={`kw-${tracker.id}`} rows={3} autoFocus value={keywords}
                    onChange={(e) => setKeywords(e.target.value)}
                    className="w-full resize-y rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
                  />
                  <div className="mt-2 flex gap-2">
                    <Button size="sm" onClick={() => addKeywords(tracker.id)} disabled={pending || !keywords.trim()}>
                      Add
                    </Button>
                    <Button size="sm" variant="quiet" onClick={() => setAddingTo(null)}>Cancel</Button>
                  </div>
                </div>
              ) : null}

              {rows.length > 0 ? (
                <div className="mt-4">
                  <Table label={`Positions for ${tracker.domain}`}>
                    <thead>
                      <Tr>
                        <Th>Keyword</Th>
                        <Th align="right">Position</Th>
                        <Th align="right">Change</Th>
                        <Th>Ranking URL</Th>
                      </Tr>
                    </thead>
                    <tbody>
                      {rows.map((row) => (
                        <Tr key={`${row.keyword_id}-${row.device}`}>
                          <Td>
                            <span className="text-[var(--text-primary)]">{row.keyword}</span>
                            <span className="ml-2 text-[12px] text-[var(--text-faint)]">{row.device}</span>
                          </Td>
                          <Td align="right">
                            {/*
                              Null means "outside the tracked depth" — not zero,
                              and never a sentinel like 101. Saying so is the
                              difference between an honest average and a wrong one.
                            */}
                            <span className="tabular-nums">
                              {row.position ?? <span className="text-[var(--text-faint)]">not ranking</span>}
                            </span>
                          </Td>
                          <Td align="right"><Movement row={row} /></Td>
                          <Td>
                            <span className="block max-w-[36ch] truncate text-[13px] text-[var(--text-secondary)]">
                              {row.url ?? '—'}
                            </span>
                          </Td>
                        </Tr>
                      ))}
                    </tbody>
                  </Table>
                </div>
              ) : tracker.keywords > 0 ? (
                <p className="mt-4 text-[13px] text-[var(--text-muted)]">
                  Waiting for the first check.
                </p>
              ) : null}
            </Card>
          );
        })}
      </div>
    </>
  );
}

/**
 * Up is good, and up means the number went *down*. Worth being explicit.
 *
 * Arriving and disappearing get their own words rather than a dash: a keyword
 * that was nowhere and is now 27th is the most interesting row on the page, and
 * "—" is exactly what a reader skips.
 */
function Movement({ row }: { row: PositionRow }) {
  const { position, previous_position: previous } = row;

  if (previous === null && position !== null) {
    return <StatusBadge status="ok">New</StatusBadge>;
  }
  if (previous !== null && position === null) {
    return <StatusBadge status="error">Dropped out</StatusBadge>;
  }
  if (previous === null || position === null) {
    return <span className="text-[var(--text-faint)]">—</span>;
  }

  const delta = previous - position;
  if (delta === 0) return <span className="text-[var(--text-faint)]">—</span>;

  const tone: Status = delta > 0 ? 'ok' : 'warn';
  return (
    <StatusBadge status={tone}>
      {delta > 0 ? '↑' : '↓'} {Math.abs(delta)}
    </StatusBadge>
  );
}
