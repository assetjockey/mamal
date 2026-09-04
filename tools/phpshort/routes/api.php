<?php

use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\DomainController;
use App\Http\Controllers\API\LinkController;
use App\Http\Controllers\API\PixelController;
use App\Http\Controllers\API\SpaceController;
use App\Http\Controllers\API\StatController;
use App\Http\Middleware\EnsureUserHasApiAccess;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([Authenticate::class .':api', ThrottleRequests::class . ':120'])->group(function () {
    Route::apiResource('links', LinkController::class, [
        'parameters' => [
            'links' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('domains', DomainController::class, [
        'parameters' => [
            'domains' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('spaces', SpaceController::class, [
        'parameters' => [
            'spaces' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('pixels', PixelController::class, [
        'parameters' => [
            'pixels' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('stats', StatController::class, [
        'parameters' => [
            'stats' => 'id'
        ],
        'only' => [
            'show'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('account', AccountController::class, [
        'only' => [
            'index'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);
});

Route::get('v1/status', function () {
    return response()->json([
        'message' => __('Online.'),
        'status' => 200
    ], 200);
});

Route::fallback(function () {
    return response()->json(['message' => __('Resource not found.'), 'status' => 404], 404);
});
