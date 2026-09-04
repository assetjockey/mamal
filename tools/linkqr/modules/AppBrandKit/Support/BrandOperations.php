<?php

namespace Modules\AppBrandKit\Support;

use Illuminate\Support\Collection;
use Modules\AppBrandKit\Models\AppBrandKit;
use Modules\AppCustomDomain\Models\AppCustomDomain;
use Modules\AppTrackingPixels\Models\AppTrackingPixel;
use Modules\AppUTMPresets\Models\AppUtmPreset;

class BrandOperations
{
    public function brandKits(int $ownerUserId): Collection
    {
        return AppBrandKit::query()
            ->where('owner_user_id', $ownerUserId)
            ->orderBy('brand_name')
            ->get();
    }

    public function defaultBrandKit(int $ownerUserId): ?AppBrandKit
    {
        return AppBrandKit::query()
            ->where('owner_user_id', $ownerUserId)
            ->latest()
            ->first();
    }

    public function domains(int $ownerUserId): Collection
    {
        return AppCustomDomain::query()
            ->where('owner_user_id', $ownerUserId)
            ->orderByDesc('is_default')
            ->orderByRaw("case when status = 'verified' then 0 else 1 end")
            ->orderBy('domain')
            ->get();
    }

    public function linkBioDomains(int $ownerUserId): Collection
    {
        return $this->domains($ownerUserId);
    }

    public function verifiedDomain(?int $domainId, int $ownerUserId): ?AppCustomDomain
    {
        if (! $domainId) {
            return null;
        }

        return AppCustomDomain::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('status', 'verified')
            ->find($domainId);
    }

    public function defaultQrDomain(int $ownerUserId): ?AppCustomDomain
    {
        return AppCustomDomain::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('status', 'verified')
            ->where('is_default', true)
            ->first();
    }

    public function verifiedLinkBioDomain(?int $domainId, int $ownerUserId): ?AppCustomDomain
    {
        return $this->verifiedDomain($domainId, $ownerUserId);
    }

    public function defaultLinkBioDomain(int $ownerUserId): ?AppCustomDomain
    {
        return $this->defaultQrDomain($ownerUserId);
    }

    public function utmPresets(int $ownerUserId): Collection
    {
        return AppUtmPreset::query()
            ->where('owner_user_id', $ownerUserId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function pixels(int $ownerUserId): Collection
    {
        return AppTrackingPixel::query()
            ->where('owner_user_id', $ownerUserId)
            ->orderByDesc('is_default')
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('provider')
            ->orderBy('name')
            ->get();
    }

    public function defaultPixelIds(int $ownerUserId): array
    {
        return AppTrackingPixel::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('status', 'active')
            ->where('is_default', true)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    public function brandKit(?int $brandKitId, int $ownerUserId): ?AppBrandKit
    {
        if (! $brandKitId) {
            return null;
        }

        return AppBrandKit::query()
            ->where('owner_user_id', $ownerUserId)
            ->find($brandKitId);
    }

    public function utmPreset(?int $utmPresetId, int $ownerUserId): ?AppUtmPreset
    {
        if (! $utmPresetId) {
            return AppUtmPreset::query()
                ->where('owner_user_id', $ownerUserId)
                ->where('is_default', true)
                ->first();
        }

        return AppUtmPreset::query()
            ->where('owner_user_id', $ownerUserId)
            ->find($utmPresetId);
    }

    public function activePixels(array $pixelIds, int $ownerUserId): Collection
    {
        $pixelIds = collect($pixelIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($pixelIds === []) {
            return collect();
        }

        return AppTrackingPixel::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('status', 'active')
            ->whereIn('id', $pixelIds)
            ->get();
    }

    public function applyUtm(string $url, ?AppUtmPreset $preset, array $overrides = []): string
    {
        $url = trim($url);

        if ($url === '' || $preset === null || ! str_starts_with($url, 'http')) {
            return $url;
        }

        $params = array_filter(array_merge([
            'utm_source' => $preset->source,
            'utm_medium' => $preset->medium,
            'utm_campaign' => $preset->campaign,
            'utm_term' => $preset->term,
            'utm_content' => $preset->content,
        ], $overrides), fn ($value): bool => filled($value));

        if ($params === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($params);
    }

    public function customUrl(object|null $domain, string $path, string $fallback): string
    {
        if ($domain === null || trim((string) $domain->domain) === '') {
            return $fallback;
        }

        return 'https://'.trim((string) $domain->domain, '/').'/'.ltrim($path, '/');
    }

    public function pixelSnippets(Collection $pixels): array
    {
        return $pixels
            ->map(fn (AppTrackingPixel $pixel): ?string => $this->pixelSnippet($pixel))
            ->filter()
            ->values()
            ->all();
    }

    protected function pixelSnippet(AppTrackingPixel $pixel): ?string
    {
        $id = trim((string) $pixel->pixel_id);

        if ($id === '') {
            return null;
        }

        return match (strtolower((string) $pixel->provider)) {
            'meta', 'facebook' => "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','".e($id)."');fbq('track','PageView');</script>",
            'google', 'google analytics', 'ga4' => '<script async src="https://www.googletagmanager.com/gtag/js?id='.e($id)."\"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','".e($id)."');</script>",
            'tiktok' => "<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.load=function(e){var i='https://analytics.tiktok.com/i18n/pixel/events.js';ttq._i=ttq._i||{};ttq._i[e]=[];ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]={};var o=d.createElement('script');o.type='text/javascript';o.async=!0;o.src=i+'?sdkid='+e+'&lib='+t;var a=d.getElementsByTagName('script')[0];a.parentNode.insertBefore(o,a)};ttq.load('".e($id)."');ttq.page()}(window,document,'ttq');</script>",
            'linkedin' => "<script type=\"text/javascript\">_linkedin_partner_id=\"".e($id)."\";window._linkedin_data_partner_ids=window._linkedin_data_partner_ids||[];window._linkedin_data_partner_ids.push(_linkedin_partner_id);</script><script type=\"text/javascript\">(function(l){if(!l){window.lintrk=function(a,b){window.lintrk.q.push([a,b])};window.lintrk.q=[]}var s=document.getElementsByTagName('script')[0];var b=document.createElement('script');b.type='text/javascript';b.async=true;b.src='https://snap.licdn.com/li.lms-analytics/insight.min.js';s.parentNode.insertBefore(b,s)})(window.lintrk);</script>",
            default => (string) data_get($pixel->settings, 'script', ''),
        };
    }
}
