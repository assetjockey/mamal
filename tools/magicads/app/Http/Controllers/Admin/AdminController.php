<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Whether GA4 is fully configured (property id + a readable credentials
     * file). Mirrors the runtime config hydrated in AppServiceProvider.
     */
    public static function gaConfigured(): bool
    {
        $property = config('laravel-google-analytics.property_id');
        $credentials = config('laravel-google-analytics.service_account_credentials_json');

        return ! empty($property) && ! empty($credentials) && is_file($credentials);
    }

    /**
     * GA4 dashboard payload, fetched lazily over AJAX so the slow Google
     * Analytics round-trips never block the initial dashboard render.
     */
    public function analytics(Request $request): JsonResponse
    {
        if (! $request->ajax() || ! self::gaConfigured()) {
            return response()->json(['status' => 400]);
        }

        try {
            $google = new GoogleAnalyticsService();

            return response()->json([
                'status'                 => 200,
                'google_users'           => json_encode($google->users()),
                'google_user_sessions'   => json_encode($google->userSessions()),
                'traffic_label'          => json_encode($google->getTrafficLabels()),
                'traffic_data'           => json_encode($google->getTrafficData()),
                'google_average_session' => $google->averageSessionDuration(),
                'google_sessions'        => number_format((float) $google->sessions()),
                'google_session_views'   => number_format((float) $google->sessionViews(), 2),
                'google_bounce_rate'     => $google->bounceRate(),
                'google_countries'       => $this->countryList($google),
                'map_countries'          => $this->mapCountries($google),
            ]);
        } catch (\Throwable $e) {
            Log::error('Google Analytics dashboard error: '.$e->getMessage());

            return response()->json(['status' => 500]);
        }
    }

    /**
     * Country leaderboard markup for the "Top Visitor Countries" panel — flag,
     * name, brand-gradient progress bar, and user count. Flags live under
     * public/assets/img/flags/{cc}.svg (lowercase ISO code).
     */
    protected function countryList(GoogleAnalyticsService $google): string
    {
        $countries = $google->userCountries();
        $total = max(1, (int) $google->userCountriesTotal());
        $max = 0;

        foreach ($countries as $c) {
            $max = max($max, (int) $c['totalUsers']);
        }
        $max = max(1, $max);

        $list = '';

        foreach ($countries as $data) {
            $code = strtolower($data['countryId'] ?? '');
            $flag = asset('assets/img/flags/'.$code.'.svg');
            $width = max(4, round(((int) $data['totalUsers'] / $max) * 100));
            $name = e($data['country'] ?? __('Unknown'));
            $count = number_format((int) $data['totalUsers']);

            $list .= '<li class="flex items-center gap-3 py-2">'
                .'<img alt="'.$name.'" class="w-6 h-4 rounded-sm object-cover shrink-0" src="'.$flag.'" onerror="this.style.visibility=\'hidden\'">'
                .'<span class="text-xs font-medium text-zinc-600 dark:text-zinc-300 w-24 truncate" title="'.$name.'">'.$name.'</span>'
                .'<div class="flex-1 h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">'
                .'<div class="h-full rounded-full" style="width:'.$width.'%;background:linear-gradient(120deg,#4F46E5,#0F172A);"></div>'
                .'</div>'
                .'<span class="text-[11px] font-semibold text-zinc-500 w-10 text-right">'.$count.'</span>'
                .'</li>';
        }

        return $list;
    }

    /**
     * Country => users map keyed by full country name, consumed by the
     * GeoChart on the dashboard.
     *
     * @return array<string, int>
     */
    protected function mapCountries(GoogleAnalyticsService $google): array
    {
        $out = [];

        foreach ($google->userCountries() as $data) {
            $name = $data['country'] ?? null;
            if ($name) {
                $out[$name] = (int) $data['totalUsers'];
            }
        }

        return $out;
    }
}
