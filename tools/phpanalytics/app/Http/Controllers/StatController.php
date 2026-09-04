<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateWebsitePasswordRequest;
use App\Models\Event;
use App\Traits\DateRangeTrait;
use App\Models\Website;
use App\Models\Stat;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv as CSV;

class StatController extends Controller
{
    use DateRangeTrait;

    /**
     * Show the Overview stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        }

        $now = Carbon::now();
        $range = $this->range();

        $rangeMap = $this->calcAllDates(Carbon::createFromFormat('Y-m-d', $range['from'])->format($range['format']), Carbon::createFromFormat('Y-m-d', $range['to'])->format($range['format']), $range['unit'], $range['format'], 0);

        $visitorsMap = Stat::select([
                DB::raw("date_format(CONVERT_TZ(`created_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $range['format']) . "') as `date_result`, COUNT(*) as `aggregate`")
            ])
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('date_result')
            ->orderBy('date_result', 'asc')
            ->get()
            ->mapWithKeys(function ($result) use ($request, $range) {
                return [strval(Carbon::createFromFormat($range['format'], $result->date_result)->format($range['format'])) => $result->aggregate];
            })->all();

        // Merge the results with the pre-calculated possible time range
        $visitorsMap = array_replace($rangeMap, $visitorsMap);

        $totalVisitors = 0;
        foreach ($visitorsMap as $value) {
            $totalVisitors = $totalVisitors + $value;
        }

        $pageviewsMap = Stat::select([
                DB::raw("date_format(CONVERT_TZ(`created_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $range['format']) . "') as `date_result`, COUNT(*) as `aggregate`")
            ])
            ->where('website_id', '=', $website->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('date_result')
            ->orderBy('date_result', 'asc')
            ->get()
            ->mapWithKeys(function ($row) use ($request, $range) {
                return [strval(Carbon::createFromFormat($range['format'], $row->date_result)->format($range['format'])) => $row->aggregate];
            })->all();

        // Merge the results with the pre-calculated possible time range
        $pageviewsMap = array_replace($rangeMap, $pageviewsMap);

        $totalPageviews = 0;
        foreach ($pageviewsMap as $value) {
            $totalPageviews = $totalPageviews + $value;
        }

        $totalVisitorsOld = Stat::where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from_old'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to_old'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])->count();

        $totalPageviewsOld = Stat::where('website_id', '=', $website->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from_old'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to_old'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])->count();

        $pages = $this->getPages($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $totalReferrers = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $referrers = $this->getReferrers($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $countries = $this->getCountries($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $browsers = $this->getBrowsers($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $operatingSystems = $this->getOperatingSystems($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $events = $this->getEvents($request, $website, $range, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        return view('stats.container', ['view' => 'overview', 'website' => $website, 'now' => $now, 'range' => $range, 'referrers' => $referrers, 'pages' => $pages, 'visitorsMap' => $visitorsMap, 'pageviewsMap' => $pageviewsMap, 'countries' => $countries, 'browsers' => $browsers, 'operatingSystems' => $operatingSystems, 'events' => $events, 'totalVisitors' => $totalVisitors, 'totalPageviews' => $totalPageviews, 'totalVisitorsOld' => $totalVisitorsOld, 'totalPageviewsOld' => $totalPageviewsOld, 'totalReferrers' => $totalReferrers]);
    }

    /**
     * Show the Realtime stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws \Throwable
     */
    public function realtime(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();

        if ($request->wantsJson()) {
            // Date ranges
            $to = $now;
            $from = $to->copy()->subMinutes(1);
            $to_old = (clone $from)->subSecond(1);
            $from_old = (clone $to_old)->subMinutes(1);

            // Get the available dates
            $visitorsMap = $pageviewsMap = $this->calcAllDates($from, $to, 'second', 'Y-m-d H:i:s', ['count' => 0]);

            $visitors = Stat::selectRaw('COUNT(`website_id`) as `count`, `created_at`')
                ->where('website_id', '=', $website->id)
                ->where('unique', '=', 1)
                ->whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                ->groupBy('created_at')
                ->get();

            $pageviews = Stat::selectRaw('COUNT(`website_id`) as `count`, `created_at`')
                ->where([['website_id', '=', $website->id]])
                ->whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                ->groupBy('created_at')
                ->get();

            $recent = Stat::where([['website_id', '=', $website->id]])
                ->whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $visitorsOld = Stat::where('website_id', '=', $website->id)
                ->where('unique', '=', 1)
                ->whereBetween('created_at', [$from_old->format('Y-m-d H:i:s'), $to_old->format('Y-m-d H:i:s')])
                ->count();

            $pageviewsOld = Stat::where([['website_id', '=', $website->id]])
                ->whereBetween('created_at', [$from_old->format('Y-m-d H:i:s'), $to_old->format('Y-m-d H:i:s')])
                ->count();

            $totalVisitors = $totalPageviews = 0;

            // Map the values to each date
            foreach ($visitors as $visitor) {
                // Map the value to each date
                $visitorsMap[$visitor->created_at->format('Y-m-d H:i:s')] = $visitor->count;
                $totalVisitors = $totalVisitors + $visitor->count;
            }

            foreach ($pageviews as $pageview) {
                // Map the value to each date
                $pageviewsMap[$pageview->created_at->format('Y-m-d H:i:s')] = $pageview->count;
                $totalPageviews = $totalPageviews + $pageview->count;
            }

            $visitorsCount = $pageviewsCount = [];
            foreach ($visitorsMap as $key => $value) {
                // Remap the key
                $visitorsCount[Carbon::createFromDate($key)->diffForHumans(['options' => Carbon::JUST_NOW])] = $value;
            }

            foreach ($pageviewsMap as $key => $value) {
                // Remap the key
                $pageviewsCount[Carbon::createFromDate($key)->diffForHumans(['options' => Carbon::JUST_NOW])] = $value;
            }

            return response()->json([
                'visitors' => $visitorsCount,
                'pageviews' => $pageviewsCount,
                'visitors_growth' => view('stats.growth', ['growthCurrent' => $totalVisitors, 'growthPrevious' => $visitorsOld])->render(),
                'pageviews_growth' => view('stats.growth', ['growthCurrent' => $totalPageviews, 'growthPrevious' => $pageviewsOld])->render(),
                'recent' => view('stats.recent', ['website' => $website, 'range' => $range, 'recent' => $recent])->render(),
                'status' => 200
            ], 200);
        }

        return view('stats.container', ['view' => 'realtime', 'website' => $website, 'now' => $now, 'range' => $range]);
    }

