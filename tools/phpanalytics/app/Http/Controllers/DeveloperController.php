<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    /**
     * Show the Developer index page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('developers.index');
    }

    /**
     * Show the Developer Stats page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stats()
    {
        return view('developers.stats.index');
    }

    /**
     * Show the Developer Websites page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function websites()
    {
        return view('developers.websites.index');
    }

    /**
     * Show the Developer Account page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function account()
    {
        return view('developers.account.index');
    }
}
