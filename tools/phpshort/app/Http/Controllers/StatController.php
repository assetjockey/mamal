<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateLinkStatsPasswordRequest;
use App\Models\Link;
use App\Models\Stat;
use App\Models\User;
use App\Services\DateRangeService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use League\Csv\Bom as CsvBom;
use League\Csv\Writer as CsvWriter;
use SplTempFileObject;

class StatController extends Controller
{
    /**
     * The date range service instance.
     */
    private DateRangeService $dateRangeService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DateRangeService $dateRangeService)
    {
        $this->dateRangeService = $dateRangeService;
    }

    /**
     * Show the overview stats page.
     */
    public function index(Request $request, string $id): Response|View
    {
        $link = Link::where('id', '=', $id)->firstOrFail();

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));

        if (!$link->user) {
            return view('stats.guest', ['link' => $link, 'now' => $now]);
        }

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $rangeMap = $this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format($dateRange['format']), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format($dateRange['format']), $dateRange['unit'], $dateRange['format'], 0);

        $clicksMap = Stat::select([
                DB::raw("date_format(CONVERT_TZ(`created_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, COUNT(*) as `aggregate`")
            ])
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('date_result')
            ->orderBy('date_result', 'asc')
            ->get()
            ->mapWithKeys(function ($row) use ($request, $dateRange) {
                return [strval(Carbon::createFromFormat($dateRange['format'], $row->date_result)->format($dateRange['format'])) => $row->aggregate];
            })->all();

        // Merge the results with the pre-calculated possible time range
        $clicksMap = array_replace($rangeMap, $clicksMap);

        $totalClicks = 0;
        foreach ($clicksMap as $value) {
            $totalClicks = $totalClicks + $value;
        }

        $totalClicksOld = Stat::where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from_old'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to_old'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])->count();

        $referrers = $this->getReferrers($request, $link, $dateRange, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $countries = $this->getCountries($request, $link, $dateRange, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $browsers = $this->getBrowsers($request, $link, $dateRange, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        $operatingSystems = $this->getOperatingSystems($request, $link, $dateRange, null, null, 'count', 'desc')
            ->limit(5)
            ->get();

        return view('stats.overview', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'referrers' => $referrers, 'clicksMap' => $clicksMap, 'countries' => $countries, 'browsers' => $browsers, 'operatingSystems' => $operatingSystems, 'totalClicks' => $totalClicks, 'totalClicksOld' => $totalClicksOld]);
    }

    /**
     * Show the referrers stats page.
     */
    public function referrers(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $referrers = $this->getReferrers($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.referrers', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.referrers.export', 'referrers' => $referrers, 'total' => $total]);
    }

    /**
     * Show the countries stats page.
     */
    public function countries(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $countriesChart = $this->getCountries($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->get();

        $countries = $this->getCountries($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.countries', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.countries.export', 'countries' => $countries, 'countriesChart' => $countriesChart, 'total' => $total]);
    }

    /**
     * Show the cities stats page.
     */
    public function cities(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $cities = $this->getCities($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.cities', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.cities.export', 'cities' => $cities, 'total' => $total]);
    }

    /**
     * Show the languages stats page.
     */
    public function languages(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $languages = $this->getLanguages($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.languages', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.languages.export', 'languages' => $languages, 'total' => $total]);
    }

    /**
     * Show the operating systems stats page.
     */
    public function operatingSystems(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $operatingSystems = $this->getOperatingSystems($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.operating-systems', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.operating-systems.export', 'operatingSystems' => $operatingSystems, 'total' => $total]);
    }

    /**
     * Show the browsers stats page.
     */
    public function browsers(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $browsers = $this->getBrowsers($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.browsers', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.browsers.export', 'browsers' => $browsers, 'total' => $total]);
    }

    /**
     * Show the devices stats page.
     */
    public function devices(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $total = Stat::selectRaw('COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->first();

        $devices = $this->getDevices($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('stats.devices', ['link' => $link, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'stats.devices.export', 'devices' => $devices, 'total' => $total]);
    }

    /**
     * Export the referrers stats.
     */
    public function exportReferrers(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Referrers'), $dateRange, __('URL'), __('Clicks'), $this->getReferrers($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.referrers', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the countries stats.
     */
    public function exportCountries(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Countries'), $dateRange, __('Name'), __('Clicks'), $this->getCountries($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.countries', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the cities stats.
     */
    public function exportCities(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Cities'), $dateRange, __('Name'), __('Clicks'), $this->getCities($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.cities', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the languages stats.
     */
    public function exportLanguages(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Languages'), $dateRange, __('Name'), __('Clicks'), $this->getLanguages($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.languages', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the operating systems stats.
     */
    public function exportOperatingSystems(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Operating systems'), $dateRange, __('Name'), __('Clicks'), $this->getOperatingSystems($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.operating-systems', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the browsers stats.
     */
    public function exportBrowsers(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        return $this->exportCsv($request, $link, __('Browsers'), $dateRange, __('Name'), __('Clicks'), $this->getBrowsers($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.browsers', ['id' => $link->id] + $request->query()));
    }

    /**
     * Export the devices stats.
     */
    public function exportDevices(Request $request, string $id): Response|View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '<>', 0]])->firstOrFail();

        if (Gate::denies('view', [$link])) {
            if ($link->isPrivate()) {
                abort(403);
            }

            return view('stats.password', ['link' => $link]);
        }

        $dateRange = $this->dateRangeService->build(($request->input('from') ? null : $link->created_at));
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
        $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        
        return $this->exportCsv($request, $link, __('Devices'), $dateRange, __('Type'), __('Clicks'), $this->getDevices($request, $link, $dateRange, $search, $searchBy, $sortBy, $sort), route('stats.devices', ['id' => $link->id] + $request->query()));
    }

    /**
     * Get the referrers.
     */
    private function getReferrers(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`referrer` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'referrer');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the countries.
     */
    private function getCountries(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`country` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'country');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the cities.
     */
    private function getCities(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`city` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'city');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the languages.
     */
    private function getLanguages(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`language` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'language');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the operating systems.
     */
    private function getOperatingSystems(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`operating_system` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'operating_system');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the browsers.
     */
    private function getBrowsers(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`browser` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'browser');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Get the devices.
     */
    private function getDevices(Request $request, Link $link, array $dateRange, ?string $search = null, ?string $searchBy = null, ?string $sortBy = null, ?string $sort = null): Builder
    {
        return Stat::selectRaw('`device` as `value`, COUNT(1) as `count`')
            ->where('link_id', '=', $link->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->search($search, 'device');
            })
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->groupBy('value')
            ->orderBy($sortBy, $sort);
    }

    /**
     * Export data in CSV format.
     */
    private function exportCsv(Request $request, Link $link, string $title, array $dateRange, string $name, string $count, Builder $results, string $url): Response
    {
        if ($link->user->cannot('dataExport', [User::class])) {
            abort(403);
        }

        $now = Carbon::now();

        $content = CsvWriter::from(new SplTempFileObject);

        $content->insertOne([__('Short URL'), $link->displayShortUrl]);
        $content->insertOne([__('Type'), $title]);
        $content->insertOne([__('Interval'), $dateRange['from'] . ' - ' . $dateRange['to']]);
        $content->insertOne([__('Date'), $now->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]);
        $content->insertOne([__('URL'), $url]);
        $content->insertOne([__(' ')]);

        $content->insertOne([__('Clicks'), Stat::where('link_id', '=', $link->id)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->count()]);
        $content->insertOne([__(' ')]);

        $content->insertOne([__($name), __($count)]);
        foreach ($results->get() as $result) {
            $content->insertOne($result->toArray());
        }

        $content->setOutputBOM(CsvBom::Utf8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle([$link->alias, str_replace(['http://', 'https://'], '', ($link->domain->url ?? config('app.url'))), $title, $dateRange['from'], $dateRange['to'], config('settings.title')]) . '.csv"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /**
     * Validate the website password.
     */
    public function validatePassword(ValidateLinkStatsPasswordRequest $request, string $id): RedirectResponse
    {
        $link = Link::findOrFail($id);

        session([$link->passwordSessionKey() => true]);

        return redirect()->back();
    }
}
