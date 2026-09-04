<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client as GuzzleClient;

trait Webhookable
{
    /**
     * Send a webhook POST request to a given URL.
     */
    public function sendWebhook(array $data, ?string $url): void
    {
        if (!empty($url)) {
            try {
                $httpClient = new GuzzleClient();

                $headers = [
                    'Authorization' => 'Bearer ' . config('settings.webhook_secret_key')
                ];

                $httpClient->request('POST', $url, [
                    'timeout' => 5,
                    'headers' => $headers,
                    'form_params' => $data,
                ]);
            } catch (Exception) {}
        }
    }
}
