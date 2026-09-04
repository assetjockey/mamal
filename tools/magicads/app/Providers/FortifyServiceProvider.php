<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use App\Http\Responses\VerifyEmailResponse;
use App\Services\HelperService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(VerifyEmailResponseContract::class, VerifyEmailResponse::class);

        // Stop Fortify from registering its own (unlocalized) routes. We
        // re-register them ourselves inside the LaravelLocalization group in
        // routes/web.php so the auth pages inherit the active locale prefix and
        // run the locale middleware — otherwise a visitor who switched to, say,
        // Turkish on the frontend would drop back to English the moment they
        // opened login/register (the routes lived outside the locale group, so
        // App::setLocale() never fired and route('login') produced an
        // unprefixed URL). See routes/web.php "AUTHENTICATION ROUTES".
        //
        // register() (not boot()) is deliberate: Fortify's package provider
        // registers its routes in its own boot(), and every provider's
        // register() runs before any boot(), so flipping this flag here
        // guarantees it is honored regardless of provider order.
        Fortify::ignoreRoutes();

        $this->syncRegistrationFeature();
    }

    /**
     * Reflect the admin-managed "user registration" setting into Fortify's
     * feature list.
     *
     * The admin toggle (Admin → Backend → Registration) persists a boolean to
     * `general_settings.user_registration`, but Fortify only ever consults the
     * static `config/fortify.php` feature array. Without this sync, disabling
     * registration in the panel has no effect: the `register` / `register.store`
     * routes stay live and every `Features::enabled(Features::registration())`
     * check in the frontend views keeps reporting "enabled".
     *
     * We run this in register() (not boot()) on purpose: Laravel runs every
     * provider's register() before any boot(), and Fortify's own package
     * provider boots — registering the gated `register` route — *before* this
     * app provider boots. Mutating the config here guarantees the value is in
     * place before Fortify reads it.
     *
     * We deliberately read via the query builder rather than the
     * {@see GeneralSetting} Eloquent model: at register() time Eloquent's
     * connection resolver isn't wired up yet (that happens when the database
     * provider boots), so an Eloquent call here throws "Call to a member
     * function connection() on null". The query builder resolves the
     * connection lazily and works fine.
     *
     * Guarded so fresh installs (DB unreachable / table not migrated yet) boot
     * cleanly with registration left enabled by default.
     */
    private function syncRegistrationFeature(): void
    {
        if (! HelperService::checkDBStatus() || ! Schema::hasTable('general_settings')) {
            return;
        }

        $userRegistration = DB::table('general_settings')->value('user_registration');

        // No settings row yet (mid-install) → leave the default in place.
        if ($userRegistration === null) {
            return;
        }

        $features = config('fortify.features', []);
        $registration = Features::registration();
        $enabled = (bool) $userRegistration;

        if ($enabled && ! in_array($registration, $features, true)) {
            $features[] = $registration;
        } elseif (! $enabled) {
            $features = array_values(array_filter(
                $features,
                fn ($feature) => $feature !== $registration
            ));
        }

        config(['fortify.features' => $features]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::registerView(fn () => view('livewire.auth.register'));
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
