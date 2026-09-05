/**
 * The ad platform catalogue and the spend arithmetic.
 *
 * Both are pure, and both are what Market shows when AI is switched off — so
 * they are also the answer to "what does a lifetime plan actually get".
 */
import { describe, expect, it } from 'vitest';
import {
  AD_PLATFORMS, FRAMEWORKS, OBJECTIVES, PRESETS, TONES,
  aspectRatio, copyIsUsable, platformFor, presetsFor, validateCopy,
} from '../ad-platforms.ts';
import {
  MIN_CONVERSIONS_FOR_TREND, comparisonWindows, creativeFatigue, findAll, stalled,
  totalsOf, wasting, winning, type MetricRow,
} from '../ad-performance.ts';
import { toPromptContext } from '../creatives.ts';

const google = AD_PLATFORMS.google_search!;
const meta = AD_PLATFORMS.meta_feed!;

describe('the platform catalogue', () => {
  it('covers the platforms the plan names', () => {
    expect(Object.keys(AD_PLATFORMS).length).toBeGreaterThanOrEqual(28);
    for (const key of ['google_search', 'meta_feed', 'linkedin_sponsored', 'tiktok', 'x_ads']) {
      expect(platformFor(key), key).not.toBeNull();
    }
    expect(platformFor('myspace')).toBeNull();
  });

  it('keeps every field self-consistent', () => {
    for (const platform of Object.values(AD_PLATFORMS)) {
      for (const field of platform.fields) {
        expect(field.min, `${platform.key}.${field.key} min`).toBeLessThanOrEqual(field.max);
        expect(field.maxLength, `${platform.key}.${field.key} length`).toBeGreaterThan(0);
      }
      // An objective list nobody can pick from is a dead dropdown.
      expect(platform.objectives.length, platform.key).toBeGreaterThan(0);
      for (const objective of platform.objectives) {
        expect(OBJECTIVES, `${platform.key}: ${objective}`).toContain(objective);
      }
    }
  });

  it('carries the taxonomy magicads got right', () => {
    expect(Object.keys(FRAMEWORKS)).toContain('aida');
    expect(Object.keys(FRAMEWORKS)).toContain('pastor');
    expect(Object.keys(FRAMEWORKS).length).toBe(11);
    expect(TONES.length).toBe(13);
    // Each framework carries a shape the model can follow, not just a name.
    for (const [key, framework] of Object.entries(FRAMEWORKS)) {
      expect(framework.shape.length, key).toBeGreaterThan(20);
    }
  });
});

describe('canvas presets', () => {
  it('reduces a ratio the way a designer reads it', () => {
    expect(aspectRatio(1080, 1920)).toBe('9:16');
    expect(aspectRatio(1080, 1350)).toBe('4:5');
    expect(aspectRatio(1920, 1080)).toBe('16:9');
    expect(aspectRatio(1080, 1080)).toBe('1:1');
  });

  it('lists one canvas per shape rather than one per platform', () => {
    const vertical = PRESETS.find((p) => p.key === 'vertical_9_16')!;
    // The same 1080×1920 serves Stories, Reels, TikTok and Shorts; four entries
    // would imply four different renders of one image.
    expect(vertical.platforms).toEqual(
      expect.arrayContaining(['meta_stories', 'meta_reels', 'tiktok']),
    );
    expect(presetsFor('tiktok').map((p) => p.key)).toContain('vertical_9_16');
    expect(presetsFor('tiktok').map((p) => p.key)).not.toContain('leaderboard');
  });
});

