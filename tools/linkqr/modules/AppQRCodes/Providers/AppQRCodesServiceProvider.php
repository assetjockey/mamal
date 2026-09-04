<?php

namespace Modules\AppQRCodes\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Carbon\CarbonImmutable;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppLinkBio\Models\LinkBioEvent;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppCustomDomain\Models\AppCustomDomain;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppQRCodes\Support\QrPlanLimits;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppQRCodesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqrcodes');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'qr_codes',
            'label' => __('QR Codes'),
            'type' => 'config',
            'order' => 145,
            'default' => true,
            'fields' => [
                [
                    'key' => 'max_qr_codes',
                    'label' => __('Max QR codes'),
                    'type' => 'number',
                    'default' => -1,
                    'description' => __('Maximum QR campaigns. Use -1 for unlimited.'),
                ],
                [
                    'key' => 'max_qr_scans_monthly',
                    'label' => __('Monthly QR scans'),
                    'type' => 'number',
                    'default' => -1,
                    'description' => __('Maximum recorded QR scan analytics per month. Redirects continue after the limit.'),
                ],
                [
                    'key' => 'max_dynamic_rules_per_qr',
                    'label' => __('Dynamic rules per QR'),
                    'type' => 'number',
                    'default' => 10,
                    'description' => __('Maximum redirect rules on each QR campaign.'),
                ],
            ],
        ]);

        register_user_sidebar_section('qr-codes', 'QR Codes', 29);

        register_user_sidebar_item('qr-codes', [
            'label' => 'Campaigns',
            'route_name' => 'portal.qr-codes.index',
            'active_when' => ['portal.qr-codes.index', 'portal.qr-codes.create', 'portal.qr-codes.edit', 'portal.qr-codes.type-content'],
            'icon' => 'fa-light fa-qrcode',
            'order' => 10,
            'visible' => fn () => ! auth()->user()?->plan
                || (auth()->user()?->canUsePlanFeature('qr_codes') ?? false)
                || (auth()->user()?->canUsePlanFeature('link_bio_qr_codes') ?? false),
        ]);

        register_user_dashboard_item('app-qr-codes.overview', [
            'title' => 'Link Bio & QR',
            'view' => 'appqrcodes::dashboard.user-overview',
            'width' => 'full',
            'order' => 18,
            'route_name' => 'portal.qr-codes.index',
            'visible' => fn ($user): bool => $user !== null && (
                ! $user?->plan
                || ($user?->canUsePlanFeature('qr_codes') ?? false)
                || ($user?->canUsePlanFeature('link_bio_qr_codes') ?? false)
                || ($user?->canUsePlanFeature('link_bio') ?? false)
            ),
            'data' => function ($user): array {
                $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
                $bioPages = LinkBioPage::query()->ownedBy($ownerUserId);
                $qrCodes = AppQrCode::query()->where('owner_user_id', $ownerUserId);
                $verifiedDomains = AppCustomDomain::query()
                    ->where('owner_user_id', $ownerUserId)
                    ->where('status', 'verified');
                $chartStart = CarbonImmutable::now()->subDays(6)->startOfDay();
                $chartDays = collect(range(0, 6))->map(fn (int $day): CarbonImmutable => $chartStart->addDays($day));
                $scanCountsByDay = AppQrScanEvent::query()
                    ->where('owner_user_id', $ownerUserId)
                    ->where('created_at', '>=', $chartStart)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $bioEventCountsByDay = LinkBioEvent::query()
                    ->whereHas('page', fn ($query) => $query->where('owner_user_id', $ownerUserId))
                    ->where('created_at', '>=', $chartStart)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $qrTypeLabels = collect(AppQrCode::typeCatalog())->pluck('label', 'key');

                return [
                    'metrics' => [
                        'bio_pages' => (clone $bioPages)->count(),
                        'published_bio_pages' => (clone $bioPages)->where('is_published', true)->count(),
                        'qr_codes' => (clone $qrCodes)->count(),
                        'active_qr_codes' => (clone $qrCodes)->where('status', 'active')->count(),
                        'qr_scans' => AppQrScanEvent::query()->where('owner_user_id', $ownerUserId)->count(),
                        'bio_events' => LinkBioEvent::query()
                            ->whereHas('page', fn ($query) => $query->where('owner_user_id', $ownerUserId))
                            ->count(),
                        'verified_domains' => (clone $verifiedDomains)->count(),
                    ],
                    'onboardingChecklist' => [
                        [
                            'key' => 'bio',
                            'label' => __('Create your first Bio page'),
                            'description' => __('Publish a mobile landing page for links, offers, files, and CTAs.'),
                            'done' => (clone $bioPages)->exists(),
                            'route' => Route::has('portal.link-bio.create') ? route('portal.link-bio.create') : null,
                            'done_route' => Route::has('portal.link-bio.index') ? route('portal.link-bio.index') : null,
                            'action' => __('Create Bio'),
                            'icon' => 'fa-link',
                            'color' => '#7c3aed',
                        ],
                        [
                            'key' => 'qr',
                            'label' => __('Create your first QR campaign'),
                            'description' => __('Generate a dynamic QR code that can track scans and keep links editable.'),
                            'done' => (clone $qrCodes)->exists(),
                            'route' => Route::has('portal.qr-codes.create') ? route('portal.qr-codes.create') : null,
                            'done_route' => Route::has('portal.qr-codes.index') ? route('portal.qr-codes.index') : null,
                            'action' => __('Create QR'),
                            'icon' => 'fa-qrcode',
                            'color' => '#2563eb',
                        ],
                        [
                            'key' => 'domain',
                            'label' => __('Add a custom domain'),
                            'description' => __('Route public QR and Bio links through your own branded domain.'),
                            'done' => (clone $verifiedDomains)->exists(),
                            'route' => Route::has('portal.brand.domains') ? route('portal.brand.domains') : null,
                            'done_route' => Route::has('portal.brand.domains') ? route('portal.brand.domains') : null,
                            'action' => __('Add domain'),
                            'icon' => 'fa-globe-pointer',
                            'color' => '#0f766e',
                        ],
                        [
                            'key' => 'analytics',
                            'label' => __('Check analytics'),
                            'description' => __('Review scans, Bio views, clicks, traffic sources, and campaign mix.'),
                            'done' => ((int) AppQrScanEvent::query()->where('owner_user_id', $ownerUserId)->count()) > 0
                                || ((int) LinkBioEvent::query()->whereHas('page', fn ($query) => $query->where('owner_user_id', $ownerUserId))->count()) > 0,
                            'route' => Route::has('portal.qr-codes.analytics') ? route('portal.qr-codes.analytics') : null,
                            'done_route' => Route::has('portal.qr-codes.analytics') ? route('portal.qr-codes.analytics') : null,
                            'action' => __('View analytics'),
                            'icon' => 'fa-chart-line',
                            'color' => '#f59e0b',
                        ],
                    ],
                    'qrLimits' => app(QrPlanLimits::class)->summary($user, $ownerUserId),
                    'recentQrCodes' => (clone $qrCodes)
                        ->latest()
                        ->limit(5)
                        ->get(['id', 'name', 'type', 'status', 'scans_count', 'updated_at']),
                    'charts' => [
                        'activity' => $chartDays
                            ->map(function (CarbonImmutable $date) use ($scanCountsByDay, $bioEventCountsByDay): array {
                                $key = $date->toDateString();

                                return [
                                    'label' => $date->format('M j'),
                                    'scans' => (int) ($scanCountsByDay[$key] ?? 0),
                                    'events' => (int) ($bioEventCountsByDay[$key] ?? 0),
                                ];
                            })
                            ->values()
                            ->all(),
                        'qr_types' => (clone $qrCodes)
                            ->select('type', DB::raw('COUNT(*) as aggregate'))
                            ->groupBy('type')
                            ->orderByDesc('aggregate')
                            ->limit(5)
                            ->get()
                            ->map(fn ($item): array => [
                                'label' => (string) ($qrTypeLabels[$item->type] ?? str((string) $item->type)->replace('_', ' ')->title()),
                                'value' => (int) $item->aggregate,
                            ])
                            ->all(),
                        'bio_events' => LinkBioEvent::query()
                            ->whereHas('page', fn ($query) => $query->where('owner_user_id', $ownerUserId))
                            ->select('type', DB::raw('COUNT(*) as aggregate'))
                            ->groupBy('type')
                            ->orderByDesc('aggregate')
                            ->limit(4)
                            ->get()
                            ->map(fn ($item): array => [
                                'label' => str((string) $item->type)->replace('_', ' ')->title()->value(),
                                'value' => (int) $item->aggregate,
                            ])
                            ->all(),
                    ],
                    'routes' => [
                        'bio_pages' => Route::has('portal.link-bio.index') ? route('portal.link-bio.index') : null,
                        'create_bio' => Route::has('portal.link-bio.create') ? route('portal.link-bio.create') : null,
                        'qr_codes' => Route::has('portal.qr-codes.index') ? route('portal.qr-codes.index') : null,
                        'create_qr' => Route::has('portal.qr-codes.create') ? route('portal.qr-codes.create') : null,
                        'analytics' => Route::has('portal.qr-codes.analytics') ? route('portal.qr-codes.analytics') : null,
                        'domains' => Route::has('portal.brand.domains') ? route('portal.brand.domains') : null,
                    ],
                ];
            },
        ]);

        register_admin_dashboard_item('admin-qr-codes.snapshot', [
            'title' => 'Link Bio & QR',
            'view' => 'appqrcodes::dashboard.admin-snapshot',
            'width' => 'full',
            'order' => 7,
            'region' => 'welcome',
            'data' => function (): array {
                $now = CarbonImmutable::now();
                $last7Start = $now->subDays(6)->startOfDay();
                $last30Start = $now->subDays(29)->startOfDay();
                $chartDays = collect(range(0, 6))->map(fn (int $day): CarbonImmutable => $last7Start->addDays($day));
                $scanCountsByDay = AppQrScanEvent::query()
                    ->where('created_at', '>=', $last7Start)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $bioEventCountsByDay = LinkBioEvent::query()
                    ->where('created_at', '>=', $last7Start)
                    ->selectRaw('DATE(created_at) as event_date, COUNT(*) as aggregate')
                    ->groupBy('event_date')
                    ->pluck('aggregate', 'event_date');
                $qrTypeLabels = collect(AppQrCode::typeCatalog())->pluck('label', 'key');
                $bioEventsByType = LinkBioEvent::query()
                    ->select('type', DB::raw('COUNT(*) as aggregate'))
                    ->groupBy('type')
                    ->pluck('aggregate', 'type');
                $qrOwnerIds = AppQrCode::query()->distinct()->pluck('owner_user_id');
                $bioOwnerIds = LinkBioPage::query()->distinct()->pluck('owner_user_id');
                $qrOwners = $qrOwnerIds->count();
                $bioOwners = $bioOwnerIds->count();

                return [
                    'metrics' => [
                        'bio_pages' => LinkBioPage::query()->count(),
                        'published_bio_pages' => LinkBioPage::query()->where('is_published', true)->count(),
                        'draft_bio_pages' => LinkBioPage::query()->where('is_published', false)->count(),
                        'qr_codes' => AppQrCode::query()->count(),
                        'active_qr_codes' => AppQrCode::query()->where('status', 'active')->count(),
                        'draft_qr_codes' => AppQrCode::query()->where('status', 'draft')->count(),
                        'qr_scans' => AppQrScanEvent::query()->count(),
                        'qr_scans_7d' => AppQrScanEvent::query()->where('created_at', '>=', $last7Start)->count(),
                        'qr_scans_30d' => AppQrScanEvent::query()->where('created_at', '>=', $last30Start)->count(),
                        'bio_events' => LinkBioEvent::query()->count(),
                        'bio_events_7d' => LinkBioEvent::query()->where('created_at', '>=', $last7Start)->count(),
                        'bio_views' => (int) ($bioEventsByType['view'] ?? 0),
                        'bio_clicks' => (int) ($bioEventsByType['click'] ?? 0),
                        'owners' => $qrOwners,
                        'bio_owners' => $bioOwners,
                        'total_owners' => $qrOwnerIds->merge($bioOwnerIds)->filter()->unique()->count(),
                    ],
                    'charts' => [
                        'activity' => $chartDays
                            ->map(function (CarbonImmutable $date) use ($scanCountsByDay, $bioEventCountsByDay): array {
                                $key = $date->toDateString();

                                return [
                                    'label' => $date->format('M j'),
                                    'scans' => (int) ($scanCountsByDay[$key] ?? 0),
                                    'events' => (int) ($bioEventCountsByDay[$key] ?? 0),
                                ];
                            })
                            ->values()
                            ->all(),
                        'qr_types' => AppQrCode::query()
                            ->select('type', DB::raw('COUNT(*) as aggregate'))
                            ->groupBy('type')
                            ->orderByDesc('aggregate')
                            ->limit(6)
                            ->get()
                            ->map(fn ($item): array => [
                                'label' => (string) ($qrTypeLabels[$item->type] ?? str((string) $item->type)->replace('_', ' ')->title()),
                                'value' => (int) $item->aggregate,
                            ])
                            ->all(),
                        'qr_status' => AppQrCode::query()
                            ->select('status', DB::raw('COUNT(*) as aggregate'))
                            ->groupBy('status')
                            ->orderByDesc('aggregate')
                            ->get()
                            ->map(fn ($item): array => [
                                'label' => str((string) $item->status)->replace('_', ' ')->title()->value(),
                                'value' => (int) $item->aggregate,
                            ])
                            ->all(),
                        'bio_events' => $bioEventsByType
                            ->map(fn ($value, $type): array => [
                                'label' => str((string) $type)->replace('_', ' ')->title()->value(),
                                'value' => (int) $value,
                            ])
                            ->values()
                            ->all(),
                    ],
                    'topQrCodes' => AppQrCode::query()
                        ->orderByDesc('scans_count')
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'name', 'type', 'status', 'scans_count']),
                    'topBioPages' => LinkBioPage::query()
                        ->withCount('events')
                        ->orderByDesc('events_count')
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'title', 'slug', 'is_published']),
                    'routes' => [
                        'qr_codes' => Route::has('portal.qr-codes.index') ? route('portal.qr-codes.index') : null,
                        'analytics' => Route::has('portal.qr-codes.analytics') ? route('portal.qr-codes.analytics') : null,
                        'users' => Route::has('admin-users.index') ? route('admin-users.index') : null,
                    ],
                ];
            },
        ]);

        $this->app->booted(function (): void {
            $this->placeQrCodesSectionBeforeContentTools();
            $this->placeAdminDashboardItemNearTop();

            \Pricing::addSubFeatures([
                [
                    'sort' => 240,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'qr_codes',
                    'label' => __('Advanced QR codes'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
                [
                    'sort' => 241,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'max_qr_codes',
                    'label' => __('Max QR codes'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 242,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'max_qr_scans_monthly',
                    'label' => __('Monthly QR scans'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 243,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'max_dynamic_rules_per_qr',
                    'label' => __('Dynamic rules per QR'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
            ]);

            \Pricing::add([
                'sort' => 640,
                'key' => 'qr_codes',
                'label' => __('Advanced QR codes'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }

    protected function placeAdminDashboardItemNearTop(): void
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

        $topItems = ['admin-short-links.snapshot'];
        $stored = array_values(array_filter(
            $stored,
            fn ($id): bool => is_string($id) && $id !== '' && ! in_array($id, $topItems, true)
        ));

        foreach (array_reverse($topItems) as $itemId) {
            array_unshift($stored, $itemId);
        }

        $options->set($key, $stored);
    }

    protected function placeQrCodesSectionBeforeContentTools(): void
    {
        $options = app(OptionStore::class);
        $stored = $options->get('admin_menu_sidebar_overrides', []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored) || ! is_array($stored['user']['sections'] ?? null)) {
            return;
        }

        $sections = array_values(array_filter((array) $stored['user']['sections'], fn ($section): bool => is_array($section)));
        $sections = array_values(array_filter(
            $sections,
            fn ($section): bool => ($section['key'] ?? null) !== 'qr-codes'
        ));
        $qrSection = [
            'key' => 'qr-codes',
            'label' => 'QR Codes',
            'items' => [
                ['key' => 'portal.qr-codes.index', 'label' => 'Campaigns'],
                ['key' => 'portal.qr-codes.ai', 'label' => 'AI QR Codes'],
                ['key' => 'portal.qr-codes.analytics', 'label' => 'Analytics'],
                ['key' => 'portal.qr-codes.pre-printed', 'label' => 'Pre-Printed QR'],
                ['key' => 'portal.qr-codes.bulk', 'label' => 'Bulk Generation'],
            ],
        ];

        $contentToolsIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'content-tools');

        if ($contentToolsIndex === false) {
            $sections[] = $qrSection;
        } else {
            array_splice($sections, (int) $contentToolsIndex, 0, [$qrSection]);
        }

        $stored['user']['sections'] = array_values($sections);
        $options->set('admin_menu_sidebar_overrides', $stored);
    }
}
