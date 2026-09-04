<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A normalized daily metric row for one ad entity (ad / ad set / campaign).
 *
 * Every provider adapter maps its native reporting shape onto these canonical
 * columns, so the dashboards and aggregator never branch per provider. Base
 * metrics are stored; derived metrics (ctr, cpc, cpm, roas, cpa) are computed
 * on the fly to stay internally consistent.
 *
 * A row's grain is identified by (ad_account_id, level, entity_id, date).
 */
class AdMetric extends Model
{
    protected $table = 'ad_metrics';

    protected $guarded = [];

    protected $casts = [
        'date'             => 'date',
        'impressions'      => 'integer',
        'clicks'           => 'integer',
        'spend'            => 'decimal:4',
        'conversions'      => 'decimal:2',
        'conversion_value' => 'decimal:4',
    ];

    public function account()
    {
        return $this->belongsTo(AdAccount::class, 'ad_account_id');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeLevel($q, string $level)
    {
        return $q->where('level', $level);
    }

    public function scopeBetween($q, $from, $to)
    {
        return $q->whereBetween('date', [$from, $to]);
    }

    /* -------- derived metrics (safe against divide-by-zero) -------- */

    public function ctr(): float
    {
        return $this->impressions > 0
            ? round($this->clicks / $this->impressions * 100, 2)
            : 0.0;
    }

    public function cpc(): float
    {
        return $this->clicks > 0
            ? round((float) $this->spend / $this->clicks, 2)
            : 0.0;
    }

    public function cpm(): float
    {
        return $this->impressions > 0
            ? round((float) $this->spend / $this->impressions * 1000, 2)
            : 0.0;
    }

    public function roas(): float
    {
        return (float) $this->spend > 0
            ? round((float) $this->conversion_value / (float) $this->spend, 2)
            : 0.0;
    }

    public function cpa(): float
    {
        return (float) $this->conversions > 0
            ? round((float) $this->spend / (float) $this->conversions, 2)
            : 0.0;
    }
}
