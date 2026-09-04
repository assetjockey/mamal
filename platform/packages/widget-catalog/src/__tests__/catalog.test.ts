import { describe, expect, it } from 'vitest';
import { z } from 'zod';
import { WIDGET_CATALOG, WIDGET_CATEGORIES, WIDGET_FAMILIES, widgetDef, widgetsIn } from '../index.ts';
import { THEMES, themeVars } from '../themes.ts';
import { fieldsFor } from '../fields.ts';

describe('the widget catalogue', () => {
  it('ships all 44 types the brief enumerates', () => {
    /*
     * 44, not the 41 the brief's prose says.
     *
     * Its headline figure is carried from the source product's marketing, but
     * its own category lists name 11 proof + 12 announce + 9 collect +
     * 4 feedback + 8 engage. The enumerated list is the specification — a
     * customer migrating cares whether *their* widget exists, not what the
     * total is called — so all 44 are here and the count is asserted per
     * category to make a future miscount obvious.
     */
    expect(WIDGET_CATALOG.length).toBe(44);
    const per = Object.fromEntries(
      WIDGET_CATEGORIES.map((c) => [c, widgetsIn(c).length]),
    );
    expect(per).toEqual({ proof: 11, announce: 12, collect: 9, feedback: 4, engage: 8 });
  });

  it('has no duplicate keys', () => {
    const keys = WIDGET_CATALOG.map((w) => w.key);
    expect(new Set(keys).size, `duplicates: ${keys.filter((k, i) => keys.indexOf(k) !== i)}`).toBe(
      keys.length,
    );
  });

  it('covers every category, and every type belongs to a known one', () => {
    for (const c of WIDGET_CATEGORIES) expect(widgetsIn(c).length, c).toBeGreaterThan(0);
    for (const w of WIDGET_CATALOG) expect(WIDGET_CATEGORIES).toContain(w.category);
  });

  it('maps 41 types onto 8 families — the whole point of the design', () => {
    for (const w of WIDGET_CATALOG) expect(WIDGET_FAMILIES).toContain(w.family);
    const used = new Set(WIDGET_CATALOG.map((w) => w.family));
    expect(used.size).toBeLessThanOrEqual(8);
  });

  it('every type parses its own defaults', () => {
    // A default that fails its own schema is a widget that cannot be created.
    for (const w of WIDGET_CATALOG) {
      const parsed = w.settings.safeParse(w.defaults);
      expect(parsed.success, `${w.key}: ${parsed.success ? '' : parsed.error.message}`).toBe(true);
    }
  });

  it('every type is describable to a user', () => {
    for (const w of WIDGET_CATALOG) {
      expect(w.label.length, w.key).toBeGreaterThan(0);
      expect(w.description.length, w.key).toBeGreaterThan(10);
    }
  });

  it('a cookie notice cannot be configured without a decline', () => {
    // Not a style preference: consent with no one-click refusal is not consent,
    // and in most places this ships it is not lawful either. The schema makes
    // it impossible rather than merely discouraged.
    const notice = widgetDef('cookie_notice')!;
    expect(notice.settings.safeParse({ showDecline: false }).success).toBe(false);
    expect(notice.settings.safeParse({ showDecline: true }).success).toBe(true);
  });

  it('only widgets that need conversions declare the need', () => {
    // The runtime withholds the conversion feed from anything that does not
    // declare it — that feed is other customers' data crossing to a browser.
    expect(widgetDef('recent_conversion')!.needs).toContain('conversions');
    expect(widgetDef('cookie_notice')!.needs).not.toContain('conversions');
    expect(widgetDef('whatsapp_chat')!.needs).toEqual([]);
  });

  it('returns undefined for an unknown key rather than guessing', () => {
    expect(widgetDef('does_not_exist')).toBeUndefined();
  });
});

