<?php

namespace App\Observers;

use App\Models\Domain;
use Illuminate\Support\Facades\Storage;

class DomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Domain $domain): void
    {
        $domain->sendWebhook([
            'id' => $domain->id,
            'name' => $domain->name,
            'action' => 'created'
        ], config('settings.webhook_domain_created'));
    }

    /**
     * Handle the Domain "deleted" event.
     */
    public function deleted(Domain $domain): void
    {
        $domain->sendWebhook([
            'id' => $domain->id,
            'name' => $domain->name,
            'action' => 'deleted'
        ], config('settings.webhook_domain_deleted'));
    }

    /**
     * Handle the Domain "deleting" event.
     */
    public function deleting(Domain $domain): void
    {
        $domain->links()->each(function ($link) {
            $link->delete();
        });
    }
}
