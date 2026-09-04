<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRAnalytics\Livewire\AnalyticsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/analytics', AnalyticsIndex::class)->name('analytics');
        Route::get('/{qrCode}/analytics', AnalyticsIndex::class)->name('campaign.analytics');
    });
