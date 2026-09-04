<?php

namespace App\Observers;

use App\Models\Monitor;

class MonitorObserver
{
    /**
     * Handle the Monitor "deleting" event.
     */
    public function deleting(Monitor $monitor): void
    {
        $monitor->checks()->delete();
        $monitor->incidents()->delete();
        $monitor->statusPages()->detach();
    }
}
