'use client';

import { useMemo, useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import type { Rule } from '@mamal/redirect';
import { Button, Card, SectionLabel, StatusBadge, UpgradeGate, useToast } from '@mamal/ui';
import { saveRules, setPassword, updateLink } from '../../actions';
import { Simulator } from './simulator';
import { RuleList } from './rules';

export type EditableLink = {
  id: string; alias: string; kind: string; title: string | null;
  destinationUrl: string | null; isEnabled: boolean; campaign: string | null;
  expiresAt: string | null; expiresUrl: string | null; maxClicks: number | null;
  clicksCount: number; hasPassword: boolean;
  settings: Record<string, unknown>; shortUrl: string;
};

const field =
  'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 ' +
  'text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle =
  'mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function LinkEditor({
  link,
  rules: initialRules,
  rulesAllowed,
  rulesWhy,
}: {
  link: EditableLink;
  rules: Rule[];
  rulesAllowed: boolean;
  rulesWhy: string | null;
}) {
  const [destination, setDestination] = useState(link.destinationUrl ?? '');
  const [title, setTitle] = useState(link.title ?? '');
  const [enabled, setEnabled] = useState(link.isEnabled);
  const [expiresAt, setExpiresAt] = useState(link.expiresAt?.slice(0, 16) ?? '');
  const [expiresUrl, setExpiresUrl] = useState(link.expiresUrl ?? '');
  const [maxClicks, setMaxClicks] = useState(link.maxClicks?.toString() ?? '');
  const [password, setPasswordValue] = useState('');
  const [rules, setRules] = useState<Rule[]>(initialRules);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const utm = (link.settings.utm ?? {}) as Record<string, string>;
  const forwardQuery = link.settings.forwardQuery !== false;

  /**
   * The link as the resolver will see it once saved.
   *
   * The simulator is fed this object rather than a copy of the form, so what it
   * previews is the same input the redirect will get. That is the whole reason
   * `resolve` takes plain data: a simulator that re-implemented the rules would
   * drift, and "what a visitor from Germany on iOS sees" would become a guess.
   */
  const previewLink = useMemo(
    () => ({
      id: link.id,
      kind: link.kind,
      destinationUrl: destination || null,
      isEnabled: enabled,
      moderationStatus: 'ok',
      expiresAt: expiresAt ? new Date(expiresAt).toISOString() : null,
      expiresUrl: expiresUrl || null,
      maxClicks: maxClicks ? Number(maxClicks) : null,
      clicksCount: link.clicksCount,
      // Deliberately null: the simulator answers "where does this go", and a
      // password gate would return `password` for every visitor and show
      // nothing useful.
      passwordHash: null,
      settings: link.settings,
    }),
    [link, destination, enabled, expiresAt, expiresUrl, maxClicks],
  );

  const saveBasics = () => {
    start(async () => {
      const result = await updateLink(link.id, {
        destinationUrl: destination || undefined,
        title: title || undefined,
        isEnabled: enabled,
        expiresAt: expiresAt ? new Date(expiresAt).toISOString() : null,
        expiresUrl: expiresUrl || null,
        maxClicks: maxClicks ? Number(maxClicks) : null,
      });
      toast(result.ok
        ? { kind: 'ok', message: 'Saved. The change is live on the next click.' }
        : { kind: 'error', message: result.error });
      router.refresh();
    });
  };

  const savePassword = (value: string | null) => {
    start(async () => {
      await setPassword(link.id, value);
      toast({
        kind: 'ok',
        message: value ? 'Password set. Visitors will be asked for it.' : 'Password removed.',
      });
      setPasswordValue('');
      router.refresh();
    });
  };

  const persistRules = (next: Rule[]) => {
    start(async () => {
      const result = await saveRules(link.id, next);
      if (!result.ok) { toast({ kind: 'error', message: result.error }); return; }
      setRules(next);
      toast({ kind: 'ok', message: 'Rules saved.' });
      router.refresh();
    });
  };

  return (
    <div className="grid gap-6 [&>*]:min-w-0 xl:grid-cols-[minmax(0,1fr)_360px]">
      <div className="grid min-w-0 gap-6 [&>*]:min-w-0">
        <Card>
          <div className="flex flex-wrap items-center justify-between gap-3">
            <SectionLabel>Destination</SectionLabel>
            <div className="flex items-center gap-2">
              <StatusBadge status={enabled ? 'ok' : 'neutral'}>
                {enabled ? 'Live' : 'Paused'}
              </StatusBadge>
              <Button size="sm" variant="quiet" onClick={() => setEnabled((v) => !v)}>
                {enabled ? 'Pause' : 'Resume'}
              </Button>
            </div>
          </div>

          <div className="mt-4 grid gap-4">
            <div>
              <label htmlFor="dest" className={labelStyle}>Destination URL</label>
              <input id="dest" value={destination} onChange={(e) => setDestination(e.target.value)}
                     className={field} placeholder="https://example.com/page" />
            </div>
            <div>
              <label htmlFor="title" className={labelStyle}>Internal name</label>
              <input id="title" value={title} onChange={(e) => setTitle(e.target.value)}
                     className={field} placeholder="Spring campaign — Instagram" />
            </div>
          </div>
        </Card>

        <Card>
          <SectionLabel>Limits</SectionLabel>
          <p className="mt-1 max-w-[70ch] text-[13px] text-[var(--text-secondary)]">
            An expiry or a click limit ends the link. Give it a fallback URL and visitors
            go there instead of seeing an error — which is almost always what you want on
            something already printed.
          </p>
          <div className="mt-4 grid gap-4 md:grid-cols-2">
            <div>
              <label htmlFor="expires" className={labelStyle}>Expires</label>
              <input id="expires" type="datetime-local" value={expiresAt}
                     onChange={(e) => setExpiresAt(e.target.value)} className={field} />
            </div>
            <div>
              <label htmlFor="max" className={labelStyle}>Click limit</label>
              <input id="max" type="number" min={0} value={maxClicks}
                     onChange={(e) => setMaxClicks(e.target.value)} className={field}
                     placeholder="No limit" />
              <p className="mt-1 text-[12px] tabular-nums text-[var(--text-faint)]">
                {link.clicksCount.toLocaleString()} click
                {link.clicksCount === 1 ? '' : 's'} so far.
              </p>
            </div>
            <div className="md:col-span-2">
              <label htmlFor="fallback" className={labelStyle}>Fallback URL, once it ends</label>
              <input id="fallback" value={expiresUrl} onChange={(e) => setExpiresUrl(e.target.value)}
                     className={field} placeholder="https://example.com/campaign-over" />
            </div>
          </div>
          <div className="mt-5 flex gap-2 border-t border-[var(--border-hairline)] pt-4">
            <Button onClick={saveBasics} disabled={pending}>
              {pending ? 'Saving…' : 'Save'}
            </Button>
          </div>
        </Card>

        <Card>
          <SectionLabel>Password</SectionLabel>
          <p className="mt-1 max-w-[70ch] text-[13px] text-[var(--text-secondary)]">
            {link.hasPassword
              ? 'Visitors are asked for a password before the link resolves. The gate runs before any targeting rule, so nobody can learn the destination by reloading.'
              : 'Ask for a password before the link resolves.'}
          </p>
          <div className="mt-4 flex flex-wrap items-end gap-2">
            <div className="min-w-0 grow">
              <label htmlFor="pw" className={labelStyle}>
                {link.hasPassword ? 'New password' : 'Password'}
              </label>
              <input id="pw" type="password" autoComplete="new-password" value={password}
                     onChange={(e) => setPasswordValue(e.target.value)} className={field} />
            </div>
            <Button onClick={() => savePassword(password)} disabled={pending || !password}>
              {link.hasPassword ? 'Change' : 'Set password'}
            </Button>
            {link.hasPassword ? (
              <Button variant="ghost" onClick={() => savePassword(null)} disabled={pending}>
                Remove
              </Button>
            ) : null}
          </div>
        </Card>

        <Card>
          <SectionLabel>Rules</SectionLabel>
          {rulesAllowed ? (
            <RuleList rules={rules} onChange={persistRules} pending={pending} />
          ) : (
            <div className="mt-3">
              <UpgradeGate
                feature="Targeting and rotation rules"
                reason={rulesWhy ?? 'Rules are available on a paid plan.'}
                action={
                  <NextLink href="/settings/billing">
                    <Button size="sm">See plans</Button>
                  </NextLink>
                }
              />
            </div>
          )}
        </Card>
      </div>

      <div className="min-w-0">
        <Simulator link={previewLink} rules={rules} utm={utm} forwardQuery={forwardQuery} />
      </div>
    </div>
  );
}
