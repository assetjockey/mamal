<?php

namespace App\Http\Controllers;

use App\Models\Domain;
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

        $domains = Domain::select('name')->where('user_id', '=', 0)
            ->whereNotIn('id', [config('settings.short_domain_id')])
            ->get()
            ->map(function ($item) {
                return $item->name;
            })
            ->toArray();

        return view('pricing.index', ['plans' => $plans, 'domains' => $domains]);
    }
}
