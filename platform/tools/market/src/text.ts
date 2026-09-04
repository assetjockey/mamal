/**
 * Finding a term in prose, correctly.
 *
 * Shared by AI visibility (does the model name this brand?) and the content
 * editor (does this draft cover this entity?), because both get the same thing
 * wrong in the same way if written twice: a naive `includes` matches "Ace"
 * inside "surface" and a brand called "Go" inside every sentence with the verb.
 *
 * The boundaries are Unicode-aware. `\b` is ASCII-only, so it mis-handles
 * "Café" and "Ω-corp" in both directions — it sees a boundary where there is
 * none and misses one where there is.
 */

/** Every place `term` appears as a word. Empty for terms too short to be safe. */
export function spansOf(text: string, term: string): { start: number; end: number }[] {
  const needle = term.trim();
  if (needle.length < 2) return [];

  const escaped = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const pattern = new RegExp(`(?<![\\p{L}\\p{N}])${escaped}(?![\\p{L}\\p{N}])`, 'giu');

  const out: { start: number; end: number }[] = [];
  for (const match of text.matchAll(pattern)) {
    if (match.index !== undefined) out.push({ start: match.index, end: match.index + match[0].length });
  }
  return out;
}

/**
 * Offsets where any of `terms` appears, counted once each.
 *
 * The terms overlap by design — "Acme" is a prefix of "Acme Corporation", and
 * "content marketing" contains "marketing" — so matching each separately counts
 * one textual occurrence several times. A brand with three aliases would
 * triple its own share of voice; a draft mentioning "content marketing" twice
 * would look like it covered two separate entities four times.
 *
 * Overlapping spans are merged and the longest match at a position wins.
 */
export function mentionsOf(text: string, terms: string[]): number[] {
  const spans = terms
    .flatMap((term) => spansOf(text, term))
    .sort((a, b) => a.start - b.start || b.end - a.end);

  const merged: { start: number; end: number }[] = [];
  for (const span of spans) {
    const last = merged[merged.length - 1];
    if (last && span.start < last.end) {
      last.end = Math.max(last.end, span.end);
      continue;
    }
    merged.push({ ...span });
  }
  return merged.map((s) => s.start);
}

/** Whether the term appears at all. */
export function appearsIn(text: string, term: string): boolean {
  return spansOf(text, term).length > 0;
}

/**
 * Whether the term appears, tolerating English inflection.
 *
 * For *coverage* only — "does this draft cover pricing?" is answered by a page
 * that says "costs", and demanding the exact surface form makes every coverage
 * number pessimistic in a way the writer cannot act on.
 *
 * Deliberately not used for brands: a brand is a proper noun, "Ace" and "Aces"
 * are different companies, and `appearsIn` stays exact for that reason.
 *
 * The boundaries are unchanged, so a suffix cannot let "Ace" match inside
 * "surface" — only the *end* of the term is allowed to vary.
 */
export function appearsInflected(text: string, term: string): boolean {
  const needle = term.trim();
  if (needle.length < 2) return false;

  // "reviews" should find "review", so the term is reduced to a stem first.
  // Not below four characters, and never through "ss" — "class" is not "cla".
  const stem =
    needle.length >= 4 && /[^s]s$/i.test(needle) ? needle.slice(0, -1) : needle;

  const escaped = stem.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const pattern = new RegExp(
    `(?<![\\p{L}\\p{N}])${escaped}(?:s|es|ed|ing|d)?(?![\\p{L}\\p{N}])`,
    'iu',
  );
  return pattern.test(text);
}
