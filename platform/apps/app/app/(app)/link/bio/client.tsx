'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { Button, useToast } from '@mamal/ui';
import { newBioPage } from '../actions';

const field =
  'rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 ' +
  'text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';

export function NewBioPage({ allowed }: { allowed: boolean }) {
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState('');
  const [alias, setAlias] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  if (!allowed) {
    return (
      <NextLink href="/settings/billing">
        <Button variant="ghost">Upgrade for more pages</Button>
      </NextLink>
    );
  }

  if (!open) return <Button onClick={() => setOpen(true)}>New page</Button>;

  const submit = () => {
    setError(null);
    start(async () => {
      const result = await newBioPage(title.trim(), alias.trim() || undefined);
      if (!result.ok) { setError(result.error); return; }
      toast({ kind: 'ok', message: 'Page created. Add blocks, then publish.' });
      router.push(`/link/bio/${result.id}`);
    });
  };

  return (
    <div className="flex flex-wrap items-start gap-2">
      <div>
        <label htmlFor="bio-title" className="sr-only">Page title</label>
        <input id="bio-title" autoFocus value={title} onChange={(e) => setTitle(e.target.value)}
               onKeyDown={(e) => { if (e.key === 'Enter' && title.trim()) submit(); if (e.key === 'Escape') setOpen(false); }}
               placeholder="Your name or brand" className={`${field} w-[min(240px,55vw)]`} />
        {error ? <p role="alert" className="mt-1 text-[12px] text-[var(--color-status-error)]">{error}</p> : null}
      </div>
      <div>
        <label htmlFor="bio-alias" className="sr-only">Custom alias, optional</label>
        <input id="bio-alias" value={alias} onChange={(e) => setAlias(e.target.value)}
               placeholder="your-name" className={`${field} w-[140px]`} />
      </div>
      <Button onClick={submit} disabled={pending || !title.trim()}>
        {pending ? 'Creating…' : 'Create'}
      </Button>
      <Button variant="ghost" onClick={() => { setOpen(false); setError(null); }}>Cancel</Button>
    </div>
  );
}
