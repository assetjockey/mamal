/**
 * Ranking for the command palette.
 *
 * Deliberately not a fuzzy-search dependency. A palette over a few hundred
 * known strings does not need one, and the ranking here encodes a product
 * decision a generic library cannot: a *word-start* match ("iss" -> "Issues")
 * must beat a mid-word one ("iss" -> "Missing alt text"), because the user is
 * typing the beginning of a thing they already know the name of.
 */

export const NO_MATCH = -1;

/**
 * Returns a score, higher is better, or NO_MATCH.
 *
 * Tiers, in order: whole-string prefix, word-start prefix, substring,
 * subsequence. Within a tier, an earlier and tighter match wins.
 */
export function score(text: string, query: string): number {
  if (!query) return 0;
  const t = text.toLowerCase();
  const q = query.toLowerCase();

  if (t.startsWith(q)) return 1000 - t.length;

  // Word starts, so "free tools" is found by "to" but "Bottom" is not.
  const words = t.split(/[\s/._-]+/);
  let offset = 0;
  for (const word of words) {
    if (word.startsWith(q)) return 800 - offset - t.length / 100;
    offset += word.length + 1;
  }

  const at = t.indexOf(q);
  if (at !== -1) return 600 - at - t.length / 100;

  // Subsequence: "aud" matches "A Useful Draft". Last resort, and scored below
  // every literal match so it never outranks one.
  let i = 0;
  let spread = 0;
  let last = -1;
  for (let c = 0; c < t.length && i < q.length; c++) {
    if (t[c] === q[i]) {
      if (last !== -1) spread += c - last - 1;
      last = c;
      i++;
    }
  }
  if (i === q.length) return 400 - spread - t.length / 100;

  return NO_MATCH;
}

/** Ranks and filters, keeping input order for equal scores so groups stay stable. */
export function rank<T>(items: T[], query: string, text: (item: T) => string): T[] {
  if (!query) return items;
  return items
    .map((item, i) => ({ item, i, s: score(text(item), query) }))
    .filter((r) => r.s !== NO_MATCH)
    .sort((a, b) => b.s - a.s || a.i - b.i)
    .map((r) => r.item);
}
