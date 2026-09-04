<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\View\View;

class RegisterController extends Controller
{
    use RegistersUsers;

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

        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data): ValidatorContract
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:128', 'confirmed'],
            'agreement' => ['required'],
            captchaFieldName() => config('settings.captcha_driver') ? ['required', 'captcha'] : [],
        ]);
    }

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm(Request $request): View
    {
        // If the request comes from the Home or Pricing page, and the user has selected a plan
        if (($request->server('HTTP_REFERER') == route('pricing') || $request->server('HTTP_REFERER') == route('home').'/') && $request->input('plan') > 1) {
            $request->session()->put('plan_redirect', ['id' => $request->input('plan'), 'interval' => $request->input('interval')]);
        }

        // If the registration is enabled
        if (config('settings.registration')) {
            return view('auth.register');
        }

        abort(404);
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validator($request->all())->validate();

        try {
            event(new Registered($user = $this->userService->store($data + ['mark_email_as_verified' => !config('settings.registration_require_email_verification')])));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }
}
