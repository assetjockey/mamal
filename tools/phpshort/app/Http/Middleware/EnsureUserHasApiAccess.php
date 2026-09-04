<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasApiAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ($request->user()->cannot('api', [User::class])) {
            return response()->json([
                'message' => __('You don\'t have access to this feature.'),
                'status' => 403
            ], 403);
        }

        return $next($request);
    }
}
