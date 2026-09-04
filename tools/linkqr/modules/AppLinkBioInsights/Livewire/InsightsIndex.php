<?php

namespace Modules\AppLinkBioInsights\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppLinkBio\Models\LinkBioEvent;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Support\LinkBioAccess;
use Modules\AppCustomDomain\Models\AppCustomDomain;
use Modules\AppTrackingPixels\Models\AppTrackingPixel;
use Modules\AppUTMPresets\Models\AppUtmPreset;

#[Title('Link Bio Analytics')]
class InsightsIndex extends Component
{
    public function render(LinkBioAccess $access): View
    {
        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseAnalytics(auth()->user()), 403);

        $ownerId = $access->workspaceOwnerUserId(auth()->user());
        $pages = LinkBioPage::query()
            ->ownedBy($ownerId)
            ->orderByDesc('updated_at')
            ->get();

        $pageIds = $pages->pluck('id')->map(fn ($id) => (int) $id)->all();
        $events = $pageIds === []
            ? collect()
            : LinkBioEvent::query()
                ->whereIn('link_bio_page_id', $pageIds)
                ->selectRaw('link_bio_page_id, type, count(*) as aggregate')
                ->groupBy('link_bio_page_id', 'type')
                ->get();

        $rawEvents = $pageIds === []
            ? collect()
            : LinkBioEvent::query()
                ->with('page')
                ->whereIn('link_bio_page_id', $pageIds)
                ->latest()
                ->get();

        $analytics = $events
            ->groupBy('link_bio_page_id')
            ->map(function ($items): array {
                $views = (int) optional($items->firstWhere('type', 'view'))->aggregate;
                $clicks = (int) optional($items->firstWhere('type', 'click'))->aggregate;

                return [
                    'views' => $views,
                    'clicks' => $clicks,
                    'ctr' => $views > 0 ? round(($clicks / $views) * 100, 1) : 0,
                ];
            });

        $totals = [
            'pages' => $pages->count(),
            'views' => (int) $events->where('type', 'view')->sum('aggregate'),
            'clicks' => (int) $events->where('type', 'click')->sum('aggregate'),
        ];
        $totals['ctr'] = $totals['views'] > 0 ? round(($totals['clicks'] / $totals['views']) * 100, 1) : 0;

        $domains = AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('domain', 'id');

        $utmPresets = AppUtmPreset::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('name', 'id');

        $pixels = AppTrackingPixel::query()
            ->where('owner_user_id', $ownerId)
            ->pluck('name', 'id');

        $pageBreakdown = $this->pageBreakdown($pages, $analytics, 8);
        $typeBreakdown = $this->countBy($rawEvents, fn (LinkBioEvent $event): string => ucfirst($event->type), 6);
        $domainBreakdown = $this->countBy($rawEvents, fn (LinkBioEvent $event): string => $this->domainLabel($event, $domains), 8);
        $utmBreakdown = $this->countBy($rawEvents, fn (LinkBioEvent $event): string => $this->utmLabel($event, $utmPresets), 8);
        $pixelBreakdown = $this->pixelBreakdown($rawEvents, $pixels, 8);
        $activityDays = $this->activityDays($rawEvents, 14);
        $attributionCards = [
            [
                'title' => __('Brand domains'),
                'label' => __('Domain coverage'),
                'icon' => 'fa-light fa-globe',
                'color' => '#0f766e',
                'rows' => $domainBreakdown,
                'count' => $domains->count() + 1,
            ],
            [
                'title' => __('UTM presets'),
                'label' => __('Tagged events'),
                'icon' => 'fa-light fa-tags',
                'color' => '#7c3aed',
                'rows' => $utmBreakdown,
                'count' => $utmPresets->count(),
            ],
            [
                'title' => __('Tracking pixels'),
                'label' => __('Pixel attribution'),
                'icon' => 'fa-light fa-bullseye-pointer',
                'color' => '#db2777',
                'rows' => $pixelBreakdown,
                'count' => $pixels->count(),
            ],
        ];

