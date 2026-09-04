<?php

namespace Modules\AppShortLinks\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppShortLinksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appshortlinks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'url_shorteners',
            'label' => __('Short Links'),
            'type' => 'config',
            'order' => 152,
            'default' => true,
            'fields' => [
                [
                    'key' => 'max_short_links',
                    'label' => __('Max short links'),
                    'type' => 'number',
                    'default' => -1,
                    'description' => __('Maximum short links per workspace. Use -1 for unlimited.'),
                ],
                [
                    'key' => 'max_short_link_clicks_monthly',
                    'label' => __('Monthly short-link clicks'),
                    'type' => 'number',
                    'default' => -1,
                    'description' => __('Maximum recorded short-link click analytics per month. Redirects continue after the limit.'),
                ],
                [
                    'key' => 'max_short_link_bulk_rows',
                    'label' => __('Bulk rows per import'),
                    'type' => 'number',
                    'default' => 100,
                    'description' => __('Maximum rows users can import in one short-link bulk operation.'),
                ],
                [
                    'key' => 'short_link_bulk_import',
                    'label' => __('Bulk import'),
                    'type' => 'boolean',
                    'default' => true,
                    'description' => __('Allow CSV bulk import for short links.'),
                ],
                [
                    'key' => 'short_link_analytics_retention_days',
                    'label' => __('Analytics retention days'),
                    'type' => 'number',
                    'default' => 30,
                    'description' => __('How many days short-link click analytics should be available. Use -1 for unlimited.'),
                ],
                [
                    'key' => 'short_link_advanced_rules',
                    'label' => __('Advanced routing rules'),
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Allow rotator, geo, device, and time-based redirects.'),
                ],
                [
                    'key' => 'short_link_ab_testing',
                    'label' => __('A/B destination testing'),
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Allow weighted destination variants, winners, and variant analytics.'),
                ],
                [
                    'key' => 'short_link_password_protection',
                    'label' => __('Password protected links'),
                    'type' => 'boolean',
                    'default' => true,
                    'description' => __('Allow users to protect short links with passwords.'),
                ],
                [
                    'key' => 'short_link_expiration_controls',
                    'label' => __('Expiration and click caps'),
                    'type' => 'boolean',
                    'default' => true,
                    'description' => __('Allow expiration dates and click limits for short links.'),
                ],
                [
                    'key' => 'short_link_custom_alias',
                    'label' => __('Custom short codes'),
                    'type' => 'boolean',
                    'default' => true,
                    'description' => __('Allow users to choose branded custom short codes.'),
                ],
                [
                    'key' => 'short_link_api_access',
                    'label' => __('Short Links API access'),
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Allow API keys and webhooks for short links.'),
                ],
                [
                    'key' => 'max_short_link_api_keys',
                    'label' => __('Max API keys'),
                    'type' => 'number',
                    'default' => 2,
                    'description' => __('Maximum active short-link API keys per workspace. Use -1 for unlimited.'),
                ],
                [
                    'key' => 'max_short_link_webhooks',
                    'label' => __('Max webhooks'),
                    'type' => 'number',
                    'default' => 2,
                    'description' => __('Maximum active short-link webhooks per workspace. Use -1 for unlimited.'),
                ],
            ],
        ]);

        register_user_sidebar_section('short-links', 'Short Links', 30);

        register_user_sidebar_item('short-links', [
            'label' => 'Links',
            'route_name' => 'portal.short-links.index',
            'active_when' => ['portal.short-links.index'],
            'icon' => 'fa-light fa-link-simple',
            'order' => 10,
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('url_shorteners') ?? false),
        ]);

        register_user_dashboard_item('app-short-links.overview', [
            'title' => 'Short Links',
            'view' => 'appshortlinks::dashboard.user-overview',
            'width' => 'full',
            'order' => 19,
            'route_name' => 'portal.short-links.index',
            'visible' => fn ($user): bool => $user !== null && (
                ! $user?->plan || ($user?->canUsePlanFeature('url_shorteners') ?? false)
            ),
            'data' => function ($user): array {
                $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
                $links = AppShortLink::query()->where('owner_user_id', $ownerUserId);
                $last7Start = CarbonImmutable::now()->subDays(6)->startOfDay();
                $chartDays = collect(range(0, 6))->map(fn (int $day): CarbonImmutable => $last7Start->addDays($day));
                $clickCountsByDay = AppShortLinkClick::query()
                    ->where('owner_user_id', $ownerUserId)
                    ->where('created_at', '>=', $last7Start)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $monthlyClicks = AppShortLinkClick::query()
                    ->where('owner_user_id', $ownerUserId)
                    ->where('created_at', '>=', CarbonImmutable::now()->startOfMonth())
                    ->count();
                $usage = app(ShortLinkPlanLimits::class)->usageSummary($user, $ownerUserId);

                return [
                    'metrics' => [
                        'links' => (clone $links)->count(),
                        'active_links' => (clone $links)->where('status', 'active')->count(),
                        'blocked_links' => (clone $links)->where('moderation_status', 'blocked')->count(),
                        'clicks' => AppShortLinkClick::query()->where('owner_user_id', $ownerUserId)->count(),
                        'clicks_7d' => AppShortLinkClick::query()->where('owner_user_id', $ownerUserId)->where('created_at', '>=', $last7Start)->count(),
                        'monthly_clicks' => $monthlyClicks,
                    ],
                    'limits' => [
                        'links' => ['used' => (int) $usage['links_used'], 'limit' => $usage['links_limit']],
                        'monthly_clicks' => ['used' => (int) $usage['monthly_clicks_used'], 'limit' => $usage['monthly_clicks_limit']],
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
                    'topLinks' => (clone $links)->orderByDesc('clicks_count')->latest()->limit(6)->get(['id', 'name', 'short_code', 'destination_url', 'clicks_count', 'status']),
                    'routes' => [
                        'links' => Route::has('portal.short-links.index') ? route('portal.short-links.index') : null,
                        'analytics' => Route::has('portal.short-links.analytics') ? route('portal.short-links.analytics') : null,
                        'create' => Route::has('portal.short-links.index') ? route('portal.short-links.index') : null,
                    ],
                ];
            },
        ]);

        $this->app->booted(function (): null {
            $this->registerShortLinksPricing();

            return $this->placeShortLinksSectionAfterQrCodes();
        });
    }

    protected function registerShortLinksPricing(): void
    {
        \Pricing::add([
            'sort' => 61,
            'key' => 'short_links_saas',
            'label' => __('Short Links'),
            'check' => false,
            'type' => 'group',
            'subfeature' => fn (): array => \Pricing::getSubFeatures('short_links'),
        ]);

        \Pricing::addSubFeatures([
            [
                'parent' => 'short_links',
                'tab_id' => '01_core',
                'tab_name' => __('Short Links'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 10,
                'key' => 'max_short_links',
                'label' => __('Short link quota'),
                'type' => 'number',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '01_core',
                'tab_name' => __('Short Links'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 20,
                'key' => 'max_short_link_clicks_monthly',
                'label' => __('Monthly clicks'),
                'type' => 'number',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '01_core',
                'tab_name' => __('Short Links'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 30,
                'key' => 'short_link_analytics_retention_days',
                'label' => __('Analytics retention'),
                'type' => 'number',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '02_controls',
                'tab_name' => __('Controls'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 40,
                'key' => 'short_link_custom_alias',
                'label' => __('Custom short codes'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '02_controls',
                'tab_name' => __('Controls'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 50,
                'key' => 'short_link_password_protection',
                'label' => __('Password links'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '02_controls',
                'tab_name' => __('Controls'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 60,
                'key' => 'short_link_expiration_controls',
                'label' => __('Expiration and click caps'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '03_growth',
                'tab_name' => __('Growth'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 70,
                'key' => 'short_link_advanced_rules',
                'label' => __('Advanced routing'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '03_growth',
                'tab_name' => __('Growth'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 75,
                'key' => 'short_link_ab_testing',
                'label' => __('A/B destination testing'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '03_growth',
                'tab_name' => __('Growth'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 80,
                'key' => 'short_link_bulk_import',
                'label' => __('Bulk import'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '03_growth',
                'tab_name' => __('Growth'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 90,
                'key' => 'max_short_link_bulk_rows',
                'label' => __('Bulk rows per import'),
                'type' => 'number',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '04_developer',
                'tab_name' => __('Developer'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 100,
                'key' => 'short_link_api_access',
                'label' => __('API and Webhooks'),
                'type' => 'boolean',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '04_developer',
                'tab_name' => __('Developer'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 110,
                'key' => 'max_short_link_api_keys',
                'label' => __('API keys'),
                'type' => 'number',
            ],
            [
                'parent' => 'short_links',
                'tab_id' => '04_developer',
                'tab_name' => __('Developer'),
                'standalone' => false,
                'standalone_key' => 'short_links_saas',
                'standalone_label' => __('Short Links'),
                'standalone_sort' => 40,
                'sort' => 120,
                'key' => 'max_short_link_webhooks',
                'label' => __('Webhooks'),
                'type' => 'number',
            ],
        ]);
    }

    protected function placeShortLinksSectionAfterQrCodes(): null
    {
        $options = app(OptionStore::class);
        $stored = $options->get('admin_menu_sidebar_overrides', []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored) || ! is_array($stored['user']['sections'] ?? null)) {
            return null;
        }

        $sections = array_values(array_filter((array) $stored['user']['sections'], fn ($section): bool => is_array($section)));
        $sections = array_values(array_filter(
            $sections,
            fn ($section): bool => ($section['key'] ?? null) !== 'short-links'
        ));

        $shortLinksSection = [
            'key' => 'short-links',
            'label' => 'Short Links',
            'items' => [
                ['key' => 'portal.short-links.index', 'label' => 'Links'],
                ['key' => 'portal.short-links.bulk', 'label' => 'Bulk Import'],
                ['key' => 'portal.short-links.ab-testing', 'label' => 'A/B Tests'],
                ['key' => 'portal.short-links.analytics', 'label' => 'Analytics'],
                ['key' => 'portal.short-links.api', 'label' => 'API & Webhooks'],
            ],
        ];

        $qrCodesIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'qr-codes');
        $brandIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'brand');

        if ($qrCodesIndex !== false) {
            array_splice($sections, (int) $qrCodesIndex + 1, 0, [$shortLinksSection]);
        } elseif ($brandIndex !== false) {
            array_splice($sections, (int) $brandIndex, 0, [$shortLinksSection]);
        } else {
            $sections[] = $shortLinksSection;
        }

        $stored['user']['sections'] = array_values($sections);
        $options->set('admin_menu_sidebar_overrides', $stored);

        return null;
    }
}
