/**
 * Deciding whether something is down, and how much of the time it was up.
 *
 * These are the two numbers the whole tool exists to produce, and both have a
 * failure mode that is worse than being wrong: an outage nobody believes, and
 * an uptime figure that flatters. Every case here is one of those.
 */
import { describe, expect, it } from 'vitest';
import {
  ESCALATION_MINUTES, formatUptime, judge, nextEscalation, requiredAgreement, uptime,
  type RegionResult,
} from '../agreement.ts';

const ok = (region: string): RegionResult => ({ region, ok: true, responseMs: 120 });
const down = (region: string, failureKind = 'unreachable'): RegionResult => ({
  region, ok: false, failureKind, error: 'connect ETIMEDOUT',
});

describe('how many regions have to agree', () => {
  it('needs a majority of those reporting', () => {
    expect(requiredAgreement(3)).toBe(2);
    expect(requiredAgreement(5)).toBe(3);
    expect(requiredAgreement(2)).toBe(2);
  });

  it('takes one region at its word, because there is nobody to ask', () => {
    // Not a weaker guarantee — an honest one. The UI says a single-region
    // monitor is more prone to false positives.
    expect(requiredAgreement(1)).toBe(1);
  });
});

describe('judging a round of checks', () => {
  it('calls it up when every region agrees', () => {
    const verdict = judge([ok('us'), ok('eu'), ok('ap')]);
    expect(verdict).toMatchObject({ status: 'up', confirmed: true, failed: 0 });
  });

  it('does not open an incident for one flaky region', () => {
    /*
     * The number one support ticket in every uptime product. One probe failing
     * is a bad minute on somebody's network, not an outage — and a tool that
     * pages for it gets muted within a fortnight.
     */
    const verdict = judge([down('us'), ok('eu'), ok('ap')]);
    expect(verdict.status).toBe('degraded');
    expect(verdict.confirmed).toBe(false);
    expect(verdict.reason).toMatch(/2 of 3 regions have to agree/);
  });

  it('opens one when two of three agree', () => {
    const verdict = judge([down('us'), down('eu'), ok('ap')]);
    expect(verdict).toMatchObject({ status: 'down', confirmed: true, failed: 2 });
    expect(verdict.reason).toMatch(/2 of 3/);
  });

  it('decides on the regions that reported, not the ones configured', () => {
    /*
     * If a probe region is itself down, waiting for it means no incident ever
     * opens — the failure mode that quietly turns monitoring off. Two reporting,
     * both failing, is an outage.
     */
    const verdict = judge([down('us'), down('eu')]);
    expect(verdict.confirmed).toBe(true);
    expect(verdict.reporting).toBe(2);
  });

  it('says so when nothing reported, rather than blaming the customer', () => {
    const verdict = judge([]);
    // Calling a service down because *our* probes failed is how a monitoring
    // outage becomes somebody else's incident.
    expect(verdict.status).toBe('degraded');
    expect(verdict.confirmed).toBe(false);
    expect(verdict.reason).toMatch(/our problem/i);
  });

  it('names where it is failing from', () => {
    const verdict = judge([down('eu'), down('us'), ok('ap')]);
    // "Down" without "where" is not actionable — half the time it is a routing
    // problem rather than the service.
    expect(verdict.reason).toContain('eu and us');
  });

  it('labels the incident by the commonest failure, not the first', () => {
    const verdict = judge([
      down('us', 'timeout'),
      down('eu', 'timeout'),
      down('ap', 'status'),
    ]);
    // Whichever probe finished first would otherwise decide, and most incidents
    // would be mislabelled.
    expect(verdict.failureKind).toBe('timeout');
  });
});

