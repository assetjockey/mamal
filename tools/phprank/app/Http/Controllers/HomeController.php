<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Plan;
use App\Models\Tool;
use App\Traits\ReportTrait;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use ReportTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // If the app is not installed
        if (!config()->has('settings.title')) {
            return redirect()->route('install');
        }

        // If the user is logged-in, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // If there's a custom site index
        if (config('settings.index')) {
            return redirect()->to(config('settings.index'), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        // If there's a payment processor enabled
        if (paymentProcessors()) {
            $plans = Plan::where('visibility', 1)->orderBy('position')->orderBy('id')->get();
        } else {
            $plans = null;
        }

        $tools = Tool::when(!config('settings.gcs'), function ($query) {
                return $query->whereNotIn('slug', ['serp-checker', 'indexed-pages-checker']);
            })
            ->when(!config('settings.ke'), function ($query) {
                return $query->whereNotIn('slug', ['keyword-research']);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('home.index', ['plans' => $plans, 'tools' => $tools]);
    }

    /**
     * Store the Report.
     *
     * @param StoreLinkRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeReport(StoreReportRequest $request)
    {
        if (!config('settings.report_guest')) {
            abort(404);
        }

        try {
            $report = $this->reportStore($request);
        } catch (\Exception $e) {
            return back()->with('error', __('An unexpected error has occurred, please try again.') . $e->getMessage())->withInput();
        }

        return redirect()->route('reports.show', ['id' => $report->id]);
    }
}
