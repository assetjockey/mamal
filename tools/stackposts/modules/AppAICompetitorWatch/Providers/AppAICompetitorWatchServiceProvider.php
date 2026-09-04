<?php

namespace Modules\AppAICompetitorWatch\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AppAIStudio\Support\AIStudioPlanFeature;
use Modules\AppTeams\Support\TeamPermissionRegistry;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppAICompetitorWatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaicompetitorwatch');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appaicompetitorwatch');

        TeamPermissionRegistry::registerModule('ai_competitor_watch', [
            'label' => __('AI Competitor Watch'),
            'configurable' => true,
            'enabled_by_default' => true,
        ]);

        register_credit_action([
            'key' => 'ai_studio_competitor_watch',
            'plan_key' => 'credit_cost_ai_studio_competitor_watch',
            'label' => __('AI Competitor Watch'),
            'default_cost' => 2,
            'order' => 25,
            'description' => __('Credits deducted each time AI analyzes competitor positioning and content opportunities.'),
        ]);

        register_plan_permission([
            'key' => 'ai_studio',
            'fields' => [
                ['key' => 'ai_studio_competitor_watch', 'label' => __('Competitor Watch'), 'type' => 'boolean', 'default' => '1', 'order' => 28],
            ],
        ]);

        register_user_sidebar_item('workspace', [
            'label' => 'Competitor Watch',
            'route_name' => 'portal.ai-competitor-watch',
            'active_when' => ['portal.ai-competitor-watch'],
            'icon' => 'fa-light fa-binoculars',
            'order' => 62,
            'visible' => function (): bool {
                if (! Route::has('portal.ai-competitor-watch')) {
                    return false;
                }

                $user = auth()->user();
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;

                return TeamWorkspaceAccess::teamHasModule($team, 'ai_competitor_watch')
                    && ($planOwner?->canUsePlanFeature('ai_studio') ?? false)
                    && AIStudioPlanFeature::enabled($planOwner, 'ai_studio_competitor_watch');
            },
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 86,
                'parent' => 'features',
                'tab_id' => 'ai_studio',
                'tab_name' => __('AI Studio'),
                'standalone' => true,
                'standalone_sort' => 200,
                'standalone_key' => 'ai_studio',
                'key' => 'ai_studio_competitor_watch',
                'label' => __('Competitor Watch'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
