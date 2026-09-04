<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatusPageController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\EnsureLicenseIsConfigured;
use App\Http\Middleware\EnsurePaymentProcessorsAreEnabled;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Locale routes
Route::post('/locale', [LocaleController::class, 'updateLocale'])->name('locale');

// Auth routes
Auth::routes(['verify' => true]);
Route::get(ltrim(config('services.google.redirect'), '/'), [LoginController::class, 'google'])->name('login.google');
Route::get(ltrim(config('services.azure.redirect'), '/'), [LoginController::class, 'microsoft'])->name('login.microsoft');
Route::post(ltrim(config('services.apple.redirect'), '/'), [LoginController::class, 'apple'])->name('login.apple');
Route::post('login/tfa/validate', [LoginController::class, 'validateTfaCode'])->name('login.tfa.validate');
Route::post('login/tfa/resend', [LoginController::class, 'resendTfaCode'])->name('login.tfa.resend');

// Install routes
Route::prefix('install')->group(function () {
    Route::middleware(EnsureNotInstalled::class)->group(function () {
        Route::get('/', [InstallController::class, 'index'])->name('install');
        Route::get('/requirements', [InstallController::class, 'requirements'])->name('install.requirements');
        Route::get('/permissions', [InstallController::class, 'permissions'])->name('install.permissions');
        Route::get('/database', [InstallController::class, 'database'])->name('install.database');
        Route::get('/account', [InstallController::class, 'account'])->name('install.account');

        Route::post('/database', [InstallController::class, 'storeConfig']);
        Route::post('/account', [InstallController::class, 'storeDatabase']);
    });

    Route::get('/complete', [InstallController::class, 'complete'])->name('install.complete');
});

// Update routes
Route::prefix('update')->group(function () {
    Route::get('/', [UpdateController::class, 'index'])->name('update');
    Route::get('/overview', [UpdateController::class, 'overview'])->name('update.overview');
    Route::get('/complete', [UpdateController::class, 'complete'])->name('update.complete');

    Route::post('/overview', [UpdateController::class, 'updateDatabase']);
});

// Home routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Contact routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->middleware(ThrottleRequests::class . ':5,10');

// Page routes
Route::get('/pages/{id}', [PageController::class, 'show'])->name('pages.show');

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(EnsureEmailIsVerified::class)->name('dashboard');

// Monitor routes
Route::get('/monitors', [MonitorController::class, 'index'])->middleware(EnsureEmailIsVerified::class)->name('monitors');
Route::get('/monitors/new', [MonitorController::class, 'create'])->middleware(EnsureEmailIsVerified::class)->name('monitors.new');
Route::get('/monitors/{id}/edit', [MonitorController::class, 'edit'])->middleware(EnsureEmailIsVerified::class)->name('monitors.edit');
Route::get('/monitors/{id}', [MonitorController::class, 'show'])->name('monitors.overview');
Route::get('/monitors/{id}/realtime', [MonitorController::class, 'realTime'])->name('monitors.realtime');
Route::get('/monitors/{id}/checks', [MonitorController::class, 'checks'])->name('monitors.checks');
Route::get('/monitors/{id}/incidents', [MonitorController::class, 'incidents'])->name('monitors.incidents');
Route::get('/monitors/{id}/export/incidents', [MonitorController::class, 'exportIncidents'])->name('monitors.export.incidents');
Route::post('/monitors/new', [MonitorController::class, 'store']);
Route::post('/monitors/{id}/edit', [MonitorController::class, 'update']);
Route::post('/monitors/{id}/destroy', [MonitorController::class, 'destroy'])->name('monitors.destroy');

