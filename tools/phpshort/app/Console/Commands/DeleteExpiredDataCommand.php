<?php

namespace App\Console\Commands;

use App\Models\Stat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:delete-expired-data';

    /**
     * The console command description.
     */
    protected $description = 'Delete the expired data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        foreach (User::cursor() as $user) {
            if ($user->active_plan->features->data_retention >= 0) {
                Stat::whereIn('link_id', function ($q) use ($user) {
                    $q->select('id')->from('links')->where('user_id', '=', $user->id);
                })->where('created_at', '<', (clone $now)->subDays((int) $user->active_plan->features->data_retention + (int) config('settings.data_retention_grace_days'))->startOfDay())->delete();
            }
        }

        return 0;
    }
}
