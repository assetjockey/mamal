<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\FreeSubscriptionRenewalNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class RenewFree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:renew-free';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renewing the free subscription and inform the users via email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $plan = Plan::where([['price', 0], ['interval', '!=', 2]])->first();
        if (!is_null($plan)) {
            $subscriptions = Subscription::where([['plan_id', $plan->id], ['expiry_at', '<=', Carbon::now()], ['status', 1]])->with(['user', 'plan'])->get();
            foreach ($subscriptions as $subscription) {
                $expiry_at = ($subscription->plan->interval) ? Carbon::now()->addYear() : Carbon::now()->addMonth();
                $update = $subscription->update(['expiry_at' => $expiry_at]);
                if ($update) {
                    $title = lang('Your free subscription has been renewed', 'notifications');
                    $image = asset('images/icons/renewed.png');
                    $link = route('user.subscription');
                    userNotify($subscription->user->id, $title, $image, $link);
                    $details['name'] = $subscription->user->firstname;
                    if (settings('mail_status')) {
                        Notification::send($subscription->user, new FreeSubscriptionRenewalNotification($details));
                    }
                }
            }
        }
    }
}
