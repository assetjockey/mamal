/**
 * The 29 barcode symbologies.
 *
 * Every one of these carries a validator, and that is the whole point of the
 * file. A barcode is printed onto physical stock in quantities of ten thousand;
 * the failure mode is not an error message, it is a pallet of labels that no
 * scanner in the receiving warehouse will read. So the editor refuses a value
 * the symbology cannot encode *before* the render, and offers to compute a
 * check digit rather than silently accepting a wrong one.
 *
 * Rendering is somebody else's job (`worker-media` rasterises), which is why
 * nothing here draws bars. This module answers two questions: is this value
 * legal for this symbology, and what is its check digit.
 */

export const BARCODE_FAMILIES = ['retail', 'industrial', 'postal', 'matrix'] as const;
export type BarcodeFamily = (typeof BARCODE_FAMILIES)[number];

export type Validity = { ok: true } | { ok: false; reason: string };

export type BarcodeDef = {
  key: string;
  family: BarcodeFamily;
  label: string;
  description: string;
  /** What a human should type. Shown as the field's placeholder. */
  example: string;
  /** True when a check digit is part of the encoded value and we can compute it. */
  checkDigit: boolean;
  validate: (value: string) => Validity;
};

/* -------------------------------------------------------------- validators */

const ok: Validity = { ok: true };
const no = (reason: string): Validity => ({ ok: false, reason });

const digitsOnly = (v: string) => /^[0-9]+$/.test(v);

/**
 * The GS1 modulo-10 check digit, shared by every GTIN-family symbology.
 *
 * `payload` is the value *without* its check digit. Weights alternate 3 and 1
 * from the right, which is why the parity depends on length rather than being
 * fixed — getting that backwards produces a digit that is right half the time,
 * which is the worst possible kind of wrong.
 */
export function gtinCheckDigit(payload: string): number {
  let sum = 0;
  for (let i = payload.length - 1, weight = 3; i >= 0; i--, weight = weight === 3 ? 1 : 3) {
    sum += Number(payload[i]) * weight;
  }
  return (10 - (sum % 10)) % 10;
}

/**
 * Accepts either the full length (check digit present and correct) or one digit
 * short (we will compute it). Both are things a customer legitimately types:
 * the short form comes off a supplier sheet, the long form off an existing pack.
 */
function gtin(length: number) {
  return (value: string): Validity => {
    const v = value.trim();
    if (!digitsOnly(v)) return no('Digits only.');
    if (v.length === length - 1) return ok;
    if (v.length !== length) return no(`Needs ${length - 1} or ${length} digits, not ${v.length}.`);
    const expected = gtinCheckDigit(v.slice(0, -1));
    if (Number(v[v.length - 1]) !== expected) {
      return no(`Check digit should be ${expected}, not ${v[v.length - 1]}.`);
    }
    return ok;
  };
}

function exactDigits(...lengths: number[]) {
  return (value: string): Validity => {
    const v = value.trim();
    if (!digitsOnly(v)) return no('Digits only.');
    if (!lengths.includes(v.length)) {
      return no(`Needs ${lengths.join(' or ')} digits, not ${v.length}.`);
    }
    return ok;
  };
}

function charset(re: RegExp, describe: string, max?: number) {
  return (value: string): Validity => {
    const v = value.trim();
    if (v.length === 0) return no('Cannot be empty.');
    if (max && v.length > max) return no(`At most ${max} characters.`);
    if (!re.test(v)) return no(describe);
    return ok;
  };
}

const CODE39_CHARS = /^[0-9A-Z\-. $/+%]+$/;
const CODE93_CHARS = CODE39_CHARS;
/* Code 128 encodes the whole printable ASCII range plus the control codes. */
const ASCII = /^[\x00-\x7f]+$/;
const CODABAR = /^[A-D][0-9\-$:/.+]*[A-D]$/i;

const def = (d: BarcodeDef): BarcodeDef => d;

/* ------------------------------------------------------------------ retail */

