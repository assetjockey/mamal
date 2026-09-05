/**
 * What each network will actually accept.
 *
 * This is 4D's catalogue, and it exists so the composer can refuse *before*
 * scheduling rather than after. A post queued at 09:00 and rejected by
 * Instagram at 14:00 because it had no image is the single most common
 * complaint about every tool in this category: the failure arrives hours late,
 * on somebody else's clock, in a log nobody reads.
 *
 * Two things here are more subtle than they look.
 *
 * **Counting characters is not `text.length`.** JavaScript counts UTF-16 code
 * units, so an emoji is 2 and a flag is 4 — a post that says 279 in our UI is
 * rejected at 280 by X. Networks count code points (and X additionally counts
 * every URL as a fixed 23 regardless of length, because it wraps them). Getting
 * this wrong is invisible in English and immediate in any post with an emoji.
 *
 * **Some networks cannot take text.** Instagram, TikTok, YouTube and Pinterest
 * are media-first: a text-only post is not a short post, it is an impossible
 * one. That is a validation error, not a warning.
 */

export type MediaKind = 'image' | 'video' | 'none';

export type Network = {
  key: string;
  label: string;
  /** Maximum body length, in whatever the network counts. */
  maxBody: number;
  /** Every URL counts as this many characters, whatever its length. */
  urlWeight: number | null;
  maxImages: number;
  maxVideos: number;
  /** True when a post cannot exist without media. */
  requiresMedia: boolean;
  /** Some networks demand a destination URL — Pinterest is useless without one. */
  requiresLink: boolean;
  maxHashtags: number | null;
  /** Long bodies can be split across a thread rather than refused. */
  supportsThread: boolean;
  /** A separate title field, and its limit. */
  titleLimit: number | null;
  altTextLimit: number | null;
  supportsFirstComment: boolean;
};

/**
 * The nine channels the plan names.
 *
 * Limits are the published ones at the time of writing. They move, so they are
 * data in one place rather than conditionals spread through the composer.
 */
export const NETWORKS: Record<string, Network> = {
  x: {
    key: 'x', label: 'X', maxBody: 280,
    // X wraps every link in t.co, so a 200-character URL costs 23 and a
    // 12-character one also costs 23. Counting the real length under-reports
    // for short links and over-reports for long ones.
    urlWeight: 23,
    maxImages: 4, maxVideos: 1, requiresMedia: false, requiresLink: false,
    maxHashtags: null, supportsThread: true, titleLimit: null,
    altTextLimit: 1000, supportsFirstComment: false,
  },
  facebook: {
    key: 'facebook', label: 'Facebook', maxBody: 63_206, urlWeight: null,
    maxImages: 10, maxVideos: 1, requiresMedia: false, requiresLink: false,
    maxHashtags: null, supportsThread: false, titleLimit: null,
    altTextLimit: 1000, supportsFirstComment: true,
  },
  instagram: {
    key: 'instagram', label: 'Instagram', maxBody: 2200, urlWeight: null,
    maxImages: 10, maxVideos: 1,
    // Not a short post — an impossible one.
    requiresMedia: true, requiresLink: false,
    maxHashtags: 30, supportsThread: false, titleLimit: null,
    altTextLimit: 1000, supportsFirstComment: true,
  },
  threads: {
    key: 'threads', label: 'Threads', maxBody: 500, urlWeight: null,
    maxImages: 20, maxVideos: 1, requiresMedia: false, requiresLink: false,
    maxHashtags: null, supportsThread: true, titleLimit: null,
    altTextLimit: 1000, supportsFirstComment: false,
  },
  linkedin: {
    key: 'linkedin', label: 'LinkedIn', maxBody: 3000, urlWeight: null,
    maxImages: 20, maxVideos: 1, requiresMedia: false, requiresLink: false,
    maxHashtags: null, supportsThread: false, titleLimit: null,
    altTextLimit: 300, supportsFirstComment: true,
  },
  tiktok: {
    key: 'tiktok', label: 'TikTok', maxBody: 2200, urlWeight: null,
    maxImages: 35, maxVideos: 1, requiresMedia: true, requiresLink: false,
    maxHashtags: null, supportsThread: false, titleLimit: null,
    altTextLimit: null, supportsFirstComment: true,
  },
  youtube: {
    key: 'youtube', label: 'YouTube', maxBody: 5000, urlWeight: null,
    maxImages: 0, maxVideos: 1, requiresMedia: true, requiresLink: false,
    maxHashtags: 15, supportsThread: false, titleLimit: 100,
    altTextLimit: null, supportsFirstComment: true,
  },
  pinterest: {
    key: 'pinterest', label: 'Pinterest', maxBody: 500, urlWeight: null,
    maxImages: 1, maxVideos: 1, requiresMedia: true,
    // A pin with nowhere to go is a picture; the whole mechanic is the click.
    requiresLink: true,
    maxHashtags: 20, supportsThread: false, titleLimit: 100,
    altTextLimit: 500, supportsFirstComment: false,
  },
  google_business: {
    key: 'google_business', label: 'Google Business', maxBody: 1500, urlWeight: null,
    maxImages: 1, maxVideos: 0, requiresMedia: false, requiresLink: false,
    maxHashtags: null, supportsThread: false, titleLimit: 58,
    altTextLimit: null, supportsFirstComment: false,
  },
};

