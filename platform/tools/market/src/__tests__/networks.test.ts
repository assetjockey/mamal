/**
 * Per-network validation and thread splitting.
 *
 * The cases worth pinning are the ones where the obvious implementation is
 * quietly wrong: `.length` on a post with an emoji, a URL counted at its real
 * length on X, a text-only Instagram post accepted at 09:00 and rejected at
 * 14:00, and a thread split through the middle of a link.
 */
import { describe, expect, it } from 'vitest';
import {
  NETWORKS, canSchedule, countCharacters, hashtagCount, hashtagsIn, networkFor, splitThread,
  validatePost,
} from '../networks.ts';

const x = NETWORKS.x!;
const instagram = NETWORKS.instagram!;
const linkedin = NETWORKS.linkedin!;

describe('counting characters the way the network does', () => {
  it('counts an emoji once, not twice', () => {
    // `'🎉'.length` is 2 — a post reading 279 in our UI is rejected at 280.
    expect('🎉'.length).toBe(2);
    expect(countCharacters('🎉', linkedin)).toBe(1);
  });

  it('counts a family emoji once, not eleven', () => {
    const family = '👩‍👩‍👧‍👦';
    expect(family.length).toBeGreaterThan(4);
    // Code points, so the ZWJ sequence is still several — but nothing like the
    // UTF-16 count, and consistent with what X reports.
    expect(countCharacters(family, linkedin)).toBeLessThan(family.length);
  });

  it('charges every X link the same 23, however long', () => {
    const short = countCharacters('see https://a.co', x);
    const long = countCharacters(`see https://example.com/${'a'.repeat(300)}`, x);
    // t.co wraps them, so the real length is not what counts.
    expect(short).toBe(long);
    expect(short).toBe('see '.length + 23);
  });

  it('leaves links alone on networks that do not wrap them', () => {
    const text = 'see https://example.com/a-very-long-path-indeed';
    expect(countCharacters(text, linkedin)).toBe(text.length);
  });

  it('does not mistake an email address for a link', () => {
    const text = 'write to ed@example.com';
    // The bare-domain rule must not fire after an @, or every mention costs 23.
    expect(countCharacters(text, x)).toBe(text.length);
  });
});

describe('hashtags', () => {
  it('lists each once, but counts every occurrence against the limit', () => {
    const text = '#widgets and more #Widgets plus #racks';
    // What the writer used…
    expect(hashtagsIn(text)).toEqual(['widgets', 'racks']);
    // …and what Instagram counts. A caption repeating one tag thirty times is
    // thirty hashtags to the network and one to a reader; checking the distinct
    // set would wave through the exact spam the limit exists to stop.
    expect(hashtagCount(text)).toBe(3);
  });

  it('ignores a hash inside a word', () => {
    expect(hashtagsIn('C#5 is a note, issue no#4')).toEqual([]);
  });

  it('reads hashtags in any script', () => {
    expect(hashtagsIn('#東京 #café')).toEqual(['東京', 'café']);
  });
});

