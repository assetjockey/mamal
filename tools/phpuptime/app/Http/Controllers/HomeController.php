<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Exception;
use GeoIp2\Database\Reader as GeoIP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index(): RedirectResponse|View
    {
        if (!config()->has('settings.title')) {
            return redirect()->route('install');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (config('settings.homepage_redirect_url')) {
            return redirect()->to(config('settings.homepage_redirect_url'), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        $plans = null;
        if (enabledPaymentProcessors()) {
            $plans = Plan::where('visibility', 1)->orderBy('position')->orderBy('id')->get();
        }

        try {
            $location = (new GeoIP(storage_path('app/geoip/GeoLite2-City.mmdb')))->city(getHostIp());
        } catch (Exception) {
            $location = null;
        }

        return view('home.index', ['plans' => $plans, 'location' => $location]);
    }
}
