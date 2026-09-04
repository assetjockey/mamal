'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Button, Card, EmptyState, SectionLabel, StatusBadge, useToast, type Status,
} from '@mamal/ui';
import { addDomain, recheckDomain, removeDomain } from '../actions';

type Domain = {
  id: string; host: string; kind: string; dns_status: string; ssl_status: string;
  verification_token: string; is_primary: boolean; verified_at: string | null; links: number;
  last_check: { owned?: boolean; routed?: boolean; nextStep?: string | null };
  dns_checked_at: string | null;
};

export function DomainList({
  domains,
  canAdd,
  why,
  used,
  max,
}: {
  domains: Domain[];
  canAdd: boolean;
  why: string | null;
  used: number;
  max: number | null;
}) {
  const [host, setHost] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [pending, start] = useTransition();
  const router = useRouter();

  const add = () => {
    setError(null);
    start(async () => {
      const result = await addDomain(host.trim());
      if (!result.ok) { setError(result.error); return; }
      setHost('');
      router.refresh();
    });
  };

  return (
    <>
      {max !== null && max > 0 ? (
        <p className="mb-6 text-[13px] tabular-nums text-[var(--text-muted)]">
          {used} of {max} domain{max === 1 ? '' : 's'} used.
          {!canAdd ? <span className="text-[var(--color-status-warn)]"> {why}</span> : null}
        </p>
      ) : null}

      {canAdd ? (
        <div className="mb-6 flex flex-wrap items-start gap-2">
          <div>
            <label htmlFor="domain-host" className="sr-only">Domain</label>
            <input
              id="domain-host" value={host} onChange={(e) => setHost(e.target.value)}
              onKeyDown={(e) => { if (e.key === 'Enter' && host.trim()) add(); }}
              placeholder="links.example.com"
              className="w-[min(280px,60vw)] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
            />
            {error ? (
              <p role="alert" className="mt-1 max-w-[280px] text-[12px] text-[var(--color-status-error)]">{error}</p>
            ) : null}
          </div>
          <Button onClick={add} disabled={pending || !host.trim()}>Add domain</Button>
        </div>
      ) : (
        <p className="mb-6">
          <NextLink href="/settings/billing">
            <Button variant="ghost">{why ?? 'Upgrade to add another domain'}</Button>
          </NextLink>
        </p>
      )}

      {domains.length === 0 ? (
        <EmptyState
          title="No custom domains"
          description="Add a subdomain you control — links.yourbrand.com — and point a CNAME at us. Certificates are issued automatically."
        />
      ) : (
        <>
          <SectionLabel>Domains</SectionLabel>
          <div className="grid gap-4">
            {domains.map((d) => <DomainCard key={d.id} domain={d} pending={pending} start={start} />)}
          </div>
        </>
      )}
    </>
  );
}

function DomainCard({
  domain,
  pending,
  start,
}: {
  domain: Domain;
  pending: boolean;
  start: (fn: () => void) => void;
}) {
  const [copied, setCopied] = useState(false);
  const toast = useToast();
  const router = useRouter();

  /*
   * Three states, not two.
   *
   * "Verified but the certificate is still being issued" is a real and common
   * middle — the edge provider issues after the hostname is routed — and
   * showing it as Live puts a green tick next to a domain that still serves a
   * TLS warning.
   */
  const tone: Status = domain.verified_at && domain.ssl_status === 'active' ? 'ok'
    : domain.verified_at ? 'info'
    : domain.dns_status === 'partial' ? 'warn' : 'neutral';
  const label = domain.verified_at && domain.ssl_status === 'active' ? 'Live'
    : domain.verified_at ? 'Issuing certificate'
    : domain.dns_status === 'partial' ? 'Almost there' : 'Waiting for DNS';

  return (
    <Card>
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="min-w-0">
          <h3 className="truncate text-[20px] text-[var(--text-primary)]">{domain.host}</h3>
          <p className="mt-0.5 text-[13px] text-[var(--text-faint)]">
            {domain.links.toLocaleString()} link{domain.links === 1 ? '' : 's'} · {domain.kind}
          </p>
        </div>
        <StatusBadge status={tone}>{label}</StatusBadge>
      </div>

      {!domain.verified_at ? (
        <div className="mt-4 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] p-3">
          {/*
            What the last lookup actually saw, not just "pending".
            A customer who has added both records and still sees pending needs
            to know which one we cannot find — and support needs it without
            asking them to run `dig`.
          */}
          <p className="text-[13px] text-[var(--text-secondary)]">
            {domain.last_check?.nextStep ??
              'Add this record at your DNS provider. It usually takes a few minutes; some providers take up to an hour.'}
          </p>
          {domain.dns_checked_at ? (
            <p className="mt-1 text-[12px] text-[var(--text-faint)]">
              Ownership {domain.last_check?.owned ? 'confirmed' : 'not proved yet'} ·
              {' '}routing {domain.last_check?.routed ? 'confirmed' : 'not seen yet'}.
            </p>
          ) : null}
          <dl className="mt-3 grid gap-1 font-mono text-[12px] text-[var(--text-primary)]">
            <div className="flex flex-wrap gap-2">
              <dt className="text-[var(--text-faint)]">Type</dt><dd>CNAME</dd>
            </div>
            <div className="flex flex-wrap gap-2">
              <dt className="text-[var(--text-faint)]">Name</dt><dd className="break-all">{domain.host}</dd>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <dt className="text-[var(--text-faint)]">Value</dt>
              <dd className="break-all">cname.mamal.app</dd>
              <button
                type="button"
                onClick={async () => {
                  await navigator.clipboard?.writeText('cname.mamal.app').catch(() => {});
                  setCopied(true);
                  setTimeout(() => setCopied(false), 1600);
                }}
                className="rounded-[4px] px-1.5 py-0.5 text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)] hover:text-[var(--text-secondary)] focus-visible:outline-2 focus-visible:outline-[var(--accent-solid)]"
              >
                {copied ? 'Copied' : 'Copy'}
              </button>
            </div>
            <div className="flex flex-wrap gap-2">
              <dt className="text-[var(--text-faint)]">Verify</dt>
              <dd className="break-all">TXT _mamal.{domain.host} = {domain.verification_token}</dd>
            </div>
          </dl>
        </div>
      ) : null}

      <div className="mt-4 flex flex-wrap gap-2 border-t border-[var(--border-hairline)] pt-3">
        {!domain.verified_at ? (
          <Button
            size="sm" variant="quiet" disabled={pending}
            onClick={() => start(async () => {
              const result = await recheckDomain(domain.id);
              toast(
                !result.ok
                  ? { kind: 'error', message: result.error }
                  : result.owned && result.routed
                    ? { kind: 'ok', message: `${domain.host} is verified. The certificate takes a minute or two.` }
                    : // The resolver's actual finding, so pressing the button
                      // again is not the only thing left to try.
                      { kind: 'info', message: result.nextStep ?? 'Nothing found yet. DNS can take an hour.' },
              );
              router.refresh();
            })}
          >
            Check now
          </Button>
        ) : null}
        <Button
          size="sm" variant="quiet" disabled={pending}
          onClick={() => start(async () => {
            const result = await removeDomain(domain.id);
            toast(result.ok
              ? { kind: 'info', message: `Removed ${domain.host}.` }
              : { kind: 'error', message: result.error });
            router.refresh();
          })}
        >
          Remove
        </Button>
      </div>
    </Card>
  );
}
