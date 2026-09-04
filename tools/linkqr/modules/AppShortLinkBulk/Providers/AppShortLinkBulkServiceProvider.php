<?php

namespace Modules\AppShortLinkBulk\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;

class AppShortLinkBulkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appshortlinkbulk');

        register_user_sidebar_item('short-links', [
            'label' => 'Bulk Import',
            'route_name' => 'portal.short-links.bulk',
            'active_when' => ['portal.short-links.bulk'],
            'icon' => 'fa-light fa-file-csv',
            'order' => 15,
            'visible' => fn () => app(ShortLinkPlanLimits::class)->canUseFeature(auth()->user(), 'url_shorteners'),
        ]);
    }
}
