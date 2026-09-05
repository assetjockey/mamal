/**
 * Queue slots.
 *
 * Nearly every assertion here is about time zones, because that is where this
 * kind of code goes wrong: an offset captured once is right for half the year,
 * and the resulting one-hour drift every spring is the sort of bug that nobody
 * reports and everybody notices.
 */
import { describe, expect, it } from 'vitest';
import { cleanSlots, defaultSlots, isEmpty, nextSlot, nextSlots } from '../queue.ts';

/**
 * The wall clock in a zone, for asserting what a person would see.
 *
 * Assembled from parts rather than formatted whole: ICU's separator between the
 * weekday and the time differs between Node builds, and asserting on it tests
 * the runtime rather than the code.
 */
const localTime = (instant: Date, timeZone: string) => {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone, weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false,
  }).formatToParts(instant);
  const get = (type: string) => parts.find((p) => p.type === type)?.value ?? '';
  return `${get('weekday')} ${get('hour')}:${get('minute')}`;
};

describe('finding the next slot', () => {
  const slots = { mon: [9], tue: [9, 17], wed: [], thu: [9], fri: [], sat: [], sun: [] };

  it('returns the next one in the account’s own clock', () => {
    // Monday 2026-03-16, 10:00 London — past the 09:00 slot.
    const from = new Date('2026-03-16T10:00:00Z');
    const slot = nextSlot({ slots, timezone: 'Europe/London', from })!;

    expect(localTime(slot, 'Europe/London')).toBe('Tue 09:00');
  });

  it('never returns a slot in the past', () => {
    const from = new Date('2026-03-17T09:30:00Z');
    const slot = nextSlot({ slots, timezone: 'Europe/London', from })!;
    expect(slot.getTime()).toBeGreaterThan(from.getTime());
    expect(localTime(slot, 'Europe/London')).toBe('Tue 17:00');
  });

  it('skips a slot that is already taken rather than doubling up', () => {
    const from = new Date('2026-03-16T10:00:00Z');
    const first = nextSlot({ slots, timezone: 'Europe/London', from })!;
    const second = nextSlot({ slots, timezone: 'Europe/London', from, taken: [first] })!;

    // Two posts in one slot is a burst, which is what a queue is for avoiding.
    expect(localTime(first, 'Europe/London')).toBe('Tue 09:00');
    expect(localTime(second, 'Europe/London')).toBe('Tue 17:00');
  });

  it('says the queue is empty rather than posting immediately', () => {
    const empty = { mon: [], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [] };
    expect(isEmpty(empty)).toBe(true);
    // "It went out now" when somebody asked for "queue it" is worse than
    // "your queue is full".
    expect(nextSlot({ slots: empty, timezone: 'UTC', from: new Date() })).toBeNull();
  });
});

