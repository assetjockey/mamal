<?php

namespace Modules\AppShortLinkCampaigns\Providers;

use Illuminate\Support\ServiceProvider;

class AppShortLinkCampaignsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
