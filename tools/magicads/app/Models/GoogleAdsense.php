<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleAdsense extends Model
{
    protected $guarded = [];

    protected $table = 'google_adsense';

    protected $casts = [
        'enabled' => 'boolean',
        'auto_ads' => 'boolean',
    ];

    /**
     * The catalogue of frontend ad placements. Each maps a slot column to a
     * Google-recommended ad unit format/size and the spot's purpose. The view
     * partial reads this to know how to render a given placement.
     */
    public const PLACEMENTS = [
        'home_top' => [
            'label' => 'Home — Top (below hero)',
            'format' => 'horizontal',
            'size' => '728×90 leaderboard / responsive',
        ],
        'home_bottom' => [
            'label' => 'Home — Bottom (above footer)',
            'format' => 'horizontal',
            'size' => '728×90 leaderboard / responsive',
        ],
        'blog_top' => [
            'label' => 'Blog — Top of listing',
            'format' => 'horizontal',
            'size' => '970×90 / responsive',
        ],
        'blog_article' => [
            'label' => 'Blog post — In-article',
            'format' => 'fluid',
            'size' => 'In-article fluid',
        ],
        'blog_bottom' => [
            'label' => 'Blog post — Below content',
            'format' => 'rectangle',
            'size' => '336×280 / responsive',
        ],
    ];

    /**
     * Whether AdSense is switched on and a publisher ID is present. Without
     * both, nothing should render on the frontend.
     */
    public function isActive(): bool
    {
        return $this->enabled && filled($this->publisher_id);
    }

    /**
     * The configured slot ID for a placement key, or null when not set.
     */
    public function slotFor(string $placement): ?string
    {
        return $this->{'slot_' . $placement} ?? null;
    }

    /**
     * A placement renders only when ads are active and its slot is filled.
     */
    public function hasPlacement(string $placement): bool
    {
        return $this->isActive() && filled($this->slotFor($placement));
    }
}
