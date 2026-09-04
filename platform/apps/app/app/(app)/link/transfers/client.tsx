'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { Button, useToast } from '@mamal/ui';
import { newTransfer, pullBackTransfer } from '../actions';

const field =
  'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 ' +
  'text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function NewTransfer({ allowed }: { allowed: boolean }) {
  const [open, setOpen] = useState(false);
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [recipients, setRecipients] = useState('');
  const [password, setPassword] = useState('');
  const [days, setDays] = useState('7');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  if (!allowed) {
    return (
      <NextLink href="/settings/billing">
        <Button variant="ghost">Upgrade for more transfers</Button>
      </NextLink>
    );
  }

  if (!open) return <Button onClick={() => setOpen(true)}>New transfer</Button>;

  const list = recipients.split(/[,\s]+/).map((r) => r.trim()).filter(Boolean);

  const submit = () => {
    setError(null);
    start(async () => {
      const result = await newTransfer({
        subject: subject.trim() || 'Files',
        message: message.trim() || undefined,
        recipients: list,
        delivery: list.length > 0 ? 'email' : 'link',
        password: password || undefined,
        expiresInDays: days ? Number(days) : undefined,
      });
      if (!result.ok) { setError(result.error); return; }
      toast({ kind: 'ok', message: 'Transfer created. Add files, then share the link.' });
      setOpen(false); setSubject(''); setMessage(''); setRecipients(''); setPassword('');
      router.refresh();
    });
  };

  return (
    <div className="w-full max-w-[520px] rounded-[4px] border border-[var(--border-hairline)] p-4">
      <div className="grid gap-3 md:grid-cols-2">
        <div className="md:col-span-2">
          <label htmlFor="tr-subject" className={labelStyle}>What is it</label>
          <input id="tr-subject" autoFocus value={subject} onChange={(e) => setSubject(e.target.value)}
                 className={field} placeholder="Final artwork" />
        </div>
        <div className="md:col-span-2">
          <label htmlFor="tr-message" className={labelStyle}>Message (optional)</label>
          <textarea id="tr-message" rows={2} value={message} onChange={(e) => setMessage(e.target.value)}
                    className={`${field} resize-y`} />
        </div>
        <div className="md:col-span-2">
          <label htmlFor="tr-to" className={labelStyle}>Email it to (optional)</label>
          <input id="tr-to" value={recipients} onChange={(e) => setRecipients(e.target.value)}
                 className={field} placeholder="someone@example.com, another@example.com" />
          <p className="mt-1 text-[12px] text-[var(--text-faint)]">
            {list.length > 0
              ? `We will email ${list.length} recipient${list.length === 1 ? '' : 's'} when it is ready.`
              : 'Leave blank and you get a link to share yourself.'}
          </p>
        </div>
        <div>
          <label htmlFor="tr-pw" className={labelStyle}>Password (optional)</label>
          <input id="tr-pw" type="password" autoComplete="new-password" value={password}
                 onChange={(e) => setPassword(e.target.value)} className={field} />
        </div>
        <div>
          <label htmlFor="tr-days" className={labelStyle}>Expires after</label>
          <select id="tr-days" value={days} onChange={(e) => setDays(e.target.value)} className={field}>
            <option value="1">1 day</option>
            <option value="7">7 days</option>
            <option value="30">30 days</option>
            <option value="">Never</option>
          </select>
        </div>
      </div>

      {error ? <p role="alert" className="mt-2 text-[13px] text-[var(--color-status-error)]">{error}</p> : null}

      <div className="mt-4 flex gap-2 border-t border-[var(--border-hairline)] pt-3">
        <Button onClick={submit} disabled={pending}>{pending ? 'Creating…' : 'Create'}</Button>
        <Button variant="ghost" onClick={() => { setOpen(false); setError(null); }}>Cancel</Button>
      </div>
    </div>
  );
}

/**
 * Pulling a transfer back, with a reason the recipient sees.
 *
 * `swipgle`'s idea and a good one: the common case is "wrong file", and a
 * recipient told that is far better served than one who finds a dead link and
 * has to ask.
 */
export function PullBack({ id, subject }: { id: string; subject: string }) {
  const [asking, setAsking] = useState(false);
  const [reason, setReason] = useState('');
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  if (!asking) {
    return <Button size="sm" variant="quiet" onClick={() => setAsking(true)}>Pull back</Button>;
  }

  return (
    <div className="flex items-center justify-end gap-1">
      <label htmlFor={`reason-${id}`} className="sr-only">Why are you pulling back {subject}?</label>
      <input
        id={`reason-${id}`} autoFocus value={reason} placeholder="Wrong file"
        onChange={(e) => setReason(e.target.value)}
        onKeyDown={(e) => { if (e.key === 'Escape') setAsking(false); }}
        className="w-[150px] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2 py-1 text-[13px] text-[var(--text-primary)]"
      />
      <Button
        size="sm" variant="quiet" disabled={pending}
        onClick={() => start(async () => {
          await pullBackTransfer(id, reason.trim() || 'The sender cancelled this transfer.');
          toast({ kind: 'ok', message: 'Pulled back. Recipients see your reason.' });
          setAsking(false);
          router.refresh();
        })}
      >
        Confirm
      </Button>
    </div>
  );
}
