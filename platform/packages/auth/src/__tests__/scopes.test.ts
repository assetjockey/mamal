import { describe, expect, it } from 'vitest';
import { hasScope } from '../api-keys.ts';

describe('API key scopes', () => {
  it('grants an exact match', () => {
    expect(hasScope(['audit:sites:read'], 'audit:sites:read')).toBe(true);
  });

  it('refuses a different action on the same resource', () => {
    expect(hasScope(['audit:sites:read'], 'audit:sites:write')).toBe(false);
  });

  it('treats a bare * as full access', () => {
    expect(hasScope(['*'], 'audit:sites:write')).toBe(true);
  });

  it('expands a wildcard segment', () => {
    expect(hasScope(['audit:*:read'], 'audit:issues:read')).toBe(true);
    expect(hasScope(['audit:*:read'], 'audit:issues:write')).toBe(false);
  });

  it('matches per segment, never as a text prefix', () => {
    // The bug this guards: a prefix match would let `audit:*` reach a
    // differently-named tool that merely starts with the same letters.
    expect(hasScope(['audit:*:read'], 'audit_admin:secrets:read')).toBe(false);
    expect(hasScope(['audit:*'], 'audit:sites:read')).toBe(false); // arity differs
  });

  it('refuses when nothing is granted', () => {
    expect(hasScope([], 'audit:sites:read')).toBe(false);
  });

  it('accepts any one of several grants', () => {
    expect(hasScope(['link:links:read', 'audit:sites:read'], 'audit:sites:read')).toBe(true);
  });
});
