'use client';

import { useMemo, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Button, Card, EmptyState, SectionLabel, StatusBadge, useToast,
} from '@mamal/ui';
/*
 * The pure half of Market: per-network limits, character counting and the
 * queue grid. Not the barrel — see `scripts/check-client-imports.mjs`.
 */
import { NETWORKS, countCharacters, hashtagCount, validatePost } from '@mamal/tool-market/scoring';
import { createPost, reviewPost } from '../actions';

type Account = {
  id: string; provider: string; handle: string | null; displayName: string;
  followers: number | null; timezone: string; queued: number;
};

type Post = {
  id: string; body: string; status: string; scheduleType: string;
  scheduledAt: string | null; approvalState: string; campaign: string | null;
  targets: { provider: string; displayName: string; status: string; error: string | null; url: string | null; at: string | null }[];
};

export function Composer({ accounts, posts }: { accounts: Account[]; posts: Post[] }) {
  const [body, setBody] = useState('');
  const [link, setLink] = useState('');
  const [selected, setSelected] = useState<string[]>([]);
  const [when, setWhen] = useState<'now' | 'scheduled' | 'queue'>('queue');
  const [at, setAt] = useState('');
  const [images, setImages] = useState(0);

  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const providers = useMemo(
    () => [...new Set(accounts.filter((a) => selected.includes(a.id)).map((a) => a.provider))],
    [accounts, selected],
  );

  /*
   * Validated here, on every keystroke, by the same function the server runs at
   * compose time. The writer sees "Instagram needs an image" while typing
   * rather than discovering it from Instagram five hours later.
   */
  const problems = useMemo(
    () => validatePost({ body, images, link: link || null }, providers),
    [body, images, link, providers],
  );
  const errors = problems.filter((p) => p.level === 'error');
  const warnings = problems.filter((p) => p.level === 'warning');

  const submit = () => {
    start(async () => {
      const result = await createPost({
        body,
        accountIds: selected,
        link: link || null,
        images,
        scheduleType: when,
        scheduledAt: when === 'scheduled' && at ? new Date(at).toISOString() : undefined,
      });
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      const unplaced = result.scheduled.filter((s) => s.at === null);
      toast({
        kind: unplaced.length > 0 ? 'info' : 'ok',
        message:
          unplaced.length > 0
            // Never silently "now": an empty queue is a thing to say.
            ? `Saved. ${unplaced.map((s) => NETWORKS[s.provider]?.label ?? s.provider).join(', ')} has no free slot — add slots or pick a time.`
            : result.linkNote ?? `Scheduled to ${result.scheduled.length} account${result.scheduled.length === 1 ? '' : 's'}.`,
      });
      setBody('');
      setLink('');
      router.refresh();
    });
  };

  if (accounts.length === 0) {
    return (
      <EmptyState
        title="No accounts connected"
        description="Connect a channel from Connections and it appears here. One post then goes to all of them, checked against each network's own rules first."
      />
    );
  }

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4 xl:flex-row xl:items-start">
        <div className="flex min-w-0 flex-1 flex-col gap-3">
          <SectionLabel>New post</SectionLabel>

          <fieldset className="flex flex-wrap gap-2">
            <legend className="sr-only">Accounts to post to</legend>
            {accounts.map((account) => {
              const on = selected.includes(account.id);
              return (
                <label
                  key={account.id}
                  className={
                    'flex cursor-pointer items-center gap-2 rounded-[4px] border px-3 py-2 text-[12px] ' +
                    (on
                      ? 'border-[var(--accent)] bg-[var(--accent-wash)]'
                      : 'border-[var(--border)] hover:bg-[var(--surface-hover)]')
                  }
                >
                  <input
                    type="checkbox"
                    checked={on}
                    onChange={() =>
                      setSelected((prev) =>
                        on ? prev.filter((id) => id !== account.id) : [...prev, account.id],
                      )
                    }
                    className="size-3"
                  />
                  <span className="min-w-0 truncate">
                    {NETWORKS[account.provider]?.label ?? account.provider}
                    <span className="text-[var(--text-secondary)]"> · {account.displayName}</span>
                  </span>
                </label>
              );
            })}
          </fieldset>

          <label className="flex flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Post</span>
            <textarea
              value={body}
              onChange={(e) => setBody(e.target.value)}
              rows={6}
              placeholder="What are you announcing?"
              className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-3 text-[14px] leading-[1.5]"
            />
          </label>

          <div className="flex flex-wrap gap-3">
            <label className="flex min-w-0 flex-[2] flex-col gap-1 text-[12px]">
              <span className="text-[var(--text-secondary)]">Link (optional)</span>
              <input
                value={link}
                onChange={(e) => setLink(e.target.value)}
                placeholder="https://example.com/announcement"
                className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
              />
            </label>
            <label className="flex w-[6rem] min-w-0 flex-col gap-1 text-[12px]">
              <span className="text-[var(--text-secondary)]">Images</span>
              <input
                type="number"
                min={0}
                max={20}
                value={images}
                onChange={(e) => setImages(Math.max(0, Number(e.target.value) || 0))}
                className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px] tabular-nums"
              />
            </label>
            <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
              <span className="text-[var(--text-secondary)]">When</span>
              <select
                value={when}
                onChange={(e) => setWhen(e.target.value as typeof when)}
                className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
              >
                <option value="queue">Next free slot</option>
                <option value="scheduled">At a time</option>
                <option value="now">Now</option>
              </select>
            </label>
            {when === 'scheduled' && (
              <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
                <span className="text-[var(--text-secondary)]">Time</span>
                <input
                  type="datetime-local"
                  value={at}
                  onChange={(e) => setAt(e.target.value)}
                  className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
                />
              </label>
            )}
          </div>

          <div className="flex flex-wrap items-center gap-3">
            <Button
              onClick={submit}
              disabled={pending || selected.length === 0 || body.trim().length === 0 || errors.length > 0}
            >
              {pending ? 'Saving…' : when === 'now' ? 'Post now' : 'Schedule'}
            </Button>
            {errors.length > 0 && (
              <span className="text-[12px] text-[var(--status-error)]" aria-live="polite">
                {errors.length} thing{errors.length === 1 ? '' : 's'} to fix first
              </span>
            )}
          </div>
        </div>

        {/* ----------------------------------------------- per-network state */}
        <aside className="flex w-full min-w-0 flex-col gap-2 xl:w-[340px] xl:shrink-0">
          {providers.length === 0 ? (
            <p className="text-[12px] text-[var(--text-secondary)]">
              Pick an account and this shows what each network will make of the post.
            </p>
          ) : (
            providers.map((provider) => {
              const network = NETWORKS[provider];
              if (!network) return null;
              const count = countCharacters(body, network);
              const over = count > network.maxBody;
              const networkProblems = problems.filter((p) => p.network === provider);

              return (
                <Card key={provider}>
                  <div className="flex items-baseline justify-between gap-2">
                    <span className="text-[14px]">{network.label}</span>
                    <span
                      className={
                        'text-[12px] tabular-nums ' +
                        (over ? 'text-[var(--status-error)]' : 'text-[var(--text-secondary)]')
                      }
                    >
                      {count}/{network.maxBody}
                    </span>
                  </div>
                  {network.maxHashtags !== null && (
                    <p className="mt-1 text-[11px] text-[var(--text-secondary)]">
                      {hashtagCount(body)}/{network.maxHashtags} hashtags
                    </p>
                  )}
                  {networkProblems.length === 0 ? (
                    <p className="mt-2 text-[12px] text-[var(--text-secondary)]">Ready.</p>
                  ) : (
                    <ul className="mt-2 flex flex-col gap-1 text-[12px]">
                      {networkProblems.map((problem, i) => (
                        <li
                          key={`${problem.network}-${i}`}
                          className={
                            problem.level === 'error'
                              ? 'text-[var(--status-error)]'
                              : 'text-[var(--text-secondary)]'
                          }
                        >
                          {problem.message}
                        </li>
                      ))}
                    </ul>
                  )}
                </Card>
              );
            })
          )}
          {warnings.length > 0 && errors.length === 0 && (
            <p className="text-[11px] text-[var(--text-secondary)]">
              Warnings do not block — you can schedule and fix them later.
            </p>
          )}
        </aside>
      </section>

      {/* --------------------------------------------------------- the list */}
      {posts.length > 0 && (
        <section className="flex flex-col gap-3">
          <SectionLabel>Posts</SectionLabel>
          <div className="flex flex-col gap-2">
            {posts.map((post) => (
              <Card key={post.id}>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="min-w-0 flex-1 truncate text-[14px]">
                    {post.body || '(no text)'}
                  </span>
                  <StatusBadge status={statusTone(post.status)}>{post.status}</StatusBadge>
                </div>

                <ul className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[12px]">
                  {post.targets.map((target) => (
                    <li key={`${post.id}-${target.provider}`} className="flex items-baseline gap-1">
                      <span>{NETWORKS[target.provider]?.label ?? target.provider}</span>
                      <span
                        className={
                          target.status === 'failed'
                            ? 'text-[var(--status-error)]'
                            : 'text-[var(--text-secondary)]'
                        }
                      >
                        {target.status === 'published' && target.url ? (
                          <a href={target.url} target="_blank" rel="noreferrer noopener" className="underline-offset-2 hover:underline">
                            published
                          </a>
                        ) : target.status === 'pending' && target.at ? (
                          `at ${target.at.slice(0, 16).replace('T', ' ')}`
                        ) : (
                          target.status
                        )}
                      </span>
                    </li>
                  ))}
                </ul>

                {/*
                  * Four of five succeeded is a real outcome, and the one that
                  * did not says why rather than turning the whole post red.
                  */}
                {post.targets.filter((t) => t.error).map((target) => (
                  <p key={`${post.id}-${target.provider}-err`} className="mt-1 text-[11px] text-[var(--status-error)]">
                    {NETWORKS[target.provider]?.label ?? target.provider}: {target.error}
                  </p>
                ))}

                {post.approvalState === 'pending' && (
                  <div className="mt-3 flex flex-wrap items-center gap-2">
                    <span className="text-[12px] text-[var(--text-secondary)]">Waiting for review</span>
                    <Button
                      variant="ghost"
                      disabled={pending}
                      onClick={() =>
                        start(async () => {
                          const result = await reviewPost(post.id, 'approved');
                          if (!result.ok) toast({ kind: 'error', message: result.error });
                          router.refresh();
                        })
                      }
                    >
                      Approve
                    </Button>
                    <Button
                      variant="ghost"
                      disabled={pending}
                      onClick={() =>
                        start(async () => {
                          const result = await reviewPost(post.id, 'rejected');
                          if (!result.ok) toast({ kind: 'error', message: result.error });
                          router.refresh();
                        })
                      }
                    >
                      Reject
                    </Button>
                  </div>
                )}
              </Card>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}

function statusTone(status: string): 'ok' | 'warn' | 'error' | 'info' {
  if (status === 'published') return 'ok';
  if (status === 'failed') return 'error';
  if (status === 'cancelled') return 'warn';
  return 'info';
}
