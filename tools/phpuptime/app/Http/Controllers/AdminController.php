<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Http\Requests\UpdateMonitorRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Requests\UpdateStatusPageRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\PaymentMail;
use App\Models\Coupon;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorStatusPage;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\StatusPage;
use App\Models\TaxRate;
use App\Services\IncidentService;
use App\Services\MonitorService;
use App\Services\PaymentService;
use App\Services\StatusPageService;
use App\Services\UserService;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * The user service instance.
     */
    private UserService $userService;

    /**
     * The payment service instance.
     */
    private PaymentService $paymentService;

    /**
     * The monitor service instance.
     */
    private MonitorService $monitorService;

    /**
     * The status page service instance.
     */
    private StatusPageService $statusPageService;

    /**
     * The incident service instance.
     */
    private IncidentService $incidentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UserService $userService, PaymentService $paymentService, MonitorService $monitorService, StatusPageService $statusPageService, IncidentService $incidentService)
    {
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->monitorService = $monitorService;
        $this->statusPageService = $statusPageService;
        $this->incidentService = $incidentService;
    }

    /**
     * Show the dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'users' => User::withTrashed()->count(),
            'pages' => Page::count(),
            'plans' => Plan::withTrashed()->count(),
            'payments' => Payment::count()
        ];

        $latestUsers = User::withTrashed()->orderBy('id', 'desc')->limit(5)->get();
        $latestPayments = [];
        $latestMonitors = [];

        if (enabledPaymentProcessors()) {
            $latestPayments = Payment::with('plan')->orderBy('id', 'desc')->limit(5)->get();
        } else {
            $latestMonitors = Monitor::orderBy('id', 'desc')->limit(5)->get();
        }

        return view('admin.dashboard.index', ['stats' => $stats, 'latestUsers' => $latestUsers, 'latestPayments' => $latestPayments, 'latestMonitors' => $latestMonitors]);
    }

    /**
     * Show the settings forms.
     */
    public function settings(string $id): View
    {
        if (!view()->exists('admin.settings.' . $id)) {
            abort(404);
        }

        return view('admin.settings.' . $id);
    }

    /**
     * Update the settings.
     */
    public function updateSetting(UpdateSettingRequest $request, string $id): RedirectResponse
    {
        foreach ($request->except(['_token', 'submit']) as $key => $value) {
            if ($request->hasFile($key)) {
                $value = $request->file($key)->hashName();

                if (file_exists(public_path('uploads/brand/' . config('settings.' . $key)))) {
                    unlink(public_path('uploads/brand/' . config('settings.' . $key)));
                }
                $request->file($key)->move(public_path('uploads/brand'), $value);
            }

            if ($id == 'cronjob' || $id == 'webhook_secret_key') {
                $value = Str::random(32);
            } elseif ($id == 'license') {
                $httpClient = new GuzzleClient(['timeout' => 10, 'verify' => false]);

                try {
                    $response = $httpClient->request('POST', 'https://api.lunatio.com/license', [
                        'form_params' => [
                            'license' => $request->input('license_key'),
                            'product' => config('info.software.name'),
                            'url' => config('app.url')
                        ]
                    ]);

                    $output = json_decode($response->getBody()->getContents());

                    if ($output->status == 200) {
                        Setting::where('name', '=', 'license_key')->update(['value' => $request->input('license_key')]);
                        Setting::where('name', '=', 'license_type')->update(['value' => $output->type]);

                        if (config('settings.license_key')) {
                            return redirect()->route('admin.settings', 'license');
                        }

                        return redirect()->route('admin.dashboard');
                    }

                    return redirect()->back()->withErrors(['license_key' => $output->message])->withInput();
                } catch (Exception $e) {
                    return redirect()->back()->with('error', $e->getMessage());
                }
            }

            Setting::where('name', $key)->update(['value' => $value]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * List the users.
     */
    public function indexUsers(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'email']) ? $request->input('search_by') : 'name';
        $planId = $request->input('plan_id');
        $role = $request->input('role');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'email']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $users = User::withTrashed()
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'email') {
                    return $query->searchEmail($search);
                }
                return $query->searchName($search);
            })
            ->when(!empty($planId), function ($query) use ($planId) {
                return $query->ofPlan($planId);
            })
            ->when(isset($role) && is_numeric($role), function ($query) use ($role) {
                return $query->ofRole($role);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'plan_id' => $planId, 'role' => $role, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $plans = Plan::withTrashed()->get();

        return view('admin.users.index', ['users' => $users, 'plans' => $plans]);
    }

    /**
     * Show the create user form.
     */
    public function createUser(): View
    {
        return view('admin.users.new');
    }

    /**
     * Show the edit User form.
     */
    public function editUser($id): View
    {
        $user = User::withTrashed()
            ->withCount([
                'payments',
                'monitors',
                'statusPages',
                'incidents',
            ])
            ->where('id', '=', $id)
            ->firstOrFail();

        $plans = Plan::withTrashed()->get();

        return view('account.profile', ['user' => $user, 'plans' => $plans]);
    }

    /**
     * Store the user.
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->store($request->validated() + ['mark_email_as_verified' => true]);

        return redirect()->route('admin.users')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the user.
     */
    public function updateUser(UpdateUserRequest $request, string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($request->user()->id == $user->id && $request->input('role') == 0) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $this->userService->update($user, $request->validated(), $request->user());

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the user.
     */
    public function destroyUser(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            User::withTrashed()->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($user) use ($request) {
                $request->user()->id == $user->id && $user->isAdmin() ?: $user->forceDelete();
            });

            return redirect()->route('admin.users')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $user = User::withTrashed()->findOrFail($id);

        if ($request->user()->id == $user->id && $user->isAdmin()) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $user->forceDelete();

        return redirect()->route('admin.users')->with('success', __(':name has been deleted.', ['name' => $user->name]));
    }

    /**
     * Soft delete the user.
     */
    public function disableUser(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($request->user()->id == $user->id && $user->isAdmin()) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $user->delete();

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the soft deleted user.
     */
    public function restoreUser(string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Login as the user.
     */
    public function loginUser(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        Auth::logout();

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * List the pages.
     */
    public function indexPages(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'email']) ? $request->input('search_by') : 'name';
        $visibility = $request->input('visibility');
        $language = $request->input('language');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $pages = Page::when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->when(isset($visibility) && is_numeric($visibility), function ($query) use ($visibility) {
                return $query->ofVisibility((int)$visibility);
            })
            ->when(isset($language), function ($query) use ($language) {
                return $query->ofLanguage($language);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'visibility' => $visibility, 'language' => $language, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('admin.pages.index', ['pages' => $pages]);
    }

    /**
     * Show the create page form.
     */
    public function createPage(): View
    {
        return view('admin.pages.new');
    }

    /**
     * Show the edit page form.
     */
    public function editPage(string $id): View
    {
        $page = Page::where('id', '=', $id)->firstOrFail();

        return view('admin.pages.edit', ['page' => $page]);
    }

    /**
     * Store the page.
     */
    public function storePage(StorePageRequest $request): RedirectResponse
    {
        $page = new Page;

        $page->name = $request->input('name');
        $page->slug = $request->input('slug');
        $page->visibility = $request->input('visibility');
        $page->language = $request->input('language');
        $page->content = $request->input('content');

        $page->save();

        return redirect()->route('admin.pages')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the page.
     */
    public function updatePage(UpdatePageRequest $request, string $id): RedirectResponse
    {
        $page = Page::findOrFail($id);

        $page->name = $request->input('name');
        $page->slug = $request->input('slug');
        $page->visibility = $request->input('visibility');
        $page->language = $request->input('language');
        $page->content = $request->input('content');

        $page->save();

        return redirect()->route('admin.pages.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the page.
     */
    public function destroyPage(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Page::whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($page) use ($request) {
                $page->delete();
            });

            return redirect()->route('admin.pages')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $page = Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages')->with('success', __(':name has been deleted.', ['name' => $page->name]));
    }

    /**
     * List the payments.
     */
    public function indexPayments(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['payment_id', 'invoice_id']) ? $request->input('search_by') : 'payment_id';
        $userId = $request->input('user_id');
        $planId = $request->input('plan_id');
        $interval = $request->input('interval');
        $processor = $request->input('processor');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $payments = Payment::with('user')
            ->when(!empty($planId), function ($query) use ($planId) {
                return $query->ofPlan($planId);
            })
            ->when($userId, function ($query) use ($userId) {
                return $query->ofUser($userId);
            })
            ->when($interval, function ($query) use ($interval) {
                return $query->ofInterval($interval);
            })
            ->when($processor, function ($query) use ($processor) {
                return $query->ofProcessor($processor);
            })
            ->when($status, function ($query) use ($status) {
                return $query->ofStatus($status);
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'invoice_id') {
                    return $query->searchInvoice($search);
                }
                return $query->searchPayment($search);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'interval' => $interval, 'processor' => $processor, 'plan_id' => $planId, 'status' => $status, 'user_id' => $userId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $plans = Plan::where([['amount_month', '>', 0], ['amount_year', '>', 0]])->withTrashed()->get();

        $user = null;
        if ($userId) {
            $user = User::where('id', '=', $userId)->withTrashed()->first();
        }

        return view('admin.payments.index', ['payments' => $payments, 'interval' => $interval, 'plans' => $plans, 'user' => $user]);
    }

    /**
     * Show the edit payment form.
     */
    public function editPayment(string $id): View
    {
        $payment = Payment::where('id', '=', $id)->firstOrFail();

        return view('account.payments.edit', ['payment' => $payment]);
    }

    /**
     * Approve the payment.
     */
    public function approvePayment(Request $request, string $id): RedirectResponse
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending']])->firstOrFail();
        $payment->status = 'completed';
        $payment->save();

        $user = User::withTrashed()->findOrFail($payment->user_id);
        $user->cancelPlanSubscription();
        $now = Carbon::now();
        $user->plan_id = $payment->plan->id;
        $user->plan_interval = $payment->interval;
        $user->plan_currency = $payment->currency;
        $user->plan_amount = $payment->amount;
        $user->plan_payment_processor = $payment->processor;
        $user->plan_subscription_id = null;
        $user->plan_subscription_status = null;
        $user->plan_subscription_information = null;
        $user->plan_created_at = $now;
        $user->plan_recurring_at = null;
        $user->plan_trial_ends_at = $user->plan_trial_ends_at ? $now : null;
        $user->plan_ends_at = $payment->interval == 'month' ? (clone $now)->addMonth() : (clone $now)->addYear();
        $user->save();

        if (isset($payment->coupon->id)) {
            $coupon = Coupon::find($payment->coupon->id);

            if ($coupon) {
                $coupon->increment('redeems', 1);
            }
        }

        $this->paymentService->sendInvoiceEmail($payment, $user);

        return redirect()->route('admin.payments.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Cancel the payment.
     */
    public function cancelPayment(Request $request, string $id): RedirectResponse
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending']])->firstOrFail();
        $payment->status = 'cancelled';
        $payment->save();

        $user = User::withTrashed()->findOrFail($payment->user_id);

        if (config('settings.invoicing')) {
            try {
                Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
            } catch (Exception) {}
        }

        return redirect()->route('admin.payments.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Show the invoice.
     */
    public function showInvoice(string $id): View
    {
        if (!config('settings.invoicing')) {
            abort(404);
        }

        $payment = Payment::where([['id', '=', $id], ['status', '!=', 'pending']])->firstOrFail();

        $inclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 0)->sum('percentage');
        $exclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 1)->sum('percentage');

        return view('account.payments.invoice', ['payment' => $payment, 'inclTaxRatesPercentage' => $inclTaxRatesPercentage, 'exclTaxRatesPercentage' => $exclTaxRatesPercentage]);
    }

    /**
     * List the plans.
     */
    public function indexPlans(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $visibility = $request->input('visibility');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $plans = Plan::withTrashed()
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->when(isset($visibility) && is_numeric($visibility), function ($query) use ($visibility) {
                return $query->ofVisibility((int)$visibility);
            })
            ->when(isset($status) && is_numeric($status), function ($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'visibility' => $visibility, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('admin.plans.index', ['plans' => $plans]);
    }

    /**
     * Show the create plan form.
     */
    public function createPlan(): View
    {
        $coupons = Coupon::all();

        $taxRates = TaxRate::all();

        return view('admin.plans.new', ['coupons' => $coupons, 'taxRates' => $taxRates]);
    }

    /**
     * Show the edit plan form.
     */
    public function editPlan(string $id): View
    {
        $plan = Plan::withTrashed()->where('id', '=', $id)->firstOrFail();

        $coupons = Coupon::all();

        $taxRates = TaxRate::all();

        return view('admin.plans.edit', ['plan' => $plan, 'coupons' => $coupons, 'taxRates' => $taxRates]);
    }

    /**
     * Store the plan.
     */
    public function storePlan(StorePlanRequest $request): RedirectResponse
    {
        $plan = new Plan;

        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        if ($request->has('amount_month')) {
            $plan->amount_month = $request->input('amount_month');
        }
        if ($request->has('amount_year')) {
            $plan->amount_year = $request->input('amount_year');
        }
        if ($request->has('currency')) {
            $plan->currency = $request->input('currency');
        }
        if ($request->has('coupons')) {
            $plan->coupons = $request->input('coupons');
        }
        if ($request->has('tax_rates')) {
            $plan->tax_rates = $request->input('tax_rates');
        }
        if ($request->has('trial_days')) {
            $plan->trial_days = $request->input('trial_days');
        }
        if ($request->has('visibility')) {
            $plan->visibility = $request->input('visibility');
        }
        if ($request->has('position')) {
            $plan->position = $request->input('position');
        }
        $plan->features = $request->input('features');
        $plan->save();

        return redirect()->route('admin.plans')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the plan.
     */
    public function updatePlan(UpdatePlanRequest $request, string $id): RedirectResponse
    {
        $plan = Plan::withTrashed()->findOrFail($id);

        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        if (config('settings.license_type')) {
            if (!$plan->isDefault()) {
                if ($request->has('amount_month')) {
                    $plan->amount_month = $request->input('amount_month');
                }
                if ($request->has('amount_year')) {
                    $plan->amount_year = $request->input('amount_year');
                }
                if ($request->has('currency')) {
                    $plan->currency = $request->input('currency');
                }
                if ($request->has('coupons')) {
                    $plan->coupons = $request->input('coupons');
                }
                if ($request->has('tax_rates')) {
                    $plan->tax_rates = $request->input('tax_rates');
                }
                if ($request->has('trial_days')) {
                    $plan->trial_days = $request->input('trial_days');
                }
            }
            if ($request->has('visibility')) {
                $plan->visibility = $request->input('visibility');
            }
            if ($request->has('position')) {
                $plan->position = $request->input('position');
            }
        }
        $plan->features = $request->input('features');
        $plan->save();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the plan.
     */
    public function disablePlan(string $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);

        if ($plan->isDefault()) {
            return redirect()->route('admin.plans.edit', $id)->with('error', __('The default plan can\'t be disabled.'));
        }

        $plan->delete();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the plan.
     */
    public function restorePlan(string $id): RedirectResponse
    {
        $plan = Plan::withTrashed()->findOrFail($id);
        $plan->restore();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the coupons.
     */
    public function indexCoupons(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'code']) ? $request->input('search_by') : 'name';
        $type = $request->input('type');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'code']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $coupons = Coupon::withTrashed()
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'code') {
                    return $query->searchCode($search);
                }
                return $query->searchName($search);
            })
            ->when(isset($type) && is_numeric($type), function ($query) use ($type) {
                return $query->ofType($type);
            })
            ->when(isset($status) && is_numeric($status), function ($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'type' => $type, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('admin.coupons.index', ['coupons' => $coupons]);
    }

    /**
     * Show the create coupon form.
     */
    public function createCoupon(): View
    {
        return view('admin.coupons.new');
    }

    /**
     * Show the edit coupon form.
     */
    public function editCoupon(string $id): View
    {
        $coupon = Coupon::where('id', '=', $id)->withTrashed()->firstOrFail();

        return view('admin.coupons.edit', ['coupon' => $coupon]);
    }

    /**
     * Store the coupon.
     */
    public function storeCoupon(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = new Coupon;

        $coupon->name = $request->input('name');
        $coupon->code = $request->input('code');
        $coupon->type = $request->input('type');
        $coupon->days = $request->input('days');
        $coupon->percentage = $request->input('type') ? 100 : $request->input('percentage');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the coupon.
     */
    public function updateCoupon(UpdateCouponRequest $request, string $id): RedirectResponse
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);

        $coupon->code = $request->input('code');
        $coupon->days = $request->input('days');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the Coupon.
     */
    public function disableCoupon(string $id): RedirectResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the coupon.
     */
    public function restoreCoupon(string $id): RedirectResponse
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);
        $coupon->restore();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the tax rates.
     */
    public function indexTaxRates(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'code']) ? $request->input('search_by') : 'name';
        $type = $request->input('type');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'code']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $taxRates = TaxRate::withTrashed()
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->when(isset($type) && is_numeric($type), function ($query) use ($type) {
                return $query->ofType($type);
            })
            ->when(isset($status) && is_numeric($status), function ($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'type' => $type, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('admin.tax-rates.index', ['taxRates' => $taxRates]);
    }

    /**
     * Show the create tax rate form.
     */
    public function createTaxRate(): View
    {
        return view('admin.tax-rates.new');
    }

    /**
     * Show the edit tax rate form.
     */
    public function editTaxRate(string $id): View
    {
        $taxRate = TaxRate::where('id', '=', $id)->withTrashed()->firstOrFail();

        return view('admin.tax-rates.edit', ['taxRate' => $taxRate]);
    }

    /**
     * Store the tax rate.
     */
    public function storeTaxRate(StoreTaxRateRequest $request): RedirectResponse
    {
        $taxRate = new TaxRate;

        $taxRate->name = $request->input('name');
        $taxRate->type = $request->input('type');
        $taxRate->percentage = $request->input('percentage');
        $taxRate->regions = $request->input('regions');

        $taxRate->save();

        return redirect()->route('admin.tax_rates')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the tax rate.
     */
    public function updateTaxRate(UpdateTaxRateRequest $request, string $id): RedirectResponse
    {
        $taxRate = TaxRate::withTrashed()->findOrFail($id);

        $taxRate->regions = $request->input('regions');

        $taxRate->save();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the Tax Rate.
     */
    public function disableTaxRate(string $id): RedirectResponse
    {
        $taxRate = TaxRate::findOrFail($id);
        $taxRate->delete();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the tax rate.
     */
    public function restoreTaxRate(string $id): RedirectResponse
    {
        $taxRate = TaxRate::withTrashed()->findOrFail($id);
        $taxRate->restore();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the monitors.
     */
    public function indexMonitors(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'url']) ? $request->input('search_by') : 'name';
        $userId = $request->input('user_id');
        $statusPageId = $request->input('status_page_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $monitors = Monitor::with('user')
            ->when($userId, function ($query) use ($userId) {
                return $query->ofUser($userId);
            })
            ->when($statusPageId, function ($query) use ($statusPageId) {
                return $query->whereIn('id', MonitorStatusPage::select('monitor_id')->where('status_page_id', '=', $statusPageId));
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'url') {
                    return $query->searchUrl($search);
                } else {
                    return $query->searchName($search);
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'user_id' => $userId, 'status_page_id' => $statusPageId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $user = null;
        if ($userId) {
            $user = User::where('id', '=', $userId)->withTrashed()->first();
        }

        $statusPage = null;
        if ($statusPageId) {
            $statusPage = StatusPage::where('id', '=', $statusPageId)->first();
        }

        return view('admin.monitors.index', ['monitors' => $monitors, 'user'=> $user, 'statusPage' => $statusPage]);
    }

    /**
     * Show the edit monitor form.
     */
    function editMonitor(string $id): View
    {
        $monitor = Monitor::withCount([
                'statusPages',
                'incidents',
            ])
            ->where('id', '=', $id)
            ->firstOrFail();

        return view('monitors.edit', ['monitor' => $monitor]);
    }

    /**
     * Update the monitor.
     */
    public function updateMonitor(UpdateMonitorRequest $request, string $id): RedirectResponse
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        $this->monitorService->update($monitor, $request->validated());

        return redirect()->route('admin.monitors.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the monitor.
     */
    public function destroyMonitor(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Monitor::whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($monitor) use ($request) {
                $monitor->delete();
            });

            return redirect()->route('admin.monitors')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $monitor = Monitor::where('id', '=', $id)->firstOrFail();
        $monitor->delete();

        return redirect()->route('admin.monitors')->with('success', __(':name has been deleted.', ['name' => $monitor->name]));
    }

    /**
     * List the status pages.
     */
    public function indexStatusPages(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $userId = $request->input('user_id');
        $monitorId = $request->input('monitor_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $statusPages = StatusPage::with('user', 'monitors')
            ->when($userId, function ($query) use ($userId) {
                return $query->ofUser($userId);
            })
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->whereIn('id', MonitorStatusPage::select('status_page_id')->where('monitor_id', '=', $monitorId)->get());
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'url') {
                    return $query->searchUrl($search);
                } else {
                    return $query->searchName($search);
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'user_id' => $userId, 'monitor_id' => $monitorId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $user = null;
        if ($userId) {
            $user = User::where('id', '=', $userId)->withTrashed()->first();
        }

        $monitor = null;
        if ($monitorId) {
            $monitor = Monitor::where('id', '=', $monitorId)->first();
        }

        return view('admin.status-pages.index', ['statusPages' => $statusPages, 'user' => $user, 'monitor' => $monitor]);
    }

    /**
     * Show the edit status page form.
     */
    function editStatusPage(string $id): View
    {
        $statusPage = StatusPage::withCount([
                'monitors',
            ])
            ->where('id', '=', $id)
            ->firstOrFail();

        $monitors = Monitor::where('user_id', '=', $statusPage->user_id)->get();

        return view('status-pages.edit', ['statusPage' => $statusPage, 'monitors' => $monitors]);
    }

    /**
     * Update the status page.
     */
    public function updateStatusPage(UpdateStatusPageRequest $request, string $id): RedirectResponse
    {
        $statusPage = StatusPage::where('id', '=', $id)->firstOrFail();

        $this->statusPageService->update($statusPage, $request->validated());

        return redirect()->route('admin.status_pages.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the status page.
     */
    public function destroyStatusPage(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            StatusPage::whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($statusPage) use ($request) {
                $statusPage->delete();
            });

            return redirect()->route('admin.status_pages')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $statusPage = StatusPage::where('id', '=', $id)->firstOrFail();
        $statusPage->delete();

        return redirect()->route('admin.status_pages')->with('success', __(':name has been deleted.', ['name' => $statusPage->name]));
    }

    /**
     * List the incidents.
     */
    public function indexIncidents(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['monitor', 'cause', 'comment']) ? $request->input('search_by') : 'monitor';
        $userId = $request->input('user_id');
        $monitorId = $request->input('monitor_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'ended_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $incidents = Incident::with('user', 'monitor')
            ->when($userId, function ($query) use ($userId) {
                return $query->ofUser($userId);
            })
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->ofMonitor($monitorId);
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'monitor') {
                    return $query->whereHas('monitor', function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    });
                } elseif ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($sortBy == 'ended_at', function ($query) use ($sort, $sortBy) {
                $query->orderByRaw('(`ended_at` IS NULL) ' . $sort);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'user_id' => $userId, 'monitor_id' => $monitorId, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $user = null;
        if ($userId) {
            $user = User::where('id', '=', $userId)->withTrashed()->first();
        }

        $monitor = null;
        if ($monitorId) {
            $monitor = Monitor::where('id', '=', $monitorId)->first();
        }

        return view('admin.incidents.index', ['incidents' => $incidents, 'user' => $user, 'monitor' => $monitor]);
    }

    /**
     * Show the edit incident form.
     */
    function editIncident(string $id): View
    {
        $incident = Incident::where('id', '=', $id)->firstOrFail();

        $monitors = Monitor::where('user_id', '=', $incident->user_id)->get();

        return view('incidents.edit', ['incident' => $incident, 'monitors' => $monitors]);
    }

    /**
     * Update the incident.
     */
    public function updateIncident(UpdateIncidentRequest $request, string $id): RedirectResponse
    {
        $incident = Incident::where('id', '=', $id)->firstOrFail();

        $this->incidentService->update($incident, $request->validated());

        return redirect()->route('admin.incidents.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the incident.
     */
    public function destroyIncident(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Incident::whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($incident) use ($request) {
                $incident->delete();
            });

            return redirect()->route('admin.incidents')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $incident = Incident::where('id', '=', $id)->firstOrFail();
        $incident->delete();

        return redirect()->route('admin.incidents')->with('success', __(':name has been deleted.', ['name' => $incident->cause]));
    }
}
