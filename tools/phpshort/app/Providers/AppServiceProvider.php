<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Link;
use App\Models\Payment;
use App\Observers\DomainObserver;
use App\Observers\LinkObserver;
use App\Observers\PaymentObserver;
use App\Observers\PixelObserver;
use App\Observers\SpaceObserver;
use App\Observers\UserObserver;
use App\Models\Pixel;
use App\Models\Space;
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
        Space::observe(SpaceObserver::class);
        Link::observe(LinkObserver::class);
        Domain::observe(DomainObserver::class);
        Pixel::observe(PixelObserver::class);
        Payment::observe(PaymentObserver::class);

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', SocialiteProvidersAppleProvider::class);
            $event->extendSocialite('azure', SocialiteProvidersAzureProvider::class);
        });
    }
}
