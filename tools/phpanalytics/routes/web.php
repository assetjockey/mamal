<?php

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth routes
Auth::routes(['verify' => true]);
Route::get(ltrim(config('services.google.redirect'), '/'), 'Auth\LoginController@google')->name('login.google');
Route::get(ltrim(config('services.azure.redirect'), '/'), 'Auth\LoginController@microsoft')->name('login.microsoft');
Route::post(ltrim(config('services.apple.redirect'), '/'), 'Auth\LoginController@apple')->name('login.apple');
Route::post('login/tfa/validate', 'Auth\LoginController@validateTfaCode')->name('login.tfa.validate');
Route::post('login/tfa/resend', 'Auth\LoginController@resendTfaCode')->name('login.tfa.resend');

// Install routes
Route::prefix('install')->group(function () {
    Route::middleware('install')->group(function () {
        Route::get('/', 'InstallController@index')->name('install');
        Route::get('/requirements', 'InstallController@requirements')->name('install.requirements');
        Route::get('/permissions', 'InstallController@permissions')->name('install.permissions');
        Route::get('/database', 'InstallController@database')->name('install.database');
        Route::get('/account', 'InstallController@account')->name('install.account');

        Route::post('/database', 'InstallController@storeConfig');
        Route::post('/account', 'InstallController@storeDatabase');
    });

    Route::get('/complete', 'InstallController@complete')->name('install.complete');
});

// Update routes
Route::prefix('update')->group(function () {
    Route::get('/', 'UpdateController@index')->name('update');
    Route::get('/overview', 'UpdateController@overview')->name('update.overview');
    Route::get('/complete', 'UpdateController@complete')->name('update.complete');

    Route::post('/overview', 'UpdateController@updateDatabase');
});

// Locale routes
Route::post('/locale', 'LocaleController@updateLocale')->name('locale');

// Home routes
Route::get('/', 'HomeController@index')->name('home');

// Contact routes
Route::get('/contact', 'ContactController@index')->name('contact');
Route::post('/contact', 'ContactController@send')->middleware('throttle:5,10');

// Page routes
Route::get('/pages/{id}', 'PageController@show')->name('pages.show');

// Dashboard routes
Route::get('/dashboard', 'DashboardController@index')->middleware('verified')->name('dashboard');

// Website routes
Route::get('/websites/new', 'WebsiteController@create')->middleware('verified')->name('websites.new');
Route::get('/websites/{id}/edit', 'WebsiteController@edit')->middleware('verified')->name('websites.edit');
Route::post('/websites/new', 'WebsiteController@store')->middleware('verified');
Route::post('/websites/{id}/edit', 'WebsiteController@update')->middleware('verified');
Route::post('/websites/{id}/destroy', 'WebsiteController@destroy')->middleware('verified')->name('websites.destroy');

// Account routes
Route::prefix('account')->middleware('verified')->group(function () {
    Route::get('/', 'AccountController@index')->name('account');

    Route::get('/profile', 'AccountController@profile')->name('account.profile');
    Route::post('/profile', 'AccountController@updateProfile')->name('account.profile.update');
    Route::post('/profile/resend', 'AccountController@resendAccountEmailConfirmation')->name('account.profile.resend');
    Route::post('/profile/cancel', 'AccountController@cancelAccountEmailConfirmation')->name('account.profile.cancel');

    Route::get('/security', 'AccountController@security')->name('account.security');
    Route::post('/security', 'AccountController@updateSecurity');

    Route::get('/preferences', 'AccountController@preferences')->name('account.preferences');
    Route::post('/preferences', 'AccountController@updatePreferences');

    Route::get('/plan', 'AccountController@plan')->name('account.plan');
    Route::post('/plan', 'AccountController@updatePlan')->middleware('payment');

    Route::get('/payments', 'AccountController@indexPayments')->middleware('payment')->name('account.payments');
    Route::get('/payments/{id}/edit', 'AccountController@editPayment')->middleware('payment')->name('account.payments.edit');
    Route::post('/payments/{id}/cancel', 'AccountController@cancelPayment')->name('account.payments.cancel');

    Route::get('/invoices/{id}', 'AccountController@showInvoice')->middleware('payment')->name('account.invoices.show');

    Route::get('/api', 'AccountController@api')->name('account.api');
    Route::post('/api', 'AccountController@updateApi');

    Route::get('/delete', 'AccountController@delete')->name('account.delete');
    Route::post('/destroy', 'AccountController@destroyUser')->name('account.destroy');
});

// Admin routes
Route::get('admin/license', 'AdminController@license')->middleware('admin')->name('admin.settings', 'license');
Route::post('admin/license', 'AdminController@updateLicense')->middleware('admin');

