<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StripeMarketplace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Drives the theme purchase payment flow.
 *
 * The catalog, checkout and success screens are rendered by Livewire
 * components ({@see \App\Livewire\Admin\Themes\Themes},
 * {@see \App\Livewire\Admin\Themes\ThemeCheckout},
 * {@see \App\Livewire\Admin\Themes\ThemeSuccess}). This controller only owns
 * the gateway hand-off and Stripe return URLs, which need to live outside of a
 * Livewire lifecycle so external redirects work cleanly.
 */
class ThemeController extends Controller
{
    private StripeMarketplace $stripe;

    public function __construct()
    {
        $this->stripe = new StripeMarketplace;
    }

    /**
     * Interstitial "initiating secure payment" screen. It immediately POSTs to
     * {@see process()} and forwards the buyer to the hosted Stripe checkout.
     */
    public function gateway(): View|RedirectResponse
    {
        if (! session()->has('type')) {
            return redirect()->route('admin.themes');
        }

        return view('admin.themes.gateway');
    }

    /**
     * Create the Stripe Checkout Session and return its hosted-checkout URL.
     */
    public function process(): JsonResponse
    {
        return $this->stripe->processStripe();
    }

    /**
     * Stripe success_url — verify the payment and continue to the success page.
     */
    public function approved(): RedirectResponse
    {
        return $this->stripe->handleThemeApproval();
    }

    /**
     * Stripe cancel_url — buyer abandoned the hosted checkout.
     */
    public function cancel(): RedirectResponse
    {
        return $this->stripe->processThemeCancel();
    }
}
