<?php

namespace Modules\AppShortLinkBranding\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\AppBrandKit\Models\AppBrandKit;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AppShortLinkRules\Support\ShortLinkRuleResolver;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkBranding
{
    public function __construct(
        protected BrandOperations $brandOperations,
    ) {}

    public function domains(int $ownerUserId): Collection
    {
        return $this->brandOperations->domains($ownerUserId)
            ->where('status', 'verified')
            ->values();
    }

    public function utmPresets(int $ownerUserId): Collection
    {
        return $this->brandOperations->utmPresets($ownerUserId);
    }

    public function defaultUtmPreset(int $ownerUserId): ?object
    {
        return $this->brandOperations->utmPreset(null, $ownerUserId);
    }

    public function pixels(int $ownerUserId): Collection
    {
        return $this->brandOperations->pixels($ownerUserId);
    }

    public function defaultPixelIds(int $ownerUserId): array
    {
        return $this->brandOperations->defaultPixelIds($ownerUserId);
    }

    public function brandKit(int $ownerUserId): ?AppBrandKit
    {
        return $this->brandOperations->defaultBrandKit($ownerUserId);
    }

    public function visualSettings(int $ownerUserId): array
    {
        $kit = $this->brandKit($ownerUserId);

        return [
            'brand_name' => (string) ($kit?->brand_name ?: config('app.name', 'Brand')),
            'logo_url' => (string) ($kit?->logo_url ?: ''),
            'primary_color' => $this->hex((string) ($kit?->primary_color ?: '#2563eb'), '#2563eb'),
            'secondary_color' => $this->hex((string) ($kit?->secondary_color ?: '#0f172a'), '#0f172a'),
            'accent_color' => $this->hex((string) ($kit?->accent_color ?: '#10b981'), '#10b981'),
            'font_family' => (string) ($kit?->font_family ?: 'Inter'),
        ];
    }

    public function qrOptions(int $ownerUserId): array
    {
        $visual = $this->visualSettings($ownerUserId);

        return [
            'foreground_color' => $visual['primary_color'],
            'accent_color' => $visual['accent_color'],
            'finder_color' => $visual['secondary_color'],
            'finder_dot_color' => $visual['primary_color'],
            'background_color' => '#ffffff',
            'logo_url' => $visual['logo_url'],
            'ecc' => 'high',
        ];
    }

    public function shortUrl(AppShortLink $shortLink): string
    {
        $fallback = route('short-links.public.redirect', ['code' => $shortLink->short_code]);
        $domain = $shortLink->custom_domain_id
            ? $this->brandOperations->verifiedDomain((int) $shortLink->custom_domain_id, (int) $shortLink->owner_user_id)
            : null;

        return $this->brandOperations->customUrl($domain, 's/'.$shortLink->short_code, $fallback);
    }

    public function destination(AppShortLink $shortLink, ?Request $request = null): string
    {
        $destination = $request
            ? app(ShortLinkRuleResolver::class)->destination($shortLink, $request)
            : (string) $shortLink->destination_url;

        return $this->brandOperations->applyUtm(
            $destination,
            $this->brandOperations->utmPreset((int) $shortLink->utm_preset_id, (int) $shortLink->owner_user_id)
        );
    }

    public function pixelSnippets(AppShortLink $shortLink): array
    {
        $pixels = $this->brandOperations->activePixels(
            (array) $shortLink->tracking_pixel_ids,
            (int) $shortLink->owner_user_id
        );

        return $this->brandOperations->pixelSnippets($pixels);
    }

    protected function hex(string $value, string $fallback): string
    {
        $value = strtolower(trim($value));
        $value = str_starts_with($value, '#') ? $value : '#'.$value;

        return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : $fallback;
    }
}
