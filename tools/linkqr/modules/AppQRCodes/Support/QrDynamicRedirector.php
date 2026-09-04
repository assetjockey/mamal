<?php

namespace Modules\AppQRCodes\Support;

use App\Support\RequestGeo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\AppQRCodes\Models\AppQrCode;

class QrDynamicRedirector
{
    public function destination(AppQrCode $qrCode, Request $request): string
    {
        $fallback = $this->safeUrl((string) $qrCode->destination_url) ?: url('/');
        $rules = collect((array) data_get($qrCode->settings, 'dynamic_rules', []))
            ->filter(fn ($rule): bool => is_array($rule) && (bool) data_get($rule, 'enabled', true))
            ->sortBy(fn (array $rule): int => (int) data_get($rule, 'priority', 100))
            ->values();

        foreach ($rules as $rule) {
            if ($this->matches($rule, $request)) {
                return $this->safeUrl((string) data_get($rule, 'url', '')) ?: $fallback;
            }
        }

        return $fallback;
    }

    protected function matches(array $rule, Request $request): bool
    {
        $now = now();
        $country = RequestGeo::country($request);
        $device = $this->deviceFromUserAgent((string) $request->userAgent());
        $hour = (int) $now->format('G');

        $countries = $this->csv(data_get($rule, 'countries'));
        if ($countries !== [] && ! in_array($country, $countries, true)) {
            return false;
        }

        $devices = $this->csv(data_get($rule, 'devices'));
        if ($devices !== [] && ! in_array($device, $devices, true)) {
            return false;
        }

        $startAt = $this->date(data_get($rule, 'start_at'));
        if ($startAt && $now->lt($startAt)) {
            return false;
        }

        $endAt = $this->date(data_get($rule, 'end_at'));
        if ($endAt && $now->gt($endAt)) {
            return false;
        }

        $hours = collect((array) data_get($rule, 'hours', []))
            ->map(fn ($value): int => (int) $value)
            ->filter(fn (int $value): bool => $value >= 0 && $value <= 23)
            ->values()
            ->all();

        return $hours === [] || in_array($hour, $hours, true);
    }

    protected function safeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    protected function csv(mixed $value): array
    {
        return collect(is_array($value) ? $value : explode(',', (string) $value))
            ->map(fn ($item): string => strtoupper(trim((string) $item)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function date(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function deviceFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'TABLET',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'MOBILE',
            $userAgent !== '' => 'DESKTOP',
            default => 'UNKNOWN',
        };
    }
}
