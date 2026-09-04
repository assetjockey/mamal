<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PauseOfflineMonitorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:pause-offline-monitors';

    /**
     * The console command description.
     */
    protected $description = 'Pause the offline monitors';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Pause monitors that have been offline for too long
        DB::table('monitors')
            ->where('status', '=', 'offline')
            ->where('started_at', '<', Carbon::now()->subDays(config('settings.monitors_inactive_days')))
            ->whereIn('user_id', function ($query) {
                // Get users that have never subscribed to a plan, and are not an admin
                $query->select('id')->from('users')->where([['plan_id', '=', 1], ['role', '!=', 1]]);
            })
            ->update([
                'status' => 'paused',
                'started_at' => Carbon::now(),
            ]);

        return 0;
    }
}
