'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Button, Card, EmptyState, SectionLabel, StatusBadge, useToast, type Status,
} from '@mamal/ui';
import { connectProvider, disconnect, syncNow } from '../actions';

export type ConnectionRow = {
  id: string;
  provider: string;
  display_name: string;
  status: string;
  last_error: string | null;
  last_synced_at: string | null;
  expires_at: string | null;
};

type Limit = { used: number; max: number | null; allowed: boolean; why: string | null };

/**
 * The providers, and what each one buys.
 *
 * Ordered by usefulness rather than alphabetically: Search Console first,
 * because it is free and it is the one that makes the rest of the tool work
 * without spending anything.
 */
const PROVIDERS: { key: string; label: string; blurb: string; free: boolean }[] = [
  { key: 'google_search_console', label: 'Search Console', free: true,
    blurb: 'Queries, pages and positions. Every opportunity finder runs on this.' },
  { key: 'google_analytics', label: 'Analytics', free: true,
    blurb: 'What visitors did after they arrived.' },
  { key: 'google_business', label: 'Google Business', free: false,
    blurb: 'Reviews, posts and local rank.' },
  { key: 'x', label: 'X', free: true, blurb: 'Publish and monitor.' },
  { key: 'linkedin', label: 'LinkedIn', free: true, blurb: 'Pages and profiles.' },
  { key: 'instagram', label: 'Instagram', free: true, blurb: 'Profiles and reels.' },
  { key: 'meta_ads', label: 'Meta Ads', free: false, blurb: 'Spend, joined to results.' },
  { key: 'google_ads', label: 'Google Ads', free: false, blurb: 'Spend, joined to results.' },
];

