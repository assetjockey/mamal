/**
 * Google OAuth, the parts a sync job actually needs.
 *
 * Access tokens live an hour and refresh tokens live until somebody revokes
 * them, so every long-running sync has to be able to refresh mid-flight. That
 * is the whole of this file: exchange, refresh, and tell the difference between
 * "expired" and "revoked" — because one is recoverable without the customer and
 * the other is not.
 */

export type GoogleCredentials = {
  accessToken: string;
  refreshToken?: string;
  /** Epoch milliseconds. */
  expiresAt?: number;
  scopes?: string[];
};

export type OAuthConfig = {
  clientId: string;
  clientSecret: string;
  /** Injectable, so the tests never touch the network. */
  fetch?: typeof globalThis.fetch;
};

export class GoogleAuthError extends Error {
  constructor(
    /** `expired` is recoverable by refreshing; `revoked` needs the customer. */
    readonly reason: 'expired' | 'revoked' | 'misconfigured' | 'network',
    message: string,
  ) {
    super(message);
    this.name = 'GoogleAuthError';
  }
}

const TOKEN_URL = 'https://oauth2.googleapis.com/token';

/**
 * A token that is definitely usable, refreshing first if it is close to expiry.
 *
 * Sixty seconds of headroom rather than checking `expiresAt > now`: a sync that
 * starts with fifty seconds left will make its first request and fail its
 * second, and a mid-flight 401 is far harder to reason about than a refresh
 * that happened slightly early.
 */
export async function freshAccessToken(
  credentials: GoogleCredentials,
  config: OAuthConfig,
): Promise<{ accessToken: string; refreshed: GoogleCredentials | null }> {
  const headroomMs = 60_000;
  const stillGood =
    credentials.expiresAt === undefined || credentials.expiresAt - headroomMs > Date.now();

  if (stillGood) return { accessToken: credentials.accessToken, refreshed: null };

  if (!credentials.refreshToken) {
    throw new GoogleAuthError(
      'revoked',
      'The access token has expired and there is no refresh token. Reconnect the account.',
    );
  }
  const refreshed = await refreshAccessToken(credentials.refreshToken, config);
  return { accessToken: refreshed.accessToken, refreshed };
}

export async function refreshAccessToken(
  refreshToken: string,
  config: OAuthConfig,
): Promise<GoogleCredentials> {
  if (!config.clientId || !config.clientSecret) {
    throw new GoogleAuthError(
      'misconfigured',
      'GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET are not set on this instance.',
    );
  }

  const doFetch = config.fetch ?? globalThis.fetch;
  let response: Response;
  try {
    response = await doFetch(TOKEN_URL, {
      method: 'POST',
      headers: { 'content-type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        client_id: config.clientId,
        client_secret: config.clientSecret,
        refresh_token: refreshToken,
        grant_type: 'refresh_token',
      }),
    });
  } catch (e) {
    // A network failure is not a revoked grant, and treating it as one would
    // disconnect every customer during an outage.
    throw new GoogleAuthError('network', e instanceof Error ? e.message : 'The request failed.');
  }

  const body = (await response.json().catch(() => ({}))) as {
    access_token?: string; expires_in?: number; scope?: string; error?: string;
    error_description?: string;
  };

  if (!response.ok) {
    /*
     * `invalid_grant` is the one that means "the customer revoked us" — or
     * changed their password, or the token has been unused for six months.
     * Everything else is worth retrying; this one is not, and telling them
     * apart is the difference between an alert and a retry loop.
     */
    const revoked = body.error === 'invalid_grant';
    throw new GoogleAuthError(
      revoked ? 'revoked' : 'network',
      body.error_description ?? body.error ?? `Token refresh answered ${response.status}.`,
    );
  }

  if (!body.access_token) {
    throw new GoogleAuthError('network', 'Token refresh returned no access token.');
  }

  return {
    accessToken: body.access_token,
    // Google does not reissue the refresh token, so it has to be carried over
    // — dropping it here turns every hour into a reconnect.
    refreshToken,
    expiresAt: Date.now() + (body.expires_in ?? 3600) * 1000,
    scopes: body.scope?.split(' '),
  };
}
