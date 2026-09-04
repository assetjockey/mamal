<?php

namespace App\Livewire\Admin\Ai;

use App\Models\AdminKey;
use App\Models\FeatureSetting;
use App\Models\MediaModel;
use App\Models\TextModel;
use App\Services\AdCopy\Support\EngineRegistry;
use App\Services\AiStudio\VendorRegistry;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Title('AI Settings')]
class AISettings extends Component
{
    // ----- Global feature toggles -----
    public bool $image_studio_feature = true;
    public bool $video_studio_feature = true;
    public bool $copy_studio_feature  = true;

    // ----- Free-tier access toggles -----
    // When on, non-subscribers (users without a plan) can use that studio.
    // Subscribers always defer to their plan's per-studio column instead.
    public bool $image_studio_free_tier = true;
    public bool $video_studio_free_tier = true;
    public bool $copy_studio_free_tier  = true;

    // ----- Defaults -----
    public string $defaultImageModel = '';
    public string $defaultVideoModel = '';
    public string $defaultCopyEngine = '';

    // ----- Vendor configuration modal state -----
    public bool $showVendorModal = false;
    public ?string $editingVendor = null;     // key column, e.g. 'gemini_key'
    public string $apiKey = '';               // the vendor's API key (plain)
    public bool $apiKeyDirty = false;          // whether the field was edited
    public bool $hasExistingKey = false;       // vendor already has a key stored
    /** @var array<int, array<string,mixed>> per-model editable rows */
    public array $modelRows = [];

    // ----- Multi-vendor engine (e.g. Seedance) modal state -----
    public bool $showModelModal = false;
    public ?string $editingModel = null;       // model vendor slug, e.g. 'seedance'
    public string $modelLabel = '';
    public bool $modelEnabled = true;
    public string $modelActiveProvider = '';   // selected vendor key
    /**
     * Per-vendor rows keyed by vendor key. Each:
     *   ['label','key_field','has_key','api_key','model_id']
     * `api_key` starts blank; a non-empty value on save replaces the stored key.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $providerRows = [];
    /**
     * Per-tier rows keyed by tier ('480p'|'720p'|'1080p'|'4k'):
     *   ['enabled' => bool, 'credit_cost' => int]
     *
     * @var array<string, array<string, mixed>>
     */
    public array $resolutionRows = [];

    public function mount(): void
    {
        $features = FeatureSetting::first();

        $this->defaultImageModel = $features?->default_image_model ?? 'gemini';
        $this->defaultVideoModel = $features?->default_video_model ?? 'veo';
        $this->defaultCopyEngine = $features?->default_copy_engine ?? 'openai';

        $this->image_studio_feature = (bool) ($features?->image_studio_feature ?? true);
        $this->video_studio_feature = (bool) ($features?->video_studio_feature ?? true);
        $this->copy_studio_feature  = (bool) ($features?->copy_studio_feature  ?? true);

        $this->image_studio_free_tier = (bool) ($features?->image_studio_free_tier ?? true);
        $this->video_studio_free_tier = (bool) ($features?->video_studio_free_tier ?? true);
        $this->copy_studio_free_tier  = (bool) ($features?->copy_studio_free_tier  ?? true);
    }

    // ------------------------------------------------------------------
    // Vendor modal
    // ------------------------------------------------------------------

    public function configureVendor(string $keyColumn): void
    {
        $vendor = app(VendorRegistry::class)->vendor($keyColumn);

        if (! $vendor) {
            Toaster::warning(__('Unknown vendor.'));
            return;
        }

        $this->editingVendor = $keyColumn;
        $this->apiKey = '';
        $this->apiKeyDirty = false;
        $this->hasExistingKey = (bool) $vendor['has_key'];

        // Flatten all models for this vendor into editable rows.
        $rows = [];
        foreach (['image', 'video', 'copy'] as $cap) {
            foreach ($vendor['models'][$cap] as $model) {
                $rows[] = [
                    'id'          => $model['id'],
                    'table'       => $model['table'],   // 'media' | 'text'
                    'type'        => $model['type'],     // image | video | copy
                    'label'       => $model['label'],
                    'sub_label'   => $model['sub_label'],
                    'model_id'    => $model['model_id'],
                    'tier'        => $model['tier'],
                    'credit_cost' => (int) $model['credit_cost'],
                    'enabled'     => (bool) $model['enabled'],
                    // Image-quality knob — only present on media engines whose
                    // driver supports it (e.g. OpenAI GPT Image 2).
                    'supports_quality' => (bool) ($model['supports_quality'] ?? false),
                    'quality_options'  => $model['quality_options'] ?? [],
                    'image_quality'    => $model['image_quality'] ?? null,
                ];
            }
        }
        $this->modelRows = $rows;

        $this->showVendorModal = true;
    }

