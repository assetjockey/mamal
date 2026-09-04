<?php

namespace App\Http\Middleware;

use App\Models\FrontendSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects public frontend pages to an admin-configured external URL.
 *
 * When the site owner enables "Custom Landing Page URL" in Frontend Settings,
 * every public marketing page (home, contact, privacy, terms) bounces to the
 * configured destination. Guarded so it never loops back onto the same host
 * and degrades safely on fresh installs (no DB / un-migrated table).
 */
class FrontendCustomUrl
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

        if (! $settings || ! $settings->custom_url_enabled || blank($settings->custom_url)) {
            return $next($request);
        }

        $target = trim((string) $settings->custom_url);

        // Never redirect to ourselves — that would loop forever.
        $targetHost = parse_url($target, PHP_URL_HOST);
        if ($targetHost && strcasecmp($targetHost, $request->getHost()) === 0) {
            return $next($request);
        }

        return redirect()->away($target, 302);
    }
}