    /**
     * Show the Pages stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'page');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $pages = $this->getPages($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'pages', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.pages', 'pages' => $pages, 'total' => $total]);
    }

    /**
     * Show the Landing Pages stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function landingPages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $landingPages = $this->getLandingPages($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'landing-pages', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.landing_pages', 'landingPages' => $landingPages, 'total' => $total]);
    }

    /**
     * Show the Referrers stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function referrers(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $referrers = $this->getReferrers($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'referrers', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.referrers', 'referrers' => $referrers, 'total' => $total]);
    }

    /**
     * Show the Search Engines stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchEngines(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');
        $websites = $this->getSearchEnginesList();

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('referrer')
            ->whereIn('referrer', $websites)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $searchEngines = $this->getSearchEngines($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'search-engines', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.search_engines', 'searchEngines' => $searchEngines, 'total' => $total]);
    }

    /**
     * Show the Social Networks stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function socialNetworks(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');
        $websites = $this->getSocialNetworksList();

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('referrer')
            ->whereIn('referrer', $websites)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $socialNetworks = $this->getSocialNetworks($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'social-networks', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.social_networks', 'socialNetworks' => $socialNetworks, 'total' => $total]);
    }

    /**
     * Show the Campaigns stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function campaigns(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('campaign')
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $campaigns = $this->getCampaigns($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'campaigns', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.campaigns', 'campaigns' => $campaigns, 'total' => $total]);
    }

    /**
     * Show the Continents stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function continents(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $continents = $this->getContinents($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'continents', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.continents', 'continents' => $continents, 'total' => $total]);
    }

    /**
     * Show the Countries stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function countries(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $countriesChart = $this->getCountries($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->get();

        $countries = $this->getCountries($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'countries', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.countries', 'countries' => $countries, 'countriesChart' => $countriesChart, 'total' => $total]);
    }

    /**
     * Show the Cities stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cities(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $cities = $this->getCities($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'cities', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.cities', 'cities' => $cities, 'total' => $total]);
    }

    /**
     * Show the Languages stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function languages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $languages = $this->getLanguages($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'languages', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.languages', 'languages' => $languages, 'total' => $total]);
    }

    /**
     * Show the Operating Systems stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function operatingSystems(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $operatingSystems = $this->getOperatingSystems($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'operating-systems', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.operating_systems', 'operatingSystems' => $operatingSystems, 'total' => $total]);
    }

    /**
     * Show the Browsers stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function browsers(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $browsers = $this->getBrowsers($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'browsers', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.browsers', 'browsers' => $browsers, 'total' => $total]);
    }

    /**
     * Show the Screen Resolutions stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function screenResolutions(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $screenResolutions = $this->getScreenResolutions($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'screen-resolutions', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.screen_resolutions', 'screenResolutions' => $screenResolutions, 'total' => $total]);
    }

    /**
     * Show the Devices stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function devices(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $devices = $this->getDevices($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'devices', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.devices', 'devices' => $devices, 'total' => $total]);
    }

    /**
     * Show the Events stats page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function events(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $now = Carbon::now();
        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Event::selectRaw('COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $events = $this->getEvents($request, $website, $range, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $range['from'], 'to' => $range['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.container', ['view' => 'events', 'website' => $website, 'now' => $now, 'range' => $range, 'export' => 'stats.export.events', 'events' => $events, 'total' => $total]);
    }

    /**
     * Export the Pages stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportPages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Pages'), $range, __('URL'), __('Pageviews'), $this->getPages($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Landing Pages stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportLandingPages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Landing pages'), $range, __('URL'), __('Visitors'), $this->getLandingPages($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Referrers stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportReferrers(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Referrers'), $range, __('Website'), __('Visitors'), $this->getReferrers($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Search Engines stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportSearchEngines(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Search engines'), $range, __('Website'), __('Visitors'), $this->getSearchEngines($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Social Networks stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportSocialNetworks(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Social networks'), $range, __('Website'), __('Visitors'), $this->getSocialNetworks($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Campaigns stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportCampaigns(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Campaigns'), $range, __('Name'), __('Visitors'), $this->getCampaigns($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Continents stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportContinents(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Continents'), $range, __('Name'), __('Visitors'), $this->getContinents($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Countries stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportCountries(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Countries'), $range, __('Name'), __('Visitors'), $this->getCountries($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Cities stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportCities(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Cities'), $range, __('Name'), __('Visitors'), $this->getCities($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Languages stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportLanguages(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Languages'), $range, __('Name'), __('Visitors'), $this->getLanguages($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Operating Systems stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportOperatingSystems(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Operating systems'), $range, __('Name'), __('Visitors'), $this->getOperatingSystems($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Browsers stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportBrowsers(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Browsers'), $range, __('Name'), __('Visitors'), $this->getBrowsers($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Screen Resolutions stats
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportScreenResolutions(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Screen resolutions'), $range, __('Size'), __('Visitors'), $this->getScreenResolutions($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Devices stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportDevices(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Devices'), $range, __('Type'), __('Visitors'), $this->getDevices($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Export the Events stats.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    public function exportEvents(Request $request, $id)
    {
        $website = Website::where('domain', $id)->firstOrFail();

        if ($this->guard($website)) {
            return view('stats.password', ['website' => $website]);
        };

        $range = $this->range();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCSV($request, $website, __('Events'), $range, __('Name'), __('Completions'), $this->getEvents($request, $website, $range, $search, $searchBy, $sortBy, $sort)->get());
    }

    /**
     * Get the Pages.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getPages($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`page` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'page');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Landing Pages.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getLandingPages($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`page` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'page');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Referrers.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getReferrers($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`referrer` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'referrer');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Search Engines.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getSearchEngines($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        $websites = $this->getSearchEnginesList();

        return Stat::selectRaw('`referrer` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('referrer')
            ->whereIn('referrer', $websites)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'referrer');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Social Networks.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getSocialNetworks($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        $websites = $this->getSocialNetworksList();

        return Stat::selectRaw('`referrer` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('referrer')
            ->whereIn('referrer', $websites)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'referrer');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Campaigns.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getCampaigns($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`campaign` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereNotNull('campaign')
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'utm_campaign');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Continents.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getContinents($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`continent` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'continent');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Countries.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getCountries($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`country` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'country');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Cities.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getCities($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`city` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'city');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Languages.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getLanguages($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`language` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'language');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Operating Systems.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getOperatingSystems($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`operating_system` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'operating_system');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Browsers.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getBrowsers($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`browser` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'browser');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Screen Resolutions.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getScreenResolutions($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`screen_resolution` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'screen_resolution');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Devices.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getDevices($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Stat::selectRaw('`device` as `value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'device');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the Events.
     *
     * @param $request
     * @param $website
     * @param $range
     * @param null $search
     * @param null $sort
     * @return mixed
     */
    private function getEvents($request, $website, $range, $search = null, $searchBy = null, $sortBy = null, $sort = null)
    {
        return Event::selectRaw('`value`, COUNT(1) as `count`')
            ->where('website_id', '=', $website->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search);
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * List of Social Networks domains.
     *
     * @return string[]
     */
    private function getSocialNetworksList()
    {
        return ['l.facebook.com', 't.co', 'l.instagram.com', 'out.reddit.com', 'www.youtube.com', 'away.vk.com', 't.umblr.com', 'www.pinterest.com', 'www.snapchat.com'];
    }

    /**
     * List of Search Engine domains.
     *
     * @return string[]
     */
    private function getSearchEnginesList()
    {
        return ['www.google.com', 'www.google.com', 'www.google.ad', 'www.google.ae', 'www.google.com.af', 'www.google.com.ag', 'www.google.com.ai', 'www.google.al', 'www.google.am', 'www.google.co.ao', 'www.google.com.ar', 'www.google.as', 'www.google.at', 'www.google.com.au', 'www.google.az', 'www.google.ba', 'www.google.com.bd', 'www.google.be', 'www.google.bf', 'www.google.bg', 'www.google.com.bh', 'www.google.bi', 'www.google.bj', 'www.google.com.bn', 'www.google.com.bo', 'www.google.com.br', 'www.google.bs', 'www.google.bt', 'www.google.co.bw', 'www.google.by', 'www.google.com.bz', 'www.google.ca', 'www.google.cd', 'www.google.cf', 'www.google.cg', 'www.google.ch', 'www.google.ci', 'www.google.co.ck', 'www.google.cl', 'www.google.cm', 'www.google.cn', 'www.google.com.co', 'www.google.co.cr', 'www.google.com.cu', 'www.google.cv', 'www.google.com.cy', 'www.google.cz', 'www.google.de', 'www.google.dj', 'www.google.dk', 'www.google.dm', 'www.google.com.do', 'www.google.dz', 'www.google.com.ec', 'www.google.ee', 'www.google.com.eg', 'www.google.es', 'www.google.com.et', 'www.google.fi', 'www.google.com.fj', 'www.google.fm', 'www.google.fr', 'www.google.ga', 'www.google.ge', 'www.google.gg', 'www.google.com.gh', 'www.google.com.gi', 'www.google.gl', 'www.google.gm', 'www.google.gr', 'www.google.com.gt', 'www.google.gy', 'www.google.com.hk', 'www.google.hn', 'www.google.hr', 'www.google.ht', 'www.google.hu', 'www.google.co.id', 'www.google.ie', 'www.google.co.il', 'www.google.im', 'www.google.co.in', 'www.google.iq', 'www.google.is', 'www.google.it', 'www.google.je', 'www.google.com.jm', 'www.google.jo', 'www.google.co.jp', 'www.google.co.ke', 'www.google.com.kh', 'www.google.ki', 'www.google.kg', 'www.google.co.kr', 'www.google.com.kw', 'www.google.kz', 'www.google.la', 'www.google.com.lb', 'www.google.li', 'www.google.lk', 'www.google.co.ls', 'www.google.lt', 'www.google.lu', 'www.google.lv', 'www.google.com.ly', 'www.google.co.ma', 'www.google.md', 'www.google.me', 'www.google.mg', 'www.google.mk', 'www.google.ml', 'www.google.com.mm', 'www.google.mn', 'www.google.ms', 'www.google.com.mt', 'www.google.mu', 'www.google.mv', 'www.google.mw', 'www.google.com.mx', 'www.google.com.my', 'www.google.co.mz', 'www.google.com.na', 'www.google.com.ng', 'www.google.com.ni', 'www.google.ne', 'www.google.nl', 'www.google.no', 'www.google.com.np', 'www.google.nr', 'www.google.nu', 'www.google.co.nz', 'www.google.com.om', 'www.google.com.pa', 'www.google.com.pe', 'www.google.com.pg', 'www.google.com.ph', 'www.google.com.pk', 'www.google.pl', 'www.google.pn', 'www.google.com.pr', 'www.google.ps', 'www.google.pt', 'www.google.com.py', 'www.google.com.qa', 'www.google.ro', 'www.google.ru', 'www.google.rw', 'www.google.com.sa', 'www.google.com.sb', 'www.google.sc', 'www.google.se', 'www.google.com.sg', 'www.google.sh', 'www.google.si', 'www.google.sk', 'www.google.com.sl', 'www.google.sn', 'www.google.so', 'www.google.sm', 'www.google.sr', 'www.google.st', 'www.google.com.sv', 'www.google.td', 'www.google.tg', 'www.google.co.th', 'www.google.com.tj', 'www.google.tl', 'www.google.tm', 'www.google.tn', 'www.google.to', 'www.google.com.tr', 'www.google.tt', 'www.google.com.tw', 'www.google.co.tz', 'www.google.com.ua', 'www.google.co.ug', 'www.google.co.uk', 'www.google.com.uy', 'www.google.co.uz', 'www.google.com.vc', 'www.google.co.ve', 'www.google.vg', 'www.google.co.vi', 'www.google.com.vn', 'www.google.vu', 'www.google.ws', 'www.google.rs', 'www.google.co.za', 'www.google.co.zm', 'www.google.co.zw', 'www.google.cat', 'www.bing.com', 'search.yahoo.com', 'uk.search.yahoo.com', 'de.search.yahoo.com', 'fr.search.yahoo.com', 'es.search.yahoo.com', 'search.aol.co.uk', 'search.aol.com', 'duckduckgo.com', 'www.baidu.com', 'yandex.ru', 'www.ecosia.org', 'search.lycos.com', 'www.qwant.com', 'search.brave.com', 'search.naver.com', 'www.sogou.com'];
    }

    /**
     * Export data in CSV format.
     *
     * @param $request
     * @param $website
     * @param $title
     * @param $range
     * @param $name
     * @param $count
     * @param $results
     * @return CSV\Writer
     * @throws CSV\CannotInsertRecord
     */
    private function exportCSV($request, $website, $title, $range, $name, $count, $results)
    {
        if ($website->user->cannot('dataExport', ['App\Models\User'])) {
            abort(403);
        }

        $now = Carbon::now();

        $content = CSV\Writer::createFromFileObject(new \SplTempFileObject);

        // Generate the header
        $content->insertOne([__('Website'), $website->domain]);
        $content->insertOne([__('Type'), $title]);
        $content->insertOne([__('Interval'), $range['from'] . ' - ' . $range['to']]);
        $content->insertOne([__('Date'), $now->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]);
        $content->insertOne([__('URL'), $request->fullUrl()]);
        $content->insertOne([__(' ')]);

        // Generate the summary
        $content->insertOne([__('Visitors'), Stat::where('website_id', '=', $website->id)
            ->where('unique', '=', 1)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->count()]);
        $content->insertOne([__('Pageviews'), Stat::where('website_id', '=', $website->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $range['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $range['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->count()]);
        $content->insertOne([__(' ')]);

        // Generate the content
        $content->insertOne([__($name), __($count)]);
        foreach ($results as $result) {
            $content->insertOne($result->toArray());
        }

        // Set the output BOM
        $content->setOutputBOM(CSV\Reader::BOM_UTF8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle([$website->domain, $title, $range['from'], $range['to'], config('settings.title')]) . '.csv"'
        ]);
    }

    /**
     * Validate the Model's password.
     *
     * @param ValidateWebsitePasswordRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validatePassword(ValidateWebsitePasswordRequest $request, $id)
    {
        session([md5($id) => true]);
        return redirect()->back();
    }

    /**
     * Guard the Model.
     *
     * @param $model
     * @return bool
     */
    private function guard($model)
    {
        // If the model is not set to public
        if($model->privacy !== 0) {
            $user = Auth::user();

            // If the model's privacy is set to private
            if ($model->privacy == 1) {
                // If the user is not authenticated
                // Or if the user is not the owner of the model and not an admin
                if ($user == null || $user->id != $model->user_id && $user->role != 1) {
                    abort(403);
                }
            }

            // If the model's privacy is set to password
            if ($model->privacy == 2) {
                // If there's no password validation in the current session
                if (!session(md5($model->domain))) {
                    // If the user is not authenticated
                    // Or if the user is not the owner of the link and not an admin
                    if ($user == null || $user->id != $model->user_id && $user->role != 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
