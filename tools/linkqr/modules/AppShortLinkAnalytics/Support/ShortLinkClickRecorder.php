<?php

namespace Modules\AppShortLinkAnalytics\Support;

use App\Support\RequestGeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AdminUser\Models\User;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinkApi\Support\ShortLinkWebhookDispatcher;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkClickRecorder
{
    public function record(Request $request, AppShortLink $shortLink): void
    {
        $owner = User::query()->find((int) $shortLink->owner_user_id);

        if (! app(ShortLinkPlanLimits::class)->canRecordClick($owner, (int) $shortLink->owner_user_id)) {
            return;
        }

        $country = RequestGeo::country($request);
        $city = RequestGeo::city($request);
        $region = RequestGeo::region($request);

        AppShortLinkClick::query()->create([
            'app_short_link_id' => (int) $shortLink->id,
            'owner_user_id' => (int) $shortLink->owner_user_id,
            'ip_address' => $request->ip() ? hash('sha256', (string) $request->ip()) : null,
            'user_agent' => (string) $request->userAgent(),
            'referer' => (string) $request->headers->get('referer', ''),
            'country' => $country,
            'metadata' => [
                'host' => $request->getHost(),
                'country' => $country,
                'city' => $city,
                'region' => $region,
                'timezone' => RequestGeo::timezone($request),
                'variant' => $request->attributes->get('short_link_variant'),
                'language' => $this->languageFromHeader((string) $request->headers->get('accept-language', '')),
                'browser' => $this->browserFromUserAgent((string) $request->userAgent()),
                'os' => $this->osFromUserAgent((string) $request->userAgent()),
                'device' => $this->deviceFromUserAgent((string) $request->userAgent()),
                'is_bot' => $this->isBot((string) $request->userAgent()),
            ],
            'created_at' => now(),
        ]);

        $shortLink->newQuery()
            ->whereKey($shortLink->id)
            ->update([
                'clicks_count' => DB::raw('clicks_count + 1'),
                'last_clicked_at' => now(),
            ]);

        app(ShortLinkWebhookDispatcher::class)->dispatch((int) $shortLink->owner_user_id, 'short_link.clicked', $shortLink);
    }

    protected function browserFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Unknown',
        };
    }

    protected function osFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    protected function deviceFromUserAgent(string $userAgent): string
    {
        return match (true) {
            $this->isBot($userAgent) => 'Bot',
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'Tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'Mobile',
            $userAgent !== '' => 'Desktop',
            default => 'Unknown',
        };
    }

    protected function languageFromHeader(string $header): string
    {
        $language = trim(explode(',', $header)[0] ?? '');

        if ($language === '') {
            return 'Unknown';
        }

        $language = preg_replace('/;q=[0-9.]+$/i', '', $language) ?: $language;

        return substr($language, 0, 16);
    }

    protected function isBot(string $userAgent): bool
    {
        $userAgent = strtolower($userAgent);

        return str_contains($userAgent, 'bot')
            || str_contains($userAgent, 'crawler')
            || str_contains($userAgent, 'spider')
            || str_contains($userAgent, 'preview')
            || str_contains($userAgent, 'facebookexternalhit');
    }
}