        return view('applinkbioinsights::index', [
            'pages' => $pages,
            'analytics' => $analytics,
            'totals' => $totals,
            'pageOptions' => $this->barOptions($pageBreakdown, __('Events'), '#2563eb'),
            'typeOptions' => $this->donutOptions($typeBreakdown, __('Events')),
            'domainOptions' => $this->barOptions($domainBreakdown, __('Events'), '#0891b2'),
            'utmOptions' => $this->barOptions($utmBreakdown, __('Events'), '#7c3aed'),
            'pixelOptions' => $this->barOptions($pixelBreakdown, __('Events'), '#db2777'),
            'pageBreakdown' => $pageBreakdown,
            'typeBreakdown' => $typeBreakdown,
            'domainBreakdown' => $domainBreakdown,
            'utmBreakdown' => $utmBreakdown,
            'pixelBreakdown' => $pixelBreakdown,
            'activityDays' => $activityDays,
            'attributionCards' => $attributionCards,
            'recentEvents' => $rawEvents->take(10),
        ])->layout(theme_view('layouts.app', 'app'), [
                'title' => __('Link Bio Analytics'),
        ]);
    }

    protected function activityDays(Collection $events, int $days): Collection
    {
        $start = Carbon::today()->subDays($days - 1);
        $grouped = $events
            ->filter(fn (LinkBioEvent $event): bool => $event->created_at !== null && $event->created_at->greaterThanOrEqualTo($start))
            ->groupBy(fn (LinkBioEvent $event): string => $event->created_at->format('Y-m-d'));

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $grouped): array {
                $date = $start->copy()->addDays($offset);
                $items = $grouped->get($date->format('Y-m-d'), collect());
                $views = $items->where('type', 'view')->count();
                $clicks = $items->where('type', 'click')->count();

                return [
                    'label' => $date->format('M d'),
                    'short' => $date->format('d'),
                    'views' => $views,
                    'clicks' => $clicks,
                    'total' => $views + $clicks,
                ];
            });
    }

    protected function pageBreakdown(Collection $pages, Collection $analytics, int $limit): Collection
    {
        return $pages
            ->map(function (LinkBioPage $page) use ($analytics): array {
                $row = $analytics->get($page->id, ['views' => 0, 'clicks' => 0, 'ctr' => 0]);

                return [
                    'id' => (int) $page->id,
                    'label' => (string) $page->title,
                    'slug' => (string) $page->slug,
                    'views' => (int) $row['views'],
                    'clicks' => (int) $row['clicks'],
                    'ctr' => (float) $row['ctr'],
                    'value' => (int) $row['views'] + (int) $row['clicks'],
                    'status' => $page->is_published ? __('Published') : __('Draft'),
                ];
            })
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }

    protected function countBy(Collection $events, callable $resolver, int $limit): Collection
    {
        return $events
            ->map(fn (LinkBioEvent $event): string => trim((string) $resolver($event)) ?: 'Unknown')
            ->countBy()
            ->map(fn (int $value, string $label): array => ['label' => $label, 'value' => $value])
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }

    protected function pixelBreakdown(Collection $events, Collection $pixels, int $limit): Collection
    {
        return $events
            ->flatMap(function (LinkBioEvent $event) use ($pixels): array {
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

    protected function domainLabel(LinkBioEvent $event, Collection $domains): string
    {
        $domainId = (int) data_get($event->metadata, 'custom_domain_id', 0);

        if ($domainId > 0) {
            return (string) ($domains->get($domainId) ?: 'Domain #'.$domainId);
        }

        return 'Default app domain';
    }

    protected function utmLabel(LinkBioEvent $event, Collection $utmPresets): string
    {
        $utmPresetId = (int) data_get($event->metadata, 'utm_preset_id', 0);

        if ($utmPresetId > 0) {
            return (string) ($utmPresets->get($utmPresetId) ?: 'UTM #'.$utmPresetId);
        }

        return 'No UTM preset';
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
}
