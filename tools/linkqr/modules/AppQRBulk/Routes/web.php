<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRBulk\Livewire\BulkIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/bulk', BulkIndex::class)->name('bulk');
    });
