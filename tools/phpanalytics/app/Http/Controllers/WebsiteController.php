<?php

namespace App\Http\Controllers;

use App\Traits\DateRangeTrait;
use App\Models\Website;
use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Traits\WebsiteTrait;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    use WebsiteTrait, DateRangeTrait;

    /**
     * Show the create Website form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('websites.container', ['view' => 'new']);
    }

    /**
     * Show the edit Website form.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('websites.container', ['view' => 'edit', 'website' => $website]);
    }

    /**
     * Store the Website.
     *
     * @param StoreWebsiteRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreWebsiteRequest $request)
    {
        $this->websiteStore($request);

        $request->user()->has_websites = true;
        $request->user()->save();

        return redirect()->route('dashboard')->with('success', __(':name has been created.', ['name' => $request->input('domain')]));
    }

    /**
     * Update the Website.
     *
     * @param UpdateWebsiteRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateWebsiteRequest $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->websiteUpdate($request, $website);

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Website.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        if ($request->has('bulk')) {
            Website::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($website) use ($request) {
                $website->delete();
            });

            return redirect()->route('dashboard')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $website->delete();

        $request->user()->has_websites = Website::where('user_id', '=', $request->user()->id)->count() > 0;
        $request->user()->save();

        return redirect()->route('dashboard')->with('success', __(':name has been deleted.', ['name' => $website->domain]));
    }
}
