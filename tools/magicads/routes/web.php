<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\InstallController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\UpdateController;
use App\Livewire\Admin\Dashboard\AdminDashboard;
use App\Livewire\Admin\Accounts\AccountsDashboard;
use App\Livewire\Admin\Accounts\AccountsActivity;
use App\Livewire\Admin\Accounts\List\Accounts;
use App\Livewire\Admin\Accounts\List\AccountCreate;
use App\Livewire\Admin\Accounts\List\AccountView;
use App\Livewire\Admin\Accounts\List\AccountEdit;
use App\Livewire\Admin\Support\Tickets;
use App\Livewire\Admin\Support\TicketView;
use App\Livewire\Admin\Plugins\Plugins;
use App\Livewire\Admin\Plugins\PluginCheckout;
use App\Livewire\Admin\Plugins\PluginSuccess;
use App\Livewire\Admin\Plugins\PluginSuccessPackage;
use App\Livewire\Admin\Themes\Themes;
use App\Livewire\Admin\Themes\ThemeCheckout;
use App\Livewire\Admin\Themes\ThemeSuccess;
use App\Livewire\Admin\Ai\AISettings;
use App\Livewire\Admin\General\GeneralSettings;
use App\Livewire\Admin\Backend\GlobalSettings;
use App\Livewire\Admin\Backend\Smtp;
use App\Livewire\Admin\Backend\Registration;
use App\Livewire\Admin\Backend\Auth;
use App\Livewire\Admin\Backend\Gdpr;
use App\Livewire\Admin\Frontend\Settings;
use App\Livewire\Admin\Frontend\Seo;
use App\Livewire\Admin\Frontend\Logos;
use App\Livewire\Admin\Frontend\Faqs;
use App\Livewire\Admin\Frontend\Testimonials;
use App\Livewire\Admin\Frontend\GoogleAdsense;
use App\Livewire\Admin\Frontend\Pages as PagesManager;
use App\Livewire\Admin\Frontend\Blogs\Blogs;
use App\Livewire\Admin\Frontend\Blogs\BlogForm;
use App\Livewire\Admin\Frontend\Blogs\BlogComments;
use App\Livewire\Admin\Notifications\System\SystemNotification;
use App\Livewire\Admin\Update;
use App\Livewire\Admin\Status;
use App\Livewire\Admin\Activation;
use App\Livewire\User\Dashboard\UserDashboard;
use App\Livewire\User\Studio\ImageStudio;
use App\Livewire\User\Studio\VideoStudio;
use App\Livewire\User\Studio\Gallery;
use App\Livewire\User\Brands\BrandsIndex;
use App\Livewire\User\Brands\BrandEditor;
use App\Livewire\User\Projects\ProjectsIndex;
use App\Livewire\User\Projects\ProjectWorkspace;
use App\Livewire\User\CopyStudio\CopyStudio;
use App\Livewire\User\CopyStudio\CopyLibrary;
use App\Livewire\User\Support\Support;
use App\Livewire\User\Support\SupportView;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Elseyyid\LaravelJsonLocationsManager\Controllers\HomeController as ElseyyidController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Laravel\Fortify\Features;
use App\Models\GeneralSetting;

// PUBLIC PAGE ROUTES
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'frontend.customUrl']], function () {
    Route::controller(PageController::class)->group(function () {
        Route::get('/',        'home')->name('home')->middleware('frontend.page');
        Route::get('/contact', 'contact')->name('contact');
        Route::post('/contact', 'submitContact')->name('contact.submit');
        Route::get('/privacy', 'privacy')->name('privacy');
        Route::get('/terms',   'terms')->name('terms');
    });

    // PLAN SELECTION — public "Choose plan" buttons on the pricing page route
    // here. Guests get their plan stashed as a session intent (replayed after
    // register/verify/login); signed-in users are sent straight to checkout.
    Route::get('/plans/{planId}/select', [\App\Http\Controllers\PlanSelectionController::class, 'select'])
        ->where('planId', '[A-Za-z0-9_\-]+')
        ->name('plans.select');

    Route::controller(\App\Http\Controllers\BlogController::class)->group(function () {
        Route::get('/blog', 'index')->name('blog.index');
        Route::get('/blog/{slug}', 'show')->name('blog.show')->where('slug', '[a-z0-9-]+');
        Route::post('/blog/{slug}/comments', 'storeComment')
            ->name('blog.comments.store')
            ->where('slug', '[a-z0-9-]+');
    });

    // SOCIAL (oAuth) LOGIN ROUTES
    Route::controller(\App\Http\Controllers\Auth\SocialAuthController::class)->group(function () {
        Route::get('/auth/{provider}/redirect', 'redirect')
            ->name('social.redirect')
            ->where('provider', 'facebook|twitter|google|linkedin');
        Route::get('/auth/{provider}/callback', 'callback')
            ->name('social.callback')
            ->where('provider', 'facebook|twitter|google|linkedin');
    });
});