const RETAIL: BarcodeDef[] = [
  def({
    key: 'ean13', family: 'retail', label: 'EAN-13',
    description: 'The global retail standard outside North America.',
    example: '9780306406157', checkDigit: true, validate: gtin(13),
  }),
  def({
    key: 'ean8', family: 'retail', label: 'EAN-8',
    description: 'The short form, for packs too small for an EAN-13.',
    example: '96385074', checkDigit: true, validate: gtin(8),
  }),
  def({
    key: 'upca', family: 'retail', label: 'UPC-A',
    description: 'North American retail. An EAN-13 with a leading zero.',
    example: '036000291452', checkDigit: true, validate: gtin(12),
  }),
  def({
    key: 'upce', family: 'retail', label: 'UPC-E',
    description: 'A zero-suppressed UPC-A, for very small packaging.',
    example: '04252614', checkDigit: true, validate: exactDigits(6, 7, 8),
  }),
  def({
    key: 'isbn', family: 'retail', label: 'ISBN',
    description: 'Books. An EAN-13 in the 978 or 979 prefix range.',
    example: '9783161484100', checkDigit: true,
    validate: (v) => {
      const r = gtin(13)(v);
      if (!r.ok) return r;
      return /^97[89]/.test(v.trim()) ? ok : no('An ISBN starts 978 or 979.');
    },
  }),
  def({
    key: 'ismn', family: 'retail', label: 'ISMN',
    description: 'Printed music. An EAN-13 in the 9790 range.',
    example: '9790260000438', checkDigit: true,
    validate: (v) => {
      const r = gtin(13)(v);
      if (!r.ok) return r;
      return v.trim().startsWith('9790') ? ok : no('An ISMN starts 9790.');
    },
  }),
  def({
    key: 'issn', family: 'retail', label: 'ISSN',
    description: 'Periodicals. Either the 8-digit form or its 977 EAN-13.',
    example: '9771234567003', checkDigit: true,
    validate: (v) => {
      const t = v.trim().replace(/-/g, '');
      if (t.length === 8) return /^[0-9]{7}[0-9X]$/i.test(t) ? ok : no('Eight characters, last may be X.');
      const r = gtin(13)(t);
      if (!r.ok) return r;
      return t.startsWith('977') ? ok : no('An ISSN barcode starts 977.');
    },
  }),
  def({
    key: 'ean5', family: 'retail', label: 'EAN-5 add-on',
    description: 'The price supplement printed beside a book barcode.',
    example: '52495', checkDigit: false, validate: exactDigits(5),
  }),
  def({
    key: 'ean2', family: 'retail', label: 'EAN-2 add-on',
    description: 'The issue-number supplement on magazines.',
    example: '12', checkDigit: false, validate: exactDigits(2),
  }),
];

/* -------------------------------------------------------------- industrial */

const INDUSTRIAL: BarcodeDef[] = [
  def({
    key: 'code39', family: 'industrial', label: 'Code 39',
    description: 'The oldest alphanumeric symbology. Uppercase and digits only.',
    example: 'MAMAL-001', checkDigit: false,
    validate: charset(CODE39_CHARS, 'Uppercase letters, digits and - . $ / + % only.'),
  }),
  def({
    key: 'code39ext', family: 'industrial', label: 'Code 39 Extended',
    description: 'Code 39 with the full ASCII set, encoded as character pairs.',
    example: 'Mamal/001', checkDigit: false,
    validate: charset(ASCII, 'ASCII characters only.'),
  }),
  def({
    key: 'code93', family: 'industrial', label: 'Code 93',
    description: 'Denser than Code 39, with two built-in check characters.',
    example: 'MAMAL93', checkDigit: true,
    validate: charset(CODE93_CHARS, 'Uppercase letters, digits and - . $ / + % only.'),
  }),
  def({
    key: 'code128', family: 'industrial', label: 'Code 128',
    description: 'The general-purpose default: full ASCII, compact, ubiquitous.',
    example: 'MAMAL-2026-0001', checkDigit: true,
    validate: charset(ASCII, 'ASCII characters only.'),
  }),
  def({
    key: 'gs1_128', family: 'industrial', label: 'GS1-128',
    description: 'Code 128 carrying GS1 application identifiers in parentheses.',
    example: '(01)09501101020917(10)LOT42', checkDigit: true,
    validate: (v) => {
      const t = v.trim();
      if (!t.startsWith('(')) return no('Starts with an application identifier, e.g. (01).');
      if (!/^(\([0-9]{2,4}\)[^()]+)+$/.test(t)) return no('Each segment is (AI) followed by its value.');
      return ok;
    },
  }),
  def({
    key: 'codabar', family: 'industrial', label: 'Codabar',
    description: 'Blood banks, libraries and couriers. Starts and ends A–D.',
    example: 'A12345B', checkDigit: false,
    validate: charset(CODABAR, 'Starts and ends with A–D; digits and - $ : / . + between.'),
  }),
  def({
    key: 'itf14', family: 'industrial', label: 'ITF-14',
    description: 'The shipping-carton code. Always 14 digits.',
    example: '00012345678905', checkDigit: true, validate: gtin(14),
  }),
  def({
    key: 'interleaved2of5', family: 'industrial', label: 'Interleaved 2 of 5',
    description: 'Numeric only, and always an even number of digits.',
    example: '1234567890', checkDigit: false,
    validate: (v) => {
      const t = v.trim();
      if (!digitsOnly(t)) return no('Digits only.');
      return t.length % 2 === 0 ? ok : no('Needs an even number of digits.');
    },
  }),
  def({
    key: 'code11', family: 'industrial', label: 'Code 11',
    description: 'Telecoms equipment labelling. Digits and dashes.',
    example: '912-345678', checkDigit: true,
    validate: charset(/^[0-9\-]+$/, 'Digits and dashes only.'),
  }),
  def({
    key: 'msi', family: 'industrial', label: 'MSI Plessey',
    description: 'Inventory and shelf labels in retail warehouses.',
    example: '1234567', checkDigit: true,
    validate: charset(/^[0-9]+$/, 'Digits only.'),
  }),
  def({
    key: 'pharmacode', family: 'industrial', label: 'Pharmacode',
    description: 'Pharmaceutical packaging control. A number from 3 to 131070.',
    example: '117480', checkDigit: false,
    validate: (v) => {
      const t = v.trim();
      if (!digitsOnly(t)) return no('Digits only.');
      const n = Number(t);
      return n >= 3 && n <= 131070 ? ok : no('Must be between 3 and 131070.');
    },
  }),
  def({
    key: 'telepen', family: 'industrial', label: 'Telepen',
    description: 'UK libraries and academia. Full ASCII, fixed density.',
    example: 'MAMAL123', checkDigit: true,
    validate: charset(ASCII, 'ASCII characters only.'),
  }),
];

