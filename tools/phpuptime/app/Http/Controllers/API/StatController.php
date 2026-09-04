<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SelectStatsRequest;
use App\Http\Resources\StatResource;
use App\Models\Check;
use App\Models\Monitor;
use App\Services\DateRangeService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
     * Display the specified resource.
     */
    public function show(SelectStatsRequest $request, $id): StatResource|JsonResponse
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        $dateRange = $this->dateRangeService->build();

        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        if ($monitor) {
            if ($request->input('name') == 'incident') {
                $stat = DB::table(DB::raw("(select " . implode(' as `date` union select ', array_map(function ($date) use ($dateRange) { return "'" . Carbon::createFromFormat(($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), $date)->{ ($dateRange['unit'] == 'hour' ? 'startOfHour' : 'startOfDay') }(). "'"; }, array_keys($this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), ($dateRange['unit'] == 'hour' ? 'hour' : 'day'), ($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), 0)))) . " as `date`) as `d`"))
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
                    ->havingRaw('`aggregate` > 0')
                    ->groupBy('date_result')
                    ->orderBy('date_result', $sort)
                    ->get();
            } elseif ($request->input('name') == 'incident_duration') {
                $stat = DB::table(DB::raw("(select " . implode(' as `date` union select ', array_map(function ($date) use ($dateRange) { return "'" . Carbon::createFromFormat(($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), $date)->{ ($dateRange['unit'] == 'hour' ? 'startOfHour' : 'startOfDay') }(). "'"; }, array_keys($this->dateRangeService->generateTimeBuckets(Carbon::createFromFormat('Y-m-d', $dateRange['from'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), Carbon::createFromFormat('Y-m-d', $dateRange['to'])->format(($dateRange['unit'] == 'hour' ? 'Y-m-d H' : 'Y-m-d')), ($dateRange['unit'] == 'hour' ? 'hour' : 'day'), ($dateRange['unit'] == 'hour' ? $dateRange['format'] : 'Y-m-d'), 0)))) . " as `date`) as `d`"))
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
                    ->havingRaw('`aggregate` > 0')
                    ->groupBy('date_result')
                    ->orderBy('date_result', $sort)
                    ->get();
            } elseif ($request->input('name') == 'check') {
                $stat = Check::select([
                        DB::raw("date_format(CONVERT_TZ(`checked_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, COUNT(*) as `aggregate`")
                    ])
                    ->where('monitor_id', '=', $monitor->id)
                    ->whereBetween('checked_at', [
                        Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ])
                    ->groupBy('date_result')
                    ->orderBy('date_result', $sort)
                    ->get();
            } else {
                $stat = Check::select([
                        DB::raw("date_format(CONVERT_TZ(`checked_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `date_result`, SUM(`response_time`) as `aggregate`")
                    ])
                    ->where('monitor_id', '=', $monitor->id)
                    ->whereBetween('checked_at', [
                        Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ])
                    ->groupBy('date_result')
                    ->orderBy('date_result', $sort)
                    ->get();
            }

            return StatResource::make($stat);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }
}
