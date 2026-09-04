<?php

namespace Modules\AppQRTypeBuilder\Providers;

use Illuminate\Support\ServiceProvider;

class AppQRTypeBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqrtypebuilder');

        register_plan_permission([
            'key' => 'qr_type_builders',
            'label' => __('QR Type Builders'),
            'type' => 'toggle',
            'order' => 152,
            'default' => true,
        ]);
    }
}
