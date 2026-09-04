<?php

namespace App\Observers;

use App\Models\StatusPage;
use App\Traits\Webhookable;
use Illuminate\Support\Facades\Storage;

class StatusPageObserver
{
    /**
     * Handle the StatusPage "created" event.
     */
    public function created(StatusPage $statusPage): void
    {
        $statusPage->sendWebhook([
            'id' => $statusPage->id,
            'domain' => $statusPage->domain,
            'action' => 'created'
        ], config('settings.webhook_status_page_created'));
    }

    /**
     * Handle the StatusPage "updated" event.
     */
    public function updated(StatusPage $statusPage): void
    {
        $statusPage->sendWebhook([
            'id' => $statusPage->id,
            'domain' => $statusPage->domain,
            'action' => 'updated'
        ], config('settings.webhook_status_page_updated'));
    }

    /**
     * Handle the StatusPage "forceDeleted" event.
     */
    public function deleted(StatusPage $statusPage): void
    {
        $statusPage->sendWebhook([
            'id' => $statusPage->id,
            'domain' => $statusPage->domain,
            'action' => 'deleted'
        ], config('settings.webhook_status_page_deleted'));
    }

    /**
     * Handle the Status Page "deleting" event.
     */
    public function deleting(StatusPage $statusPage): void
    {
        $statusPage->monitors()->detach();

        Storage::disk(config('settings.storage_driver'))->delete('users/'. $statusPage->user_id .'/status-pages/' . $statusPage->id . '/' . $statusPage->logo);

        Storage::disk(config('settings.storage_driver'))->delete('users/'. $statusPage->user_id .'/status-pages/' . $statusPage->id . '/' . $statusPage->favicon);
    }
}
