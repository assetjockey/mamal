<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Models\Space;
use App\Services\SpaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * List the spaces.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $spaces = Space::where('user_id', '=', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('spaces.index', ['spaces' => $spaces]);
    }

    /**
     * Show the create space form.
     */
    public function create(): View
    {
        return view('spaces.new');
    }

    /**
     * Show the edit space form.
     */
    public function edit(Request $request, string $id): View
    {
        $space = Space::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('spaces.edit', ['space' => $space]);
    }

    /**
     * Store the space.
     */
    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        $this->spaceService->store($request->validated());

        return redirect()->route('spaces')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the space.
     */
    public function update(UpdateSpaceRequest $request, string $id): RedirectResponse
    {
        $space = Space::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->spaceService->update($space, $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the space.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Space::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($space) use ($request) {
                $space->delete();
            });

            return redirect()->route('spaces')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $space = Space::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $space->delete();

        return redirect()->route('spaces')->with('success', __(':name has been deleted.', ['name' => $space->name]));
    }
}
