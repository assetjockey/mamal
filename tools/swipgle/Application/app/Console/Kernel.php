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
        Commands\Subscriptions\DeleteExpired::class,
        Commands\Subscriptions\RenewFree::class,
        Commands\Subscriptions\ExpiryReminder::class,
        Commands\Subscriptions\RenewalReminder::class,
        Commands\DeleteUnpaidTransactions::class,
        Commands\DeleteUploadedFiles::class,
        Commands\DeleteExpiredTransferFiles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('subscriptions:delete-expired')->everyMinute(); // run every minute
        $schedule->command('subscriptions:renew-free')->everyMinute(); // run every minute
        $schedule->command('subscription:expiry-reminder')->cron('0 0 */3 * *'); // run every 3 days at 00:00 on every 3rd day-of-month
        $schedule->command('subscription:renewal-reminder')->cron('0 0 */2 * *'); // run every day at 00:00 on every day-of-month
        $schedule->command('transactions:unpaid-delete')->cron('25 * * * *'); // run every hour on the 25th minute
        $schedule->command('uploads:delete')->cron('25 * * * *'); // run every hour on the 25th minute
        $schedule->command('transfer:expired-files-delete')->everyMinute(); // run every minute
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
