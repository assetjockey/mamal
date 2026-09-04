<?php

namespace App\Observers;

use App\Models\Space;

class SpaceObserver
{
    /**
     * Handle the Space "deleting" event.
     */
    public function deleting(Space $space): void
    {
        $space->links()->each(function ($link) {
            $link->delete();
        });
    }
}
