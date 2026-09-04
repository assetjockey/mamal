<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * List the domains.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $domains = Domain::where('user_id', '=', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('domains.index', ['domains' => $domains]);
    }

    /**
     * Show the create domain form.
     */
    public function create(): View
    {
        return view('domains.new');
    }

    /**
     * Show the edit domain form.
     */
    public function edit(Request $request, string $id): View
    {
        $domain = Domain::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('domains.edit', ['domain' => $domain]);
    }

    /**
     * Store the domain.
     */
    public function store(StoreDomainRequest $request): RedirectResponse
    {
        $this->domainService->store($request->validated());

        return redirect()->route('domains')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the domain.
     */
    public function update(UpdateDomainRequest $request, string $id): RedirectResponse
    {
        $domain = Domain::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->domainService->update($domain, $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the domain.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Domain::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($domain) use ($request) {
                $domain->delete();
            });

            return redirect()->route('domains')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $domain = Domain::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $domain->delete();

        return redirect()->route('domains')->with('success', __(':name has been deleted.', ['name' => $domain->name]));
    }
}
