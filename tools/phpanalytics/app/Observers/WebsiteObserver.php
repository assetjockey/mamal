<?php

namespace App\Observers;

use App\Models\Website;

class WebsiteObserver
{
    /**
     * Handle the Website "deleting" event.
     *
     * @param  Website  $website
     * @return void
     */

    public function deleting(Website $website)
    {
        $website->events()->delete();
        $website->stats()->delete();
    }
}
