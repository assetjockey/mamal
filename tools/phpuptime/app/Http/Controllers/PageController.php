<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Show the page.
     */
    public function show(string $id): View
    {
        $page = Page::where('slug', $id)
            ->ofLanguage(config('app.locale'))
            ->orderBy('language', 'desc')
            ->firstOrFail();

        return view('pages.show', ['page' => $page]);
    }
}
