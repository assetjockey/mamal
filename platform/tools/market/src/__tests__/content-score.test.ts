/**
 * The content editor's scoring, which is pure arithmetic and therefore testable
 * without a database, a vendor or a model.
 *
 * The cases that matter are the ones where a naive implementation is confidently
 * wrong: code blocks counted as prose, URLs counted as keyword uses, a stuffed
 * page scoring higher than a well-written one, and a readability number
 * reported for text the formula was never calibrated on.
 */
import { describe, expect, it } from 'vitest';
import {
  DENSITY_BAND, parseBody, readingEase, scoreContent, syllablesIn, wordsOf,
} from '../content-score.ts';

const prose = (sentences: number, word = 'widget') =>
  Array.from({ length: sentences }, (_, i) => `The ${word} team shipped a change on day ${i}.`).join(' ');

describe('reading the markdown', () => {
  it('does not count a code block as writing', () => {
    const body = [
      '# Guide',
      '',
      'Here is how it works.',
      '',
      '```ts',
      'const widget = new Widget({ name: "widget", widget: true });',
      'for (let i = 0; i < 100; i += 1) console.log(widget, widget, widget);',
      '```',
      '',
      'That is all.',
    ].join('\n');

    const parsed = parseBody(body);
    // Otherwise a tutorial with a long snippet reads as a long, keyword-dense
    // article that nobody actually wrote.
    expect(parsed.prose).not.toContain('console.log');
    expect(parsed.words.length).toBeLessThan(15);
  });

  it('reads link text but not the URL', () => {
    const parsed = parseBody('See our [widget reviews](/blog/best-widget-reviews) for more.');
    expect(parsed.prose).toContain('widget reviews');
    expect(parsed.prose).not.toContain('best-widget-reviews');
    expect(parsed.links[0]).toMatchObject({ internal: true, text: 'widget reviews' });
  });

  it('treats a full URL as leaving the site and a relative one as staying', () => {
    const parsed = parseBody('[a](/x) [b](https://other.test/y) [c](#section) [d](//cdn.test/z)');
    expect(parsed.links.map((l) => l.internal)).toEqual([true, false, true, false]);
  });

  it('finds headings but never one inside a fence', () => {
    const body = ['# Real', '', '```md', '# Not a heading', '```', '', '## Also real'].join('\n');
    expect(parseBody(body).headings).toEqual([
      { level: 1, text: 'Real' },
      { level: 2, text: 'Also real' },
    ]);
  });

  it('separates images from their alt text', () => {
    const parsed = parseBody('![a widget](/w.png) and ![](/bare.png)');
    expect(parsed.images).toEqual([
      { alt: 'a widget', src: '/w.png' },
      { alt: '', src: '/bare.png' },
    ]);
    expect(parsed.prose).not.toContain('/w.png');
  });

  it('counts words in any script', () => {
    expect(wordsOf('naïve café 東京 x-ray')).toEqual(['naïve', 'café', '東京', 'x-ray']);
  });
});

describe('readability', () => {
  it('counts syllables the way the formula assumes', () => {
    expect(syllablesIn('game')).toBe(1);      // silent trailing e
    expect(syllablesIn('table')).toBe(2);     // -le after a consonant is its own
    expect(syllablesIn('walked')).toBe(1);    // -ed usually is not
    expect(syllablesIn('the')).toBe(1);
    expect(syllablesIn('readability')).toBe(5);
  });

  it('refuses to score text too short to mean anything', () => {
    const { score, note } = readingEase('Short and sweet.');
    // One full stop either way swings the sentence average wildly.
    expect(score).toBeNull();
    expect(note).toMatch(/under 50 words/i);
  });

  it('refuses to score text that is not English', () => {
    const japanese = Array.from({ length: 60 }, () => '東京の天気は今日晴れです。').join('');
    const { score, note } = readingEase(japanese);
    // The constants are fitted to English syllables; a number here would be
    // confident and meaningless.
    expect(score).toBeNull();
    expect(note).toMatch(/English/i);
  });

  it('scores plain writing higher than dense writing', () => {
    const plain = readingEase(prose(12)).score!;
    const dense = readingEase(
      Array.from({ length: 8 }, () =>
        'Notwithstanding the aforementioned considerations, the organisational implementation ' +
        'necessitates comprehensive reconceptualisation of interdepartmental methodologies.',
      ).join(' '),
    ).score!;
    expect(plain).toBeGreaterThan(dense);
  });
});

