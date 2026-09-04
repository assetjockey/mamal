<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Validator;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $monthlyPlans = Plan::where('interval', 0)->get();
        $yearlyPlans = Plan::where('interval', 1)->get();
        $lifetimePlans = Plan::where('interval', 2)->get();
        return view('backend.plans.index', ['monthlyPlans' => $monthlyPlans, 'yearlyPlans' => $yearlyPlans, 'lifetimePlans' => $lifetimePlans]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $activePaymentMethod = PaymentGateway::where('status', 1)->get();
        if (count($activePaymentMethod) < 1) {
            toastr()->error(__('No active payment method'));
            return back()->withInput();
        }
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'short_description' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:10'],
            'interval' => ['required', 'integer', 'min:0', 'max:2'],
            'price' => ['sometimes', 'regex:/^\d*(\.\d{2})?$/'],
            'storage_space' => ['sometimes', 'numeric'],
            'transfer_size' => ['sometimes', 'numeric'],
            'transfer_interval' => ['sometimes', 'numeric'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }
        if ($request->has('custom_features')) {
            foreach ($request->custom_features as $custom_feature) {
                if (empty($custom_feature['name'])) {
                    toastr()->error(__('Custom feature cannot be empty'));
                    return back()->withInput();
                }
            }
        }
        if ($request->has('featured_plan') && $request->has('free_plan')) {
            toastr()->error(__('Free plan cannot be featured'));
            return back()->withInput();
        }
        if ($request->has('free_plan')) {
            $plan = Plan::where([['price', 0], ['auth', 0]])->first();
            if (!is_null($plan)) {
                toastr()->error(__('Free plan is already exists'));
                return back()->withInput();
            }
            $request->price = 0;
            $request->auth = ($request->has('auth')) ? 1 : 0;
        } else {
            if ($request->price < 0.50) {
                toastr()->error(__('Price cannot be less than ') . priceSymbol('0.50'));
                return back()->withInput();
            }
            $request->auth = 1;
        }
        $oneMega = 1048576;
        if ($request->has('unlimited_storage_space')) {
            $request->storage_space = null;
        } else {
            if ($request->storage_space < 1) {
                toastr()->error(__('Storage space cannot be less than 1 MB'));
                return back()->withInput();
            } else {
                $request->storage_space = $request->storage_space * $oneMega;
            }
        }
        if ($request->has('unlimited_transfer_size')) {
            $request->transfer_size = null;
        } else {
            if ($request->transfer_size < 1) {
                toastr()->error(__('Transfer size cannot be less than 1 MB'));
                return back()->withInput();
            } else {
                $request->transfer_size = $request->transfer_size * $oneMega;
            }
        }
        if ($request->has('unlimited_transfer_time')) {
            $request->transfer_interval = null;
        } else {
            if ($request->transfer_interval < 1) {
                toastr()->error(__('Files available for ' . $request->transfer_interval . ' days is not logical'));
                return back()->withInput();
            }
        }
        $request->transfer_password = ($request->has('transfer_password')) ? 1 : 0;
        $request->transfer_notify = ($request->has('transfer_notify')) ? 1 : 0;
        $request->transfer_expiry = ($request->has('transfer_expiry')) ? 1 : 0;
        $request->transfer_link = ($request->has('transfer_link')) ? 1 : 0;
        $request->advertisements = ($request->has('advertisements')) ? 1 : 0;
        $request->custom_features = ($request->has('custom_features')) ? $request->custom_features : null;
        $request->featured_plan = ($request->has('featured_plan')) ? 1 : 0;
        $createPlan = Plan::create([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'color' => $request->color,
            'interval' => $request->interval,
            'price' => $request->price,
            'auth' => $request->auth,
            'storage_space' => $request->storage_space,
            'transfer_size' => $request->transfer_size,
            'transfer_interval' => $request->transfer_interval,
            'transfer_password' => $request->transfer_password,
            'transfer_notify' => $request->transfer_notify,
            'transfer_expiry' => $request->transfer_expiry,
            'transfer_link' => $request->transfer_link,
            'advertisements' => $request->advertisements,
            'custom_features' => $request->custom_features,
            'featured_plan' => $request->featured_plan,
        ]);
        if ($createPlan) {
            if ($request->has('featured_plan')) {
                Plan::where([['id', '!=', $createPlan->id], ['interval', $createPlan->interval]])->update(['featured_plan' => 0]);
            }
            toastr()->success(__('Created Successfully'));
            return redirect()->route('admin.plans.index');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function show(Plan $plan)
    {
        return abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        $interval = !$plan->interval ? 'Monthly' : 'yearly';
        return view('backend.plans.edit', ['plan' => $plan, 'interval' => $interval]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plan $plan)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'short_description' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:10'],
            'price' => ['sometimes', 'regex:/^\d*(\.\d{2})?$/'],
            'storage_space' => ['sometimes', 'numeric'],
            'transfer_size' => ['sometimes', 'numeric'],
            'transfer_interval' => ['sometimes', 'numeric'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }
        if ($request->has('custom_features')) {
            foreach ($request->custom_features as $custom_feature) {
                if (empty($custom_feature['name'])) {
                    toastr()->error(__('Custom feature cannot be empty'));
                    return back()->withInput();
                }
            }
        }
        if ($request->has('featured_plan') && $request->has('free_plan')) {
            toastr()->error(__('Free plan cannot be featured'));
            return back()->withInput();
        }
        if ($request->has('free_plan')) {
            $checkPlan = Plan::where([['price', 0], ['auth', 0]])->first();
            if (!is_null($checkPlan) && $checkPlan->id != $plan->id) {
                toastr()->error(__('Free plan is already exists'));
                return back()->withInput();
            }
            $request->price = 0;
            $request->auth = ($request->has('auth')) ? 1 : 0;
        } else {
            if ($request->price < 0.50) {
                toastr()->error(__('Price cannot be less than ') . priceSymbol('0.50'));
                return back()->withInput();
            }
            $request->auth = 1;
        }

        $oneMega = 1048576;
        if ($request->has('unlimited_storage_space')) {
            $request->storage_space = null;
        } else {
            if (is_null($plan->storage_space)) {
                toastr()->error(__('Storage space cannot be reduced'));
                return back()->withInput();
            }
            if ($request->storage_space < 1) {
                toastr()->error(__('Storage space cannot be less than 1 MB'));
                return back()->withInput();
            } else {
                $request->storage_space = $request->storage_space * $oneMega;
            }
            if ($request->storage_space < $plan->storage_space) {
                toastr()->error(__('Storage space cannot be reduced'));
                return back()->withInput();
            }
        }

        if ($request->has('unlimited_transfer_size')) {
            $request->transfer_size = null;
        } else {
            if ($request->transfer_size < 1) {
                toastr()->error(__('Transfer size cannot be less than 1 MB'));
                return back()->withInput();
            } else {
                $request->transfer_size = $request->transfer_size * $oneMega;
            }
        }

        if ($request->has('unlimited_transfer_time')) {
            $request->transfer_interval = null;
        } else {
            if ($request->transfer_interval < 1) {
                toastr()->error(__('Files available for ' . $request->transfer_interval . ' days is not logical'));
                return back()->withInput();
            }
        }

        $request->transfer_password = ($request->has('transfer_password')) ? 1 : 0;
        $request->transfer_notify = ($request->has('transfer_notify')) ? 1 : 0;
        $request->transfer_expiry = ($request->has('transfer_expiry')) ? 1 : 0;
        $request->transfer_link = ($request->has('transfer_link')) ? 1 : 0;
        $request->advertisements = ($request->has('advertisements')) ? 1 : 0;
        $request->custom_features = ($request->has('custom_features')) ? $request->custom_features : null;
        $request->featured_plan = ($request->has('featured_plan')) ? 1 : 0;

        $updatePlan = $plan->update([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'color' => $request->color,
            'price' => $request->price,
            'auth' => $request->auth,
            'storage_space' => $request->storage_space,
            'transfer_size' => $request->transfer_size,
            'transfer_interval' => $request->transfer_interval,
            'transfer_password' => $request->transfer_password,
            'transfer_notify' => $request->transfer_notify,
            'transfer_expiry' => $request->transfer_expiry,
            'transfer_link' => $request->transfer_link,
            'advertisements' => $request->advertisements,
            'custom_features' => $request->custom_features,
            'featured_plan' => $request->featured_plan,
        ]);

        if ($updatePlan) {
            if ($request->has('featured_plan')) {
                Plan::where([['id', '!=', $plan->id], ['interval', $plan->interval]])->update(['featured_plan' => 0]);
            }
            toastr()->success(__('Updated Successfully'));
            return back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plan $plan)
    {
        $transactions = Transaction::where('plan_id', $plan->id)->get();
        if (count($transactions) > 0) {
            toastr()->error(__('This Plan linked with transactions it can not be deleted'));
            return back();
        }
        $subscriptions = Subscription::where('plan_id', $plan->id)->get();
        if (count($subscriptions) > 0) {
            toastr()->error(__('This plan has subscriptions it can not be deleted'));
            return back();
        }
        $coupons = Coupon::where([['plan_id', $plan->id], ['deleted_at', null]])->get();
        if (count($coupons) > 0) {
            toastr()->error(__('This Plan linked with coupons it can not be deleted'));
            return back();
        }
        $plan->delete();
        toastr()->success(__('Deleted Successfully'));
        return back();
    }
}