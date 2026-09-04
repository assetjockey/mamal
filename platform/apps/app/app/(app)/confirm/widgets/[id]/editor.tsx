'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useCallback, useMemo, useState, useTransition } from 'react';
import type { Field } from '@mamal/widget-catalog';
import { Button, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { saveWidget, setWidgetEnabled } from '../../actions';
import { Preview, FRAMES, type FrameKey } from './preview';
import { TargetingPanel } from './targeting';

export type WidgetState = {
  id: string;
  campaignId: string;
  type: string;
  name: string;
  settings: Record<string, unknown>;
  targeting: Record<string, unknown>;
  theme: string;
  position: string;
  displayFrequency: string;
  displayLimit: number;
  delaySeconds: number;
  durationSeconds: number;
  isEnabled: boolean;
};

const POSITIONS = [
  'bottom-left', 'bottom-center', 'bottom-right',
  'top-left', 'top-center', 'top-right', 'center', 'inline',
];

/**
 * The widget editor.
 *
 * Three panes: what it says (left), what it looks like (centre), who sees it
 * (right). The centre is the real runtime — see `preview.tsx` — so what is on
 * screen is what ships.
 *
 * State lives here and flows down; nothing is saved until Save, so a half-typed
 * headline never reaches a visitor. The preview reads the same unsaved state,
 * which is the point of keeping it in one place.
 */
export function Editor({
  widget, meta, fields, themes,
}: {
  widget: WidgetState;
  meta: {
    label: string; description: string; family: string; needs: string[];
    host: string; showBranding: boolean;
  };
  fields: Field[];
  themes: { key: string; label: string }[];
}) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const [frame, setFrame] = useState<FrameKey>('desktop');
  const [state, setState] = useState<WidgetState>(widget);
  const [saved, setSaved] = useState<WidgetState>(widget);

  const dirty = useMemo(() => JSON.stringify(state) !== JSON.stringify(saved), [state, saved]);

  const set = useCallback(<K extends keyof WidgetState>(key: K, value: WidgetState[K]) => {
    setState((s) => ({ ...s, [key]: value }));
  }, []);

  const setSetting = useCallback((name: string, value: unknown) => {
    setState((s) => ({ ...s, settings: { ...s.settings, [name]: value } }));
  }, []);

  const save = () =>
    start(async () => {
      const r = await saveWidget(state.id, {
        name: state.name,
        settings: state.settings,
        targeting: state.targeting,
        theme: state.theme,
        position: state.position,
        displayFrequency: state.displayFrequency,
        displayLimit: state.displayLimit,
        delaySeconds: state.delaySeconds,
        durationSeconds: state.durationSeconds,
      });
      if (!r.ok) {
        // The schema's own message — "title: String must contain at most 280
        // characters" — beats "save failed".
        return toast({ message: r.error, kind: 'error' });
      }
      setSaved(state);
      toast({ message: 'Saved. Live within a minute.', kind: 'ok' });
      router.refresh();
    });

  return (
    <>
      <header className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div className="min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <input
              value={state.name}
              onChange={(e) => set('name', e.target.value)}
              aria-label="Notification name"
              className="min-w-0 max-w-full bg-transparent text-[26px] leading-[1.12] tracking-[-0.26px] text-[var(--text-primary)] focus:outline-none"
            />
            <StatusBadge status={state.isEnabled ? 'ok' : 'neutral'}>
              {state.isEnabled ? 'Live' : 'Off'}
            </StatusBadge>
          </div>
          <p className="mt-2 max-w-2xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
            {meta.label} · {meta.description}
          </p>
        </div>

        <div className="flex min-w-0 max-w-full flex-wrap items-center gap-2">
          <Link href={`/confirm/campaigns/${state.campaignId}`}>
            <Button size="sm" variant="quiet">Back</Button>
          </Link>
          <Button
            size="sm"
            variant="quiet"
            disabled={pending}
            onClick={() => start(async () => {
              await setWidgetEnabled(state.id, !state.isEnabled);
              set('isEnabled', !state.isEnabled);
              router.refresh();
            })}
          >
            {state.isEnabled ? 'Pause' : 'Go live'}
          </Button>
          <Button size="sm" disabled={pending || !dirty} onClick={save}>
            {pending ? 'Saving…' : dirty ? 'Save' : 'Saved'}
          </Button>
        </div>
      </header>

      <div className="grid gap-6 xl:grid-cols-[19rem_1fr_19rem] [&>*]:min-w-0">
        {/* ------------------------------------------------ what it says */}
        <section className="min-w-0 space-y-5">
          <div>
            <SectionLabel>Content</SectionLabel>
            <div className="space-y-3">
              {fields.length === 0 ? (
                <p className="text-[13px] text-[var(--text-muted)]">
                  This type has no editable content.
                </p>
              ) : (
                fields.map((f) => (
                  <FieldControl
                    key={f.name}
                    field={f}
                    value={state.settings[f.name]}
                    onChange={(v) => setSetting(f.name, v)}
                  />
                ))
              )}
            </div>
          </div>

          <div>
            <SectionLabel>Appearance</SectionLabel>
            <div className="space-y-3">
              <Labelled label="Theme">
                <Select
                  value={state.theme}
                  onChange={(v) => set('theme', v)}
                  options={themes.map((t) => ({ value: t.key, label: t.label }))}
                />
              </Labelled>
              <Labelled label="Position">
                <Select
                  value={state.position}
                  onChange={(v) => set('position', v)}
                  options={POSITIONS.map((p) => ({ value: p, label: p }))}
                />
              </Labelled>
            </div>
          </div>
        </section>

        {/* --------------------------------------------- what it looks like */}
        <section className="min-w-0">
          <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
            <SectionLabel>Preview</SectionLabel>
            <div className="flex gap-1" role="group" aria-label="Preview device">
              {(Object.keys(FRAMES) as FrameKey[]).map((k) => (
                <button
                  key={k}
                  onClick={() => setFrame(k)}
                  aria-pressed={frame === k}
                  className={`rounded-[4px] px-2.5 py-1 text-[12px] transition-colors duration-[120ms] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)] ${
                    frame === k
                      ? 'bg-[var(--accent-wash)] text-[var(--accent)]'
                      : 'text-[var(--text-muted)] hover:bg-[var(--surface-hover)]'
                  }`}
                >
                  {FRAMES[k].label}
                </button>
              ))}
            </div>
          </div>

          <Preview
            type={state.type}
            settings={state.settings}
            targeting={state.targeting}
            theme={state.theme}
            position={state.position}
            showBranding={meta.showBranding}
            host={meta.host}
            frame={frame}
          />

          <p className="mt-3 text-[12px] leading-[1.5] text-[var(--text-faint)]">
            This is the real widget script running against sample data — not a mock. What you see
            is what a visitor gets.
            {meta.needs.includes('conversions')
              ? ' Sample conversions are fictional; live ones come from your own sources.'
              : ''}
          </p>
        </section>

        {/* ------------------------------------------------- who sees it */}
        <section className="min-w-0 space-y-5">
          <TargetingPanel
            targeting={state.targeting}
            onChange={(t) => set('targeting', t)}
          />

          <div>
            <SectionLabel>Behaviour</SectionLabel>
            <div className="space-y-3">
              <Labelled label="Show at most">
                <Select
                  value={state.displayFrequency}
                  onChange={(v) => set('displayFrequency', v)}
                  options={[
                    { value: 'always', label: 'Every page view' },
                    { value: 'once_per_session', label: 'Once per session' },
                    { value: 'once_per_hours', label: 'Once every N hours' },
                    { value: 'n_times', label: 'N times in total' },
                  ]}
                />
              </Labelled>

              {state.displayFrequency === 'once_per_hours' || state.displayFrequency === 'n_times' ? (
                <Labelled
                  label={state.displayFrequency === 'n_times' ? 'How many times' : 'Hours between'}
                >
                  <NumberInput
                    value={state.displayLimit}
                    min={1}
                    onChange={(v) => set('displayLimit', v)}
                  />
                </Labelled>
              ) : null}

              <Labelled label="Delay (seconds)">
                <NumberInput value={state.delaySeconds} min={0} max={120}
                  onChange={(v) => set('delaySeconds', v)} />
              </Labelled>

              <Labelled label="Hide after (0 = keep)">
                <NumberInput value={state.durationSeconds} min={0} max={300}
                  onChange={(v) => set('durationSeconds', v)} />
              </Labelled>
            </div>
          </div>
        </section>
      </div>
    </>
  );
}

