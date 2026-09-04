<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $payment->sendWebhook($payment->toArray() + ['action' => 'created'], config('settings.webhook_payment_created'));
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        $payment->sendWebhook($payment->toArray() + ['action' => 'updated'], config('settings.webhook_payment_updated'));
    }
}
