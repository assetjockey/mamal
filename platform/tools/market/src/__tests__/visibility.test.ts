import { describe, expect, it } from 'vitest';
import {
  buildProbe, hostOf, isNotableShift, prepareBrands, readAnswer, summarise,
  type AnswerReading, type Brand, type ModelAnswer,
} from '../visibility.ts';

const brands: Brand[] = [
  { name: 'Acme', domain: 'acme.com', isSelf: true },
  { name: 'Widgetly', domain: 'widgetly.io', isSelf: false },
  { name: 'Ace', domain: 'ace.dev', isSelf: false },
];

const answer = (over: Partial<ModelAnswer> = {}): ModelAnswer => ({
  model: 'claude', text: '', ...over,
});

describe('finding a brand in prose', () => {
  it('matches the brand as a word, not as a substring', () => {
    /*
     * "Ace" inside "surface" and "placement" is the failure that quietly makes
     * a visibility number nonsense — and it is what a naive `includes` does.
     */
    const reading = readAnswer(
      answer({ text: 'The surface finish and placement are good. Acme is worth a look.' }),
      brands,
    );
    expect(reading.brands.find((b) => b.brand === 'Ace')!.mentioned).toBe(false);
    expect(reading.brands.find((b) => b.brand === 'Acme')!.mentioned).toBe(true);
  });

  it('is case-insensitive but still boundary-aware', () => {
    const reading = readAnswer(answer({ text: 'ACME and acme and Acme-branded.' }), brands);
    const acme = reading.brands.find((b) => b.brand === 'Acme')!;
    // Three: the hyphen is a boundary, so "Acme-branded" counts.
    expect(acme.mentions).toBe(3);
  });

  it('handles a brand with regex characters in its name', () => {
    const reading = readAnswer(
      answer({ text: 'C++ Builder is a real product name.' }),
      [{ name: 'C++', isSelf: true }],
    );
    expect(reading.brands[0]!.mentioned).toBe(true);
  });

  it('respects boundaries outside ASCII', () => {
    // `\b` alone would match inside "Caféteria" and miss "Café."
    const reading = readAnswer(
      answer({ text: 'Café is good. Caféteria is a different thing.' }),
      [{ name: 'Café', isSelf: true }],
    );
    expect(reading.brands[0]!.mentions).toBe(1);
  });

  it('ignores a one-character brand rather than matching everything', () => {
    const reading = readAnswer(answer({ text: 'a b c' }), [{ name: 'a', isSelf: true }]);
    expect(reading.brands[0]!.mentioned).toBe(false);
  });

  it('counts an alias as the brand, once', () => {
    /*
     * "Acme" is a prefix of "Acme Corporation", so matching each term
     * separately counts one occurrence twice — and a brand with three aliases
     * would triple its own share of voice against competitors who have none.
     */
    const reading = readAnswer(
      answer({ text: 'Acme Corporation makes them.' }),
      [{ name: 'Acme', aliases: ['Acme Corporation'], isSelf: true }],
    );
    expect(reading.brands[0]!.mentions).toBe(1);
  });

  it('counts genuinely separate occurrences separately', () => {
    const reading = readAnswer(
      answer({ text: 'Acme Corporation makes them. Acme also sells parts.' }),
      [{ name: 'Acme', aliases: ['Acme Corporation'], isSelf: true }],
    );
    expect(reading.brands[0]!.mentions).toBe(2);
  });

  it('does not let aliases inflate share of voice', () => {
    // The bug this guards: an aliased brand out-scoring an unaliased one on
    // identical prose.
    const withAliases = readAnswer(
      answer({ text: 'Acme Corporation and Widgetly are the two options.' }),
      [
        { name: 'Acme', aliases: ['Acme Corporation', 'Acme Corp', 'acme.com'], isSelf: true },
        { name: 'Widgetly', isSelf: false },
      ],
    );
    const acme = withAliases.brands.find((b) => b.brand === 'Acme')!;
    const widgetly = withAliases.brands.find((b) => b.brand === 'Widgetly')!;
    expect(acme.mentions).toBe(widgetly.mentions);
  });
});

