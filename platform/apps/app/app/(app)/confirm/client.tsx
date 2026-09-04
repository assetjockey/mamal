'use client';

import { useRouter } from 'next/navigation';
import { useState, useTransition } from 'react';
import { Button, useToast } from '@mamal/ui';
import { addCampaign } from './actions';

/** Adds a campaign for a site that does not have one yet. */
export function NewCampaign({ sites }: { sites: { id: string; host: string }[] }) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const [open, setOpen] = useState(false);

  if (sites.length === 0) return null;

  // One site: no picker, just do it. A dropdown with a single option is a
  // click that asks a question with one answer.
  if (sites.length === 1 || !open) {
    return (
      <Button
        size="sm"
        disabled={pending}
        onClick={() => {
          if (sites.length > 1) return setOpen(true);
          start(async () => {
            const r = await addCampaign(sites[0]!.id);
            if (!r.ok) return toast({ message: r.error, kind: 'error' });
            router.push(`/confirm/campaigns/${r.id}`);
          });
        }}
      >
        New campaign
      </Button>
    );
  }

  return (
    <div className="flex flex-wrap items-center gap-2">
      <label className="sr-only" htmlFor="site">Website</label>
      <select
        id="site"
        className="h-8 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[13px] text-[var(--text-primary)]"
        onChange={(e) => {
          const id = e.target.value;
          if (!id) return;
          start(async () => {
            const r = await addCampaign(id);
            if (!r.ok) return toast({ message: r.error, kind: 'error' });
            router.push(`/confirm/campaigns/${r.id}`);
          });
        }}
        defaultValue=""
      >
        <option value="" disabled>Choose a website…</option>
        {sites.map((s) => <option key={s.id} value={s.id}>{s.host}</option>)}
      </select>
      <Button size="sm" variant="quiet" onClick={() => setOpen(false)}>Cancel</Button>
    </div>
  );
}