export function ConnectionList({
  rows,
  limits,
}: {
  rows: ConnectionRow[];
  limits: Record<string, Limit>;
}) {
  const [adding, setAdding] = useState<string | null>(null);
  const [identifier, setIdentifier] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const byProvider = new Map(rows.map((r) => [r.provider, r]));

  const connect = (provider: string) => {
    setError(null);
    start(async () => {
      const result = await connectProvider({
        provider,
        externalId: identifier.trim(),
        displayName: identifier.trim(),
      });
      if (!result.ok) { setError(result.error); return; }
      toast({ kind: 'ok', message: 'Connected. The first sync runs shortly.' });
      setAdding(null); setIdentifier('');
      router.refresh();
    });
  };

  return (
    <>
      {rows.length > 0 ? (
        <>
          <SectionLabel>Connected</SectionLabel>
          <div className="mb-8 mt-3 grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
            {rows.map((row) => {
              const tone: Status = row.status === 'active' ? 'ok'
                : row.status === 'error' ? 'error' : 'warn';
              return (
                <Card key={row.id}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                      <p className="truncate text-[16px] text-[var(--text-primary)]">
                        {row.display_name}
                      </p>
                      <p className="mt-0.5 text-[13px] text-[var(--text-faint)]">
                        {label(row.provider)}
                        {row.last_synced_at
                          ? ` · synced ${new Date(row.last_synced_at).toLocaleString()}`
                          : ' · waiting for the first sync'}
                      </p>
                    </div>
                    <StatusBadge status={tone}>
                      {row.status === 'active' ? 'Connected' : row.status}
                    </StatusBadge>
                  </div>

                  {row.last_error ? (
                    // The provider's own words: "token revoked" and "insufficient
                    // permission" need different fixes, and only it knows which.
                    <p className="mt-3 rounded-[4px] bg-[var(--surface-ground)] px-3 py-2 text-[13px] text-[var(--text-secondary)]">
                      {row.last_error}
                    </p>
                  ) : null}

                  <div className="mt-4 flex flex-wrap gap-2 border-t border-[var(--border-hairline)] pt-3">
                    {row.provider === 'google_search_console' ? (
                      <Button
                        size="sm" variant="quiet" disabled={pending}
                        onClick={() => start(async () => {
                          const result = await syncNow(row.id);
                          toast(
                            result.ok
                              ? {
                                  kind: 'ok',
                                  message:
                                    `Pulled ${result.rows.toLocaleString()} row(s) across ${result.days} day(s). ` +
                                    `${result.opportunities} opportunit${result.opportunities === 1 ? 'y' : 'ies'} now open.`,
                                }
                              : { kind: 'error', message: result.error },
                          );
                          router.refresh();
                        })}
                      >
                        Sync now
                      </Button>
                    ) : null}
                    <Button
                      size="sm" variant="quiet" disabled={pending}
                      onClick={() => start(async () => {
                        await disconnect(row.id);
                        // The history stays: months of Search Console rows point
                        // at this connection, and losing them is not recoverable.
                        toast({ kind: 'info', message: 'Disconnected. Everything already synced is kept.' });
                        router.refresh();
                      })}
                    >
                      Disconnect
                    </Button>
                  </div>
                </Card>
              );
            })}
          </div>
        </>
      ) : null}

      <SectionLabel>Available</SectionLabel>
      <div className="mt-3 grid gap-4 lg:grid-cols-2 [&>*]:min-w-0">
        {PROVIDERS.filter((p) => !byProvider.has(p.key)).map((provider) => {
          const limit = limits[provider.key];
          return (
            <Card key={provider.key}>
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0">
                  <p className="text-[16px] text-[var(--text-primary)]">{provider.label}</p>
                  <p className="mt-0.5 max-w-[46ch] text-[13px] text-[var(--text-secondary)]">
                    {provider.blurb}
                  </p>
                  {limit && limit.max !== null ? (
                    <p className="mt-1 text-[12px] tabular-nums text-[var(--text-faint)]">
                      {limit.used} of {limit.max} used
                    </p>
                  ) : null}
                </div>
                {limit && !limit.allowed ? (
                  <NextLink href="/settings/billing">
                    <Button size="sm" variant="ghost">{limit.why ?? 'Upgrade'}</Button>
                  </NextLink>
                ) : (
                  <Button size="sm" variant="quiet" onClick={() => { setAdding(provider.key); setError(null); }}>
                    Connect
                  </Button>
                )}
              </div>

              {adding === provider.key ? (
                <div className="mt-4 border-t border-[var(--border-hairline)] pt-3">
                  {/*
                    OAuth is not wired yet, so this takes the property or handle
                    directly. Said plainly rather than shown as a broken button:
                    the connection it creates is real and everything downstream
                    works against it.
                  */}
                  <label htmlFor={`id-${provider.key}`} className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
                    {provider.key.startsWith('google_search') ? 'Property' : 'Account or handle'}
                  </label>
                  <input
                    id={`id-${provider.key}`}
                    autoFocus
                    value={identifier}
                    onChange={(e) => setIdentifier(e.target.value)}
                    onKeyDown={(e) => { if (e.key === 'Enter' && identifier.trim()) connect(provider.key); }}
                    placeholder={provider.key.startsWith('google_search') ? 'sc-domain:example.com' : '@example'}
                    className="w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
                  />
                  <p className="mt-1 text-[12px] text-[var(--text-faint)]">
                    Sign-in through {provider.label} arrives with the OAuth flow. For now this
                    records the account so syncing and limits work against it.
                  </p>
                  {error ? (
                    <p role="alert" className="mt-2 text-[13px] text-[var(--color-status-error)]">{error}</p>
                  ) : null}
                  <div className="mt-3 flex gap-2">
                    <Button size="sm" onClick={() => connect(provider.key)} disabled={pending || !identifier.trim()}>
                      {pending ? 'Connecting…' : 'Connect'}
                    </Button>
                    <Button size="sm" variant="quiet" onClick={() => { setAdding(null); setError(null); }}>
                      Cancel
                    </Button>
                  </div>
                </div>
              ) : null}
            </Card>
          );
        })}
      </div>

      {rows.length === 0 && PROVIDERS.length === 0 ? (
        <EmptyState title="Nothing to connect" description="" />
      ) : null}
    </>
  );
}

const label = (provider: string) =>
  PROVIDERS.find((p) => p.key === provider)?.label ?? provider.replace(/_/g, ' ');
