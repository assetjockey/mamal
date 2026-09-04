<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminShortLinks\Livewire\AdminShortLinksIndex;
use Modules\AdminShortLinks\Livewire\AdminShortLinksManage;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin/short-links')
    ->name('admin.short-links.')
    ->group(function (): void {
        Route::livewire('/', AdminShortLinksIndex::class)->name('index');
        Route::livewire('/manage', AdminShortLinksManage::class)->name('manage');
    });
