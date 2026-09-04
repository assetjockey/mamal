<?php

namespace Modules\AppShortLinkApi\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;

class AppShortLinkApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appshortlinkapi');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_user_sidebar_item('short-links', [
            'label' => 'API & Webhooks',
            'route_name' => 'portal.short-links.api',
            'active_when' => ['portal.short-links.api'],
            'icon' => 'fa-light fa-code',
            'order' => 30,
            'visible' => fn () => app(ShortLinkPlanLimits::class)->canUseFeature(auth()->user(), 'url_shorteners'),
        ]);
    }
}
