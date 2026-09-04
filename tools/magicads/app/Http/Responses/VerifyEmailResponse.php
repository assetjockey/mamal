<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * Where to send a user once they've verified their email address.
     *
     * Like {@see RegisterResponse}, Fortify's stock response falls back to
     * `config('fortify.home')` (`/dashboard`) — a route this application does
     * not define. We resolve the role-specific dashboard instead so a user
     * who just confirmed their email (the step that immediately follows
     * registration when email verification is enabled) doesn't hit a 404.
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 202);
        }

        $user = $request->user();

        // Replay a plan selected on the public pricing page before signing up.
        // The user just verified their email, so checkout (behind the `verified`
        // middleware) is now reachable — forward them there instead of the
        // dashboard. The intent is consumed/cleared inside the helper.
        if ($planTarget = \App\Services\HelperService::planIntentRedirect()) {
            return redirect()->to($planTarget);
        }

        $target = $user && $user->hasRole('admin')
            ? route('admin.dashboard')
            : route('user.dashboard');

        return redirect()->intended(
            LaravelLocalization::localizeUrl($target).'?verified=1'
        );
    }
}
