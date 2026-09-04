<?php

namespace App\Observers;

use App\Models\AdCreative;
use App\Models\PromptListing;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps the Prompt Marketplace in sync when a creative is removed.
 *
 * Ships with the "magicads-prompt-marketplace" plugin and is registered in
 * AppServiceProvider behind a class_exists() guard, so core keeps working when
 * the plugin is uninstalled (its file disappears and the observer is skipped).
 *
 * When a user deletes a generated image/video, any active marketplace listing
 * for it is removed from the storefront. Buyers are unaffected — their
 * purchases live in `prompt_purchases` with a fully self-contained snapshot
 * (prompt + a copied preview file), so a deletion here never strips a paid
 * customer of what they bought.
 */
class AdCreativeObserver
{
    /**
     * Catch every delete path (gallery action, cascade, tinker, …) by hooking
     * `deleting`, before the row — and its nullOnDelete FK — is gone.
     */
    public function deleting(AdCreative $creative): void
    {
        // Defensive: the plugin's tables may not exist yet during a partial
        // install / rollback. Never let marketplace bookkeeping block a delete.
        if (! Schema::hasTable('prompt_listings')) {
            return;
        }

        try {
            PromptListing::where('ad_creative_id', $creative->id)
                ->update([
                    'status'         => 'removed',
                    'ad_creative_id' => null,
                ]);
        } catch (\Throwable $e) {
            // Swallow — deleting the creative must always succeed.
            report($e);
        }
    }
}
