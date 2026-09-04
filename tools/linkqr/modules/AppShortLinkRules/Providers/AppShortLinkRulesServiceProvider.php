<?php

namespace Modules\AppShortLinkRules\Providers;

use Illuminate\Support\ServiceProvider;

class AppShortLinkRulesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
