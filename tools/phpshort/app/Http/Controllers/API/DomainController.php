<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreDomainRequest;
use App\Http\Requests\API\UpdateDomainRequest;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DomainController extends Controller
{
    /**
     * The domain service instance.
     */
    private DomainService $domainService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
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

        return DomainResource::collection(Domain::where('user_id', '=', $request->user()->id)
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
    public function store(StoreDomainRequest $request): DomainResource
    {
        $domain = $this->domainService->store($request->validated());

        return DomainResource::make($domain);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): DomainResource|JsonResponse
    {
        $domain = Domain::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($domain) {
            return DomainResource::make($domain);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDomainRequest $request, string $id): DomainResource|JsonResponse
    {
        $domain = Domain::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($domain) {
            return DomainResource::make($this->domainService->update($domain, $request->validated()));
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): DomainResource|JsonResponse
    {
        $domain = Domain::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($domain) {
            $domain->delete();

            return response()->json([
                'id' => $domain->id,
                'object' => 'domain',
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
