'use client';

import { useMemo, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { validateBarcode, withCheckDigit } from '@mamal/link-catalog';
import { Button, Card, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { newBarcode } from '../actions';

/**
 * The barcode studio.
 *
 * Validation runs on every keystroke, with the *same* validator the server
 * enforces — so "check digit should be 7" appears while somebody is typing
 * rather than after they have saved and gone to print. That is the entire
 * product argument for barcodes carrying validators at all.
 */

/*
 * `min-w-0` is load-bearing on the selects.
 *
 * A <select> keeps an intrinsic minimum width from its longest option, and
 * `w-full` does not override it — so the QR type picker (whose options include
 * full type descriptions) pushed its card 51px past a 360px viewport and gave
 * the whole page a horizontal scrollbar.
 */
const control =
  'w-full min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

type Item = {
  key: string; label: string; description: string; example: string; checkDigit: boolean;
};

export function BarcodeStudio({
  families,
  saved,
}: {
  families: { family: string; items: Item[] }[];
  saved: { id: string; symbology: string; value: string }[];
}) {
  const [symbology, setSymbology] = useState('ean13');
  const [value, setValue] = useState('');
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const def = useMemo(
    () => families.flatMap((f) => f.items).find((i) => i.key === symbology),
    [families, symbology],
  );

  const check = useMemo(
    () => (value.trim() ? validateBarcode(symbology, value.trim()) : null),
    [symbology, value],
  );

  // Offered, not applied: the customer may be typing a value they will finish.
  const completed = useMemo(() => withCheckDigit(symbology, value.trim()), [symbology, value]);
  const canComplete = completed !== value.trim();

  const save = () => {
    start(async () => {
      const result = await newBarcode(symbology, value.trim());
      toast(result.ok
        ? { kind: 'ok', message: 'Barcode saved.' }
        : { kind: 'error', message: result.error });
      if (result.ok) { setValue(''); router.refresh(); }
    });
  };

  return (
    <div className="grid gap-6 [&>*]:min-w-0 lg:grid-cols-2">
      <Card>
        <SectionLabel>Symbology</SectionLabel>
        <div className="mt-3 grid gap-3">
          <div>
            <label htmlFor="bc-type" className="sr-only">Symbology</label>
            <select id="bc-type" value={symbology} className={control}
                    onChange={(e) => { setSymbology(e.target.value); setValue(''); }}>
              {families.map((f) => (
                <optgroup key={f.family} label={humanise(f.family)}>
                  {f.items.map((i) => <option key={i.key} value={i.key}>{i.label}</option>)}
                </optgroup>
              ))}
            </select>
          </div>

          {def ? (
            <p className="text-[13px] text-[var(--text-secondary)]">{def.description}</p>
          ) : null}

          <div>
            <label htmlFor="bc-value" className={labelStyle}>Value</label>
            <input
              id="bc-value" value={value} className={control}
              placeholder={def?.example}
              aria-describedby="bc-status"
              onChange={(e) => setValue(e.target.value)}
            />
            <div id="bc-status" aria-live="polite" className="mt-2 min-h-[24px]">
              {check === null ? (
                <p className="text-[12px] text-[var(--text-faint)]">
                  {def?.checkDigit
                    ? 'This symbology carries a check digit. Type the value without it and we will work it out.'
                    : `For example: ${def?.example ?? ''}`}
                </p>
              ) : check.ok ? (
                <div className="flex flex-wrap items-center gap-2">
                  <StatusBadge status="ok">Valid</StatusBadge>
                  {canComplete ? (
                    <button
                      type="button"
                      onClick={() => setValue(completed)}
                      className="rounded-[4px] px-1.5 py-0.5 text-[12px] text-[var(--accent)] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-[var(--accent-solid)]"
                    >
                      Add the check digit — {completed}
                    </button>
                  ) : null}
                </div>
              ) : (
                <div className="flex flex-wrap items-center gap-2">
                  <StatusBadge status="error">Will not scan</StatusBadge>
                  <span className="text-[12px] text-[var(--color-status-error)]">{check.reason}</span>
                </div>
              )}
            </div>
          </div>

          <div className="border-t border-[var(--border-hairline)] pt-3">
            <Button onClick={save} disabled={pending || !check?.ok}>
              {pending ? 'Saving…' : 'Save barcode'}
            </Button>
          </div>
        </div>
      </Card>

      <Card>
        <SectionLabel>Saved</SectionLabel>
        {saved.length === 0 ? (
          <p className="mt-3 text-[13px] text-[var(--text-secondary)]">
            Nothing saved yet. Barcodes are rendered for print by the export job — this
            screen is where the values live and get checked.
          </p>
        ) : (
          <ul className="mt-3 grid gap-2">
            {saved.map((b) => (
              <li key={b.id} className="flex items-center justify-between gap-3 border-b border-[var(--border-hairline)] pb-2 last:border-0">
                <span className="truncate font-mono text-[13px] tabular-nums text-[var(--text-primary)]">
                  {b.value}
                </span>
                <span className="shrink-0 text-[12px] text-[var(--text-faint)]">{b.symbology}</span>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </div>
  );
}

const humanise = (s: string) => s.replace(/^./, (c) => c.toUpperCase());
