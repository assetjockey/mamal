<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseIsConfigured
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ((config('settings.license_key') === null || config('settings.license_type') === null) && $request->url() != route('admin.settings', 'license')) {
            return redirect()->route('admin.settings', 'license');
        }

        return $next($request);
    }
}
