<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An AI generation engine (image or video) — the database-backed
 * replacement for the old config('ai-studio.providers') array.
 *
 * @property string      $vendor
 * @property string      $label
 * @property string|null $sub_label
 * @property string|null $model_id
 * @property string      $type
 * @property string      $driver
 * @property string|null $key_field
 * @property string|null $api_provider
 * @property array|null  $provider_settings
 * @property array|null  $resolutions
 * @property string|null $description
 * @property array|null  $tags
 * @property string|null $tier
 * @property bool        $audio
 * @property array|null  $durations
 * @property int|null    $max_duration
 * @property int         $credit_cost
 * @property string|null $text_rendering
 * @property string|null $image_quality
 * @property int|null    $max_resolution
 * @property string|null $icon_svg
 * @property bool        $recommended
 * @property bool        $is_active
 * @property int         $sort_order
 */
class MediaModel extends Model
{
    protected $fillable = [
        'vendor',
        'label',
        'sub_label',
        'model_id',
        'type',
        'driver',
        'key_field',
        'api_provider',
        'provider_settings',
        'resolutions',
        'description',
        'tags',
        'tier',
        'audio',
        'durations',
        'max_duration',
        'credit_cost',
        'text_rendering',
        'image_quality',
        'max_resolution',
        'icon_svg',
        'recommended',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'tags'              => 'array',
        'durations'         => 'array',
        'provider_settings' => 'array',
        'resolutions'       => 'array',
        'audio'          => 'boolean',
        'recommended'    => 'boolean',
        'is_active'      => 'boolean',
        'max_duration'   => 'integer',
        'credit_cost'    => 'integer',
        'max_resolution' => 'integer',
        'sort_order'     => 'integer',
    ];

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Find an engine by its stable vendor slug (e.g. 'gemini', 'veo-lite').
     */
    public static function findByVendor(string $vendor): ?self
    {
        return static::query()->where('vendor', $vendor)->first();
    }

    // ------------------------------------------------------------------
    // Multi-vendor support (e.g. Seedance via ByteDance / fal.ai / kie.ai)
    // ------------------------------------------------------------------

    /**
     * Ordered list of the quality tiers this project understands. Any tier
     * outside this list stored on the row is ignored; any missing tier is
     * simply treated as unavailable.
     */
    public const RESOLUTION_TIERS = ['480p', '720p', '1080p', '4k'];

    /**
     * Whether this engine is powered by a choice of interchangeable API
     * vendors (as opposed to a single hard-wired provider).
     */
    public function isMultiVendor(): bool
    {
        return is_array($this->provider_settings) && $this->provider_settings !== [];
    }

    /**
     * The vendor key currently selected to power this model. Falls back to
     * the first configured vendor, then null for single-vendor models.
     */
    public function activeProvider(): ?string
    {
        if (! $this->isMultiVendor()) {
            return null;
        }

        if ($this->api_provider && isset($this->provider_settings[$this->api_provider])) {
            return $this->api_provider;
        }

        return array_key_first($this->provider_settings);
    }

    /**
     * The config block ({label, key_field, model_id}) for one vendor, or the
     * currently-active vendor when no key is given.
     *
     * @return array<string, mixed>|null
     */
    public function providerConfig(?string $vendorKey = null): ?array
    {
        if (! $this->isMultiVendor()) {
            return null;
        }

        $vendorKey ??= $this->activeProvider();

        return $vendorKey ? ($this->provider_settings[$vendorKey] ?? null) : null;
    }

    /**
     * The admin_keys column that holds the API key actually in use — the
     * active vendor's column for multi-vendor models, otherwise `key_field`.
     */
    public function activeKeyField(): ?string
    {
        if ($this->isMultiVendor()) {
            return $this->providerConfig()['key_field'] ?? $this->key_field;
        }

        return $this->key_field;
    }

    /**
     * The concrete provider-side model id in use — the active vendor's id for
     * multi-vendor models, otherwise the `model_id` column.
     */
    public function activeModelId(): ?string
    {
        if ($this->isMultiVendor()) {
            return $this->providerConfig()['model_id'] ?? $this->model_id;
        }

        return $this->model_id;
    }

    /**
     * Whether this engine exposes selectable quality tiers (480p/720p/…).
     */
    public function hasResolutions(): bool
    {
        return is_array($this->resolutions) && $this->resolutions !== [];
    }

