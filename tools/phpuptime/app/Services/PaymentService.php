<?php

namespace App\Services;

use App\Mail\PaymentMail;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\TaxRate;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    /**
     * Store the payment.
     */
    public function store(array $data): Payment
    {
        $payment = new Payment();
        $payment->user_id = $data['user_id'];
        $payment->plan_id = $data['plan_id'];
        $payment->payment_id = $data['payment_id'];
        $payment->processor = $data['processor'];
        $payment->amount = $data['amount'];
        $payment->currency = $data['currency'];
        $payment->interval = $data['interval'];
        $payment->status = $data['status'];
        $payment->product = Plan::select('id', 'name', 'currency', 'amount_' . $data['interval'])->where('id', '=', $data['plan_id'])->withTrashed()->first();
        $payment->coupon = $data['coupon'] ? Coupon::select('id', 'name', 'code', 'type', 'percentage')->where('id', '=', $data['coupon'])->withTrashed()->first() : null;
        $payment->tax_rates = $data['tax_rates'] ? TaxRate::select('id', 'name', 'type', 'percentage')->whereIn('id', explode('_', $data['tax_rates']))->withTrashed()->get() : null;
        $payment->customer = $data['customer'];
        $payment->seller = collect([
            'title' => config('settings.title'),
            'vendor' => config('settings.billing_vendor'),
            'address' => config('settings.billing_address'),
            'city' => config('settings.billing_city'),
            'state' => config('settings.billing_state'),
            'postal_code' => config('settings.billing_postal_code'),
            'country' => config('settings.billing_country'),
            'phone' => config('settings.billing_phone'),
            'vat_number' => config('settings.billing_vat_number')
        ]);
        $payment->save();

        $payment->invoice_id = config('settings.invoice_prefix') . $payment->id;
        $payment->save();

        return $payment;
    }

    /**
     * Send the invoice email.
     */
    public function sendInvoiceEmail(Payment $payment, User $user): void
    {
        if (config('settings.invoicing')) {
            try {
                Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
            } catch (Exception) {}
        }
    }
}