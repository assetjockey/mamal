<?php

namespace Modules\AppLinkBioABTesting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;

class AppLinkBioABTestingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applinkbioabtesting');

        register_user_sidebar_item('workspace', [
            'label' => 'A/B Tests',
            'route_name' => 'portal.link-bio.ab-testing',
            'active_when' => ['portal.link-bio.ab-testing'],
            'icon' => 'fa-light fa-flask',
            'order' => 45,
            'visible' => fn () => app(LinkBioAccess::class)->enabled(auth()->user())
                && (! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('link_bio_ab_testing') ?? false)),
        ]);

        register_plan_permission([
            'key' => 'link_bio_ab_testing',
            'label' => __('Link Bio A/B Testing'),
            'type' => 'toggle',
            'order' => 149,
            'default' => true,
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 253,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'link_bio_ab_testing',
                'label' => __('Link Bio A/B testing'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 654,
                'key' => 'link_bio_ab_testing',
                'label' => __('Link Bio A/B testing'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
