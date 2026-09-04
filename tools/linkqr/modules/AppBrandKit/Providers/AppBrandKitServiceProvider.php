<?php

namespace Modules\AppBrandKit\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppBrandKit\Support\BrandSidebarIntegrator;

class AppBrandKitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appbrandkit');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'brand_kit',
            'label' => __('Brand Kit'),
            'type' => 'toggle',
            'order' => 146,
            'default' => true,
        ]);

        BrandSidebarIntegrator::registerSharedItem([
            'label' => 'Brand Kit',
            'route_name' => 'portal.brand.kit',
            'active_when' => ['portal.brand.kit'],
            'icon' => 'fa-light fa-palette',
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('brand_kit') ?? false),
        ], 60);

        $this->app->booted(function (): void {
            BrandSidebarIntegrator::sync();

            \Pricing::addSubFeatures([
                'sort' => 245,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'brand_kit',
                'label' => __('Brand Kit'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 655,
                'key' => 'brand_kit',
                'label' => __('Brand Kit'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
