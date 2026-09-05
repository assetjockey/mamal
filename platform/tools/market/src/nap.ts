/**
 * Name, address, phone — and whether they agree across directories.
 *
 * "NAP consistency" is the oldest advice in local SEO and the most tediously
 * hard thing to check, because almost every difference is cosmetic. `123 High
 * Street, Suite 4` and `123 High St #4` are the same place; `123 High Street`
 * and `132 High Street` are not, and differ by less. A checker that flags the
 * first wastes somebody's afternoon; one that misses the second is worthless.
 *
 * So the comparison normalises hard — abbreviations, punctuation, casing,
 * accents, phone formatting — and then compares what is left *exactly*. It
 * reports what differs rather than a similarity score, because "84% match" is
 * not something anybody can act on.
 *
 * Everything here is pure. The directory clients that supply the other side of
 * each comparison are separate and land per directory.
 */

export type Nap = { name: string; address: string; phone: string };

export type Difference = {
  field: 'name' | 'address' | 'phone';
  ours: string;
  theirs: string;
  /** `differs` needs fixing; `formatting` is cosmetic and only shown on request. */
  kind: 'differs' | 'formatting' | 'missing';
  note: string;
};

/* --------------------------------------------------------------- names */

const BUSINESS_SUFFIXES = [
  'ltd', 'limited', 'llc', 'inc', 'incorporated', 'plc', 'llp', 'lp',
  'gmbh', 'bv', 'nv', 'sarl', 'sa', 'ag', 'pty', 'co',
];

/**
 * A business name, reduced to what makes it that business.
 *
 * `Acme Widgets Ltd.` and `Acme Widgets` are the same listing; the suffix is a
 * legal fact rather than a naming difference, and directories are wildly
 * inconsistent about it. `&` and `and` likewise.
 */
