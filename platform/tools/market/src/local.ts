/**
 * Local profiles, reviews and rank grids.
 *
 * The division of labour is the same as everywhere else in Market: the geometry
 * and the comparisons are pure (`geo-grid.ts`, `nap.ts`), this is the part that
 * touches the database, and AI only ever *writes* — it drafts a review reply and
 * narrates a grid. Every number and every triage decision below is arithmetic,
 * which is what makes the local screen complete on a lifetime plan.
 *
 * Two behaviours worth stating:
 *
 * **A grid is priced before it is run.** Forty-nine paid searches for one
 * keyword on a 7×7 is a real amount of money, and the customer sees the figure
 * on the button. The allowance is checked once for the whole grid rather than
 * per point, so a grid never half-runs.
 *
 * **A review's urgency is computed, not generated.** One star from a named
 * customer three days ago needs answering today whether or not AI is available.
 */
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { MarketNotAllowed } from './service.ts';
import {
  buildGrid, gridCost, summariseGrid, type GridReading, type GridRun, type GridSize,
} from './geo-grid.ts';
import { profileGaps, triage, type LocalProfile, type ReviewRow } from './local-rules.ts';

export async function listProfiles(
  tx: WorkspaceScopedDb,
  opts: { projectId: string },
): Promise<LocalProfile[]> {
  const rows = await tx.execute<{
    id: string; name: string; address: string | null;
    latitude: string | null; longitude: string | null;
    primary_category: string | null; categories: string[];
    rating: number | null; review_count: number;
  }>(sql`
    select id, name, address, latitude, longitude, primary_category, categories,
           rating, review_count
      from market_local_profiles where project_id = ${opts.projectId} order by name`);

  return rows.map((r) => {
    // `numeric` comes back as a string, and `Number(null)` is 0 — which would
    // put a business on Null Island rather than saying it has no pin.
    const latitude = r.latitude === null ? null : Number(r.latitude);
    const longitude = r.longitude === null ? null : Number(r.longitude);

    return {
      id: r.id,
      name: r.name,
      address: r.address,
      latitude,
      longitude,
      primaryCategory: r.primary_category,
      rating: r.rating,
      reviewCount: r.review_count,
      gaps: profileGaps({
        address: r.address,
        latitude,
        longitude,
        primaryCategory: r.primary_category,
        categories: r.categories,
        reviewCount: r.review_count,
      }),
    };
  });
}

/* ------------------------------------------------------------ rank grids */

/** What a grid costs, so the button can say so before it is pressed. */
export function quoteGrid(size: GridSize, keywords: string[]): number {
  return gridCost(size, keywords.length);
}

export type RankPointSource = (input: {
  keyword: string;
  latitude: number;
  longitude: number;
  externalId: string;
}) => Promise<number | null>;

/**
 * Runs a grid, one lookup per point.
 *
 * The allowance is checked **once, for the whole grid**. Checking per point
 * would let a 7×7 stop at point 31 having spent 31 credits on a map with a hole
 * in it, which is worse than not running it.
 */
export async function runGrid(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    profileId: string;
    keyword: string;
    size: GridSize;
    radiusKm: number;
    today?: Date;
  },
  lookup: RankPointSource,
): Promise<GridRun> {
  const [profile] = await tx.execute<{
    external_id: string; latitude: string | null; longitude: string | null;
  }>(sql`
    select external_id, latitude, longitude from market_local_profiles
     where id = ${opts.profileId}`);

  if (!profile) throw new MarketNotAllowed('not_found', 'That profile is no longer connected.');
  if (profile.latitude === null || profile.longitude === null) {
    throw new MarketNotAllowed(
      'invalid',
      'This profile has no map pin, so there is nowhere to centre a grid on.',
    );
  }

  const points = buildGrid({
    centre: { latitude: Number(profile.latitude), longitude: Number(profile.longitude) },
    size: opts.size,
    radiusKm: opts.radiusKm,
  });

  await requireHeadroom(tx, opts.workspaceId, 'market.rank_check', points.length);

  const capturedOn = (opts.today ?? new Date()).toISOString().slice(0, 10);
  const readings: GridReading[] = [];

  for (const point of points) {
    /*
     * A lookup that fails is stored as `null` — "not found here" — rather than
     * abandoning the grid. A map with one missing square is worth far more than
     * no map, and the alternative is losing the forty-eight points that worked.
     */
    let position: number | null = null;
    try {
      position = await lookup({
        keyword: opts.keyword,
        latitude: point.latitude,
        longitude: point.longitude,
        externalId: profile.external_id,
      });
    } catch {
      position = null;
    }

    await tx.execute(sql`
      insert into market_local_rank_points
        (workspace_id, profile_id, keyword, captured_on, latitude, longitude, position, credits_spent)
      values (${opts.workspaceId}, ${opts.profileId}, ${opts.keyword}, ${capturedOn}::date,
              ${point.latitude}, ${point.longitude}, ${position}, 1)`);

    readings.push({
      latitude: point.latitude,
      longitude: point.longitude,
      col: point.col,
      row: point.row,
      position,
    });
  }

  return {
    keyword: opts.keyword,
    capturedOn,
    size: opts.size,
    summary: summariseGrid(readings, opts.size),
    readings,
  };
}