// Status Page routes
Route::get('/status-pages', [StatusPageController::class, 'index'])->middleware(EnsureEmailIsVerified::class)->name('status_pages');
Route::get('/status-pages/new', [StatusPageController::class, 'create'])->middleware(EnsureEmailIsVerified::class)->name('status_pages.new');
Route::get('/status-pages/{id}/edit', [StatusPageController::class, 'edit'])->middleware(EnsureEmailIsVerified::class)->name('status_pages.edit');
Route::post('/status-pages/new', [StatusPageController::class, 'store']);
Route::post('/status-pages/{id}/edit', [StatusPageController::class, 'update']);
Route::post('/status-pages/{id}/destroy', [StatusPageController::class, 'destroy'])->name('status_pages.destroy');

// Incident routes
Route::get('/incidents', [IncidentController::class, 'index'])->middleware(EnsureEmailIsVerified::class)->name('incidents');
Route::get('/incidents/new', [IncidentController::class, 'create'])->middleware(EnsureEmailIsVerified::class)->name('incidents.new');
Route::get('/incidents/{id}/edit', [IncidentController::class, 'edit'])->middleware(EnsureEmailIsVerified::class)->name('incidents.edit');
Route::get('/incidents/export', [IncidentController::class, 'export'])->middleware(EnsureEmailIsVerified::class)->name('incidents.export');
Route::get('/incidents/{id}', [IncidentController::class, 'show'])->name('incidents.show');
Route::post('/incidents/new', [IncidentController::class, 'store']);
Route::post('/incidents/{id}/edit', [IncidentController::class, 'update']);
Route::post('/incidents/{id}/acknowledge', [IncidentController::class, 'acknowledge'])->name('incidents.acknowledge');
Route::post('/incidents/{id}/resolve', [IncidentController::class, 'resolve'])->name('incidents.resolve');
Route::post('/incidents/{id}/destroy', [IncidentController::class, 'destroy'])->name('incidents.destroy');

// Account routes
Route::prefix('account')->middleware(EnsureEmailIsVerified::class)->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('account');

    Route::get('/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/profile/resend', [AccountController::class, 'resendAccountEmailConfirmation'])->name('account.profile.resend');
    Route::post('/profile/cancel', [AccountController::class, 'cancelAccountEmailConfirmation'])->name('account.profile.cancel');

    Route::get('/security', [AccountController::class, 'security'])->name('account.security');
    Route::post('/security', [AccountController::class, 'updateSecurity']);

    Route::get('/plan', [AccountController::class, 'plan'])->name('account.plan');
    Route::post('/plan', [AccountController::class, 'updatePlan'])->middleware(EnsurePaymentProcessorsAreEnabled::class);

    Route::get('/payments', [AccountController::class, 'indexPayments'])->middleware(EnsurePaymentProcessorsAreEnabled::class)->name('account.payments');
    Route::get('/payments/{id}/edit', [AccountController::class, 'editPayment'])->middleware(EnsurePaymentProcessorsAreEnabled::class)->name('account.payments.edit');
    Route::post('/payments/{id}/cancel', [AccountController::class, 'cancelPayment'])->name('account.payments.cancel');

    Route::get('/invoices/{id}', [AccountController::class, 'showInvoice'])->middleware(EnsurePaymentProcessorsAreEnabled::class)->name('account.invoices.show');

    Route::get('/api', [AccountController::class, 'api'])->name('account.api');
    Route::post('/api', [AccountController::class, 'updateApi']);

    Route::get('/delete', [AccountController::class, 'delete'])->name('account.delete');
    Route::post('/destroy', [AccountController::class, 'destroyUser'])->name('account.destroy');
});

