<?php

use Illuminate\Support\Facades\Route;
use Modules\AppShortLinkAnalytics\Livewire\ShortLinkAnalyticsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/short-links')
    ->name('portal.short-links.')
    ->group(function (): void {
        Route::livewire('/analytics', ShortLinkAnalyticsIndex::class)->name('analytics');
    });
