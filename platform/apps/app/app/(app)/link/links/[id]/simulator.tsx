'use client';

import { useMemo, useState } from 'react';
import { resolve, type Link, type Rule } from '@mamal/redirect';
import { Card, SectionLabel, StatusBadge } from '@mamal/ui';

/**
 * "What does a visitor from Germany, on iOS, see?"
 *
 * The single most useful thing on this screen, and the reason `resolve` is a
 * pure function over plain data. It runs the *real* resolver in the browser
 * against the rules exactly as they will be saved — no re-implementation, no
 * approximation, no second copy of the precedence logic to drift out of sync.
 *
 * It is also the fastest way to catch the mistake that rules invite: a rule
 * ordered below one that already matches everything can never fire, and the
 * simulator shows that immediately by naming which rule won.
 */

const COUNTRIES: [code: string, label: string][] = [
  ['', 'Anywhere'], ['DE', 'Germany'], ['US', 'United States'], ['GB', 'United Kingdom'],
  ['FR', 'France'], ['BR', 'Brazil'], ['IN', 'India'], ['JP', 'Japan'], ['AU', 'Australia'],
];
const SYSTEMS = ['', 'iOS', 'Android', 'macOS', 'Windows', 'Linux'];
const DEVICES: ('' | 'desktop' | 'mobile' | 'tablet')[] = ['', 'desktop', 'mobile', 'tablet'];
const BROWSERS = ['', 'Chrome', 'Safari', 'Firefox', 'Edge'];

const control =
  'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function Simulator({
  link,
  rules,
  utm,
  forwardQuery,
}: {
  link: Link;
  rules: Rule[];
  utm: Record<string, string>;
  forwardQuery: boolean;
}) {
  const [country, setCountry] = useState('');
  const [os, setOs] = useState('');
  const [device, setDevice] = useState<'' | 'desktop' | 'mobile' | 'tablet'>('');
  const [browser, setBrowser] = useState('');
  const [language, setLanguage] = useState('');
  const [query, setQuery] = useState('');

  const outcome = useMemo(
    () =>
      resolve({
        link,
        rules,
        visitor: {
          country: country || undefined,
          os: os || undefined,
          device: device || undefined,
          browser: browser || undefined,
          language: language || undefined,
          // A stable hash so a rotation shows the *same* arm while the operator
          // changes other fields — otherwise every keystroke rerolls the test
          // and the panel looks broken.
          visitorHash: `sim|${country}|${os}|${device}|${browser}`,
        } as never,
        query: query.replace(/^\?/, ''),
      }),
    [link, rules, country, os, device, browser, language, query],
  );

  const winner = 'ruleId' in outcome && outcome.ruleId
    ? rules.findIndex((r) => r.id === outcome.ruleId)
    : -1;

  return (
    <Card>
      <SectionLabel>What a visitor sees</SectionLabel>
      <p className="mt-1 text-[13px] text-[var(--text-secondary)]">
        This runs the same resolver the redirect does, on the rules as they are now.
      </p>

      <div className="mt-4 grid grid-cols-2 gap-3">
        <div>
          <label htmlFor="sim-country" className={labelStyle}>Country</label>
          <select id="sim-country" value={country} onChange={(e) => setCountry(e.target.value)} className={control}>
            {COUNTRIES.map(([code, label]) => <option key={code} value={code}>{label}</option>)}
          </select>
        </div>
        <div>
          <label htmlFor="sim-os" className={labelStyle}>Operating system</label>
          <select id="sim-os" value={os} onChange={(e) => setOs(e.target.value)} className={control}>
            {SYSTEMS.map((s) => <option key={s} value={s}>{s || 'Any'}</option>)}
          </select>
        </div>
        <div>
          <label htmlFor="sim-device" className={labelStyle}>Device</label>
          <select id="sim-device" value={device}
                  onChange={(e) => setDevice(e.target.value as typeof device)} className={control}>
            {DEVICES.map((d) => <option key={d} value={d}>{d || 'Any'}</option>)}
          </select>
        </div>
        <div>
          <label htmlFor="sim-browser" className={labelStyle}>Browser</label>
          <select id="sim-browser" value={browser} onChange={(e) => setBrowser(e.target.value)} className={control}>
            {BROWSERS.map((b) => <option key={b} value={b}>{b || 'Any'}</option>)}
          </select>
        </div>
        <div>
          <label htmlFor="sim-lang" className={labelStyle}>Language</label>
          <input id="sim-lang" value={language} onChange={(e) => setLanguage(e.target.value)}
                 className={control} placeholder="de" />
        </div>
        <div>
          <label htmlFor="sim-query" className={labelStyle}>Incoming query</label>
          <input id="sim-query" value={query} onChange={(e) => setQuery(e.target.value)}
                 className={control} placeholder="ref=newsletter" />
        </div>
      </div>

      <div
        aria-live="polite"
        className="mt-5 border-t border-[var(--border-hairline)] pt-4"
      >
        <Result outcome={outcome} winner={winner} rulesCount={rules.length} />

        {Object.keys(utm).length > 0 || forwardQuery ? (
          <p className="mt-3 text-[12px] text-[var(--text-faint)]">
            {Object.keys(utm).length > 0
              ? `The link’s own UTM wins over anything on the incoming request. `
              : ''}
            {forwardQuery
              ? 'Other query parameters are forwarded through.'
              : 'Incoming query parameters are dropped.'}
          </p>
        ) : null}
      </div>
    </Card>
  );
}

