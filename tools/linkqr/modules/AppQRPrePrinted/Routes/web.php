<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRPrePrinted\Http\Controllers\PrePrintedExportController;
use Modules\AppQRPrePrinted\Livewire\PrePrintedIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/pre-printed', PrePrintedIndex::class)->name('pre-printed');
        Route::get('/pre-printed/export', PrePrintedExportController::class)->name('pre-printed.export');
    });
