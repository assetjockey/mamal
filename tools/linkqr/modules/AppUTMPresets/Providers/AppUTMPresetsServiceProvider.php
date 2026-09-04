<?php

namespace Modules\AppUTMPresets\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppBrandKit\Support\BrandSidebarIntegrator;

class AppUTMPresetsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'apputmpresets');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'utm_presets',
            'label' => __('UTM Presets'),
            'type' => 'toggle',
            'order' => 151,
            'default' => true,
        ]);

        BrandSidebarIntegrator::registerSharedItem([
            'label' => 'UTM Presets',
            'route_name' => 'portal.brand.utm-presets',
            'active_when' => ['portal.brand.utm-presets'],
            'icon' => 'fa-light fa-tags',
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('utm_presets') ?? false),
        ], 90);

        $this->app->booted(function (): void {
            BrandSidebarIntegrator::sync();

            \Pricing::addSubFeatures([
                'sort' => 248,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'utm_presets',
                'label' => __('UTM Presets'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 658,
                'key' => 'utm_presets',
                'label' => __('UTM Presets'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
