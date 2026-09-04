<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];
    
    protected $table = "plans";

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    /**
     * Plan has many orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Plan has many subscribers
     */
    public function subscribers()
    {
        return $this->hasMany(Subscriber::class);
    }

    /**
     * The per-plan Project_Limit stored in the `features` JSON array.
     *
     * Returns the configured integer when `features['project_limit']` is set,
     * or null when the key is absent so callers can fall back to the
     * Free_Tier_Limit (Requirement 8.3). The `features` attribute is cast to
     * an array, but guard against non-array/legacy values for safety.
     */
    public function projectLimit(): ?int
    {
        $features = $this->features;

        if (! is_array($features) || ! array_key_exists('project_limit', $features)) {
            return null;
        }

        $value = $features['project_limit'];

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
