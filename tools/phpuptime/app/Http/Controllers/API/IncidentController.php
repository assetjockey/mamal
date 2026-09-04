<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreIncidentRequest;
use App\Http\Requests\API\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncidentController extends Controller
{
    /**
     * The incident service instance.
     */
    private IncidentService $incidentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(IncidentService $incidentService)
    {
        $this->incidentService = $incidentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['monitor', 'cause', 'comment']) ? $request->input('search_by') : 'monitor';
        $monitorId = $request->input('monitor_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'ended_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return IncidentResource::collection(Incident::with('monitor')
            ->where('user_id', $request->user()->id)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->ofMonitor($monitorId);
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'monitor') {
                    return $query->whereHas('monitor', function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    });
                } elseif ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($sortBy == 'ended_at', function ($query) use ($sort, $sortBy) {
                $query->orderByRaw('(`ended_at` IS NULL) ' . $sort);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'monitor_id' => $monitorId, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(StoreIncidentRequest $request): IncidentResource
    {
        $incident = $this->incidentService->store($request->validated());

        return IncidentResource::make($incident);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): IncidentResource|JsonResponse
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($incident) {
            return IncidentResource::make($incident);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncidentRequest $request, string $id): IncidentResource|JsonResponse
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($incident) {
            return IncidentResource::make($this->incidentService->update($incident, $request->validated()));
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
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($incident) {
            $incident->delete();

            return response()->json([
                'id' => $incident->id,
                'object' => 'incident',
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
