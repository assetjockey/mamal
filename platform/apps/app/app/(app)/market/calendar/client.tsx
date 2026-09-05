'use client';

import { useMemo, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { DAYS, NETWORKS, defaultSlots, type Slots } from '@mamal/tool-market/scoring';
import { saveAccountQueue } from '../actions';

type Account = {
  id: string; provider: string; handle: string | null; displayName: string;
  followers: number | null; slots: Slots | null; timezone: string; queued: number;
};

type Post = {
  id: string; body: string; status: string; scheduledAt: string | null;
  targets: { provider: string; displayName: string; status: string; at: string | null; error: string | null; url: string | null }[];
};

const DAY_LABEL: Record<string, string> = {
  mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun',
};

export function Calendar({ accounts, posts }: { accounts: Account[]; posts: Post[] }) {
  /**
   * Upcoming, grouped by the day it goes out.
   *
   * By *target*, not by post: one post to three networks at three different
   * slot times is three entries on the calendar, because that is what a person
   * looking at Thursday needs to know.
   */
  const upcoming = useMemo(() => {
    const entries: { at: string; provider: string; body: string; postId: string; status: string }[] = [];
    for (const post of posts) {
      for (const target of post.targets) {
        if (!target.at || target.status === 'failed' || target.status === 'skipped') continue;
        entries.push({
          at: target.at, provider: target.provider, body: post.body,
          postId: post.id, status: target.status,
        });
      }
    }
    entries.sort((a, b) => a.at.localeCompare(b.at));

    const byDay = new Map<string, typeof entries>();
    for (const entry of entries) {
      const day = entry.at.slice(0, 10);
      byDay.set(day, [...(byDay.get(day) ?? []), entry]);
    }
    return [...byDay.entries()];
  }, [posts]);

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-3">
        <SectionLabel>Coming up</SectionLabel>
        {upcoming.length === 0 ? (
          <EmptyState
            title="Nothing scheduled"
            description="Posts you queue appear here, one entry per network — three networks at three slot times is three things to know about, not one."
          />
        ) : (
          <div className="flex flex-col gap-4">
            {upcoming.map(([day, entries]) => (
              <div key={day} className="flex flex-col gap-2">
                <h3 className="text-[12px] text-[var(--text-secondary)]">
                  {new Date(`${day}T12:00:00Z`).toLocaleDateString(undefined, {
                    weekday: 'long', day: 'numeric', month: 'long',
                  })}
                </h3>
                <ul className="flex flex-col gap-2">
                  {entries.map((entry, i) => (
                    <li key={`${entry.postId}-${entry.provider}-${i}`}>
                      <Card>
                        <div className="flex flex-wrap items-baseline justify-between gap-2">
                          <span className="text-[12px] tabular-nums text-[var(--text-secondary)]">
                            {entry.at.slice(11, 16)}
                          </span>
                          <StatusBadge status={entry.status === 'published' ? 'ok' : 'info'}>
                            {NETWORKS[entry.provider]?.label ?? entry.provider}
                          </StatusBadge>
                        </div>
                        <p className="mt-1 max-w-[70ch] truncate text-[14px]">
                          {entry.body || '(no text)'}
                        </p>
                      </Card>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        )}
      </section>

      <section className="flex flex-col gap-3">
        <SectionLabel>Slots</SectionLabel>
        <p className="text-[12px] text-[var(--text-secondary)]">
          The hours each account posts in, read in that account&rsquo;s own timezone — so a 09:00
          slot stays 09:00 when the clocks change.
        </p>
        {accounts.length === 0 ? (
          <EmptyState
            title="No accounts connected"
            description="Slots belong to an account — connect a channel from Connections and its weekly grid appears here."
          />
        ) : (
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {accounts.map((account) => (
              <QueueGrid key={account.id} account={account} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}

/** Hours offered in the grid. A slot every hour would be unreadable and unwise. */
const HOURS = [6, 8, 9, 10, 12, 14, 16, 17, 19, 21];

function QueueGrid({ account }: { account: Account }) {
  const [slots, setSlots] = useState<Slots>(() => account.slots ?? defaultSlots());
  const [timezone, setTimezone] = useState(account.timezone);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const toggle = (day: string, hour: number) => {
    setSlots((prev) => {
      const hours = prev[day] ?? [];
      return {
        ...prev,
        [day]: hours.includes(hour)
          ? hours.filter((h) => h !== hour)
          : [...hours, hour].sort((a, b) => a - b),
      };
    });
  };

  const save = () => {
    start(async () => {
      const result = await saveAccountQueue(account.id, slots, timezone);
      toast(
        result.ok
          ? { kind: 'ok', message: 'Slots saved.' }
          : { kind: 'error', message: result.error },
      );
      router.refresh();
    });
  };

  const total = Object.values(slots).flat().length;

  return (
    <Card>
      <div className="flex flex-wrap items-baseline justify-between gap-2">
        <span className="text-[14px]">
          {NETWORKS[account.provider]?.label ?? account.provider}
          <span className="text-[var(--text-secondary)]"> · {account.displayName}</span>
        </span>
        <span className="text-[11px] text-[var(--text-secondary)] tabular-nums">
          {account.queued} queued
        </span>
      </div>

      <div className="mt-3 overflow-x-auto">
        <table className="text-[11px]">
          <caption className="sr-only">
            Posting slots for {account.displayName}, by day and hour
          </caption>
          <thead>
            <tr>
              <th scope="col" className="pr-2 text-left font-normal text-[var(--text-secondary)]">
                <span className="sr-only">Day</span>
              </th>
              {HOURS.map((hour) => (
                <th key={hour} scope="col" className="px-1 font-normal text-[var(--text-secondary)] tabular-nums">
                  {String(hour).padStart(2, '0')}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {DAYS.map((day) => (
              <tr key={day}>
                <th scope="row" className="pr-2 text-left font-normal text-[var(--text-secondary)]">
                  {DAY_LABEL[day]}
                </th>
                {HOURS.map((hour) => {
                  const on = (slots[day] ?? []).includes(hour);
                  return (
                    <td key={hour} className="p-[2px]">
                      <button
                        type="button"
                        onClick={() => toggle(day, hour)}
                        aria-pressed={on}
                        aria-label={`${DAY_LABEL[day]} ${String(hour).padStart(2, '0')}:00`}
                        className={
                          'block size-4 rounded-[2px] border ' +
                          (on
                            ? 'border-[var(--accent)] bg-[var(--accent)]'
                            : 'border-[var(--border)] hover:bg-[var(--surface-hover)]')
                        }
                      />
                    </td>
                  );
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="mt-3 flex flex-wrap items-end gap-2">
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Timezone</span>
          <input
            value={timezone}
            onChange={(e) => setTimezone(e.target.value)}
            placeholder="Europe/London"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <Button onClick={save} disabled={pending}>Save</Button>
      </div>

      <p className="mt-2 text-[11px] text-[var(--text-secondary)]">
        {total === 0
          // An empty grid is not "post immediately" — it is a queue with
          // nowhere to put anything, and the composer says so rather than
          // guessing.
          ? 'No slots: queued posts will have nowhere to go.'
          : `${total} slot${total === 1 ? '' : 's'} a week.`}
      </p>
    </Card>
  );
}