/** The most recent grid for each keyword on a profile. */
export async function latestGrids(
  tx: WorkspaceScopedDb,
  opts: { profileId: string },
): Promise<GridRun[]> {
  const rows = await tx.execute<{
    keyword: string; captured_on: string; latitude: string; longitude: string;
    position: number | null;
  }>(sql`
    with latest as (
      select keyword, max(captured_on) as captured_on
        from market_local_rank_points where profile_id = ${opts.profileId}
       group by keyword
    )
    select p.keyword, p.captured_on::text, p.latitude, p.longitude, p.position
      from market_local_rank_points p
      join latest l on l.keyword = p.keyword and l.captured_on = p.captured_on
     where p.profile_id = ${opts.profileId}
     order by p.keyword, p.latitude desc, p.longitude`);

  type Row = (typeof rows)[number];
  const byKeyword = new Map<string, Row[]>();
  for (const row of rows) {
    byKeyword.set(row.keyword, [...(byKeyword.get(row.keyword) ?? []), row]);
  }

  return [...byKeyword.entries()].map(([keyword, group]) => {
    // The grid is square, so the side is the root of the point count. Derived
    // rather than stored because a customer can change the size between runs
    // and an old grid must still read correctly.
    const size = Math.round(Math.sqrt(group.length));
    const readings: GridReading[] = group.map((row, index) => ({
      latitude: Number(row.latitude),
      longitude: Number(row.longitude),
      row: Math.floor(index / size),
      col: index % size,
      position: row.position,
    }));

    return {
      keyword,
      capturedOn: group[0]!.captured_on,
      size,
      summary: summariseGrid(readings, size),
      readings,
    };
  });
}

/* --------------------------------------------------------------- reviews */

export async function listReviews(
  tx: WorkspaceScopedDb,
  opts: { profileId: string; limit?: number; now?: Date },
): Promise<ReviewRow[]> {
  const rows = await tx.execute<{
    id: string; author: string | null; rating: number | null; comment: string | null;
    reply: string | null; occurred_at: string;
  }>(sql`
    select id, author, rating, comment, reply, occurred_at::text
      from market_local_reviews where profile_id = ${opts.profileId}
     order by occurred_at desc limit ${opts.limit ?? 50}`);

  return rows
    .map((r) => {
      const { urgency, reason } = triage({
        rating: r.rating,
        comment: r.comment,
        reply: r.reply,
        occurredAt: r.occurred_at,
        now: opts.now,
      });
      return {
        id: r.id,
        author: r.author,
        rating: r.rating,
        comment: r.comment,
        reply: r.reply,
        occurredAt: r.occurred_at,
        urgency,
        reason,
      };
    })
    .sort((a, b) => b.urgency - a.urgency);
}

/**
 * Drafts a reply, or explains that it cannot.
 *
 * The only AI in 4F, and it is genuinely optional: with it off the customer
 * still sees which reviews need answering and why, which is the part that
 * decides whether the work gets done.
 */
export async function draftReply(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; reviewId: string; businessName: string; voice?: string },
  deps: ExecuteDeps,
): Promise<{ draft: string }> {
  const [review] = await tx.execute<{
    rating: number | null; comment: string | null; author: string | null;
  }>(sql`
    select rating, comment, author from market_local_reviews where id = ${opts.reviewId}`);

  if (!review) throw new MarketNotAllowed('not_found', 'That review no longer exists.');

  try {
    const result = await execute(
      tx,
      {
        featureKey: 'market.ai_reply',
        system:
          'You reply to public reviews as the business owner. Be brief, specific and human. ' +
          'Never argue, never repeat the complaint back, never offer compensation you were ' +
          'not told to offer, and never invent facts about what happened.',
        prompt: [
          `Business: ${opts.businessName}.`,
          opts.voice ? `Voice: ${opts.voice}` : '',
          `Rating: ${review.rating ?? 'not given'} out of 5.`,
          review.author ? `From: ${review.author}` : '',
          review.comment ? `They wrote: ${review.comment}` : 'They left no comment.',
          'Write the reply only — no preamble.',
        ].filter(Boolean).join('\n'),
        modality: 'text',
      },
      { workspaceId: opts.workspaceId },
      deps,
    );

    if (!result.ok || !result.text) {
      throw new MarketNotAllowed('generation_failed', result.error ?? 'Nothing came back.');
    }
    return { draft: result.text.trim() };
  } catch (err) {
    if (err instanceof AiUnavailable) {
      throw new MarketNotAllowed(
        'ai_unavailable',
        `${err.message} The review is still listed and still needs a reply — write it yourself for now.`,
      );
    }
    throw err;
  }
}

export async function saveReply(
  tx: WorkspaceScopedDb,
  opts: { profileId: string; reviewId: string; reply: string },
): Promise<void> {
  const reply = opts.reply.trim();
  if (!reply) throw new MarketNotAllowed('invalid', 'An empty reply helps nobody.');

  await tx.execute(sql`
    update market_local_reviews
       set reply = ${reply}, replied_at = now(), updated_at = now()
     where id = ${opts.reviewId} and profile_id = ${opts.profileId}`);
}

export async function saveProfileCategories(
  tx: WorkspaceScopedDb,
  opts: { profileId: string; primaryCategory: string; categories: string[] },
): Promise<void> {
  await tx.execute(sql`
    update market_local_profiles
       set primary_category = ${opts.primaryCategory},
           categories = ${textArray(opts.categories)}::text[],
           updated_at = now()
     where id = ${opts.profileId}`);
}

async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  quantity: number,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const decision = resolveEntitlement(ctx, quantity);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}

export { profileGaps, triage, type LocalProfile, type ReviewRow };
export type { GridRun };