function Result({
  outcome,
  winner,
  rulesCount,
}: {
  outcome: ReturnType<typeof resolve>;
  winner: number;
  rulesCount: number;
}) {
  switch (outcome.kind) {
    case 'redirect':
      return (
        <>
          <div className="flex items-center gap-2">
            <StatusBadge status="ok">302</StatusBadge>
            <span className="text-[12px] text-[var(--text-faint)]">
              {winner >= 0
                ? `Rule ${winner + 1} of ${rulesCount} matched${
                    outcome.variantIndex !== undefined ? ` — variant ${outcome.variantIndex + 1}` : ''
                  }`
                : 'No rule matched — the default destination'}
            </span>
          </div>
          <p className="mt-2 break-all text-[13px] text-[var(--text-primary)]">{outcome.url}</p>
        </>
      );
    case 'blocked':
      return (
        <>
          <StatusBadge status="error">Blocked</StatusBadge>
          <p className="mt-2 text-[13px] text-[var(--text-secondary)]">
            {outcome.reason === 'rule'
              ? `Rule ${winner + 1} blocks this visitor.`
              : outcome.reason === 'disabled'
                ? 'The link is paused, so nobody reaches the destination.'
                : 'The link was blocked by moderation.'}
          </p>
        </>
      );
    case 'gone':
      return (
        <>
          <StatusBadge status="warn">
            {outcome.reason === 'expired' ? 'Expired' : 'Click limit reached'}
          </StatusBadge>
          <p className="mt-2 break-all text-[13px] text-[var(--text-secondary)]">
            {outcome.url ? `Visitors go to ${outcome.url}` : 'Visitors see the “not available” page.'}
          </p>
        </>
      );
    case 'splash':
    case 'interstitial':
      return (
        <>
          <StatusBadge status="info">
            {outcome.kind === 'splash' ? 'Splash page' : 'Content warning'}
          </StatusBadge>
          <p className="mt-2 break-all text-[13px] text-[var(--text-secondary)]">
            Then on to {outcome.url}
          </p>
        </>
      );
    case 'render':
      return (
        <>
          <StatusBadge status="info">Renders a page</StatusBadge>
          <p className="mt-2 text-[13px] text-[var(--text-secondary)]">
            This link shows a {outcome.what} rather than redirecting.
          </p>
        </>
      );
    case 'password':
      return (
        <>
          <StatusBadge status="info">Password</StatusBadge>
          <p className="mt-2 text-[13px] text-[var(--text-secondary)]">
            Visitors are asked for the password before any rule is evaluated.
          </p>
        </>
      );
    case 'not_found':
      return (
        <>
          <StatusBadge status="warn">No destination</StatusBadge>
          <p className="mt-2 text-[13px] text-[var(--text-secondary)]">
            No rule matched and there is no default destination, so this visitor sees a 404.
          </p>
        </>
      );
  }
}
