<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\VerifyUserLimitsCommand',
        'App\Console\Commands\EmailReportsCommand',
        'App\Console\Commands\DeleteUnverifiedUsersCommand',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:clear')->weeklyOn(0, '01:00');
        $schedule->command('view:clear')->weeklyOn(0, '02:00');
        $schedule->command('auth:clear-resets')->weeklyOn(0, '03:00');
        $schedule->command('cron:delete-unverified-users')->dailyAt('04:00');

        if (config('settings.email_reports_period') == 'weekly') {
            $schedule->command('cron:email-reports')->weeklyOn(1, '6:00');
        } else {
            $schedule->command('cron:email-reports')->monthlyOn(1, '6:00');
        }
        $schedule->command('cron:verify-user-limits')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
