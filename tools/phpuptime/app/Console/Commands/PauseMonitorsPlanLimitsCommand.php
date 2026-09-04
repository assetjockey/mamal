<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PauseMonitorsPlanLimitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:pause-monitors-plan-limits';

    /**
     * The console command description.
     */
    protected $description = 'Pause the monitors outside the plan limits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::withCount('monitors')->chunk(config('settings.request_simultaneous_requests'), function ($users)
        {
            foreach ($users as $user) {
                $now = Carbon::now();
                $activeMonitorsCount = $user->monitors()->where('status', '!=', 'paused')->count();

                // If the user's monitors count exceeds the plan limits,
                // and the user is not on an unlimited plan
                if (
                    $user->monitors_count > $user->active_plan->features->monitors
                    && $user->active_plan->features->monitors != -1
                ) {
                    // Check if the active monitors exceed the plan limits
                    if ($activeMonitorsCount > $user->active_plan->features->monitors) {
                        // Pause monitors exceeding the plan limits
                        Monitor::where('user_id', $user->id)
                            ->where('status', '!=', 'paused')
                            ->orderByDesc('created_at')
                            ->take(($activeMonitorsCount - $user->active_plan->features->monitors))
                            ->update([
                                'status' => 'paused',
                                'started_at' => $now,
                            ]);
                    }
                }

                // Update the monitors intervals exceeding the plan limits
                Monitor::where('user_id', $user->id)
                    ->where('interval', '<', min($user->active_plan->features->monitor_intervals))
                    ->update([
                        'interval' => min($user->active_plan->features->monitor_intervals)
                    ]);
            }
        });

        return 0;
    }
}
