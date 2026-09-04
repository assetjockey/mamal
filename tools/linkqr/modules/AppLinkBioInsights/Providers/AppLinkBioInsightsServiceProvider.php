<?php

namespace Modules\AppLinkBioInsights\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;

class AppLinkBioInsightsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applinkbioinsights');

        register_user_sidebar_item('workspace', [
            'label' => 'Analytics',
            'route_name' => 'portal.link-bio.insights',
            'active_when' => ['portal.link-bio.insights'],
            'icon' => 'fa-light fa-chart-mixed',
            'order' => 50,
            'visible' => fn () => app(LinkBioAccess::class)->canUseAnalytics(auth()->user()),
        ]);

        register_plan_permission([
            'key' => 'link_bio_analytics',
            'label' => __('Link Bio Analytics'),
            'type' => 'toggle',
            'order' => 148,
            'default' => true,
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 252,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'link_bio_analytics',
                'label' => __('Link Bio analytics'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 653,
                'key' => 'link_bio_analytics',
                'label' => __('Link Bio analytics'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
