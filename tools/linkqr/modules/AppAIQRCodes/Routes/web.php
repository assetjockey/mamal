<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIQRCodes\Livewire\AIQRCodeIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::livewire('/ai/create', AIQRCodeIndex::class)->name('ai.create');
        Route::livewire('/ai', AIQRCodeIndex::class)->name('ai');
    });