Route::prefix('admin')->middleware(['admin', 'license'])->group(function () {
    Route::redirect('/', 'admin/dashboard');

    Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');

    Route::get('/settings/{id}', 'AdminController@settings')->name('admin.settings');
    Route::post('/settings/{id}', 'AdminController@updateSetting');

    Route::get('/users', 'AdminController@indexUsers')->name('admin.users');
    Route::get('/users/new', 'AdminController@createUser')->name('admin.users.new');
    Route::get('/users/{id}/edit', 'AdminController@editUser')->name('admin.users.edit');
    Route::post('/users/new', 'AdminController@storeUser');
    Route::post('/users/{id}/edit', 'AdminController@updateUser');
    Route::post('/users/{id}/destroy', 'AdminController@destroyUser')->name('admin.users.destroy');
    Route::post('/users/{id}/disable', 'AdminController@disableUser')->name('admin.users.disable');
    Route::post('/users/{id}/restore', 'AdminController@restoreUser')->name('admin.users.restore');
    Route::post('/users/{id}/login', 'AdminController@loginUser')->name('admin.users.login');

    Route::get('/pages', 'AdminController@indexPages')->name('admin.pages');
    Route::get('/pages/new', 'AdminController@createPage')->name('admin.pages.new');
    Route::get('/pages/{id}/edit', 'AdminController@editPage')->name('admin.pages.edit');
    Route::post('/pages/new', 'AdminController@storePage');
    Route::post('/pages/{id}/edit', 'AdminController@updatePage');
    Route::post('/pages/{id}/destroy', 'AdminController@destroyPage')->name('admin.pages.destroy');

    Route::get('/payments', 'AdminController@indexPayments')->name('admin.payments');
    Route::get('/payments/{id}/edit', 'AdminController@editPayment')->name('admin.payments.edit');
    Route::post('/payments/{id}/approve', 'AdminController@approvePayment')->name('admin.payments.approve');
    Route::post('/payments/{id}/cancel', 'AdminController@cancelPayment')->name('admin.payments.cancel');

    Route::get('/invoices/{id}', 'AdminController@showInvoice')->name('admin.invoices.show');

    Route::get('/plans', 'AdminController@indexPlans')->name('admin.plans');
    Route::get('/plans/new', 'AdminController@createPlan')->name('admin.plans.new');
    Route::get('/plans/{id}/edit', 'AdminController@editPlan')->name('admin.plans.edit');
    Route::post('/plans/new', 'AdminController@storePlan');
    Route::post('/plans/{id}/edit', 'AdminController@updatePlan');
    Route::post('/plans/{id}/disable', 'AdminController@disablePlan')->name('admin.plans.disable');
    Route::post('/plans/{id}/restore', 'AdminController@restorePlan')->name('admin.plans.restore');

    Route::get('/coupons', 'AdminController@indexCoupons')->name('admin.coupons');
    Route::get('/coupons/new', 'AdminController@createCoupon')->name('admin.coupons.new');
    Route::get('/coupons/{id}/edit', 'AdminController@editCoupon')->name('admin.coupons.edit');
    Route::post('/coupons/new', 'AdminController@storeCoupon');
    Route::post('/coupons/{id}/edit', 'AdminController@updateCoupon');
    Route::post('/coupons/{id}/disable', 'AdminController@disableCoupon')->name('admin.coupons.disable');
    Route::post('/coupons/{id}/restore', 'AdminController@restoreCoupon')->name('admin.coupons.restore');

    Route::get('/tax-rates', 'AdminController@indexTaxRates')->name('admin.tax_rates');
    Route::get('/tax-rates/new', 'AdminController@createTaxRate')->name('admin.tax_rates.new');
    Route::get('/tax-rates/{id}/edit', 'AdminController@editTaxRate')->name('admin.tax_rates.edit');
    Route::post('/tax-rates/new', 'AdminController@storeTaxRate');
    Route::post('/tax-rates/{id}/edit', 'AdminController@updateTaxRate');
    Route::post('/tax-rates/{id}/disable', 'AdminController@disableTaxRate')->name('admin.tax_rates.disable');
    Route::post('/tax-rates/{id}/restore', 'AdminController@restoreTaxRate')->name('admin.tax_rates.restore');

    Route::get('/websites', 'AdminController@indexWebsites')->name('admin.websites');
    Route::get('/websites/{id}/edit', 'AdminController@editWebsite')->name('admin.websites.edit');
    Route::post('/websites/{id}/edit', 'AdminController@updateWebsite');
    Route::post('/websites/{id}/destroy', 'AdminController@destroyWebsite')->name('admin.websites.destroy');
});

// Pricing routes
Route::prefix('pricing')->middleware('payment')->group(function () {
    Route::get('/', 'PricingController@index')->name('pricing');
});

