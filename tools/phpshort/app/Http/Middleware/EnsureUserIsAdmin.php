<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * The authentication guard instance.
     */
    protected Guard $auth;

    /**
     * Create a new middleware instance.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($this->auth->guest() || !$this->auth->user()->isAdmin()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
