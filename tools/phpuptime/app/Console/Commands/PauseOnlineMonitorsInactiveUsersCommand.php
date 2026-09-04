<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PauseOnlineMonitorsInactiveUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:pause-online-monitors-inactive-users';

    /**
     * The console command description.
     */
    protected $description = 'Pause the online monitors of inactive users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (config('settings.monitors_pause_inactive_users_grace_days')) {
            DB::table('monitors')
                ->where('status', '=', 'online')
                ->whereIn('user_id', function ($query) {
                    // Get users that have never subscribed to a plan, and are not an admin, and haven't authed since a given date
                    $query->select('id')->from('users')->where([['plan_id', '=', 1], ['role', '!=', 1], ['authed_at', '<', Carbon::now()->subMinutes(config('settings.auth_remember_me_duration'))->subDays(config('settings.monitors_pause_inactive_users_grace_days'))]]);
                })
                ->update([
                    'status' => 'paused',
                    'started_at' => Carbon::now(),
                ]);
        }

        return 0;
    }
}
