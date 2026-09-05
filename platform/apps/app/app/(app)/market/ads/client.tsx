'use client';

import { Card, EmptyState, SectionLabel, StatusBadge } from '@mamal/ui';
import type { Finding, Totals } from '@mamal/tool-market/scoring';

type Report = {
  accountId: string;
  name: string;
  currency: string;
  totals: Totals;
  previous: Totals;
  findings: Finding[];
};

/** Micros, because money in floats is how ledgers stop balancing. */
const money = (micros: number | null, currency: string) =>
  micros === null
    ? '—'
    : new Intl.NumberFormat(undefined, {
        style: 'currency', currency, maximumFractionDigits: 0,
      }).format(micros / 1_000_000);

const pct = (n: number | null) => (n === null ? '—' : `${(n * 100).toFixed(2)}%`);

const FINDING_LABEL: Record<Finding['kind'], { label: string; tone: 'ok' | 'warn' | 'error' | 'info' }> = {
  wasting: { label: 'Wasting', tone: 'error' },
  stalled: { label: 'Stalled', tone: 'warn' },
  creative_fatigue: { label: 'Tired creative', tone: 'warn' },
  winning: { label: 'Working', tone: 'ok' },
  budget_capped: { label: 'Budget capped', tone: 'info' },
};

export function AdReport({ reports }: { reports: Report[] }) {
  if (reports.length === 0) {
    return (
      <EmptyState
        title="No ad accounts connected"
        description="Connect Meta, Google or TikTok from Connections. Everything on this screen is computed from the spend they report — it costs nothing and works with AI switched off."
      />
    );
  }

  return (
    <div className="flex flex-col gap-8">
      {reports.map((report) => (
        <section key={report.accountId} className="flex flex-col gap-3">
          <SectionLabel>{report.name}</SectionLabel>

          <div className="grid grid-cols-2 gap-3 lg:grid-cols-3 2xl:grid-cols-6">
            <Stat label="Spend" value={money(report.totals.spendMicros, report.currency)} />
            <Stat
              label="Conversions"
              value={report.totals.conversions.toLocaleString()}
              delta={change(report.previous.conversions, report.totals.conversions)}
            />
            <Stat
              label="Cost each"
              value={money(report.totals.cpaMicros, report.currency)}
              // Cheaper is better, so the sign is inverted deliberately.
              delta={invert(change(report.previous.cpaMicros, report.totals.cpaMicros))}
            />
            <Stat
              label="Return"
              value={report.totals.roas === null ? '—' : `${report.totals.roas.toFixed(1)}×`}
              delta={change(report.previous.roas, report.totals.roas)}
            />
            <Stat label="Clicks" value={report.totals.clicks.toLocaleString()} />
            <Stat label="Click rate" value={pct(report.totals.ctr)} />
          </div>

          {/*
            * A blank where a rate has no denominator, never a zero: "no
            * conversions yet" and "a cost of zero" are different facts and
            * only one of them is true.
            */}
          {report.totals.conversions === 0 && report.totals.spendMicros > 0 && (
            <p className="text-[12px] text-[var(--text-secondary)]">
              Nothing has converted in this period, so cost-per-conversion and return have
              nothing to divide by.
            </p>
          )}

          {report.findings.length === 0 ? (
            <p className="text-[12px] text-[var(--text-secondary)]">
              Nothing stands out this period — no campaign is spending without converting, and
              none has got noticeably worse.
            </p>
          ) : (
            <ul className="flex flex-col gap-2">
              {report.findings.map((finding, i) => {
                const kind = FINDING_LABEL[finding.kind];
                return (
                  <li key={`${finding.entityId}-${finding.kind}-${i}`}>
                    <Card>
                      <div className="flex flex-wrap items-baseline justify-between gap-2">
                        <span className="min-w-0 flex-1 truncate text-[14px]">
                          {finding.entityName}
                        </span>
                        <StatusBadge status={kind.tone}>{kind.label}</StatusBadge>
                      </div>
                      <p className="mt-1 text-[12px]">{finding.message}</p>
                    </Card>
                  </li>
                );
              })}
            </ul>
          )}
        </section>
      ))}

      <p className="text-[11px] text-[var(--text-secondary)]">
        Compared against the previous period of equal length, ending three days ago — ad
        platforms revise recent days as conversions attribute late, and including them shows a
        decline that is not there.
      </p>
    </div>
  );
}

function Stat({
  label,
  value,
  delta,
}: {
  label: string;
  value: string;
  delta?: number | null;
}) {
  return (
    <Card>
      <div className="text-[11px] text-[var(--text-secondary)]">{label}</div>
      <div className="mt-1 text-[20px] leading-none font-light tabular-nums">{value}</div>
      {delta !== null && delta !== undefined && Math.abs(delta) >= 1 && (
        <div
          className={
            'mt-1 text-[11px] tabular-nums ' +
            (delta > 0 ? 'text-[var(--status-ok)]' : 'text-[var(--status-error)]')
          }
        >
          {delta > 0 ? '+' : ''}
          {Math.round(delta)}%
        </div>
      )}
    </Card>
  );
}

/** Percentage change, or null where there is nothing to compare against. */
function change(before: number | null, after: number | null): number | null {
  if (before === null || after === null || before === 0) return null;
  return ((after - before) / before) * 100;
}

function invert(value: number | null): number | null {
  return value === null ? null : -value;
}
