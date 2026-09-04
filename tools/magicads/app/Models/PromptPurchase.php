<?php

namespace App\Models;

use App\Services\PromptMarketplace\PreviewUrl;
use Illuminate\Database\Eloquent\Model;

/**
 * A completed marketplace sale.
 *
 * This row is the buyer's PERSISTENT copy of what they paid for: the full
 * prompt plus an independently-copied preview file. It survives the seller
 * deleting the source creative or unlisting the offer, and also serves as the
 * admin transaction ledger.
 */
class PromptPurchase extends Model
{
    protected $guarded = [];

    protected $casts = [
        'generation_meta'   => 'array',
        'price_paid'        => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'seller_earning'    => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function listing()
    {
        return $this->belongsTo(PromptListing::class, 'listing_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function previewUrl(): ?string
    {
        return PreviewUrl::resolve($this->preview_disk, $this->preview_path);
    }
}
