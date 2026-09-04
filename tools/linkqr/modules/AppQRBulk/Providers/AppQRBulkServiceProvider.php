<?php

namespace Modules\AppQRBulk\Providers;

use Illuminate\Support\ServiceProvider;

class AppQRBulkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqrbulk');

        register_plan_permission([
            'key' => 'qr_bulk_generation',
            'label' => __('QR Bulk Generation'),
            'type' => 'config',
            'order' => 148,
            'default' => true,
            'fields' => [
                [
                    'key' => 'max_qr_bulk_rows',
                    'label' => __('Max bulk rows'),
                    'type' => 'number',
                    'default' => -1,
                    'description' => __('Maximum CSV rows per bulk import. Use -1 for unlimited.'),
                ],
            ],
        ]);

        register_user_sidebar_item('qr-codes', [
            'label' => 'Bulk Generation',
            'route_name' => 'portal.qr-codes.bulk',
            'active_when' => ['portal.qr-codes.bulk'],
            'icon' => 'fa-light fa-file-csv',
            'order' => 50,
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('qr_bulk_generation') ?? false),
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                [
                    'sort' => 244,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'qr_bulk_generation',
                    'label' => __('QR Bulk Generation'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
                [
                    'sort' => 245,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'max_qr_bulk_rows',
                    'label' => __('Max bulk rows'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
            ]);
        });
    }
}
