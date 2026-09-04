<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreStatusPageRequest;
use App\Http\Requests\API\UpdateStatusPageRequest;
use App\Http\Resources\StatusPageResource;
use App\Models\MonitorStatusPage;
use App\Models\StatusPage;
use App\Services\StatusPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatusPageController extends Controller
{
    /**
     * The status page service instance.
     */
    private StatusPageService $statusPageService;

    /**
     * Create a new controller instance.
     */
    public function __construct(StatusPageService $statusPageService)
    {
        $this->statusPageService = $statusPageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $monitorId = $request->input('monitor_id');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return StatusPageResource::collection(StatusPage::with('monitors')
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'monitor_id' => $monitorId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStatusPageRequest $request): StatusPageResource
    {
        $statusPage = $this->statusPageService->store($request->validated());

        return StatusPageResource::make($statusPage);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): StatusPageResource|JsonResponse
    {
        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($statusPage) {
            return StatusPageResource::make($statusPage);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStatusPageRequest $request, string $id): StatusPageResource|JsonResponse
    {
        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($statusPage) {
            return StatusPageResource::make($this->statusPageService->update($statusPage, $request->validated()));
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
        $statusPage = StatusPage::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($statusPage) {
            $statusPage->delete();

            return response()->json([
                'id' => $statusPage->id,
                'object' => 'statusPage',
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
