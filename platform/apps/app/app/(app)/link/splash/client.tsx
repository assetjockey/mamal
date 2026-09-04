'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, useToast } from '@mamal/ui';
import { newSplashPage, removeSplashPage, saveSplashPage } from '../actions';

const control =
  'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

type Splash = {
  id: string; name: string; delay_seconds: number; is_skippable: boolean;
  auto_redirect: boolean; settings: { title?: string; body?: string }; used: number;
};

export function SplashList({ pages }: { pages: Splash[] }) {
  const [name, setName] = useState('');
  const [pending, start] = useTransition();
  const router = useRouter();

  return (
    <>
      <div className="mb-6 flex flex-wrap items-center gap-2">
        <label htmlFor="splash-name" className="sr-only">Splash page name</label>
        <input
          id="splash-name" value={name} onChange={(e) => setName(e.target.value)}
          placeholder="Sponsor interstitial"
          className="w-[min(280px,60vw)] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
        />
        <Button
          disabled={pending || !name.trim()}
          onClick={() => start(async () => {
            await newSplashPage(name.trim());
            setName('');
            router.refresh();
          })}
        >
          New splash page
        </Button>
      </div>

      {pages.length === 0 ? (
        <EmptyState
          title="No splash pages"
          description="Most links do not need one. When you do — a disclaimer, a sponsor slot — this is where it lives."
        />
      ) : (
        <div className="grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
          {pages.map((p) => <Editor key={p.id} splash={p} />)}
        </div>
      )}
    </>
  );
}

function Editor({ splash }: { splash: Splash }) {
  const [draft, setDraft] = useState({
    name: splash.name,
    delaySeconds: splash.delay_seconds,
    isSkippable: splash.is_skippable,
    autoRedirect: splash.auto_redirect,
    title: splash.settings.title ?? '',
    body: splash.settings.body ?? '',
  });
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  // Re-seed when the server sends a newer row — same reason as the bio builder:
  // `useState` reads its argument once, so a refresh would leave a stale draft.
  const [lastFromServer, setLastFromServer] = useState(splash);
  if (lastFromServer !== splash) {
    setLastFromServer(splash);
    setDraft({
      name: splash.name,
      delaySeconds: splash.delay_seconds,
      isSkippable: splash.is_skippable,
      autoRedirect: splash.auto_redirect,
      title: splash.settings.title ?? '',
      body: splash.settings.body ?? '',
    });
  }

  return (
    <Card>
      <SectionLabel>{splash.name}</SectionLabel>
      <p className="mt-1 text-[12px] text-[var(--text-faint)]">
        {splash.used === 0
          ? 'Not used by any link yet.'
          : `Used by ${splash.used} link${splash.used === 1 ? '' : 's'}.`}
      </p>

      <div className="mt-3 grid gap-3">
        <div>
          <label htmlFor={`n-${splash.id}`} className={labelStyle}>Name</label>
          <input id={`n-${splash.id}`} value={draft.name} className={control}
                 onChange={(e) => setDraft((d) => ({ ...d, name: e.target.value }))} />
        </div>
        <div>
          <label htmlFor={`t-${splash.id}`} className={labelStyle}>Heading</label>
          <input id={`t-${splash.id}`} value={draft.title} className={control}
                 placeholder="One moment"
                 onChange={(e) => setDraft((d) => ({ ...d, title: e.target.value }))} />
        </div>
        <div>
          <label htmlFor={`b-${splash.id}`} className={labelStyle}>Body</label>
          <textarea id={`b-${splash.id}`} rows={2} value={draft.body} className={`${control} resize-y`}
                    onChange={(e) => setDraft((d) => ({ ...d, body: e.target.value }))} />
        </div>
        <div className="grid gap-3 sm:grid-cols-2">
          <div>
            <label htmlFor={`d-${splash.id}`} className={labelStyle}>Delay, seconds</label>
            <input id={`d-${splash.id}`} type="number" min={0} max={60} value={draft.delaySeconds}
                   className={control}
                   onChange={(e) => setDraft((d) => ({ ...d, delaySeconds: Number(e.target.value) }))} />
          </div>
          <div className="flex flex-col justify-end gap-2 pb-1">
            <label className="flex items-center gap-2 text-[13px] text-[var(--text-secondary)]">
              <input type="checkbox" checked={draft.autoRedirect}
                     onChange={(e) => setDraft((d) => ({ ...d, autoRedirect: e.target.checked }))} />
              Forward automatically
            </label>
            <label className="flex items-center gap-2 text-[13px] text-[var(--text-secondary)]">
              <input type="checkbox" checked={draft.isSkippable}
                     onChange={(e) => setDraft((d) => ({ ...d, isSkippable: e.target.checked }))} />
              Let visitors skip
            </label>
          </div>
        </div>

        {!draft.isSkippable && draft.autoRedirect ? (
          <p className="text-[12px] text-[var(--color-status-warn)]">
            An unskippable interstitial on a link people reach from search is the pattern that
            gets a domain flagged, not a monetisation strategy. Consider leaving skip on.
          </p>
        ) : null}
      </div>

      <div className="mt-4 flex gap-2 border-t border-[var(--border-hairline)] pt-3">
        <Button
          size="sm" disabled={pending}
          onClick={() => start(async () => {
            await saveSplashPage(splash.id, draft);
            toast({ kind: 'ok', message: 'Saved.' });
            router.refresh();
          })}
        >
          Save
        </Button>
        <Button
          size="sm" variant="quiet" disabled={pending}
          onClick={() => start(async () => {
            await removeSplashPage(splash.id);
            toast({
              kind: 'info',
              message: splash.used > 0
                ? `Deleted. The ${splash.used} link${splash.used === 1 ? '' : 's'} using it now go straight to their destination.`
                : 'Deleted.',
            });
            router.refresh();
          })}
        >
          Delete
        </Button>
      </div>
    </Card>
  );
}
