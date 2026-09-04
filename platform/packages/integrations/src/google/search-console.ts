import { freshAccessToken, GoogleAuthError, type GoogleCredentials, type OAuthConfig } from './oauth.ts';

/**
 * Google Search Console, the search-analytics half.
 *
 * Three facts about this API shape everything here, and getting any of them
 * wrong produces data that looks right and is not:
 *
 * **It lags.** Rows for a day appear two to three days later, and arrive
 * partial before they are complete. Syncing "yesterday" gets a fraction of the
 * truth and then never revisits it.
 *
 * **It restates.** The last two to three days are revised as late attribution
 * lands. A sync that appends rather than upserts ends up double-counting.
 *
 * **It pages.** 25,000 rows a request, and a mid-sized site exceeds that in a
 * week. Stopping at the first page silently truncates the long tail — which is
 * exactly where the opportunities are.
 */

export type SearchAnalyticsRow = {
  query: string;
  page: string;
  device: string;
  country: string;
  clicks: number;
  impressions: number;
  position: number;
  date: string;
};

export type SearchConsoleClient = {
  /** Everything for one day, paged to exhaustion. */
  queryDay(siteUrl: string, date: string): Promise<SearchAnalyticsRow[]>;
  /** The properties this account can read. */
  listSites(): Promise<{ siteUrl: string; permissionLevel: string }[]>;
  /** Set when a call refreshed the token; the caller must persist it. */
  refreshedCredentials(): GoogleCredentials | null;
};

export class SearchConsoleError extends Error {
  constructor(
    readonly reason: 'unauthorised' | 'forbidden' | 'rate_limited' | 'not_found' | 'server',
    message: string,
    readonly retryAfterSeconds?: number,
  ) {
    super(message);
    this.name = 'SearchConsoleError';
  }
}

/** GSC data is not complete until roughly three days later. */
export const LAG_DAYS = 3;

/**
 * How many recent days to re-fetch even when they are already stored.
 *
 * Covers the restatement window: those days keep changing, and a sync that
 * only ever moves forward leaves the most recent — and most interesting — data
 * permanently understated.
 */
export const RESTATEMENT_DAYS = 5;

const API = 'https://searchconsole.googleapis.com/webmasters/v3';
const ROW_LIMIT = 25_000;

export function searchConsoleClient(
  credentials: GoogleCredentials,
  config: OAuthConfig,
): SearchConsoleClient {
  const doFetch = config.fetch ?? globalThis.fetch;
  let refreshed: GoogleCredentials | null = null;
  let current = credentials;

  async function call<T>(path: string, init?: RequestInit): Promise<T> {
    const token = await freshAccessToken(current, config);
    if (token.refreshed) {
      refreshed = token.refreshed;
      current = token.refreshed;
    }

    let response: Response;
    try {
      response = await doFetch(`${API}${path}`, {
        ...init,
        headers: {
          ...init?.headers,
          authorization: `Bearer ${token.accessToken}`,
          'content-type': 'application/json',
        },
      });
    } catch (e) {
      throw new SearchConsoleError('server', e instanceof Error ? e.message : 'The request failed.');
    }

    if (!response.ok) {
      const body = (await response.json().catch(() => ({}))) as {
        error?: { message?: string; status?: string };
      };
      const message = body.error?.message ?? `Search Console answered ${response.status}.`;

      /*
       * These map to different *actions*, which is why they are distinguished
       * rather than collapsed into "the sync failed":
       *   401 → refresh, or reconnect if that fails
       *   403 → the account lost access to the property; only they can fix it
       *   429 → back off; nothing is wrong
       */
      if (response.status === 401) throw new SearchConsoleError('unauthorised', message);
      if (response.status === 403) throw new SearchConsoleError('forbidden', message);
      if (response.status === 429) {
        const retryAfter = Number(response.headers.get('retry-after') ?? 60);
        throw new SearchConsoleError('rate_limited', message, Number.isFinite(retryAfter) ? retryAfter : 60);
      }
      if (response.status === 404) throw new SearchConsoleError('not_found', message);
      throw new SearchConsoleError('server', message);
    }

    return (await response.json()) as T;
  }

  return {
    refreshedCredentials: () => refreshed,

    async listSites() {
      const body = await call<{ siteEntry?: { siteUrl: string; permissionLevel: string }[] }>(
        '/sites',
        { method: 'GET' },
      );
      return body.siteEntry ?? [];
    },

    async queryDay(siteUrl, date) {
      const rows: SearchAnalyticsRow[] = [];
      let startRow = 0;

      for (;;) {
        const body = await call<{
          rows?: { keys: string[]; clicks: number; impressions: number; ctr: number; position: number }[];
        }>(`/sites/${encodeURIComponent(siteUrl)}/searchAnalytics/query`, {
          method: 'POST',
          body: JSON.stringify({
            startDate: date,
            endDate: date,
            // Order matters: it is how the flat `keys` array is read back.
            dimensions: ['query', 'page', 'device', 'country'],
            rowLimit: ROW_LIMIT,
            startRow,
            /*
             * `dataState: 'final'` excludes the still-settling rows.
             *
             * The alternative, `all`, includes fresh partial data — which
             * looks like a traffic collapse the moment it is compared against
             * a complete day.
             */
            dataState: 'final',
          }),
        });

        const page = body.rows ?? [];
        for (const row of page) {
          const [query, url, device, country] = row.keys;
          rows.push({
            query: query ?? '',
            page: url ?? '',
            device: (device ?? 'DESKTOP').toLowerCase(),
            country: country ?? '',
            clicks: row.clicks,
            impressions: row.impressions,
            position: row.position,
            date,
          });
        }

        // A short page is the last page. Asking again would cost a request to
        // be told the same thing.
        if (page.length < ROW_LIMIT) break;
        startRow += ROW_LIMIT;
      }

      return rows;
    },
  };
}

/**
 * The days worth fetching.
 *
 * Two rules, both learned from the API's behaviour rather than from a spec:
 * never ask for anything inside the lag window, and always re-ask for the
 * restatement window even when it is already stored. Everything between the
 * last complete day and the backfill horizon is fetched once.
 */
export function daysToSync(opts: {
  today: Date;
  /** The most recent day already stored, if any. */
  latestStored: string | null;
  /** How far back to go on a first sync. */
  backfillDays?: number;
  lagDays?: number;
  restatementDays?: number;
}): string[] {
  const lag = opts.lagDays ?? LAG_DAYS;
  const restatement = opts.restatementDays ?? RESTATEMENT_DAYS;
  const backfill = opts.backfillDays ?? 90;

  const iso = (d: Date) => d.toISOString().slice(0, 10);
  const shift = (days: number) =>
    iso(new Date(Date.UTC(
      opts.today.getUTCFullYear(), opts.today.getUTCMonth(), opts.today.getUTCDate() - days,
    )));

  const newest = shift(lag);
  const oldest = opts.latestStored
    // Re-ask for the restatement window; those days are still moving.
    ? minIso(shift(restatement + lag), nextDay(opts.latestStored))
    : shift(backfill);

  const out: string[] = [];
  for (let cursor = oldest; cursor <= newest; cursor = nextDay(cursor)) out.push(cursor);
  return out;
}

function nextDay(date: string): string {
  const d = new Date(`${date}T00:00:00Z`);
  d.setUTCDate(d.getUTCDate() + 1);
  return d.toISOString().slice(0, 10);
}

const minIso = (a: string, b: string) => (a < b ? a : b);

export { GoogleAuthError };
