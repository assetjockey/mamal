'use client';

import { useRouter } from 'next/navigation';
import { useEffect, useState, useTransition } from 'react';
import { Button, Card, StatusBadge, useToast } from '@mamal/ui';
import { enablePushForSite } from '../actions';

export function EnablePush({ sites }: { sites: { id: string; host: string }[] }) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const [open, setOpen] = useState(false);

  if (sites.length === 0) return null;

  const run = (siteId: string) =>
    start(async () => {
      const r = await enablePushForSite(siteId);
      if (!r.ok) return toast({ message: r.error, kind: 'error' });
      toast({ message: 'Push enabled. Host the service worker to start collecting.', kind: 'ok' });
      setOpen(false);
      router.refresh();
    });

  if (sites.length === 1 || !open) {
    return (
      <Button
        size="sm"
        disabled={pending}
        onClick={() => (sites.length > 1 ? setOpen(true) : run(sites[0]!.id))}
      >
        Enable push
      </Button>
    );
  }

  return (
    <div className="flex flex-wrap items-center gap-2">
      <label className="sr-only" htmlFor="push-site">Website</label>
      <select
        id="push-site"
        defaultValue=""
        onChange={(e) => e.target.value && run(e.target.value)}
        className="h-8 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[13px] text-[var(--text-primary)]"
      >
        <option value="" disabled>Choose a website…</option>
        {sites.map((s) => <option key={s.id} value={s.id}>{s.host}</option>)}
      </select>
      <Button size="sm" variant="quiet" onClick={() => setOpen(false)}>Cancel</Button>
    </div>
  );
}

/**
 * Install instructions for push.
 *
 * Harder than the widget snippet, and the UI should say so rather than pretend
 * otherwise: a service worker only controls the scope it is *served from*, so
 * it cannot be loaded cross-origin the way `confirm.js` can. The customer has
 * to serve one file from their own root.
 */
export function PushInstall({ publicKey, host }: { publicKey: string; host: string }) {
  const [origin, setOrigin] = useState('');
  const toast = useToast();
  const [checked, setChecked] = useState<'idle' | 'ok' | 'bad'>('idle');

  useEffect(() => setOrigin(window.location.origin), []);

  const worker = `// Save as /mamal-sw.js at the root of ${host}
importScripts('${origin}/api/push/sw');`;

  const snippet = `<script>
  navigator.serviceWorker.register('/mamal-sw.js').then(async (reg) => {
    if (Notification.permission === 'denied') return;
    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: '${publicKey}',
    });
    await fetch('${origin}/api/push/subscribe', {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify({
        key: '${publicKey}',
        subscription: sub,
        meta: { language: navigator.language },
      }),
    });
  });
</script>`;

  const copy = async (text: string, what: string) => {
    try {
      await navigator.clipboard.writeText(text);
      toast({ message: `${what} copied.`, kind: 'ok' });
    } catch {
      toast({ message: 'Copy blocked — select the text instead.' });
    }
  };

  return (
    <Card>
      <p className="text-[13px] leading-[1.5] text-[var(--text-secondary)]">
        Two steps. A service worker only controls the scope it is served from, so unlike the
        widget script this one cannot be loaded from us.
      </p>

      <ol className="mt-3 space-y-3">
        <li>
          <span className="text-[12px] text-[var(--text-muted)]">
            1 · Host this at <code className="font-mono">/mamal-sw.js</code>
          </span>
          <pre
            tabIndex={0}
            className="mt-1 overflow-x-auto rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-2.5 font-mono text-[11px] leading-[1.6] text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
          >
            {worker}
          </pre>
          <Button size="sm" variant="quiet" onClick={() => copy(worker, 'Worker')}>Copy</Button>
        </li>

        <li>
          <span className="text-[12px] text-[var(--text-muted)]">
            2 · Ask for permission, when it makes sense to
          </span>
          <pre
            tabIndex={0}
            className="mt-1 overflow-x-auto rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-2.5 font-mono text-[11px] leading-[1.6] text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
          >
            {snippet}
          </pre>
          <Button size="sm" variant="quiet" onClick={() => copy(snippet, 'Snippet')}>Copy</Button>
        </li>
      </ol>

      <div className="mt-3 flex flex-wrap items-center gap-2">
        <Button
          size="sm"
          variant="quiet"
          onClick={async () => {
            try {
              const r = await fetch('/api/push/sw', { cache: 'no-store' });
              setChecked(r.ok ? 'ok' : 'bad');
            } catch {
              setChecked('bad');
            }
          }}
        >
          Test worker
        </Button>
        {checked === 'ok' ? <StatusBadge status="ok">Worker served</StatusBadge> : null}
        {checked === 'bad' ? <StatusBadge status="error">Not reachable</StatusBadge> : null}
      </div>

      <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
        Ask after someone has shown interest, not on page load. A permission prompt on arrival is
        denied by most people and browsers remember that refusal permanently — there is no second
        chance at it.
      </p>
    </Card>
  );
}
