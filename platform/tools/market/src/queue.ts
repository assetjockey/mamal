/**
 * Queue slots: "post this at the next good time" rather than "post this at
 * 14:32 because that is when I clicked".
 *
 * An account has a weekly grid of slots — Tuesday 09:00, Tuesday 17:00,
 * Thursday 09:00 — and queued posts fill them in order. It is the feature that
 * makes a scheduler feel like a plan instead of a list of alarms.
 *
 * **The grid is in the account's timezone, and the answer is an instant.** That
 * is the whole difficulty. "Tuesday 09:00 in Europe/London" is a different UTC
 * moment in January and in July, and a naive implementation that adds an offset
 * once is wrong for half the year — posts drift by an hour every spring, which
 * is exactly the kind of bug nobody reports and everybody notices.
 *
 * **A taken slot is skipped, not shared.** Two posts in one slot is a burst,
 * which is the opposite of what a queue is for.
 */

export type Slots = Record<string, number[]>;

/** Monday-first, matching how the grid reads to a person. */
export const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as const;
export type Day = (typeof DAYS)[number];

/** How far ahead to look before giving up. Eight weeks is a real plan. */
export const HORIZON_DAYS = 56;

export function isEmpty(slots: Slots): boolean {
  return Object.values(slots).every((hours) => hours.length === 0);
}

/**
 * The next free slot at or after `from`, as a UTC instant.
 *
 * `taken` is what is already scheduled for this account. Returns null when the
 * grid is empty or every slot inside the horizon is spoken for — the caller
 * says so rather than silently posting immediately, because "it went out now"
 * when somebody asked for "queue it" is worse than "your queue is full".
 */
export function nextSlot(opts: {
  slots: Slots;
  timezone: string;
  from: Date;
  taken?: Date[];
}): Date | null {
  if (isEmpty(opts.slots)) return null;

  const taken = new Set((opts.taken ?? []).map((d) => d.getTime()));
  const startOfDay = new Date(opts.from);

  for (let offset = 0; offset <= HORIZON_DAYS; offset += 1) {
    const day = new Date(startOfDay.getTime() + offset * 86_400_000);
    const local = partsIn(day, opts.timezone);
    const hours = opts.slots[local.day] ?? [];

    for (const hour of [...hours].sort((a, b) => a - b)) {
      const instant = instantFor(local.year, local.month, local.date, hour, opts.timezone);
      if (instant === null) continue;                 // the hour does not exist today
      if (instant.getTime() <= opts.from.getTime()) continue;
      if (taken.has(instant.getTime())) continue;     // a slot holds one post
      return instant;
    }
  }

  return null;
}

/**
 * Fills `count` slots in order.
 *
 * Used when a bulk import drops forty posts into a queue: each one takes the
 * next free slot, and the ones that do not fit inside the horizon come back as
 * fewer instants than asked for — so the caller can say "38 scheduled, 2 need
 * more slots" instead of quietly dropping them.
 */
export function nextSlots(opts: {
  slots: Slots;
  timezone: string;
  from: Date;
  count: number;
  taken?: Date[];
}): Date[] {
  const claimed = [...(opts.taken ?? [])];
  const out: Date[] = [];

  for (let i = 0; i < opts.count; i += 1) {
    const slot = nextSlot({
      slots: opts.slots,
      timezone: opts.timezone,
      from: opts.from,
      taken: claimed,
    });
    if (!slot) break;
    claimed.push(slot);
    out.push(slot);
  }

  return out;
}

/* ------------------------------------------------------------- timezones */

type LocalParts = { year: number; month: number; date: number; day: Day };

/**
 * The wall-clock date in a timezone, and which weekday it is there.
 *
 * `Intl` rather than arithmetic on offsets: the offset is not a property of a
 * zone, it is a property of a zone *at an instant*, and treating it as constant
 * is the bug this whole module exists to avoid.
 */
function partsIn(instant: Date, timezone: string): LocalParts {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: timezone,
    year: 'numeric', month: '2-digit', day: '2-digit', weekday: 'short',
  }).formatToParts(instant);

  const get = (type: string) => parts.find((p) => p.type === type)?.value ?? '';
  const weekday = get('weekday').toLowerCase().slice(0, 3) as Day;

  return {
    year: Number(get('year')),
    month: Number(get('month')),
    date: Number(get('day')),
    day: DAYS.includes(weekday) ? weekday : 'mon',
  };
}

/**
 * The UTC instant for a wall-clock time in a zone.
 *
 * Found by guessing UTC and correcting by the zone's offset *at that guess*,
 * twice — one pass is wrong when the guess and the answer straddle a DST
 * boundary, and two always converges for the one-hour shifts real zones use.
 *
 * Returns null for a wall-clock time that does not exist: on a spring-forward
 * morning 01:30 is skipped entirely, and a queue slot there must be passed over
 * rather than silently posting at 02:30.
 */
function instantFor(
  year: number,
  month: number,
  date: number,
  hour: number,
  timezone: string,
): Date | null {
  const wanted = Date.UTC(year, month - 1, date, hour, 0, 0);

  let guess = wanted;
  for (let pass = 0; pass < 2; pass += 1) {
    guess = wanted - offsetAt(new Date(guess), timezone);
  }

  const instant = new Date(guess);
  const check = partsIn(instant, timezone);
  const localHour = Number(
    new Intl.DateTimeFormat('en-GB', {
      timeZone: timezone, hour: '2-digit', hour12: false,
    }).format(instant),
  );

  // Round-tripped: if the wall clock we land on is not the one we asked for,
  // that time does not exist in this zone today.
  if (check.year !== year || check.month !== month || check.date !== date || localHour !== hour) {
    return null;
  }
  return instant;
}

/** The zone's offset from UTC, in milliseconds, at a given instant. */
function offsetAt(instant: Date, timezone: string): number {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: timezone,
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit',
    hour12: false,
  }).formatToParts(instant);

  const get = (type: string) => Number(parts.find((p) => p.type === type)?.value ?? '0');
  const asUtc = Date.UTC(
    get('year'), get('month') - 1, get('day'),
    // `hour12: false` renders midnight as 24 in some environments.
    get('hour') % 24, get('minute'), get('second'),
  );
  return asUtc - instant.getTime();
}

/* ------------------------------------------------------------- defaults */

/**
 * A starting grid for a new account.
 *
 * Weekday mid-mornings and one late afternoon. Deliberately modest: an empty
 * queue is useless and a queue with thirty slots posts thirty times a week,
 * which is how accounts lose followers.
 */
export function defaultSlots(): Slots {
  return {
    mon: [9, 16], tue: [9, 16], wed: [9, 16], thu: [9, 16], fri: [9],
    sat: [], sun: [],
  };
}

/** Normalises a grid from the UI: whole hours, in range, no duplicates. */
export function cleanSlots(input: Record<string, unknown>): Slots {
  const out: Slots = {};
  for (const day of DAYS) {
    const raw = input[day];
    const hours = Array.isArray(raw) ? raw : [];
    out[day] = [
      ...new Set(
        hours
          .map((h) => Math.trunc(Number(h)))
          .filter((h) => Number.isFinite(h) && h >= 0 && h <= 23),
      ),
    ].sort((a, b) => a - b);
  }
  return out;
}
