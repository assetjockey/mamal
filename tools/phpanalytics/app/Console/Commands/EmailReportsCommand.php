<?php

namespace App\Console\Commands;

use App\Mail\ReportMail;
use App\Models\User;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class EmailReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:email-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out websites analytics email reports';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        if (config('settings.email_reports_period') == 'weekly') {
            $from = (clone $now)->startOfWeek()->subWeek();
            $to = (clone $now)->endOfWeek()->subWeek();
        } else {
            $from = (clone $now)->startOfMonth()->subMonthsNoOverflow(1);
            $to = (clone $now)->endOfMonth()->subMonthsNoOverflow(1);
        }

        foreach (User::where('has_websites', '=', 1)->cursor() as $user) {
            if ($user->can('emailReports', ['App\Models\User'])) {
                $websites = Website::with([
                    'visitors' => function ($query) use ($from, $to) {
                        $query->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
                    },
                    'pageviews' => function ($query) use ($from, $to) {
                        $query->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
                    }]
                )
                ->where([['user_id', '=', $user->id], ['email', '=', 1]])->get();

                $stats = [];
                foreach ($websites as $website) {
                    $stats[] = ['domain' => $website->domain, 'visitors' => $website->visitors->sum('count') ?? 0, 'pageviews' => $website->pageviews->sum('count') ?? 0];
                }

                // If the user has any websites with email notifications enabled
                if ($stats) {
                    try {
                        Mail::to($user->email)->locale($user->locale)->send(new ReportMail($stats, ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]));
                    } catch (\Exception $e) {}
                }
            }
        }

        return 0;
    }
}
