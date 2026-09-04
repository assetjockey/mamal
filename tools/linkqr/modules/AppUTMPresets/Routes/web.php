<?php

use Illuminate\Support\Facades\Route;
use Modules\AppUTMPresets\Livewire\UtmPresetsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/brand')
    ->name('portal.brand.')
    ->group(function (): void {
        Route::get('/utm-presets', UtmPresetsIndex::class)->name('utm-presets');
    });
