<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An ad-copy generation model — the database-backed replacement for the old
 * config('ad-copy.engines') array.
 *
 * Rows are grouped by `vendor` (the engine, e.g. 'openai'). Vendor-level
 * wiring (driver, key_column, icon, tint, vendor_label) is denormalised onto
 * every model row so the whole Copy Studio picker can be built from one query.
 *
 * @property string      $vendor
 * @property string      $model_id
 * @property string      $label
 * @property string|null $description
 * @property string      $driver
 * @property string|null $vendor_label
 * @property string      $key_column
 * @property string|null $icon
 * @property string|null $tint
 * @property string|null $tier
 * @property int         $credit_cost
 * @property bool        $enabled
 * @property int         $sort_order
 */
class TextModel extends Model
{
    protected $fillable = [
        'vendor',
        'model_id',
        'label',
        'description',
        'driver',
        'vendor_label',
        'key_column',
        'icon',
        'tint',
        'tier',
        'credit_cost',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled'     => 'boolean',
        'credit_cost' => 'integer',
        'sort_order'  => 'integer',
    ];

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeForVendor(Builder $query, string $vendor): Builder
    {
        return $query->where('vendor', $vendor);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Shape this row into the per-model array the Copy Studio UI expects.
     */
    public function toModelArray(): array
    {
        return [
            'id'          => $this->model_id,
            'label'       => $this->label,
            'description' => $this->description ?? '',
            'tier'        => $this->tier ?? 'standard',
            'credit_cost' => (int) $this->credit_cost,
        ];
    }
}
