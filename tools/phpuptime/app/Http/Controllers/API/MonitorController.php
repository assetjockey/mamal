<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreMonitorRequest;
use App\Http\Requests\API\UpdateMonitorRequest;
use App\Http\Resources\MonitorResource;
use App\Models\Monitor;
use App\Models\MonitorStatusPage;
use App\Services\MonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MonitorController extends Controller
{
    /**
     * The monitor service instance.
     */
    private MonitorService $monitorService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name', 'url']) ? $request->input('search_by') : 'name';
        $statusPageId = $request->input('status_page_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return MonitorResource::collection(Monitor::where('user_id', $request->user()->id)
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'status_page_id' => $statusPageId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMonitorRequest $request): MonitorResource
    {
        $monitor = $this->monitorService->store($request->validated());

        return MonitorResource::make($monitor);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): MonitorResource|JsonResponse
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($monitor) {
            return MonitorResource::make($monitor);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMonitorRequest $request, string $id): MonitorResource|JsonResponse
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($monitor) {
            return MonitorResource::make($this->monitorUpdate($monitor, $request->validated()));
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $monitor = Monitor::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($monitor) {
            $monitor->delete();

            return response()->json([
                'id' => $monitor->id,
                'object' => 'monitor',
                'deleted' => true,
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }
}