// AUTHENTICATION ROUTES (Fortify)
//
// Fortify's built-in route registration is disabled in FortifyServiceProvider
// (Fortify::ignoreRoutes()). We load its route definitions here instead, inside
// the LaravelLocalization prefix group, so that:
//   1. Named auth routes (login, register, password.request, verification.*, …)
//      inherit the active locale prefix. A visitor browsing /tr keeps /tr when
//      they click "Sign in" instead of landing on the unprefixed English URL.
//   2. The locale middleware actually runs on the auth pages, so the __()
//      strings in the login/register/forgot-password/etc. views resolve to the
//      chosen language rather than always falling back to English.
//
// The `web` middleware is already applied to every route in this file by
// bootstrap/app.php's withRouting(); config('fortify.middleware') is emptied so
// it is not applied a second time. The locale middleware is added here.
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
    require base_path('vendor/laravel/fortify/routes/routes.php');
});


// INSTALL ROUTES
Route::group(['prefix' => 'install', 'middleware' => 'install'], function() {
    Route::controller(InstallController::class)->group(function () {
        Route::get('/', 'index')->name('install');
        Route::get('/requirements', 'requirements')->name('install.requirements');
        Route::get('/permissions', 'permissions')->name('install.permissions');
        Route::get('/database', 'database')->name('install.database');    
        Route::post('/database', 'storeDatabaseCredentials')->name('install.database.store');
        Route::get('/activation', 'activation')->name('install.activation');    
        Route::post('/activation', 'activateApplication')->name('install.activation.activate');
    });
});

// USER PROFILE ROUTES
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
    Route::group(['prefix' => 'app', 'middleware' => ['auth']], function () {
        Route::redirect('settings', 'app/settings/profile');

        Route::get('settings/profile', Profile::class)->name('profile.edit');
        Route::get('settings/password', Password::class)->name('user-password.edit');
        Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

        Route::get('settings/two-factor', TwoFactor::class)
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor.show');
    });
});

// USER ROUTES
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
    Route::group(['prefix' => 'app', 'middleware' => ['auth', 'verified']], function() {    
        Route::group(['prefix' => 'user', 'middleware' => ['role:user|subscriber|admin', 'PreventBackHistory']], function() {

            // DASHBOARD ROUTES
            Route::get('/dashboard', UserDashboard::class)->name('user.dashboard');

            // AI STUDIO ROUTES
            Route::group(['prefix' => 'studio'], function() {
                Route::get('/images', ImageStudio::class)->name('user.studio.images');
                Route::get('/videos', VideoStudio::class)->name('user.studio.videos');
                Route::get('/gallery', Gallery::class)->name('user.studio.gallery');
                Route::get('/download/{creative}', \App\Http\Controllers\User\CreativeDownloadController::class)
                    ->name('user.studio.download');
            });

            // BRANDS ROUTES
            Route::group(['prefix' => 'brands'], function() {
                Route::get('/', BrandsIndex::class)->name('user.brands.index');
                Route::get('/create', BrandEditor::class)->name('user.brands.create');
                Route::get('/{id}/edit', BrandEditor::class)->name('user.brands.edit');
            });

            // PROJECTS ROUTES
            Route::group(['prefix' => 'projects'], function() {
                Route::get('/', ProjectsIndex::class)->name('user.projects.index');
                Route::get('/{id}', ProjectWorkspace::class)->name('user.projects.show');
            });

            // AD COPY ROUTES
            Route::group(['prefix' => 'copy'], function() {
                Route::get('/', CopyStudio::class)->name('user.copy.studio');
                Route::get('/library', CopyLibrary::class)->name('user.copy.library');
            });

            // SUPPORT ROUTES
            Route::group(['prefix' => 'support'], function() {
                Route::get('/', Support::class)->name('user.support');
                Route::get('/create', Support::class)->name('user.support.create');
                Route::get('/tickets/{ticket_id}', SupportView::class)->name('user.support.view');
            });
        });
    });
});


