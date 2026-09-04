<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Installed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the app has been installed
        // This prevents doing any SQL queries to the database, before the app has been setup
        if (file_exists(storage_path('installed'))) {
            return $next($request);
        }

        return redirect()->route('install');
    }
}