    /**
     * The enabled resolution tiers for user selection, in canonical order,
     * each as ['tier' => '720p', 'credit_cost' => 4].
     *
     * @return array<int, array{tier:string, credit_cost:int}>
     */
    public function enabledResolutions(): array
    {
        if (! $this->hasResolutions()) {
            return [];
        }

        $out = [];
        foreach (self::RESOLUTION_TIERS as $tier) {
            $cfg = $this->resolutions[$tier] ?? null;
            if (is_array($cfg) && ! empty($cfg['enabled'])) {
                $out[] = [
                    'tier'        => $tier,
                    'credit_cost' => max(1, (int) ($cfg['credit_cost'] ?? $this->credit_cost)),
                ];
            }
        }

        return $out;
    }

    /**
     * The per-second credit cost for a specific tier, or null when the tier
     * is not configured/enabled on this model.
     */
    public function resolutionCreditCost(?string $tier): ?int
    {
        if (! $tier || ! $this->hasResolutions()) {
            return null;
        }

        $cfg = $this->resolutions[$tier] ?? null;

        if (! is_array($cfg) || empty($cfg['enabled'])) {
            return null;
        }

        return max(1, (int) ($cfg['credit_cost'] ?? $this->credit_cost));
    }

    /**
     * The default tier to preselect — the first enabled tier, preferring
     * 720p when available. Null when the model has no resolution tiers.
     */
    public function defaultResolution(): ?string
    {
        $enabled = array_column($this->enabledResolutions(), 'tier');

        if ($enabled === []) {
            return null;
        }

        return in_array('720p', $enabled, true) ? '720p' : $enabled[0];
    }

    /**
     * Drivers that expose a per-generation image "quality" knob, mapped to
     * the option values that driver accepts. Engines outside this map don't
     * render a quality selector in the admin modal and ignore the column.
     *
     * @var array<class-string, array<int, string>>
     */
    public const QUALITY_OPTIONS = [
        \App\Services\AiStudio\Drivers\GptImageDriver::class => ['auto', 'low', 'medium', 'high'],
    ];

    /**
     * Image engines whose drivers can ingest reference images (e.g. a brand
     * logo) as part of the generation, so the model composites the real asset
     * instead of inventing one. Engines outside this list still receive the
     * full text brief but cannot be handed the logo file.
     *
     * @var array<int, class-string>
     */
    public const IMAGE_INPUT_DRIVERS = [
        \App\Services\AiStudio\Drivers\GeminiDriver::class,
        \App\Services\AiStudio\Drivers\GptImageDriver::class,
        \App\Services\AiStudio\Drivers\FluxDriver::class,
    ];

    /**
     * Whether this engine exposes an image-quality knob (driver-dependent).
     */
    public function supportsImageQuality(): bool
    {
        return $this->type === 'image'
            && isset(self::QUALITY_OPTIONS[$this->driver]);
    }

    /**
     * Whether this engine can ingest a reference image (brand logo / style
     * reference) alongside the text prompt.
     */
    public function supportsImageInput(): bool
    {
        return $this->type === 'image'
            && in_array($this->driver, self::IMAGE_INPUT_DRIVERS, true);
    }

    /**
     * Whether the engine identified by a vendor slug can ingest image inputs.
     */
    public static function vendorSupportsImageInput(string $vendor): bool
    {
        return (bool) (static::findByVendor($vendor)?->supportsImageInput());
    }

    /**
     * The quality option values this engine accepts, or an empty array when
     * it has no quality knob.
     *
     * @return array<int, string>
     */
    public function qualityOptions(): array
    {
        return self::QUALITY_OPTIONS[$this->driver] ?? [];
    }

    /**
     * Shape this row into the array the studio UI expects on each engine
     * card. Mirrors the payload the old ProviderManager built from config.
     *
     * @param  bool  $isDefault  Whether this engine is the configured default for its type.
     */
    public function toCardArray(bool $isDefault = false): array
    {
        return [
            'key'             => $this->vendor,
            'label'           => $this->label,
            'sub_label'       => $this->sub_label,
            'model_id'        => $this->model_id,
            'description'     => $this->description,
            'tags'            => $this->tags ?? [],
            'icon_svg'        => $this->icon_svg,
            'recommended'     => (bool) $this->recommended,
            'is_default'      => $isDefault,
            'tier'            => $this->tier,
            'credit_cost'     => $this->credit_cost,
            'audio'           => (bool) $this->audio,
            'durations'       => $this->durations ?? [],
            'max_duration'    => $this->max_duration,
            'text_rendering'  => $this->text_rendering,
            'max_resolution'  => $this->max_resolution,
            // Selectable quality tiers (only present on multi-tier engines
            // like Seedance). Each: ['tier' => '720p', 'credit_cost' => 4].
            'resolutions'     => $this->enabledResolutions(),
        ];
    }
}
