<?php

namespace Modules\AppQRAnalytics\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppCustomDomain\Models\AppCustomDomain;
use Modules\AppTrackingPixels\Models\AppTrackingPixel;
use Modules\AppUTMPresets\Models\AppUtmPreset;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('QR Analytics')]
class AnalyticsIndex extends Component
{
    #[Url(as: 'from', except: '')]
    public string $fromDate = '';

    #[Url(as: 'to', except: '')]
    public string $toDate = '';

    public ?AppQrCode $campaign = null;

    public function mount(?AppQrCode $qrCode = null): void
    {
        if ($qrCode && (int) $qrCode->owner_user_id !== TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user())) {
            abort(403);
        }

        $this->campaign = $qrCode;

        [$startDate, $endDate] = $this->resolveRange();

        $this->fromDate = $startDate->toDateString();
        $this->toDate = $endDate->toDateString();
    }

    public function resetFilters(): void
    {
        $this->fromDate = '';
        $this->toDate = '';

        [$startDate, $endDate] = $this->resolveRange();

        $this->fromDate = $startDate->toDateString();
        $this->toDate = $endDate->toDateString();
    }

    public function render(): View
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        [$startDate, $endDate] = $this->resolveRange();
        $rangeStart = $startDate->copy()->startOfDay();
        $rangeEnd = $endDate->copy()->endOfDay();

        $baseEvents = $this->baseEventQuery($ownerId, $rangeStart, $rangeEnd);

        $qrCodeScope = AppQrCode::query()
            ->where('owner_user_id', $ownerId)
            ->when($this->campaign, fn (Builder $query) => $query->whereKey($this->campaign?->id));

        $qrCodesCount = (clone $qrCodeScope)->count();
        $activeQrCodesCount = (clone $qrCodeScope)->where('status', 'active')->count();
        $topQrCodes = (clone $qrCodeScope)
            ->orderByDesc('scans_count')
            ->limit(10)
            ->get();

        $domains = AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('domain', 'id');

        $utmPresets = AppUtmPreset::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('name', 'id');

        $pixels = AppTrackingPixel::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('name', 'id');

        $dailyRows = (clone $baseEvents)
            ->selectRaw('DATE(created_at) as bucket_date, COUNT(*) as scans, COUNT(DISTINCT ip_address) as unique_ips')
            ->groupBy('bucket_date')
            ->pluck('scans', 'bucket_date');

        $dailyUniqueRows = (clone $baseEvents)
            ->selectRaw('DATE(created_at) as bucket_date, COUNT(DISTINCT ip_address) as unique_ips')
            ->groupBy('bucket_date')
            ->pluck('unique_ips', 'bucket_date');

        $daily = collect(range(0, max(0, $startDate->diffInDays($endDate))))
            ->map(function (int $offset) use ($dailyRows, $dailyUniqueRows, $startDate): array {
                $date = $startDate->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'label' => $date->format('d M'),
                    'scans' => (int) ($dailyRows[$key] ?? 0),
                    'unique_ips' => (int) ($dailyUniqueRows[$key] ?? 0),
                ];
            })
            ->values();

        $hourRows = (clone $baseEvents)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as scans')
            ->groupBy('hour')
            ->pluck('scans', 'hour');

        $hourly = collect(range(0, 23))->map(function (int $hour) use ($hourRows): array {
            return [
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                'scans' => (int) ($hourRows[$hour] ?? 0),
            ];
        });

        $typeBreakdown = $this->typeBreakdown($ownerId, $rangeStart, $rangeEnd, 10);
        $sourceBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(referer, '://', -1), '/', 1), ''), NULLIF(source, ''), 'Direct')", 8);
        $countryBreakdown = $this->groupedBreakdown((clone $baseEvents), "UPPER(COALESCE(NULLIF(country, ''), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.country')), ''), 'Unknown'))", 12);
        $browserBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.browser')), ''), 'Unknown')", 8);
        $osBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.os')), ''), 'Unknown')", 8);
        $deviceBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.device')), ''), 'Unknown')", 8);
        $brandBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.device_brand')), ''), 'Other')", 8);
        $languageBreakdown = $this->languageBreakdown((clone $baseEvents), 8);
        $cityBreakdown = $this->groupedBreakdown((clone $baseEvents), "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.city')), ''), 'Unknown')", 12);
        $domainBreakdown = $this->idBreakdown((clone $baseEvents), "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.custom_domain_id'))", $domains, 'Default app domain', 'Domain #', 8);
        $utmBreakdown = $this->idBreakdown((clone $baseEvents), "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.utm_preset_id'))", $utmPresets, 'No UTM preset', 'UTM #', 8);
        $pixelBreakdown = $this->pixelBreakdown($ownerId, $rangeStart, $rangeEnd, $pixels, 8);

        $totalScans = (clone $baseEvents)->count();
        $uniqueVisitors = (clone $baseEvents)->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address');
        $today = (clone $baseEvents)->whereDate('created_at', now()->toDateString())->count();
        $peakDay = $daily->sortByDesc('scans')->first();
        $recentEvents = AppQrScanEvent::query()
            ->with('qrCode')
            ->where('owner_user_id', $ownerId)
            ->when($this->campaign, fn (Builder $query) => $query->where('app_qr_code_id', $this->campaign?->id))
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->latest('created_at')
            ->limit(12)
            ->get();

        return view('appqranalytics::index', [
            'campaign' => $this->campaign,
            'isCampaignAnalytics' => $this->campaign !== null,
            'rangeLabel' => $startDate->format('M d, Y').' - '.$endDate->format('M d, Y'),
            'stats' => [
                'scans' => $totalScans,
                'today' => $today,
                'uniqueVisitors' => $uniqueVisitors,
                'active' => $activeQrCodesCount,
                'qrCodes' => $qrCodesCount,
                'avgDaily' => $daily->count() > 0 ? round((float) $daily->avg('scans'), 1) : 0,
            ],
            'summaryCards' => [
                ['title' => __('Reporting range'), 'description' => $startDate->format('M d').' - '.$endDate->format('M d, Y'), 'variant' => 'info', 'icon' => 'fa-light fa-calendar-range'],
                ['title' => __('Peak day'), 'description' => (($peakDay['label'] ?? __('N/A')).' - '.number_format((int) ($peakDay['scans'] ?? 0)).' '.__('scans')), 'variant' => 'success', 'icon' => 'fa-light fa-chart-line-up'],
                ['title' => __('Unique visitors'), 'description' => number_format($uniqueVisitors).' '.__('unique IPs in this range'), 'variant' => 'warning', 'icon' => 'fa-light fa-user-magnifying-glass'],
                ['title' => __('Active QR codes'), 'description' => number_format($activeQrCodesCount).' '.__('campaigns currently active'), 'variant' => 'neutral', 'icon' => 'fa-light fa-circle-check'],
            ],
            'scanTrendOptions' => $this->scanTrendOptions($daily),
            'hourlyOptions' => $this->singleSeriesColumnOptions($hourly, __('Scans')),
            'topQrOptions' => $this->topQrOptions($topQrCodes),
            'typeOptions' => $this->donutOptions($typeBreakdown, __('Scans')),
            'sourceOptions' => $this->barOptions($sourceBreakdown, __('Scans'), '#0ea5e9'),
            'countryOptions' => $this->barOptions($countryBreakdown, __('Scans'), '#14b8a6'),
            'browserOptions' => $this->barOptions($browserBreakdown, __('Scans'), '#6366f1'),
            'osOptions' => $this->barOptions($osBreakdown, __('Scans'), '#f59e0b'),
            'deviceOptions' => $this->donutOptions($deviceBreakdown, __('Scans')),
            'brandOptions' => $this->barOptions($brandBreakdown, __('Scans'), '#e11d48'),
            'languageOptions' => $this->barOptions($languageBreakdown, __('Scans'), '#8b5cf6'),
            'cityOptions' => $this->barOptions($cityBreakdown, __('Scans'), '#f97316'),
            'domainOptions' => $this->barOptions($domainBreakdown, __('Scans'), '#0891b2'),
            'utmOptions' => $this->barOptions($utmBreakdown, __('Scans'), '#7c3aed'),
            'pixelOptions' => $this->barOptions($pixelBreakdown, __('Events'), '#db2777'),
            'events' => $recentEvents,
            'topQrCodes' => $topQrCodes,
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('QR Analytics')]);
    }

    protected function baseEventQuery(int $ownerId, Carbon $startDate, Carbon $endDate): QueryBuilder
    {
        return DB::table('app_qr_scan_events')
            ->where('owner_user_id', $ownerId)
            ->when($this->campaign, fn (QueryBuilder $query) => $query->where('app_qr_code_id', $this->campaign?->id))
            ->whereBetween('created_at', [$startDate, $endDate]);
    }

    protected function resolveRange(): array
    {
        $startDate = $this->parseDate($this->fromDate) ?? now()->subDays(29)->startOfDay();
        $endDate = $this->parseDate($this->toDate) ?? now()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()];
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function countBy(Collection $events, callable $resolver, int $limit): Collection
    {
        return $events
            ->map(fn (AppQrScanEvent $event): string => trim((string) $resolver($event)) ?: 'Unknown')
            ->countBy()
            ->map(fn (int $value, string $label): array => ['label' => $label, 'value' => $value])
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }

    protected function groupedBreakdown(QueryBuilder $query, string $labelExpression, int $limit): Collection
    {
        return $query
            ->selectRaw($labelExpression.' as label, COUNT(*) as value')
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'label' => trim((string) $row->label) !== '' ? (string) $row->label : 'Unknown',
                'value' => (int) $row->value,
            ]);
    }

    protected function typeBreakdown(int $ownerId, Carbon $startDate, Carbon $endDate, int $limit): Collection
    {
        return DB::table('app_qr_scan_events as scans')
            ->join('app_qr_codes as codes', 'codes.id', '=', 'scans.app_qr_code_id')
            ->where('scans.owner_user_id', $ownerId)
            ->when($this->campaign, fn (QueryBuilder $query) => $query->where('scans.app_qr_code_id', $this->campaign?->id))
            ->whereBetween('scans.created_at', [$startDate, $endDate])
            ->selectRaw("COALESCE(NULLIF(codes.type, ''), 'unknown') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'label' => str_replace('_', ' ', (string) $row->label),
                'value' => (int) $row->value,
            ]);
    }

    protected function languageBreakdown(QueryBuilder $query, int $limit): Collection
    {
        return $query
            ->selectRaw("UPPER(SUBSTRING(REPLACE(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.language')), ''), 'Unknown'), '_', '-'), 1, 2)) as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'label' => trim((string) $row->label) !== '' ? (string) $row->label : 'Unknown',
                'value' => (int) $row->value,
            ]);
    }

    protected function idBreakdown(QueryBuilder $query, string $idExpression, Collection $labels, string $emptyLabel, string $unknownPrefix, int $limit): Collection
    {
        return $query
            ->selectRaw($idExpression.' as item_id, COUNT(*) as value')
            ->groupBy('item_id')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(function ($row) use ($labels, $emptyLabel, $unknownPrefix): array {
                $id = (int) $row->item_id;

                return [
                    'label' => $id > 0 ? (string) ($labels->get($id) ?: $unknownPrefix.$id) : $emptyLabel,
                    'value' => (int) $row->value,
                ];
            });
    }

    protected function pixelBreakdown(int $ownerId, Carbon $startDate, Carbon $endDate, Collection $pixels, int $limit): Collection
    {
        return AppQrScanEvent::query()
            ->where('owner_user_id', $ownerId)
            ->when($this->campaign, fn (Builder $query) => $query->where('app_qr_code_id', $this->campaign?->id))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->limit(5000)
            ->get(['metadata'])
            ->flatMap(function (AppQrScanEvent $event) use ($pixels): array {
                $ids = collect((array) data_get($event->metadata, 'tracking_pixel_ids', []))
                    ->filter()
                    ->map(fn ($id): int => (int) $id)
                    ->unique();

                if ($ids->isEmpty()) {
                    return ['No pixel'];
                }

                return $ids
                    ->map(fn (int $id): string => (string) ($pixels->get($id) ?: 'Pixel #'.$id))
                    ->all();
            })
            ->countBy()
            ->map(fn (int $value, string $label): array => ['label' => $label, 'value' => $value])
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }

    protected function scanTrendOptions(Collection $daily): array
    {
        return [
            'chart' => ['type' => 'areaspline'],
            'xAxis' => ['categories' => $daily->pluck('label')->all()],
            'yAxis' => [
                ['title' => ['text' => null], 'min' => 0],
                ['title' => ['text' => null], 'min' => 0, 'opposite' => true],
            ],
            'tooltip' => ['shared' => true],
            'series' => [
                ['name' => __('Scans'), 'type' => 'areaspline', 'data' => $daily->pluck('scans')->all(), 'color' => '#2563eb'],
                ['name' => __('Unique IPs'), 'type' => 'spline', 'yAxis' => 1, 'data' => $daily->pluck('unique_ips')->all(), 'color' => '#14b8a6'],
            ],
        ];
    }

    protected function singleSeriesColumnOptions(Collection $rows, string $name): array
    {
        return [
            'chart' => ['type' => 'column'],
            'legend' => ['enabled' => false],
            'xAxis' => ['categories' => $rows->pluck('label')->all()],
            'series' => [[
                'name' => $name,
                'data' => $rows->pluck('scans')->all(),
                'color' => '#2563eb',
            ]],
        ];
    }

    protected function topQrOptions(Collection $qrCodes): array
    {
        return [
            'chart' => ['type' => 'bar'],
            'legend' => ['enabled' => false],
            'xAxis' => ['categories' => $qrCodes->pluck('name')->all()],
            'series' => [[
                'name' => __('Scans'),
                'data' => $qrCodes->pluck('scans_count')->map(fn ($value) => (int) $value)->all(),
                'color' => '#0f766e',
            ]],
        ];
    }

    protected function barOptions(Collection $rows, string $name, string $color): array
    {
        return [
            'chart' => ['type' => 'bar'],
            'legend' => ['enabled' => false],
            'xAxis' => ['categories' => $rows->pluck('label')->all()],
            'series' => [[
                'name' => $name,
                'data' => $rows->pluck('value')->all(),
                'color' => $color,
            ]],
        ];
    }

    protected function donutOptions(Collection $rows, string $name): array
    {
        return [
            'chart' => ['type' => 'pie'],
            'legend' => ['enabled' => true],
            'series' => [[
                'name' => $name,
                'data' => $rows->map(fn (array $row): array => ['name' => $row['label'], 'y' => $row['value']])->all(),
            ]],
        ];
    }

    protected function sourceLabel(AppQrScanEvent $event): string
    {
        $referer = (string) $event->referer;

        if ($referer !== '') {
            $host = parse_url($referer, PHP_URL_HOST);

            if (is_string($host) && $host !== '') {
                return $host;
            }
        }

        return (string) ($event->source ?: 'Direct');
    }

    protected function domainLabel(AppQrScanEvent $event, Collection $domains): string
    {
        $domainId = (int) data_get($event->metadata, 'custom_domain_id', 0);

        if ($domainId > 0) {
            return (string) ($domains->get($domainId) ?: 'Domain #'.$domainId);
        }

        $host = (string) data_get($event->metadata, 'host', '');

        return $host !== '' ? $host : 'Default app domain';
    }

    protected function utmLabel(AppQrScanEvent $event, Collection $utmPresets): string
    {
        $utmPresetId = (int) data_get($event->metadata, 'utm_preset_id', 0);

        if ($utmPresetId > 0) {
            return (string) ($utmPresets->get($utmPresetId) ?: 'UTM #'.$utmPresetId);
        }

        return 'No UTM preset';
    }

    protected function browserFromUserAgent(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') || str_contains($ua, 'CriOS/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/') => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    protected function osFromUserAgent(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iPod') => 'iOS',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS X') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    protected function deviceFromUserAgent(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            str_contains($ua, 'bot') || str_contains($ua, 'Bot') || str_contains($ua, 'spider') => 'Bot',
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'Tablet',
            str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone') => 'Mobile',
            $ua !== '' => 'Desktop',
            default => 'Unknown',
        };
    }

    protected function brandFromUserAgent(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'Macintosh') => 'Apple',
            str_contains($ua, 'Samsung') || str_contains($ua, 'SM-') => 'Samsung',
            str_contains($ua, 'Pixel') => 'Google',
            str_contains($ua, 'Huawei') || str_contains($ua, 'HUAWEI') => 'Huawei',
            str_contains($ua, 'Xiaomi') || str_contains($ua, 'Redmi') => 'Xiaomi',
            str_contains($ua, 'OPPO') => 'OPPO',
            str_contains($ua, 'Vivo') => 'Vivo',
            str_contains($ua, 'ZTE') => 'ZTE',
            $ua !== '' => 'Other',
            default => 'Unknown',
        };
    }

    protected function languageLabel(string $language): string
    {
        $primary = trim(strtolower(explode(',', $language)[0] ?? ''));

        if ($primary === '') {
            return 'Unknown';
        }

        $primary = str_replace('_', '-', $primary);

        return strtoupper(substr($primary, 0, 2));
    }
}
