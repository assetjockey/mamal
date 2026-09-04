<?php

namespace Modules\AppShortLinkApi\Support;

use Illuminate\Support\Facades\Http;
use Modules\AppShortLinkApi\Models\AppShortLinkWebhook;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkWebhookDispatcher
{
    public function dispatch(int $ownerUserId, string $event, AppShortLink $shortLink, array $payload = []): void
    {
        AppShortLinkWebhook::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('is_active', true)
            ->get()
            ->filter(fn (AppShortLinkWebhook $webhook): bool => in_array($event, (array) $webhook->events, true))
            ->each(function (AppShortLinkWebhook $webhook) use ($event, $shortLink, $payload): void {
                $body = [
                    'event' => $event,
                    'short_link' => [
                        'id' => $shortLink->id,
                        'name' => $shortLink->name,
                        'short_code' => $shortLink->short_code,
                        'short_url' => $shortLink->shortUrl(),
                        'destination_url' => $shortLink->destination_url,
                    ],
                    'payload' => $payload,
                    'sent_at' => now()->toIso8601String(),
                ];
                $signature = hash_hmac('sha256', json_encode($body), (string) $webhook->secret);

                try {
                    Http::timeout(6)
                        ->withHeaders(['X-ShortLink-Signature' => $signature])
                        ->post((string) $webhook->url, $body);

                    $webhook->update(['last_delivered_at' => now()]);
                } catch (\Throwable) {
                    //
                }
            });
    }
}
