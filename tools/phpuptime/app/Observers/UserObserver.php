<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->sendWebhook([
            'id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'name' => $user->name,
            'action' => 'created'
        ], config('settings.webhook_user_created'));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $user->sendWebhook([
            'id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'name' => $user->name,
            'action' => 'updated'
        ], config('settings.webhook_user_updated'));
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $user->sendWebhook([
            'id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'name' => $user->name,
            'action' => 'deleted'
        ], config('settings.webhook_user_deleted'));
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        if ($user->isForceDeleting()) {
            $user->cancelPlanSubscription();

            Storage::disk(config('settings.storage_driver'))->delete('users/' . $user->id . '/' . $user->avatar);

            $user->statusPagesMonitors()->delete();
            $user->statusPages()->each(function ($statusPage) { $statusPage->delete(); });
            $user->checks()->delete();
            $user->monitors()->delete();
            $user->incidents()->delete();
        } else {
            $user->cancelPlanSubscription();
            $user->monitors()->update(['status' => 'paused']);
        }
    }
}
