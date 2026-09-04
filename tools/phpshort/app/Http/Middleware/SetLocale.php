<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Auth::check()) {
                if (array_key_exists($request->user()->locale, config('app.locales'))) {
                    app()->setLocale($request->user()->locale);
                } else {
                    app()->setLocale(config('app.locale'));
                }
            } elseif ($request->hasCookie('locale')) {
                if (array_key_exists($request->cookie('locale'), config('app.locales'))) {
                    app()->setLocale($request->cookie('locale'));
                } else {
                    app()->setLocale(config('app.locale'));
                }
            }
            elseif ($request->server('HTTP_ACCEPT_LANGUAGE')) {
                $locale = explode('-', $request->server('HTTP_ACCEPT_LANGUAGE'))[0] ?? null;

                if (array_key_exists($locale, config('app.locales'))) {
                    app()->setLocale($locale);
                } else {
                    app()->setLocale(config('app.locale'));
                }
            } else {
                app()->setLocale(config('app.locale'));
            }
        } catch (Exception) {}

        return $next($request);
    }
}
