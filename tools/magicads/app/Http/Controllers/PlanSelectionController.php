<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\HelperService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * PlanSelectionController
 *
 * Single entry point for the public pricing "Choose plan" buttons. It bridges
 * the gap between the marketing pricing cards and the authenticated checkout
 * flow so that a visitor's plan choice is never lost — regardless of whether
 * they're already signed in, need to register, or need to verify their email
 * first.
 *
 *   • Signed-in user  → straight to checkout for the selected plan.
 *   • Guest           → the plan is stashed as a session "intent" and the user
 *                       is sent to register/login. {@see HelperService::planIntentRedirect()}
 *                       replays that intent from the auth responses once the
 *                       user is authenticated (and verified, if required), so
 *                       they land on checkout instead of the bare dashboard.
 *
 * Free plans never carry an intent — there's nothing to check out — so guests
 * are simply sent to register.
 */
class PlanSelectionController extends Controller
{
    public function select(string $planId): RedirectResponse
    {
        // Billing lives in the SaaS plugin. If it isn't active there's nothing
        // to buy, so fall back to the marketing home page.
        if (! HelperService::extensionSaaS()) {
            return redirect()->route('home');
        }

        $plan = Plan::where('plan_id', $planId)
            ->where('status', 'active')
            ->first();

        // Unknown / inactive plan → back to the pricing section.
        if (! $plan) {
            return redirect()->route('home');
        }

        $isFree = (float) $plan->price <= 0;

        // Already authenticated: send them where they actually need to go.
        if (Auth::check()) {
            if ($isFree) {
                return redirect()->to(
                    LaravelLocalization::localizeUrl(route('user.billing'))
                );
            }

            return redirect()->to(LaravelLocalization::localizeUrl(
                route('user.billing.checkout', ['planId' => $plan->plan_id])
            ));
        }

        // Guest. Remember the paid plan they picked so it survives the
        // register → (verify email) → login round trip.
        if (! $isFree) {
            session(['plan_intent' => $plan->plan_id]);
        }

        $registrationEnabled = class_exists(Features::class)
            && Features::enabled(Features::registration());

        return redirect()->route($registrationEnabled ? 'register' : 'login');
    }
}
