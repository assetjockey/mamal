<?php

use Illuminate\Support\Facades\Route;
Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applinkbio.portal_route_prefix', 'portal/link-bio'))
    ->name('portal.link-bio.')
    ->group(function (): void {
        Route::redirect('/qr-codes', '/portal/qr-codes/create', 301)->name('qr-codes');
        Route::redirect('/share-kit', '/portal/qr-codes/create', 301)->name('share-kit');
    });
