<?php

namespace App\Traits;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait WebsiteTrait
{
    /**
     * Store the Website.
     *
     * @param Request $request
     * @return Website
     */
    protected function websiteStore(Request $request)
    {
        $website = new Website;

        $website->domain = $request->input('domain');
        $website->user_id = $request->user()->id;
        $website->privacy = $request->input('privacy');
        $website->password = $request->input('password');
        $website->email = $request->input('email');
        $website->exclude_bots = ($request->has('exclude_bots') ? $request->input('exclude_bots') : 1);
        $website->exclude_params = $request->input('exclude_params');
        $website->exclude_ips = $request->input('exclude_ips');
        $website->save();

        return $website;
    }

    /**
     * Update the Website.
     *
     * @param Request $request
     * @param Website $website
     * @return Website
     */
    protected function websiteUpdate(Request $request, Website $website)
    {
        if ($request->has('privacy')) {
            $website->privacy = $request->input('privacy');
        }

        if ($request->has('password')) {
            $website->password = $request->input('password');
        }

        if ($request->has('email')) {
            $website->email = $request->input('email');
        }

        if ($request->has('exclude_bots')) {
            $website->exclude_bots = $request->input('exclude_bots');
        }

        if ($request->has('exclude_params')) {
            $website->exclude_params = $request->input('exclude_params');
        }

        if ($request->has('exclude_ips')) {
            $website->exclude_ips = $request->input('exclude_ips');
        }

        if ($request->has('favorite')) {
            $website->favorited_at = $request->input('favorite') ? Carbon::now() : null;
        }

        $website->save();

        return $website;
    }
}
