<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the hosting reverse proxy / load balancer so Laravel reads the
        // original scheme from `X-Forwarded-Proto`. Without this, requests
        // behind an HTTPS-terminating proxy look like plain HTTP to the app,
        // so generated URLs (and Livewire redirects) come out as `http://`.
        // The host then 301/302s them to `https://`, which downgrades the
        // Livewire `POST /livewire-*/update` to a GET and yields a spurious
        // 405 Method Not Allowed mid-generation.
        //
        // `at: '*'` trusts any upstream proxy — fine for typical single-proxy
        // shared hosting. If you front the app with a known proxy IP/CIDR,
        // pin it here instead for tighter security.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_AWS_ELB);

        // Exclude payment webhook endpoints from CSRF — gateways post raw
        // payloads with no token, and each handler verifies its own
        // signature instead.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        $middleware->alias([
            'PreventBackHistory'      => \App\Http\Middleware\PreventBackHistory::class,
            'role'                    => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'              => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'install'                 => \App\Http\Middleware\Install::class,
            'installed'               => \App\Http\Middleware\Installed::class,
            'frontend.page'           => \App\Http\Middleware\FrontendPageEnabled::class,
            'frontend.customUrl'      => \App\Http\Middleware\FrontendCustomUrl::class,
            'localize'                => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
            'localizationRedirect'    => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect'   => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeCookieRedirect'    => \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            'localeViewPath'          => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
