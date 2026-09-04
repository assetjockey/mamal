<?php

namespace Modules\AppShortLinkAnalytics\Providers;

use Illuminate\Support\ServiceProvider;

class AppShortLinkAnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appshortlinkanalytics');

        register_user_sidebar_item('short-links', [
            'label' => 'Analytics',
            'route_name' => 'portal.short-links.analytics',
            'active_when' => ['portal.short-links.analytics'],
            'icon' => 'fa-light fa-chart-line',
            'order' => 20,
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('url_shorteners') ?? false),
        ]);
    }
}
