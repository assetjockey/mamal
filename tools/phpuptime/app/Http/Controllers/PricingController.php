<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Show the Pricing page.
     */
    public function index(): View
    {
        $plans = Plan::where('visibility', 1)
            ->whereNot(function ($query) {
                $query->where('amount_month', '=', 0)
                    ->where('amount_year', '=', 0)
                    ->where('id', '>', 1);
            })
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('pricing.index', ['plans' => $plans]);
    }
}