describe('what each network will accept', () => {
  const draft = { body: 'A new widget rack.', images: 1, altText: ['a rack'] };

  it('refuses a text-only post on a media-first network', () => {
    const problems = validatePost({ body: 'Just words.' }, ['instagram', 'facebook']);
    const instagramProblem = problems.find((p) => p.network === 'instagram');

    // Rejected here, at 09:00, rather than by Instagram at 14:00.
    expect(instagramProblem).toMatchObject({ level: 'error' });
    expect(instagramProblem!.message).toMatch(/cannot be text only/i);
    // Facebook is fine with it, and must not be blocked by Instagram's rule.
    expect(problems.filter((p) => p.network === 'facebook' && p.level === 'error')).toEqual([]);
  });

  it('refuses a pin with nowhere to go', () => {
    const problems = validatePost(
      { ...draft, title: 'Racks' },
      ['pinterest'],
    );
    expect(problems.some((p) => p.level === 'error' && /destination link/i.test(p.message))).toBe(true);
  });

  it('reports every problem at once rather than the first', () => {
    const problems = validatePost(
      { body: '#a #b #c '.repeat(12), images: 12, videos: 1 },
      ['instagram'],
    );
    const errors = problems.filter((p) => p.level === 'error').map((p) => p.message);

    // Fixing five problems in five round trips is how people stop using a
    // scheduler.
    expect(errors.length).toBeGreaterThanOrEqual(3);
    expect(errors.some((m) => /at most 10 images/i.test(m))).toBe(true);
    expect(errors.some((m) => /cannot mix images and video/i.test(m))).toBe(true);
    expect(errors.some((m) => /hashtags/i.test(m))).toBe(true);
  });

  it('offers a thread where one is possible and does not where it is not', () => {
    const long = { body: 'w'.repeat(600) };
    const [onX] = validatePost(long, ['x']);
    expect(onX!.message).toMatch(/split it into a thread/i);

    const [onGoogle] = validatePost({ body: 'w'.repeat(2000) }, ['google_business']);
    expect(onGoogle!.message).not.toMatch(/thread/i);
  });

  it('treats missing alt text as advice, not a blocker', () => {
    const problems = validatePost({ body: 'Hello', images: 2, altText: ['one'] }, ['linkedin']);
    const alt = problems.find((p) => /alt text/i.test(p.message))!;

    // Blocking a launch over this teaches people to switch the check off.
    expect(alt.level).toBe('warning');
    expect(canSchedule(problems)).toBe(true);
  });

  it('requires a title only where the network has one', () => {
    expect(validatePost({ ...draft, videos: 1, images: 0 }, ['youtube']).some(
      (p) => p.level === 'error' && /needs a title/i.test(p.message),
    )).toBe(true);

    expect(validatePost(draft, ['linkedin']).some((p) => /title/i.test(p.message))).toBe(false);
  });

  it('names an unknown network rather than passing it through', () => {
    expect(validatePost(draft, ['myspace'])).toEqual([
      { level: 'error', network: 'myspace', message: 'myspace is not a network we can publish to.' },
    ]);
    expect(networkFor('myspace')).toBeNull();
  });

  it('says nothing about a post that is fine', () => {
    expect(validatePost(draft, ['linkedin', 'facebook', 'x'])).toEqual([]);
  });
});

describe('splitting a thread', () => {
  it('leaves a short post alone', () => {
    expect(splitThread('Short.', x)).toEqual(['Short.']);
  });

  it('never breaks a word or a URL', () => {
    const body = `${'A sentence about widget racks. '.repeat(20)}https://example.com/a/very/long/path/that/must/stay/whole`;
    const parts = splitThread(body, x);

    expect(parts.length).toBeGreaterThan(1);
    // Half a link is worse than a shorter post.
    expect(parts.some((p) => p.includes('https://example.com/a/very/long/path/that/must/stay/whole'))).toBe(true);
    for (const part of parts) {
      expect(part).not.toMatch(/\S-$/);
      expect(countCharacters(part, x)).toBeLessThanOrEqual(x.maxBody);
    }
  });

  it('budgets for the counter rather than appending it afterwards', () => {
    const parts = splitThread('Widget racks are useful. '.repeat(40), x);
    expect(parts.length).toBeGreaterThan(1);
    for (const part of parts) {
      // The " 3/7" is inside the limit, not pushing it over.
      expect(part).toMatch(/ \d+\/\d+$/);
      expect(countCharacters(part, x)).toBeLessThanOrEqual(x.maxBody);
    }
  });

  it('prefers paragraph boundaries to sentence boundaries', () => {
    const body = `${'First para. '.repeat(15)}\n\n${'Second para. '.repeat(15)}`;
    const parts = splitThread(body, x);
    expect(parts[0]).not.toContain('Second para');
  });

  it('splits a single unbroken sentence rather than giving up', () => {
    const parts = splitThread('word '.repeat(200).trim(), x);
    expect(parts.length).toBeGreaterThan(1);
    for (const part of parts) expect(countCharacters(part, x)).toBeLessThanOrEqual(x.maxBody);
  });

  it('does not split for a network that has no threads', () => {
    const body = 'w'.repeat(3000);
    expect(splitThread(body, instagram)).toEqual([body]);
  });
});
