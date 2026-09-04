<?php

use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\IncidentController;
use App\Http\Controllers\API\MonitorController;
use App\Http\Controllers\API\StatController;
use App\Http\Controllers\API\StatusPageController;
use App\Http\Middleware\EnsureUserHasApiAccess;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([Authenticate::class .':api', ThrottleRequests::class . ':120'])->group(function () {
    Route::apiResource('monitors', MonitorController::class, [
        'parameters' => [
            'monitors' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('status-pages', StatusPageController::class, [
        'parameters' => [
            'status-pages' => 'id'
        ],
        'as' => 'api'
    ])->middleware(EnsureUserHasApiAccess::class);

    Route::apiResource('incidents', IncidentController::class, [
        'parameters' => [
            'incidents' => 'id'
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
