<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Auth\Events\Registered;

class NotifyAdminOnUserRegistered
{
    public function handle(Registered $event): void
    {
        $admin = User::where('group', 'admin')->first();

        if ($admin) {
            $admin->notify(new NewUserRegistered($event->user));
        }
    }
}
