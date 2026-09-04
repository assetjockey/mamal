<?php

namespace App\Observers;

use App\Models\Pixel;

class PixelObserver
{
    /**
     * Handle the Pixel "deleting" event.
     */
    public function deleting(Pixel $pixel): void
    {
        $pixel->links()->detach();
    }
}
