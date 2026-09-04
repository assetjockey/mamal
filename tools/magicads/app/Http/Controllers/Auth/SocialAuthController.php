<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Concerns\ResolvesReferrals;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Services\HelperService;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Throwable;

/**
 * Handles social (oAuth) sign-in via Laravel Socialite.
 *
 * Flow:
 *  - `redirect()`  sends the visitor to the chosen provider's consent screen.
 *  - `callback()`  receives the provider's response, finds or creates a local
 *                  user, logs them in, and forwards them to their dashboard.
 *
 * All provider availability + credential wiring is delegated to
 * {@see SocialAuthService}, which reads the admin-managed settings row.
 */
class SocialAuthController extends Controller
{
    use ResolvesReferrals;

    public function __construct(private readonly SocialAuthService $social) {}

    /**
     * Send the user to the provider's OAuth consent screen.
     */
    public function redirect(string $provider, Request $request): RedirectResponse
    {
        if (! $this->social->isProviderEnabled($provider)) {
            return $this->failRedirect(__('That social login option is not available.'));
        }

        // Stash any referral code so it survives the OAuth round-trip — query
        // params don't come back on the provider callback.
        if (filled($request->query('ref'))) {
            session(['referral_code' => (string) $request->query('ref')]);
        }

        try {
            return $this->social->driver($provider)->redirect();
        } catch (Throwable $e) {
            report($e);

            return $this->failRedirect(__('Unable to start social login. Please try again later.'));
        }
    }

    /**
     * Handle the provider callback: resolve the user and authenticate them.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! $this->social->isProviderEnabled($provider)) {
            return $this->failRedirect(__('That social login option is not available.'));
        }

        try {
            $socialUser = $this->social->driver($provider)->user();
        } catch (Throwable $e) {
            report($e);

            return $this->failRedirect(__('We could not complete the social login. Please try again.'));
        }

        $email = $socialUser->getEmail();

        if (blank($email)) {
            return $this->failRedirect(__('Your social account did not share an email address, so we cannot sign you in.'));
        }

        $user = $this->resolveUser($provider, $socialUser->getId(), $email, $socialUser->getName(), $socialUser->getAvatar());

        if (! $user) {
            return $this->failRedirect(__('Registration is currently closed, so a new account could not be created.'));
        }

        if ($user->status === 'banned' || $user->status === 'suspended') {
            return $this->failRedirect(__('Your account is not active. Please contact support.'));
        }

        Auth::login($user, remember: true);

        // Honor a plan picked on the public pricing page before this social
        // sign-in. Social accounts are marked verified on creation, so checkout
        // is reachable right away.
        if ($planTarget = HelperService::planIntentRedirect()) {
            return redirect()->to($planTarget);
        }

        $target = $user->hasRole('admin')
            ? route('admin.dashboard')
            : route('user.dashboard');

        return redirect()->intended(LaravelLocalization::localizeUrl($target));
    }

    /**
     * Find an existing user (by provider identity or email) or create one.
     *
     * Returns null when the user does not exist and public registration is
     * disabled by the site owner.
     */
    private function resolveUser(string $provider, ?string $providerId, string $email, ?string $name, ?string $avatar): ?User
    {
        // 1) Returning social user — matched on the stable provider identity.
        if (filled($providerId)) {
            $existing = User::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        // 2) Existing local account with the same email — link it to the
        //    provider so future social logins resolve in step 1.
        $byEmail = User::query()->where('email', $email)->first();

        if ($byEmail) {
            $byEmail->forceFill([
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);

            if (blank($byEmail->avatar) || $byEmail->avatar === 'img/users/avatar.jpg') {
                if (filled($avatar)) {
                    $byEmail->avatar = $avatar;
                }
            }

            if (blank($byEmail->email_verified_at)) {
                $byEmail->email_verified_at = now();
            }

            $byEmail->save();

            return $byEmail;
        }

        // 3) Brand new user — only if public registration is enabled.
        if (! $this->registrationEnabled()) {
            return null;
        }

        return $this->createUser($provider, $providerId, $email, $name, $avatar);
    }

    /**
     * Create a fresh social-only user, mirroring the standard registration
     * action (CreateNewUser) so social accounts get the same defaults.
     */
    private function createUser(string $provider, ?string $providerId, string $email, ?string $name, ?string $avatar): User
    {
        // Resolve the referrer (if any) from the code stashed at redirect time.
        $referrer = $this->resolveReferrer(session('referral_code'));

        $user = User::create([
            'name' => $name ?: Str::before($email, '@'),
            'email' => $email,
            'password' => null,
            'provider' => $provider,
            'provider_id' => $providerId,
            'user_id' => $this->generateUserId(),
            'avatar' => filled($avatar) ? $avatar : 'img/users/avatar.jpg',
            'referral_id' => strtoupper(Str::random(15)),
            'referred_by' => $referrer?->referral_id,
        ]);

        $user->forceFill([
            'group' => 'user',
            'status' => 'active',
            'credits' => 0,
            'email_verified_at' => now(),
        ])->save();

        $user->assignRole('user');

        if ($referrer) {
            $this->recordReferral($referrer, $user);
        }

        // Consume the stashed code so it can't leak into a later signup.
        session()->forget('referral_code');

        return $user;
    }

    /**
     * Whether the site owner allows new user registration.
     */
    private function registrationEnabled(): bool
    {
        $settings = GeneralSetting::query()->first();

        // Default to allowing registration when the setting is absent so a
        // fresh install isn't accidentally locked down.
        return $settings ? (bool) $settings->user_registration : true;
    }

    /**
     * Generate a unique 3-segment user id (XXXXX-XXXXX-XXXXX, lowercased),
     * matching the format produced during standard registration.
     */
    private function generateUserId(): string
    {
        do {
            $code = strtolower(
                Str::upper(Str::random(5)).'-'.
                Str::upper(Str::random(5)).'-'.
                Str::upper(Str::random(5))
            );
        } while (User::query()->where('user_id', $code)->exists());

        return $code;
    }

    /**
     * Redirect back to the login page with a flashed error message.
     */
    private function failRedirect(string $message): RedirectResponse
    {
        return redirect()
            ->to(LaravelLocalization::localizeUrl(route('login')))
            ->with('status', $message)
            ->withErrors(['social' => $message]);
    }
}
