<?php

namespace Modules\AdminShortLinks\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppShortLinks\Models\AppShortLinkAbuseReport;

class AdminShortLinksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'adminshortlinks');

        register_sidebar_section('marketing-tools', 'Marketing Tools', 19);
        register_sidebar_item('marketing-tools', [
            'label' => 'Analytics',
            'route_name' => 'admin.short-links.index',
            'active_when' => ['admin.short-links.index'],
            'icon' => 'fa-light fa-chart-line',
            'order' => 20,
        ]);

        register_sidebar_item('marketing-tools', [
            'label' => 'Short Links',
            'route_name' => 'admin.short-links.manage',
            'active_when' => ['admin.short-links.manage'],
            'icon' => 'fa-light fa-link-simple',
            'order' => 21,
        ]);

        register_admin_dashboard_item('admin-short-links.snapshot', [
            'title' => 'Short Links',
            'view' => 'adminshortlinks::dashboard.snapshot',
            'width' => 'full',
            'order' => 6,
            'data' => function (): array {
                $last7Start = CarbonImmutable::now()->subDays(6)->startOfDay();
                $chartDays = collect(range(0, 6))->map(fn (int $day): CarbonImmutable => $last7Start->addDays($day));
                $clickCountsByDay = AppShortLinkClick::query()
                    ->where('created_at', '>=', $last7Start)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $ownerIds = AppShortLink::query()->distinct()->pluck('owner_user_id')->filter();
                $topClientRows = AppShortLink::query()
                    ->selectRaw('owner_user_id, COUNT(*) as links_count, SUM(clicks_count) as clicks_sum')
                    ->groupBy('owner_user_id')
                    ->orderByDesc('clicks_sum')
                    ->limit(5)
                    ->get();
                $owners = User::query()
                    ->whereIn('id', $topClientRows->pluck('owner_user_id')->filter())
                    ->get(['id', 'name', 'email'])
                    ->keyBy('id');

                return [
                    'metrics' => [
                        'links' => AppShortLink::query()->count(),
                        'active_links' => AppShortLink::query()->where('status', 'active')->count(),
                        'blocked_links' => AppShortLink::query()->where('moderation_status', 'blocked')->count(),
                        'clients' => $ownerIds->unique()->count(),
                        'clicks' => AppShortLinkClick::query()->count(),
                        'clicks_7d' => AppShortLinkClick::query()->where('created_at', '>=', $last7Start)->count(),
                        'open_reports' => AppShortLinkAbuseReport::query()->where('status', 'open')->count(),
                    ],
                    'charts' => [
                        'activity' => $chartDays->map(function (CarbonImmutable $date) use ($clickCountsByDay): array {
                            $key = $date->toDateString();

                            return [
                                'label' => $date->format('M j'),
                                'clicks' => (int) ($clickCountsByDay[$key] ?? 0),
                            ];
                        })->values()->all(),
                    ],
                    'topClients' => $topClientRows->map(fn ($row): array => [
                        'name' => $owners[(int) $row->owner_user_id]->name ?? __('Unknown client'),
                        'email' => $owners[(int) $row->owner_user_id]->email ?? null,
                        'links' => (int) $row->links_count,
                        'clicks' => (int) $row->clicks_sum,
                    ])->all(),
                    'topLinks' => AppShortLink::query()
                        ->orderByDesc('clicks_count')
                        ->latest()
                        ->limit(5)
                        ->get(['id', 'name', 'short_code', 'destination_url', 'clicks_count', 'status', 'moderation_status']),
                    'routes' => [
                        'analytics' => Route::has('admin.short-links.index') ? route('admin.short-links.index') : null,
                        'manage' => Route::has('admin.short-links.manage') ? route('admin.short-links.manage') : null,
                    ],
                ];
            },
        ]);

        $this->app->booted(function (): void {
            $this->placeMarketingToolsBeforeAiCenter();
            $this->placeDashboardItemBelowWelcome();
        });
    }

    protected function placeDashboardItemBelowWelcome(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $options = app(OptionStore::class);
        $key = 'admin_dashboard_layout_'.$user->getAuthIdentifier();
        $stored = $options->get($key, []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored) || $stored === []) {
            return;
        }

        $itemId = 'admin-short-links.snapshot';
        $stored = array_values(array_filter($stored, fn ($id): bool => is_string($id) && $id !== '' && $id !== $itemId));
        array_unshift($stored, $itemId);

        $options->set($key, $stored);
    }

    protected function placeMarketingToolsBeforeAiCenter(): null
    {
        $options = app(OptionStore::class);
        $stored = $options->get('admin_menu_sidebar_overrides', []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored) || ! is_array($stored['admin']['sections'] ?? null)) {
            return null;
        }

        $sections = array_values(array_filter((array) $stored['admin']['sections'], fn ($section): bool => is_array($section)));
        $marketingSection = collect($sections)->first(fn ($section): bool => ($section['key'] ?? null) === 'marketing-tools') ?: [
            'key' => 'marketing-tools',
            'label' => 'Marketing Tools',
            'items' => [
                ['key' => 'admin.short-links.index', 'label' => 'Analytics'],
                ['key' => 'admin.short-links.manage', 'label' => 'Short Links'],
            ],
        ];

        $marketingSection['label'] = $marketingSection['label'] ?? 'Marketing Tools';
        $marketingSection['items'] = array_values(array_filter(
            (array) ($marketingSection['items'] ?? []),
            fn ($item): bool => is_array($item)
        ));

        if (! collect($marketingSection['items'])->contains(fn ($item): bool => ($item['key'] ?? null) === 'admin.short-links.index')) {
            $marketingSection['items'][] = ['key' => 'admin.short-links.index', 'label' => 'Analytics'];
        }

        if (! collect($marketingSection['items'])->contains(fn ($item): bool => ($item['key'] ?? null) === 'admin.short-links.manage')) {
            $marketingSection['items'][] = ['key' => 'admin.short-links.manage', 'label' => 'Short Links'];
        }

        $marketingSection['items'] = collect($marketingSection['items'])
            ->map(function (array $item): array {
                if (($item['key'] ?? null) === 'admin.short-links.index') {
                    $item['label'] = 'Analytics';
                }

                if (($item['key'] ?? null) === 'admin.short-links.manage') {
                    $item['label'] = 'Short Links';
                }

                return $item;
            })
            ->values()
            ->all();

        $sections = array_values(array_filter(
            $sections,
            fn ($section): bool => ($section['key'] ?? null) !== 'marketing-tools'
        ));

        $aiCenterIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'ai-settings');

        if ($aiCenterIndex !== false) {
            array_splice($sections, (int) $aiCenterIndex, 0, [$marketingSection]);
        } else {
            array_unshift($sections, $marketingSection);
        }

        $stored['admin']['sections'] = array_values($sections);
        $options->set('admin_menu_sidebar_overrides', $stored);

        return null;
    }
}
