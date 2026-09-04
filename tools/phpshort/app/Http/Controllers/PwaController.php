<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaController extends Controller
{
    /**
     * Show the manifest.json.
     */
    public function manifest(): Response
    {
        if (!config('settings.pwa')) {
            abort(404);
        }

        return response()->view('pwa.manifest')->header('Content-Type', 'application/json');
    }
}
