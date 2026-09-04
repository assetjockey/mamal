<?php

namespace App\Providers;

use App\Models\Incident;
use App\Models\Monitor;
use App\Models\Payment;
use App\Models\StatusPage;
use App\Observers\IncidentObserver;
use App\Observers\MonitorObserver;
use App\Observers\PaymentObserver;
use App\Observers\StatusPageObserver;
use App\Observers\UserObserver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\Provider as SocialiteProvidersAppleProvider;
use SocialiteProviders\Azure\Provider as SocialiteProvidersAzureProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        if (config('settings.force_https')) {
            // If the request comes from a remote source
            if (parse_url(config('app.url'), PHP_URL_HOST) == $request->getHost()) {
                URL::forceScheme('https');
            }
        }

        Paginator::useBootstrap();

        User::observe(UserObserver::class);
        Monitor::observe(MonitorObserver::class);
        StatusPage::observe(StatusPageObserver::class);
        Incident::observe(IncidentObserver::class);
        Payment::observe(PaymentObserver::class);

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', SocialiteProvidersAppleProvider::class);
            $event->extendSocialite('azure', SocialiteProvidersAzureProvider::class);
        });
    }
}
