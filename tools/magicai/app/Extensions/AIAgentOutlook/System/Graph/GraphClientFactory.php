<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Graph;

use App\Extensions\AIAgentGmail\System\Models\AIAgentConnector;
use App\Extensions\AIAgentOutlook\System\OAuth\OutlookOAuth;
use Microsoft\Graph\Graph;
use RuntimeException;

class GraphClientFactory
{
    public function __construct(private readonly OutlookOAuth $oauth) {}

    public function make(int $userId): Graph
    {
        $connector = AIAgentConnector::query()
            ->where('user_id', $userId)
            ->where('type', 'outlook')
            ->where('is_active', true)
            ->firstOrFail();

        $credentials = $connector->credentials ?? [];
        $accessToken = data_get($credentials, 'access_token');
        $refreshToken = data_get($credentials, 'refresh_token');
        $expiresAt = data_get($credentials, 'expires_at');

        if (! $accessToken) {
            throw new RuntimeException('Outlook connector has no access token. Please reconnect.');
        }

        if ($expiresAt && now()->gte($expiresAt) && $refreshToken) {
            $tokenResponse = $this->oauth->refreshToken($refreshToken);

            if ($tokenResponse->successful()) {
                $tokenData = $tokenResponse->json();
                $accessToken = data_get($tokenData, 'access_token', $accessToken);
                $expiresIn = (int) data_get($tokenData, 'expires_in', 3600);

                $newCredentials = array_merge($credentials, [
                    'access_token' => $accessToken,
                    'expires_at'   => now()->addSeconds($expiresIn)->toDateTimeString(),
                ]);

                $connector->update(['credentials' => $newCredentials, 'expires_at' => now()->addSeconds($expiresIn)]);
            }
        }

        $graph = new Graph;
        $graph->setAccessToken($accessToken);

        return $graph;
    }
}
