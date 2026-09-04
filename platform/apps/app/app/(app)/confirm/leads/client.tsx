'use client';

import { useRouter } from 'next/navigation';
import { useState, useTransition } from 'react';
import { Button, useToast } from '@mamal/ui';
import { addConversion } from '../actions';

/**
 * Adds a conversion by hand.
 *
 * Kept because a customer with no integrations yet needs to see what a
 * notification will look like — and because CSV import of past sales is a real
 * use. It is recorded as `manual` so the distinction from a verified event is
 * never lost.
 */
export function AddConversion({ campaigns }: { campaigns: { id: string; name: string }[] }) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const [open, setOpen] = useState(false);

  if (!open) return <Button size="sm" variant="quiet" onClick={() => setOpen(true)}>Add by hand</Button>;

  return (
    <form
      className="flex min-w-0 max-w-full flex-wrap items-end gap-2"
      action={(fd) => start(async () => {
        const r = await addConversion({
          campaignId: String(fd.get('campaign')),
          type: String(fd.get('type') || 'signed up'),
          name: String(fd.get('name') || ''),
          city: String(fd.get('city') || ''),
        });
        if (!r.ok) return toast({ message: r.error, kind: 'error' });
        toast({ message: 'Added, marked as entered by hand.', kind: 'ok' });
        setOpen(false);
        router.refresh();
      })}
    >
      <select
        name="campaign"
        aria-label="Campaign"
        className="h-8 min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[13px] text-[var(--text-primary)]"
      >
        {campaigns.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
      </select>
      {[['name', 'First name'], ['city', 'City'], ['type', 'Did what']].map(([n, label]) => (
        <input
          key={n}
          name={n}
          placeholder={label}
          aria-label={label}
          className="h-8 w-28 min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[13px] text-[var(--text-primary)]"
        />
      ))}
      <Button size="sm" disabled={pending}>Add</Button>
      <Button size="sm" variant="quiet" onClick={() => setOpen(false)}>Cancel</Button>
    </form>
  );
}
