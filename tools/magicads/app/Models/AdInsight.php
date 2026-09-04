<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A cached AI-generated performance analysis for a user + date range.
 *
 * Generating an insight spends credits (see AdAnalyticsBilling), so results are
 * cached and re-served rather than regenerated on every page view. The
 * `metrics_snapshot` records the KPI totals the analysis was based on.
 */
class AdInsight extends Model
{
    protected $table = 'ad_insights';

    protected $guarded = [];

    protected $casts = [
        'metrics_snapshot' => 'array',
        'recommendations'  => 'array',
        'wins'             => 'array',
        'risks'            => 'array',
        'range_from'       => 'date',
        'range_to'         => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }
}