/* ------------------------------------------------------------- controls */

function Labelled({ label, hint, children }: { label: string; hint?: string; children: React.ReactNode }) {
  return (
    <label className="block">
      <span className="mb-1 block text-[12px] text-[var(--text-muted)]">{label}</span>
      {children}
      {hint ? <span className="mt-1 block text-[11px] text-[var(--text-faint)]">{hint}</span> : null}
    </label>
  );
}

const inputClass =
  'h-9 w-full min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2.5 text-[13px] text-[var(--text-primary)] focus:border-[var(--accent)] focus:outline-none';

function Select({
  value, onChange, options,
}: { value: string; onChange: (v: string) => void; options: { value: string; label: string }[] }) {
  return (
    <select className={inputClass} value={value} onChange={(e) => onChange(e.target.value)}>
      {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
    </select>
  );
}

function NumberInput({
  value, onChange, min, max,
}: { value: number; onChange: (v: number) => void; min?: number; max?: number }) {
  return (
    <input
      type="number"
      className={inputClass}
      value={Number.isFinite(value) ? value : 0}
      min={min}
      max={max}
      onChange={(e) => onChange(Number(e.target.value))}
    />
  );
}

/**
 * One control per field, chosen by the kind the schema implies.
 *
 * No per-type UI anywhere: adding a widget type to the catalogue gives it a
 * working editor, and a field the validator would reject cannot be offered
 * because both come from the same schema.
 */
function FieldControl({
  field, value, onChange,
}: { field: Field; value: unknown; onChange: (v: unknown) => void }) {
  const hint = field.templated ? 'Supports {{name}}, {{city}}, {{count}}' : undefined;

  switch (field.kind) {
    case 'boolean':
      return (
        <label className="flex items-center gap-2">
          <input
            type="checkbox"
            checked={value !== false}
            onChange={(e) => onChange(e.target.checked)}
            className="size-4 accent-[var(--accent-solid)]"
          />
          <span className="text-[13px] text-[var(--text-secondary)]">{field.label}</span>
        </label>
      );

    case 'number':
      return (
        <Labelled label={field.label}>
          <NumberInput
            value={Number(value ?? field.default ?? 0)}
            min={field.min}
            max={field.max}
            onChange={onChange}
          />
        </Labelled>
      );

    case 'select':
      return (
        <Labelled label={field.label}>
          <Select
            value={String(value ?? field.default ?? field.options?.[0] ?? '')}
            onChange={onChange}
            options={(field.options ?? []).map((o) => ({ value: o, label: o }))}
          />
        </Labelled>
      );

    case 'textarea':
      return (
        <Labelled label={field.label} hint={hint}>
          <textarea
            rows={3}
            value={String(value ?? '')}
            onChange={(e) => onChange(e.target.value)}
            className={`${inputClass} h-auto py-2 leading-[1.5]`}
          />
        </Labelled>
      );

    case 'colour':
      return (
        <Labelled label={field.label}>
          <div className="flex items-center gap-2">
            <input
              type="color"
              value={String(value ?? '#533afd')}
              onChange={(e) => onChange(e.target.value)}
              className="h-9 w-12 shrink-0 rounded-[4px] border border-[var(--border-hairline)] bg-transparent"
            />
            <input
              className={inputClass}
              value={String(value ?? '')}
              placeholder="Theme default"
              onChange={(e) => onChange(e.target.value || undefined)}
            />
          </div>
        </Labelled>
      );

    case 'string-list':
      return (
        <Labelled label={field.label} hint="Comma separated. Empty means all.">
          <input
            className={inputClass}
            value={Array.isArray(value) ? value.join(', ') : ''}
            onChange={(e) =>
              onChange(e.target.value.split(',').map((s) => s.trim()).filter(Boolean))
            }
          />
        </Labelled>
      );

    case 'datetime':
      return (
        <Labelled label={field.label}>
          <input
            type="datetime-local"
            className={inputClass}
            value={typeof value === 'string' ? value.slice(0, 16) : ''}
            onChange={(e) =>
              onChange(e.target.value ? new Date(e.target.value).toISOString() : undefined)
            }
          />
        </Labelled>
      );

    case 'unsupported':
      return (
        <Labelled label={field.label} hint="Edited elsewhere — this field has its own editor.">
          <div className={`${inputClass} flex items-center text-[var(--text-faint)]`}>
            {Array.isArray(value) ? `${value.length} item(s)` : '—'}
          </div>
        </Labelled>
      );

    default:
      return (
        <Labelled label={field.label} hint={hint}>
          <input
            className={inputClass}
            value={String(value ?? '')}
            maxLength={field.max}
            onChange={(e) => onChange(e.target.value)}
          />
        </Labelled>
      );
  }
}
