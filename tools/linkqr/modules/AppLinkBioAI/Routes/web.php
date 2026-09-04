<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLinkBioAI\Livewire\AIAssistantIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applinkbio.portal_route_prefix', 'portal/link-bio'))
    ->name('portal.link-bio.')
    ->group(function (): void {
        Route::get('/ai-assistant', AIAssistantIndex::class)->name('ai-assistant');
    });
