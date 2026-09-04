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
     * Show the developer monitors page.
     */
    public function monitors(): View
    {
        return view('developers.monitors.index');
    }

    /**
     * Show the developer status pages page.
     */
    public function statusPages(): View
    {
        return view('developers.status-pages.index');
    }

    /**
     * Show the developer incidents page.
     */
    public function incidents(): View
    {
        return view('developers.incidents.index');
    }

    /**
     * Show the developer stats page.
     */
    public function stats(): View
    {
        return view('developers.stats.index');
    }

    /**
     * Show the developer account page.
     */
    public function account(): View
    {
        return view('developers.account.index');
    }
}
