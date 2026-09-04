<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\OAuth;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OutlookOAuth
{
    private const GRAPH_PROFILE_URL = 'https://graph.microsoft.com/v1.0/me';

    public function authorizationUrl(int $userId): string
    {
        $state = $this->cacheState($userId);
        $tenant = setting('microsoft_tenant_id', 'common') ?: 'common';
        $authUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize";

        $scopes = config('ai-agent-outlook.scopes', []);

        $params = array_filter([
            'client_id'     => setting('microsoft_client_id'),
            'redirect_uri'  => $this->redirectUri(),
            'response_type' => 'code',
            'scope'         => implode(' ', $scopes),
            'response_mode' => 'query',
            'prompt'        => 'consent',
            'state'         => $state,
        ], fn ($value) => $value !== null && $value !== '');

        return $authUrl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function exchangeCode(string $code, string $state, int $userId): array
    {
        $this->validateState($state, $userId);

        $tenant = setting('microsoft_tenant_id', 'common') ?: 'common';
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $response = Http::asForm()->post($tokenUrl, [
            'client_id'     => setting('microsoft_client_id'),
            'client_secret' => setting('microsoft_client_secret'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUri(),
        ])->throw();

        return $response->json();
    }

    public function refreshToken(string $refreshToken): Response
    {
        $tenant = setting('microsoft_tenant_id', 'common') ?: 'common';
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        return Http::asForm()->post($tokenUrl, [
            'client_id'     => setting('microsoft_client_id'),
            'client_secret' => setting('microsoft_client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);
    }

    public function getProfile(string $accessToken): array
    {
        return Http::withToken($accessToken)
            ->get(self::GRAPH_PROFILE_URL)
            ->throw()
            ->json();
    }

    private function cacheState(int $userId): string
    {
        $state = (string) Str::uuid();

        cache()->put($this->stateCacheKey($state), ['user_id' => $userId], 600);

        return $state;
    }

    private function validateState(string $state, int $userId): void
    {
        $payload = cache()->pull($this->stateCacheKey($state));

        if (! $payload || (int) data_get($payload, 'user_id') !== $userId) {
            throw new RuntimeException('Invalid or expired OAuth state.');
        }
    }

    private function stateCacheKey(string $state): string
    {
        return 'outlook.oauth.state.' . $state;
    }

    private function redirectUri(): string
    {
        return route('dashboard.user.ai-agent.connectors.outlook.callback');
    }
}
