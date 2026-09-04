<?php

namespace Database\Seeders;

use App\Models\TextModel;
use Illuminate\Database\Seeder;

/**
 * Seeds the text_models table with the default ad-copy engine registry —
 * the same set previously held in config('ad-copy.engines').
 *
 * Idempotent: uses updateOrCreate keyed on (vendor, model_id), so re-running
 * won't duplicate rows and refreshes the managed fields to their canonical
 * defaults on existing installs.
 */
class TextModelSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach ($this->vendors() as $vendor) {
            foreach ($vendor['models'] as $model) {
                TextModel::updateOrCreate(
                    [
                        'vendor'   => $vendor['vendor'],
                        'model_id' => $model['model_id'],
                    ],
                    [
                        'label'        => $model['label'],
                        'description'  => $model['description'],
                        'driver'       => $vendor['driver'],
                        'vendor_label' => $vendor['vendor_label'],
                        'key_column'   => $vendor['key_column'],
                        'icon'         => $vendor['icon'],
                        'tint'         => $vendor['tint'],
                        'tier'         => $model['tier'],
                        'credit_cost'  => $model['credit_cost'],
                        'enabled'      => $model['enabled'],
                        'sort_order'   => $sort += 10,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function vendors(): array
    {
        return [
            [
                'vendor'       => 'openai',
                'vendor_label' => 'OpenAI GPT',
                'driver'       => \App\Services\AdCopy\Drivers\OpenAiCopyDriver::class,
                'key_column'   => 'openai_key',
                'icon'         => 'sparkles',
                'tint'         => 'emerald',
                'models' => [
                    ['model_id' => 'gpt-5.5', 'label' => 'GPT-5.5', 'description' => 'Flagship model — top quality for complex, high-converting ad copy.', 'tier' => 'premium', 'credit_cost' => 3, 'enabled' => true],
                    ['model_id' => 'gpt-5.4', 'label' => 'GPT-5.4', 'description' => 'More affordable flagship-tier model. Excellent all-round copy quality.', 'tier' => 'premium', 'credit_cost' => 2, 'enabled' => true],
                    ['model_id' => 'gpt-5.4-mini', 'label' => 'GPT-5.4 Mini', 'description' => 'Fast & cost-efficient. Great for high-volume runs.', 'tier' => 'standard', 'credit_cost' => 1, 'enabled' => true],
                    ['model_id' => 'gpt-5.4-nano', 'label' => 'GPT-5.4 Nano', 'description' => 'Smallest GPT-5.4 — fastest and cheapest for short copy.', 'tier' => 'fast', 'credit_cost' => 1, 'enabled' => true],
                ],
            ],
            [
                'vendor'       => 'gemini',
                'vendor_label' => 'Google Gemini',
                'driver'       => \App\Services\AdCopy\Drivers\GeminiCopyDriver::class,
                'key_column'   => 'gemini_key',
                'icon'         => 'cpu-chip',
                'tint'         => 'sky',
                'models' => [
                    ['model_id' => 'gemini-3.1-pro-preview', 'label' => 'Gemini 3.1 Pro', 'description' => 'Highest-quality Gemini. Best for long-form / multilingual / complex briefs.', 'tier' => 'premium', 'credit_cost' => 3, 'enabled' => true],
                    ['model_id' => 'gemini-3.5-flash', 'label' => 'Gemini 3.5 Flash', 'description' => 'Near-Pro intelligence at Flash speed & cost. Strong general-purpose pick.', 'tier' => 'standard', 'credit_cost' => 1, 'enabled' => true],
                    ['model_id' => 'gemini-3.1-flash-lite', 'label' => 'Gemini 3.1 Flash-Lite', 'description' => 'Fastest, most budget-friendly Gemini. Great for high-volume short copy.', 'tier' => 'fast', 'credit_cost' => 1, 'enabled' => true],
                ],
            ],
            [
                'vendor'       => 'anthropic',
                'vendor_label' => 'Anthropic Claude',
                'driver'       => \App\Services\AdCopy\Drivers\AnthropicCopyDriver::class,
                'key_column'   => 'anthropic_key',
                'icon'         => 'chat-bubble-left-right',
                'tint'         => 'amber',
                'models' => [
                    ['model_id' => 'claude-opus-4-8', 'label' => 'Claude Opus 4.8', 'description' => 'Most capable Claude. Best for complex, premium, high-stakes copy.', 'tier' => 'premium', 'credit_cost' => 4, 'enabled' => true],
                    ['model_id' => 'claude-sonnet-4-6', 'label' => 'Claude Sonnet 4.6', 'description' => 'Best balance of speed and intelligence. Strong default pick.', 'tier' => 'standard', 'credit_cost' => 2, 'enabled' => true],
                    ['model_id' => 'claude-haiku-4-5', 'label' => 'Claude Haiku 4.5', 'description' => 'Fastest Claude with near-frontier quality. Great for high-volume runs.', 'tier' => 'fast', 'credit_cost' => 1, 'enabled' => true],
                ],
            ],
            [
                'vendor'       => 'xai',
                'vendor_label' => 'xAI Grok',
                'driver'       => \App\Services\AdCopy\Drivers\GrokCopyDriver::class,
                'key_column'   => 'xai_key',
                'icon'         => 'bolt',
                'tint'         => 'zinc',
                'models' => [
                    ['model_id' => 'grok-4.3', 'label' => 'Grok 4.3', 'description' => 'xAI flagship. Most intelligent and fastest Grok, with a 1M-token context.', 'tier' => 'premium', 'credit_cost' => 2, 'enabled' => true],
                ],
            ],
        ];
    }
}
