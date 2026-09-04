<?php

namespace App\Services;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

/**
 * Handles theme AND plugin/extension purchases against the Berkine marketplace
 * through Stripe.
 *
 * The flow mirrors the legacy DaVinci implementation but is adapted to the
 * Livewire + Flux stack used here:
 *
 *   1. A checkout component stashes {name, type, amount, extension_name} in the
 *      session and sends the buyer to a lightweight "gateway" loading screen.
 *   2. That screen POSTs to {@see processStripe()} which spins up a Stripe
 *      Checkout Session using the marketplace secret key and hands back the
 *      hosted checkout URL (no publishable key required — we redirect to the
 *      session URL directly instead of using Stripe.js redirectToCheckout()).
 *   3. Stripe returns the buyer to the matching success / cancel handler.
 *
 * The session `type` value decides which product family we are buying and which
 * routes Stripe should return to:
 *   - `theme`               → theme catalog
 *   - `extension`           → plugin catalog
 *   - `package` / `support` → marketplace bundle / support subscription
 */
class StripeMarketplace
{
    /** Marketplace Stripe secret key (fetched from the licensing server). */
    protected ?string $sak;

    private ExtensionController $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionController;
        $this->sak = $this->extensions->sak();
    }

    /**
     * Create a Stripe Checkout Session for the product stashed in the session and
     * return its hosted-checkout URL as JSON for the gateway screen to redirect to.
     */
    public function processStripe()
    {
        if (! session()->has('type')) {
            return response()->json([
                'status' => false,
                'message' => __('Your checkout session has expired, please try again.'),
            ], 422);
        }

        $slug = session()->get('name');
        $type = session()->get('type');
        $amount = session()->get('amount');

        $name = 'Payment for: '.ucfirst((string) $slug).' '.ucfirst((string) $type);
        $total = (int) round(((float) $amount) * 100);

        if (empty($this->sak)) {
            return response()->json([
                'status' => false,
                'message' => __('Unable to reach the marketplace payment service, please try again later.'),
            ], 502);
        }

        Stripe::setApiKey($this->sak);

        /** @var User $user */
        $user = Auth::user();

        $isTheme = $type === 'theme';

        try {
            $session = StripeSession::create([
                'customer_email' => $user->email,
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'USD',
                            'product_data' => [
                                'name' => $name,
                            ],
                            'unit_amount' => $total,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $isTheme
                    ? route('admin.themes.payments.approved')
                    : route('admin.plugins.payments.approved'),
                'cancel_url' => $isTheme
                    ? route('admin.themes.payments.cancel')
                    : route('admin.plugins.payments.cancel'),
            ]);

            // Checkout Sessions in payment mode expose payment_intent only once
            // the buyer completes payment, so fall back to the session id which
            // the marketplace can still verify against.
            session()->put('paymentIntentID', $session->payment_intent ?? $session->id);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => __('Stripe authentication error, verify your marketplace settings first.').' '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'id' => $session->id,
            'url' => $session->url,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Theme handlers
    |--------------------------------------------------------------------------
    */

    /**
     * Stripe cancel_url handler — buyer backed out of the hosted checkout.
     */
    public function processThemeCancel()
    {
        $this->clearCheckoutSession();

        Toaster::warning(__('The payment was cancelled.'));

        return redirect()->route('admin.themes');
    }

    /**
     * Stripe success_url handler — verify the payment with the marketplace,
     * flag the theme as purchased, then hand the buyer to the success screen.
     */
    public function handleThemeApproval()
    {
        $paymentIntentID = session()->get('paymentIntentID');
        $slug = session()->get('name');

        $theme = $this->extensions->verify($slug, $paymentIntentID);

        $this->clearCheckoutSession();

        if (! empty($theme)) {
            Toaster::success(__('Payment successfully processed.'));

            return redirect()->route('admin.themes.success', ['slug' => $slug]);
        }

        Toaster::warning(__('Please contact the support team, there was an issue verifying your payment.'));

        return redirect()->route('admin.themes');
    }

    /*
    |--------------------------------------------------------------------------
    | Plugin / package handlers
    |--------------------------------------------------------------------------
    */

    /**
     * Stripe cancel_url handler for the plugin flow.
     */
    public function processPluginCancel()
    {
        $this->clearCheckoutSession();

        Toaster::warning(__('The payment was cancelled.'));

        return redirect()->route('admin.plugins');
    }

    /**
     * Stripe success_url handler for the plugin flow.
     *
     * Verifies the payment with the marketplace then routes the buyer to the
     * matching success screen: a one-click install screen for individual
     * plugins, or a "thank you / contact support" screen for the bundle and
     * support subscription.
     */
    public function handlePluginApproval()
    {
        $paymentIntentID = session()->get('paymentIntentID');
        $slug = session()->get('name');
        $type = session()->get('type');

        $verified = $this->extensions->verify($slug, $paymentIntentID);

        $this->clearCheckoutSession();

        if (! empty($verified)) {
            Toaster::success(__('Payment successfully processed.'));

            if (in_array($slug, ['premier', 'support'], true) || in_array($type, ['package', 'support'], true)) {
                return redirect()->route('admin.plugins.success.package', ['slug' => $slug]);
            }

            return redirect()->route('admin.plugins.success', ['slug' => $slug]);
        }

        Toaster::warning(__('Please contact the support team, there was an issue verifying your payment.'));

        return redirect()->route('admin.plugins');
    }

    /**
     * Drop every key the checkout flow stashes in the session.
     */
    private function clearCheckoutSession(): void
    {
        session()->forget(['paymentIntentID', 'name', 'type', 'amount', 'extension_name']);
    }
}