describe('position', () => {
  it('ranks by first appearance among the brands that appear', () => {
    const reading = readAnswer(
      answer({ text: 'Widgetly is popular. Acme is the other option.' }),
      brands,
    );
    expect(reading.brands.find((b) => b.brand === 'Widgetly')!.position).toBe(1);
    expect(reading.brands.find((b) => b.brand === 'Acme')!.position).toBe(2);
  });

  it('gives an absent brand no position rather than last place', () => {
    /*
     * Assigning "last" would make average position improve every time a
     * competitor is removed from the tracked set — the wrong incentive and the
     * wrong number.
     */
    const reading = readAnswer(answer({ text: 'Widgetly is popular.' }), brands);
    const acme = reading.brands.find((b) => b.brand === 'Acme')!;
    expect(acme.mentioned).toBe(false);
    expect(acme.position).toBeNull();
  });
});

describe('citations', () => {
  it('treats a link as a stronger signal than a mention', () => {
    const reading = readAnswer(
      answer({
        text: 'Acme and Widgetly are both fine.',
        citations: [{ url: 'https://www.acme.com/pricing' }],
      }),
      brands,
    );
    const acme = reading.brands.find((b) => b.brand === 'Acme')!;
    const widgetly = reading.brands.find((b) => b.brand === 'Widgetly')!;
    // Being named is nice; being linked is traffic.
    expect(acme).toMatchObject({ mentioned: true, cited: true });
    expect(widgetly).toMatchObject({ mentioned: true, cited: false });
  });

  it('counts a subdomain but not a lookalike domain', () => {
    const reading = readAnswer(
      answer({
        text: 'Acme.',
        citations: [
          { url: 'https://docs.acme.com/guide' },
          { url: 'https://notacme.com/blog' },
        ],
      }),
      brands,
    );
    expect(reading.brands.find((b) => b.brand === 'Acme')!.citedUrls).toEqual([
      'https://docs.acme.com/guide',
    ]);
  });

  it('reports every source, including ones no tracked brand owns', () => {
    const reading = readAnswer(
      answer({
        text: 'Acme.',
        citations: [
          { url: 'https://acme.com/x' },
          { url: 'https://someblog.example/review' },
        ],
      }),
      brands,
    );
    // "Which URLs do the models cite, and are they yours" is the question —
    // so the ones that are not yours matter just as much.
    expect(reading.sources).toEqual([
      { url: 'https://acme.com/x', host: 'acme.com', brand: 'Acme' },
      { url: 'https://someblog.example/review', host: 'someblog.example', brand: null },
    ]);
  });

  it('does not fall over on a citation that is not a URL', () => {
    const reading = readAnswer(
      answer({ text: 'Acme.', citations: [{ url: 'not a url' }] }),
      brands,
    );
    expect(reading.sources[0]).toMatchObject({ host: '', brand: null });
  });
});

describe('hostOf', () => {
  it('normalises the way a comparison needs', () => {
    expect(hostOf('https://www.Example.com/path?q=1')).toBe('example.com');
    expect(hostOf('http://sub.example.com')).toBe('sub.example.com');
    expect(hostOf('rubbish')).toBe('');
  });
});

/* ------------------------------------------------------------- summarising */

const reading = (text: string, citations?: { url: string }[]): AnswerReading =>
  readAnswer(answer({ text, citations }), brands);

describe('summarising a model', () => {
  it('answers “will they hear about us” with the mention rate', () => {
    const snapshot = summarise(
      [
        reading('Acme is the one to beat.'),
        reading('Widgetly, mostly.'),
        reading('Acme or Widgetly.'),
        reading('Neither, really.'),
      ],
      'Acme',
    );
    expect(snapshot.mentionRate).toBe(0.5);
    expect(snapshot.promptsRun).toBe(4);
  });

  it('computes share of voice over mentions, not over answers', () => {
    /*
     * A model that names a competitor three times and you once in the same
     * answer has told you something. Counting each answer as one vote hides it.
     */
    const snapshot = summarise(
      [reading('Widgetly is great. Widgetly leads. Widgetly again. Acme exists.')],
      'Acme',
    );
    expect(snapshot.shareOfVoice).toBeCloseTo(0.25, 5);
  });

  it('reports zero rather than NaN when nobody was named', () => {
    const snapshot = summarise([reading('It depends on your needs.')], 'Acme');
    // A model that named nobody is a real outcome; it must not blank the chart.
    expect(snapshot).toMatchObject({ shareOfVoice: 0, mentionRate: 0, avgPosition: null });
  });

  it('averages position only over the answers where the brand appeared', () => {
    const snapshot = summarise(
      [
        reading('Acme first, then Widgetly.'),   // position 1
        reading('Widgetly first, then Acme.'),   // position 2
        reading('Nobody in particular.'),        // absent — must not count
      ],
      'Acme',
    );
    expect(snapshot.avgPosition).toBe(1.5);
    expect(snapshot.mentionRate).toBeCloseTo(2 / 3, 5);
  });

  it('counts citations separately from mentions', () => {
    const snapshot = summarise(
      [
        reading('Acme.', [{ url: 'https://acme.com/a' }]),
        reading('Acme.'),
      ],
      'Acme',
    );
    expect(snapshot.mentionRate).toBe(1);
    expect(snapshot.citationCount).toBe(1);
  });

  it('handles a run that produced nothing', () => {
    expect(summarise([], 'Acme')).toMatchObject({ promptsRun: 0, shareOfVoice: 0 });
  });
});

