import { describe, expect, it } from 'vitest';
import {
  BLOCK_CATALOG, BLOCK_CATEGORIES, BLOCK_FAMILIES, blockDef, blocksIn,
  QR_CATALOG, QR_CATEGORIES, qrDef, qrTypesIn, encodePayload,
  BARCODE_CATALOG, BARCODE_FAMILIES, barcodeDef, barcodesIn,
  validateBarcode, withCheckDigit, gtinCheckDigit,
  BODY_PATTERNS, INNER_EYE_SHAPES, OUTER_EYE_SHAPES, FRAMES, FRAME_FONTS,
  qrStyleSchema, styleWarnings,
} from '../index.ts';

describe('the block catalogue', () => {
  it('ships all 84 blocks the brief enumerates', () => {
    /*
     * 84, not the 82 the brief's prose says — the same headline-vs-enumeration
     * split the widget catalogue has. The gap is entirely in the embed list,
     * where `twitter tweet/video/profile` is a slash-compressed triple: one
     * entry to read, three blocks to build. The enumeration is the
     * specification, so all 84 ship and the count is asserted per category to
     * make a future miscount fail here rather than in a migration.
     */
    expect(BLOCK_CATALOG.length).toBe(84);
    const per = Object.fromEntries(BLOCK_CATEGORIES.map((c) => [c, blocksIn(c).length]));
    expect(per).toEqual({ standard: 34, embed: 36, file: 4, collect: 5, commerce: 5 });
  });

  it('has no duplicate keys', () => {
    const keys = BLOCK_CATALOG.map((b) => b.key);
    expect(new Set(keys).size, `duplicates: ${keys.filter((k, i) => keys.indexOf(k) !== i)}`).toBe(
      keys.length,
    );
  });

  it('maps 84 blocks onto 12 render families — the whole point of the design', () => {
    for (const b of BLOCK_CATALOG) expect(BLOCK_FAMILIES).toContain(b.family);
    expect(new Set(BLOCK_CATALOG.map((b) => b.family)).size).toBeLessThanOrEqual(12);
  });

  it('every block parses its own defaults', () => {
    // A default that fails its own schema is a block that cannot be created.
    for (const b of BLOCK_CATALOG) {
      const parsed = b.settings.safeParse(b.defaults);
      expect(parsed.success, `${b.key}: ${parsed.success ? '' : parsed.error.message}`).toBe(true);
    }
  });

  it('gives every embed block a provider, and nothing else one', () => {
    for (const b of BLOCK_CATALOG) {
      if (b.category === 'embed') expect(b.provider, b.key).toBe(b.key);
      else expect(b.provider, b.key).toBeUndefined();
    }
  });

  it('looks up by key and returns undefined for a stranger', () => {
    expect(blockDef('countdown')?.label).toBe('Countdown');
    expect(blockDef('nope')).toBeUndefined();
  });
});

