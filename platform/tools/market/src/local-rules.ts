/**
 * The parts of Local that are pure arithmetic.
 *
 * Split out of `local.ts` for one reason: the composer, the review list and the
 * grid all want these in the *browser*, and `local.ts` imports `@mamal/db`.
 * Re-exporting them through the browser-safe subpath from there pulls Postgres
 * into the client bundle — see `scripts/check-client-imports.mjs`, which now
 * checks the subpath itself rather than only its import sites.
 */

export type LocalProfile = {
  id: string;
  name: string;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  primaryCategory: string | null;
  rating: number | null;
  reviewCount: number;
  /** What the profile is missing, computed rather than generated. */
  gaps: string[];
};

export type ReviewRow = {
  id: string;
  author: string | null;
  rating: number | null;
  comment: string | null;
  reply: string | null;
  occurredAt: string;
  /** Higher needs answering sooner. Computed, never generated. */
  urgency: number;
  reason: string;
};

/**
 * What is missing from a profile.
 *
 * Free, and the highest-value thing on the screen for most businesses: a
 * profile with no categories or no coordinates does not rank, and no amount of
 * anything else fixes that.
 */
export function profileGaps(profile: {
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  primaryCategory: string | null;
  categories: string[];
  reviewCount: number;
}): string[] {
  const gaps: string[] = [];
  if (!profile.primaryCategory) {
    gaps.push('No primary category — this is the single biggest factor in what you rank for.');
  }
  if (profile.categories.length < 2) {
    gaps.push('Only one category. Additional ones widen what you can appear for.');
  }
  if (!profile.address) gaps.push('No address on the profile.');
  if (profile.latitude === null || profile.longitude === null) {
    // Without a pin there is nothing to centre a grid on, and Google has
    // nothing to rank by distance.
    gaps.push('No map pin, so the profile cannot be ranked by proximity.');
  }
  if (profile.reviewCount < 10) {
    gaps.push(`${profile.reviewCount} reviews. Under about ten, ratings move sharply on one review.`);
  }
  return gaps;
}

/**
 * Which reviews need answering first.
 *
 * Arithmetic, deliberately: a one-star review from three days ago needs a reply
 * today whether or not AI is switched on, and a triage list that disappears
 * with the AI toggle would be the wrong thing to build.
 */
export function triage(review: {
  rating: number | null;
  comment: string | null;
  reply: string | null;
  occurredAt: string;
  now?: Date;
}): { urgency: number; reason: string } {
  if (review.reply) return { urgency: 0, reason: 'Answered.' };

  const ageDays = Math.max(
    0,
    ((review.now ?? new Date()).getTime() - Date.parse(review.occurredAt)) / 86_400_000,
  );

  const rating = review.rating ?? 3;
  // A one-star weighs five times a five-star; the scale is inverted and
  // squared so the bottom of it dominates, which is how people read them.
  let urgency = (6 - rating) ** 2;

  // A written complaint is public in a way a bare rating is not.
  if (review.comment && review.comment.trim().length > 40) urgency *= 1.5;

  /*
   * Decays with age rather than growing. A week-old unanswered one-star is
   * still worth answering, but the *new* one-star is the one that is visible
   * at the top of the profile — sorting old grievances first buries it.
   */
  urgency *= Math.max(0.3, 1 - ageDays / 30);

  const reason =
    rating <= 2
      ? ageDays < 3
        ? 'A new low rating, still at the top of your profile.'
        : `A low rating, ${Math.round(ageDays)} days unanswered.`
      : rating >= 4
        ? 'Positive and unanswered — replying is cheap and public.'
        : 'Middling and unanswered.';

  return { urgency: Math.round(urgency * 10) / 10, reason };
}
