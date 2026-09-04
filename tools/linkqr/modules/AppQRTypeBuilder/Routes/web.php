<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRTypeBuilder\Livewire\TypeContentBuilder;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/{qrCode}/type-content', TypeContentBuilder::class)->name('type-content');
    });
