<?php

namespace Modules\AppShortLinkRules\Support;

use App\Support\RequestGeo;
use Illuminate\Http\Request;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkRuleResolver
{
    public function destination(AppShortLink $shortLink, Request $request): string
    {
        $rules = collect((array) $shortLink->redirect_rules)
            ->filter(fn ($rule): bool => is_array($rule) && filled($rule['url'] ?? null))
            ->reject(fn (array $rule): bool => strtolower((string) ($rule['status'] ?? 'active')) === 'paused')
            ->values();

        if ($rules->isEmpty()) {
            $this->rememberVariant($request, null);

            return (string) $shortLink->destination_url;
        }

        $winner = $rules->first(fn (array $rule): bool => (bool) ($rule['winner'] ?? false));

        if (is_array($winner)) {
            $this->rememberVariant($request, $winner);

            return (string) $winner['url'];
        }

        $country = RequestGeo::country($request);
        $device = $this->deviceFromUserAgent((string) $request->userAgent());
        $now = now();

        $matched = $rules->first(function (array $rule) use ($country, $device, $now): bool {
            $countries = collect((array) ($rule['countries'] ?? []))->map(fn ($item) => strtoupper(trim((string) $item)))->filter();
            $devices = collect((array) ($rule['devices'] ?? []))->map(fn ($item) => strtolower(trim((string) $item)))->filter();
            $startsAt = filled($rule['starts_at'] ?? null) ? rescue(fn () => \Carbon\Carbon::parse((string) $rule['starts_at']), null, false) : null;
            $endsAt = filled($rule['ends_at'] ?? null) ? rescue(fn () => \Carbon\Carbon::parse((string) $rule['ends_at']), null, false) : null;

            if ($countries->isEmpty() && $devices->isEmpty() && $startsAt === null && $endsAt === null) {
                return false;
            }

            return ($countries->isEmpty() || ($country !== '' && $countries->contains($country)))
                && ($devices->isEmpty() || $devices->contains($device))
                && ($startsAt === null || $now->greaterThanOrEqualTo($startsAt))
                && ($endsAt === null || $now->lessThanOrEqualTo($endsAt));
        });

        if (is_array($matched)) {
            $this->rememberVariant($request, $matched);

            return (string) $matched['url'];
        }

        $weighted = $rules
            ->map(fn (array $rule): array => $rule + ['weight' => max(1, (int) ($rule['weight'] ?? 1))])
            ->values();
        $total = (int) $weighted->sum('weight');
        $cursor = random_int(1, max(1, $total));

        foreach ($weighted as $rule) {
            $cursor -= (int) $rule['weight'];
            if ($cursor <= 0) {
                $this->rememberVariant($request, $rule);

                return (string) $rule['url'];
            }
        }

        $this->rememberVariant($request, null);

        return (string) $shortLink->destination_url;
    }

    protected function rememberVariant(Request $request, ?array $rule): void
    {
        $request->attributes->set('short_link_variant', $rule ? [
            'key' => (string) ($rule['variant_key'] ?? ''),
            'name' => (string) ($rule['name'] ?? ''),
            'url' => (string) ($rule['url'] ?? ''),
            'weight' => (int) ($rule['weight'] ?? 0),
            'winner' => (bool) ($rule['winner'] ?? false),
        ] : null);
    }

    protected function deviceFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'mobile',
            default => 'desktop',
        };
    }
}
