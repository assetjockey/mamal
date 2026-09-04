<?php

namespace App\Http\Middleware;

use App\Models\FrontendSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the public landing page behind the "Frontend Page" toggle.
 *
 * When the site owner disables the frontend page in Frontend Settings, the
 * marketing landing page is no longer publicly available: guests are sent to
 * the login screen and authenticated users to their dashboard. Degrades
 * safely on fresh installs (no DB / un-migrated table) by allowing access.
 */
class FrontendPageEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('frontend_settings')) {
            return $next($request);
        }

        $settings = FrontendSetting::query()->first();

        // Default-on: only block when a row exists and the flag is explicitly off.
        if ($settings && ! $settings->frontend_page) {
            if (Auth::check()) {
                return redirect()->route('user.dashboard');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
