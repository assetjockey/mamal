'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { GRID_SIZES, gridCost, type GridRun, type LocalProfile, type ReviewRow } from '@mamal/tool-market/scoring';
import { replyToReview, runRankGrid } from '../actions';

export function LocalBoard({
  profiles,
  grids,
  reviews,
}: {
  profiles: LocalProfile[];
  grids: GridRun[];
  reviews: ReviewRow[];
}) {
  if (profiles.length === 0) {
    return (
      <EmptyState
        title="No business profile connected"
        description="Connect Google Business from Connections. The profile check, the review triage and the rank grid all read from it."
      />
    );
  }

  const profile = profiles[0]!;

  return (
    <div className="flex flex-col gap-8">
      <ProfileHealth profile={profile} />
      <RankGrids profile={profile} grids={grids} />
      <Reviews profile={profile} reviews={reviews} />
    </div>
  );
}

/* ------------------------------------------------------------- profile */

function ProfileHealth({ profile }: { profile: LocalProfile }) {
  return (
    <section className="flex flex-col gap-3">
      <SectionLabel>{profile.name}</SectionLabel>
      <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <Card>
          <div className="text-[11px] text-[var(--text-secondary)]">Rating</div>
          <div className="mt-1 text-[20px] leading-none font-light tabular-nums">
            {profile.rating === null ? '—' : profile.rating.toFixed(1)}
          </div>
        </Card>
        <Card>
          <div className="text-[11px] text-[var(--text-secondary)]">Reviews</div>
          <div className="mt-1 text-[20px] leading-none font-light tabular-nums">
            {profile.reviewCount.toLocaleString()}
          </div>
        </Card>
        <Card>
          <div className="text-[11px] text-[var(--text-secondary)]">Category</div>
          <div className="mt-1 truncate text-[14px]">{profile.primaryCategory ?? 'Not set'}</div>
        </Card>
        <Card>
          <div className="text-[11px] text-[var(--text-secondary)]">Map pin</div>
          <div className="mt-1 text-[14px]">
            {profile.latitude === null ? 'Missing' : 'Set'}
          </div>
        </Card>
      </div>

      {profile.gaps.length === 0 ? (
        <p className="text-[12px] text-[var(--text-secondary)]">
          Nothing obvious missing from the profile.
        </p>
      ) : (
        <ul className="flex flex-col gap-1 text-[12px]">
          {profile.gaps.map((gap) => (
            <li key={gap} className="flex gap-2">
              <span aria-hidden className="text-[var(--status-warn)]">•</span>
              <span>{gap}</span>
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}

/* ---------------------------------------------------------------- grid */

function RankGrids({ profile, grids }: { profile: LocalProfile; grids: GridRun[] }) {
  const [keyword, setKeyword] = useState('');
  const [size, setSize] = useState<number>(5);
  const [radius, setRadius] = useState(5);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const cost = gridCost(size as (typeof GRID_SIZES)[number], 1);
  const pinned = profile.latitude !== null && profile.longitude !== null;

  const run = () => {
    start(async () => {
      const result = await runRankGrid({ profileId: profile.id, keyword, size, radiusKm: radius });
      toast(
        result.ok
          ? {
              kind: 'ok',
              message: `Ran ${result.points} points. You appear at ${Math.round(result.coverage * 100)}% of them.`,
            }
          : { kind: 'error', message: result.error },
      );
      router.refresh();
    });
  };

  return (
    <section className="flex flex-col gap-3">
      <SectionLabel>Rank grid</SectionLabel>
      <p className="text-[12px] text-[var(--text-secondary)]">
        A single &ldquo;position 4&rdquo; means little locally — you can be first in your own
        street and absent two miles away. The grid samples the ranking at points around you and
        reads it as a map.
      </p>

      <div className="flex flex-wrap items-end gap-2">
        <label className="flex min-w-0 flex-[2] flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Keyword</span>
          <input
            value={keyword}
            onChange={(e) => setKeyword(e.target.value)}
            placeholder="emergency plumber"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <label className="flex min-w-0 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Grid</span>
          <select
            value={size}
            onChange={(e) => setSize(Number(e.target.value))}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            {GRID_SIZES.map((s) => (
              <option key={s} value={s}>{s}×{s} · {s * s} points</option>
            ))}
          </select>
        </label>
        <label className="flex min-w-0 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Radius</span>
          <select
            value={radius}
            onChange={(e) => setRadius(Number(e.target.value))}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            {[1, 2, 5, 10, 20].map((r) => <option key={r} value={r}>{r} km</option>)}
          </select>
        </label>
        {/* Every point is a paid lookup, so the figure is on the button. */}
        <Button onClick={run} disabled={pending || !keyword.trim() || !pinned}>
          {pending ? 'Running…' : `Run grid · ${cost} credits`}
        </Button>
      </div>

      {!pinned && (
        <p className="text-[12px] text-[var(--status-warn)]">
          This profile has no map pin, so there is nowhere to centre a grid on.
        </p>
      )}

      {grids.length === 0 ? (
        <EmptyState
          title="No grid yet"
          description="Pick the phrase your customers actually search for — the one you would want to be first for when somebody is standing nearby."
        />
      ) : (
        <div className="flex flex-col gap-4">
          {grids.map((grid) => (
            <Grid key={grid.keyword} grid={grid} />
          ))}
        </div>
      )}
    </section>
  );
}

/** The colour ramp. Green for the pack, amber for the page, grey for absent. */
function cellTone(position: number | null): string {
  if (position === null) return 'bg-[var(--surface-hover)] text-[var(--text-secondary)]';
  if (position <= 3) return 'bg-[var(--status-ok)] text-white';
  if (position <= 10) return 'bg-[var(--status-warn)] text-white';
  return 'bg-[var(--status-error)] text-white';
}

function Grid({ grid }: { grid: GridRun }) {
  const { summary } = grid;

  return (
    <Card>
      <div className="flex flex-wrap items-baseline justify-between gap-2">
        <span className="text-[14px]">{grid.keyword}</span>
        <span className="text-[11px] text-[var(--text-secondary)]">{grid.capturedOn}</span>
      </div>

      <div className="mt-3 flex flex-wrap items-start gap-6">
        <div className="overflow-x-auto">
          <table>
            <caption className="sr-only">
              Ranking for {grid.keyword} at each point on a {grid.size} by {grid.size} grid
            </caption>
            <tbody>
              {Array.from({ length: grid.size }, (_, row) => (
                <tr key={row}>
                  {Array.from({ length: grid.size }, (_, col) => {
                    const cell = grid.readings.find((r) => r.row === row && r.col === col);
                    const position = cell?.position ?? null;
                    return (
                      <td key={col} className="p-[2px]">
                        <span
                          className={
                            'flex size-6 items-center justify-center rounded-[2px] text-[10px] tabular-nums ' +
                            cellTone(position)
                          }
                          // The number is in the cell, but a screen reader
                          // needs the position stated rather than a colour.
                          title={position === null ? 'Not found here' : `Position ${position}`}
                        >
                          {position ?? '·'}
                        </span>
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <dl className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <Row term="You appear at" value={`${Math.round(summary.coverage * 100)}% of points`} />
          <Row term="In the top three" value={`${Math.round(summary.inTopThree * 100)}% of the area`} />
          <Row
            term="Average where you appear"
            // Absences are carried by coverage, not folded into the average —
            // substituting a sentinel makes strong-but-narrow look weak.
            value={summary.averagePosition === null ? 'nowhere' : String(summary.averagePosition)}
          />
          <Row
            term="At your own address"
            value={summary.atCentre === null ? 'not found' : String(summary.atCentre)}
          />
        </dl>
      </div>

      {summary.weakest.length > 0 && (
        <p className="mt-3 text-[12px]">
          <span className="text-[var(--text-secondary)]">Weakest: </span>
          {summary.weakest
            .map((w) =>
              w.coverage === 0
                ? `${w.direction} (absent)`
                : `${w.direction} (${Math.round(w.coverage * 100)}%, avg ${w.averagePosition})`,
            )
            .join(', ')}
        </p>
      )}
    </Card>
  );
}

function Row({ term, value }: { term: string; value: string }) {
  return (
    <div className="flex items-baseline justify-between gap-3">
      <dt className="text-[var(--text-secondary)]">{term}</dt>
      <dd className="shrink-0 tabular-nums">{value}</dd>
    </div>
  );
}

/* ------------------------------------------------------------- reviews */

function Reviews({ profile, reviews }: { profile: LocalProfile; reviews: ReviewRow[] }) {
  const [drafts, setDrafts] = useState<Record<string, string>>({});
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const unanswered = reviews.filter((r) => !r.reply);

  return (
    <section className="flex flex-col gap-3">
      <SectionLabel>Reviews</SectionLabel>
      <p className="text-[12px] text-[var(--text-secondary)]">
        Ordered by what needs answering soonest — a new low rating sits at the top of your
        profile where everybody sees it. That ordering is arithmetic, so it works with AI off.
      </p>

      {unanswered.length === 0 ? (
        <EmptyState
          title="Everything answered"
          description="New reviews appear here in the order they need attention."
        />
      ) : (
        <ul className="flex flex-col gap-2">
          {unanswered.map((review) => (
            <li key={review.id}>
              <Card>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="text-[14px]">
                    {review.author ?? 'Anonymous'}
                    <span className="ml-2 tabular-nums text-[var(--text-secondary)]">
                      {review.rating === null ? '—' : `${review.rating}/5`}
                    </span>
                  </span>
                  <StatusBadge
                    status={review.urgency >= 12 ? 'error' : review.urgency >= 5 ? 'warn' : 'info'}
                  >
                    {review.reason}
                  </StatusBadge>
                </div>
                {review.comment && <p className="mt-2 text-[13px]">{review.comment}</p>}

                <div className="mt-3 flex flex-col gap-2">
                  <label className="flex flex-col gap-1 text-[12px]">
                    <span className="text-[var(--text-secondary)]">Your reply</span>
                    <textarea
                      value={drafts[review.id] ?? ''}
                      onChange={(e) =>
                        setDrafts((prev) => ({ ...prev, [review.id]: e.target.value }))
                      }
                      rows={3}
                      className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-2 text-[13px]"
                    />
                  </label>
                  <div className="flex flex-wrap gap-2">
                    <Button
                      disabled={pending || !(drafts[review.id] ?? '').trim()}
                      onClick={() =>
                        start(async () => {
                          const result = await replyToReview({
                            profileId: profile.id,
                            reviewId: review.id,
                            reply: drafts[review.id] ?? '',
                          });
                          if (!result.ok) toast({ kind: 'error', message: result.error });
                          router.refresh();
                        })
                      }
                    >
                      Reply
                    </Button>
                    <Button
                      variant="ghost"
                      disabled={pending}
                      onClick={() =>
                        start(async () => {
                          const result = await replyToReview({
                            profileId: profile.id,
                            reviewId: review.id,
                            draft: true,
                            businessName: profile.name,
                          });
                          if (!result.ok) {
                            // Includes the reason AI is unavailable, and that
                            // the review still needs a reply either way.
                            toast({ kind: 'error', message: result.error });
                            return;
                          }
                          setDrafts((prev) => ({ ...prev, [review.id]: result.draft ?? '' }));
                        })
                      }
                    >
                      Draft one for me
                    </Button>
                  </div>
                </div>
              </Card>
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}
