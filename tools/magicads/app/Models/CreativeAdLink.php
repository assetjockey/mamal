<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The attribution bridge between a MagicAds creative and an external ad.
 *
 * Populated three ways (best-effort, in order of confidence):
 *   - 'tag'    — a magicads_<id> token recovered from the ad name / utm_content
 *   - 'manual' — the user explicitly linked an external ad to a creative
 *   - 'auto'   — a heuristic match (reserved for future hash matching)
 *
 * `external_ad_id` is unique per ad account so one ad maps to one creative.
 */
class CreativeAdLink extends Model
{
    protected $table = 'creative_ad_links';

    protected $guarded = [];

    protected $casts = [
        'confidence' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(AdAccount::class, 'ad_account_id');
    }

    public function creative()
    {
        return $this->belongsTo(AdCreative::class, 'creative_id');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }
}