describe('measuring what a model produced', () => {
  it('accepts copy that fits', () => {
    const problems = validateCopy(google, {
      headline: ['Widget racks for teams', 'Ships in two days', 'Fits any shelf'],
      description: ['Racks that hold every widget you own.', 'Free returns for thirty days.'],
    });
    expect(problems).toEqual([]);
    expect(copyIsUsable(problems)).toBe(true);
  });

  it('catches a headline three characters over, before Google does', () => {
    const problems = validateCopy(google, {
      headline: ['Widget racks', 'A headline that is far too long for this', 'Third'],
      description: ['One.', 'Two.'],
    });

    const over = problems.find((p) => p.field === 'headline' && p.level === 'error')!;
    // Names *which* headline: "one of your fifteen is too long" is unusable.
    expect(over.index).toBe(1);
    expect(over.message).toMatch(/over 30/);
    expect(copyIsUsable(problems)).toBe(false);
  });

  it('counts an emoji as one character, as the platform does', () => {
    const exactly = `${'a'.repeat(29)}🎉`;
    // 30 code points; `.length` would say 31 and refuse a valid headline.
    expect(exactly.length).toBe(31);
    const problems = validateCopy(google, {
      headline: [exactly, 'Two', 'Three'],
      description: ['One.', 'Two.'],
    });
    expect(problems.filter((p) => p.field === 'headline' && p.level === 'error')).toEqual([]);
  });

  it('refuses nothing where a field is required', () => {
    const problems = validateCopy(google, { headline: [], description: ['One.', 'Two.'] });
    expect(problems.find((p) => p.field === 'headline')).toMatchObject({ level: 'error' });
  });

  it('treats too few as advice, because a valid ad is still a valid ad', () => {
    const problems = validateCopy(google, {
      headline: ['One headline only'],
      description: ['One.', 'Two.'],
    });

    const short = problems.find((p) => p.field === 'headline')!;
    // One headline is a real ad that will underperform. Refusing to save it
    // would be overruling the customer about their own work.
    expect(short.level).toBe('warning');
    expect(copyIsUsable(problems)).toBe(true);
    expect(short.message).toMatch(/recommended 3/);
  });

  it('refuses more than the format takes', () => {
    const problems = validateCopy(meta, {
      primary_text: ['a', 'b', 'c', 'd', 'e', 'f'],
      headline: ['One'],
    });
    expect(problems.find((p) => p.field === 'primary_text')).toMatchObject({ level: 'error' });
  });

  it('mentions a field the model invented without refusing the whole thing', () => {
    const problems = validateCopy(google, {
      headline: ['One', 'Two', 'Three'],
      description: ['One.', 'Two.'],
      tagline: ['Something the model made up'],
    });
    const invented = problems.find((p) => p.field === 'tagline')!;
    expect(invented.level).toBe('warning');
    expect(copyIsUsable(problems)).toBe(true);
  });
});

describe('the brand as prompt context', () => {
  it('puts the prohibitions last', () => {
    const context = toPromptContext({
      name: 'Acme',
      voice: 'Plain and direct.',
      palette: ['#533afd'],
      dos: ['Say the price'],
      donts: ['Never use exclamation marks'],
    });

    expect(context).toContain('Brand: Acme.');
    // A model follows an instruction at the end of a prompt more reliably than
    // one buried in the middle.
    expect(context.indexOf('Never:')).toBeGreaterThan(context.indexOf('Always:'));
  });

  it('says nothing when there is nothing to say', () => {
    expect(toPromptContext({})).toBe('');
  });
});

/* --------------------------------------------------------- performance */

const row = (over: Partial<MetricRow> = {}): MetricRow => ({
  entityId: 'c1', entityName: 'Spring sale', level: 'campaign', capturedOn: '2026-03-10',
  impressions: 10_000, clicks: 200, spendMicros: 100_000_000,
  conversions: 20, conversionValueMicros: 400_000_000,
  ...over,
});

describe('totals', () => {
  it('is null where a rate has no denominator', () => {
    const empty = totalsOf([row({ impressions: 0, clicks: 0, conversions: 0, spendMicros: 0 })]);
    // "No clicks yet" and "a CPC of zero" are different facts, and only one is
    // true. Printing £0.00 or ∞ are both lies.
    expect(empty.ctr).toBeNull();
    expect(empty.cpcMicros).toBeNull();
    expect(empty.cpaMicros).toBeNull();
    expect(empty.roas).toBeNull();
  });

  it('computes the four rates from the sums', () => {
    const totals = totalsOf([row(), row({ capturedOn: '2026-03-11' })]);
    expect(totals.impressions).toBe(20_000);
    expect(totals.ctr).toBeCloseTo(0.02);
    expect(totals.cpaMicros).toBe(5_000_000);
    expect(totals.roas).toBeCloseTo(4);
  });
});

