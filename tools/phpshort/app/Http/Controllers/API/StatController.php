<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SelectStatsRequest;
use App\Http\Resources\StatResource;
use App\Models\Link;
use App\Models\Stat;
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
        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($link) {
            $dateRange = $this->dateRangeService->build();

            $search = $request->input('search');
            $searchBy = in_array($request->input('search_by'), ['value']) ? $request->input('search_by') : 'value';
            $sortBy = in_array($request->input('sort_by'), ['count', 'value']) ? $request->input('sort_by') : 'count';
            $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
            $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

            if ($request->input('name') == 'click') {
                $stat = Stat::select([
                        DB::raw("date_format(CONVERT_TZ(`created_at`, '" . CarbonTimeZone::create(config('app.timezone'))->toOffsetName() . "', '" . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . "'), '" . str_replace(['Y', 'm', 'd', 'H'], ['%Y', '%m', '%d', '%H'], $dateRange['format']) . "') as `value`, COUNT(*) as `count`")
                    ])
                    ->where('link_id', '=', $link->id)
                    ->whereBetween('created_at', [
                        Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ])
                    ->groupBy('value')
                    ->orderBy($sortBy, $sort)
                    ->get();
            } else {
                $stat = Stat::selectRaw('`' . $request->input('name') . '` as `value`, COUNT(1) as `count`')
                    ->where('link_id', '=', $link->id)
                    ->when($search, function ($query) use ($search, $searchBy, $request) {
                        return $query->search($search, $request->input('name'));
                    })
                    ->whereBetween('created_at', [
                        Carbon::createFromFormat('Y-m-d', $dateRange['from'], $request->user()->timezone ?? config('settings.timezone'))->startOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        Carbon::createFromFormat('Y-m-d', $dateRange['to'], $request->user()->timezone ?? config('settings.timezone'))->endOfDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
                    ])
                    ->groupBy('value')
                    ->orderBy($sortBy, $sort)
                    ->paginate($perPage)
                    ->appends(['name' => $request->input('name'), 'from' => $request->input('from'), 'to' => $request->input('to'), 'search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);
            }

            return StatResource::make($stat);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }
}
