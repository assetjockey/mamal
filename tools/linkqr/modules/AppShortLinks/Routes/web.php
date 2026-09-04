<?php

use Illuminate\Support\Facades\Route;
use Modules\AppShortLinks\Livewire\ShortLinkCreate;
use Modules\AppShortLinks\Livewire\ShortLinkEdit;
use Modules\AppShortLinks\Http\Controllers\ShortLinkRedirectController;
use Modules\AppShortLinks\Livewire\ShortLinksIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/short-links')
    ->name('portal.short-links.')
    ->group(function (): void {
        Route::livewire('/', ShortLinksIndex::class)->name('index');
        Route::livewire('/create', ShortLinkCreate::class)->name('create');
        Route::livewire('/{shortLink}/edit', ShortLinkEdit::class)->name('edit');
    });

Route::middleware(['web'])
    ->controller(ShortLinkRedirectController::class)
    ->group(function (): void {
        Route::get('/s/{code}', 'show')
            ->where('code', '[A-Za-z0-9_-]+')
            ->name('short-links.public.redirect');
        Route::post('/s/{code}', 'unlock')
            ->where('code', '[A-Za-z0-9_-]+')
            ->name('short-links.public.unlock');
        Route::get('/s/{code}/report', 'report')
            ->where('code', '[A-Za-z0-9_-]+')
            ->name('short-links.public.report');
        Route::post('/s/{code}/report', 'storeReport')
            ->where('code', '[A-Za-z0-9_-]+')
            ->name('short-links.public.report.store');
    });
