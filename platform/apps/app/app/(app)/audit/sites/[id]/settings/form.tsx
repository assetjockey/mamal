'use client';

import { useState, useTransition } from 'react';
import { Button, Card, SectionLabel, StatusBadge } from '@mamal/ui';
import { saveSettings } from './actions';

const SCHEDULES = [
  { key: 'manual', label: 'Manual only' },
  { key: 'daily', label: 'Every day' },
  { key: 'weekly', label: 'Every week' },
  { key: '30d', label: 'Every 30 days' },
  { key: '6h', label: 'Every 6 hours' },
  { key: '12h', label: 'Every 12 hours' },
  { key: '3d', label: 'Every 3 days' },
];

export function SettingsForm({
  auditSiteId,
  schedule,
  config,
  canSchedule,
}: {
  auditSiteId: string;
  schedule: string;
  config: { maxPages: number; maxDepth: number; respectRobots: boolean; excludePatterns?: string[] };
  canSchedule: boolean;
}) {
  const [pending, start] = useTransition();
  const [message, setMessage] = useState<{ tone: 'ok' | 'error'; text: string } | null>(null);

  return (
    <form
      action={(data) =>
        start(async () => {
          const result = await saveSettings(auditSiteId, data);
          setMessage(
            'error' in result && result.error
              ? { tone: 'error', text: result.error }
              : { tone: 'ok', text: 'Saved' },
          );
        })
      }
      className="max-w-2xl space-y-8 [&_*]:min-w-0"
    >
      <section>
        <SectionLabel>Schedule</SectionLabel>
        {!canSchedule ? (
          <p className="mb-3 text-[13px] text-[var(--text-muted)]">
            Scheduled audits are a paid feature. You can still run one whenever you like.
          </p>
        ) : null}
        <Card>
          <div className="grid gap-2 sm:grid-cols-2">
            {SCHEDULES.map((s) => (
              <label
                key={s.key}
                className="flex cursor-pointer items-center gap-2.5 rounded-[4px] border border-[var(--border-hairline)] px-3 py-2.5 text-[14px] transition-colors duration-[120ms] hover:bg-[var(--surface-hover)] has-[:checked]:border-[var(--accent)] has-[:checked]:bg-[var(--accent-wash)]"
              >
                <input
                  type="radio"
                  name="schedule"
                  value={s.key}
                  defaultChecked={schedule === s.key}
                  disabled={!canSchedule && s.key !== 'manual'}
                  className="accent-[var(--accent)]"
                />
                <span className={!canSchedule && s.key !== 'manual' ? 'text-[var(--text-faint)]' : ''}>
                  {s.label}
                </span>
              </label>
            ))}
          </div>
        </Card>
      </section>

      <section>
        <SectionLabel>Crawl</SectionLabel>
        <Card className="space-y-5">
          <Field
            name="maxPages"
            label="Maximum pages"
            hint="Each page counts once against your monthly quota. A crawl stops here even if more pages exist."
            defaultValue={config.maxPages}
            type="number"
            min={1}
            max={50000}
          />
          <Field
            name="maxDepth"
            label="Maximum depth"
            hint="How many clicks from the homepage to follow."
            defaultValue={config.maxDepth}
            type="number"
            min={1}
            max={20}
          />

          <label className="flex items-start gap-2.5">
            <input
              type="checkbox"
              name="respectRobots"
              defaultChecked={config.respectRobots}
              className="mt-0.5 accent-[var(--accent)]"
            />
            <span>
              <span className="block text-[14px]">Respect robots.txt</span>
              <span className="block text-[12px] text-[var(--text-faint)]">
                Leave this on unless you are auditing a staging site that blocks everything.
              </span>
            </span>
          </label>

          <div>
            <label htmlFor="excludePatterns" className="mb-1.5 block text-[13px] text-[var(--text-secondary)]">
              Exclude patterns
            </label>
            <textarea
              id="excludePatterns"
              name="excludePatterns"
              rows={4}
              defaultValue={(config.excludePatterns ?? []).join('\n')}
              placeholder={'/admin/\n\\?page=\\d+'}
              className="w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 py-2 font-mono text-[13px] outline-none transition-colors duration-[120ms] focus:border-[var(--accent)]"
            />
            <p className="mt-1 text-[12px] text-[var(--text-faint)]">
              One regular expression per line. Matching URLs are skipped, which keeps faceted search
              and paginated archives from eating the whole budget.
            </p>
          </div>
        </Card>
      </section>

      <div className="flex items-center gap-3">
        <Button type="submit" disabled={pending}>
          {pending ? 'Saving…' : 'Save settings'}
        </Button>
        {message ? (
          <StatusBadge status={message.tone === 'ok' ? 'ok' : 'error'}>{message.text}</StatusBadge>
        ) : null}
      </div>
    </form>
  );
}

function Field({
  name, label, hint, ...rest
}: { name: string; label: string; hint?: string } & React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <div>
      <label htmlFor={name} className="mb-1.5 block text-[13px] text-[var(--text-secondary)]">
        {label}
      </label>
      <input
        id={name}
        name={name}
        className="h-10 w-full max-w-40 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 text-[14px] tabular-nums outline-none transition-colors duration-[120ms] focus:border-[var(--accent)]"
        {...rest}
      />
      {hint ? <p className="mt-1 max-w-lg text-[12px] text-[var(--text-faint)]">{hint}</p> : null}
    </div>
  );
}
