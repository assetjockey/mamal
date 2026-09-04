<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tool;

class PricingController extends Controller
{
    /**
     * Show the Pricing page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $plans = Plan::where('visibility', 1)->orderBy('position')->orderBy('id')->get();

        $tools = Tool::when(!config('settings.gcs'), function ($query) {
                return $query->whereNotIn('slug', ['serp-checker', 'indexed-pages-checker']);
            })
            ->when(!config('settings.ke'), function ($query) {
                return $query->whereNotIn('slug', ['keyword-research']);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('pricing.index', ['plans' => $plans, 'tools' => $tools]);
    }
}
