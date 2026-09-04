<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\TransferFile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired subscriptions if user does not renew it'; // You can set interval from admin>settings>general

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
        $subscriptions = Subscription::where([['expiry_at', '<', Carbon::now()], ['status', 1]])->with('plan')
            ->get();
        foreach ($subscriptions as $subscription) {
            if ($subscription->plan->price != 0 || $subscription->plan->interval != 2) {
                if (Carbon::parse($subscription->expiry_at)->addDays(settings('expired_subscriptions_data_delete')) < Carbon::now()) {
                    $transfers = Transfer::where([['user_id', $subscription->user_id], ['status', 1], ['files_deleted_at', null]])->get();
                    foreach ($transfers as $transfer) {
                        $transferFiles = TransferFile::where('transfer_id', $transfer->id)->get();
                        foreach ($transferFiles as $transferFile) {
                            $handler = $transferFile->storageProvider->handler;
                            if ($handler::delete($transferFile->path)) {
                                $transferFile->delete();
                            }
                        }
                        $transfer->delete();
                    }
                    $transactions = Transaction::where('user_id', $subscription->user_id)->get();
                    if ($transactions->count() > 0) {
                        foreach ($transactions as $transaction) {
                            $transaction->delete();
                        }
                    }
                    $subscription->delete();
                }
            }
        }
    }
}