// Admin routes
Route::prefix('admin')->middleware([EnsureUserIsAdmin::class, EnsureLicenseIsConfigured::class])->group(function () {
    Route::redirect('/', 'admin/dashboard');

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/settings/{id}', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/{id}', [AdminController::class, 'updateSetting']);

    Route::get('/users', [AdminController::class, 'indexUsers'])->name('admin.users');
    Route::get('/users/new', [AdminController::class, 'createUser'])->name('admin.users.new');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::post('/users/new', [AdminController::class, 'storeUser']);
    Route::post('/users/{id}/edit', [AdminController::class, 'updateUser']);
    Route::post('/users/{id}/destroy', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::post('/users/{id}/disable', [AdminController::class, 'disableUser'])->name('admin.users.disable');
    Route::post('/users/{id}/restore', [AdminController::class, 'restoreUser'])->name('admin.users.restore');
    Route::post('/users/{id}/login', [AdminController::class, 'loginUser'])->name('admin.users.login');

    Route::get('/pages', [AdminController::class, 'indexPages'])->name('admin.pages');
    Route::get('/pages/new', [AdminController::class, 'createPage'])->name('admin.pages.new');
    Route::get('/pages/{id}/edit', [AdminController::class, 'editPage'])->name('admin.pages.edit');
    Route::post('/pages/new', [AdminController::class, 'storePage']);
    Route::post('/pages/{id}/edit', [AdminController::class, 'updatePage']);
    Route::post('/pages/{id}/destroy', [AdminController::class, 'destroyPage'])->name('admin.pages.destroy');

    Route::get('/payments', [AdminController::class, 'indexPayments'])->name('admin.payments');
    Route::get('/payments/{id}/edit', [AdminController::class, 'editPayment'])->name('admin.payments.edit');
    Route::post('/payments/{id}/approve', [AdminController::class, 'approvePayment'])->name('admin.payments.approve');
    Route::post('/payments/{id}/cancel', [AdminController::class, 'cancelPayment'])->name('admin.payments.cancel');

    Route::get('/invoices/{id}', [AdminController::class, 'showInvoice'])->name('admin.invoices.show');

    Route::get('/plans', [AdminController::class, 'indexPlans'])->name('admin.plans');
    Route::get('/plans/new', [AdminController::class, 'createPlan'])->name('admin.plans.new');
    Route::get('/plans/{id}/edit', [AdminController::class, 'editPlan'])->name('admin.plans.edit');
    Route::post('/plans/new', [AdminController::class, 'storePlan']);
    Route::post('/plans/{id}/edit', [AdminController::class, 'updatePlan']);
    Route::post('/plans/{id}/disable', [AdminController::class, 'disablePlan'])->name('admin.plans.disable');
    Route::post('/plans/{id}/restore', [AdminController::class, 'restorePlan'])->name('admin.plans.restore');

    Route::get('/coupons', [AdminController::class, 'indexCoupons'])->name('admin.coupons');
    Route::get('/coupons/new', [AdminController::class, 'createCoupon'])->name('admin.coupons.new');
    Route::get('/coupons/{id}/edit', [AdminController::class, 'editCoupon'])->name('admin.coupons.edit');
    Route::post('/coupons/new', [AdminController::class, 'storeCoupon']);
    Route::post('/coupons/{id}/edit', [AdminController::class, 'updateCoupon']);
    Route::post('/coupons/{id}/disable', [AdminController::class, 'disableCoupon'])->name('admin.coupons.disable');
    Route::post('/coupons/{id}/restore', [AdminController::class, 'restoreCoupon'])->name('admin.coupons.restore');

    Route::get('/tax-rates', [AdminController::class, 'indexTaxRates'])->name('admin.tax_rates');
    Route::get('/tax-rates/new', [AdminController::class, 'createTaxRate'])->name('admin.tax_rates.new');
    Route::get('/tax-rates/{id}/edit', [AdminController::class, 'editTaxRate'])->name('admin.tax_rates.edit');
    Route::post('/tax-rates/new', [AdminController::class, 'storeTaxRate']);
    Route::post('/tax-rates/{id}/edit', [AdminController::class, 'updateTaxRate']);
    Route::post('/tax-rates/{id}/disable', [AdminController::class, 'disableTaxRate'])->name('admin.tax_rates.disable');
    Route::post('/tax-rates/{id}/restore', [AdminController::class, 'restoreTaxRate'])->name('admin.tax_rates.restore');

    Route::get('/monitors', [AdminController::class, 'indexMonitors'])->name('admin.monitors');
    Route::get('/monitors/{id}/edit', [AdminController::class, 'editMonitor'])->name('admin.monitors.edit');
    Route::post('/monitors/{id}/edit', [AdminController::class, 'updateMonitor']);
    Route::post('/monitors/{id}/destroy', [AdminController::class, 'destroyMonitor'])->name('admin.monitors.destroy');

    Route::get('/status-pages', [AdminController::class, 'indexStatusPages'])->name('admin.status_pages');
    Route::get('/status-pages/{id}/edit', [AdminController::class, 'editStatusPage'])->name('admin.status_pages.edit');
    Route::post('/status-pages/{id}/edit', [AdminController::class, 'updateStatusPage']);
    Route::post('/status-pages/{id}/destroy', [AdminController::class, 'destroyStatusPage'])->name('admin.status_pages.destroy');

    Route::get('/incidents', [AdminController::class, 'indexIncidents'])->name('admin.incidents');
    Route::get('/incidents/{id}/edit', [AdminController::class, 'editIncident'])->name('admin.incidents.edit');
    Route::post('/incidents/{id}/edit', [AdminController::class, 'updateIncident']);
    Route::post('/incidents/{id}/destroy', [AdminController::class, 'destroyIncident'])->name('admin.incidents.destroy');
});

