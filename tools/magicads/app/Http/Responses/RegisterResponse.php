<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Where to send a user immediately after they register.
     *
     * Fortify's stock response redirects to `config('fortify.home')`
     * (`/dashboard`), but this application has no such route — dashboards live
     * at the role-specific `user.dashboard` / `admin.dashboard` named routes
     * under the localized `/app` prefix. We mirror {@see LoginResponse} so a
     * freshly registered user lands on a route that actually exists.
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $user = $request->user();

        // If the user came from a "Choose plan" button on the public pricing
        // page, PlanSelectionController stashed the plan in the session. When
        // email verification is disabled the user is already verified, so we can
        // forward them straight to checkout. When it's enabled we leave the
        // intent untouched so VerifyEmailResponse can replay it after the user
        // confirms their email (checkout is gated by the `verified` middleware).
        if ($user && $user->hasVerifiedEmail()
            && ($planTarget = \App\Services\HelperService::planIntentRedirect())) {
            return redirect()->to($planTarget);
        }

        // Newly registered users get the `user` role, but resolve the target by
        // role anyway so the behavior stays correct if registration ever creates
        // other roles.
        $target = $user && $user->hasRole('admin')
            ? route('admin.dashboard')
            : route('user.dashboard');

        return redirect()->intended(LaravelLocalization::localizeUrl($target));
    }
}
