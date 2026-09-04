<?php

use Illuminate\Support\Facades\Route;
use Modules\AppShortLinkApi\Http\Controllers\ShortLinkApiController;
use Modules\AppShortLinkApi\Livewire\ShortLinkApiSettings;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/short-links')
    ->name('portal.short-links.')
    ->group(function (): void {
        Route::livewire('/api', ShortLinkApiSettings::class)->name('api');
    });

Route::middleware(['api'])
    ->prefix('api/short-links')
    ->name('api.short-links.')
    ->controller(ShortLinkApiController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
    });