describe('the QR catalogue', () => {
  it('ships all 35 types the brief enumerates', () => {
    /*
     * 35, against a headline of "34 QR types" — the enumeration simply runs one
     * longer than the count written above it, as it does for blocks and
     * widgets.
     *
     * Three of those names carry a "(static + dynamic)" annotation. That is not
     * two types: it is one type that can be addressed either way, so it is one
     * entry with an `addressing` field. Splitting them would give 38 types and
     * make the customer choose between `email` and `email_dynamic` in a picker,
     * when what they actually want to decide is whether the code stays editable
     * after it is printed.
     */
    expect(QR_CATALOG.length).toBe(35);
    const per = Object.fromEntries(QR_CATEGORIES.map((c) => [c, qrTypesIn(c).length]));
    expect(per).toEqual({ web: 7, business: 7, contact: 3, message: 9, payment: 6, utility: 3 });
  });

  it('has no duplicate keys', () => {
    const keys = QR_CATALOG.map((q) => q.key);
    expect(new Set(keys).size, `duplicates: ${keys.filter((k, i) => keys.indexOf(k) !== i)}`).toBe(
      keys.length,
    );
  });

  it('says of every type whether it can be edited after printing', () => {
    for (const q of QR_CATALOG) {
      expect(['dynamic', 'static', 'either'], q.key).toContain(q.addressing);
    }
    // The ones that physically cannot resolve through a link must say static.
    for (const key of ['wifi', 'vcard', 'call', 'crypto', 'location', 'text', 'epc', 'pix']) {
      expect(qrDef(key)?.addressing, key).toBe('static');
    }
  });

  it('encodes wifi in the format a phone camera reads', () => {
    const out = encodePayload('wifi', { ssid: 'Cafe Mamal', password: 'hunter2', encryption: 'WPA' });
    expect(out).toBe('WIFI:T:WPA;S:Cafe Mamal;P:hunter2;;');
  });

  it('escapes the characters that are structural in a wifi payload', () => {
    // An SSID with a semicolon in it truncates the payload unless escaped.
    const out = encodePayload('wifi', { ssid: 'Bar;Grill', password: 'a:b', encryption: 'WPA' });
    expect(out).toBe('WIFI:T:WPA;S:Bar\\;Grill;P:a\\:b;;');
  });

  it('encodes a vcard that carries only the fields supplied', () => {
    const out = encodePayload('vcard', { firstName: 'Ada', lastName: 'Lovelace', email: 'ada@example.com' })!;
    expect(out).toContain('BEGIN:VCARD');
    expect(out).toContain('FN:Ada Lovelace');
    expect(out).toContain('EMAIL:ada@example.com');
    expect(out).not.toContain('TEL:');
    expect(out.endsWith('END:VCARD')).toBe(true);
  });

  it('builds a mailto with no trailing ? when there is nothing to add', () => {
    expect(encodePayload('email', { to: 'hi@example.com' })).toBe('mailto:hi@example.com');
    expect(encodePayload('email', { to: 'hi@example.com', subject: 'Hello there' })).toBe(
      'mailto:hi@example.com?subject=Hello+there',
    );
  });

  it('strips punctuation from a whatsapp number', () => {
    expect(encodePayload('whatsapp', { phone: '+44 20 7946 0958' })).toBe('https://wa.me/442079460958');
  });

  it('treats a boolean and its string form the same', () => {
    // The API sends JSON `true`; the editor's checkbox sends "true".
    const a = encodePayload('wifi', { ssid: 'Backroom', encryption: 'WPA', hidden: true });
    const b = encodePayload('wifi', { ssid: 'Backroom', encryption: 'WPA', hidden: 'true' });
    expect(a).toBe(b);
    expect(a).toContain('H:true;');
  });

  it('returns null for a dynamic type — the caller supplies the short link', () => {
    expect(encodePayload('dynamic_url', { url: 'https://example.com' })).toBeNull();
    expect(encodePayload('bio_link', {})).toBeNull();
  });

  it('returns null rather than throwing on a half-filled payload', () => {
    // The editor encodes on every keystroke; an incomplete form is normal.
    expect(encodePayload('event', { title: 'Launch', start: 'not-a-date' })).toBeNull();
    expect(encodePayload('unknown_type', {})).toBeNull();
    expect(encodePayload('text', {})).toBeNull();
  });
});

