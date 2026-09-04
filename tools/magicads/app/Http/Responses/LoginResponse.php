<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        $user = $request->user();

        // If the user picked a plan on the public pricing page before signing
        // in, send them straight to that plan's checkout instead of the
        // dashboard. Only consume the intent once the user can actually reach
        // checkout (it sits behind the `verified` middleware); otherwise leave
        // it in the session for VerifyEmailResponse to replay after they verify.
        if ($user->hasVerifiedEmail()
            && ($planTarget = \App\Services\HelperService::planIntentRedirect())) {
            return redirect()->to($planTarget);
        }

        // Use named routes so URLs are generated through Laravel's URL generator,
        // then run them through localizeUrl() so the active locale is preserved.
        $target = $user->hasRole('admin')
            ? route('admin.dashboard')
            : route('user.dashboard');

        return redirect()->intended(LaravelLocalization::localizeUrl($target));
    }
}
