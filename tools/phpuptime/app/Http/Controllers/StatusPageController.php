<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStatusPageRequest;
use App\Http\Requests\UpdateStatusPageRequest;
use App\Http\Requests\ValidateStatusPagePasswordRequest;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorStatusPage;
use App\Models\StatusPage;
use App\Services\DateRangeService;
use App\Services\StatusPageService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StatusPageController extends Controller
{
    /**
     * The status page service instance.
     */
    private StatusPageService $statusPageService;

    /**
     * The date range service instance.
     */
    private DateRangeService $dateRangeService;

    /**
     * Create a new controller instance.
     */
    public function __construct(StatusPageService $statusPageService, DateRangeService $dateRangeService)
    {
        $this->statusPageService = $statusPageService;
        $this->dateRangeService = $dateRangeService;
    }

    /**
     * List the status pages.
     */
    public function index(Request $request): View
    {
        $monitors = Monitor::where('user_id', $request->user()->id)->get();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $monitorId = $request->input('monitor_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $statusPages = StatusPage::with('monitors')
            ->where('user_id', $request->user()->id)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->whereIn('id', MonitorStatusPage::select('status_page_id')->where('monitor_id', '=', $monitorId)->get());
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'url') {
                    return $query->searchUrl($search);
                } else {
                    return $query->searchName($search);
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'monitor_id' => $monitorId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('status-pages.index', ['statusPages' => $statusPages, 'monitors' => $monitors]);
    }

    /**
     * Show the create status page form.
     */
    public function create(Request $request): View
    {
        $monitors = Monitor::where('user_id', $request->user()->id)->get();

        return view('status-pages.new', ['monitors' => $monitors]);
    }

    /**
     * Show the status page.
     */
    public function show(Request $request, $id = null): View
    {
        $dateRange = $this->dateRangeService->build(Carbon::now()->subDays(89), Carbon::now());

        $rangeMap = $this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format($dateRange['format']), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format($dateRange['format']), $dateRange['unit'], $dateRange['format'], 0);

        // If the host request is not the same with the primary domain
        if (request()->getHost() != parse_url(config('app.url'), PHP_URL_HOST)) {
            $statusPage = StatusPage::where('domain', '=', request()->getHost())->firstOrFail();
        } else {
            $statusPage = StatusPage::where('slug', '=', $id)->firstOrFail();
        }

        if (Gate::denies('view', [$statusPage])) {
            if ($statusPage->isPrivate()) {
                abort(403);
            }

            return view('status-pages.password', ['statusPage' => $statusPage]);
        }

        $incidents = Incident::whereIn('monitor_id', $statusPage->monitors->pluck('id'))->whereNull('ended_at')->get();

        $monitors = [];

        $now = Carbon::now();

        foreach ($statusPage->monitors as $key => $monitor) {
            $monitors[$key] = $monitor;

            $incidentsMap = DB::table(DB::raw("(select " . implode(' as `date` union select ', array_map(function ($date) use ($dateRange) { return "'" . Carbon::createFromFormat(($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), $date)->{ ($dateRange['unit'] == 'hour' ? 'startOfHour' : 'startOfDay') }(). "'"; }, array_keys($this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), ($dateRange['unit'] == 'hour' ? 'hour' : 'day'), ($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), 0)))) . " as `date`) as `d`"))
                ->selectRaw("DATE_FORMAT(`d`.`date`, '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, COUNT(`incidents`.`id`) AS `aggregate`")
                ->leftJoin('incidents', function ($join) use ($request, $monitor, $dateRange) {
                    $join->on('incidents.monitor_id', '=', DB::raw($monitor->id))
                        ->where(function ($query) use ($request, $dateRange) {
                            $query->where(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->whereBetween(DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), [DB::raw('`d`.`date`'), DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->whereBetween(DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), [DB::raw('`d`.`date`'), DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->where([[DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '<=', DB::raw('`d`.`date`')], [DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '>=', DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request) {
                                $nestedQuery->where([[DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '<=', DB::raw('`d`.`date`')], [DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '=', null]])
                                    ->where(DB::raw('`d`.`date`'), '<=', DB::raw("'" . Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone')) . "'"));
                            });
                    });
                })
                ->groupBy('date_result')
                ->orderBy('date_result')
                ->get()
                ->mapWithKeys(function ($row) use ($dateRange) {
                    return [$row->date_result => $row->aggregate ? (int) $row->aggregate : 0];
                })
                ->toArray();

            // Merge the results with the pre-calculated possible time range
            $incidentsMap = array_replace($rangeMap, $incidentsMap);

            $incidentsDurationMap = DB::table(DB::raw("(select " . implode(' as `date` union select ', array_map(function ($date) use ($dateRange) { return "'" . Carbon::createFromFormat(($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), $date)->{ ($dateRange['unit'] == 'hour' ? 'startOfHour' : 'startOfDay') }(). "'"; }, array_keys($this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), ($dateRange['unit'] == 'hour' ? 'hour' : 'day'), ($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), 0)))) . " as `date`) as `d`"))
                ->selectRaw("DATE_FORMAT(`d`.`date`, '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, SUM(GREATEST(0, TIMESTAMPDIFF(MICROSECOND, GREATEST(CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), `d`.`date`), LEAST(CONVERT_TZ(COALESCE(`incidents`.`ended_at`, UTC_TIMESTAMP()), '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), DATE_ADD(`d`.`date`, INTERVAL 1 " . ($dateRange['unit'] == 'hour' ? 'HOUR' : 'DAY') . "))))) AS `aggregate`")
                ->leftJoin('incidents', function ($join) use ($request, $monitor, $dateRange) {
                    $join->on('incidents.monitor_id', '=', DB::raw($monitor->id))
                        ->where(function ($query) use ($request, $dateRange) {
                            $query->where(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->whereBetween(DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), [DB::raw('`d`.`date`'), DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->whereBetween(DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), [DB::raw('`d`.`date`'), DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request, $dateRange) {
                                $nestedQuery->where([[DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '<=', DB::raw('`d`.`date`')], [DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '>=', DB::raw('DATE_ADD(`d`.`date`, INTERVAL ' . ($dateRange['unit'] == 'hour' ? '3599 SECOND' : '86399 SECOND') . ')')]]);
                            })
                            ->orWhere(function ($nestedQuery) use ($request) {
                                $nestedQuery->where([[DB::raw("CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '<=', DB::raw('`d`.`date`')], [DB::raw("CONVERT_TZ(`incidents`.`ended_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "')"), '=', null]])
                                    ->where(DB::raw('`d`.`date`'), '<=', DB::raw("'" . Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone')) . "'"));
                            });
                    });
                })
                ->groupBy('date_result')
                ->orderBy('date_result')
                ->get()
                ->mapWithKeys(function ($row) use ($dateRange) {
                    return [$row->date_result => $row->aggregate ? (int) $row->aggregate : 0];
                })
                ->toArray();

            $totalIncidentsDuration = 0;
            foreach ($incidentsDurationMap as $value) {
                $totalIncidentsDuration = $totalIncidentsDuration + $value;
            }

            $monitors[$key]['incidentsMap'] = $incidentsMap;
            $monitors[$key]['incidentsDurationMap'] = $incidentsDurationMap;
            $monitors[$key]['totalIncidentsDuration'] = $totalIncidentsDuration;
        }

        return view('status-pages.show', ['now' => $now, 'statusPage' => $statusPage, 'monitors' => $monitors, 'incidents' => $incidents, 'dateRange' => $dateRange, 'rangeMap' => $this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format($dateRange['format']), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format($dateRange['format']), $dateRange['unit'], $dateRange['format'], 0)]);
    }

    /**
     * Show the edit status page form.
     */
    public function edit(Request $request, $id): View
    {
        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $monitors = Monitor::where('user_id', $request->user()->id)->get();

        return view('status-pages.edit', ['statusPage' => $statusPage, 'monitors' => $monitors]);
    }

    /**
     * Store the status page.
     */
    public function store(StoreStatusPageRequest $request): RedirectResponse
    {
        $this->statusPageService->store($request->validated());

        return redirect()->route('status_pages')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the status page.
     */
    public function update(UpdateStatusPageRequest $request, string $id): RedirectResponse
    {
        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->statusPageService->update($statusPage, $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Status Page.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            StatusPage::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($statusPage) use ($request) {
                $statusPage->delete();
            });

            return redirect()->route('status_pages')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $statusPage->delete();

        return redirect()->route('status_pages')->with('success', __(':name has been deleted.', ['name' => $statusPage->name]));
    }

    /**
     * Validate the Status Page password.
     */
    public function validatePassword(ValidateStatusPagePasswordRequest $request, string $id): RedirectResponse
    {
        $statusPage = StatusPage::findOrFail($id);

        session([$statusPage->passwordSessionKey() => true]);
        return redirect()->back();
    }
}
