'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { Button, StatusBadge, Td, Tr, useToast, type Status } from '@mamal/ui';
import { actOnSuggestion, deleteLink, newLink, restoreLink } from './actions';

export type LinkRowData = {
  id: string; alias: string; kind: string; title: string | null;
  destination_url: string | null; campaign: string | null; tags: string[];
  is_enabled: boolean; clicks_count: number; max_clicks: number | null;
  expires_at: string | null; has_password: boolean; rules: number;
  shortUrl: string;
};

const field =
  'rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 ' +
  'text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';

/* ------------------------------------------------------------------ create */

export function NewLink({ allowed }: { allowed: boolean }) {
  const [open, setOpen] = useState(false);
  const [url, setUrl] = useState('');
  const [alias, setAlias] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  // Discovery is a feature: the gate names what is missing rather than hiding
  // the button and leaving the customer to guess why nothing happens.
  if (!allowed) {
    return (
      <NextLink href="/settings/billing">
        <Button variant="ghost">Upgrade for more links</Button>
      </NextLink>
    );
  }

  if (!open) return <Button onClick={() => setOpen(true)}>New link</Button>;

  const submit = () => {
    setError(null);
    start(async () => {
      const result = await newLink({ url: url.trim(), alias: alias.trim() || undefined });
      if (!result.ok) { setError(result.error); return; }
      // Copying is what they came to do. Offering it beats making them find the
      // row they just created and copy it themselves.
      await navigator.clipboard?.writeText(result.url).catch(() => {});
      toast({ kind: 'ok', message: `${result.url} — copied to your clipboard.` });
      setOpen(false); setUrl(''); setAlias('');
      router.refresh();
    });
  };

  const keys = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && url.trim()) submit();
    if (e.key === 'Escape') { setOpen(false); setError(null); }
  };

  return (
    <div className="flex flex-wrap items-start gap-2">
      <div className="min-w-0">
        <label htmlFor="new-url" className="sr-only">Destination URL</label>
        <input
          id="new-url" autoFocus value={url} onKeyDown={keys}
          onChange={(e) => setUrl(e.target.value)}
          placeholder="https://example.com/page"
          className={`${field} w-[min(320px,60vw)]`}
        />
        {error ? (
          <p role="alert" className="mt-1 max-w-[320px] text-[12px] text-[var(--color-status-error)]">
            {error}
          </p>
        ) : null}
      </div>
      <div>
        <label htmlFor="new-alias" className="sr-only">Custom alias, optional</label>
        <input
          id="new-alias" value={alias} onKeyDown={keys}
          onChange={(e) => setAlias(e.target.value)}
          placeholder="custom-alias"
          className={`${field} w-[140px]`}
        />
      </div>
      <Button onClick={submit} disabled={pending || !url.trim()}>
        {pending ? 'Creating…' : 'Create'}
      </Button>
      <Button variant="ghost" onClick={() => { setOpen(false); setError(null); }}>Cancel</Button>
    </div>
  );
}

/* --------------------------------------------------------------------- row */

