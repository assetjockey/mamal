<?php

namespace Modules\AppAIStudio\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppAIStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaistudio');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appaistudio');

        register_credit_action([
            'key' => 'ai_studio_generate_captions',
            'plan_key' => 'credit_cost_ai_studio_generate_captions',
            'label' => __('AI Studio Caption Generation'),
            'default_cost' => 1,
            'order' => 22,
            'description' => __('Credits deducted each time AI Studio generates caption variants.'),
        ]);

        register_credit_action([
            'key' => 'ai_studio_repurpose_content',
            'plan_key' => 'credit_cost_ai_studio_repurpose_content',
            'label' => __('AI Studio Repurpose Content'),
            'default_cost' => 1,
            'order' => 23,
            'description' => __('Credits deducted each time AI Studio repurposes source content.'),
        ]);

        register_credit_action([
            'key' => 'ai_studio_plan_calendar',
            'plan_key' => 'credit_cost_ai_studio_plan_calendar',
            'label' => __('AI Studio Calendar Planning'),
            'default_cost' => 1,
            'order' => 24,
            'description' => __('Credits deducted each time AI Studio creates a content plan.'),
        ]);

        register_credit_action([
            'key' => 'ai_studio_generate_image',
            'plan_key' => 'credit_cost_ai_studio_generate_image',
            'label' => __('AI Studio Image Generation'),
            'default_cost' => 3,
            'order' => 26,
            'description' => __('Credits deducted each time AI Studio generates an image.'),
        ]);

        register_plan_permission([
            'key' => 'ai_studio',
            'label' => __('AI Studio'),
            'type' => 'config',
            'order' => 81,
            'fields' => [
                ['key' => 'ai_studio_caption_generator', 'label' => __('AI Content'), 'type' => 'boolean', 'default' => '1'],
                ['key' => 'ai_studio_repurpose', 'label' => __('Repurpose'), 'type' => 'boolean', 'default' => '1'],
                ['key' => 'ai_studio_content_planner', 'label' => __('Content Planner'), 'type' => 'boolean', 'default' => '1'],
                ['key' => 'ai_studio_image', 'label' => __('AI Image'), 'type' => 'boolean', 'default' => '1'],
            ],
        ]);

        register_user_sidebar_item('content-tools', [
            'label' => 'AI Studio',
            'route_name' => 'portal.ai-studio',
            'active_when' => ['portal.ai-studio', 'portal.ai-studio.*'],
            'icon' => 'fa-light fa-wand-magic-sparkles',
            'order' => 20,
            'visible' => function (): bool {
                $user = auth()->user();
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;

                if (! TeamWorkspaceAccess::teamHasModule($team, 'publishing')) {
                    return false;
                }

                return $planOwner?->canUsePlanFeature('ai_studio') ?? false;
            },
            'children_resolver' => function (): array {
                $user = auth()->user();
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;
                $enabled = fn (string $feature): bool => $planOwner?->canUsePlanFeature($feature) ?? false;

                return array_values(array_filter([
                    [
                        'label' => __('AI Content'),
                        'route_name' => 'portal.ai-content',
                        'active_when' => ['portal.ai-content', 'portal.ai-studio'],
                        'order' => 10,
                        'visible' => $enabled('ai_studio_caption_generator'),
                    ],
                    [
                        'label' => __('AI Image'),
                        'route_name' => 'portal.ai-image',
                        'active_when' => ['portal.ai-image'],
                        'order' => 20,
                        'visible' => $enabled('ai_studio_image'),
                    ],
                    [
                        'label' => __('Repurpose'),
                        'route_name' => 'portal.ai-repurpose',
                        'active_when' => ['portal.ai-repurpose'],
                        'order' => 30,
                        'visible' => $enabled('ai_studio_repurpose'),
                    ],
                    [
                        'label' => __('Content Planner'),
                        'route_name' => 'portal.ai-content-planner',
                        'active_when' => ['portal.ai-content-planner'],
                        'order' => 40,
                        'visible' => $enabled('ai_studio_content_planner'),
                    ],
                ], fn (array $item): bool => (bool) ($item['visible'] ?? true)));
            },
        ]);

        register_user_dashboard_item('app-ai-studio.summary', [
            'title' => 'AI Studio',
            'view' => 'appaistudio::dashboard.user-summary',
            'width' => 'full',
            'order' => 23,
            'route_name' => 'portal.ai-studio',
            'data' => function ($user): array {
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;
                $enabled = fn (string $feature): bool => $planOwner?->canUsePlanFeature($feature) ?? false;
                $toolRoute = fn (string $routeName): ?string => Route::has($routeName) ? route($routeName) : null;
                $toolVisible = fn (string $routeName, string $feature): bool => Route::has($routeName) && $enabled($feature);
                $hasAiPublishingRoute = Route::has('portal.ai-publishing');
                $canUseAiPublishing = $hasAiPublishingRoute
                    && TeamWorkspaceAccess::teamHasModule($team, 'ai_publishing')
                    && ($planOwner?->canUsePlanFeature('ai_publishing') ?? false);

                return [
                    'tools' => array_values(array_filter([
                        [
                            'label' => __('AI Content'),
                            'description' => __('Generate captions and fresh draft directions before pushing work into publishing.'),
                            'route' => $toolRoute('portal.ai-content'),
                            'icon' => 'fa-light fa-pen-nib',
                            'tone' => 'rgba(var(--theme-accent-rgb),0.10)',
                            'visible' => $toolVisible('portal.ai-content', 'ai_studio_caption_generator'),
                        ],
                        [
                            'label' => __('AI Image'),
                            'description' => __('Create new visual assets for connected channels without leaving the current flow.'),
                            'route' => $toolRoute('portal.ai-image'),
                            'icon' => 'fa-light fa-image',
                            'tone' => 'rgba(var(--theme-success-color-rgb),0.10)',
                            'visible' => $toolVisible('portal.ai-image', 'ai_studio_image'),
                        ],
                        [
                            'label' => __('Repurpose'),
                            'description' => __('Turn one source idea into multiple post variants for different channels.'),
                            'route' => $toolRoute('portal.ai-repurpose'),
                            'icon' => 'fa-light fa-arrows-rotate',
                            'tone' => 'rgba(var(--theme-warning-color-rgb),0.10)',
                            'visible' => $toolVisible('portal.ai-repurpose', 'ai_studio_repurpose'),
                        ],
                        [
                            'label' => __('Content Planner'),
                            'description' => __('Map out upcoming content ideas and posting arcs with AI planning support.'),
                            'route' => $toolRoute('portal.ai-content-planner'),
                            'icon' => 'fa-light fa-calendar-lines-pen',
                            'tone' => 'rgba(var(--theme-accent-rgb),0.08)',
                            'visible' => $toolVisible('portal.ai-content-planner', 'ai_studio_content_planner'),
                        ],
                    ], fn (array $tool): bool => (bool) ($tool['visible'] ?? true))),
                    'supportTools' => array_values(array_filter([
                        [
                            'label' => __('AI Publishing'),
                            'route' => $hasAiPublishingRoute ? route('portal.ai-publishing') : null,
                            'visible' => $canUseAiPublishing,
                        ],
                        [
                            'label' => __('Prompt History'),
                            'route' => route('portal.ai-studio.prompt-history'),
                            'visible' => true,
                        ],
                        [
                            'label' => __('Credit Usage'),
                            'route' => route('portal.credits'),
                            'visible' => true,
                        ],
                    ], fn (array $tool): bool => (bool) ($tool['visible'] ?? true))),
                ];
            },
            'visible' => function () {
                $user = auth()->user();
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;

                if (! TeamWorkspaceAccess::teamHasModule($team, 'publishing')) {
                    return false;
                }

                return $planOwner?->canUsePlanFeature('ai_studio') ?? false;
            },
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                ['sort' => 81, 'parent' => 'features', 'tab_id' => 'ai_studio', 'tab_name' => __('AI Studio'), 'standalone' => true, 'standalone_sort' => 200, 'standalone_key' => 'ai_studio', 'key' => 'ai_studio_caption_generator', 'label' => __('AI Content'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ['sort' => 82, 'parent' => 'features', 'tab_id' => 'ai_studio', 'tab_name' => __('AI Studio'), 'standalone' => true, 'standalone_sort' => 200, 'standalone_key' => 'ai_studio', 'key' => 'ai_studio_image', 'label' => __('AI Image'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ['sort' => 84, 'parent' => 'features', 'tab_id' => 'ai_studio', 'tab_name' => __('AI Studio'), 'standalone' => true, 'standalone_sort' => 200, 'standalone_key' => 'ai_studio', 'key' => 'ai_studio_repurpose', 'label' => __('Repurpose'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ['sort' => 85, 'parent' => 'features', 'tab_id' => 'ai_studio', 'tab_name' => __('AI Studio'), 'standalone' => true, 'standalone_sort' => 200, 'standalone_key' => 'ai_studio', 'key' => 'ai_studio_content_planner', 'label' => __('Content Planner'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
            ]);
        });
    }
}
