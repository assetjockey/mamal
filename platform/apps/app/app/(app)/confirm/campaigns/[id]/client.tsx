'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect, useState, useTransition } from 'react';
import { Button, Card, StatusBadge, useToast } from '@mamal/ui';
import { addWidget, deleteWidget, restoreWidget, setWidgetEnabled } from '../../actions';

/* ---------------------------------------------------------------- install */

export function InstallSnippet({ pixelKey, host }: { pixelKey: string; host: string }) {
  const [origin, setOrigin] = useState('');
  const [state, setState] = useState<'idle' | 'checking' | 'live' | 'missing'>('idle');
  const toast = useToast();

  // The snippet has to carry an absolute URL, and the right one depends on
  // where this app is deployed — so it is read from the browser rather than
  // guessed on the server.
  useEffect(() => setOrigin(window.location.origin), []);

  const snippet = `<script async src="${origin}/confirm.js" data-key="${pixelKey}"></script>`;

  return (
    <Card>
      <p className="text-[13px] leading-[1.5] text-[var(--text-secondary)]">
        Paste this before <code className="font-mono text-[12px]">&lt;/body&gt;</code> on{' '}
        <span className="text-[var(--text-primary)]">{host}</span>.
      </p>

      <pre
        tabIndex={0}
        className="mt-3 overflow-x-auto rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-3 font-mono text-[11px] leading-[1.6] text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
      >
        {snippet}
      </pre>

      <div className="mt-3 flex flex-wrap items-center gap-2">
        <Button
          size="sm"
          variant="quiet"
          onClick={async () => {
            try {
              await navigator.clipboard.writeText(snippet);
              toast({ message: 'Snippet copied.', kind: 'ok' });
            } catch {
              // Clipboard is blocked in some contexts; the code is on screen
              // and selectable, so this is a nudge rather than a failure.
              toast({ message: 'Copy blocked — select the snippet instead.' });
            }
          }}
        >
          Copy
        </Button>

        <Button
          size="sm"
          variant="quiet"
          disabled={state === 'checking'}
          onClick={async () => {
            setState('checking');
            /*
             * Verifies the *config* is reachable, not that the tag is installed.
             * We cannot fetch a customer's page from the browser — CORS forbids
             * it — and claiming "installed ✓" from a request we cannot make
             * would be a lie the first support ticket exposes.
             */
            try {
              const res = await fetch(`/c/${pixelKey}.json`, { cache: 'no-store' });
              setState(res.ok ? 'live' : 'missing');
            } catch {
              setState('missing');
            }
          }}
        >
          {state === 'checking' ? 'Checking…' : 'Test config'}
        </Button>

        {state === 'live' ? (
          <StatusBadge status="ok">Config served</StatusBadge>
        ) : state === 'missing' ? (
          <StatusBadge status="error">Not reachable</StatusBadge>
        ) : null}
      </div>

      <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
        Impressions appear here once the tag is on a page a visitor loads. The config is
        cached at the edge for a minute, so an edit is live within one.
      </p>
    </Card>
  );
}

/* ------------------------------------------------------------ type picker */

type Category = { key: string; types: { key: string; label: string; description: string }[] };