describe('what is worth telling somebody', () => {
  it('names spend with nothing to show for it', () => {
    const findings = wasting([
      row({ conversions: 0, conversionValueMicros: 0, spendMicros: 300_000_000 }),
    ]);
    expect(findings[0]).toMatchObject({ kind: 'wasting' });
    expect(findings[0]!.message).toMatch(/no conversions/i);
  });

  it('ignores a campaign that has barely spent anything', () => {
    // £2 and no conversions is a campaign that started this morning.
    expect(wasting([row({ conversions: 0, spendMicros: 2_000_000 })])).toEqual([]);
  });

  it('flags converting at more than the target price', () => {
    const findings = wasting([row({ conversions: 2 })], { targetCpaMicros: 10_000_000 });
    expect(findings[0]!.message).toMatch(/against a target/i);
  });

  it('finds what deserves more budget', () => {
    const findings = winning([row({ conversions: 40, conversionValueMicros: 600_000_000 })]);
    expect(findings[0]).toMatchObject({ kind: 'winning' });
    expect(findings[0]!.message).toMatch(/6\.0×/);
  });

  it('will not call three conversions a win', () => {
    expect(winning([row({ conversions: 3, conversionValueMicros: 900_000_000 })])).toEqual([]);
    expect(MIN_CONVERSIONS_FOR_TREND).toBeGreaterThan(3);
  });
});

describe('comparing two periods', () => {
  it('notices a campaign that stopped converting', () => {
    const findings = stalled([row({ conversions: 25 })], [row({ conversions: 0 })]);
    expect(findings[0]).toMatchObject({ kind: 'stalled' });
    expect(findings[0]!.message).toMatch(/none in this one/i);
  });

  it('compares cost per conversion, not conversion count', () => {
    /*
     * Half the budget, half the conversions, same CPA. Nothing is wrong, and a
     * finder counting conversions would shout about it.
     */
    const before = [row({ conversions: 20, spendMicros: 100_000_000 })];
    const after = [row({ conversions: 10, spendMicros: 50_000_000 })];
    expect(stalled(before, after)).toEqual([]);

    // Same spend, half the conversions: CPA doubled, and that is the signal.
    const worse = [row({ conversions: 10, spendMicros: 100_000_000 })];
    expect(stalled(before, worse)[0]!.message).toMatch(/up 100%/);
  });

  it('will not read a small base as a trend', () => {
    expect(stalled([row({ conversions: 3 })], [row({ conversions: 0 })])).toEqual([]);
  });

  it('separates creative fatigue from simply being shown less', () => {
    const before = [row({ impressions: 50_000, clicks: 1000 })];
    // Same impressions, a third of the clicks — the audience has seen it.
    const fatigued = [row({ impressions: 48_000, clicks: 300 })];
    const found = creativeFatigue(before, fatigued);
    expect(found[0]).toMatchObject({ kind: 'creative_fatigue' });
    expect(found[0]!.message).toMatch(/new creative, not a new campaign/i);

    // A tenth of the impressions and a proportionate drop in clicks is a
    // campaign that got quieter, which is a different problem.
    const quieter = [row({ impressions: 5_000, clicks: 100 })];
    expect(creativeFatigue(before, quieter)).toEqual([]);
  });

  it('answers the money question first', () => {
    const findings = findAll(
      [row({ conversions: 25 })],
      [row({ conversions: 0, conversionValueMicros: 0, spendMicros: 300_000_000 })],
    );
    expect(findings[0]!.kind).toBe('wasting');
  });
});

describe('choosing the windows', () => {
  it('leaves the unsettled days alone and makes both halves equal', () => {
    const { earlier, later } = comparisonWindows({
      today: new Date('2026-03-31T00:00:00Z'), days: 7, settleDays: 3,
    });

    // Nothing from the last three days: platforms revise them as conversions
    // attribute late, so including them shows a decline that is not there.
    expect(later.to).toBe('2026-03-28');
    expect(later.from).toBe('2026-03-22');
    expect(earlier.to).toBe('2026-03-21');
    expect(earlier.from).toBe('2026-03-15');

    expect(dayCount(later)).toBe(dayCount(earlier));
  });
});

function dayCount(window: { from: string; to: string }): number {
  return (Date.parse(window.to) - Date.parse(window.from)) / 86_400_000 + 1;
}
