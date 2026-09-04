<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLinkBio\Http\Controllers\PublicLinkBioController;
use Modules\AppLinkBio\Http\Controllers\PublicShortLinkController;
use Modules\AppLinkBio\Http\Controllers\UsernameAvailabilityController;
use Modules\AppLinkBio\Livewire\LinkBioForm;
use Modules\AppLinkBio\Livewire\LinkBioIndex;
use Modules\AppLinkBio\Livewire\LinkBioSettings;
use Modules\AppLinkBio\Livewire\LinkBioTemplates;

$publicSlugPattern = '^(?!(?:admin|api|app|auth|b|billing|blog|blogs|contact|dashboard|download|faqs|home|l|lang|login|logout|livewire|media|password|payment|portal|pricing|privacy-policy|register|reset-password|s|settings|signup|social-pages|storage|terms|terms-of-use|username|user|users|verify-email|webhook|webhooks)$)[A-Za-z0-9](?:[A-Za-z0-9_-]*[A-Za-z0-9])?$';

Route::middleware(['web'])
    ->get('/username/check', UsernameAvailabilityController::class)
    ->name('guest.username.check');

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applinkbio.portal_route_prefix', 'portal/link-bio'))
    ->name('portal.link-bio.')
    ->group(function (): void {
        Route::get('/', LinkBioIndex::class)->name('index');
        Route::get('/create', LinkBioForm::class)->name('create');
        Route::get('/templates', LinkBioTemplates::class)->name('templates');
        Route::get('/{page}/edit', LinkBioForm::class)->name('edit');
    });

Route::middleware(['web', 'auth'])
    ->prefix('admin/settings')
    ->group(function (): void {
        Route::get('/link-bio', LinkBioSettings::class)->name('settings.link-bio');
    });

Route::middleware(['web'])
    ->prefix('l')
    ->name('link-bio.short.')
    ->group(function (): void {
        Route::get('/{code}', PublicShortLinkController::class)->name('show');
    });

Route::middleware(['web'])
    ->prefix(config('modules.applinkbio.public_route_prefix', 'b'))
    ->name('link-bio.public.')
    ->group(function () use ($publicSlugPattern): void {
        Route::get('/{slug}/click/{block}/{item}', [PublicLinkBioController::class, 'click'])
            ->whereNumber(['block', 'item'])
            ->where('slug', $publicSlugPattern)
            ->name('click');
        Route::get('/{slug}', PublicLinkBioController::class)
            ->where('slug', $publicSlugPattern)
            ->name('show');
    });
