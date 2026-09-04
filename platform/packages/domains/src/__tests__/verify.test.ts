import { describe, expect, it } from 'vitest';
import { verifyDomain, type Resolver } from '../verify.ts';

const TOKEN = 'mamal-verify-abc123';
const TARGET = 'cname.mamal.app';

function resolver(answers: {
  txt?: string[][];
  cname?: string[];
  a?: string[];
  fail?: ('txt' | 'cname' | 'a')[];
}): Resolver {
  const boom = (which: string) => () => Promise.reject(new Error(`ENOTFOUND ${which}`));
  return {
    resolveTxt: answers.fail?.includes('txt') ? boom('txt') : async () => answers.txt ?? [],
    resolveCname: answers.fail?.includes('cname') ? boom('cname') : async () => answers.cname ?? [],
    resolve4: answers.fail?.includes('a') ? boom('a') : async () => answers.a ?? [],
  };
}

const check = (answers: Parameters<typeof resolver>[0], extra: { addresses?: string[] } = {}) =>
  verifyDomain({
    host: 'links.example.com',
    token: TOKEN,
    target: TARGET,
    resolver: resolver(answers),
    ...extra,
  });

describe('domain verification', () => {
  it('is done when both the token and the CNAME are in place', async () => {
    const result = await check({ txt: [[TOKEN]], cname: [TARGET] });
    expect(result).toMatchObject({ owned: true, routed: true, nextStep: null });
  });

  it('treats ownership and routing as separate facts', async () => {
    // Proved but not pointed: DNS is still propagating, or they added the TXT
    // first. Telling them "pending" without saying which half is missing is how
    // "I did everything and it still says pending" happens.
    const proved = await check({ txt: [[TOKEN]] });
    expect(proved).toMatchObject({ owned: true, routed: false });
    expect(proved.nextStep).toMatch(/point links\.example\.com at cname\.mamal\.app/i);

    // Pointed but not proved: somebody CNAME'd at us hoping to claim a
    // hostname. This must never verify.
    const pointed = await check({ cname: [TARGET] });
    expect(pointed).toMatchObject({ owned: false, routed: true });
    expect(pointed.nextStep).toMatch(/TXT record/i);
  });

  it('reads a TXT value that the provider split into chunks', async () => {
    // Anything over 255 bytes arrives in pieces, and some providers split
    // shorter values too. Comparing the first chunk would fail on those.
    const result = await check({ txt: [['mamal-verify-', 'abc123']], cname: [TARGET] });
    expect(result.owned).toBe(true);
  });

  it('ignores trailing dots and case in the CNAME answer', async () => {
    // A resolver may return the fully-qualified form. That is not a mismatch.
    expect((await check({ txt: [[TOKEN]], cname: ['CNAME.Mamal.App.'] })).routed).toBe(true);
  });

  it('accepts an A record for an apex domain', async () => {
    // An apex cannot carry a CNAME, so a pointed apex proves itself by address.
    const result = await check(
      { txt: [[TOKEN]], a: ['203.0.113.10'] },
      { addresses: ['203.0.113.10'] },
    );
    expect(result.routed).toBe(true);
  });

  it('treats a missing record as absent, not as an error', async () => {
    // NXDOMAIN is the normal state for the first ninety seconds. Throwing here
    // would turn "not yet" into "failed" on every newly-added domain.
    const result = await check({ fail: ['txt', 'cname', 'a'] });
    expect(result).toMatchObject({ owned: false, routed: false });
    expect(result.nextStep).toMatch(/Add a TXT record/);
  });

  it('refuses somebody else’s token', async () => {
    const result = await check({ txt: [['mamal-verify-somebody-else']], cname: [TARGET] });
    expect(result.owned).toBe(false);
  });

  it('reports everything it saw, for the runbook', async () => {
    const result = await check({ txt: [['other']], cname: ['elsewhere.example.'], a: ['198.51.100.1'] });
    expect(result.found).toEqual({
      txt: ['other'],
      cname: ['elsewhere.example.'],
      a: ['198.51.100.1'],
    });
  });
});
