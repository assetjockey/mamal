<?php

namespace Modules\AppLinkBioTemplates\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;

class AppLinkBioTemplatesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        register_user_sidebar_item('workspace', [
            'label' => 'Templates',
            'route_name' => 'portal.link-bio.templates',
            'active_when' => ['portal.link-bio.templates'],
            'icon' => 'fa-light fa-swatchbook',
            'order' => 30,
            'visible' => fn () => app(LinkBioAccess::class)->canUseTemplates(auth()->user()),
        ]);

        register_plan_permission([
            'key' => 'link_bio_templates',
            'label' => __('Link Bio Templates'),
            'type' => 'toggle',
            'order' => 146,
            'default' => true,
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 251,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'link_bio_templates',
                'label' => __('Link Bio templates'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 651,
                'key' => 'link_bio_templates',
                'label' => __('Link Bio templates'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