// Pricing routes
Route::prefix('pricing')->middleware(EnsurePaymentProcessorsAreEnabled::class)->group(function () {
    Route::get('/', [PricingController::class, 'index'])->name('pricing');
});

// Checkout routes
Route::prefix('checkout')->middleware([EnsureEmailIsVerified::class, EnsurePaymentProcessorsAreEnabled::class])->group(function () {
    Route::get('/cancelled', [CheckoutController::class, 'cancelled'])->name('checkout.cancelled');
    Route::get('/pending', [CheckoutController::class, 'pending'])->name('checkout.pending');
    Route::get('/complete', [CheckoutController::class, 'complete'])->name('checkout.complete');

    Route::get('/{id}', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/{id}', [CheckoutController::class, 'process']);
});

// Cronjob routes
Route::get('/cronjob', [CronjobController::class, 'index'])->name('cronjob');

// Webhook routes
Route::post('webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::post('webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('webhooks/mollie', [WebhookController::class, 'mollie'])->name('webhooks.mollie');
Route::post('webhooks/paddle', [WebhookController::class, 'paddle'])->name('webhooks.paddle');
Route::post('webhooks/razorpay', [WebhookController::class, 'razorpay'])->name('webhooks.razorpay');
Route::post('webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
Route::post('webhooks/cryptocom', [WebhookController::class, 'cryptocom'])->name('webhooks.cryptocom');
Route::post('webhooks/coinbase', [WebhookController::class, 'coinbase'])->name('webhooks.coinbase');

// Developer routes
Route::prefix('/developers')->group(function () {
    Route::get('/', [DeveloperController::class, 'index'])->name('developers');
    Route::get('/monitors', [DeveloperController::class, 'monitors'])->name('developers.monitors');
    Route::get('/status-pages', [DeveloperController::class, 'statusPages'])->name('developers.status_pages');
    Route::get('/incidents', [DeveloperController::class, 'incidents'])->name('developers.incidents');
    Route::get('/stats', [DeveloperController::class, 'stats'])->name('developers.stats');
    Route::get('/account', [DeveloperController::class, 'account'])->name('developers.account');
});

// Sitemap routes
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// PWA routes
Route::get('manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest')->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class]);

// Remote Status Page routes
if (parse_url(config('app.url'), PHP_URL_HOST) && request()->getHost() !== parse_url(config('app.url'), PHP_URL_HOST)) {
    Route::get('/', [StatusPageController::class, 'show'])->name('status_pages.show');
    Route::post('/{id}/password', [StatusPageController::class, 'validatePassword'])->name('status_pages.password');
}
// Status Page routes
else {
    Route::get('/{id}', [StatusPageController::class, 'show'])->name('status_pages.show');
    Route::post('/{id}/password', [StatusPageController::class, 'validatePassword'])->name('status_pages.password');
}

Route::fallback(function () {
    abort(404);
});
