<?php

use Illuminate\Support\Facades\Route;
use Modules\AppTrackingPixels\Livewire\TrackingPixelsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/brand')
    ->name('portal.brand.')
    ->group(function (): void {
        Route::get('/tracking-pixels', TrackingPixelsIndex::class)->name('tracking-pixels');
    });