describe('scoring a draft', () => {
  const good = {
    title: 'How to choose the best widget for a small team',
    metaDescription:
      'A practical guide to choosing the best widget for a small team, covering price, ' +
      'setup time, support and the three mistakes buyers make most often.',
    targetKeywords: ['best widget'],
    body: [
      '# How to choose the best widget for a small team',
      '',
      'Choosing the best widget comes down to three things: price, setup and support.',
      prose(6),
      '',
      '## Price',
      prose(8),
      'Compare this against our [pricing guide](/pricing) before deciding.',
      '',
      '## Setup time',
      prose(8),
      'Most teams finish in a day. See the [setup checklist](/setup).',
      '',
      '## Support',
      prose(8),
      'A widget is only as good as the help behind it.',
      '',
      '![A widget dashboard](/dash.png)',
    ].join('\n'),
  };

  it('rewards a draft that does the obvious things', () => {
    const result = scoreContent(good, { targetWordCount: 200 });
    expect(result.score).toBeGreaterThan(75);
    expect(result.checks.filter((c) => c.state === 'fail')).toEqual([]);
  });

  it('every failing check says what to do', () => {
    const result = scoreContent(
      { title: '', body: 'Too short.', targetKeywords: [] },
      { entities: ['price', 'support'], questions: ['how much does a widget cost'] },
    );
    for (const check of result.checks) {
      if (check.state === 'pass') expect(check.fix).toBeUndefined();
      // A score with no reasons is a mood, not a to-do list.
      else expect(check.fix, `${check.key} has no fix`).toBeTruthy();
    }
  });

  it('penalises stuffing rather than rewarding it', () => {
    const stuffed = {
      ...good,
      body: `${good.body}\n\n${Array.from({ length: 40 }, () => 'The best widget is the best widget.').join(' ')}`,
    };

    const density = scoreContent(stuffed).checks.find((c) => c.key === 'keyword_density')!;
    expect(density.state).toBe('fail');
    expect(density.fix).toMatch(/above/i);
    // The whole point: repeating the phrase must not buy a better score.
    expect(scoreContent(stuffed).score).toBeLessThan(scoreContent(good).score);
  });

  it('does not count a URL as a use of the keyword', () => {
    const linkOnly = {
      title: 'Widgets',
      targetKeywords: ['best widget'],
      body: `${prose(20)}\n\nSee [more](/blog/best-widget-roundup) and https://example.test/best-widget`,
    };
    const result = scoreContent(linkOnly);
    // The phrase is in two hrefs and nowhere a reader can see it.
    expect(result.density).toBe(0);
    expect(result.checks.find((c) => c.key === 'keyword_early')!.state).toBe('fail');
  });

  it('scales the section count to the length, and says nothing about short posts', () => {
    const short = scoreContent({
      title: 'A short answer to a small question about widgets',
      targetKeywords: ['widget'],
      body: `# Answer\n\n## Detail\n\n${prose(4)}`,
    });
    // Nobody skims forty words. "Add more headings" here would be noise, so
    // the check does not run at all rather than passing vacuously.
    expect(short.checks.find((c) => c.key === 'sections')).toBeUndefined();

    const long = scoreContent({
      title: 'A very long guide to widgets for teams of every size',
      targetKeywords: ['widget'],
      body: `# Guide\n\n## One\n\n${prose(120)}`,
    });
    const sections = long.checks.find((c) => c.key === 'sections')!;
    expect(sections.state).not.toBe('pass');
    expect(sections.fix).toMatch(/around 3/);
  });

  it('catches a skipped heading level and names the offender', () => {
    const result = scoreContent({
      title: 'Widgets for teams that need a longer title here',
      targetKeywords: ['widget'],
      body: `# Top\n\n### Jumped\n\n${prose(10)}`,
    });
    const order = result.checks.find((c) => c.key === 'heading_order')!;
    expect(order.state).toBe('fail');
    expect(order.detail).toContain('Jumped');
  });

  it('reads a question as answered when the page covers it in its own words', () => {
    const result = scoreContent(
      {
        title: 'What a widget costs and why',
        targetKeywords: ['widget'],
        body: `# Cost\n\nA widget costs £19 a month.\n\n${prose(10)}`,
      },
      { questions: ['how much does a widget cost', 'which widget integrates with Salesforce'] },
    );
    // Nobody writes the question verbatim; the content words are the test.
    expect(result.questionsAnswered).toEqual(['how much does a widget cost']);
    expect(result.questionsMissing).toHaveLength(1);
  });

  it('matches topics as words, not substrings', () => {
    const result = scoreContent(
      { title: 'Surfacing data', targetKeywords: ['data'], body: `# Surface\n\n${prose(10)}` },
      { entities: ['Ace'] },
    );
    // "Ace" must not match inside "surface" — the mistake that makes most
    // coverage numbers quietly meaningless.
    expect(result.entitiesCovered).toEqual([]);
  });

  it('is honest that the band has two edges', () => {
    expect(DENSITY_BAND.min).toBeLessThan(DENSITY_BAND.max);
    const empty = scoreContent({ title: '', body: '', targetKeywords: [] });
    // No keyword set is a failure that explains itself rather than a zero.
    expect(empty.checks.find((c) => c.key === 'keyword_set')!.state).toBe('fail');
    expect(empty.score).toBe(0);
  });
});
