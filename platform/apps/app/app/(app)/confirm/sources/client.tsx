'use client';

import { useEffect, useState } from 'react';
import { Button, useToast } from '@mamal/ui';

/**
 * The webhook a customer's backend posts to.
 *
 * Shows the real URL and a working example rather than linking to docs: this is
 * the one source anybody can wire up today, and a copyable curl is the shortest
 * path from reading to a conversion appearing.
 */
export function WebhookSource({
  campaigns, enabled,
}: { campaigns: { id: string; name: string; pixel_key: string }[]; enabled: boolean }) {
  const toast = useToast();
  const [origin, setOrigin] = useState('');
  const [selected, setSelected] = useState(campaigns[0]?.pixel_key ?? '');

  useEffect(() => setOrigin(window.location.origin), []);

  const example = `curl -X POST ${origin}/api/c/conversion \\
  -H 'content-type: application/json' \\
  -d '{
    "key": "${selected}",
    "type": "bought",
    "data": { "name": "Ana", "city": "Lisbon" }
  }'`;

  return (
    <div className="mt-3">
      {campaigns.length > 1 ? (
        <label className="mb-2 block">
          <span className="sr-only">Campaign</span>
          <select
            value={selected}
            onChange={(e) => setSelected(e.target.value)}
            className="h-8 w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-2 text-[13px] text-[var(--text-primary)]"
          >
            {campaigns.map((c) => (
              <option key={c.id} value={c.pixel_key}>{c.name}</option>
            ))}
          </select>
        </label>
      ) : null}

      <pre
        tabIndex={0}
        className="overflow-x-auto rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-band)] p-2.5 font-mono text-[11px] leading-[1.6] text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
      >
        {example}
      </pre>

      <div className="mt-2 flex flex-wrap items-center gap-2">
        <Button
          size="sm"
          variant="quiet"
          disabled={!enabled}
          onClick={async () => {
            try {
              await navigator.clipboard.writeText(example);
              toast({ message: 'Example copied.', kind: 'ok' });
            } catch {
              toast({ message: 'Copy blocked — select the text instead.' });
            }
          }}
        >
          Copy
        </Button>
        {!enabled ? (
          <span className="text-[11px] text-[var(--text-faint)]">
            Included from Starter.
          </span>
        ) : null}
      </div>

      <p className="mt-2 text-[11px] leading-[1.5] text-[var(--text-faint)]">
        Only <code className="font-mono">name</code> and <code className="font-mono">city</code> reach
        a visitor’s browser, and only the first name. Send more if it is useful to you here — it
        stays here.
      </p>
    </div>
  );
}
