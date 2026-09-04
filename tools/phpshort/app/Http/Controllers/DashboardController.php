<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!empty($request->session()->get('plan_redirect'))) {
            return redirect()->route('checkout.index', ['id' => $request->session()->get('plan_redirect')['id'], 'interval' => $request->session()->get('plan_redirect')['interval']]);
        }

        $latestLinks = Link::with('domain')->where('user_id', '=', $request->user()->id)->orderBy('id', 'desc')->limit(5)->get();

        $popularLinks = Link::with('domain')->where('user_id', '=', $request->user()->id)->orderBy('clicks_count', 'desc')->limit(5)->get();

        return view('dashboard.index', ['user' => $request->user(), 'latestLinks' => $latestLinks, 'popularLinks' => $popularLinks]);
    }
}