// Checkout routes
Route::prefix('checkout')->middleware(['verified', 'payment'])->group(function () {
    Route::get('/cancelled', 'CheckoutController@cancelled')->name('checkout.cancelled');
    Route::get('/pending', 'CheckoutController@pending')->name('checkout.pending');
    Route::get('/complete', 'CheckoutController@complete')->name('checkout.complete');

    Route::get('/{id}', 'CheckoutController@index')->name('checkout.index');
    Route::post('/{id}', 'CheckoutController@process');
});

// Cronjob routes
Route::get('/cronjob', 'CronjobController@index')->name('cronjob');

// Webhook routes
Route::post('webhooks/paypal', 'WebhookController@paypal')->name('webhooks.paypal');
Route::post('webhooks/stripe', 'WebhookController@stripe')->name('webhooks.stripe');
Route::post('webhooks/mollie', 'WebhookController@mollie')->name('webhooks.mollie');
Route::post('webhooks/paddle', 'WebhookController@paddle')->name('webhooks.paddle');
Route::post('webhooks/razorpay', 'WebhookController@razorpay')->name('webhooks.razorpay');
Route::post('webhooks/paystack', 'WebhookController@paystack')->name('webhooks.paystack');
Route::post('webhooks/cryptocom', 'WebhookController@cryptocom')->name('webhooks.cryptocom');
Route::post('webhooks/coinbase', 'WebhookController@coinbase')->name('webhooks.coinbase');

// Developer routes
Route::prefix('/developers')->group(function () {
    Route::get('/', 'DeveloperController@index')->name('developers');
    Route::get('/stats', 'DeveloperController@stats')->name('developers.stats');
    Route::get('/websites', 'DeveloperController@websites')->name('developers.websites');
    Route::get('/account', 'DeveloperController@account')->name('developers.account');
});

// Stat routes
Route::prefix('/{id}')->group(function () {
    Route::get('/', 'StatController@index')->name('stats.overview');

    Route::get('/realtime', 'StatController@realTime')->name('stats.realtime');

    Route::get('/pages', 'StatController@pages')->name('stats.pages');
    Route::get('/landing_pages', 'StatController@landingPages')->name('stats.landing_pages');

    Route::get('/referrers', 'StatController@referrers')->name('stats.referrers');
    Route::get('/search-engines', 'StatController@searchEngines')->name('stats.search_engines');
    Route::get('/social-networks', 'StatController@socialNetworks')->name('stats.social_networks');
    Route::get('/campaigns', 'StatController@campaigns')->name('stats.campaigns');

    Route::get('/continents', 'StatController@continents')->name('stats.continents');
    Route::get('/countries', 'StatController@countries')->name('stats.countries');
    Route::get('/cities', 'StatController@cities')->name('stats.cities');
    Route::get('/languages', 'StatController@languages')->name('stats.languages');

    Route::get('/browsers', 'StatController@browsers')->name('stats.browsers');
    Route::get('/operating-systems', 'StatController@operatingSystems')->name('stats.operating_systems');
    Route::get('/screen-resolutions', 'StatController@screenResolutions')->name('stats.screen_resolutions');
    Route::get('/devices', 'StatController@devices')->name('stats.devices');

    Route::get('/events', 'StatController@events')->name('stats.events');

    Route::prefix('/export')->group(function () {
        Route::get('/pages', 'StatController@exportPages')->name('stats.export.pages');
        Route::get('/landing_pages', 'StatController@exportLandingPages')->name('stats.export.landing_pages');

        Route::get('/referrers', 'StatController@exportReferrers')->name('stats.export.referrers');
        Route::get('/search-engines', 'StatController@exportSearchEngines')->name('stats.export.search_engines');
        Route::get('/social-networks', 'StatController@exportSocialNetworks')->name('stats.export.social_networks');
        Route::get('/campaigns', 'StatController@exportCampaigns')->name('stats.export.campaigns');

        Route::get('/continents', 'StatController@exportContinents')->name('stats.export.continents');
        Route::get('/countries', 'StatController@exportCountries')->name('stats.export.countries');
        Route::get('/cities', 'StatController@exportCities')->name('stats.export.cities');
        Route::get('/languages', 'StatController@exportLanguages')->name('stats.export.languages');

        Route::get('/browsers', 'StatController@exportBrowsers')->name('stats.export.browsers');
        Route::get('/operating-systems', 'StatController@exportOperatingSystems')->name('stats.export.operating_systems');
        Route::get('/screen-resolutions', 'StatController@exportScreenResolutions')->name('stats.export.screen_resolutions');
        Route::get('/devices', 'StatController@exportDevices')->name('stats.export.devices');

        Route::get('/events', 'StatController@exportEvents')->name('stats.export.events');
    });

    Route::post('/password', 'StatController@validatePassword')->name('stats.password');
});
