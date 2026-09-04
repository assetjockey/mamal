<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonitorRequest;
use App\Http\Requests\UpdateMonitorRequest;
use App\Models\Check;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorStatusPage;
use App\Models\StatusPage;
use App\Models\User;
use App\Services\DateRangeService;
use App\Services\MonitorService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use League\Csv\Writer as CsvWriter;
use League\Csv\Bom as CsvBom;
use SplTempFileObject;

class MonitorController extends Controller
{
    /**
     * The monitor service instance.
     */
    private MonitorService $monitorService;

    /**
     * The date range service instance.
     */
    private DateRangeService $dateRangeService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MonitorService $monitorService, DateRangeService $dateRangeService)
    {
        $this->monitorService = $monitorService;
        $this->dateRangeService = $dateRangeService;
    }

    /**
     * List the monitors.
     */
    public function index(Request $request): View
    {
        $statusPages = StatusPage::where('user_id', '=', $request->user()->id)->get();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'url']) ? $request->input('search_by') : 'name';
        $statusPageId = $request->input('status_page_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $monitors = Monitor::where('user_id', $request->user()->id)
            ->when($statusPageId, function ($query) use ($statusPageId) {
                return $query->whereIn('id', MonitorStatusPage::select('monitor_id')->where('status_page_id', '=', $statusPageId)->get());
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'status_page_id' => $statusPageId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('monitors.index', ['monitors' => $monitors, 'statusPages' => $statusPages]);
    }

    /**
     * Show the create monitor form.
     */
    public function create(): View
    {
        return view('monitors.new');
    }

    /**
     * Show the monitor.
     */
    public function show(Request $request, string $id): View
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        if (Gate::denies('view', [$monitor, $request->input('token')])) {
            abort(403);
        }

        $dateRange = $this->dateRangeService->build();
        $now = Carbon::now();

        $rangeMap = $this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format($dateRange['format']), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format($dateRange['format']), $dateRange['unit'], $dateRange['format'], 0);

        $checksMap = Check::select([
            DB::raw("date_format(CONVERT_TZ(`checked_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, COUNT(*) as `aggregate`")
            ])
            ->where('monitor_id', '=', $monitor->id)
            ->whereBetween('checked_at', [
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
        $checksMap = array_replace($rangeMap, $checksMap);

        $totalChecks = 0;
        foreach ($checksMap as $value) {
            $totalChecks = $totalChecks + $value;
        }

        $checksResponseTimeMap = Check::select([
            DB::raw("date_format(CONVERT_TZ(`checked_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, SUM(`response_time`) as `aggregate`")
            ])
            ->where('monitor_id', '=', $monitor->id)
            ->whereBetween('checked_at', [
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
        $checksResponseTimeMap = array_replace($rangeMap, $checksResponseTimeMap);

        $totalChecksResponseTime = 0;
        foreach ($checksResponseTimeMap as $value) {
            $totalChecksResponseTime = $totalChecksResponseTime + $value;
        }

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

        $totalIncidents = Incident::where('monitor_id', '=', $monitor->id)
            ->where(function ($query) use ($dateRange, $request) {
                return $query->whereBetween('started_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                    ->orWhereBetween('ended_at', [
                        Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ])
                    ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                        $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                            ->where('ended_at', '>=',  Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'));
                    })
                    ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                        $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                            ->whereNull('ended_at');
                    });
            })
            ->count();

        $incidentsDurationMap = DB::table(DB::raw("(select " . implode(' as `date` union select ', array_map(function ($date) use ($dateRange) { return "'" . Carbon::createFromFormat(($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), $date)->{ ($dateRange['unit'] == 'hour' ? 'startOfHour' : 'startOfDay') }(). "'"; }, array_keys($this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), ($dateRange['unit'] == 'hour' ? 'hour' : 'day'), ($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), 0)))) . " as `date`) as `d`"))
            ->selectRaw("DATE_FORMAT(`d`.`date`, '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, SUM(GREATEST(0, TIMESTAMPDIFF(MICROSECOND, GREATEST(CONVERT_TZ(`incidents`.`started_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), `d`.`date`), LEAST(CONVERT_TZ(COALESCE(`incidents`.`ended_at`, NOW()), '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), DATE_ADD(`d`.`date`, INTERVAL 1 " . ($dateRange['unit'] == 'hour' ? 'HOUR' : 'DAY') . "))))) AS `aggregate`")
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

        $incidents = Incident::where('monitor_id', '=', $monitor->id)
            ->where(function ($query) use ($dateRange, $request) {
                return $query->whereBetween('started_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhereBetween('ended_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                    $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                        ->where('ended_at', '>=',  Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'));
                })
                ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                    $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                        ->whereNull('ended_at');
                });
            })
            ->orderBy('started_at', 'desc')
            ->limit(5)
            ->get();

        $checks = Check::where('monitor_id', '=', $monitor->id)
            ->whereBetween('checked_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->orderBy('checked_at', 'desc')
            ->limit(5)
            ->get();

        return view('monitors.overview', ['monitor' => $monitor, 'now' => $now, 'dateRange' => $dateRange, 'checksMap' => $checksMap, 'checksResponseTimeMap' => $checksResponseTimeMap, 'totalChecks' => $totalChecks, 'totalChecksResponseTime' => $totalChecksResponseTime, 'incidentsMap' => $incidentsMap, 'incidentsDurationMap' => $incidentsDurationMap, 'totalIncidents' => $totalIncidents, 'totalIncidentsDuration' => $totalIncidentsDuration, 'incidents' => $incidents, 'checks' => $checks]);
    }

    /**
     * Show the edit monitor form.
     */
    public function edit(Request $request, string $id): View
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('monitors.edit', ['monitor' => $monitor]);
    }

    /**
     * Get the monitor status in realtime.
     */
    public function realtime(Request $request, string $id): JsonResponse
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        if (Gate::denies('view', [$monitor, $request->input('token')])) {
            abort(403);
        }

        $now = Carbon::now();

        return response()->json([
            'real_time' => view('monitors.partials.real-time', ['dateRange' => $this->dateRangeService->build(), 'monitor' => $monitor, 'now' => $now])->render(),
            'status' => 200
        ], 200);
    }

    /**
     * Show the incidents monitor page.
     */
    public function incidents(Request $request, string $id): View
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        if (Gate::denies('view', [$monitor, $request->input('token')])) {
            abort(403);
        }

        $dateRange = $this->dateRangeService->build();
        $now = Carbon::now();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['cause', 'comment']) ? $request->input('search_by') : 'cause';
        $token = $request->input('token');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'started_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $incidents = Incident::where('monitor_id', '=', $monitor->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->where(function ($query) use ($dateRange, $request) {
                return $query->whereBetween('started_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhereBetween('ended_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                    $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                        ->whereNull('ended_at');
                });
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => $token, 'search' => $search, 'search_by' => $searchBy, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('monitors.incidents', ['monitor' => $monitor, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'monitors.export.incidents', 'incidents' => $incidents]);
    }

    /**
     * Show the checks monitor page.
     */
    public function checks(Request $request, string $id): View
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        if (Gate::denies('view', [$monitor, $request->input('token')])) {
            abort(403);
        }

        $dateRange = $this->dateRangeService->build();
        $now = Carbon::now();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['response_status_code']) ? $request->input('response_status_code') : 'response_status_code';
        $token = $request->input('token');
        $sortBy = in_array($request->input('sort_by'), ['checked_at']) ? $request->input('sort_by') : 'checked_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $checks = Check::where('monitor_id', '=', $monitor->id)
            ->when($search, function ($query) use ($search) {
                return $query->searchResponseStatusCode($search);
            })
            ->whereBetween('checked_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ])
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => $token, 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('monitors.checks', ['monitor' => $monitor, 'now' => $now, 'dateRange' => $dateRange, 'export' => 'monitors.export.checks', 'checks' => $checks]);
    }

    /**
     * Store the monitor.
     */
    public function store(StoreMonitorRequest $request): RedirectResponse
    {
        $this->monitorService->store($request->validated());

        return redirect()->route('monitors')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the monitor.
     */
    public function update(UpdateMonitorRequest $request, string $id): RedirectResponse
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->monitorService->update($monitor, $request->validated());

        if ($request->has('pause')) {
            return back();
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the monitor.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Monitor::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($monitor) use ($request) {
                $monitor->delete();
            });

            return redirect()->route('monitors')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $monitor->delete();

        return redirect()->route('monitors')->with('success', __(':name has been deleted.', ['name' => $monitor->name]));
    }

    /**
     * Export the incidents.
     */
    public function exportIncidents(Request $request, string $id): Response
    {
        $monitor = Monitor::where('id', '=', $id)->firstOrFail();

        if (Gate::denies('view', [$monitor, $request->input('token')])) {
            abort(403);
        }

        if ($monitor->user->cannot('dataExport', [User::class])) {
            abort(403);
        }

        $now = Carbon::now();
        $dateRange = $this->dateRangeService->build();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['cause', 'comment']) ? $request->input('search_by') : 'cause';
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'started_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        $incidents = Incident::where('monitor_id', '=', $id)
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->where(function ($query) use ($dateRange, $request) {
                return $query->whereBetween('started_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhereBetween('ended_at', [
                    Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                ])
                ->orWhere(function ($nestedQuery) use ($dateRange, $request) {
                    $nestedQuery->where('started_at', '<=',  Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'))
                        ->whereNull('ended_at');
                });
            })
            ->orderBy($sortBy, $sort)
            ->get();

        $content = CsvWriter::from(new SplTempFileObject);

        $content->insertOne([__('Monitor'), $monitor->name]);
        $content->insertOne([__('Type'), __('Incidents')]);
        $content->insertOne([__('Date'), $now->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) . ' ' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->format('H:i:s') . ' (' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->getOffsetString() . ')']);
        $content->insertOne([__('URL'), route('monitors.incidents', ['id' => $monitor->id] + $request->query())]);
        $content->insertOne([__(' ')]);

        $content->insertOne([__('ID'), __('URL'), __('Cause'), __('Comment'), __('Duration') . ' (' . __('μs') . ')', __('Started at'), __('Ended at')]);
        foreach ($incidents as $incident) {
            $content->insertOne([$incident->id, $incident->url, $incident->cause, $incident->comment, $incident->started_at->diffInMicroseconds($incident->ended_at ?: $now), $incident->started_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), ($incident->ended_at ? $incident->ended_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) : '')]);
        }

        $content->setOutputBOM(CsvBom::Utf8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle([$monitor->name, __('Incidents'), $dateRange['from'], $dateRange['to'], config('settings.title')]) . '.csv"',
        ]);
    }
}