    public function updatedApiKey(): void
    {
        $this->apiKeyDirty = true;
    }

    /**
     * Enable/disable every model of one capability (image|video|copy) for the
     * vendor currently open in the modal — a quick "turn this studio on/off
     * for this vendor" switch. Operates on the in-memory rows; persisted on save.
     */
    public function toggleCapability(string $type, bool $enabled): void
    {
        foreach ($this->modelRows as $i => $row) {
            if ($row['type'] === $type) {
                $this->modelRows[$i]['enabled'] = $enabled;
            }
        }
    }

    public function saveVendor(): void
    {
        if (! $this->editingVendor) {
            return;
        }

        $column = $this->editingVendor;

        // 1) Persist the API key only if the admin typed something.
        if ($this->apiKeyDirty && $this->apiKey !== '') {
            $keys = AdminKey::firstOrCreate([]);
            $keys->setApiKey($column, $this->apiKey);
        }

        // 2) Persist per-model enabled + credit_cost.
        foreach ($this->modelRows as $row) {
            $cost = max(1, (int) $row['credit_cost']);

            if ($row['table'] === 'media') {
                $update = [
                    'is_active'   => (bool) $row['enabled'],
                    'credit_cost' => $cost,
                ];

                // Persist the quality knob only for engines that expose one,
                // and only when the chosen value is a valid option.
                if (! empty($row['supports_quality'])) {
                    $quality = $row['image_quality'] ?? null;
                    if (is_string($quality) && in_array($quality, $row['quality_options'] ?? [], true)) {
                        $update['image_quality'] = $quality;
                    }
                }

                MediaModel::whereKey($row['id'])->update($update);
            } else {
                TextModel::whereKey($row['id'])->update([
                    'enabled'     => (bool) $row['enabled'],
                    'credit_cost' => $cost,
                ]);
            }
        }

        // Bust caches that depend on these tables.
        EngineRegistry::flush();

        Toaster::success(__(':vendor configuration saved.', [
            'vendor' => app(VendorRegistry::class)->vendor($column)['name'] ?? __('Vendor'),
        ]));

        $this->reset(['editingVendor', 'apiKey', 'apiKeyDirty', 'hasExistingKey', 'modelRows']);
        $this->showVendorModal = false;
    }

    public function clearApiKey(): void
    {
        if (! $this->editingVendor) {
            return;
        }

        $keys = AdminKey::firstOrCreate([]);
        $keys->setApiKey($this->editingVendor, null);

        $this->apiKey = '';
        $this->apiKeyDirty = false;
        $this->hasExistingKey = false;

        Toaster::success(__('API key removed.'));
    }

    // ------------------------------------------------------------------
    // Multi-vendor engine modal (e.g. Seedance: ByteDance / fal.ai / kie.ai)
    // ------------------------------------------------------------------

    public function configureModel(string $vendorSlug): void
    {
        $model = app(VendorRegistry::class)->multiVendorModel($vendorSlug);

        if (! $model) {
            Toaster::warning(__('Unknown model.'));

            return;
        }

        $this->editingModel = $vendorSlug;
        $this->modelLabel = $model['label'];
        $this->modelEnabled = (bool) $model['enabled'];
        $this->modelActiveProvider = (string) $model['active_provider'];

        $rows = [];
        foreach ($model['providers'] as $p) {
            $rows[$p['key']] = [
                'label'     => $p['label'],
                'key_field' => $p['key_field'],
                'has_key'   => (bool) $p['has_key'],
                'model_id'  => $p['model_id'],
                'api_key'   => '',
            ];
        }
        $this->providerRows = $rows;

        $this->resolutionRows = $model['resolutions'];

        $this->showModelModal = true;
    }

