<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\StoreLinkRequest;
use App\Http\Requests\API\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\LinkPixel;
use App\Http\Controllers\Controller;
use App\Services\LinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LinkController extends Controller
{
    /**
     * The link service instance.
     */
    private LinkService $linkService;

    /**
     * Create a new controller instance.
     */
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['title', 'alias', 'url']) ? $request->input('search_by') : 'title';
        $spaceId = $request->input('space_id');
        $domainId = $request->input('domain_id');
        $pixelId = $request->input('pixel_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'clicks_count', 'title', 'alias', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return LinkResource::collection(Link::with('domain', 'space')->where('user_id', '=', $request->user()->id)
            ->when($domainId, function ($query) use ($domainId) {
                return $query->ofDomain($domainId);
            })
            ->when(isset($spaceId) && is_numeric($spaceId), function ($query) use ($spaceId) {
                return $query->ofSpace($spaceId);
            })
            ->when($pixelId, function ($query) use ($pixelId) {
                return $query->whereIn('id', LinkPixel::select('link_id')->where('pixel_id', '=', $pixelId)->get());
            })
            ->when($status, function ($query) use ($status) {
                if ($status == 1) {
                    return $query->active();
                } elseif ($status == 2) {
                    return $query->expired();
                }
                return $query;
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'url') {
                    return $query->searchUrl($search);
                } elseif ($searchBy == 'alias') {
                    return $query->searchAlias($search);
                } else {
                    return $query->searchTitle($search);
                }
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'domain_id' => $domainId, 'space_id' => $spaceId, 'pixel_id' => $pixelId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLinkRequest $request): LinkResource|JsonResponse
    {
        if (!$request->input('multiple_links')) {
            $link = $this->linkService->store($request->validated());

            return LinkResource::make($link);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): LinkResource|JsonResponse
    {
        $link = Link::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($link) {
            return LinkResource::make($link);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLinkRequest $request, string $id): LinkResource|JsonResponse
    {
        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($link) {
            return LinkResource::make($this->linkService->update($link, $request->validated()));
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): LinkResource|JsonResponse
    {
        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($link) {
            $link->delete();

            return response()->json([
                'id' => $link->id,
                'object' => 'link',
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
