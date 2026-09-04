<?php

namespace App\Models;

use App\Services\PromptMarketplace\PreviewUrl;
use Illuminate\Database\Eloquent\Model;

/**
 * A seller's marketplace offer for a single generated creative.
 *
 * Belongs to the "magicads-prompt-marketplace" plugin. The protected `prompt`
 * is only revealed for free listings, or to a buyer who has purchased a paid
 * one — the browse/detail UI is responsible for honoring that.
 */
class PromptListing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'generation_meta' => 'array',
        'is_paid'         => 'boolean',
        'price'           => 'decimal:2',
        'revenue_total'   => 'decimal:2',
        'sales_count'     => 'integer',
        'views'           => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function creative()
    {
        return $this->belongsTo(AdCreative::class, 'ad_creative_id');
    }

    public function purchases()
    {
        return $this->hasMany(PromptPurchase::class, 'listing_id');
    }

    /* ----------------------------- Scopes ------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true)->where('price', '>', 0);
    }

    public function scopeFree($query)
    {
        return $query->where(function ($q) {
            $q->where('is_paid', false)->orWhere('price', '<=', 0);
        });
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /* ----------------------------- Helpers ------------------------------ */

    /** Whether the listing actually charges (paid flag + a positive price). */
    public function isPurchasable(): bool
    {
        return $this->is_paid && (float) $this->price > 0;
    }

    /** Has the given user already bought this listing? */
    public function purchasedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->purchases()
            ->where('buyer_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Whether the prompt may be revealed to the given viewer:
     *   - free listings are open to everyone
     *   - the seller always sees their own prompt
     *   - paid listings unlock once the viewer has purchased
     */
    public function promptVisibleTo(?int $userId): bool
    {
        if (! $this->isPurchasable()) {
            return true;
        }

        if ($userId && (int) $this->seller_id === (int) $userId) {
            return true;
        }

        return $this->purchasedBy($userId);
    }

    /**
     * Resolve a public URL for the preview media. Mirrors
     * {@see \App\Models\AdCreative::fileUrl()} so cloud-offloaded previews keep
     * working through the StorageManager.
     */
    public function previewUrl(): ?string
    {
        return PreviewUrl::resolve($this->preview_disk, $this->preview_path);
    }
}
