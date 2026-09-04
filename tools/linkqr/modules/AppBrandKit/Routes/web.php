<?php

use Illuminate\Support\Facades\Route;
use Modules\AppBrandKit\Livewire\BrandKitIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/brand')
    ->name('portal.brand.')
    ->group(function (): void {
        Route::get('/kit', BrandKitIndex::class)->name('kit');
    });
