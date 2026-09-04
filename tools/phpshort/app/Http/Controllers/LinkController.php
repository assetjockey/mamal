<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use App\Models\LinkPixel;
use App\Models\Pixel;
use App\Models\Space;
use App\Models\User;
use App\Services\LinkService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use League\Csv\Bom as CsvBom;
use League\Csv\Writer as CsvWriter;
use SplTempFileObject;

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
     * List the links.
     */
    public function index(Request $request): View
    {
        $spaces = Space::where('user_id', '=', $request->user()->id)->get();

        $domains = Domain::whereIn('user_id', [0, $request->user()->id])->orderBy('name')->get();

        $pixels = Pixel::where('user_id', '=', $request->user()->id)->get();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['title', 'alias', 'url']) ? $request->input('search_by') : 'title';
        $spaceId = $request->input('space_id');
        $domainId = $request->input('domain_id');
        $pixelId = $request->input('pixel_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'clicks_count', 'title', 'alias', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        if (!$request->session()->get('toast')) {
            $request->session()->put('toast', []);
        }

        $links = Link::with('domain', 'space')
            ->where('user_id', '=', $request->user()->id)
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'domain_id' => $domainId, 'space_id' => $spaceId, 'pixel_id' => $pixelId, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $latestLink = Link::where('user_id', '=', $request->user()->id)->orderBy('id', 'desc')->first();

        return view('links.index', ['links' => $links, 'spaces' => $spaces, 'domains' => $domains, 'pixels' => $pixels, 'latestLink' => $latestLink]);
    }

    /**
     * Export the links.
     */
    public function export(Request $request): Response
    {
        if ($request->user()->cannot('dataExport', [User::class])) {
            abort(403);
        }

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['title', 'alias', 'url']) ? $request->input('search_by') : 'title';
        $spaceId = $request->input('space_id');
        $domainId = $request->input('domain_id');
        $pixelId = $request->input('pixel_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['id', 'clicks_count', 'title', 'alias', 'url']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        $links = Link::with('domain', 'space')
            ->where('user_id', '=', $request->user()->id)
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
            ->get();

        $content = CsvWriter::from(new SplTempFileObject);

        $content->insertOne([__('Type'), __('Links')]);
        $content->insertOne([__('Date'), Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) . ' ' . Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone'))->format('H:i:s') . ' (' . CarbonTimeZone::create($request->user()->timezone ?? config('settings.timezone'))->toOffsetName() . ')']);
        $content->insertOne([__('URL'), route('links', $request->query())]);
        $content->insertOne([__(' ')]);

        $content->insertOne([__('Short'), __('Original'), __('Alias'), __('Title'), __('Clicks'), __('Created at')]);
        foreach ($links as $link) {
            $content->insertOne([($link->shortUrl), $link->url, $link->alias, $link->title, $link->clicks_count, $link->created_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d'))]);
        }

        $content->setOutputBOM(CsvBom::Utf8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle([__('Links'), config('settings.title')]) . '.csv"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /**
     * Show the edit link form.
     */
    public function edit(Request $request, string $id): View
    {
        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $spaces = Space::where('user_id', '=', $request->user()->id)->get();

        $domains = Domain::whereIn('user_id', [0, $request->user()->id])->get();

        $pixels = Pixel::where('user_id', '=', $request->user()->id)->get();

        return view('links.edit', ['spaces' => $spaces, 'domains' => $domains, 'pixels' => $pixels, 'link' => $link]);
    }

    /**
     * Store the link.
     */
    public function store(StoreLinkRequest $request): RedirectResponse
    {
        if ($request->input('multiple_links')) {
            $links = $this->linkService->storeMultiple($request->validated());

            return redirect()->back()->with('toast', Link::where('user_id', '=', $request->user()->id)->whereIn('id', $links)->orderBy('id', 'desc')->get());
        } else {
            $this->linkService->store($request->validated());

            return redirect()->back()->with('toast', Link::where('user_id', '=', $request->user()->id)->orderBy('id', 'desc')->limit(1)->get());
        }
    }

    /**
     * Update the link.
     */
    public function update(UpdateLinkRequest $request, string $id): RedirectResponse
    {
        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->linkService->update($link, $request->validated());

        return redirect()->route('links.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the link.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Link::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($link) use ($request) {
                $link->delete();
            });

            return redirect()->route('links')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $link = Link::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $link->delete();

        return redirect()->route('links')->with('success', __(':name has been deleted.', ['name' => $link->displayShortUrl]));
    }
}
