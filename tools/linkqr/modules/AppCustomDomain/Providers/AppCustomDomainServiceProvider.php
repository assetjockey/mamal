<?php

namespace Modules\AppCustomDomain\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppBrandKit\Support\BrandSidebarIntegrator;

class AppCustomDomainServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appcustomdomain');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'qr_custom_domains',
            'label' => __('Custom Domains'),
            'type' => 'toggle',
            'order' => 147,
            'default' => true,
        ]);

        BrandSidebarIntegrator::registerSharedItem([
            'label' => 'Custom Domains',
            'route_name' => 'portal.brand.domains',
            'active_when' => ['portal.brand.domains', 'portal.qr-codes.domains'],
            'icon' => 'fa-light fa-globe-pointer',
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('qr_custom_domains') ?? false),
        ], 70);

        $this->app->booted(function (): void {
            BrandSidebarIntegrator::sync();

            \Pricing::addSubFeatures([
                'sort' => 246,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'qr_custom_domains',
                'label' => __('Custom Domains'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 656,
                'key' => 'qr_custom_domains',
                'label' => __('Custom Domains'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
