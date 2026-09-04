<?php

namespace Modules\AppLinkBio\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;
use Modules\AppLinkBio\Support\UsernameAvailability;

class AppLinkBioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.applinkbio');
        $this->app->singleton(LinkBioAccess::class);
        $this->app->singleton(UsernameAvailability::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applinkbio');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_setting_item('general', [
            'label' => 'Link Bio',
            'description' => 'Set default templates, publishing status, and branding text for new bio pages.',
            'route_name' => 'settings.link-bio',
            'active_when' => ['settings.link-bio'],
            'order' => 108,
        ]);

        register_plan_permission([
            'key' => 'link_bio',
            'label' => __('Link Bio'),
            'type' => 'config',
            'order' => 145,
            'fields' => [
                [
                    'key' => 'max_link_bio_pages',
                    'label' => __('Max. bio pages'),
                    'type' => 'number',
                    'default' => 1,
                ],
                [
                    'key' => 'link_bio_remove_branding',
                    'label' => __('Remove Link Bio branding'),
                    'type' => 'boolean',
                    'default' => '0',
                ],
            ],
        ]);

        register_plan_permission([
            'key' => 'link_bio_qr_codes',
            'label' => __('Link Bio QR Codes'),
            'type' => 'toggle',
            'order' => 150,
            'default' => true,
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                [
                    'sort' => 240,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'link_bio',
                    'label' => __('Link Bio'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
                [
                    'sort' => 250,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'max_link_bio_pages',
                    'label' => __('Max. bio pages'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 255,
                    'parent' => 'features',
                    'tab_id' => 'marketing',
                    'tab_name' => __('Marketing'),
                    'key' => 'link_bio_remove_branding',
                    'label' => __('Remove Link Bio branding'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
            ]);

            \Pricing::add([
                [
                    'sort' => 650,
                    'key' => 'link_bio',
                    'label' => __('Link Bio'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
            ]);
        });
    }
}