/* ------------------------------------------------------------------ alerts */

const snap = (over: Partial<ReturnType<typeof summarise>>) => ({
  model: 'claude', shareOfVoice: 0.3, mentionRate: 0.5,
  avgPosition: 2, citationCount: 1, promptsRun: 10, ...over,
});

describe('deciding what to alert on', () => {
  it('says nothing about ordinary run-to-run variation', () => {
    // Answers differ between runs with the same prompt. A threshold that fires
    // on any movement produces a daily alert and gets muted.
    const result = isNotableShift(snap({ shareOfVoice: 0.30 }), snap({ shareOfVoice: 0.34 }));
    expect(result.notable).toBe(false);
  });

  it('reports appearing and disappearing whatever the share', () => {
    expect(isNotableShift(snap({ mentionRate: 0.4 }), snap({ mentionRate: 0, shareOfVoice: 0 })))
      .toMatchObject({ notable: true });
    expect(isNotableShift(snap({ mentionRate: 0, shareOfVoice: 0 }), snap({ mentionRate: 0.4 })))
      .toMatchObject({ notable: true });
  });

  it('reports a sustained shift in share', () => {
    const up = isNotableShift(snap({ shareOfVoice: 0.2 }), snap({ shareOfVoice: 0.45 }));
    expect(up.notable).toBe(true);
    expect(up.reason).toMatch(/up 25 points/);

    const down = isNotableShift(snap({ shareOfVoice: 0.45 }), snap({ shareOfVoice: 0.2 }));
    expect(down.reason).toMatch(/down 25 points/);
  });

  it('treats a first measurement as news only when there is news', () => {
    expect(isNotableShift(null, snap({ mentionRate: 0.3 }))).toMatchObject({ notable: true });
    expect(isNotableShift(null, snap({ mentionRate: 0 }))).toMatchObject({ notable: false });
  });
});

/* ------------------------------------------------------------------ probes */

describe('the probe', () => {
  it('never names the brand it is measuring', () => {
    /*
     * Asking "is Acme good?" guarantees the answer contains "Acme" and measures
     * nothing. The number that matters is whether the brand comes up
     * unprompted — the only version that reflects what a buyer would see.
     */
    const probe = buildProbe('What is the best widget for a small team?');
    expect(probe.user).not.toMatch(/acme/i);
    expect(probe.system).toMatch(/name real products/i);
    // And it discourages invention, because a hallucinated competitor would
    // enter the share-of-voice denominator.
    expect(probe.system).toMatch(/do not name it/i);
  });
});

describe('preparing the tracked set', () => {
  it('requires exactly one brand to be ours', () => {
    expect(prepareBrands([
      { brand: 'Acme', domain: 'acme.com', isSelf: true },
      { brand: 'Widgetly', domain: null, isSelf: false },
    ]).problem).toBeNull();

    expect(prepareBrands([{ brand: 'Widgetly', domain: null, isSelf: false }]).problem)
      .toMatch(/nothing to measure/);

    // Two selves makes share of voice meaningless; saying so beats a wrong
    // number rendered confidently.
    expect(prepareBrands([
      { brand: 'Acme', domain: null, isSelf: true },
      { brand: 'Acme Labs', domain: null, isSelf: true },
    ]).problem).toMatch(/exactly one/);
  });
});