export function WidgetPicker({ campaignId, categories }: { campaignId: string; categories: Category[] }) {
  const router = useRouter();
  const toast = useToast();
  const [open, setOpen] = useState(false);
  const [pending, start] = useTransition();
  const [query, setQuery] = useState('');

  if (!open) return <Button size="sm" onClick={() => setOpen(true)}>Add notification</Button>;

  const q = query.trim().toLowerCase();
  const shown = categories
    .map((c) => ({
      ...c,
      types: c.types.filter(
        (t) => !q || t.label.toLowerCase().includes(q) || t.description.toLowerCase().includes(q),
      ),
    }))
    .filter((c) => c.types.length > 0);

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center px-4 pt-[8vh]">
      <div
        className="absolute inset-0 bg-[color-mix(in_srgb,var(--text-primary)_35%,transparent)]"
        onClick={() => setOpen(false)}
      />
      <div
        role="dialog"
        aria-modal="true"
        aria-label="Choose a notification type"
        onKeyDown={(e) => { if (e.key === 'Escape') setOpen(false); }}
        className="relative flex max-h-[78vh] w-full max-w-[640px] flex-col overflow-hidden rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)]"
      >
        <div className="border-b border-[var(--border-hairline)] px-4 py-3">
          <input
            autoFocus
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search 44 notification types…"
            aria-label="Search notification types"
            className="h-9 w-full bg-transparent text-[14px] text-[var(--text-primary)] placeholder:text-[var(--text-faint)] focus:outline-none"
          />
        </div>

        <div className="min-h-0 flex-1 overflow-y-auto p-2">
          {shown.length === 0 ? (
            <p className="px-3 py-8 text-center text-[13px] text-[var(--text-muted)]">
              Nothing matches “{query}”.
            </p>
          ) : shown.map((c) => (
            <div key={c.key} className="mb-3">
              <div className="px-3 pb-1 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                {c.key}
              </div>
              {c.types.map((t) => (
                <button
                  key={t.key}
                  disabled={pending}
                  onClick={() => start(async () => {
                    const r = await addWidget(campaignId, t.key, t.label);
                    if (!r.ok) return toast({ message: r.error, kind: 'error' });
                    setOpen(false);
                    router.push(`/confirm/widgets/${r.id}`);
                  })}
                  className="block w-full rounded-[4px] px-3 py-2 text-left hover:bg-[var(--surface-hover)] disabled:opacity-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
                >
                  <span className="block text-[14px] text-[var(--text-primary)]">{t.label}</span>
                  <span className="mt-0.5 block text-[12px] text-[var(--text-muted)]">
                    {t.description}
                  </span>
                </button>
              ))}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------ widget rows */

export function WidgetRow({
  widget, campaignId,
}: {
  widget: {
    id: string; type: string; name: string; label: string; is_enabled: boolean;
    position: string; impressions: number; clicks: number; submissions: number;
  };
  campaignId: string;
}) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();

  const ctr = widget.impressions > 0
    ? ((widget.clicks / widget.impressions) * 100).toFixed(1)
    : null;

  return (
    <Card>
      <div className="flex flex-wrap items-center gap-x-4 gap-y-3">
        <div className="min-w-0 flex-1">
          <Link
            href={`/confirm/widgets/${widget.id}`}
            className="block truncate text-[14px] text-[var(--text-primary)] hover:text-[var(--accent)]"
          >
            {widget.name}
          </Link>
          <span className="mt-0.5 block truncate text-[12px] text-[var(--text-faint)]">
            {widget.label} · {widget.position}
          </span>
        </div>

        <div className="flex shrink-0 items-center gap-3 text-[12px] tabular-nums text-[var(--text-muted)]">
          <span>{widget.impressions.toLocaleString()} shown</span>
          {ctr ? <span>{ctr}% CTR</span> : null}
        </div>

        <div className="flex shrink-0 items-center gap-2">
          <StatusBadge status={widget.is_enabled ? 'ok' : 'neutral'}>
            {widget.is_enabled ? 'Live' : 'Off'}
          </StatusBadge>
          <Button
            size="sm"
            variant="quiet"
            disabled={pending}
            onClick={() => start(async () => {
              await setWidgetEnabled(widget.id, !widget.is_enabled);
              router.refresh();
            })}
          >
            {widget.is_enabled ? 'Pause' : 'Resume'}
          </Button>
          <Button
            size="sm"
            variant="quiet"
            disabled={pending}
            onClick={() => start(async () => {
              // Undo rather than a confirm dialog, per the house rule. The
              // snapshot is taken before the delete so the restore is exact.
              const snapshot = await fetch(`/api/confirm/widget/${widget.id}`)
                .then((r) => (r.ok ? r.json() : null))
                .catch(() => null);
              await deleteWidget(widget.id);
              router.refresh();
              toast({
                message: `Deleted “${widget.name}”.`,
                onUndo: snapshot
                  ? async () => {
                      await restoreWidget(campaignId, snapshot);
                      router.refresh();
                    }
                  : undefined,
              });
            })}
          >
            Delete
          </Button>
        </div>
      </div>
    </Card>
  );
}
