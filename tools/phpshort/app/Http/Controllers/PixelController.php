<?php

namespace App\Http\Controllers;

use App\Models\Pixel;
use App\Http\Requests\StorePixelRequest;
use App\Http\Requests\UpdatePixelRequest;
use App\Services\PixelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * List the pixels.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['name']) ? $request->input('search_by') : 'name';
        $type = $request->input('type');
        $sortBy = in_array($request->input('sort_by'), ['id', 'name']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $pixels = Pixel::where('user_id', '=', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchName($search);
            })->when($type, function ($query) use ($type) {
                return $query->ofType($type);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'type' => $type, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('pixels.index', ['pixels' => $pixels]);
    }

    /**
     * Show the create pixel form.
     */
    public function create(): View
    {
        return view('pixels.new');
    }

    /**
     * Show the edit pixel form.
     */
    public function edit(Request $request, string $id): View
    {
        $pixel = Pixel::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('pixels.edit', ['pixel' => $pixel]);
    }

    /**
     * Store the pixel.
     */
    public function store(StorePixelRequest $request): RedirectResponse
    {
        $this->pixelService->store($request->validated());

        return redirect()->route('pixels')->with('success', __(':name has been created.', ['name' => str_replace(['http://', 'https://'], '', $request->input('name'))]));
    }

    /**
     * Update the pixel.
     */
    public function update(UpdatePixelRequest $request, string $id)
    {
        $pixel = Pixel::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->pixelService->update($pixel, $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the pixel.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Pixel::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($pixel) use ($request) {
                $pixel->delete();
            });

            return redirect()->route('pixels')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $pixel = Pixel::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $pixel->delete();

        return redirect()->route('pixels')->with('success', __(':name has been deleted.', ['name' => str_replace(['http://', 'https://'], '', $pixel->name)]));
    }
}
