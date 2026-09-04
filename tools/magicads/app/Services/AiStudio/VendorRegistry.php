<?php

namespace App\Services\AiStudio;

use App\Models\AdminKey;
use App\Models\MediaModel;
use App\Models\TextModel;
use Illuminate\Support\Collection;

/**
 * Vendor-centric view over BOTH model registries (media_models + text_models).
 *
 * The admin AI Settings page is organised by *vendor* — a real provider/company
 * such as OpenAI or Google. A single vendor can span multiple studios:
 *   - Google (gemini_key):  image, video, copy
 *   - OpenAI (openai_key):  image, copy
 *   - Anthropic / xAI:      copy only
 *   - FLUX / Ideogram / Recraft: image only
 *   - Runway / Kling / Seedance: video only
 *
 * Vendors are grouped by the admin_keys column that holds their API key
 * (`key_field` on media_models, `key_column` on text_models) so that one card
 * configures the whole company in one place. Each vendor exposes its models
 * bucketed by capability (image / video / copy).
 */
class VendorRegistry
{
    /**
     * Static presentation metadata per API-key column. Lets us show a friendly
     * company name + brand slug (for the SVG mark) + the AdminKey column even
     * before any models exist for it.
     *
     * @var array<string, array{name:string, brand:string}>
     */
    protected const VENDORS = [
        'openai_key' => ['name' => 'OpenAI',          'brand' => 'openai'],
        'gemini_key' => ['name' => 'Google',          'brand' => 'gemini'],
        'anthropic_key' => ['name' => 'Anthropic',       'brand' => 'anthropic'],
        'xai_key' => ['name' => 'xAI',             'brand' => 'xai'],
        'flux_key' => ['name' => 'Black Forest Labs', 'brand' => 'flux'],
        'ideogram_key' => ['name' => 'Ideogram',        'brand' => 'ideogram'],
        'recraft_key' => ['name' => 'Recraft',         'brand' => 'recraft'],
        'runway_key' => ['name' => 'Runway',          'brand' => 'runway'],
        'kling_key' => ['name' => 'Kling (Kuaishou)', 'brand' => 'kling'],
        'seedance_key' => ['name' => 'Seedance (ByteDance)', 'brand' => 'seedance'],
        'kie_key' => ['name' => 'kie.ai',              'brand' => 'kie'],
        'fal_key' => ['name' => 'fal.ai',              'brand' => 'fal'],
    ];

    /**
     * Build the full vendor list for the admin UI. Each vendor entry:
     *   key, name, brand, key_column, has_key,
     *   capabilities => ['image','video','copy'],
     *   models => ['image'=>[...], 'video'=>[...], 'copy'=>[...]]
     *
     * @return array<int, array<string, mixed>>
     */
    public function vendors(): array
    {
        $keys = AdminKey::first();

        // Multi-vendor engines (e.g. Seedance) are configured on their own
        // dedicated cards via multiVendorModels() — keep them out of the
        // key-column grouping so they don't also appear under one vendor.
        $media = MediaModel::query()->ordered()->get()
            ->reject(fn (MediaModel $m) => $m->isMultiVendor())
            ->groupBy('key_field');
        $text = TextModel::query()->ordered()->get()->groupBy('key_column');

        // Union of every key column that either appears in the data or is known.
        $columns = collect(array_keys(static::VENDORS))
            ->merge($media->keys())
            ->merge($text->keys())
            ->filter()
            ->unique()
            ->values();

        return $columns->map(function (string $column) use ($media, $text, $keys) {
            $meta = static::VENDORS[$column] ?? [
                'name' => str($column)->beforeLast('_key')->headline()->toString(),
                'brand' => str($column)->beforeLast('_key')->toString(),
            ];

            /** @var Collection<int, MediaModel> $mediaRows */
            $mediaRows = $media->get($column, collect());
            /** @var Collection<int, TextModel> $textRows */
            $textRows = $text->get($column, collect());

            $imageModels = $mediaRows->where('type', 'image')->map(fn (MediaModel $m) => $this->mediaToArray($m))->values()->all();
            $videoModels = $mediaRows->where('type', 'video')->map(fn (MediaModel $m) => $this->mediaToArray($m))->values()->all();
            $copyModels = $textRows->map(fn (TextModel $t) => $this->textToArray($t))->values()->all();

            $capabilities = [];
            if (! empty($imageModels)) {
                $capabilities[] = 'image';
            }
            if (! empty($videoModels)) {
                $capabilities[] = 'video';
            }
            if (! empty($copyModels)) {
                $capabilities[] = 'copy';
            }

            return [
                'key' => $column,                 // canonical id = key column
                'name' => $meta['name'],
                'brand' => $meta['brand'],
                'key_column' => $column,
                'has_key' => $keys ? ! empty($keys->apiKey($column)) : false,
                'capabilities' => $capabilities,
                'models' => [
                    'image' => $imageModels,
                    'video' => $videoModels,
                    'copy' => $copyModels,
                ],
                'counts' => [
                    'image' => count($imageModels),
                    'video' => count($videoModels),
                    'copy' => count($copyModels),
                ],
            ];
        })
        // Only show vendors that actually offer at least one model.
            ->filter(fn (array $v) => ! empty($v['capabilities']))
            ->values()
            ->all();
    }

