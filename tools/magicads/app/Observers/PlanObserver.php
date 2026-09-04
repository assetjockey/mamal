<?php

namespace App\Observers;

use App\Models\Plan;

/**
 * Watches Plan changes and invalidates the cached gateway fingerprint
 * whenever a pricing-relevant attribute changes. The next checkout (or
 * the artisan sync command) will then push the new pricing to Stripe /
 * PayPal automatically.
 *
 * We deliberately don't make API calls inside the observer:
 *   - It keeps the admin save UI fast (no Stripe/PayPal latency)
 *   - It avoids cascading failures if a gateway is temporarily down
 *   - Sync still happens lazily at the moment a customer actually buys
 */
class PlanObserver
{
    /**
     * Attributes whose change should bust the cached gateway fingerprint.
     */
    private const PRICING_FIELDS = ['name', 'price', 'currency', 'plan_type'];

    public function updating(Plan $plan): void
    {
        $shouldInvalidate = false;
        foreach (self::PRICING_FIELDS as $field) {
            if ($plan->isDirty($field)) {
                $shouldInvalidate = true;
                break;
            }
        }

        if (! $shouldInvalidate) {
            return;
        }

        // Wipe the fingerprints so the synchronizers detect drift and
        // create fresh gateway-side prices on next use.
        $fingerprintFields = [
            'stripe_plan_fp',
            'paypal_plan_fp',
            'paystack_plan_fp',
            'razorpay_plan_fp',
            'mollie_plan_fp',
            'flutterwave_plan_fp',
            'paddle_plan_fp',
            'yookassa_plan_fp',
            'mercadopago_plan_fp',
            'iyzico_plan_fp',
            'midtrans_plan_fp',
            'braintree_plan_fp',
        ];

        foreach ($fingerprintFields as $field) {
            if ($plan->{$field}) {
                $plan->{$field} = null;
            }
        }
    }
}
