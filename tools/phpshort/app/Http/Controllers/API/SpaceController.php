<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreSpaceRequest;
use App\Http\Requests\API\UpdateSpaceRequest;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use App\Services\SpaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SpaceController extends Controller
{
    /**
     * The space service instance.
     */
    private SpaceService $spaceService;

    /**
     * Create a new controller instance.
     */
    public function __construct(SpaceService $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return SpaceResource::collection(Space::where('user_id', '=', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpaceRequest $request): SpaceResource
    {
        $space = $this->spaceService->store($request->validated());

        return SpaceResource::make($space);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): SpaceResource|JsonResponse
    {
        $space = Space::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($space) {
            return SpaceResource::make($space);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpaceRequest $request, string $id): SpaceResource|JsonResponse
    {
        $space = Space::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($space) {
            return SpaceResource::make($this->spaceService->update($space, $request->validated()));
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): SpaceResource|JsonResponse
    {
        $space = Space::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($space) {
            $space->delete();

            return response()->json([
                'id' => $space->id,
                'object' => 'space',
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
