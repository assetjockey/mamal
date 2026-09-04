<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastSeen
{
    public function handle(Login $event): void
    {
        $event->user->update(['last_seen' => now()]);
    }
}