export function normaliseName(input: string): string {
  let value = foldAccents(input)
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/[.,'’"()]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  // Only a *trailing* suffix: "Co-op Widgets" must keep its "co".
  for (const suffix of BUSINESS_SUFFIXES) {
    const pattern = new RegExp(`\\s${suffix}$`);
    if (pattern.test(value)) {
      value = value.replace(pattern, '');
      break;
    }
  }
  return value.trim();
}

/* ------------------------------------------------------------ addresses */

/**
 * Street-type abbreviations, both directions.
 *
 * Directories disagree constantly, and every one of these disagreements is
 * cosmetic. Expanding to the long form rather than abbreviating, because the
 * long forms are unambiguous — `st` is both "street" and "saint".
 */
const STREET_TYPES: Record<string, string> = {
  st: 'street', str: 'street', rd: 'road', ave: 'avenue', av: 'avenue',
  blvd: 'boulevard', dr: 'drive', ln: 'lane', ct: 'court', pl: 'place',
  sq: 'square', ter: 'terrace', cres: 'crescent', cl: 'close', gdns: 'gardens',
  pk: 'park', hwy: 'highway', pkwy: 'parkway', cir: 'circle', trl: 'trail',
};

/**
 * Subdivision markers.
 *
 * `Suite 4`, `#4`, `Unit 4` and `Apt 4` all collapse to one token, because
 * directories genuinely swap them for the same physical door and treating them
 * as different would flag every listing a customer has.
 *
 * `floor` and `room` deliberately do *not* collapse into it: "Floor 4" and
 * "Unit 4" are different places in the same building, and quietly merging them
 * would hide a real difference rather than a cosmetic one.
 */
const UNIT_TYPES: Record<string, string> = {
  ste: 'unit', suite: 'unit', apt: 'unit', apartment: 'unit', unit: 'unit', no: 'unit',
  fl: 'floor', floor: 'floor', rm: 'room', room: 'room',
};

const DIRECTIONS: Record<string, string> = {
  n: 'north', s: 'south', e: 'east', w: 'west',
  ne: 'northeast', nw: 'northwest', se: 'southeast', sw: 'southwest',
};

/**
 * An address, reduced to the parts that identify a building.
 *
 * The house number is preserved exactly and everything around it is normalised:
 * `123` and `132` must never collapse together, which is why nothing here does
 * fuzzy matching on digits.
 */
export function normaliseAddress(input: string): string {
  const words = foldAccents(input)
    .toLowerCase()
    // `#4` is a unit marker and needs to survive punctuation stripping as one.
    .replace(/#\s*(\w)/g, ' unit $1 ')
    .replace(/[.,'’"()]/g, ' ')
    .replace(/\s*-\s*/g, ' ')
    .split(/\s+/)
    .filter(Boolean);

  const out: string[] = [];
  for (const word of words) {
    // Ordinals: "1st" and "first" are the same street.
    const ordinal = word.match(/^(\d+)(st|nd|rd|th)$/);
    if (ordinal) {
      out.push(ordinal[1]!);
      continue;
    }
    out.push(STREET_TYPES[word] ?? UNIT_TYPES[word] ?? DIRECTIONS[word] ?? word);
  }

  return out.join(' ').trim();
}

/* --------------------------------------------------------------- phones */

/**
 * A phone number, reduced to its digits, in international form where possible.
 *
 * `+44 20 7946 0958`, `020 7946 0958` and `(020) 7946-0958` are one number
 * written three ways, and every directory picks a different one. A leading zero
 * is a national trunk prefix and is dropped when a country code is present.
 *
 * `defaultCountry` is needed because a bare `020 7946 0958` cannot be compared
 * with `+44 20 7946 0958` without knowing where the business is.
 */
export function normalisePhone(input: string, defaultCountry?: string): string {
  const trimmed = input.trim();
  const hasPlus = trimmed.startsWith('+') || trimmed.startsWith('00');

  let digits = trimmed.replace(/\D/g, '');
  if (trimmed.startsWith('00')) digits = digits.slice(2);

  if (!hasPlus && defaultCountry) {
    // A national number: drop the trunk zero, prepend the country code.
    const national = digits.replace(/^0+/, '');
    digits = `${defaultCountry.replace(/\D/g, '')}${national}`;
  }

  return digits;
}

/* ------------------------------------------------------------ comparing */

/**
 * What differs between our listing and theirs.
 *
 * Returns the differences rather than a score. `formatting` means the values
 * normalise to the same thing and only look different — worth showing when
 * somebody asks, never worth an alert, because chasing those is the busywork
 * that makes people abandon this whole exercise.
 */
export function compareNap(
  ours: Nap,
  theirs: Partial<Nap>,
  opts: { defaultCountry?: string } = {},
): Difference[] {
  const differences: Difference[] = [];

  const check = (
    field: Difference['field'],
    ourRaw: string,
    theirRaw: string | undefined,
    normalise: (v: string) => string,
    note: string,
  ) => {
    if (theirRaw === undefined || theirRaw.trim() === '') {
      differences.push({
        field,
        ours: ourRaw,
        theirs: '',
        kind: 'missing',
        // A directory holding no phone number is a different problem from one
        // holding the wrong phone number, and the fix is different too.
        note: `They have no ${field} on file.`,
      });
      return;
    }

    const a = normalise(ourRaw);
    const b = normalise(theirRaw);
    if (a === b) {
      if (ourRaw.trim() !== theirRaw.trim()) {
        differences.push({
          field, ours: ourRaw, theirs: theirRaw, kind: 'formatting',
          note: 'Written differently, but the same.',
        });
      }
      return;
    }

    differences.push({ field, ours: ourRaw, theirs: theirRaw, kind: 'differs', note });
  };

  check('name', ours.name, theirs.name, normaliseName, 'A different business name.');
  check(
    'address',
    ours.address,
    theirs.address,
    normaliseAddress,
    'A different address — check the number and the unit first.',
  );
  check(
    'phone',
    ours.phone,
    theirs.phone,
    (v) => normalisePhone(v, opts.defaultCountry),
    'A different phone number.',
  );

  return differences;
}

/** Only the differences somebody needs to act on. */
export function actionable(differences: Difference[]): Difference[] {
  return differences.filter((d) => d.kind !== 'formatting');
}

function foldAccents(input: string): string {
  // `Café` and `Cafe` are the same business to every directory except a string
  // comparison.
  return input.normalize('NFD').replace(/[̀-ͯ]/g, '');
}
