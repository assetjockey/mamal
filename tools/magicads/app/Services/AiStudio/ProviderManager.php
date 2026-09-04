<?php

namespace App\Services\AiStudio;

use App\Models\AdminKey;
use App\Models\FeatureSetting;
use App\Models\MediaModel;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use Illuminate\Support\Collection;

/**
 * Resolves and lists AI generation engines.
 *
 * Engine definitions live in the `media_models` table (see MediaModel +
 * MediaModelSeeder) rather than config. Each row carries its own driver
 * class and `key_field` (the admin_keys column holding its API key), so
 * there is no longer a static provider→key map here.
 */
class ProviderManager
{
    public function driver(string $provider): AiProviderInterface
    {
        $model = MediaModel::findByVendor($provider);

        if (! $model) {
            throw new \InvalidArgumentException("AI provider [{$provider}] is not configured.");
        }

        $apiKey = '';

        // Multi-vendor engines (e.g. Seedance) resolve to the active vendor's
        // key column; single-vendor engines keep using their static key_field.
        $keyField = $model->activeKeyField();

        if ($keyField) {
            $keys = AdminKey::first();
            $apiKey = $keys?->apiKey($keyField) ?? '';
        }

        $driverClass = $model->driver;

        return new $driverClass($apiKey);
    }

    public function availableImageProviders(): Collection
    {
        return $this->availableProviders('image');
    }

    public function availableVideoProviders(): Collection
    {
        return $this->availableProviders('video');
    }

    protected function availableProviders(string $type): Collection
    {
        $keys = AdminKey::first();
        $features = FeatureSetting::first();

        return MediaModel::query()
            ->active()
            ->ofType($type)
            ->ordered()
            ->get()
            // Only surface engines whose API key is actually configured. For
            // multi-vendor engines this checks the *active* vendor's key, so
            // Seedance disappears if the selected vendor has no key set.
            ->filter(function (MediaModel $model) use ($keys) {
                $keyField = $model->activeKeyField();

                return $keyField
                    && $keys
                    && ! empty($keys->apiKey($keyField));
            })
            ->map(function (MediaModel $model) use ($features) {
                $isDefault = $features?->default_image_model === $model->vendor
                    || $features?->default_video_model === $model->vendor;

                return $model->toCardArray($isDefault);
            })
            ->values();
    }
}
