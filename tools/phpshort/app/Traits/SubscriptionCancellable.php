<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client as GuzzleClient;

trait SubscriptionCancellable
{
    /**
     * Cancel the current plan subscription.
     */
    public function cancelSubscription(): void
    {
        if (!$this->plan_payment_processor || !$this->plan_subscription_id) {
            return;
        }

        try {
            match ($this->plan_payment_processor) {
                'paypal' => $this->cancelPayPal(),
                'stripe' => $this->cancelStripe(),
                'mollie' => $this->cancelMollie(),
                'paddle' => $this->cancelPaddle(),
                'razorpay' => $this->cancelRazorpay(),
                'paystack' => $this->cancelPaystack(),
                'xendit' => $this->cancelXendit(),
                default => null
            };
        } catch (Exception) {}

        if (!empty($this->plan_recurring_at)) {
            $this->plan_ends_at = $this->plan_recurring_at;
            $this->plan_recurring_at = null;
        }

        $this->save();
    }

    /**
     * Cancel the PayPal subscription.
     */
    private function cancelPayPal(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $payPalAuthRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/oauth2/token', [
                'auth' => [config('settings.paypal_client_id'), config('settings.paypal_secret')],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $payPalAuth = json_decode($payPalAuthRequest->getBody()->getContents());

            $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/billing/subscriptions/' . $this->plan_subscription_id . '/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'reason' => __('Cancelled', [], app()->getLocale())
                ])
            ]);
        } catch (Exception) {}
    }

    /**
     * Cancel the Stripe subscription.
     */
    private function cancelStripe(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $httpClient->request('POST', 'https://api.stripe.com/v1/subscriptions/' . $this->plan_subscription_id, [
                'auth' => [config('settings.stripe_secret'), ''],
                'form_params' => [
                    'cancel_at_period_end' => 'true',
                ],
            ]);
        } catch (Exception) {}
    }

    /**
     * Cancel the Mollie subscription.
     */
    private function cancelMollie(): void
    {
        if (isset($this->plan_subscription_information->customerId)) {
            $httpClient = new GuzzleClient();

            try {
                $mollieSubscriptionCancelRequest = $httpClient->delete('https://api.mollie.com/v2/customers/' . $this->plan_subscription_information->customerId . '/subscriptions/' . $this->plan_subscription_id, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $mollieSubscription = json_decode($mollieSubscriptionCancelRequest->getBody());

                $this->plan_subscription_status = $mollieSubscription->status;
            } catch (Exception) {}
        }
    }

    /**
     * Cancel the Paddle subscription.
     */
    private function cancelPaddle(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $httpClient->request('POST', 'https://' . (config('settings.paddle_mode') == 'sandbox' ? 'sandbox-api' : 'api') . '.paddle.com/subscriptions/' . $this->plan_subscription_id . '/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paddle_api_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'effective_from' => 'next_billing_period',
                ],
            ]);
        } catch (Exception) {}
    }

    /**
     * Cancel the RazorPay subscription.
     */
    private function cancelRazorpay(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $httpClient->request('POST', 'https://api.razorpay.com/v1/subscriptions/' . $this->plan_subscription_id . '/cancel', [
                'auth' => [config('settings.razorpay_key'), config('settings.razorpay_secret')],
            ]);
        } catch (Exception) {}
    }

    /**
     * Cancel the Paystack subscription.
     */
    private function cancelPaystack(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $paystackSubscriptionRequest = $httpClient->request('GET', 'https://api.paystack.co/subscription/' . $this->plan_subscription_id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paystack_secret'),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ]
            ]);

            $paystackSubscription = json_decode($paystackSubscriptionRequest->getBody()->getContents());

            $httpClient->request('POST', 'https://api.paystack.co/subscription/disable', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paystack_secret'),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                'body' => json_encode([
                    'code' => $this->plan_subscription_id,
                    'token' => $paystackSubscription->data->email_token
                ])
            ]);
        } catch (Exception) {}
    }

    /**
     * Cancel the Xendit subscription.
     */
    private function cancelXendit(): void
    {
        $httpClient = new GuzzleClient();

        try {
            $httpClient->request('POST', 'https://api.xendit.co/recurring/plans/' . $this->plan_subscription_id . '/deactivate', [
                'auth' => [config('settings.xendit_secret_key'), ''],
                'headers' => [
                    'api-version' => '2026-01-01',
                    'Content-Type' => 'application/json',
                ]
            ]);
        } catch (Exception) {}
    }
}