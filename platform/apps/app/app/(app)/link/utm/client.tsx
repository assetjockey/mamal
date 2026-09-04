'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { newUtmPreset, removeUtmPreset, toggleAutoApply } from '../actions';

const PARAMS = ['source', 'medium', 'campaign', 'term', 'content'] as const;

const control =
  'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function PresetList({
  presets,
}: {
  presets: { id: string; name: string; values: Record<string, string>; auto_apply: boolean }[];
}) {
  const [name, setName] = useState('');
  const [values, setValues] = useState<Record<string, string>>({});
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    start(async () => {
      const filled = Object.fromEntries(Object.entries(values).filter(([, v]) => v.trim()));
      const result = await newUtmPreset(name.trim(), filled);
      if (!result.ok) { toast({ kind: 'error', message: result.error }); return; }
      setName(''); setValues({});
      router.refresh();
    });
  };

  return (
    <div className="grid gap-6 [&>*]:min-w-0 lg:grid-cols-2">
      <Card>
        <SectionLabel>New preset</SectionLabel>
        <div className="mt-3 grid gap-3">
          <div>
            <label htmlFor="utm-name" className={labelStyle}>Name</label>
            <input id="utm-name" value={name} onChange={(e) => setName(e.target.value)}
                   className={control} placeholder="Instagram — spring" />
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            {PARAMS.map((p) => (
              <div key={p}>
                <label htmlFor={`utm-${p}`} className={labelStyle}>utm_{p}</label>
                <input id={`utm-${p}`} value={values[p] ?? ''} className={control}
                       onChange={(e) => setValues((v) => ({ ...v, [p]: e.target.value }))} />
              </div>
            ))}
          </div>
          <div className="border-t border-[var(--border-hairline)] pt-3">
            <Button onClick={create} disabled={pending || !name.trim()}>
              {pending ? 'Saving…' : 'Save preset'}
            </Button>
          </div>
        </div>
      </Card>

      <Card>
        <SectionLabel>Presets</SectionLabel>
        {presets.length === 0 ? (
          <div className="mt-3">
            <EmptyState
              title="No presets yet"
              description="A preset saves you retyping the same five parameters on every link in a campaign."
            />
          </div>
        ) : (
          <ul className="mt-3 grid gap-3">
            {presets.map((p) => (
              <li key={p.id} className="border-b border-[var(--border-hairline)] pb-3 last:border-0">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <span className="text-[14px] text-[var(--text-primary)]">{p.name}</span>
                  <div className="flex items-center gap-2">
                    {p.auto_apply ? <StatusBadge status="ok">Automatic</StatusBadge> : null}
                    <Button
                      size="sm" variant="quiet" disabled={pending}
                      onClick={() => start(async () => {
                        await toggleAutoApply(p.id, !p.auto_apply);
                        router.refresh();
                      })}
                    >
                      {p.auto_apply ? 'Stop applying' : 'Apply to new links'}
                    </Button>
                    <Button
                      size="sm" variant="quiet" disabled={pending}
                      onClick={() => start(async () => {
                        await removeUtmPreset(p.id);
                        toast({ kind: 'info', message: `Deleted “${p.name}”. Links already created keep their parameters.` });
                        router.refresh();
                      })}
                    >
                      Delete
                    </Button>
                  </div>
                </div>
                <p className="mt-1 truncate text-[12px] text-[var(--text-faint)]">
                  {Object.entries(p.values).map(([k, v]) => `utm_${k}=${v}`).join(' · ') || 'No parameters set'}
                </p>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </div>
  );
}
