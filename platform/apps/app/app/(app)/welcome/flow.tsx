'use client';

import { useState, useTransition } from 'react';
import { Button, Card, SectionLabel } from '@mamal/ui';
import { addFirstSite, saveInterests } from './actions';

/** One question, six answers, mapped to the six tools. */
const INTERESTS = [
  { key: 'audit', label: 'Fix what is hurting my search visibility', tool: 'Audit' },
  { key: 'track', label: 'Understand where my traffic comes from', tool: 'Track' },
  { key: 'monitor', label: 'Know the moment my site goes down', tool: 'Monitor' },
  { key: 'link', label: 'Share links, QR codes and files', tool: 'Link' },
  { key: 'market', label: 'Rank higher and get cited by AI', tool: 'Market' },
  { key: 'confirm', label: 'Show social proof and win more trust', tool: 'Confirm' },
];

export function WelcomeFlow({
  interests: initial,
  siteUrl,
}: {
  interests: string[];
  siteUrl: string | null;
}) {
  const [interests, setInterests] = useState<string[]>(initial);
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();

  const toggle = (key: string) =>
    setInterests((prev) => (prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key]));

  return (
    <div className="space-y-8">
      <section>
        <SectionLabel>What brings you here?</SectionLabel>
        <p className="mb-4 text-[14px] text-[var(--text-secondary)]">
          Pick as many as apply. This orders your sidebar — it does not lock anything.
        </p>
        <div className="grid gap-2 sm:grid-cols-2">
          {INTERESTS.map((option) => {
            const on = interests.includes(option.key);
            return (
              <button
                key={option.key}
                type="button"
                onClick={() => toggle(option.key)}
                aria-pressed={on}
                className={
                  'rounded-[4px] border px-4 py-3 text-left transition-colors duration-[120ms] ' +
                  (on
                    ? 'border-[var(--accent)] bg-[var(--accent-wash)]'
                    : 'border-[var(--border-hairline)] hover:bg-[var(--surface-hover)]')
                }
              >
                <span className="block text-[14px] text-[var(--text-primary)]">{option.label}</span>
                <span className="mt-0.5 block text-[12px] text-[var(--text-faint)]">
                  {option.tool}
                </span>
              </button>
            );
          })}
        </div>
      </section>

      <section>
        <SectionLabel>Your first website</SectionLabel>
        <Card>
          <form
            action={(data) =>
              start(async () => {
                setError(null);
                if (interests.length) await saveInterests(interests);
                const result = await addFirstSite(data);
                if (result?.error) setError(result.error);
              })
            }
          >
            <label htmlFor="url" className="mb-1.5 block text-[13px] text-[var(--text-secondary)]">
              Website address
            </label>
            <div className="flex flex-col gap-3 sm:flex-row">
              <input
                id="url"
                name="url"
                defaultValue={siteUrl ?? ''}
                placeholder="example.com"
                required
                className="h-10 flex-1 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 text-[14px] outline-none transition-colors duration-[120ms] focus:border-[var(--accent)]"
              />
              <Button type="submit" disabled={pending}>
                {pending ? 'Setting up…' : 'Continue'}
              </Button>
            </div>
            {error ? (
              <p role="alert" className="mt-2 text-[13px] text-[var(--color-status-error)]">
                {error}
              </p>
            ) : null}
            <p className="mt-3 text-[12px] text-[var(--text-faint)]">
              One address is enough. Audit, Monitor and Track all point at the same site — you do
              not set it up three times.
            </p>
          </form>
        </Card>
      </section>
    </div>
  );
}