describe('escalation', () => {
  const started = new Date('2026-03-20T09:00:00Z');
  const at = (minutes: number) => new Date(started.getTime() + minutes * 60_000);

  it('fires immediately when the incident opens', () => {
    const step = nextEscalation({
      startedAt: started, acknowledgedAt: null, escalationLevel: 0,
      lastNotifiedAt: null, now: started,
    });
    expect(step).toMatchObject({ escalate: true, level: 1 });
  });

  it('waits before climbing', () => {
    const early = nextEscalation({
      startedAt: started, acknowledgedAt: null, escalationLevel: 1,
      lastNotifiedAt: started, now: at(2),
    });
    expect(early.escalate).toBe(false);

    const due = nextEscalation({
      startedAt: started, acknowledgedAt: null, escalationLevel: 1,
      lastNotifiedAt: started, now: at(6),
    });
    expect(due).toMatchObject({ escalate: true, level: 2 });
    expect(due.reason).toMatch(/5 minutes/);
  });

  it('stops climbing the moment somebody acknowledges', () => {
    const step = nextEscalation({
      startedAt: started, acknowledgedAt: at(3), escalationLevel: 1,
      lastNotifiedAt: started, now: at(60),
    });
    // Acknowledged is not resolved: the incident is still open and still on the
    // status page. It just stops waking people.
    expect(step.escalate).toBe(false);
    expect(step.reason).toMatch(/somebody is on it/i);
  });

  it('stops at the top of the ladder', () => {
    const step = nextEscalation({
      startedAt: started, acknowledgedAt: null,
      escalationLevel: ESCALATION_MINUTES.length,
      lastNotifiedAt: at(60), now: at(600),
    });
    // Paging hourly forever trains people to ignore the channel that matters.
    expect(step.escalate).toBe(false);
    expect(step.reason).toMatch(/as far as it goes/i);
  });
});

describe('uptime', () => {
  const window = { from: new Date('2026-03-01T00:00:00Z'), to: new Date('2026-03-31T00:00:00Z') };
  const check = (day: number, isOk: boolean) => ({
    ok: isOk,
    checkedAt: new Date(`2026-03-${String(day).padStart(2, '0')}T12:00:00Z`),
  });

  it('is the fraction that succeeded', () => {
    const result = uptime({
      window,
      checks: [check(1, true), check(2, true), check(3, false), check(4, true)],
    });
    expect(result.ratio).toBe(0.75);
    expect(result.failed).toBe(1);
  });

  it('does not count planned maintenance as downtime', () => {
    const result = uptime({
      window,
      checks: [check(1, true), check(2, false), check(3, false), check(4, true)],
      maintenance: [{
        from: new Date('2026-03-02T00:00:00Z'),
        to: new Date('2026-03-03T23:59:00Z'),
      }],
    });

    /*
     * Otherwise every declared deploy costs the customer their SLA number and
     * they stop declaring them — worse for everybody than the figure being
     * slightly kind.
     */
    expect(result.ratio).toBe(1);
    expect(result.excluded).toBe(2);
  });

  it('does not claim 100% for a period the monitor did not exist in', () => {
    const result = uptime({
      window,
      checks: [check(20, true), check(21, true)],
      createdAt: new Date('2026-03-19T00:00:00Z'),
    });

    // "100% over 90 days" under a monitor created yesterday is the most
    // damaging kind of lie a status page can tell.
    expect(result.ratio).toBe(1);
    expect(result.note).toMatch(/from when this monitor was created/i);
  });

  it('ignores checks from before the monitor existed', () => {
    const result = uptime({
      window,
      checks: [check(1, false), check(20, true)],
      createdAt: new Date('2026-03-19T00:00:00Z'),
    });
    expect(result.total).toBe(1);
    expect(result.failed).toBe(0);
  });

  it('is null, not 100%, when there is nothing to measure', () => {
    const result = uptime({ window, checks: [] });
    // No checks is no information. On a chart the two are indistinguishable
    // unless one of them is null.
    expect(result.ratio).toBeNull();
    expect(result.note).toMatch(/no checks/i);
  });

  it('says when the whole period was maintenance', () => {
    const result = uptime({
      window,
      checks: [check(2, false)],
      maintenance: [{ from: window.from, to: window.to }],
    });
    expect(result.ratio).toBeNull();
    expect(result.note).toMatch(/planned maintenance/i);
  });

  it('renders enough decimals to tell 99.9 from 99.95', () => {
    expect(formatUptime(0.999)).toBe('99.900%');
    expect(formatUptime(0.9995)).toBe('99.950%');
    expect(formatUptime(1)).toBe('100.00%');
    expect(formatUptime(null)).toBe('—');
  });
});
