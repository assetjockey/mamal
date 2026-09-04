<?php

namespace App\Observers;

use App\Models\Incident;
use Carbon\Carbon;

class IncidentObserver
{
    /**
     * Handle the Incident "deleting" event.
     */
    public function deleting(Incident $incident): void
    {
        // If the incident is ongoing
        if (!$incident->ended_at) {
            $monitor = $incident->monitor;

            $now = Carbon::now();

            $monitor->status = 'pending';
            $monitor->started_at = $now;
            $monitor->checked_at = $now;
            $monitor->save();
        }
    }
}
