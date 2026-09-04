<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Monitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the Dashboard page.
     */
    public function index(Request $request): RedirectResponse|View
    {
        if (!empty($request->session()->get('plan_redirect'))) {
            return redirect()->route('checkout.index', ['id' => $request->session()->get('plan_redirect')['id'], 'interval' => $request->session()->get('plan_redirect')['interval']]);
        }

        $latestMonitors = Monitor::where('user_id', $request->user()->id)->orderBy('id', 'desc')->limit(5)->get();

        $latestIncidents = Incident::with('monitor')
            ->where('user_id', $request->user()->id)
            ->orderByRaw('(`ended_at` IS NULL) desc')
            ->orderBy('ended_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', ['latestMonitors' => $latestMonitors, 'latestIncidents' => $latestIncidents]);
    }
}
