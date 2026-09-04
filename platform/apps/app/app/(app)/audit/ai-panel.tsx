'use client';

import { useState, useTransition } from 'react';
import { Button, StatusBadge } from '@mamal/ui';
import type { AiResult } from '@mamal/tool-audit';

/**
 * Why this is a panel and not a replacement.
 *
 * The static guidance from the rule catalogue is always rendered. This sits
 * beside it, and when AI is off or unavailable the panel explains which of the
 * several possible reasons applies — "your admin disabled AI" is a different
 * problem from "not on your plan", and telling them apart is the whole point
 * of the resolver returning the first failing reason.
 */
const REASON_COPY: Record<string, string> = {
  ai_disabled_instance: 'AI is switched off for this installation.',
  ai_disabled_tenant: 'AI is switched off for this workspace. An admin can re-enable it in Settings → AI.',
  ai_disabled_feature: 'This AI feature has been switched off.',
  ai_excluded_lifetime: 'Lifetime plans cover the platform but not AI.',
  insufficient_credits: 'Not enough credits for this.',
  not_in_plan: 'This is not included in your plan.',
  no_credential: 'No AI provider key is configured on this installation.',
  no_model_available: 'No AI model is enabled right now.',
};

export function AiPanel({
  label,
  hint,
  action,
}: {
  label: string;
  hint: string;
  action: () => Promise<AiResult<string>>;
}) {
  const [pending, start] = useTransition();
  const [result, setResult] = useState<AiResult<string> | null>(null);

  return (
    <div className="rounded-[4px] border border-[var(--color-lilac-border)] bg-[var(--accent-wash)] p-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="min-w-0">
          <div className="text-[13px] text-[var(--text-primary)]">{label}</div>
          <p className="mt-0.5 max-w-lg text-[12px] leading-[1.4] text-[var(--text-muted)]">{hint}</p>
        </div>
        <Button
          size="sm"
          variant="ghost"
          disabled={pending}
          onClick={() => start(async () => setResult(await action()))}
        >
          {pending ? 'Thinking…' : result?.ok ? 'Regenerate' : 'Generate'}
        </Button>
      </div>

      {result?.ok ? (
        <div className="mt-3 space-y-2 border-t border-[var(--color-lilac-border)] pt-3 text-[14px] leading-[1.5] text-[var(--text-primary)]">
          {result.value.split('\n\n').map((para, i) => (
            <p key={i}>{para}</p>
          ))}
        </div>
      ) : null}

      {result && !result.ok ? (
        <div className="mt-3 flex flex-wrap items-center gap-2 border-t border-[var(--color-lilac-border)] pt-3">
          <StatusBadge status="neutral">Unavailable</StatusBadge>
          <span className="text-[13px] text-[var(--text-secondary)]">
            {REASON_COPY[result.reason] ?? result.message}
          </span>
          <span className="text-[12px] text-[var(--text-faint)]">
            The guidance above does not depend on this.
          </span>
        </div>
      ) : null}
    </div>
  );
}
