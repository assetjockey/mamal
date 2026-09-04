<?php

use Illuminate\Support\Facades\Route;
use Modules\AppShortLinkBulk\Livewire\ShortLinkBulkImport;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/short-links')
    ->name('portal.short-links.')
    ->group(function (): void {
        Route::livewire('/bulk', ShortLinkBulkImport::class)->name('bulk');
    });
