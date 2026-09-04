<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StripeMarketplace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Drives the plugin/extension purchase payment flow.
 *
 * The catalog, checkout and success screens are rendered by Livewire components
 * ({@see \App\Livewire\Admin\Plugins\Plugins},
 * {@see \App\Livewire\Admin\Plugins\PluginCheckout},
 * {@see \App\Livewire\Admin\Plugins\PluginSuccess},
 * {@see \App\Livewire\Admin\Plugins\PluginSuccessPackage}). This controller only
 * owns the gateway hand-off, the marketplace bundle/support purchase entry
 * points, and the Stripe return URLs — all of which need to live outside of a
 * Livewire lifecycle so external redirects work cleanly.
 *
 * It mirrors {@see ThemeController} so the two product families behave
 * identically.
 */
class PluginController extends Controller
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
            return redirect()->route('admin.plugins');
        }

        return view('admin.plugins.gateway');
    }

    /**
     * Stash the marketplace bundle / support subscription in the session and
     * hand off to the gateway screen.
     *
     * These two products are not individual plugins, so they are priced and
     * labelled here rather than coming from a catalog entry.
     */
    public function purchasePackage(string $slug): RedirectResponse
    {
        $packages = [
            'premier' => [
                'type' => 'package',
                'amount' => 999,
                'name' => __('Premier Package Bundle'),
            ],
            'support' => [
                'type' => 'support',
                'amount' => 299,
                'name' => __('Premium Support'),
            ],
        ];

        if (! isset($packages[$slug])) {
            return redirect()->route('admin.plugins');
        }

        session()->put('name', $slug);
        session()->put('type', $packages[$slug]['type']);
        session()->put('amount', $packages[$slug]['amount']);
        session()->put('extension_name', $packages[$slug]['name']);

        return redirect()->route('admin.plugins.gateway');
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
        return $this->stripe->handlePluginApproval();
    }

    /**
     * Stripe cancel_url — buyer abandoned the hosted checkout.
     */
    public function cancel(): RedirectResponse
    {
        return $this->stripe->processPluginCancel();
    }
}
