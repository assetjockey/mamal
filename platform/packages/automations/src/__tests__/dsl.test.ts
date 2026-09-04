import { describe, expect, it } from 'vitest';
import { interpolate, parseDefinition, parseDuration, readPath } from '../dsl.ts';
import { AUTOMATION_TEMPLATES } from '@mamal/db';

describe('readPath', () => {
  const o = { data: { a: { b: 1 } }, list: [1, 2] };
  it('reads nested values', () => expect(readPath(o, 'data.a.b')).toBe(1));
  it('returns undefined rather than throwing', () => {
    expect(readPath(o, 'data.missing.deep')).toBeUndefined();
    expect(readPath(null, 'a')).toBeUndefined();
  });
});

describe('interpolate', () => {
  const scope = { subject: 'urn:mamal:core:site:1', data: { count: 42, tags: ['a', 'b'] } };

  it('preserves the raw type for a lone placeholder', () => {
    // If this stringified, `quantity: 42` would arrive as "42" and every
    // numeric comparison downstream would silently misbehave.
    expect(interpolate('{{data.count}}', scope)).toBe(42);
    expect(interpolate('{{data.tags}}', scope)).toEqual(['a', 'b']);
  });

  it('stringifies when embedded in text', () => {
    expect(interpolate('found {{data.count}} issues', scope)).toBe('found 42 issues');
  });

  it('renders a missing path as empty rather than "undefined"', () => {
    expect(interpolate('x={{data.nope}}', scope)).toBe('x=');
  });

  it('walks nested objects and arrays', () => {
    expect(interpolate({ a: ['{{subject}}'], b: { c: '{{data.count}}' } }, scope)).toEqual({
      a: ['urn:mamal:core:site:1'],
      b: { c: 42 },
    });
  });
});

describe('parseDuration', () => {
  it('parses the supported units', () => {
    expect(parseDuration('30s')).toBe(30_000);
    expect(parseDuration('15m')).toBe(900_000);
    expect(parseDuration('2h')).toBe(7_200_000);
    expect(parseDuration('7d')).toBe(604_800_000);
  });
  it('rejects nonsense', () => expect(() => parseDuration('soon')).toThrow(/invalid duration/));
});

describe('shipped templates', () => {
  it('all parse against the DSL schema', () => {
    for (const t of AUTOMATION_TEMPLATES) {
      expect(() => parseDefinition(t.definition), `${t.key} failed to parse`).not.toThrow();
    }
  });

  it('every template declares the tools it touches', () => {
    for (const t of AUTOMATION_TEMPLATES) {
      expect(t.requiredTools.length, `${t.key} declares no tools`).toBeGreaterThan(0);
    }
  });

  it('commands are namespaced to a declared tool', () => {
    for (const t of AUTOMATION_TEMPLATES) {
      const def = parseDefinition(t.definition);
      for (const a of def.actions) {
        if (a.type !== 'command' || !a.name) continue;
        const owner = a.name.split('.')[0]!;
        expect(
          t.requiredTools,
          `${t.key} calls ${a.name} but does not require "${owner}"`,
        ).toContain(owner);
      }
    }
  });

  it('covers cross-tool flows, not just single-tool ones', () => {
    const crossTool = AUTOMATION_TEMPLATES.filter((t) => t.requiredTools.length > 1);
    expect(crossTool.length).toBeGreaterThanOrEqual(7);
  });
});