// ADMIN ROUTES
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
    Route::group(['prefix' => 'app', 'middleware' => ['auth', 'verified']], function() {    
        Route::group(['prefix' => 'admin', 'middleware' => ['role:admin', 'PreventBackHistory']], function() {

            // DASHBOARD ROUTES
            Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
            Route::get('/dashboard/analytics', [\App\Http\Controllers\Admin\AdminController::class, 'analytics'])
                ->name('admin.dashboard.analytics');

            // ACCOUNT SETTINGS ROUTES
            Route::group(['prefix' => 'accounts'], function() {
                Route::get('/dashboard', AccountsDashboard::class)->name('admin.accounts.dashboard');
                Route::get('/list', Accounts::class)->name('admin.accounts.list');
                Route::get('/activity', AccountsActivity::class)->name('admin.accounts.activity');
                Route::get('/create', AccountCreate::class)->name('admin.accounts.create');
                Route::get('/view/{uid}', AccountView::class)->name('admin.accounts.view');
                Route::get('/view/{uid}/edit', AccountEdit::class)->name('admin.accounts.edit');
            });

            // SUPPORT TICKETS ROUTES
            Route::get('/support/tickets', Tickets::class)->name('admin.support.tickets');
            Route::get('/support/tickets/view/{ticket_id}', TicketView::class)->name('admin.support.tickets.view');

            // PLUGINS ROUTES
            Route::get('/plugins', Plugins::class)->name('admin.plugins');
            Route::get('/plugins/checkout/{slug}', PluginCheckout::class)
                ->where('slug', '[A-Za-z0-9_\-]+')
                ->name('admin.plugins.checkout');
            Route::get('/plugins/success/{slug}', PluginSuccess::class)
                ->where('slug', '[A-Za-z0-9_\-]+')
                ->name('admin.plugins.success');
            Route::get('/plugins/success-package/{slug}', PluginSuccessPackage::class)
                ->where('slug', '[A-Za-z0-9_\-]+')
                ->name('admin.plugins.success.package');

            // PLUGIN PURCHASE PAYMENT FLOW (Stripe marketplace)
            Route::controller(\App\Http\Controllers\Admin\PluginController::class)->group(function () {
                Route::get('/plugins/payments/gateway', 'gateway')->name('admin.plugins.gateway');
                Route::get('/plugins/purchase/package/{slug}', 'purchasePackage')->name('admin.plugins.purchase.package');
                Route::post('/plugins/payments/process', 'process')->name('admin.plugins.payments.process');
                Route::get('/plugins/payments/approved', 'approved')->name('admin.plugins.payments.approved');
                Route::get('/plugins/payments/cancel', 'cancel')->name('admin.plugins.payments.cancel');
            });

            // THEMES ROUTES
            Route::get('/themes', Themes::class)->name('admin.themes');
            Route::get('/themes/checkout/{slug}', ThemeCheckout::class)
                ->where('slug', '[A-Za-z0-9_\-]+')
                ->name('admin.themes.checkout');
            Route::get('/themes/success/{slug}', ThemeSuccess::class)
                ->where('slug', '[A-Za-z0-9_\-]+')
                ->name('admin.themes.success');

            // THEME PURCHASE PAYMENT FLOW (Stripe marketplace)
            Route::controller(\App\Http\Controllers\Admin\ThemeController::class)->group(function () {
                Route::get('/themes/payments/gateway', 'gateway')->name('admin.themes.gateway');
                Route::post('/themes/payments/process', 'process')->name('admin.themes.payments.process');
                Route::get('/themes/payments/approved', 'approved')->name('admin.themes.payments.approved');
                Route::get('/themes/payments/cancel', 'cancel')->name('admin.themes.payments.cancel');
            });

            // AI SETTINGS ROUTES
            Route::get('/ai', AISettings::class)->name('admin.ai');

            // GENERAL SETTINGS ROUTE
            Route::get('/general', GeneralSettings::class)->name('admin.general');

            // BACKEND SETTINGS ROUTES
            Route::group(['prefix' => 'backend'], function() {
                Route::get('/global', GlobalSettings::class)->name('admin.backend.global');
                Route::get('/smtp', Smtp::class)->name('admin.backend.smtp');
                Route::get('/registration', Registration::class)->name('admin.backend.registration');
                Route::get('/auth', Auth::class)->name('admin.backend.auth');
                Route::get('/gdpr', Gdpr::class)->name('admin.backend.gdpr');
            });

            // FRONTEND SETTINGS ROUTES
            Route::group(['prefix' => 'frontend'], function() {
                Route::get('/settings', Settings::class)->name('admin.frontend.settings');
                Route::get('/seo', Seo::class)->name('admin.frontend.seo');
                Route::get('/logos', Logos::class)->name('admin.frontend.logos');
                Route::get('/faqs', Faqs::class)->name('admin.frontend.faqs');
                Route::get('/testimonials', Testimonials::class)->name('admin.frontend.testimonials');
                Route::get('/adsense', GoogleAdsense::class)->name('admin.frontend.adsense');
                Route::get('/pages', PagesManager::class)->name('admin.frontend.pages');

                // BLOG MANAGER ROUTES
                Route::get('/blogs', Blogs::class)->name('admin.frontend.blogs');
                Route::get('/blogs/create', BlogForm::class)->name('admin.frontend.blogs.create');
                Route::get('/blogs/edit/{post_id}', BlogForm::class)->name('admin.frontend.blogs.edit');
                Route::get('/blog-comments', BlogComments::class)->name('admin.frontend.blog-comments');
            });

            // NOTIFICATION ROUTES
            Route::get('/notifications/system', SystemNotification::class)->name('admin.notifications.system');

            // UPDATE SOFTWARE ROUTES
            Route::get('/update', Update::class)->name('admin.update');
            Route::get('/update/now', [UpdateController::class, 'updateDatabase']);

            // SYSTEM STATUS ROUTES
            Route::get('/status', Status::class)->name('admin.status');

            // ACTIVATION ROUTES
            Route::get('/activation', Activation::class)->name('admin.activation');

            // LANGUAGE TRANSLATION SETTINGS
            Route::controller(ElseyyidController::class)->group(function() {
                Route::get('/languages/home', 'index')->name('elseyyid.translations.home2');
                Route::get('/languages/lang/{lang}', 'lang')->name('elseyyid.translations.lang2');
                Route::get('/languages/newLang', 'newLang')->name('elseyyid.translations.lang.newLang2');
                Route::get('/languages/newString', 'newString')->name('elseyyid.translations.lang.newString2');
                Route::get('/languages/search', 'search')->name('elseyyid.translations.lang.search2');
                Route::get('/languages/publish-all', 'publishAll')->name('elseyyid.translations.lang.publishAll2');
               // Route::post('/settings/languages/lang/update/{id}', 'update')->name('elseyyid.translations.lang.update');
            });

            Route::get('/languages/lang/generateJson/{lang}', [LanguageController::class, 'generateJson'])->name('elseyyid.translations.lang.generateJson2');

            Route::post('/languages/lang/update-all', [LanguageController::class, 'languagesUpdateAll'])->name('elseyyid.translations.lang.update-all2');
            Route::post('/languages/lang/save', [LanguageController::class, 'languageSave'])->name('elseyyid.translations.lang.lang-save2');
    
            Route::get('/languages/setLocale', function (\Illuminate\Http\Request $request) {
                $settings = GeneralSetting::first();
                $settings->default_language = $request->setLocale;
                $settings->save();
                LaravelLocalization::setLocale($request->setLocale);
                return redirect()
                    ->route('elseyyid.translations.home2', [$request->setLocale])
                    ->with(config('elseyyid-location.message_success_variable'), __('Default language successfully set'));
            })->name('elseyyid.translations.lang.setLocale2');
        
            Route::get('/languages/regenerate', function () {
                Artisan::call('elseyyid:location:install');
                return redirect()
                    ->route('elseyyid.translations.home2')
                    ->with(config('elseyyid-location.message_success_variable'), __('Language files successfully regenerated'));
            })->name('elseyyid.translations.lang.reinstall');

        });
    });
});

/*
|--------------------------------------------------------------------------
| Installed plugin / extension routes
|--------------------------------------------------------------------------
*/
foreach (glob(base_path('routes/extensions/*.php')) ?: [] as $extensionRoute) {
    require $extensionRoute;
}