    public function saveModel(): void
    {
        if (! $this->editingModel) {
            return;
        }

        $model = \App\Models\MediaModel::findByVendor($this->editingModel);

        if (! $model || ! $model->isMultiVendor()) {
            Toaster::warning(__('Unknown model.'));

            return;
        }

        // 1) Persist any newly-entered API keys. A blank field leaves the
        //    stored key untouched.
        $keys = AdminKey::firstOrCreate([]);
        foreach ($this->providerRows as $key => $row) {
            $typed = trim((string) ($row['api_key'] ?? ''));
            if ($typed !== '' && ! empty($row['key_field'])) {
                $keys->setApiKey($row['key_field'], $typed);
                $this->providerRows[$key]['has_key'] = true;
                $this->providerRows[$key]['api_key'] = '';
            }
        }

        // 2) The active vendor must have a usable key (freshly typed above or
        //    already stored). Refuse to activate a keyless vendor.
        $active = $this->modelActiveProvider;
        if (! isset($this->providerRows[$active])) {
            Toaster::warning(__('Select which vendor should power this model.'));

            return;
        }
        if (empty($this->providerRows[$active]['has_key'])) {
            Toaster::warning(__(':vendor needs an API key before it can power this model.', [
                'vendor' => $this->providerRows[$active]['label'],
            ]));

            return;
        }

        // 3) At least one resolution must remain enabled or users would have
        //    nothing to pick.
        $enabledTiers = collect($this->resolutionRows)->filter(fn ($r) => ! empty($r['enabled']));
        if ($enabledTiers->isEmpty()) {
            Toaster::warning(__('Enable at least one resolution.'));

            return;
        }

        // 4) Persist the model config.
        $resolutions = [];
        foreach (\App\Models\MediaModel::RESOLUTION_TIERS as $tier) {
            $row = $this->resolutionRows[$tier] ?? null;
            if (! is_array($row)) {
                continue;
            }
            $resolutions[$tier] = [
                'enabled'     => (bool) ($row['enabled'] ?? false),
                'credit_cost' => max(1, (int) ($row['credit_cost'] ?? 1)),
            ];
        }

        $model->update([
            'is_active'    => $this->modelEnabled,
            'api_provider' => $active,
            'resolutions'  => $resolutions,
        ]);

        Toaster::success(__(':model configuration saved.', ['model' => $this->modelLabel]));

        $this->reset(['editingModel', 'modelLabel', 'modelEnabled', 'modelActiveProvider', 'providerRows', 'resolutionRows']);
        $this->showModelModal = false;
    }

    public function clearProviderKey(string $providerKey): void
    {
        if (! isset($this->providerRows[$providerKey]) || empty($this->providerRows[$providerKey]['key_field'])) {
            return;
        }

        $keys = AdminKey::firstOrCreate([]);
        $keys->setApiKey($this->providerRows[$providerKey]['key_field'], null);

        $this->providerRows[$providerKey]['has_key'] = false;
        $this->providerRows[$providerKey]['api_key'] = '';

        Toaster::success(__('API key removed.'));
    }

    public function getMultiVendorModelsProperty(): array
    {
        return app(VendorRegistry::class)->multiVendorModels();
    }

    // ------------------------------------------------------------------
    // Global settings (feature toggles + defaults)
    // ------------------------------------------------------------------

    public function save(): void
    {
        FeatureSetting::updateOrCreate([], [
            'default_image_model'  => $this->defaultImageModel,
            'default_video_model'  => $this->defaultVideoModel,
            'default_copy_engine'  => $this->defaultCopyEngine,
            'image_studio_feature' => $this->image_studio_feature,
            'video_studio_feature' => $this->video_studio_feature,
            'copy_studio_feature'  => $this->copy_studio_feature,
            'image_studio_free_tier' => $this->image_studio_free_tier,
            'video_studio_free_tier' => $this->video_studio_free_tier,
            'copy_studio_free_tier'  => $this->copy_studio_free_tier,
        ]);

        Toaster::success(__('AI settings saved successfully.'));
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------

    public function getVendorsProperty(): array
    {
        return app(VendorRegistry::class)->vendors();
    }

    /**
     * Default-model dropdown options, grouped by capability.
     *
     * @return array{image:array,video:array,copy:array}
     */
    public function getDefaultOptionsProperty(): array
    {
        $image = MediaModel::query()->ofType('image')->active()->ordered()
            ->get(['vendor', 'label'])->unique('vendor')
            ->map(fn ($m) => ['value' => $m->vendor, 'label' => $m->label])->values()->all();

        $video = MediaModel::query()->ofType('video')->active()->ordered()
            ->get(['vendor', 'label'])->unique('vendor')
            ->map(fn ($m) => ['value' => $m->vendor, 'label' => $m->label])->values()->all();

        $copy = [];
        foreach (EngineRegistry::availableEngines() as $key => $meta) {
            $copy[] = ['value' => $key, 'label' => $meta['label']];
        }

        return ['image' => $image, 'video' => $video, 'copy' => $copy];
    }

    public function render()
    {
        return view('livewire.admin.ai.index', [
            'vendors'           => $this->vendors,
            'defaultOptions'    => $this->defaultOptions,
            'multiVendorModels' => $this->multiVendorModels,
        ]);
    }
}
