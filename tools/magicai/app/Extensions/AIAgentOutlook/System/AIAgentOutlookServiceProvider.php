<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System;

use App\Extensions\AIAgent\System\AIAgentServiceProvider;
use App\Extensions\AIAgent\System\Engine\AIAgentActionRegistry;
use App\Extensions\AIAgentOutlook\System\Actions\OutlookAction;
use App\Extensions\AIAgentOutlook\System\Http\Controllers\AIAgentOutlookSettingController;
use App\Extensions\AIAgentOutlook\System\Http\Controllers\OAuth\OutlookOAuthController;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AIAgentOutlookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists(AIAgentServiceProvider::class)) {
            return;
        }

        $this->registerConfig()
            ->registerViews()
            ->registerRoutes()
            ->registerAction();
    }

    private function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai-agent-outlook.php', 'ai-agent-outlook');

        return $this;
    }

    private function registerViews(): static
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ai-agent-outlook');

        return $this;
    }

    private function registerAction(): static
    {
        $registry = $this->app->make(AIAgentActionRegistry::class);
        $registry->register('outlook', OutlookAction::class);

        return $this;
    }

    private function registerRoutes(): static
    {
        /** @var Router $router */
        $router = $this->app['router'];

        $router->group([
            'middleware' => ['web', 'auth'],
            'prefix'     => 'dashboard/user/ai-agent/connectors',
            'as'         => 'dashboard.user.ai-agent.connectors.',
        ], function (Router $router): void {
            $router->get('outlook/redirect', [OutlookOAuthController::class, 'redirect'])->name('outlook.redirect');
            $router->get('outlook/callback', [OutlookOAuthController::class, 'callback'])->name('outlook.callback');
        });

        $router->group([
            'middleware' => ['web', 'auth', 'admin'],
            'prefix'     => 'dashboard/admin/ai-agent',
            'as'         => 'dashboard.admin.ai-agent.',
        ], function (Router $router): void {
            $router->get('outlook/settings', [AIAgentOutlookSettingController::class, 'index'])->name('outlook.settings');
            $router->post('outlook/settings', [AIAgentOutlookSettingController::class, 'update'])->name('outlook.settings.update');
        });

        return $this;
    }
}