    /**
     * One vendor by its key column, or null.
     *
     * @return array<string, mixed>|null
     */
    public function vendor(string $keyColumn): ?array
    {
        return collect($this->vendors())->firstWhere('key', $keyColumn);
    }

    // ------------------------------------------------------------------
    // Multi-vendor engines (one model, several interchangeable API vendors)
    // ------------------------------------------------------------------

    /**
     * Engines that can be powered by more than one API vendor (e.g. Seedance
     * via ByteDance / fal.ai / kie.ai). Each is configured on its own card:
     * one active vendor at a time, per-vendor keys, and per-resolution pricing.
     *
     * @return array<int, array<string, mixed>>
     */
    public function multiVendorModels(): array
    {
        $keys = AdminKey::first();

        return MediaModel::query()->ordered()->get()
            ->filter(fn (MediaModel $m) => $m->isMultiVendor())
            ->map(fn (MediaModel $m) => $this->multiVendorToArray($m, $keys))
            ->values()
            ->all();
    }

    /**
     * One multi-vendor engine by its model vendor slug (e.g. 'seedance').
     *
     * @return array<string, mixed>|null
     */
    public function multiVendorModel(string $vendorSlug): ?array
    {
        return collect($this->multiVendorModels())->firstWhere('vendor', $vendorSlug);
    }

    /**
     * @return array<string, mixed>
     */
    protected function multiVendorToArray(MediaModel $m, ?AdminKey $keys): array
    {
        $activeProvider = $m->activeProvider();

        $providers = [];
        foreach (($m->provider_settings ?? []) as $key => $cfg) {
            $keyField = $cfg['key_field'] ?? null;
            $providers[] = [
                'key'       => $key,
                'label'     => $cfg['label'] ?? ucfirst($key),
                'key_field' => $keyField,
                'model_id'  => $cfg['model_id'] ?? null,
                'has_key'   => $keyField && $keys ? ! empty($keys->apiKey($keyField)) : false,
                'is_active' => $key === $activeProvider,
            ];
        }

        // Expose exactly the tiers this model declares (Seedance ships 4,
        // Kling ships 3 — no 480p), in canonical order. The applicable tier
        // set is a model characteristic, not admin-editable.
        $stored = $m->resolutions ?? [];
        $resolutions = [];
        foreach (MediaModel::RESOLUTION_TIERS as $tier) {
            if (! array_key_exists($tier, $stored)) {
                continue;
            }
            $cfg = $stored[$tier] ?? [];
            $resolutions[$tier] = [
                'enabled'     => (bool) ($cfg['enabled'] ?? false),
                'credit_cost' => max(1, (int) ($cfg['credit_cost'] ?? $m->credit_cost)),
            ];
        }

        $activeHasKey = collect($providers)->firstWhere('key', $activeProvider)['has_key'] ?? false;

        return [
            'vendor'          => $m->vendor,
            'label'           => $m->label,
            'sub_label'       => $m->sub_label,
            'type'            => $m->type,
            'tier'            => $m->tier,
            'icon_svg'        => $m->icon_svg,
            'enabled'         => (bool) $m->is_active,
            'active_provider' => $activeProvider,
            'providers'       => $providers,
            'resolutions'     => $resolutions,
            'has_active_key'  => (bool) $activeHasKey,
        ];
    }

    protected function mediaToArray(MediaModel $m): array
    {
        return [
            'id' => $m->id,
            'table' => 'media',
            'vendor' => $m->vendor,
            'model_id' => $m->model_id,
            'label' => $m->label,
            'sub_label' => $m->sub_label,
            'description' => $m->description,
            'type' => $m->type,
            'tier' => $m->tier,
            'credit_cost' => (int) $m->credit_cost,
            'recommended' => (bool) $m->recommended,
            'enabled' => (bool) $m->is_active,
            // Image-quality knob (driver-dependent). When the engine has no
            // quality options the admin modal hides the selector.
            'supports_quality' => $m->supportsImageQuality(),
            'quality_options' => $m->qualityOptions(),
            'image_quality' => $m->image_quality,
        ];
    }

    protected function textToArray(TextModel $t): array
    {
        return [
            'id' => $t->id,
            'table' => 'text',
            'vendor' => $t->vendor,
            'model_id' => $t->model_id,
            'label' => $t->label,
            'sub_label' => null,
            'description' => $t->description,
            'type' => 'copy',
            'tier' => $t->tier,
            'credit_cost' => (int) $t->credit_cost,
            'recommended' => false,
            'enabled' => (bool) $t->enabled,
        ];
    }
}
