<?php

namespace Modules\AppAIQRCodes\Providers;

use Illuminate\Support\ServiceProvider;

class AppAIQRCodesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appaiqrcodes');

        register_credit_action([
            'key' => 'ai_qr_generate',
            'plan_key' => 'credit_cost_ai_qr_generate',
            'label' => __('AI QR Code Generation'),
            'default_cost' => 5,
            'order' => 27,
            'description' => __('Credits deducted each time a user generates an AI-styled QR code image.'),
        ]);

        register_plan_permission([
            'key' => 'ai_qr_codes',
            'label' => __('AI QR Codes'),
            'type' => 'toggle',
            'order' => 149,
            'default' => true,
        ]);

        register_user_sidebar_item('qr-codes', [
            'label' => 'AI QR Codes',
            'route_name' => 'portal.qr-codes.ai',
            'active_when' => ['portal.qr-codes.ai'],
            'icon' => 'fa-light fa-robot',
            'order' => 18,
            'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('ai_qr_codes') ?? false),
        ]);

        $this->app->booted(function (): void {
            \Pricing::add([
                [
                    'sort' => 241,
                    'key' => 'ai_qr_codes',
                    'label' => __('AI QR Codes'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
                [
                    'sort' => 242,
                    'key' => 'credit_cost_ai_qr_generate',
                    'label' => __('Credits per AI QR request'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
            ]);
        });
    }
}