export function networkFor(provider: string): Network | null {
  return NETWORKS[provider] ?? null;
}

/* ------------------------------------------------------------- counting */

const URL_PATTERN = /https?:\/\/[^\s<>()[\]"']+|(?<![@\w.])(?:[a-z0-9-]+\.)+[a-z]{2,}(?:\/[^\s]*)?/gi;

/**
 * Characters as the network counts them.
 *
 * Code points, not UTF-16 units: `[...text].length` treats an emoji as one
 * where `text.length` says two. Anything else means the count in our UI and the
 * count that gets the post rejected are different numbers.
 *
 * Where `urlWeight` is set, each URL contributes that fixed cost instead of its
 * own length — X wraps links, so a long URL is cheap and a short one is not
 * free.
 */
export function countCharacters(text: string, network: Network): number {
  if (network.urlWeight === null) return [...text].length;

  let total = 0;
  let cursor = 0;
  for (const match of text.matchAll(URL_PATTERN)) {
    const start = match.index ?? 0;
    total += [...text.slice(cursor, start)].length + network.urlWeight;
    cursor = start + match[0].length;
  }
  return total + [...text.slice(cursor)].length;
}

const HASHTAG = /(?<![\p{L}\p{N}_])#([\p{L}\p{N}_]+)/gu;

/**
 * The distinct hashtags in the body — for showing the writer what they used.
 *
 * Deliberately *not* what the limit is checked against: see `hashtagCount`.
 */
export function hashtagsIn(text: string): string[] {
  return [...new Set([...text.matchAll(HASHTAG)].map((m) => m[1]!.toLowerCase()))];
}

/**
 * Total hashtag *occurrences*, which is what the networks count.
 *
 * The distinction matters: a caption repeating `#sale` thirty times is thirty
 * hashtags to Instagram and one to a reader. Checking the limit against the
 * distinct set would wave through exactly the spam the limit exists to stop.
 */
export function hashtagCount(text: string): number {
  return [...text.matchAll(HASHTAG)].length;
}

/* ----------------------------------------------------------- validation */

export type Problem = {
  /** `error` blocks scheduling; `warning` is advice the writer may ignore. */
  level: 'error' | 'warning';
  network: string;
  message: string;
};

export type PostDraft = {
  body: string;
  title?: string;
  images?: number;
  videos?: number;
  link?: string | null;
  /** Alt text per image; a short array means some images have none. */
  altText?: string[];
  firstComment?: string;
};

/**
 * Everything wrong with this post, for these networks, before it is scheduled.
 *
 * Returns problems rather than throwing on the first, because the composer
 * shows all of them at once — fixing five in five round trips is how people
 * give up on a scheduler.
 */
export function validatePost(draft: PostDraft, providers: string[]): Problem[] {
  const problems: Problem[] = [];
  const images = draft.images ?? 0;
  const videos = draft.videos ?? 0;
  const hashtags = hashtagCount(draft.body);

  for (const provider of providers) {
    const network = networkFor(provider);
    if (!network) {
      problems.push({
        level: 'error', network: provider,
        message: `${provider} is not a network we can publish to.`,
      });
      continue;
    }

    const count = countCharacters(draft.body, network);
    if (count > network.maxBody) {
      const over = count - network.maxBody;
      problems.push({
        level: 'error', network: network.key,
        message: network.supportsThread
          ? `${over} over ${network.label}'s ${network.maxBody}. Split it into a thread, or shorten it.`
          : `${over} over ${network.label}'s limit of ${network.maxBody}.`,
      });
    }

    if (network.requiresMedia && images === 0 && videos === 0) {
      problems.push({
        level: 'error', network: network.key,
        // Stated as a fact about the network, not a rule of ours.
        message: `${network.label} posts cannot be text only — add an image or a video.`,
      });
    }
    if (network.requiresLink && !draft.link) {
      problems.push({
        level: 'error', network: network.key,
        message: `${network.label} needs a destination link, or the post goes nowhere.`,
      });
    }

    if (images > network.maxImages) {
      problems.push({
        level: 'error', network: network.key,
        message: network.maxImages === 0
          ? `${network.label} does not take images.`
          : `${network.label} takes at most ${network.maxImages} image${network.maxImages === 1 ? '' : 's'}; this has ${images}.`,
      });
    }
    if (videos > network.maxVideos) {
      problems.push({
        level: 'error', network: network.key,
        message: network.maxVideos === 0
          ? `${network.label} does not take video.`
          : `${network.label} takes one video.`,
      });
    }
    if (images > 0 && videos > 0) {
      problems.push({
        level: 'error', network: network.key,
        // True of every network here, and the mistake is easy to make when one
        // post fans out to nine.
        message: `${network.label} cannot mix images and video in one post.`,
      });
    }

    if (network.maxHashtags !== null && hashtags > network.maxHashtags) {
      problems.push({
        level: 'error', network: network.key,
        message: `${hashtags} hashtags; ${network.label} allows ${network.maxHashtags}.`,
      });
    }

    if (network.titleLimit !== null) {
      const title = draft.title?.trim() ?? '';
      if (title.length === 0) {
        problems.push({
          level: 'error', network: network.key,
          message: `${network.label} needs a title.`,
        });
      } else if ([...title].length > network.titleLimit) {
        problems.push({
          level: 'error', network: network.key,
          message: `The title is over ${network.label}'s ${network.titleLimit} characters.`,
        });
      }
    }

    if (draft.firstComment && !network.supportsFirstComment) {
      problems.push({
        level: 'warning', network: network.key,
        message: `${network.label} has no first comment — it will be dropped there.`,
      });
    }

    /*
     * Alt text is a warning rather than an error: a post without it publishes
     * fine and blocking somebody's launch over accessibility they can add later
     * would just teach them to turn the check off. Saying nothing would be
     * worse.
     */
    if (network.altTextLimit !== null && images > 0) {
      const described = (draft.altText ?? []).filter((t) => t.trim().length > 0).length;
      if (described < images) {
        problems.push({
          level: 'warning', network: network.key,
          message: `${images - described} of ${images} images have no alt text.`,
        });
      }
    }
  }

  return problems;
}

/** Convenience: can this be scheduled at all? */
export function canSchedule(problems: Problem[]): boolean {
  return !problems.some((p) => p.level === 'error');
}

/* --------------------------------------------------------------- threads */

/**
 * Splits a long body into thread posts.
 *
 * Splits on paragraph, then sentence, then word — never mid-word, and never
 * mid-URL, because half a link is worse than a shorter post. Each part carries
 * an "n/total" counter, which costs characters and is therefore budgeted for
 * *before* splitting rather than appended after and blowing the limit.
 */
export function splitThread(body: string, network: Network): string[] {
  if (!network.supportsThread) return [body];
  if (countCharacters(body, network) <= network.maxBody) return [body];

  // Reserve room for " 10/10" — worst case at two digits, which covers any
  // thread anybody should be writing.
  const budget = network.maxBody - 6;
  const parts: string[] = [];
  let current = '';

  const flush = () => {
    if (current.trim().length > 0) parts.push(current.trim());
    current = '';
  };

  for (const paragraph of body.split(/\n{2,}/)) {
    for (const chunk of chunksOf(paragraph.trim(), budget, network)) {
      const candidate = current.length === 0 ? chunk : `${current}\n\n${chunk}`;
      if (countCharacters(candidate, network) <= budget) {
        current = candidate;
      } else {
        flush();
        current = chunk;
      }
    }
  }
  flush();

  const total = parts.length;
  return total <= 1 ? parts : parts.map((part, i) => `${part} ${i + 1}/${total}`);
}

/** Breaks one paragraph into pieces that fit, on sentence then word bounds. */
function chunksOf(paragraph: string, budget: number, network: Network): string[] {
  if (countCharacters(paragraph, network) <= budget) return [paragraph];

  const out: string[] = [];
  let current = '';

  // Sentence-ish: keeps the terminator with the sentence it ends.
  const sentences = paragraph.match(/[^.!?]+[.!?]+[\s]*|[^.!?]+$/g) ?? [paragraph];

  for (const sentence of sentences) {
    const candidate = current + sentence;
    if (countCharacters(candidate, network) <= budget) {
      current = candidate;
      continue;
    }
    if (current.trim()) out.push(current.trim());

    if (countCharacters(sentence, network) <= budget) {
      current = sentence;
      continue;
    }

    // A single sentence too long for a post: fall back to words. A URL is one
    // "word" and is never broken.
    current = '';
    for (const word of sentence.split(/\s+/)) {
      const withWord = current.length === 0 ? word : `${current} ${word}`;
      if (countCharacters(withWord, network) <= budget) {
        current = withWord;
      } else {
        if (current.trim()) out.push(current.trim());
        current = word;
      }
    }
  }

  if (current.trim()) out.push(current.trim());
  return out;
}
