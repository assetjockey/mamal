<?php

namespace Modules\AppQRCodes\Http\Controllers;

use App\Support\RequestGeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AdminUser\Models\User;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppQRCodes\Support\QrDynamicRedirector;

class PublicQrCodeController
{
    public function __invoke(Request $request, string $code): RedirectResponse|View
    {
        $qrCode = AppQrCode::query()
            ->where('short_code', $code)
            ->where('status', 'active')
            ->firstOrFail();
        $brandOps = app(BrandOperations::class);
        $brandKitId = (int) data_get($qrCode->settings, 'brand_kit_id');
        $customDomainId = (int) data_get($qrCode->settings, 'custom_domain_id');
        $utmPresetId = data_get($qrCode->settings, 'utm_preset_id');
        $trackingPixelIds = (array) data_get($qrCode->settings, 'tracking_pixel_ids', []);
        $utmPreset = $brandOps->utmPreset($utmPresetId, (int) $qrCode->owner_user_id);
        $pixels = $brandOps->activePixels($trackingPixelIds, (int) $qrCode->owner_user_id);
        $effectiveDomain = $brandOps->verifiedDomain($customDomainId, (int) $qrCode->owner_user_id)
            ?: ($customDomainId > 0 ? null : $brandOps->defaultQrDomain((int) $qrCode->owner_user_id));

        $dynamicDestination = app(QrDynamicRedirector::class)->destination($qrCode, $request);

        if ($this->canRecordScan((int) $qrCode->owner_user_id)) {
            $country = RequestGeo::country($request);
            $city = RequestGeo::city($request);
            $region = RequestGeo::region($request);

            AppQrScanEvent::query()->create([
                'app_qr_code_id' => (int) $qrCode->id,
                'owner_user_id' => (int) $qrCode->owner_user_id,
                'source' => 'short_link',
                'ip_address' => $request->ip() ? hash('sha256', (string) $request->ip()) : null,
                'user_agent' => (string) $request->userAgent(),
                'country' => $country,
                'referer' => (string) $request->headers->get('referer', ''),
                'metadata' => [
                    'country' => $country,
                    'city' => $city,
                    'region' => $region,
                    'timezone' => RequestGeo::timezone($request),
                    'browser' => $this->browserFromUserAgent((string) $request->userAgent()),
                    'os' => $this->osFromUserAgent((string) $request->userAgent()),
                    'device' => $this->deviceFromUserAgent((string) $request->userAgent()),
                    'device_brand' => $this->brandFromUserAgent((string) $request->userAgent()),
                    'language' => (string) $request->headers->get('accept-language', ''),
                    'host' => $request->getHost(),
                    'brand_kit_id' => $brandKitId ?: null,
                    'custom_domain_id' => $effectiveDomain?->id,
                    'utm_preset_id' => $utmPreset?->id,
                    'tracking_pixel_ids' => $pixels->pluck('id')->values()->all(),
                    'dynamic_destination' => $dynamicDestination,
                ],
                'created_at' => now(),
            ]);

            $qrCode->newQuery()
                ->whereKey($qrCode->id)
                ->update([
                    'scans_count' => DB::raw('scans_count + 1'),
                    'last_scanned_at' => now(),
                ]);
        }

        if ($this->shouldRenderLandingPage($qrCode)) {
            return view('appqrcodes::public.show', [
                'qrCode' => $qrCode,
                'content' => (array) data_get($qrCode->settings, 'type_content', []),
                'typeLabel' => (string) data_get($qrCode->settings, 'type_label', str_replace('_', ' ', $qrCode->type)),
                'brandKit' => $brandOps->brandKit($brandKitId, (int) $qrCode->owner_user_id),
                'pixelSnippets' => $brandOps->pixelSnippets($pixels),
            ]);
        }

        $destination = $brandOps->applyUtm($dynamicDestination, $utmPreset);

        if ($pixels->isNotEmpty()) {
            return view('appqrcodes::public.redirect', [
                'destination' => $destination,
                'pixelSnippets' => $brandOps->pixelSnippets($pixels),
            ]);
        }

        return redirect()->away($destination);
    }

    protected function shouldRenderLandingPage(AppQrCode $qrCode): bool
    {
        $content = (array) data_get($qrCode->settings, 'type_content', []);
        $destination = trim((string) $qrCode->destination_url);

        if ($content === []) {
            return $destination === '' || $destination === url('/');
        }

        return in_array($qrCode->type, [
            'business_profile',
            'website_builder',
            'vcard_plus',
            'vcard',
            'lead_form',
            'product_catalogue',
            'restaurant_menu',
            'app_download',
            'resume_qr_code',
            'file_upload',
            'event',
            'booking',
            'donation',
        ], true);
    }

    protected function safeDestination(AppQrCode $qrCode): string
    {
        $destination = trim((string) $qrCode->destination_url);

        return $destination !== '' ? $destination : url('/');
    }

    protected function canRecordScan(int $ownerUserId): bool
    {
        $owner = User::query()->find($ownerUserId);
        $limit = $owner?->planLimit('max_qr_scans_monthly', -1);

        if (! $owner?->plan || ! is_numeric($limit) || (int) $limit < 0) {
            return true;
        }

        return AppQrScanEvent::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count() < (int) $limit;
    }

    protected function browserFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/') => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    protected function osFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    protected function deviceFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'bot') || str_contains($userAgent, 'Bot') || str_contains($userAgent, 'spider') => 'Bot',
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'Tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'Mobile',
            $userAgent !== '' => 'Desktop',
            default => 'Unknown',
        };
    }

    protected function brandFromUserAgent(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Macintosh') => 'Apple',
            str_contains($userAgent, 'Samsung') || str_contains($userAgent, 'SM-') => 'Samsung',
            str_contains($userAgent, 'Pixel') => 'Google',
            str_contains($userAgent, 'Huawei') || str_contains($userAgent, 'HUAWEI') => 'Huawei',
            str_contains($userAgent, 'Xiaomi') || str_contains($userAgent, 'Redmi') => 'Xiaomi',
            str_contains($userAgent, 'OPPO') => 'OPPO',
            str_contains($userAgent, 'Vivo') => 'Vivo',
            str_contains($userAgent, 'ZTE') => 'ZTE',
            $userAgent !== '' => 'Other',
            default => 'Unknown',
        };
    }
}
