<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUnverifiedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:delete-unverified-users';

    /**
     * The console command description.
     */
    protected $description = 'Delete the unverified users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::table('users')->where([['email_verified_at', '=', NULL], ['created_at', '<', Carbon::now()->subDays(30)]])->delete();

        return 0;
    }
}
