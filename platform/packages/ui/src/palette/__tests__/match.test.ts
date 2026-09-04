import { describe, expect, it } from 'vitest';
import { NO_MATCH, rank, score } from '../match.ts';

describe('palette ranking', () => {
  it('ranks a whole-string prefix above everything else', () => {
    expect(score('Issues', 'iss')).toBeGreaterThan(score('Missing alt text', 'iss'));
  });

  it('finds a word start, so "tools" reaches "Free tools"', () => {
    expect(score('Free tools', 'tool')).toBeGreaterThan(NO_MATCH);
    // and beats a mid-word hit of the same query
    expect(score('Free tools', 'tool')).toBeGreaterThan(score('Retooling', 'tool'));
  });

  it('treats path and punctuation as word boundaries', () => {
    expect(score('audit/sites', 'sites')).toBeGreaterThan(score('crusites', 'sites'));
  });

  it('falls back to subsequence but always below a literal match', () => {
    const sub = score('A Useful Draft', 'aud');
    const literal = score('Audit', 'aud');
    expect(sub).toBeGreaterThan(NO_MATCH);
    expect(literal).toBeGreaterThan(sub);
  });

  it('returns NO_MATCH when the letters are absent or out of order', () => {
    expect(score('Audit', 'zzz')).toBe(NO_MATCH);
    expect(score('Audit', 'tid')).toBe(NO_MATCH);
  });

  it('prefers the shorter of two equally-prefixed labels', () => {
    expect(score('Audits', 'audit')).toBeGreaterThan(score('Audit run history', 'audit'));
  });

  it('an empty query keeps every item in its original order', () => {
    const items = [{ l: 'b' }, { l: 'a' }];
    expect(rank(items, '', (i) => i.l)).toEqual(items);
  });

  it('rank filters out non-matches and orders by score', () => {
    const items = [{ l: 'Missing alt text' }, { l: 'Issues' }, { l: 'Reports' }];
    expect(rank(items, 'iss', (i) => i.l).map((i) => i.l)).toEqual(['Issues', 'Missing alt text']);
  });

  it('is case-insensitive in both directions', () => {
    expect(score('Audit', 'AUD')).toBeGreaterThan(NO_MATCH);
    expect(score('AUDIT', 'aud')).toBeGreaterThan(NO_MATCH);
  });
});
