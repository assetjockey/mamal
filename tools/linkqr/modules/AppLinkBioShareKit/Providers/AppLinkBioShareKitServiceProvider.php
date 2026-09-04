<?php

namespace Modules\AppLinkBioShareKit\Providers;

use Illuminate\Support\ServiceProvider;

class AppLinkBioShareKitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applinkbiosharekit');
    }
}
