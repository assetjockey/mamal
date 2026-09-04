<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Show the sitemap.xml.
     */
    public function index(): Response
    {
        $urls = [
            route('home'),
            route('contact'),
            route('login'),
            route('developers'),
            route('developers.account'),
            route('developers.monitors'),
            route('developers.status_pages'),
            route('developers.incidents'),
            route('developers.stats'),
        ];

        if (enabledPaymentProcessors()) {
            $urls[] = route('pricing');
        }

        if (config('settings.contact_form')) {
            $urls[] = route('contact');
        }

        if (config('settings.registration')) {
            $urls[] = route('register');
        }

        $footerPages = Page::where('visibility', '<>', 0)->get();

        foreach ($footerPages as $footerPage) {
            $urls[] = route('pages.show', $footerPage->slug);
        }

        return response()->view('sitemap.index', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
