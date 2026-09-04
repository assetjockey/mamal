<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Services\AdAnalytics\AdSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sync one ad account's metrics in the background. Used by the manual
 * "Sync now" button and by the scheduled SyncAdAnalyticsCommand.
 */
class SyncAdAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Give a slow provider report plenty of room. */
    public int $timeout = 600;

    public int $tries = 2;

    public function __construct(
        public int $adAccountId,
        public ?int $lookbackDays = null,
    ) {}

    public function handle(AdSyncService $sync): void
    {
        $account = AdAccount::find($this->adAccountId);

        if (! $account || ! $account->status) {
            return;
        }

        $sync->sync($account, $this->lookbackDays);
    }

    /** Prevent overlapping syncs of the same account. */
    public function uniqueId(): string
    {
        return 'ad-account-sync-' . $this->adAccountId;
    }
}
