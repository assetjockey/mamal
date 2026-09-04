<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        if (!userAuthInfo()->subscription && $request->st != "subscribe") {
            return redirect(route('user.plans') . '?st=subscribe');
        }
        if (userAuthInfo()->subscription && $request->st) {
            return redirect()->route('user.plans');
        }
        $monthlyPlans = Plan::where('interval', 0)->get();
        $yearlyPlans = Plan::where('interval', 1)->get();
        $lifetimePlans = Plan::where('interval', 2)->get();
        return view('frontend.user.plans.index', [
            'monthlyPlans' => $monthlyPlans,
            'yearlyPlans' => $yearlyPlans,
            'lifetimePlans' => $lifetimePlans,
        ]);
    }
}
