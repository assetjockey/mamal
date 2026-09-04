<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DeveloperController extends Controller
{
    /**
     * Show the developer index page.
     */
    public function index(): View
    {
        return view('developers.index');
    }

    /**
     * Show the developer account page.
     */
    public function account(): View
    {
        return view('developers.account');
    }

    /**
     * Show the developer links page.
     */
    public function links(): View
    {
        return view('developers.links');
    }

    /**
     * Show the developer spaces page.
     */
    public function spaces(): View
    {
        return view('developers.spaces');
    }

    /**
     * Show the developer domains page.
     */
    public function domains(): View
    {
        return view('developers.domains');
    }

    /**
     * Show the developer pixels page.
     */
    public function pixels(): View
    {
        return view('developers.pixels');
    }

    /**
     * Show the developer stats page.
     */
    public function stats(): View
    {
        return view('developers.stats');
    }
}
