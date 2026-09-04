'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, useToast } from '@mamal/ui';
import { addKeywords } from '../actions';

export function AddKeywords() {
  const [open, setOpen] = useState(false);
  const [text, setText] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  if (!open) return <Button onClick={() => setOpen(true)}>Add keywords</Button>;

  const count = new Set(
    text.split(/[\n,]/).map((k) => k.trim().toLowerCase()).filter(Boolean),
  ).size;

  const submit = () => {
    setError(null);
    start(async () => {
      const result = await addKeywords(text);
      if (!result.ok) { setError(result.error); return; }
      toast({
        kind: 'ok',
        message: `${result.added} keyword${result.added === 1 ? '' : 's'} added. Research them when you want the numbers.`,
      });
      setOpen(false); setText('');
      router.refresh();
    });
  };

  return (
    <div className="w-full max-w-[420px]">
      <label htmlFor="kw" className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
        One per line, or comma-separated
      </label>
      <textarea
        id="kw" rows={4} autoFocus value={text}
        onChange={(e) => setText(e.target.value)}
        placeholder={'widget reviews\nbest widgets\nwidgets near me'}
        className="w-full resize-y rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
      />
      {error ? (
        <p role="alert" className="mt-1 text-[13px] text-[var(--color-status-error)]">{error}</p>
      ) : (
        <p className="mt-1 text-[12px] text-[var(--text-faint)]">
          {count > 0 ? `${count} unique. ` : ''}Stored without metrics — adding them costs nothing.
        </p>
      )}
      <div className="mt-3 flex gap-2">
        <Button onClick={submit} disabled={pending || count === 0}>
          {pending ? 'Adding…' : `Add ${count || ''}`.trim()}
        </Button>
        <Button variant="ghost" onClick={() => { setOpen(false); setError(null); }}>Cancel</Button>
      </div>
    </div>
  );
}
