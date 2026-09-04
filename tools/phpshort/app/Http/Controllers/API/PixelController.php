<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StorePixelRequest;
use App\Http\Requests\API\UpdatePixelRequest;
use App\Http\Resources\PixelResource;
use App\Models\Pixel;
use App\Services\PixelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PixelController extends Controller
{
    /**
     * The pixel service instance.
     */
    private PixelService $pixelService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PixelService $pixelService)
    {
        $this->pixelService = $pixelService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $type = $request->input('type');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return PixelResource::collection(Pixel::where('user_id', '=', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })->when($type, function ($query) use ($type) {
                return $query->ofType($type);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'type' => $type, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePixelRequest $request): PixelResource
    {
        $pixel = $this->pixelService->store($request->validated());

        return PixelResource::make($pixel);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): PixelResource|JsonResponse
    {
        $pixel = Pixel::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($pixel) {
            return PixelResource::make($pixel);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePixelRequest $request, string $id): PixelResource|JsonResponse
    {
        $pixel = Pixel::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($pixel) {
            return PixelResource::make($this->pixelService->update($pixel, $request->validated()));
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): PixelResource|JsonResponse
    {
        $pixel = Pixel::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($pixel) {
            $pixel->delete();

            return response()->json([
                'id' => $pixel->id,
                'object' => 'pixel',
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
