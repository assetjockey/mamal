<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'brand_values'    => 'array',
        'social_handles'  => 'array',
        'ad_platforms'    => 'array',
        'custom_fields'   => 'array',
        'is_default'      => 'boolean',
        'is_active'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Brand $brand) {
            if (empty($brand->slug) && ! empty($brand->name)) {
                $brand->slug = Str::slug($brand->name) . '-' . Str::random(5);
            }
        });

        // Null the Brand_Kit reference on every Project that points at this Brand
        // before it is deleted. This complements the DB-level nullOnDelete() and
        // guarantees referencing Projects stay accessible across all DB drivers
        // (e.g. SQLite used in tests). Requirement 10.11.
        static::deleting(function (Brand $brand) {
            Project::where('brand_id', $brand->getKey())->update(['brand_id' => null]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeDefault($q)
    {
        return $q->where('is_default', true);
    }

    /**
     * Completion score based on the 4 wizard steps.
     * Each completed step = 25%. Matches the UI step tracker.
     */
    public function getCompletionScoreAttribute(): int
    {
        $steps = [
            // Step 1 — Brand Name & Description
            filled($this->name),
            // Step 2 — Logo
            filled($this->logo_path),
            // Step 3 — Colors (all three set)
            filled($this->primary_color) && filled($this->secondary_color) && filled($this->accent_color),
            // Step 4 — Advanced (any meaningful advanced field filled)
            filled($this->tone_of_voice)
                || filled($this->target_audience)
                || (is_array($this->brand_values) && count($this->brand_values) > 0)
                || (is_array($this->ad_platforms) && count($this->ad_platforms) > 0),
        ];

        $done = count(array_filter($steps));
        return (int) round(($done / count($steps)) * 100);
    }

    /**
     * Brand context string fed into AI prompts.
     */
    public function toPromptContext(): string
    {
        $parts = [];

        if ($this->name)             $parts[] = "Brand: {$this->name}.";
        if ($this->industry)         $parts[] = "Industry: {$this->industry}.";
        if ($this->tagline)          $parts[] = "Tagline: \"{$this->tagline}\".";
        if ($this->description)      $parts[] = "About: {$this->description}";
        if ($this->tone_of_voice)    $parts[] = "Tone of voice: {$this->tone_of_voice}.";
        if ($this->target_audience)  $parts[] = "Target audience: {$this->target_audience}.";

        $colors = array_filter([$this->primary_color, $this->secondary_color, $this->accent_color]);
        if (! empty($colors)) {
            $parts[] = 'Brand palette: ' . implode(', ', $colors) . '.';
        }

        if (is_array($this->brand_values) && ! empty($this->brand_values)) {
            $parts[] = 'Brand values: ' . implode(', ', $this->brand_values) . '.';
        }

        return implode(' ', $parts);
    }
}