describe('themes', () => {
  it('ships 30, with unique keys', () => {
    expect(THEMES.length).toBe(30);
    expect(new Set(THEMES.map((t) => t.key)).size).toBe(30);
  });

  it('every colour is a real hex value', () => {
    for (const t of THEMES) {
      for (const [field, value] of Object.entries(t)) {
        if (field === 'key' || field === 'label') continue;
        expect(value, `${t.key}.${field}`).toMatch(/^#[0-9a-f]{6}$/i);
      }
    }
  });

  it('resolves to six custom properties', () => {
    const vars = themeVars('berlin');
    expect(Object.keys(vars).sort()).toEqual([
      '--w-accent', '--w-bg', '--w-border', '--w-fg', '--w-muted', '--w-on-accent',
    ]);
  });

  it('falls back to the first theme rather than throwing on an unknown key', () => {
    // A bad theme value must degrade to a rendered widget, never to a blank one.
    expect(themeVars('not_a_theme')['--w-bg']).toBe(themeVars('stockholm')['--w-bg']);
  });

  it('a per-widget accent overrides the theme', () => {
    expect(themeVars('berlin', '#ff0000')['--w-accent']).toBe('#ff0000');
  });
});

describe('generated editor fields', () => {
  it('produces a field for every setting of every type', () => {
    // The guarantee: a form derived from the schema can never offer a field the
    // validator rejects, or omit one it requires. Checked across all 44 rather
    // than a sample, because the long tail is exactly where a hand-written
    // editor would have gaps.
    for (const def of WIDGET_CATALOG) {
      const fields = fieldsFor(def);
      const schemaKeys = Object.keys(
        (def.settings as unknown as { shape: Record<string, unknown> }).shape ?? {},
      );
      expect(fields.map((f) => f.name).sort(), def.key).toEqual(schemaKeys.sort());
    }
  });

  it('classifies the field kinds an editor needs to render', () => {
    const byName = Object.fromEntries(
      fieldsFor(widgetDef('coupon')!).map((f) => [f.name, f]),
    );
    expect(byName.title!.kind).toBe('text');
    expect(byName.body!.kind).toBe('textarea');
    expect(byName.linkUrl!.kind).toBe('url');

    const rc = Object.fromEntries(
      fieldsFor(widgetDef('recent_conversion')!).map((f) => [f.name, f]),
    );
    expect(rc.minimumCount!.kind).toBe('number');
    expect(rc.showAvatar!.kind).toBe('boolean');
    expect(rc.conversionTypes!.kind).toBe('string-list');

    const cd = Object.fromEntries(fieldsFor(widgetDef('countdown')!).map((f) => [f.name, f]));
    expect(cd.onExpiry!.kind).toBe('select');
    expect(cd.onExpiry!.options).toEqual(['hide', 'restart', 'message']);
    expect(cd.endsAt!.kind).toBe('datetime');
  });

  it('marks the fields that accept {{token}} interpolation', () => {
    // The editor tells the user which fields take tokens; getting this wrong
    // means either a missing hint or a promise the renderer will not keep.
    const rc = fieldsFor(widgetDef('recent_conversion')!);
    expect(rc.find((f) => f.name === 'title')!.templated).toBe(true);
    expect(rc.find((f) => f.name === 'windowHours')!.templated).toBe(false);
  });

  it('every non-optional field has a value straight from defaults', () => {
    /*
     * So a widget renders the moment it is created, before anyone edits it.
     *
     * Optional fields are exempt and must be: `countdown.endsAt` has no
     * sensible default — the deadline is the whole point of setting one — and
     * the editor renders it empty rather than inventing a date.
     */
    for (const def of WIDGET_CATALOG) {
      const parsed = def.settings.parse(def.defaults) as Record<string, unknown>;
      const optional = new Set(
        fieldsFor(def).filter((f) => f.default === undefined && !f.required).map((f) => f.name),
      );
      for (const field of fieldsFor(def)) {
        if (field.kind === 'unsupported' || optional.has(field.name)) continue;
        expect(parsed[field.name], `${def.key}.${field.name}`).toBeDefined();
      }
    }
  });

  it('never throws on a schema it cannot describe', () => {
    const odd = { ...widgetDef('informational')!, settings: z.map(z.string(), z.string()) };
    expect(() => fieldsFor(odd as never)).not.toThrow();
  });
});
