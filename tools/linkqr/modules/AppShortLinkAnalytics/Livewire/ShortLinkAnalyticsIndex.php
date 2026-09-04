<?php

namespace Modules\AppShortLinkAnalytics\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Short Link Analytics')]
class ShortLinkAnalyticsIndex extends Component
{
    public $link = null;

    public string $linkSearch = '';

    public string $period = 'days';

    public ?string $drilldownType = null;

    public ?string $drilldownValue = null;

    public function mount(): void
    {
        $this->link = request()->integer('link') ?: null;
    }

    public function updatedPeriod(string $value): void
    {
        if (! in_array($value, ['hours', 'days', 'months'], true)) {
            $this->period = 'days';
        }
    }

    public function clearSelectedLink(): void
    {
        $this->link = null;
        $this->linkSearch = '';
    }

    public function setDrilldown(string $type, string $value): void
    {
        $this->drilldownType = $type;
        $this->drilldownValue = $value;
    }

    public function clearDrilldown(): void
    {
        $this->drilldownType = null;
        $this->drilldownValue = null;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $selectedLink = $this->selectedLink($ownerId);
        $filename = ($selectedLink ? 'short-link-'.$selectedLink->short_code : 'short-link-clicks').'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($ownerId, $selectedLink): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'short_link_id',
                'short_link_name',
                'short_code',
                'referer',
                'country',
                'city',
                'browser',
                'os',
                'device',
                'variant_key',
                'variant_name',
                'is_bot',
                'created_at',
            ]);

            $this->clickQuery($ownerId, $selectedLink?->id)
                ->with('shortLink:id,name,short_code')
                ->latest('created_at')
                ->chunk(500, function ($clicks) use ($handle): void {
                    foreach ($clicks as $click) {
                        fputcsv($handle, [
                            $click->app_short_link_id,
                            $click->shortLink?->name,
                            $click->shortLink?->short_code,
                            Str::limit((string) $click->referer, 500, ''),
                            $click->country,
                            data_get($click->metadata, 'city'),
                            data_get($click->metadata, 'browser'),
                            data_get($click->metadata, 'os'),
                            data_get($click->metadata, 'device'),
                            data_get($click->metadata, 'variant.key'),
                            data_get($click->metadata, 'variant.name'),
                            data_get($click->metadata, 'is_bot') ? '1' : '0',
                            optional($click->created_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function resetStats(): void
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $selectedLink = $this->selectedLink($ownerId);

        $this->clickQuery($ownerId, $selectedLink?->id)->delete();

        if ($selectedLink) {
            $selectedLink->forceFill([
                'clicks_count' => 0,
                'last_clicked_at' => null,
            ])->save();
        } else {
            AppShortLink::query()
                ->where('owner_user_id', $ownerId)
                ->update([
                    'clicks_count' => 0,
                    'last_clicked_at' => null,
                ]);
        }

        $this->dispatch('app-toast', type: 'success', message: __('Analytics stats reset.'));
    }

    public function render(): View
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $selectedLink = $this->selectedLink($ownerId);
        $linksQuery = AppShortLink::query()
            ->where('owner_user_id', $ownerId);
        $totalLinks = (clone $linksQuery)->count();
        $activeLinks = (clone $linksQuery)->where('status', 'active')->count();
        $linkOptions = $this->linkOptions($ownerId, $selectedLink);
        $topLinks = (clone $linksQuery)
            ->orderByDesc('clicks_count')
            ->latest()
            ->limit(8)
            ->get();
        $clicks = $this->clickQuery($ownerId, $selectedLink?->id)
            ->with('shortLink:id,name,short_code,destination_url,clicks_count,last_clicked_at')
            ->latest('created_at')
            ->limit(5000)
            ->get();
        $allClicksQuery = $this->clickQuery($ownerId, $selectedLink?->id);
        $todayStart = CarbonImmutable::now()->startOfDay();
        $uniqueClicks = $clicks->pluck('ip_address')->filter()->unique()->count();
        $totalClicks = (clone $allClicksQuery)->count();
        $drilldownClicks = $this->drilldownClicks($clicks);

        return view('appshortlinkanalytics::index', [
            'linkOptions' => $linkOptions,
            'selectedLink' => $selectedLink,
            'totalLinks' => $totalLinks,
            'metrics' => [
                'links' => $selectedLink ? 1 : $totalLinks,
                'active_links' => $selectedLink ? (int) ($selectedLink->status === 'active') : $activeLinks,
                'clicks' => $totalClicks,
                'unique_clicks' => $uniqueClicks,
                'clicks_today' => (clone $allClicksQuery)->where('created_at', '>=', $todayStart)->count(),
                'last_click' => $clicks->first()?->created_at,
            ],
            'chart' => $this->chart($clicks, $this->period),
            'topLinks' => $topLinks,
            'countries' => $this->topValues($clicks, fn ($click) => $click->country ?: __('Unknown'), 8),
            'cities' => $this->topValues($clicks, fn ($click) => data_get($click->metadata, 'city') ?: __('Unknown'), 8),
            'referrers' => $this->topValues($clicks, fn ($click) => $this->sourceLabel($click->referer), 8),
            'socials' => $this->topValues($clicks, fn ($click) => $this->socialLabel($click->referer), 6, true),
            'browsers' => $this->topValues($clicks, fn ($click) => data_get($click->metadata, 'browser') ?: __('Unknown'), 6),
            'devices' => $this->topValues($clicks, fn ($click) => str(data_get($click->metadata, 'device') ?: __('Unknown'))->title()->value(), 6),
            'platforms' => $this->topValues($clicks, fn ($click) => data_get($click->metadata, 'os') ?: __('Unknown'), 6),
            'languages' => $this->topValues($clicks, fn ($click) => $this->languageLabel(data_get($click->metadata, 'language')), 6),
            'clickTypes' => $this->clickTypes($clicks),
            'variantStats' => $this->variantStats($selectedLink, $clicks),
            'recentClicks' => $drilldownClicks->take(20),
            'drilldownLabel' => $this->drilldownType && $this->drilldownValue ? str($this->drilldownType)->headline().' - '.$this->drilldownValue : null,
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Short Link Analytics')]);
    }

    private function selectedLink(int $ownerId): ?AppShortLink
    {
        if (! $this->link) {
            return null;
        }

        return AppShortLink::query()
            ->where('owner_user_id', $ownerId)
            ->whereKey($this->link)
            ->first();
    }

    private function clickQuery(int $ownerId, ?int $linkId = null): Builder
    {
        return AppShortLinkClick::query()
            ->where('owner_user_id', $ownerId)
            ->when($linkId, fn (Builder $query) => $query->where('app_short_link_id', $linkId));
    }

    private function linkOptions(int $ownerId, ?AppShortLink $selectedLink): Collection
    {
        $search = trim($this->linkSearch);
        $query = AppShortLink::query()
            ->where('owner_user_id', $ownerId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('short_code', 'like', '%'.$search.'%')
                        ->orWhere('destination_url', 'like', '%'.$search.'%')
                        ->orWhere('campaign', 'like', '%'.$search.'%')
                        ->orWhere('folder', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('clicks_count')
            ->latest()
            ->limit(25)
            ->get(['id', 'name', 'short_code', 'destination_url', 'clicks_count']);

        if ($selectedLink && ! $query->contains('id', $selectedLink->id)) {
            $query->prepend($selectedLink);
        }

        return $query->values();
    }

    private function chart(Collection $clicks, string $period): array
    {
        $now = CarbonImmutable::now();

        if ($period === 'hours') {
            $start = $now->subHours(23)->startOfHour();
            $points = collect(range(0, 23))->map(fn (int $step) => $start->addHours($step));
            $grouped = $clicks
                ->filter(fn ($click) => $click->created_at && $click->created_at->greaterThanOrEqualTo($start))
                ->groupBy(fn ($click) => $click->created_at->format('Y-m-d H:00'));

            return $points->map(fn (CarbonImmutable $date) => [
                'label' => $date->format('g A'),
                'clicks' => $grouped->get($date->format('Y-m-d H:00'), collect())->count(),
            ])->all();
        }

        if ($period === 'months') {
            $start = $now->subMonths(11)->startOfMonth();
            $points = collect(range(0, 11))->map(fn (int $step) => $start->addMonths($step));
            $grouped = $clicks
                ->filter(fn ($click) => $click->created_at && $click->created_at->greaterThanOrEqualTo($start))
                ->groupBy(fn ($click) => $click->created_at->format('Y-m'));

            return $points->map(fn (CarbonImmutable $date) => [
                'label' => $date->format('M Y'),
                'clicks' => $grouped->get($date->format('Y-m'), collect())->count(),
            ])->all();
        }

        $start = $now->subDays(13)->startOfDay();
        $points = collect(range(0, 13))->map(fn (int $step) => $start->addDays($step));
        $grouped = $clicks
            ->filter(fn ($click) => $click->created_at && $click->created_at->greaterThanOrEqualTo($start))
            ->groupBy(fn ($click) => $click->created_at->toDateString());

        return $points->map(fn (CarbonImmutable $date) => [
            'label' => $date->format('M j'),
            'clicks' => $grouped->get($date->toDateString(), collect())->count(),
        ])->all();
    }

    private function topValues(Collection $clicks, callable $resolver, int $limit = 6, bool $dropEmpty = false): Collection
    {
        $total = max(1, $clicks->count());

        return $clicks
            ->map(fn ($click) => trim((string) $resolver($click)))
            ->filter(fn (string $value) => ! $dropEmpty || $value !== '')
            ->map(fn (string $value) => $value !== '' ? $value : __('Unknown'))
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count, string $label) => [
                'label' => $label,
                'count' => $count,
                'percent' => round(($count / $total) * 100, 1),
            ])
            ->values();
    }

    private function clickTypes(Collection $clicks): Collection
    {
        $total = max(1, $clicks->count());
        $counts = [
            __('Direct') => 0,
            __('Referral') => 0,
            __('Bot') => 0,
        ];

        foreach ($clicks as $click) {
            if (data_get($click->metadata, 'is_bot')) {
                $counts[__('Bot')]++;
            } elseif ($click->referer) {
                $counts[__('Referral')]++;
            } else {
                $counts[__('Direct')]++;
            }
        }

        return collect($counts)
            ->map(fn (int $count, string $label) => [
                'label' => $label,
                'count' => $count,
                'percent' => round(($count / $total) * 100, 1),
            ])
            ->values();
    }

    private function drilldownClicks(Collection $clicks): Collection
    {
        if (! $this->drilldownType || $this->drilldownValue === null) {
            return $clicks;
        }

        return $clicks->filter(function ($click): bool {
            $value = match ($this->drilldownType) {
                'country' => $click->country ?: __('Unknown'),
                'city' => data_get($click->metadata, 'city') ?: __('Unknown'),
                'source' => $this->sourceLabel($click->referer),
                'social' => $this->socialLabel($click->referer),
                'browser' => data_get($click->metadata, 'browser') ?: __('Unknown'),
                'device' => str(data_get($click->metadata, 'device') ?: __('Unknown'))->title()->value(),
                'platform' => data_get($click->metadata, 'os') ?: __('Unknown'),
                'language' => $this->languageLabel(data_get($click->metadata, 'language')),
                'click_type' => data_get($click->metadata, 'is_bot') ? __('Bot') : ($click->referer ? __('Referral') : __('Direct')),
                'variant' => $this->variantLabel($click),
                default => null,
            };

            return (string) $value === (string) $this->drilldownValue;
        })->values();
    }

    private function sourceLabel(?string $referer): string
    {
        if (! $referer) {
            return __('Direct');
        }

        $host = parse_url($referer, PHP_URL_HOST);

        return $host ?: Str::limit($referer, 64);
    }

    private function socialLabel(?string $referer): string
    {
        $source = Str::lower($this->sourceLabel($referer));

        return match (true) {
            str_contains($source, 'facebook') || str_contains($source, 'fb.') => 'Facebook',
            str_contains($source, 'instagram') => 'Instagram',
            str_contains($source, 'tiktok') => 'TikTok',
            str_contains($source, 'youtube') || str_contains($source, 'youtu.be') => 'YouTube',
            str_contains($source, 'linkedin') => 'LinkedIn',
            str_contains($source, 'twitter') || str_contains($source, 'x.com') => 'X',
            default => '',
        };
    }

    private function languageLabel(mixed $language): string
    {
        $language = trim((string) $language);

        if ($language === '') {
            return __('Unknown');
        }

        $primary = trim(explode(',', $language)[0] ?? '');
        $primary = preg_replace('/;q=[0-9.]+$/i', '', $primary) ?: $primary;
        $primary = str_replace('_', '-', $primary);

        if ($primary === '') {
            return __('Unknown');
        }

        return Str::limit($primary, 16, '');
    }

    private function variantStats(?AppShortLink $selectedLink, Collection $clicks): Collection
    {
        if (! $selectedLink) {
            return collect();
        }

        $variants = collect((array) $selectedLink->redirect_rules)
            ->filter(fn ($rule): bool => is_array($rule) && filled($rule['url'] ?? null) && ((string) ($rule['type'] ?? '') === 'ab' || array_key_exists('variant_key', $rule)))
            ->values();

        if ($variants->isEmpty()) {
            return collect();
        }

        $total = max(1, $clicks->count());
        $counts = $clicks
            ->map(fn ($click): string => (string) data_get($click->metadata, 'variant.key', ''))
            ->filter()
            ->countBy();
        $maxClicks = max(1, (int) $counts->max());
        $totalWeight = max(1, (int) $variants->sum(fn (array $variant): int => max(0, (int) ($variant['weight'] ?? 0))));

        return $variants->map(function (array $variant, int $index) use ($counts, $total, $maxClicks, $totalWeight): array {
            $key = (string) ($variant['variant_key'] ?? chr(65 + $index));
            $clicks = (int) ($counts[$key] ?? 0);
            $weight = max(0, (int) ($variant['weight'] ?? 0));
            $expected = round(($weight / $totalWeight) * 100, 1);
            $observed = round(($clicks / $total) * 100, 1);

            return [
                'variant_key' => $key,
                'name' => (string) ($variant['name'] ?? __('Variant :key', ['key' => $key])),
                'url' => (string) ($variant['url'] ?? ''),
                'weight' => $weight,
                'status' => (string) ($variant['status'] ?? 'active'),
                'winner' => (bool) ($variant['winner'] ?? false),
                'clicks' => $clicks,
                'split' => $observed,
                'expected_split' => $expected,
                'ctr_index' => $expected > 0 ? round(($observed / $expected) * 100, 1) : 0,
                'bar' => round(($clicks / $maxClicks) * 100, 1),
            ];
        })->values();
    }

    private function variantLabel(AppShortLinkClick $click): string
    {
        $key = trim((string) data_get($click->metadata, 'variant.key', ''));
        $name = trim((string) data_get($click->metadata, 'variant.name', ''));

        if ($key === '' && $name === '') {
            return __('Unknown');
        }

        return trim($key.' '.$name);
    }
}
