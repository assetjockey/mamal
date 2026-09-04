<?php

namespace App\Services\AdCopy;

use App\Models\AdCopy;
use App\Models\AdminKey;
use App\Models\Brand;
use App\Models\Project;
use App\Models\User;
use App\Services\AdCopy\Support\EngineRegistry;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use Illuminate\Support\Str;

class CopyGenerator
{
    public function __construct(
        protected PromptAssembler $assembler,
        protected CreditServiceInterface $credits,
    ) {}

    /**
     * Synchronously generate ad copy and persist the result.
     */
    public function generate(User $user, array $input): AdCopy
    {
        $brand = ! empty($input['brand_id']) ? Brand::where('user_id', $user->id)->find($input['brand_id']) : null;

        // Ownership-safe project association: only attach to a Project the user
        // owns when the creative is created within a project's context.
        $project = ! empty($input['project_id'])
            ? Project::where('user_id', $user->id)->find($input['project_id'])
            : null;

        $record = AdCopy::create([
            'user_id'             => $user->id,
            'brand_id'            => $brand?->id,
            'project_id'          => $project?->id,
            'title'               => $input['title'] ?? Str::limit($input['product_description'] ?? 'Ad Copy', 80),
            'platform'            => $input['platform'],
            'objective'           => $input['objective'] ?? null,
            'framework'           => $input['framework'] ?? null,
            'tone'                => $input['tone'] ?? null,
            'engine'              => $input['engine'] ?? 'openai',
            'language'            => $input['language'] ?? 'en',
            'product_description' => $input['product_description'] ?? null,
            'target_audience'     => $input['target_audience'] ?? null,
            'key_benefits'        => $input['key_benefits'] ?? null,
            'keywords'            => $input['keywords'] ?? null,
            'cta'                 => $input['cta'] ?? null,
            'extra_instructions'  => $input['extra_instructions'] ?? null,
            'status'              => 'processing',
        ]);

        try {
            $engineConfig = EngineRegistry::engine($record->engine);
            if (! $engineConfig) {
                throw new \RuntimeException("Unknown engine: {$record->engine}");
            }

            $column = $engineConfig['key_column'] ?? null;
            if (! $column) {
                throw new \RuntimeException("Engine {$record->engine} is missing a key_column mapping.");
            }

            $apiKey = AdminKey::first()?->apiKey($column) ?? '';
            if (! $apiKey) {
                throw new \RuntimeException("API key for engine {$record->engine} is not configured. Ask an admin to set it.");
            }

            /** @var \App\Services\AdCopy\Contracts\AdCopyDriverInterface $driver */
            $driver = app($engineConfig['driver']);

            $variants = (int) ($input['variants'] ?? config('ad-copy.variants_per_generation', 3));
            $model = EngineRegistry::resolveModel($record->engine, $input['model'] ?? null);

            $prompt = $this->assembler->assemble([...$input, 'variants' => $variants], $brand);

            $results = $driver->generate($prompt, $variants, $apiKey, $model);

            // Copy is priced per 1000 words generated. Count words across every
            // field of every returned variant, then charge rate × words / 1000.
            $words = $this->countWords($results);
            $cost = $this->credits->getCost('copy', $model, $words);

            $record->update([
                'variants' => $results,
                'meta' => [
                    'engine'         => $record->engine,
                    'model'          => $model,
                    'model_label'    => $engineConfig['enabled_models'][$model]['label'] ?? $model,
                    'variants_count' => \count($results),
                    'words'          => $words,
                ],
                'status' => 'completed',
                'credits' => $cost,
                'words' => $words,
                'model_id' => $model,
            ]);

            $this->credits->deduct($user, 'copy', $model, $words);

            return $record->fresh();
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Total words across every string field of every generated variant.
     * Drives per-1000-words copy pricing.
     *
     * @param  array<int, array<string, mixed>>  $variants
     */
    protected function countWords(array $variants): int
    {
        $words = 0;

        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            foreach ($variant as $value) {
                if (is_string($value) && $value !== '') {
                    $words += str_word_count(strip_tags($value));
                }
            }
        }

        return $words;
    }
}
