<?php

namespace App\Observers;

use App\Models\Link;

class LinkObserver
{
    /**
     * Handle the Link "deleting" event.
     */
    public function deleting(Link $link): void
    {
        $link->stats()->delete();
        $link->pixels()->detach();
    }
}
