'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { saveTrendWatch } from '../actions';

type Watch = {
  id: string;
  name: string;
  keywords: string[];
  geos: string[];
  thresholdPct: number;
  lastRunAt: string | null;
  recent: { keyword: string; geo: string; deltaPct: number; at: string }[];
};

const geoLabel = (geo: string) => (geo.trim() === '' ? 'worldwide' : geo.toUpperCase());

export function TrendBoard({ watches }: { watches: Watch[] }) {
  const [pending, start] = useTransition();
  const [name, setName] = useState('');
  const [keywords, setKeywords] = useState('');
  const [geos, setGeos] = useState('');
  const [threshold, setThreshold] = useState(25);
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    const terms = keywords.split(',').map((k) => k.trim()).filter(Boolean);
    if (!name.trim() || terms.length === 0) return;

    start(async () => {
      const result = await saveTrendWatch({
        name,
        keywords: terms,
        geos: geos.split(',').map((g) => g.trim()).filter(Boolean),
        thresholdPct: threshold,
      });
      toast(
        result.ok
          ? {
              kind: 'ok',
              // Says what happens next, because "nothing happened" is the
              // expected first result and looks like a bug otherwise.
              message: 'Watching. The first check records a baseline and alerts on nothing.',
            }
          : { kind: 'error', message: result.error },
      );
      if (result.ok) {
        setName('');
        setKeywords('');
      }
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-3">
        <SectionLabel>New watch</SectionLabel>
        <div className="flex flex-wrap items-end gap-2">
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Name</span>
            <input
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Widget categories"
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            />
          </label>
          <label className="flex min-w-0 flex-[2] flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Terms, comma separated</span>
            <input
              value={keywords}
              onChange={(e) => setKeywords(e.target.value)}
              placeholder="widget racks, widget mounts"
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            />
          </label>
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Regions</span>
            <input
              value={geos}
              onChange={(e) => setGeos(e.target.value)}
              placeholder="US, GB — blank for worldwide"
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            />
          </label>
          <label className="flex w-[7rem] min-w-0 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Tell me at</span>
            <select
              value={threshold}
              onChange={(e) => setThreshold(Number(e.target.value))}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              <option value={10}>±10%</option>
              <option value={25}>±25%</option>
              <option value={50}>±50%</option>
            </select>
          </label>
          <Button onClick={create} disabled={pending || !name.trim() || !keywords.trim()}>
            Watch
          </Button>
        </div>
        <p className="text-[12px] text-[var(--text-secondary)]">
          Each region is measured on its own — a term rising in Brazil and flat in Germany is two
          facts, and averaging them describes nowhere. Moves between very small numbers are
          ignored: on a 0–100 index, 1 to 3 is a 200% rise and four extra searches.
        </p>
      </section>

      {watches.length === 0 ? (
        <EmptyState
          title="Nothing watched yet"
          description="Add the terms your buyers use. The first check stores a baseline; after that you only hear about real movement."
        />
      ) : (
        <section className="flex flex-col gap-3">
          <SectionLabel>Watching</SectionLabel>
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {watches.map((watch) => (
              <Card key={watch.id}>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="text-[14px]">{watch.name}</span>
                  <span className="text-[11px] text-[var(--text-secondary)]">
                    ±{watch.thresholdPct}%
                  </span>
                </div>
                <p className="mt-2 text-[12px] text-[var(--text-secondary)]">
                  {watch.keywords.join(', ')} · {watch.geos.map(geoLabel).join(', ')}
                </p>

                {watch.recent.length === 0 ? (
                  <p className="mt-3 text-[12px] text-[var(--text-secondary)]">
                    {watch.lastRunAt
                      ? 'Checked, nothing has moved enough to mention.'
                      : 'Not checked yet — the first run records the baseline.'}
                  </p>
                ) : (
                  <ul className="mt-3 flex flex-col gap-1 text-[12px]">
                    {watch.recent.map((event, i) => (
                      <li key={`${event.keyword}-${event.at}-${i}`} className="flex items-baseline justify-between gap-2">
                        <span className="min-w-0 truncate">
                          {event.keyword}{' '}
                          <span className="text-[var(--text-secondary)]">{geoLabel(event.geo)}</span>
                        </span>
                        <StatusBadge status={event.deltaPct > 0 ? 'ok' : 'warn'}>
                          {event.deltaPct > 0 ? '+' : ''}
                          {Math.round(event.deltaPct)}%
                        </StatusBadge>
                      </li>
                    ))}
                  </ul>
                )}
              </Card>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