describe('the barcode catalogue', () => {
  it('ships all 29 symbologies', () => {
    expect(BARCODE_CATALOG.length).toBe(29);
    const per = Object.fromEntries(BARCODE_FAMILIES.map((f) => [f, barcodesIn(f).length]));
    expect(per).toEqual({ retail: 9, industrial: 12, postal: 5, matrix: 3 });
  });

  it('has no duplicate keys, and every example validates', () => {
    const keys = BARCODE_CATALOG.map((b) => b.key);
    expect(new Set(keys).size).toBe(keys.length);
    for (const b of BARCODE_CATALOG) {
      const r = b.validate(b.example);
      expect(r.ok, `${b.key} example ${b.example}: ${r.ok ? '' : r.reason}`).toBe(true);
    }
  });

  it('computes the GS1 check digit with the right weight parity', () => {
    // Weights alternate 3,1 from the right of the payload. Get the parity
    // backwards and the answer is right about half the time.
    expect(gtinCheckDigit('978030640615')).toBe(7);   // ISBN 9780306406157
    expect(gtinCheckDigit('03600029145')).toBe(2);    // UPC-A 036000291452
    expect(gtinCheckDigit('9638507')).toBe(4);        // EAN-8 96385074
    expect(gtinCheckDigit('0001234567890')).toBe(5);  // ITF-14 00012345678905
  });

  it('rejects an EAN-13 whose check digit is wrong, and says what it should be', () => {
    const r = validateBarcode('ean13', '9780306406158');
    expect(r.ok).toBe(false);
    expect(r.ok === false && r.reason).toContain('should be 7');
  });

  it('accepts a GTIN one digit short and completes it', () => {
    expect(validateBarcode('ean13', '978030640615').ok).toBe(true);
    expect(withCheckDigit('ean13', '978030640615')).toBe('9780306406157');
    // Already complete, or not completable — returned untouched either way.
    expect(withCheckDigit('ean13', '9780306406157')).toBe('9780306406157');
    expect(withCheckDigit('code128', 'MAMAL')).toBe('MAMAL');
  });

  it('enforces the prefixes that make a book a book', () => {
    expect(validateBarcode('isbn', '9783161484100').ok).toBe(true);
    const r = validateBarcode('isbn', '4006381333931');
    expect(r.ok === false && r.reason).toContain('978 or 979');
  });

  it('holds Interleaved 2 of 5 to an even number of digits', () => {
    expect(validateBarcode('interleaved2of5', '1234567890').ok).toBe(true);
    const r = validateBarcode('interleaved2of5', '123456789');
    expect(r.ok === false && r.reason).toContain('even');
  });

  it('keeps Code 39 to the characters it can actually encode', () => {
    expect(validateBarcode('code39', 'MAMAL-001').ok).toBe(true);
    expect(validateBarcode('code39', 'mamal-001').ok).toBe(false);
  });

  it('bounds a pharmacode to its legal numeric range', () => {
    expect(validateBarcode('pharmacode', '117480').ok).toBe(true);
    expect(validateBarcode('pharmacode', '2').ok).toBe(false);
    expect(validateBarcode('pharmacode', '131071').ok).toBe(false);
  });

  it('fails closed on an unknown symbology', () => {
    // Failing open would let a typo in the key disable validation entirely.
    const r = validateBarcode('code1234', 'anything');
    expect(r.ok).toBe(false);
    expect(r.ok === false && r.reason).toContain('Unknown symbology');
  });

  it('looks up by key', () => {
    expect(barcodeDef('itf14')?.label).toBe('ITF-14');
    expect(barcodeDef('nope')).toBeUndefined();
  });
});

describe('QR styling', () => {
  it('offers the style counts the brief promises', () => {
    expect(BODY_PATTERNS.length).toBe(25);
    expect(INNER_EYE_SHAPES.length).toBe(22);
    expect(OUTER_EYE_SHAPES.length).toBe(10);
    expect(FRAMES.length).toBe(20);
    expect(FRAME_FONTS.length).toBe(16);
  });

  it('parses an empty object into a scannable default', () => {
    const style = qrStyleSchema.parse({});
    expect(style.body).toBe('square');
    expect(style.errorCorrection).toBe('M');
    expect(style.margin).toBe(4);
    expect(styleWarnings(style)).toEqual([]);
  });

  it('refuses a logo large enough to destroy the code', () => {
    expect(qrStyleSchema.safeParse({ logoScale: 0.5 }).success).toBe(false);
    expect(qrStyleSchema.safeParse({ logoScale: 0.3 }).success).toBe(true);
  });

  it('warns when a logo is added without raising error correction', () => {
    const style = qrStyleSchema.parse({
      logoAssetId: '018f4a2c-0000-7000-8000-000000000000',
      errorCorrection: 'M',
    });
    expect(styleWarnings(style).join(' ')).toContain('Q or H');
  });

  it('warns about a foreground and background too close to scan', () => {
    const style = qrStyleSchema.parse({
      foreground: { kind: 'solid', color: '#555555' },
      background: '#666666',
    });
    expect(styleWarnings(style).join(' ')).toContain('too close in tone');
  });

  it('reads the gradient start colour when checking contrast', () => {
    // A gradient has no single colour; the start is what the eyes are drawn in.
    const style = qrStyleSchema.parse({
      foreground: { kind: 'gradient', from: '#eeeeee', to: '#000000' },
      background: '#ffffff',
    });
    expect(styleWarnings(style).join(' ')).toContain('too close in tone');
  });

  it('rejects a colour that is not a hex value', () => {
    expect(qrStyleSchema.safeParse({ background: 'rebeccapurple' }).success).toBe(false);
    expect(qrStyleSchema.safeParse({ background: 'transparent' }).success).toBe(true);
  });
});