/* ------------------------------------------------------------------ postal */

const POSTAL: BarcodeDef[] = [
  def({
    key: 'postnet', family: 'postal', label: 'POSTNET',
    description: 'US ZIP routing. 5, 9 or 11 digits.',
    example: '555551237', checkDigit: true, validate: exactDigits(5, 9, 11),
  }),
  def({
    key: 'planet', family: 'postal', label: 'PLANET',
    description: 'US mail-tracking counterpart to POSTNET.',
    example: '12345678901', checkDigit: true, validate: exactDigits(11, 13),
  }),
  def({
    key: 'royal_mail', family: 'postal', label: 'Royal Mail 4-State',
    description: 'UK RM4SCC. Uppercase letters and digits.',
    example: 'SN34RD1A', checkDigit: true,
    validate: charset(/^[0-9A-Z]+$/, 'Uppercase letters and digits only.', 12),
  }),
  def({
    key: 'auspost', family: 'postal', label: 'Australia Post 4-State',
    description: 'A two-digit format code followed by the sorting data.',
    example: '5956439111ABA9', checkDigit: true,
    validate: (v) => {
      const t = v.trim();
      if (t.length < 8) return no('At least 8 characters, starting with a 2-digit format code.');
      if (!/^[0-9]{2}/.test(t)) return no('Starts with a 2-digit format code.');
      return /^[0-9A-Za-z ]+$/.test(t) ? ok : no('Letters, digits and spaces only.');
    },
  }),
  def({
    key: 'kix', family: 'postal', label: 'KIX',
    description: 'Dutch postal code. Uppercase letters and digits, up to 11.',
    example: '1231FZ13XHS', checkDigit: false,
    validate: charset(/^[0-9A-Z]+$/, 'Uppercase letters and digits only.', 11),
  }),
];

/* ------------------------------------------------------------------ matrix */

const MATRIX: BarcodeDef[] = [
  def({
    key: 'datamatrix', family: 'matrix', label: 'Data Matrix',
    description: 'Two-dimensional, readable at a few millimetres square.',
    example: 'https://mamal.app/x/ab12', checkDigit: true,
    validate: charset(/[\s\S]+/, 'Cannot be empty.', 2335),
  }),
  def({
    key: 'pdf417', family: 'matrix', label: 'PDF417',
    description: 'Stacked linear. Driving licences and boarding passes.',
    example: 'MAMAL|2026|0001', checkDigit: true,
    validate: charset(/[\s\S]+/, 'Cannot be empty.', 1850),
  }),
  def({
    key: 'aztec', family: 'matrix', label: 'Aztec',
    description: 'Two-dimensional with no quiet zone. Rail and event tickets.',
    example: 'MAMAL-TICKET-0001', checkDigit: true,
    validate: charset(/[\s\S]+/, 'Cannot be empty.', 3067),
  }),
];

export const BARCODE_CATALOG: BarcodeDef[] = [...RETAIL, ...INDUSTRIAL, ...POSTAL, ...MATRIX];

const BY_KEY = new Map(BARCODE_CATALOG.map((b) => [b.key, b]));

export function barcodeDef(key: string): BarcodeDef | undefined {
  return BY_KEY.get(key);
}

export function barcodesIn(family: string): BarcodeDef[] {
  return BARCODE_CATALOG.filter((b) => b.family === family);
}

/**
 * Validates a value against a symbology.
 *
 * An unknown symbology is a failure, not a pass. Failing open here would mean a
 * typo in the key silently disables validation for everything encoded under it.
 */
export function validateBarcode(key: string, value: string): Validity {
  const def = barcodeDef(key);
  if (!def) return no(`Unknown symbology: ${key}`);
  return def.validate(value);
}

/**
 * Completes a GTIN-family value by appending its check digit.
 *
 * Returns the value unchanged when the symbology has no computable check digit
 * or the value is already complete — so a caller can run it unconditionally.
 */
export function withCheckDigit(key: string, value: string): string {
  const lengths: Record<string, number> = {
    ean13: 13, ean8: 8, upca: 12, itf14: 14, isbn: 13, ismn: 13,
  };
  const target = lengths[key];
  const v = value.trim();
  if (!target || !digitsOnly(v) || v.length !== target - 1) return value;
  return v + String(gtinCheckDigit(v));
}
