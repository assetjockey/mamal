<?php

namespace App\Http\Controllers;

use App\Models\Stat;
use App\Traits\DateRangeTrait;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use DateRangeTrait;

    /**
     * Show the Dashboard page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        // If the user previously selected a plan
        if (!empty($request->session()->get('plan_redirect'))) {
            return redirect()->route('checkout.index', ['id' => $request->session()->get('plan_redirect')['id'], 'interval' => $request->session()->get('plan_redirect')['interval']]);
        }

        $now = Carbon::now();
        $range = $this->range();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['domain']) ? $request->input('search_by') : 'domain';
        $favorite = $request->input('favorite');
        $sortBy = in_array($request->input('sort_by'), ['id', 'domain']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10]) ? $request->input('per_page') : 10;

        $websites = Website::withCount([
                'visitors' => function ($query) use ($range, $request, $now) {
                    $query->whereBetween('created_at', [
                        Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ]);
                },
                'pageviews' => function ($query) use ($range, $request, $now) {
                    $query->whereBetween('created_at', [
                        Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ]);
                }
            ])
            ->where('user_id', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchDomain($search);
            })
            ->when(isset($favorite) && is_numeric($favorite), function ($query) use ($favorite) {
                return $query->ofFavorite($favorite);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'favorite' => $favorite, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('dashboard.index', ['range' => $range, 'now' => $now, 'websites' => $websites]);
    }
}
