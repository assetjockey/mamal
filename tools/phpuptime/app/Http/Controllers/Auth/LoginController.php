<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TfaMail;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * The path the user should be redirected to.
     */
    protected string $redirectTo;

    /**
     * Create a new controller instance.
     */
    public function __construct(protected UserService $userService)
    {
        $this->redirectTo = route('dashboard');

        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm(Request $request): View
    {
        // If the request comes from the Home or Pricing page, and the user has selected a plan
        if (($request->server('HTTP_REFERER') == route('pricing') || $request->server('HTTP_REFERER') == route('home').'/') && $request->input('plan') > 1) {
            $request->session()->put('plan_redirect', ['id' => $request->input('plan'), 'interval' => $request->input('interval')]);
        }

        if ($request->session()->get('email')) {
            $request->session()->keep(['email', 'remember']);

            return view('auth/tfa');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request): View|RedirectResponse|SymfonyResponse
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $user = User::where($this->username(), '=', $request->input($this->username()))->first();

        // If the user exists, and has two-factor authentication enabled
        if (config('settings.login_tfa') && $user && $user->tfa) {
            // If the user credentials are valid
            if (auth()->validate($this->credentials($request))) {
                try {
                    $this->userService->update($user, ['reset_tfa_code' => true]);
                    Mail::to($user->email)->locale($user->locale)->send(new TfaMail($user->tfa_code));
                } catch(Exception $e) {
                    return redirect()->route('login')->with('error', $e->getMessage());
                }

                $request->session()->flash($this->username(), $request->input($this->username()));
                $request->session()->flash('remember', $request->boolean('remember'));

                return view('auth/tfa');
            }
        } else {
            if ($this->attemptLogin($request)) {
                if ($request->hasSession()) {
                    $request->session()->put('auth.password_confirmed_at', time());
                }

                return $this->sendLoginResponse($request);
            }
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * The user has been authenticated.
     */
    public function authenticated(Request $request, User $user): void
    {
        $this->userService->update($user, ['update_authed_at' => true]);
    }

    /**
     * Validate the two-factor authentication code.
     */
    public function validateTfaCode(Request $request): RedirectResponse
    {
        $request->session()->keep(['email', 'remember']);

        $user = User::where($this->username(), '=', $request->session()->get('email'))->first();

        // If the user exists, and has two-factor authentication enabled
        if (config('settings.login_tfa') && $user && $user->tfa) {
            $request->validate([
                'code' => ['required', 'integer',
                    function ($attribute, $value, $fail) use ($user) {
                        if ($value != $user->tfa_code) {
                            $fail(__("The security code is incorrect."));
                        }
                    },
                    function ($attribute, $value, $fail) use ($user) {
                        if ($user->tfa_code_created_at->lt(Carbon::now()->subMinutes(30))) {
                            $fail(__("The security code is expired."));
                        }
                    }
                ]
            ]);

            try {
                auth()->login($user, $request->session()->get('remember'));

                if ($request->hasSession()) {
                    $request->session()->put('auth.password_confirmed_at', time());
                }

                $this->userService->update($user, ['reset_tfa_code' => true]);

                $request->session()->forget(['email', 'remember']);

                return $this->sendLoginResponse($request);
            } catch (Exception $e) {
                return redirect()->route('login')->with('error', $e->getMessage());
            }
        }

        return redirect()->route('login');
    }

    /**
     * Resend the two-factor authentication code to the user.
     */
    public function resendTfaCode(Request $request): RedirectResponse
    {
        $request->session()->keep(['email', 'remember']);

        $user = User::where($this->username(), '=', $request->session()->get('email'))->first();

        // If the user exists, and has two-factor authentication enabled
        if (config('settings.login_tfa') && $user && $user->tfa) {
            try {
                $this->userService->update($user, ['reset_tfa_code' => true]);
                Mail::to($user->email)->locale($user->locale)->send(new TfaMail($user->tfa_code));
            } catch(Exception $e) {
                return redirect()->route('login')->with('error', $e->getMessage());
            }

            return back()->with('success', __('A new security code has been sent to your email address.'));
        }

        return redirect()->route('login');
    }

    /**
     * Process the Google callback response.
     */
    public function google(): RedirectResponse
    {
        // If the driver is not enabled
        if (!config('settings.auth_google')) {
            abort(404);
        }

        return $this->handleSocialAuth('google');
    }

    /**
     * Process the microsoft callback response.
     */
    public function microsoft(): RedirectResponse
    {
        // If the driver is not enabled
        if (!config('settings.auth_microsoft')) {
            abort(404);
        }

        return $this->handleSocialAuth('azure');
    }

    /**
     * Process the Apple callback response.
     */
    public function apple(): RedirectResponse
    {
        // If the driver is not enabled
        if (!config('settings.auth_apple')) {
            abort(404);
        }

        return $this->handleSocialAuth('apple');
    }

    /**
     * Handle the social authentication process.
     */
    private function handleSocialAuth(string $driver): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($driver)->stateless()->user();

            $user = User::where('email', $socialUser->email)->first();

            if ($user) {
                Auth::login($user);

                $this->userService->update($user, ['update_authed_at' => true]);

                return redirect()->route('dashboard');
            } else {
                try {
                    $user = $this->userService->store([
                        'email' => $socialUser->email,
                        'name' => mb_substr($socialUser->name, 0, 190),
                        'mark_email_as_verified' => true
                    ]);

                    Auth::login($user);

                    $this->userService->update($user, ['update_authed_at' => true]);

                    return redirect()->route('dashboard');
                } catch (Exception $e) {
                    return redirect()->route('login')->with('error', $e->getMessage());
                }
            }
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }
}