export function LinkRow({ link }: { link: LinkRowData }) {
  const [copied, setCopied] = useState(false);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const expired = link.expires_at ? Date.parse(link.expires_at) <= Date.now() : false;
  const exhausted = link.max_clicks ? link.clicks_count >= link.max_clicks : false;

  const status: { tone: Status; label: string } = !link.is_enabled
    ? { tone: 'neutral', label: 'Paused' }
    : expired
      ? { tone: 'warn', label: 'Expired' }
      : exhausted
        ? { tone: 'warn', label: 'Limit reached' }
        : { tone: 'ok', label: 'Live' };

  const copy = async () => {
    await navigator.clipboard?.writeText(link.shortUrl).catch(() => {});
    setCopied(true);
    setTimeout(() => setCopied(false), 1600);
  };

  const remove = () => {
    start(async () => {
      await deleteLink(link.id);
      /*
       * Undoable for ten seconds rather than confirmed by a dialog.
       *
       * Undo can genuinely fail here — deleting releases the alias, so somebody
       * may have claimed it in the meantime — and it says so rather than
       * quietly doing nothing and leaving the link gone.
       */
      toast({
        kind: 'info',
        message: `Deleted /${link.alias}.`,
        onUndo: async () => {
          const result = await restoreLink(link.id);
          if (!result.ok) toast({ kind: 'error', message: result.error });
          router.refresh();
        },
      });
      router.refresh();
    });
  };

  return (
    <Tr>
      <Td>
        <div className="flex flex-wrap items-center gap-2">
          <NextLink
            href={`/link/links/${link.id}`}
            className="text-[var(--text-primary)] hover:text-[var(--accent)]"
          >
            /{link.alias}
          </NextLink>
          <button
            type="button"
            onClick={copy}
            aria-label={`Copy the short link for ${link.alias}`}
            className="rounded-[4px] px-1.5 py-0.5 text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)] hover:bg-[var(--surface-ground)] hover:text-[var(--text-secondary)] focus-visible:outline-2 focus-visible:outline-[var(--accent-solid)]"
          >
            {copied ? 'Copied' : 'Copy'}
          </button>
          {link.has_password ? (
            <span className="text-[11px] text-[var(--text-faint)]">Password</span>
          ) : null}
          {link.rules > 0 ? (
            <span className="text-[11px] tabular-nums text-[var(--text-faint)]">
              {link.rules} rule{link.rules === 1 ? '' : 's'}
            </span>
          ) : null}
        </div>
        {link.title ? (
          <p className="mt-0.5 max-w-[32ch] truncate text-[12px] text-[var(--text-faint)]">{link.title}</p>
        ) : null}
      </Td>

      <Td>
        <span className="block max-w-[36ch] truncate text-[13px] text-[var(--text-secondary)]">
          {link.destination_url ?? '—'}
        </span>
      </Td>

      <Td><StatusBadge status={status.tone}>{status.label}</StatusBadge></Td>

      <Td align="right">
        <span className="tabular-nums">{link.clicks_count.toLocaleString()}</span>
        {link.max_clicks ? (
          <span className="tabular-nums text-[var(--text-faint)]">
            {' / '}{link.max_clicks.toLocaleString()}
          </span>
        ) : null}
      </Td>

      <Td align="right">
        <div className="flex items-center justify-end gap-1">
          <NextLink href={`/link/links/${link.id}`}>
            <Button size="sm" variant="quiet">Edit</Button>
          </NextLink>
          <Button size="sm" variant="quiet" onClick={remove} disabled={pending}>Delete</Button>
        </div>
      </Td>
    </Tr>
  );
}

/* ------------------------------------------------------------- suggestions */

/**
 * What Audit found and Link could fix.
 *
 * A suggestion rather than something already done: creating these
 * automatically would spend the customer's link allowance on a decision they
 * never made, and one crawl of a large site can produce hundreds of them.
 */
export function Suggestions({
  items,
}: {
  items: { id: string; target_url: string; context_url: string | null }[];
}) {
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const act = (id: string, action: 'accept' | 'dismiss') => {
    start(async () => {
      const result = await actOnSuggestion(id, action);
      if (!result.ok) toast({ kind: 'error', message: result.error });
      else if (action === 'accept' && result.url) {
        await navigator.clipboard?.writeText(result.url).catch(() => {});
        toast({ kind: 'ok', message: `${result.url} — copied. Use it in place of the broken link.` });
      }
      router.refresh();
    });
  };

  return (
    <section
      aria-label="Suggestions from Audit"
      className="mb-8 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] p-4"
    >
      <h2 className="text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">From Audit</h2>
      <p className="mt-1 max-w-[70ch] text-[13px] text-[var(--text-secondary)]">
        These pages link to URLs that no longer resolve. A managed link can be re-pointed
        later without editing the page again.
      </p>
      <ul className="mt-3 grid gap-2">
        {items.map((s) => (
          <li key={s.id} className="flex flex-wrap items-center justify-between gap-3">
            <div className="min-w-0">
              <p className="truncate text-[13px] text-[var(--text-primary)]">{s.target_url}</p>
              {s.context_url ? (
                <p className="truncate text-[12px] text-[var(--text-faint)]">on {s.context_url}</p>
              ) : null}
            </div>
            <div className="flex shrink-0 gap-1">
              <Button size="sm" variant="quiet" onClick={() => act(s.id, 'accept')} disabled={pending}>
                Create link
              </Button>
              <Button size="sm" variant="quiet" onClick={() => act(s.id, 'dismiss')} disabled={pending}>
                Dismiss
              </Button>
            </div>
          </li>
        ))}
      </ul>
    </section>
  );
}
