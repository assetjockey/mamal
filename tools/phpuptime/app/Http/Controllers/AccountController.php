<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * The user service instance.
     */
    private UserService $userService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Show the settings index.
     */
    public function index(Request $request): View
    {
        return view('account.index', ['user' => $request->user()]);
    }

    /**
     * Show the profile settings form.
     */
    public function profile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    /**
     * Update the profile settings.
     */
    public function updateProfile(UpdateUserRequest $request): RedirectResponse
    {
        $this->userService->update($request->user(), $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Resent the account email confirmation request.
     */
    public function resendAccountEmailConfirmation(Request $request): RedirectResponse
    {
        try {
            $request->user()->resendPendingEmailVerificationMail();
        } catch (Exception $e) {
            return redirect()->route('account.profile')->with('error', $e->getMessage());
        }

        return back()->with('success', __('A new verification link has been sent to your email address.'));
    }

    /**
     * Cancel the account email confirmation request.
     */
    public function cancelAccountEmailConfirmation(Request $request): RedirectResponse
    {
        $request->user()->clearPendingEmail();

        return back();
    }

    /**
     * Show the security settings form.
     */
    public function security(Request $request): View
    {
        return view('account.security', ['user' => $request->user()]);
    }

    /**
     * Update the security settings.
     */
    public function updateSecurity(UpdateUserRequest $request): RedirectResponse
    {
        $this->userService->update($request->user(), $request->validated());

        if ($request->input('password')) {
            Auth::logoutOtherDevices($request->input('password'));
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the plan settings form.
     */
    public function plan(Request $request): View
    {
        return view('account.plan', ['user' => $request->user()]);
    }

    /**
     * Update the plan settings.
     */
    public function updatePlan(Request $request): RedirectResponse
    {
        $request->user()->cancelPlanSubscription();

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * List the payments.
     */
    public function indexPayments(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['payment_id', 'invoice_id']) ? $request->input('search_by') : 'payment_id';
        $planId = $request->input('plan_id');
        $interval = $request->input('interval');
        $processor = $request->input('processor');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $payments = Payment::where('user_id', '=', $request->user()->id)
            ->when(!empty($planId), function ($query) use ($planId) {
                return $query->ofPlan($planId);
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'interval' => $interval, 'processor' => $processor, 'plan_id' => $planId, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $plans = Plan::where([['amount_month', '>', 0], ['amount_year', '>', 0]])->withTrashed()->get();

        return view('account.payments.index', ['payments' => $payments, 'plans' => $plans]);
    }

    /**
     * Show the edit payment form.
     */
    public function editPayment(Request $request, string $id): View
    {
        $payment = Payment::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('account.payments.edit', ['payment' => $payment]);
    }

    /**
     * Cancel the payment.
     */
    public function cancelPayment(Request $request, string $id): RedirectResponse
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending'], ['user_id', '=', $request->user()->id]])->firstOrFail();
        $payment->status = 'cancelled';
        $payment->save();

        return redirect()->route('account.payments.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Show the invoice.
     */
    public function showInvoice(Request $request, string $id): View
    {
        if (!config('settings.invoicing')) {
            abort(404);
        }

        $payment = Payment::where([['user_id', '=', $request->user()->id], ['id', '=', $id], ['status', '=', 'completed']])->firstOrFail();

        $inclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 0)->sum('percentage');
        $exclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 1)->sum('percentage');

        return view('account.payments.invoice', ['payment' => $payment, 'inclTaxRatesPercentage' => $inclTaxRatesPercentage, 'exclTaxRatesPercentage' => $exclTaxRatesPercentage]);
    }

    /**
     * Show the API settings form.
     */
    public function api(Request $request): View
    {
        return view('account.api', ['user' => $request->user()]);
    }

    /**
     * Update the API settings.
     */
    public function updateApi(UpdateUserRequest $request): RedirectResponse
    {
        $this->userService->update($request->user(), $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the delete account form.
     */
    public function delete(Request $request): View
    {
        return view('account.delete', ['user' => $request->user()]);
    }

    /**
     * Delete the account.
     */
    public function destroyUser(DestroyUserRequest $request): RedirectResponse
    {
        $request->user()->forceDelete();

        return redirect()->route('home');
    }
}
