<?php

namespace Modules\AppQRPrePrinted\Providers;

use Illuminate\Support\ServiceProvider;

class AppQRPrePrintedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqrpreprinted');

        register_plan_permission([
            'key' => 'qr_pre_printed',
            'label' => __('Pre-Printed QR'),
            'type' => 'toggle',
            'order' => 149,
            'default' => true,
        ]);

        register_user_sidebar_item('qr-codes', [
            'label' => 'Pre-Printed QR',
            'route_name' => 'portal.qr-codes.pre-printed',
            'active_when' => ['portal.qr-codes.pre-printed'],
            'icon' => 'fa-light fa-print',
            'order' => 45,
            'visible' => fn () => ! auth()->user()?->plan
                || (auth()->user()?->canUsePlanFeature('qr_pre_printed') ?? false)
                || (auth()->user()?->canUsePlanFeature('qr_codes') ?? false)
                || (auth()->user()?->canUsePlanFeature('link_bio_qr_codes') ?? false),
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 247,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'qr_pre_printed',
                'label' => __('Pre-Printed QR'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 657,
                'key' => 'qr_pre_printed',
                'label' => __('Pre-Printed QR'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
