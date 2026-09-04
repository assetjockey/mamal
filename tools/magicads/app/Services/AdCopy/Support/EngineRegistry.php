<?php

namespace App\Services\AdCopy\Support;

use App\Models\TextModel;
use Illuminate\Support\Collection;

/**
 * Single source of truth for engine + model lookups against the
 * `text_models` table (see TextModel + TextModelSeeder).
 *
 * The Copy Studio UI, the CopyGenerator, and the individual provider drivers
 * all read from the same registry — keeping resolution logic here ensures we
 * never drift between layers (e.g. the UI shows a model the driver can't
 * dispatch). Rows are loaded once per request and memoised.
 */
class EngineRegistry
{
    /** @var Collection<int, TextModel>|null */
    protected static ?Collection $cache = null;

    /**
     * All enabled text models, ordered, loaded once per request.
     *
     * @return Collection<int, TextModel>
     */
    protected static function rows(): Collection
    {
        if (static::$cache === null) {
            static::$cache = TextModel::query()->enabled()->ordered()->get();
        }

        return static::$cache;
    }

    /**
     * Clear the in-memory cache (useful in tests / after seeding).
     */
    public static function flush(): void
    {
        static::$cache = null;
    }

    /**
     * Engines that have at least one enabled model, keyed by vendor slug.
     * Each entry mirrors the old config shape:
     *   label, driver, key_column, icon, tint, description, enabled_models
     *
     * @return array<string, array<string, mixed>>
     */
    public static function availableEngines(): array
    {
        $available = [];

        foreach (static::rows()->groupBy('vendor') as $vendor => $models) {
            /** @var TextModel $first */
            $first = $models->first();

            $enabledModels = [];
            foreach ($models as $model) {
                $enabledModels[$model->model_id] = $model->toModelArray();
            }

            $available[$vendor] = [
                'label'          => $first->vendor_label ?? $vendor,
                'driver'         => $first->driver,
                'key_column'     => $first->key_column,
                'icon'           => $first->icon ?? 'cpu-chip',
                'tint'           => $first->tint ?? 'indigo',
                'enabled_models' => $enabledModels,
            ];
        }

        return $available;
    }

    /**
     * Vendor-level metadata (driver, key_column, label, ...) for one engine,
     * or null when the engine has no enabled models.
     *
     * @return array<string, mixed>|null
     */
    public static function engine(string $engine): ?array
    {
        return static::availableEngines()[$engine] ?? null;
    }

    /**
     * Models for an engine that are flagged enabled, keyed by model id.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function enabledModels(string $engine): array
    {
        return static::availableEngines()[$engine]['enabled_models'] ?? [];
    }

    /**
     * The default model id for an engine — the first enabled model in order.
     */
    public static function defaultModel(string $engine): ?string
    {
        $enabled = static::enabledModels($engine);

        if (empty($enabled)) {
            return null;
        }

        return (string) array_key_first($enabled);
    }

    /**
     * Resolve a user-requested model to one we are willing to dispatch:
     *  - If the requested model is enabled for the engine → keep it.
     *  - Otherwise fall back to the engine's default.
     *  - Throws if the engine has no enabled models at all.
     */
    public static function resolveModel(string $engine, ?string $model): string
    {
        $enabled = static::enabledModels($engine);

        if (empty($enabled)) {
            throw new \RuntimeException("No enabled models configured for engine [{$engine}].");
        }

        if ($model !== null && $model !== '' && array_key_exists($model, $enabled)) {
            return $model;
        }

        return (string) array_key_first($enabled);
    }

    /**
     * Whether a (engine, model) pair is currently dispatchable.
     */
    public static function isModelEnabled(string $engine, string $model): bool
    {
        return array_key_exists($model, static::enabledModels($engine));
    }
}
