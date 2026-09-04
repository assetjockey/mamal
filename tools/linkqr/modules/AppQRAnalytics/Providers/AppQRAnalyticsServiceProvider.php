<?php

namespace Modules\AppQRAnalytics\Providers;

use Illuminate\Support\ServiceProvider;

class AppQRAnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqranalytics');

        register_plan_permission([
            'key' => 'qr_analytics',
            'label' => __('QR Analytics'),
            'type' => 'toggle',
            'order' => 146,
            'default' => true,
        ]);

        register_user_sidebar_item('qr-codes', [
            'label' => 'Analytics',
            'route_name' => 'portal.qr-codes.analytics',
            'active_when' => ['portal.qr-codes.analytics', 'portal.qr-codes.campaign.analytics'],
            'icon' => 'fa-light fa-chart-line',
            'order' => 30,
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('qr_analytics') ?? false),
        ]);
    }
}
