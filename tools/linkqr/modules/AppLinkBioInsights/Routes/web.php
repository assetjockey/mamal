<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLinkBioInsights\Livewire\InsightsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applinkbio.portal_route_prefix', 'portal/link-bio'))
    ->name('portal.link-bio.')
    ->group(function (): void {
        Route::get('/insights', InsightsIndex::class)->name('insights');
    });
