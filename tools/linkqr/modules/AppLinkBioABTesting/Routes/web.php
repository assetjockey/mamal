<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLinkBioABTesting\Livewire\LinkBioAbTests;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applinkbio.portal_route_prefix', 'portal/link-bio'))
    ->name('portal.link-bio.')
    ->group(function (): void {
        Route::get('/ab-testing', LinkBioAbTests::class)->name('ab-testing');
    });
