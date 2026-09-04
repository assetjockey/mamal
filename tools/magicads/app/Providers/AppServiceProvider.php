<?php

namespace App\Providers;

use App\Events\CreativeStored;
use App\Events\OrderCompleted;
use App\Listeners\AwardReferralCommission;
use App\Listeners\OffloadCreativeToCloud;
use App\Listeners\GenerateInvoiceForOrder;
use App\Listeners\NotifyAdminOnPayment;
use App\Listeners\UpdateLastSeen;
use App\Models\BlogPost;
use App\Models\CookieSetting;
use App\Models\EmailSetting;
use App\Models\Faq;
use App\Models\FrontendSetting;
use App\Models\GeneralSetting;
use App\Models\GoogleAdsense;
use App\Models\Plan;
use App\Models\SeoSetting;
use App\Models\SocialMediaSetting;
use App\Models\Testimonial;
use App\Services\AdCopy\CopyGenerator;
use App\Services\AdCopy\PromptAssembler;
use App\Services\AiStudio\AdGenerator;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use App\Services\AiStudio\CreditService;
use App\Services\AiStudio\ProviderManager;
use App\Services\HelperService;
use Carbon\CarbonImmutable;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CreditServiceInterface::class, CreditService::class);
        $this->app->singleton(ProviderManager::class);
        $this->app->singleton(AdGenerator::class);
        $this->app->singleton(PromptAssembler::class);
        $this->app->singleton(CopyGenerator::class);
        $this->app->singleton(\App\Services\Storage\StorageManager::class);

        // The JSON locations manager translation editor is reached in every
        // environment through our own `/admin/languages/*` admin routes (see
        // routes/web.php), which point at the vendor `HomeController`. That
        // controller needs the `langs::` view namespace and the
        // `elseyyid:location:*` Artisan commands to function.
        //
        // We deliberately do NOT register the full vendor service provider in
        // production: its boot() also (a) registers the package's own
        // `/translations/*` routes and (b) forcibly overwrites
        // `database.connections.mysql` with the package's bundled config,
        // clobbering our connection settings. So locally/testing we register
        // the whole provider for convenience, while in production we wire up
        // only the view namespace and commands without those side effects.
        if ($this->app->environment(['local', 'testing'])) {
            $this->app->register(\Elseyyid\LaravelJsonLocationsManager\Providers\LaravelJsonLocationsManagerServiceProvider::class);
        } else {
            $this->commands([
                \Elseyyid\LaravelJsonLocationsManager\Commands\InstallCommand::class,
                \Elseyyid\LaravelJsonLocationsManager\Commands\PublishAllCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGoogleServices();
        $this->configureMailFromSettings();
        $this->activateTheme();
        $this->registerLivewireComponentNamespaces();
        $this->registerFrontendViewComposers();
        $this->registerAuthViewComposers();
        $this->registerLanguageManagerViewComposer();
        Event::listen(Login::class, UpdateLastSeen::class);
        Event::listen(OrderCompleted::class, NotifyAdminOnPayment::class);
        Event::listen(OrderCompleted::class, GenerateInvoiceForOrder::class);
        Event::listen(OrderCompleted::class, AwardReferralCommission::class);

        // Studio results are mirrored to the admin's chosen cloud storage by a
        // single core listener. Storage plugins register their provider with
        // the StorageManager (guarded by class_exists so core never depends on
        // a plugin); the listener then uploads to whichever provider is set as
        // the Default Storage under General Settings.
        $this->registerStorageProviders();
        Event::listen(CreativeStored::class, OffloadCreativeToCloud::class);

        // Watch plan edits so the next checkout re-syncs the gateway plan
        // without requiring the admin UI to make a network call.
        Plan::observe(\App\Observers\PlanObserver::class);

        // Prompt Marketplace plugin — keep the storefront in sync when a
        // creative is deleted. Guarded by class_exists so core keeps working
        // when the plugin is uninstalled (its observer file is removed).
        if (class_exists(\App\Observers\AdCreativeObserver::class)) {
            \App\Models\AdCreative::observe(\App\Observers\AdCreativeObserver::class);
        }
    }

    /**
     * Register installed storage plugins with the StorageManager.
     *
     * Each storage plugin ships a provider service implementing
     * {@see \App\Contracts\StorageProvider}. We reference them by class-string
     * and guard with class_exists so core keeps working when a plugin is
     * uninstalled (its service file is removed and the registration is skipped).
     * Registration is cheap — it does NOT touch the database — so it's safe to
     * run on every boot, including during console/migrate commands.
     */
    protected function registerStorageProviders(): void
    {
        $manager = $this->app->make(\App\Services\Storage\StorageManager::class);

        $providers = [
            \App\Services\AmazonS3\AmazonS3Service::class,
            \App\Services\Wasabi\WasabiService::class,
            \App\Services\CloudflareR2\CloudflareR2Service::class,
            \App\Services\GoogleCloudStorage\GoogleCloudStorageService::class,
        ];

        foreach ($providers as $providerClass) {
            if (class_exists($providerClass)) {
                $manager->register($this->app->make($providerClass));
            }
        }
    }

    /**
     * Activate the theme the site owner configured in GeneralSetting.
     *
     * Frontend and dashboard themes can differ; dashboard routes live under
     * the `app` URL prefix (or a locale-prefixed variant). We fall back to
     * the `default` theme whenever the database is unreachable, the settings
     * table hasn't been migrated yet (fresh install / console tasks), or the
     * stored values are empty.
     */
    protected function activateTheme(): void
    {
        // In console context avoid touching the DB during install/migrate commands.
        if (! HelperService::checkDBStatus() || ! Schema::hasTable('general_settings')) {
            Theme::set('default');

            return;
        }

        $settings = GeneralSetting::query()->first();

        if (! $settings) {
            Theme::set('default');

            return;
        }

        // Heal missing values so the UI always has a theme to render.
        if (blank($settings->frontend_theme)) {
            $settings->frontend_theme = 'default';
        }
        if (blank($settings->dashboard_theme)) {
            $settings->dashboard_theme = 'default';
        }
        if ($settings->isDirty()) {
            $settings->save();
        }

        $frontend = $settings->frontend_theme;
        $dashboard = $settings->dashboard_theme;

        if ($frontend === $dashboard) {
            Theme::set($frontend);

            return;
        }

        $request = request();
        $isDashboard = $request
            && ($request->is('app*') || $request->is('*/app*'));

        Theme::set($isDashboard ? $dashboard : $frontend);
    }

    /**
     * Register Livewire `component_namespaces` (e.g. `layouts::`, `pages::`) against
     * the active theme's view paths.
     *
     * Livewire's own ServiceProvider only registers these namespaces when the
     * configured location is a real directory. With the igaster/laravel-theme
     * package, those folders live inside each theme (e.g. `resources/views/default/layouts`)
     * rather than at `resources/views/layouts`, so the registration is silently
     * skipped and any page using `component_layout = 'layouts::app'` fails with
     * "No hint path defined for [layouts]". We resolve the paths against the
     * currently active theme (falling back to all registered Laravel view paths)
     * and wire them into Blade and the view factory here.
     */
    protected function registerLivewireComponentNamespaces(): void
    {
        foreach ((array) config('livewire.component_namespaces', []) as $namespace => $relativeConfigured) {
            $locations = $this->resolveThemedNamespaceLocations($relativeConfigured);

            if ($locations === []) {
                continue;
            }

            foreach ($locations as $location) {
                Blade::anonymousComponentPath($location, $namespace);
            }

            View::addNamespace($namespace, $locations);
        }
    }

    /**
     * Share the frontend settings records with every view that extends
     * `layouts.frontend`.
     *
     * The frontend layout (and its partials like the header, footer, cookie
     * banner, and SEO meta block) expects these four records to always be
     * resolvable, even on a fresh install where the tables may not exist yet
     * or when the database is unreachable. In those cases we expose `null`
     * values so downstream Blade can fall back to sane defaults.
     */
    protected function registerFrontendViewComposers(): void
    {
        $frontendSettingsComposer = function ($view) {
            // Settings used by the frontend layout AND its child partials
            // (header, menu, footer, ad slots, SEO meta, etc.). Registered
            // for multiple view targets because Blade evaluates @section
            // bodies (which @include partials like the footer) BEFORE the
            // parent layout is composed — so a composer scoped only to
            // `layouts.frontend` fires too late to reach the footer.
            if (! HelperService::checkDBStatus()) {
                $shared = [
                    'generalSettings' => null,
                    'seoSettings' => null,
                    'cookieSettings' => null,
                    'socialMedia' => null,
                    'googleAdsense' => null,
                    'frontendSettings' => null,
                ];
            } else {
                $shared = [
                    'generalSettings' => Schema::hasTable('general_settings')
                        ? GeneralSetting::query()->first()
                        : null,
                    'seoSettings' => Schema::hasTable('seo_settings')
                        ? SeoSetting::query()->first()
                        : null,
                    'cookieSettings' => Schema::hasTable('cookie_settings')
                        ? CookieSetting::query()->first()
                        : null,
                    'socialMedia' => Schema::hasTable('social_media_settings')
                        ? SocialMediaSetting::query()->first()
                        : null,
                    'googleAdsense' => Schema::hasTable('google_adsense')
                        ? GoogleAdsense::query()->first()
                        : null,
                    'frontendSettings' => Schema::hasTable('frontend_settings')
                        ? FrontendSetting::query()->first()
                        : null,
                ];
            }

            $view->with($shared);

            // Also share globally so any other partial included from inside
            // a child view's @section body inherits these values without
            // each call site needing its own composer registration.
            foreach ($shared as $key => $value) {
                View::share($key, $value);
            }
        };

        // Attach the composer to the layout itself plus the top-level page
        // views and frontend partial namespace, so the share happens before
        // any frontend partial renders, regardless of compile order.
        View::composer([
            'layouts.frontend',
            'welcome',
            'contact',
            'privacy',
            'terms',
            'pages.blog.index',
            'pages.blog.show',
            'frontend.*',
        ], $frontendSettingsComposer);

        // The welcome view is the marketing landing page. It needs the list of
        // active plans for its pricing section, but must still render cleanly
        // on fresh installs where the database is unreachable or the `plans`
        // table hasn't been migrated yet. Guard the lookup and fall back to an
        // empty collection so the Blade template can always iterate safely.
        View::composer('welcome', function ($view) {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('plans')) {
                $view->with('plans', collect());

                return;
            }

            $view->with(
                'plans',
                Plan::query()
                    ->where('status', 'active')
                    ->orderBy('price')
                    ->get()
            );
        });

        // Latest published blog posts for the homepage carousel section.
        // Same defensive pattern: empty collection if the table hasn't been
        // migrated yet so the carousel partial can hide itself gracefully.
        View::composer('welcome', function ($view) {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('blog_posts')) {
                $view->with('latestBlogPosts', collect());

                return;
            }

            $view->with(
                'latestBlogPosts',
                BlogPost::query()
                    ->published()
                    ->orderByDesc('is_featured')
                    ->orderByDesc('published_at')
                    ->take(8)
                    ->get()
            );
        });

        // Active FAQs for the homepage FAQ section. Falls back to an empty
        // collection on fresh installs (no DB / un-migrated table) so the
        // section partial can render its own default copy gracefully.
        View::composer('welcome', function ($view) {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('faqs')) {
                $view->with('faqs', collect());

                return;
            }

            $view->with(
                'faqs',
                Faq::query()
                    ->active()
                    ->orderBy('id')
                    ->get()
            );
        });

        // Active testimonials for the homepage testimonials section. Falls
        // back to an empty collection on fresh installs (no DB / un-migrated
        // table) so the section partial can render its own default copy.
        View::composer('welcome', function ($view) {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('testimonials')) {
                $view->with('testimonials', collect());

                return;
            }

            $view->with(
                'testimonials',
                Testimonial::query()
                    ->active()
                    ->orderByDesc('featured')
                    ->orderBy('order')
                    ->orderBy('id')
                    ->get()
            );
        });
    }

    /**
     * Share the list of enabled social (oAuth) login providers with the auth
     * layout so the login and registration screens can render the matching
     * sign-in buttons.
     *
     * Resolved through {@see \App\Services\SocialAuthService}, which reads the
     * admin-managed `social_media_settings` row. Guarded so fresh installs
     * (no DB / un-migrated table) fall back to an empty list rather than
     * throwing while the auth pages still need to render.
     */
    protected function registerAuthViewComposers(): void
    {
        View::composer(['livewire.auth.login', 'livewire.auth.register'], function ($view) {
            $providers = [];

            if (HelperService::checkDBStatus() && Schema::hasTable('social_media_settings')) {
                $service = app(\App\Services\SocialAuthService::class);

                foreach ($service->enabledProviders() as $provider) {
                    $providers[$provider] = $service->label($provider);
                }
            }

            $view->with('socialProviders', $providers);
        });
    }

    /**
     * Share a `$settings` variable with the published `langs::*` views from
     * the elseyyid/laravel-json-mysql-locations-manager package.
     *
     * The vendor's stock `HomeController::index` only sends `$langs` to the
     * view, but our customized blades in `resources/views/vendor/langs/`
     * additionally read `$settings->languages` (the comma-separated list of
     * enabled locales) and `$settings->default_language` (the current default
     * locale, used to mark the active option in the dropdown). We attach an
     * Eloquent `GeneralSetting` record limited to just those two columns so
     * the views can rely on Eloquent attribute access without our composer
     * hydrating the rest of the row.
     *
     * On a fresh install where the database is unreachable or the
     * `general_settings` table hasn't been migrated yet we fall back to
     * `null` so Blade can still render via the existing `@isset`/null
     * checks rather than blowing up.
     */
    protected function registerLanguageManagerViewComposer(): void
    {
        // In local/testing the vendor service provider already registers the
        // `langs` view namespace. In production we register it here (without
        // booting the full provider) so our `/admin/languages/*` routes can
        // render the package's blades. This mirrors the provider's own
        // loadViewsFrom() call, including auto-detection of published overrides
        // in resources/views/vendor/langs.
        if (! $this->app->environment(['local', 'testing'])) {
            $this->loadViewsFrom(
                base_path('vendor/elseyyid/laravel-json-mysql-locations-manager/src/views'),
                'langs'
            );
        }

        View::composer('langs::*', function ($view) {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('general_settings')) {
                $view->with('settings', null);

                return;
            }

            $view->with(
                'settings',
                GeneralSetting::query()->select(['languages', 'default_language'])->first()
            );
        });
    }

    /**
     * Resolve the list of existing directories for a Livewire component namespace,
     * honoring the active theme hierarchy.
     */
    protected function resolveThemedNamespaceLocations(string $configuredPath): array
    {
        $viewsRoot = resource_path('views');
        $relative = ltrim(str_replace('\\', '/', substr($configuredPath, strlen($viewsRoot))), '/');

        // If the path doesn't sit under resources/views, fall back to the literal path.
        if ($relative === '' || str_starts_with($relative, '..')) {
            return is_dir($configuredPath) ? [$configuredPath] : [];
        }

        $candidates = [];

        foreach ((array) config('view.paths', []) as $viewPath) {
            $candidates[] = rtrim($viewPath, '/\\').DIRECTORY_SEPARATOR.$relative;
        }

        // Always include the literal configured path as a last-chance fallback.
        $candidates[] = $configuredPath;

        $resolved = [];
        foreach ($candidates as $candidate) {
            $normalized = rtrim($candidate, '/\\');
            if (is_dir($normalized) && ! in_array($normalized, $resolved, true)) {
                $resolved[] = $normalized;
            }
        }

        return $resolved;
    }

    /**
     * Push the admin-managed Google service keys (stored in `admin_keys`) into
     * runtime config so the GA4 package and the maps GeoChart can read them.
     *
     * The `akki-io/laravel-google-analytics` package reads
     * `config('laravel-google-analytics.property_id')` and
     * `...service_account_credentials_json`, which by default come from env.
     * Since this app stores them in the database instead, we hydrate the
     * config here on every boot. The credentials column holds just the file
     * name; the JSON itself lives in `storage/app/analytics/`.
     *
     * Guarded so fresh installs (no DB / un-migrated table) boot cleanly.
     */
    protected function configureGoogleServices(): void
    {
        if (! HelperService::checkDBStatus() || ! Schema::hasTable('admin_keys')) {
            return;
        }

        $keys = \App\Models\AdminKey::query()->first();

        if (! $keys) {
            return;
        }

        if (! empty($keys->google_analytics_property_id)) {
            config(['laravel-google-analytics.property_id' => $keys->google_analytics_property_id]);
        }

        if (! empty($keys->google_analytics_service_credentials)) {
            $credentialsPath = storage_path('app/analytics/'.$keys->google_analytics_service_credentials);

            if (is_file($credentialsPath)) {
                config(['laravel-google-analytics.service_account_credentials_json' => $credentialsPath]);
            }
        }

        if (! empty($keys->google_analytics_tracking_id)) {
            config(['services.google.analytics.tracking_id' => $keys->google_analytics_tracking_id]);
        }

        if (! empty($keys->google_maps_api_key)) {
            config(['services.google.maps.key' => $keys->google_maps_api_key]);
        }
    }

    /**
     * Push the admin-managed SMTP credentials (stored in `email_settings`) into
     * the runtime mail config so every outgoing message — email verification,
     * password resets, admin notifications, etc. — uses the values configured
     * in Admin → Backend → SMTP rather than the static `.env`/`config/mail.php`
     * defaults.
     *
     * The DB row is authoritative: when a configured row exists it always wins
     * over the env values. Mail sending normally resolves config lazily
     * (MailManager reads `config('mail.*')` only when a mailer is first
     * resolved), so setting these on every boot is enough — there's no separate
     * "real send" path that bypasses it the way the admin "test" button used to
     * build its own transient config.
     *
     * Mapping note: the panel stores `encryption` as `tls`/`ssl`, but Laravel
     * 12's SMTP transport keys off `scheme` (`smtp`/`smtps`), not `encryption`.
     * We translate `ssl` → `smtps` (implicit TLS, usually port 465) and treat
     * everything else as `smtp` (STARTTLS, usually port 587).
     *
     * Guarded so fresh installs (DB unreachable / table not migrated yet) and a
     * blank/half-filled settings row fall back to the env config cleanly.
     */
    protected function configureMailFromSettings(): void
    {
        if (! HelperService::checkDBStatus() || ! Schema::hasTable('email_settings')) {
            return;
        }

        $settings = EmailSetting::query()->first();

        // No usable row yet → leave the env-based config in place.
        if (! $settings || blank($settings->host)) {
            return;
        }

        $scheme = strtolower((string) $settings->encryption) === 'ssl' ? 'smtps' : 'smtp';

        // Trim defensively: a leading/trailing space in the stored host (easily
        // pasted into the admin panel) resolves to an unknown host and breaks
        // every outgoing message. Normalize here so an older row saved before
        // the panel started trimming can't poison real sends.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => trim((string) $settings->host),
            'mail.mailers.smtp.port' => (int) trim((string) $settings->port),
            'mail.mailers.smtp.username' => trim((string) $settings->username),
            'mail.mailers.smtp.password' => trim((string) $settings->password),
        ]);

        // Only override the "from" identity when the panel actually supplied
        // one, so a partially-filled row doesn't blank out the env default.
        if (filled($settings->from_address)) {
            config(['mail.from.address' => trim((string) $settings->from_address)]);
        }

        if (filled($settings->from_name)) {
            config(['mail.from.name' => trim((string) $settings->from_name)]);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // When the site is served over HTTPS (the configured APP_URL uses the
        // https scheme), force every generated URL to https. This is a safety
        // net for hosts that don't forward `X-Forwarded-Proto`: it stops
        // Laravel from emitting `http://` links/redirects that the proxy then
        // 301s to https — the redirect that turns a Livewire `POST .../update`
        // into a GET and produces a 405 mid-generation. Pair it with the
        // trusted-proxy config in bootstrap/app.php and an https APP_URL.
        if (str_starts_with(strtolower((string) config('app.url')), 'https://')) {
            URL::forceScheme('https');
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
