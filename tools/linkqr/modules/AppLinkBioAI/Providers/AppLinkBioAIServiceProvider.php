<?php

namespace Modules\AppLinkBioAI\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;

class AppLinkBioAIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applinkbioai');

        register_credit_action([
            'key' => 'link_bio_ai_assistant',
            'plan_key' => 'credit_cost_link_bio_ai_assistant',
            'label' => __('Link Bio AI Assistant'),
            'default_cost' => 1,
            'order' => 30,
            'description' => __('Credits deducted each time AI generates a Link Bio draft.'),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => 'AI Bio Assistant',
            'route_name' => 'portal.link-bio.ai-assistant',
            'active_when' => ['portal.link-bio.ai-assistant'],
            'icon' => 'fa-light fa-sparkles',
            'order' => 40,
            'visible' => fn () => app(LinkBioAccess::class)->canUseAiBio(auth()->user()),
        ]);

        register_plan_permission([
            'key' => 'link_bio_ai_assistant',
            'label' => __('Link Bio AI Assistant'),
            'type' => 'toggle',
            'order' => 147,
            'default' => false,
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 254,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'link_bio_ai_assistant',
                'label' => __('AI Bio Assistant'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 652,
                'key' => 'link_bio_ai_assistant',
                'label' => __('AI Bio Assistant'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);
        });
    }
}
