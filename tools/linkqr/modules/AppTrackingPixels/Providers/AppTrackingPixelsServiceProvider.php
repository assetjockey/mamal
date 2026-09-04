<?php

namespace Modules\AppTrackingPixels\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppBrandKit\Support\BrandSidebarIntegrator;

class AppTrackingPixelsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'apptrackingpixels');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'tracking_pixels',
            'label' => __('Tracking Pixels'),
            'type' => 'toggle',
            'order' => 150,
            'default' => true,
        ]);

        BrandSidebarIntegrator::registerSharedItem([
            'label' => 'Tracking Pixels',
            'route_name' => 'portal.brand.tracking-pixels',
            'active_when' => ['portal.brand.tracking-pixels'],
            'icon' => 'fa-light fa-bullseye-arrow',
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('tracking_pixels') ?? false),
        ], 80);

        $this->app->booted(function (): void {
            BrandSidebarIntegrator::sync();

            \Pricing::addSubFeatures([
                'sort' => 247,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'tracking_pixels',
                'label' => __('Tracking Pixels'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 657,
                'key' => 'tracking_pixels',
                'label' => __('Tracking Pixels'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
