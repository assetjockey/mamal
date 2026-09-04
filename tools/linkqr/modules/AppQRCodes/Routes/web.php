<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRCodes\Http\Controllers\PublicQrCodeController;
use Modules\AppQRCodes\Livewire\QrCodeBuilder;
use Modules\AppQRCodes\Livewire\QrCodeCreate;
use Modules\AppQRCodes\Livewire\QrCodeWorkspace;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/', QrCodeWorkspace::class)->name('index');
        Route::get('/create', QrCodeCreate::class)->name('create');
        Route::get('/{qrCode}/edit', QrCodeBuilder::class)->name('edit');
    });

Route::middleware(['web'])
    ->get('/q/{code}', PublicQrCodeController::class)
    ->name('qr-codes.public.show');