describe('daylight saving', () => {
  /*
   * The whole point of using Intl rather than a stored offset. Europe/London
   * goes to BST on 2026-03-29; a 09:00 slot must stay 09:00 to the person
   * whose account it is, which means the UTC instant moves by an hour.
   */
  const slots = { mon: [9], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [] };

  it('keeps the local hour across a spring-forward', () => {
    const before = nextSlot({
      slots, timezone: 'Europe/London', from: new Date('2026-03-22T10:00:00Z'),
    })!;
    const after = nextSlot({
      slots, timezone: 'Europe/London', from: new Date('2026-03-29T10:00:00Z'),
    })!;

    expect(localTime(before, 'Europe/London')).toBe('Mon 09:00');
    expect(localTime(after, 'Europe/London')).toBe('Mon 09:00');

    // Same wall clock, different UTC instant — 09:00 GMT then 08:00 UTC.
    expect(before.toISOString()).toContain('T09:00');
    expect(after.toISOString()).toContain('T08:00');
  });

  it('skips a wall-clock hour that does not exist', () => {
    // US/Eastern springs forward 2026-03-08: 02:00–02:59 never happens.
    const twoAm = { sun: [2], mon: [], tue: [], wed: [], thu: [], fri: [], sat: [] };
    const slot = nextSlot({
      slots: twoAm, timezone: 'America/New_York', from: new Date('2026-03-07T12:00:00Z'),
    })!;

    // Passed over rather than silently posting at 03:00 on the wrong day.
    expect(localTime(slot, 'America/New_York')).toBe('Sun 02:00');
    expect(slot.toISOString().slice(0, 10)).not.toBe('2026-03-08');
  });

  it('works south of the equator, where the seasons are the other way round', () => {
    const before = nextSlot({
      slots, timezone: 'Australia/Sydney', from: new Date('2026-03-30T00:00:00Z'),
    })!;
    expect(localTime(before, 'Australia/Sydney')).toBe('Mon 09:00');
  });

  it('handles a half-hour zone', () => {
    const slot = nextSlot({
      slots, timezone: 'Asia/Kolkata', from: new Date('2026-03-22T00:00:00Z'),
    })!;
    expect(localTime(slot, 'Asia/Kolkata')).toBe('Mon 09:00');
    // UTC+5:30, so 09:00 local is 03:30 UTC.
    expect(slot.toISOString()).toContain('T03:30');
  });
});

describe('filling several slots', () => {
  const slots = { mon: [9, 17], tue: [9], wed: [], thu: [], fri: [], sat: [], sun: [] };

  it('gives each post its own slot, in order', () => {
    const found = nextSlots({
      slots, timezone: 'UTC', from: new Date('2026-03-15T00:00:00Z'), count: 3,
    });

    expect(found).toHaveLength(3);
    expect(found.map((d) => localTime(d, 'UTC'))).toEqual(['Mon 09:00', 'Mon 17:00', 'Tue 09:00']);
    // Strictly increasing, or a "queue" is just a pile.
    expect(found[0]!.getTime()).toBeLessThan(found[1]!.getTime());
    expect(found[1]!.getTime()).toBeLessThan(found[2]!.getTime());
  });

  it('returns fewer than asked rather than dropping the rest silently', () => {
    const oneSlot = { mon: [9], tue: [], wed: [], thu: [], fri: [], sat: [], sun: [] };
    const found = nextSlots({
      slots: oneSlot, timezone: 'UTC', from: new Date('2026-03-15T00:00:00Z'), count: 100,
    });

    // Eight weeks of Mondays. The caller can then say "8 scheduled, 92 need
    // more slots" instead of losing 92 posts.
    expect(found.length).toBeGreaterThan(0);
    expect(found.length).toBeLessThan(100);
  });

  it('works around what is already booked', () => {
    const from = new Date('2026-03-15T00:00:00Z');
    const taken = [new Date('2026-03-16T09:00:00Z')];
    const found = nextSlots({ slots, timezone: 'UTC', from, count: 2, taken });

    expect(found.map((d) => localTime(d, 'UTC'))).toEqual(['Mon 17:00', 'Tue 09:00']);
  });
});

describe('the grid itself', () => {
  it('has a modest default', () => {
    const slots = defaultSlots();
    const total = Object.values(slots).flat().length;
    // An empty queue is useless; thirty slots a week is how accounts lose
    // followers.
    expect(total).toBeGreaterThan(3);
    expect(total).toBeLessThan(12);
    expect(slots.sat).toEqual([]);
  });

  it('cleans whatever the UI sends', () => {
    const cleaned = cleanSlots({
      mon: [9, 9, 17, 25, -1, 3.7, 'x'],
      tue: 'not an array',
      nonsense: [1],
    });

    // Deduplicated, sorted, whole hours in range, and nothing invented.
    expect(cleaned.mon).toEqual([3, 9, 17]);
    expect(cleaned.tue).toEqual([]);
    expect(Object.keys(cleaned)).toEqual(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
  });
});
