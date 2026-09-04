import type { Tool, ToolOutput } from '../types.ts';

const text = (value: string): ToolOutput => ({ kind: 'text', value });
const pairs = (p: { label: string; value: string }[]): ToolOutput => ({ kind: 'pairs', pairs: p });

const TEXT_FIELD = {
  name: 'text', label: 'Text', type: 'textarea' as const, required: true,
  placeholder: 'Paste your text here',
};

/** English stop words — enough to stop "the" topping every density report. */
const STOP_WORDS = new Set([
  'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
  'from', 'as', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
  'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'this', 'that',
  'these', 'those', 'it', 'its', 'you', 'your', 'we', 'our', 'they', 'their', 'he', 'she',
]);

export const textTools: Tool[] = [
  {
    slug: 'word-counter',
    name: 'Word counter',
    category: 'content',
    description: 'Words, characters, sentences and reading time.',
    why: 'Thin pages rank badly and give answer engines nothing to quote. This tells you where you stand before you publish.',
    fields: [TEXT_FIELD],
    run: ({ text: input }) => {
      const trimmed = (input ?? '').trim();
      const words = trimmed ? trimmed.split(/\s+/).filter(Boolean) : [];
      const sentences = trimmed ? trimmed.split(/[.!?]+(?:\s|$)/).filter((s) => s.trim()) : [];
      const paragraphs = trimmed ? trimmed.split(/\n\s*\n/).filter((p) => p.trim()) : [];
      return pairs([
        { label: 'Words', value: String(words.length) },
        { label: 'Characters', value: String(input?.length ?? 0) },
        { label: 'Characters (no spaces)', value: String((input ?? '').replace(/\s/g, '').length) },
        { label: 'Sentences', value: String(sentences.length) },
        { label: 'Paragraphs', value: String(paragraphs.length) },
        // 225 wpm is the usual adult silent-reading figure.
        { label: 'Reading time', value: `${Math.max(1, Math.round(words.length / 225))} min` },
        {
          label: 'Average sentence',
          value: sentences.length ? `${Math.round(words.length / sentences.length)} words` : '—',
        },
      ]);
    },
  },

  {
    slug: 'keyword-density',
    name: 'Keyword density',
    category: 'content',
    description: 'Which words and phrases your text actually leans on.',
    why: 'Search engines infer what a page is about from what it repeats. If your target phrase is not in the top few, the page is probably about something else.',
    fields: [TEXT_FIELD],
    run: ({ text: input }) => {
      const words = (input ?? '')
        .toLowerCase()
        .replace(/[^\p{L}\p{N}\s'-]/gu, ' ')
        .split(/\s+/)
        .filter((w) => w.length > 2 && !STOP_WORDS.has(w));

      if (words.length === 0) return { kind: 'error', message: 'No countable words found.' };

      const counts = new Map<string, number>();
      for (const w of words) counts.set(w, (counts.get(w) ?? 0) + 1);

      // Two-word phrases usually match search intent better than single words.
      const phrases = new Map<string, number>();
      for (let i = 0; i < words.length - 1; i++) {
        const phrase = `${words[i]} ${words[i + 1]}`;
        phrases.set(phrase, (phrases.get(phrase) ?? 0) + 1);
      }

      const top = (m: Map<string, number>, n: number) =>
        [...m.entries()].sort((a, b) => b[1] - a[1]).slice(0, n);

      return {
        kind: 'table',
        columns: ['Term', 'Count', 'Density'],
        rows: [
          ...top(counts, 10).map(([term, n]) => [
            term, n, `${((n / words.length) * 100).toFixed(1)}%`,
          ]),
          ...top(phrases, 5)
            .filter(([, n]) => n > 1)
            .map(([term, n]) => [term, n, `${((n / words.length) * 100).toFixed(1)}%`]),
        ],
      };
    },
  },

  {
    slug: 'slug-generator',
    name: 'URL slug generator',
    category: 'content',
    description: 'Turn a title into a clean URL slug.',
    why: 'Readable URLs earn more clicks in a result and are easier to link to than an id.',
    fields: [{ name: 'text', label: 'Title', type: 'text', required: true, placeholder: 'How to run a technical SEO audit' }],
    run: ({ text: input }) =>
      text(
        (input ?? '')
          .normalize('NFKD')
          // Strip accents so "café" becomes "cafe" rather than "caf".
          .replace(/[̀-ͯ]/g, '')
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '')
          .slice(0, 80),
      ),
  },

  {
    slug: 'case-converter',
    name: 'Case converter',
    category: 'content',
    description: 'camelCase, snake_case, kebab-case, Title Case and more.',
    why: 'A small chore done constantly — renaming a field, matching a codebase convention, tidying a heading.',
    fields: [TEXT_FIELD],
    run: ({ text: input }) => {
      const words = (input ?? '').trim().split(/[\s_-]+|(?=[A-Z])/).filter(Boolean).map((w) => w.toLowerCase());
      if (words.length === 0) return { kind: 'error', message: 'Nothing to convert.' };
      const cap = (w: string) => w.charAt(0).toUpperCase() + w.slice(1);
      return pairs([
        { label: 'lower', value: words.join(' ') },
        { label: 'UPPER', value: words.join(' ').toUpperCase() },
        { label: 'Title Case', value: words.map(cap).join(' ') },
        { label: 'Sentence case', value: cap(words.join(' ')) },
        { label: 'camelCase', value: words[0] + words.slice(1).map(cap).join('') },
        { label: 'PascalCase', value: words.map(cap).join('') },
        { label: 'snake_case', value: words.join('_') },
        { label: 'kebab-case', value: words.join('-') },
      ]);
    },
  },

  {
    slug: 'text-cleaner',
    name: 'Text cleaner',
    category: 'content',
    description: 'Strip HTML, collapse whitespace and normalise quotes.',
    why: 'Pasting from a word processor drags in smart quotes and non-breaking spaces that break code and look wrong in markup.',
    fields: [TEXT_FIELD],
    run: ({ text: input }) =>
      text(
        (input ?? '')
          .replace(/<[^>]*>/g, ' ')
          .replace(/[‘’]/g, "'")
          .replace(/[“”]/g, '"')
          .replace(/[–—]/g, '-')
          .replace(/ /g, ' ')
          .replace(/[ \t]+/g, ' ')
          .replace(/\n{3,}/g, '\n\n')
          .trim(),
      ),
  },
];
